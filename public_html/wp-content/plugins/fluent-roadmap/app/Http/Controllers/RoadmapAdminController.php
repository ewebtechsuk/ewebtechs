<?php

namespace FluentRoadmap\App\Http\Controllers;

use FluentBoards\Framework\Http\Request\Request;
use FluentBoards\Framework\Support\Arr;
use FluentRoadmap\App\Services\RoadMapHelper;

class RoadmapAdminController extends Controller
{
    public function getSettings(Request $request)
    {
        return [
            'settings' => RoadMapHelper::getRoadMapSettings()
        ];
    }

    public function updateSettings(Request $request)
    {
        $settings = $request->get('settings', []);

        $existingSettings = RoadMapHelper::getRoadMapSettings();

        $settings = Arr::only($settings, array_keys($existingSettings));

        $this->validate($settings, [
            'enable_new_idea_submission'     => 'required',
            'new_idea_require_auth'         => 'required',
            'enable_new_comment_submission'  => 'required',
            'new_idea_comment_require_auth' => 'required',
            'enable_new_vote_submission'     => 'required',
            'new_idea_vote_require_auth'    => 'required',
            'add_user_to_crm_new_idea_submission'    => 'nullable'
        ]);

        fluent_boards_update_option('roadmap_settings', $settings);

        return [
            'message'  => 'Settings has been updated successfully',
            'settings' => RoadMapHelper::getRoadMapSettings()
        ];
    }

    public function getPageSettings()
    {
        $settings = fluent_boards_get_option('roadmap_page_mapping', []);
        return [
            'page_settings' => $settings
        ];
    }

    public function updatePageSettings(Request $request)
    {
        $frontPageMapping = $request->get('selectedPages', []);

        $formattedPageMapping = [];
        foreach ($frontPageMapping as $key => $value) {
            if(!$value) {
                continue;
            }
            $formattedPageMapping[] = [
                'roadmap_id' => $key,
                'page_id' => $value
            ];
        }

        $data =  fluent_boards_update_option('roadmap_page_mapping', $formattedPageMapping);

        return [
            'message'  => __('Roadmap Board & Page Mapping Updated Successfully', 'fluent-roadmap'),
            'page_settings' => $data->value
        ];
    }
}
