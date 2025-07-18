<?php

namespace FluentBoardsPro\App\Hooks\Handlers;

use FluentBoards\App\Models\Attachment;
use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Notification;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\PermissionManager;
use FluentBoards\Framework\Support\Str;
use FluentBoardsPro\App\Modules\TimeTracking\Model\TimeTrack;
use FluentBoardsPro\App\Services\ProHelper;
use FluentBoards\App\Services\Libs\FileSystem;
use FluentBoards\App\Models\Activity;

class DataExporter
{
    private $request;

    public function exportTimeSheet()
    {
        $this->verifyRequest();
        $boardId = $this->request->get('board_id');

        $dateRange = ProHelper::getValidatedDateRange($this->request->get('date_range', []));

        $tracks = TimeTrack::when($this->request->get('board_id'), function ($q) use ($boardId) {
            $q->where('board_id', $boardId);
        })
            ->orderBy('updated_at', 'DESC')
            ->whereBetween('completed_at', $dateRange)
            ->with(['user', 'board', 'task' => function ($q) {
                $q->select('id', 'title', 'slug');
            }])
            ->whereHas('task')
            ->get();

        $writer = $this->getCsvWriter();
        $writer->insertOne([
            'Board',
            'Task',
            'Member',
            'Log Date',
            'Billable Hours',
            'Notes'
        ]);

        $rows = [];
        foreach ($tracks as $track) {
            $rows[] = [
                $this->sanitizeForCSV($track->board->title),
                $this->sanitizeForCSV($track->task->title),
                $this->sanitizeForCSV($track->user->display_name),
                $this->formatTime($track->completed_at, 'Y-m-d'),
                $this->miniutesToHours($track->billable_minutes),
                $this->sanitizeForCSV($track->message)
            ];
        }

        $writer->insertAll($rows);
        $writer->output('time-sheet-' . date('Y-m-d_H-i') . '.csv');
        die();
    }

    

    private function prepareSubtasksToExport($task)
    {
        $subtasks = $task->subtasks;
        $subtaskTitles = [];
        foreach ($subtasks as $subtask) {
            $subtaskTitles[] = $this->sanitizeForCSV($subtask->title);
        }
        return implode(', ', $subtaskTitles);
    }

    private function verifyRequest()
    {
        $this->request = FluentBoards('request');
        $boardId = $this->request->get('board_id');
        if (PermissionManager::isBoardManager($boardId)) {
            return true;
        }

        die('You do not have permission');
    }

    private function getCsvWriter()
    {
        if (!class_exists('\League\Csv\Writer')) {
            include FLUENT_BOARDS_PLUGIN_PATH . 'app/Services/Libs/csv/autoload.php';
        }

        return \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
    }

    private function sanitizeForCSV($content)
    {
        $formulas = ['=', '-', '+', '@', "\t", "\r"];

        if (Str::startsWith($content, $formulas)) {
            $content = "'" . $content;
        }

        return $content;
    }

    /*
     * Convert minutes to hours 30 mins as .5 hours
     */
    private function miniutesToHours($minutes)
    {
        return round($minutes / 60, 2);
    }

    private function formatTime($time, $format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($time));
    }

    public function exportBoardInCsv()
    {
        $this->verifyRequest();
        $boardId = $this->request->get('board_id');
        $userId = get_current_user_id(); // Get the current user ID

        if (!$boardId) {
            wp_die(__('Board ID is missing.', 'fluent-boards'));
        }
        
        $board = Board::findOrFail($boardId);

        $boardMeta = $board->getMetaByKey(Constant::BOARD_CSV_EXPORT . '_' . $userId);
        
        if($boardMeta) { 
            $boardMeta = \maybe_unserialize($boardMeta);

            if ($boardMeta['status'] == 'preparing') {
                wp_send_json_error([
                    'message' => __('The export is still in progress.', 'fluent-boards'),
                    'status' => 400
                ]);
                exit();
            }
        }
        

        $totalTasks = Task::where('board_id', $boardId)->whereNull('parent_id')->count();


        // generate file name based on board title, user id and timestamp
        $fileName = preg_replace('/\s+/', '_', $board->title) . '_user_id_' . $userId . '_' . time() . '.csv';
        $file_path = FileSystem::setSubDir('board_' . $boardId)->getDir() . DIRECTORY_SEPARATOR . $fileName;

        $boardMeta = [
            'status' => 'preparing',
            'progress' => 0,
            'user' => $userId,
            'file_path' => $file_path,
            'total_tasks' => $totalTasks
        ];

        $board->updateMeta(Constant::BOARD_CSV_EXPORT . '_' . $userId, maybe_serialize($boardMeta));

        // If tasks are small, generate CSV immediately
        if ($totalTasks <= 400) {
            $this->prepareCsvExportFile($boardId, $userId, 0,  400);
            return wp_send_json_success([
                'status' => 'succeed',
                'progress' => $totalTasks,
                'totalTasks' => $totalTasks,
            ]);
        }

        $chunkSize = 200;

        // Otherwise, process the first 200 tasks instantly
        $processed = $this->prepareSingleChunk($board, $boardMeta, 0, $chunkSize);

        // Schedule the rest with action scheduler
        if ($totalTasks > $chunkSize) {
            as_schedule_single_action(time() + 1, 'fluent_boards_prepare_csv_export_file', [$boardId, $userId, $processed, $chunkSize, ], 'fluent_boards');
        }

        wp_send_json_success([
            'status' => 'file preparing',
            'progress' => $chunkSize,
            'message' => __('Processing initial batch, remaining tasks are scheduled.', 'fluent-boards'),
            'totalTasks' => $totalTasks
        ]);
        exit();
    }


    public function prepareCsvExportFile($boardId, $userId, $offset = 0, $limit = 200)
    {
        $startTime = time();
        $maxExecutionTime = ini_get('max_execution_time') - 5; // Leave some buffer time

        $board = Board::findOrFail($boardId);

        if (!$board) {
            $dieMessage = __('Board Not Found!', 'fluent-boards');
            die($dieMessage);
        }

        $currentOffset = $offset;

        $boardMeta = \maybe_unserialize($board->getMetaByKey(Constant::BOARD_CSV_EXPORT . '_' . $userId));

        if (!$boardMeta) {
            return;
        }

        $totalTasks = $boardMeta['total_tasks'];
        $file_path = $boardMeta['file_path'];

        while (time() - $startTime < $maxExecutionTime && $currentOffset < $totalTasks) {
            $this->prepareSingleChunk($board, $boardMeta, $currentOffset, $limit);
            $currentOffset += $limit;
        }

        // Schedule next chunk if there are remaining tasks
        if ($currentOffset < $totalTasks) {
            as_schedule_single_action(
                time() + 1, 
                'fluent_boards_prepare_csv_export_file', 
                [$boardId, $userId, $currentOffset, $limit], 
                'fluent_boards'
            );
        } else {
            $boardMeta['status'] = 'succeed';
            $boardMeta['progress'] = $totalTasks;
            $board->updateMeta(Constant::BOARD_CSV_EXPORT . '_' . $userId, maybe_serialize($boardMeta));

            // Send notification to the user

            // create attachment then send notification and activity

            $fileUrl = ProHelper::getFullUrlByPath($file_path);

//            Attachment::create([
//                'status'    => 'active',
//                'object_id' => $boardId,
//                'object_type' => Constant::BOARD_ATTACHMENT,
//                'attachment_type' => 'application/csv',
//                'title' =>  "Exported CSV",
//                'file_path' => $file_path,
//                'full_url'  => $fileUrl,
//                'settings'  => '',
//                'driver'    => 'local'
//            ]);
//            $notification = Notification::create([
//                'object_id' => $boardId,
//                'object_type' => Constant::OBJECT_TYPE_BOARD_NOTIFICATION,
//                'activity_by' => $userId,
//                'action' => 'success',
//                'description' => 'Please click : <a href="' . $fileUrl . '">Download CSV</a>'
//            ]);
//
//            $notification->users()->attach($userId);

            

            // Activity::create([
            //     'object_id' => $boardId,
            //     'object_type' => Constant::ACTIVITY_BOARD,
            //     'activity_by' => $userId,
            //     'action' => 'exported_csv',
            //     'description' => "<a href='" . admin_url('admin-ajax.php?action=fluent_boards_export_csv_file_download&board_id=' . $boardId) . "'>Download CSV</a>",
            // ]);

        
        }

    }
    public function prepareSingleChunk($board, $boardMeta, $offset = 0, $limit = 200)
    {
        if (!$board) {
            $dieMessage = __('Board Not Found!', 'fluent-boards');
            die($dieMessage);
        }

        $boardId = $board->id;

        $customFields = $board->customFields;

        // Define task properties
        $taskProperties = [
            'board_title', 'task_title', 'slug', 'type', 'status', 'stage', 'source', 'priority', 'description',
            'position', 'started_at', 'due_at', 'archived_at', 'subtasks'
        ];

        foreach ($customFields as $customField) {
            $taskProperties[] = $customField->slug;
        }

        $header = $taskProperties;

        $file_path = $boardMeta['file_path'];

        // Open the file in append mode
        if (!file_exists(dirname($file_path))) {
            wp_mkdir_p(dirname($file_path), 0755, true);
        }

        $file = fopen($file_path, 'a');

        if ($file === false) {
            return new \WP_Error('file_open_error', __('Failed to open file for writing.'));
        }

        // Write the header row to CSV if it's the first chunk
        if ($offset === 0) {
            fputcsv($file, $header);
        }

        $totalProcessed = $offset;

        $tasks = Task::where('board_id', $boardId)
            ->whereNull('parent_id')
            ->with(['stage', 'customFields'])
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($tasks->isEmpty()) {
            return;
        }

        foreach ($tasks as $task) {
            foreach ($task->customFields as $customField) {
                $pivotSettings = maybe_unserialize($customField->pivot->settings);
                $task->{$customField->slug} = $pivotSettings['value'];
            }

            $row = [];
            foreach ($taskProperties as $property) {
                if ($property == 'stage') {
                    $row[] = $this->sanitizeForCSV($task->stage->title ?? '');
                } elseif ($property == 'board_title') {
                    $row[] = $this->sanitizeForCSV($task->board->title ?? '');
                } elseif ($property == 'task_title') {
                    $row[] = $this->sanitizeForCSV($task->title);
                } elseif ($property == 'subtasks') {
                    $row[] = $this->prepareSubtasksToExport($task);
                } else {
                    $row[] = $this->sanitizeForCSV($task->{$property});
                }
            }

            fputcsv($file, $row);
        }

        $offset += $limit;
        $totalProcessed += $tasks->count();

        $boardMeta['progress'] = $totalProcessed;

        // Update the meta with the current progress
        $board->updateMeta(Constant::BOARD_CSV_EXPORT . '_' . $boardMeta['user'], maybe_serialize($boardMeta));

        fclose($file);

        return $totalProcessed;
    }


    public function prepareBoardInCsv()
    {
        // Get the board_id from POST data
        $boardId = isset($_POST['board_id']) ? intval($_POST['board_id']) : null;

        // Check if boardId is missing
        if ($boardId === null) {
            // Manually trigger a WP_Error
            $error = new \WP_Error('missing_board_id', __('Board ID is missing.'));

            // Send the error back to the original request
            wp_send_json_error([
                'message' => $error->get_error_message(),
                'status' => 400  // Custom error code
            ]);

            die();
        }

        // Proceed with the export if board_id is present
        $this->prepareCsvExportFile($boardId, get_current_user_id(), 0, 200);

        die();
    }

    public function downloadBoardCsvFile()
    {
        $this->verifyRequest();
        $boardId = $this->request->get('board_id');
        $userId = get_current_user_id(); // Get the current user ID

        if (!$boardId) {
            wp_send_json([
                'success' => false,
                'message' => 'Board ID is missing.'
            ]);
        }

        $board = Board::findOrFail($boardId);

        $boardMeta = $board->getMetaByKey(Constant::BOARD_CSV_EXPORT . '_' . $userId);

        if($boardMeta) {
            $boardMeta = \maybe_unserialize($boardMeta);

            $status = $boardMeta['status'];
            $progress = $boardMeta['progress'];
            $totalTasks = $boardMeta['total_tasks'];
            $file_path = $boardMeta['file_path'];

            if ($status === 'succeed') {

                // Set headers for download
                header('Content-Description: File Transfer');
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));

                // Read the file and send it to the output buffer
                readfile($file_path);

                // Delete the file after download
                unlink($file_path);
                // $boardMeta->delete();

                // Exit after the file is sent to prevent additional output
                exit();
            } else {
                wp_send_json([
                    'success' => false,
                    'message' => __('The export is not successful.', 'fluent-boards')
                ]);
            }

        } else {
            wp_send_json([
                'success' => false,
                'message' => __('Export not found.', 'fluent-boards')
            ]);
        }
        
    }

    public function exportCsvStatus()
    {
        $boardId = isset($_POST['board_id']) ? intval($_POST['board_id']) : null;

        if ($boardId === null) {
            wp_send_json_error([
                'message' => __('Board ID is missing.'),
                'status' => 400
            ]);
        }

        $meta = Meta::where('object_id', $boardId)
            ->where('key', Constant::BOARD_CSV_EXPORT . '_' . get_current_user_id())
            ->first();

        if ($meta) {
        
            $boardMeta = \maybe_unserialize($meta->value);

            $status = $boardMeta['status'];
            $progress = $boardMeta['progress'];
            $totalTasks = $boardMeta['total_tasks'];

            wp_send_json_success([
                'status' => $status,
                'progress' => $progress,
                'totalTasks' => $totalTasks
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Export status not found.'),
                'status' => 404
            ]);
        }
    }

}

