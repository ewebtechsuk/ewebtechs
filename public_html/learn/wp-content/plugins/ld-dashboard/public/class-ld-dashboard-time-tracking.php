<?php
/**
 * LearnDash class for displaying the course wizard.
 *
 * @package    LearnDash
 * @since      4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ld_Dashboard_Time_Tracking' ) ) {
	/**
	 * Course wizard class.
	 */
	class Ld_Dashboard_Time_Tracking {
		protected $is_enable_time_tracking;
		public function __construct() {

			$obj                           = Ld_Dashboard_Functions::instance();
			$ld_dashboard_settings_data    = $obj->ld_dashboard_settings_data();
			$this->is_enable_time_tracking = false;
			if ( isset( $ld_dashboard_settings_data['time_tracking']['enable'] ) && 1 == $ld_dashboard_settings_data['time_tracking']['enable'] ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'ld_dashboard_time_tracking_enqueue_styles' ) );
				add_action( 'wp_ajax_add_time_tracking_entry', array( $this, 'ld_dashboard_add_time_tracking_entry' ) );// Add time entry.
				$this->is_enable_time_tracking = true;
			}

			add_action( 'wp_ajax_ld_dashboard_time_spent_on_course', array( $this, 'ld_dashboard_time_spent_on_course' ) );
			add_action( 'wp_ajax_ld_dashboard_course_lists_info', array( $this, 'ld_dashboard_course_lists_info' ) );
			add_action( 'wp_ajax_ld_dashboard_student_course_report', array( $this, 'ld_dashboard_student_course_report' ) );
			add_action( 'wp_ajax_ld_dashboard_reset_user_time_tracking', array( $this, 'ld_dashboard_reset_user_time_tracking' ) );// reset time entry.

			add_action( 'edit_user_profile', array( $this, 'ld_dashboard_show_user_time_tracking' ) );
		}

		public function ld_dashboard_time_tracking_enqueue_styles() {
			global $post;

			if ( ! is_singular( array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) ) ) {
				return;
			}

			if ( ! is_user_logged_in() ) {
				return;
			}

			$course_id = learndash_get_course_id( $post->ID );
			$user_id   = get_current_user_id();
			$meta      = learndash_get_setting( $course_id );

			if ( ( isset( $meta['course_price_type'] ) ) && ( 'open' === $meta['course_price_type'] ) ) {
				return;
			}

			if ( ! sfwd_lms_has_access( $course_id, $user_id ) ) {
				return;
			}

			$obj                           = Ld_Dashboard_Functions::instance();
			$ld_dashboard_settings_data    = $obj->ld_dashboard_settings_data();
			$settings                      = $ld_dashboard_settings_data['time_tracking'];
			$settings['idle_messsage']     = ( isset( $settings['idle_messsage'] ) && $settings['idle_messsage'] != '' ) ? $settings['idle_messsage'] : esc_html__( 'Are you still on this page?', 'ld-dashboard' );
			$settings['idle_button_label'] = ( isset( $settings['idle_button_label'] ) && $settings['idle_button_label'] != '' ) ? $settings['idle_button_label'] : esc_html__( 'Yes, I am', 'ld-dashboard' );
			wp_enqueue_script( 'ld-dashboard-time-tracking', plugin_dir_url( __FILE__ ) . 'js/time-tracking.js', array( 'jquery' ), '', false );
			$time_tracking = array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'   => wp_create_nonce( 'ajax-nonce' ),
				'nonce'        => wp_create_nonce( 'ld-dashboard' ),
				'idle_time'    => $settings['idle_time'],
				'idle_popup'   => apply_filters( 'ld-dashboard-show-idle-popup-message', true ),
				'idle_message' => '<div class="ld-dashboard-idle-message-wrap"><span>' . $settings['idle_messsage'] . '</span><button class="ld-dashboard-resume-timer ld-dashboard-btn">' . $settings['idle_button_label'] . '</button></div>',
				'post_id'      => $post->ID,
				'course_id'    => $course_id,
				'user_id'      => $user_id,
				'is_enrolled'  => sfwd_lms_has_access( $course_id, $user_id ),
			);
			wp_localize_script( 'ld-dashboard-time-tracking', 'time_tracking', $time_tracking );
		}

		/**
		 * This method adds an timespent entry for a user on a particular course.
		 */
		public function ld_dashboard_add_time_tracking_entry() {
			global $wpdb;
			$user_id          = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$post_id          = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
			$course_id        = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
			$activity_updated = filter_input( INPUT_POST, 'time', FILTER_VALIDATE_INT );
			$time_spent       = filter_input( INPUT_POST, 'total_time', FILTER_VALIDATE_INT );

			$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ld-dashboard' ) ) {
				wp_send_json_error();
				die();
			}
			if ( empty( $post_id ) || empty( $course_id ) || empty( $time_spent ) ) {
				wp_send_json_error();
				die();
			}
			$table_name   = $wpdb->prefix . 'ld_dashboard_time_tracking';
			$last_updated = $this->fetch_last_updated_activity( $post_id, $course_id, $user_id );

			if ( empty( $last_updated ) ) {
				// Create new entry.
				$insert_id = $wpdb->insert(
					$table_name,
					array(
						'course_id'        => $course_id,
						'post_id'          => $post_id,
						'user_id'          => $user_id,
						'time_spent'       => $time_spent,
						'activity_updated' => $activity_updated,
						'created'          => date( 'Y-m-d H:i:s' ),
					),
					array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
					)
				);

				if ( false === $insert_id ) {
					wp_send_json_error();
					die();
				}
				wp_send_json_success();
				die();
			}
			// Update existing entry.
			$activity = $this->fetch_last_updated_entry( $post_id, $course_id, $user_id );
			if ( empty( $activity ) ) {
				wp_send_json_error();
				die();
			}
			$activity_id         = current( array_column( $activity, 'id' ) );
			$previous_time_spent = current( array_column( $activity, 'time_spent' ) );
			$total_time_spent    = $time_spent + $previous_time_spent;

			$updated = $wpdb->update(
				$table_name,
				array(
					'activity_updated' => $activity_updated,
					'time_spent'       => $total_time_spent,
				),
				array(
					'id' => $activity_id,
				),
				array(
					'%d',
					'%d',
				),
				array(
					'%d',
				)
			);
			if ( false === $updated ) {
				wp_send_json_error();
				die();
			}
			wp_send_json_success();
			die();
		}
		/**
		 * This method is used to fetch the last updated activity for a user.
		 *
		 * @param  integer $post_id      Post ID.
		 * @param  integer $course_id    Course ID.
		 * @param  integer $user_id      User ID.
		 * @return array    Timespent value for the supplied params.
		 */
		public function fetch_last_updated_activity( $post_id, $course_id, $user_id = 0 ) {
			global $wpdb;

			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$output = $wpdb->get_results( $wpdb->prepare( 'SELECT time_spent FROM ' . $wpdb->prefix . 'ld_dashboard_time_tracking WHERE post_id = %d AND course_id = %d AND user_id = %d', $post_id, $course_id, $user_id ), ARRAY_A );
			if ( empty( $output ) ) {
				return false;
			}
			return $output;
			// $latest_update = max( array_column( $output, 'activity_updated' ) );// Max timestamp.
			// return $latest_update;
		}

		/**
		 * This method is used to fetch the last updated entry for a user.
		 *
		 * @param  integer $post_id      Post ID.
		 * @param  integer $course_id    Course ID.
		 * @param  integer $user_id      User ID.
		 * @return array    Timespent value for the supplied params.
		 */
		public function fetch_last_updated_entry( $post_id, $course_id, $user_id = 0 ) {
			global $wpdb;

			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$table_name = $wpdb->prefix . '';
			$output     = $wpdb->get_results( $wpdb->prepare( 'SELECT id, time_spent FROM ' . $wpdb->prefix . 'ld_dashboard_time_tracking WHERE post_id = %d AND course_id = %d AND user_id = %d', $post_id, $course_id, $user_id ), ARRAY_A );
			return $output;
		}

		public function ld_dashboard_time_spent_on_course() {
			global $wpdb;

			$course_id = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
			$nonce     = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
			$user      = wp_get_current_user();

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
				wp_send_json_error();
				die();
			}
			if ( $course_id != '' ) {
				$cours_ids      = array( $course_id );
				$course_pricing = learndash_get_course_price( $course_id );
				if ( isset( $course_pricing['type'] ) && 'open' !== $course_pricing['type'] ) {
					$course_student   = learndash_get_course_users_access_from_meta( $course_id );
					$course_group_ids = learndash_get_course_groups( $course_id );
					if ( is_array( $course_group_ids ) && ! empty( $course_group_ids ) ) {
						foreach ( $course_group_ids as $grp_id ) {
							$group_users = learndash_get_groups_user_ids( $grp_id );
							if ( ! empty( $group_users ) ) {
								$course_student = array_unique( array_merge( $course_student, $group_users ) );
							}
						}
					}
				} else {
					$course_student = array();
					$users          = get_users();
					if ( ! empty( $users ) ) {
						foreach ( $users as $student ) {
							$course_student[] = $student->ID;
						}
					}
				}
				$student_ids = $course_student;
				if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', (array) $user->roles ) ) {
					$group_student_ids = learndash_get_group_leader_groups_users();
					$course_count      = learndash_get_group_leader_groups_courses();
					$student_ids       = array_intersect( $student_ids, $group_student_ids );
				}

				if ( empty( $student_ids ) ) {
					$error = new WP_Error(
						'no-student-found',
						__( 'No student found in this course', 'ld-dashboard' ),
					);
					wp_send_json_error( $error );
				}
				$student_count = count( $student_ids );

				$total_time_spent  = 0;
				$student_wise_time = array();
				foreach ( $student_ids as $student ) {
					$student_time = 0;
					$student_info = get_user_by( 'id', $student );
					$user_t       = $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $student );
					if ( $user_t < 0 ) {
						$user_t = 0;
					}

					$student_time = $student_time + $user_t;

					$total_time_spent              = $total_time_spent + $student_time;
					$student_name                  = $student_info->display_name;
					$student_wise_time[ $student ] = array(
						'time'  => $student_time,
						'title' => $student_name,
						'html'  => '<div class="ld-dashboard-course-title"><span class="ld-dashboard-course-student">' . $student_name . '</span><span class="ld-dashboard-timespent">' . gmdate( 'H:i:s', $student_time ) . '</span></div>',
					);
				}

				$key_values = array_column( $student_wise_time, 'time' );
				array_multisort( $key_values, SORT_DESC, $student_wise_time );

				$chart_data       = array();
				$other_completion = 0;
				$display_student  = $student_count;
				if ( $student_count >= apply_filters( 'ld_dashboard_minimum_course_show_on_chat', 7 ) ) {
					$display_student = apply_filters( 'ld_dashboard_minimum_course_show_on_chat', 7 );
				}
				for ( $i = 0; $i < $display_student; $i++ ) {

					$chart_data[] = array(
						'time_inhour' => gmdate( 'H:i:s', $student_wise_time[ $i ]['time'] ),
						'time'        => $student_wise_time[ $i ]['time'],
						'title'       => $student_wise_time[ $i ]['title'],
					);

				}

				$overall_average_time = intval( $total_time_spent / $student_count );
				ob_start();
				?>
					<div class="ld-dashboard-time-spent-on-a-course-summary course-summary ">
						<div class="revenue-figure-wrapper ">
							<div class="chart-summary-revenue-figure">
								<div class="revenue-figure">
									<span class="summary-amount"><?php echo gmdate( 'H:i:s', $overall_average_time ); ?></span>
								</div>
								<div class="chart-summary-label">
									<span><?php esc_html_e( 'AVG TIME SPENT PER LEARNER', 'ld-dashboard' ); ?></span>
									<span class="dashicons dashicons-info-outline widm-ld-reports-info" data-title="<?php esc_html_e( 'Avg Time Spent = Total Time Spent/No. of Students', 'ld-dashboard' ); ?>"></span>
								</div>
							</div>
						</div>
						<div class="revenue-particulars-wrapper">
							<div class="chart-summary-revenue-particulars">
								<div class="summery-right-entry">
									<span class="summary-label"><?php esc_html_e( 'Total Time Spent:', 'ld-dashboard' ); ?> </span>
									<span class="summary-amount"><?php echo gmdate( 'H:i:s', $total_time_spent ); ?></span>
								</div>
								<div class="summery-right-entry">
									<span class="summary-label"><?php esc_html_e( 'Students', 'ld-dashboard' ); ?></span>
									<span class="summary-amount"><?php echo $student_count; ?></span>
								</div>
							</div>
						</div>
					</div>

				<?php
				$response = ob_get_clean();

				wp_send_json_success(
					array(
						'averageCourseTime' => $overall_average_time,
						'courseWiseTime'    => $student_wise_time,
						'courseCount'       => $student_count,
						'courseTotalTime'   => $total_time_spent,
						'chart_data'        => $chart_data,
						'html'              => $response,
						'chart_type'        => 'bar',
					),
				);

			} else {
				/* All Course Wise Data*/
				$cours_ids = ldd_get_user_courses_list( get_current_user_id(), true, true );
			}

			$course_count     = count( $cours_ids );
			$total_time_spent = 0;
			$course_wise_time = array();
			foreach ( $cours_ids as $course ) {
				$course_time = 0;

				$user_t = $this->fetch_ld_dashboard_course_wise_time_spent( $course );
				if ( $user_t < 0 ) {
					$user_t = 0;
				}

				$course_time = $course_time + $user_t;

				$total_time_spent            = $total_time_spent + $course_time;
				$course_title                = get_the_title( $course );
				$course_wise_time[ $course ] = array(
					'time'   => $course_time,
					'course' => $course_title,
					'html'   => '<div class="ld-dashboard-course-title"><span class="ld-dashboard-course-student">' . $course_title . '</span> <span class="ld-dashboard-timespent">' . gmdate( 'H:i:s', $course_time ) . '</span></div>',
				);
			}
			$key_values = array_column( $course_wise_time, 'time' );
			array_multisort( $key_values, SORT_DESC, $course_wise_time );

			$chart_data           = array();
			$other_completion     = 0;
			$display_course_count = $course_count;
			if ( $course_count >= apply_filters( 'ld_dashboard_minimum_course_show_on_chat', 7 ) ) {
				$display_course_count = apply_filters( 'ld_dashboard_minimum_course_show_on_chat', 7 );
			}
			for ( $i = 0; $i < $display_course_count; $i++ ) {
				$chart_data[] = array(
					'time_inhour' => gmdate( 'H:i:s', $course_wise_time[ $i ]['time'] ),
					'time'        => $course_wise_time[ $i ]['time'],
					'title'       => $course_wise_time[ $i ]['course'],
				);

			}

			$overall_average_time = intval( $total_time_spent / $course_count );
			ob_start();
			?>
				<div class="ld-dashboard-time-spent-on-a-course-summary course-summary ">
					<div class="revenue-figure-wrapper ">
						<div class="chart-summary-revenue-figure">
							<div class="revenue-figure">
								<span class="summary-amount"><?php echo gmdate( 'H:i:s', $overall_average_time ); ?></span>
							</div>
							<div class="chart-summary-label">
								<span><?php esc_html_e( 'AVG TIME SPENT', 'ld-dashboard' ); ?></span>
								<span class="dashicons dashicons-info-outline widm-ld-reports-info" data-title="<?php esc_html_e( 'Avg time spent = total time spent in courses/total no. of students who have completed the courses', 'ld-dashboard' ); ?>"></span>
							</div>
						</div>
					</div>
					<div class="revenue-particulars-wrapper">
						<div class="chart-summary-revenue-particulars">
							<div class="summery-right-entry">
								<span class="summary-label"><?php esc_html_e( 'Total Time Spent:', 'ld-dashboard' ); ?> </span>
								<span class="summary-amount"><?php echo gmdate( 'H:i:s', $total_time_spent ); ?></span>
							</div>
							<div class="summery-right-entry">
								<span class="summary-label"><?php esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ); ?></span>
								<span class="summary-amount"><?php echo $course_count; ?></span>
							</div>
						</div>
					</div>
				</div>

			<?php
			$response = ob_get_clean();

			wp_send_json_success(
				array(
					'averageCourseTime' => $overall_average_time,
					'courseWiseTime'    => $course_wise_time,
					'courseCount'       => $course_count,
					'courseTotalTime'   => $total_time_spent,
					'chart_data'        => $chart_data,
					'html'              => $response,
					'chart_type'        => 'doughnut',
				),
			);

		}

		/**
		 * This method is used to fetch time spent on a course.
		 *
		 * @param  int $course_id Course ID.
		 * @return int Time in seconds.
		 */
		public function fetch_ld_dashboard_course_wise_time_spent( $course_id, $user_id = '', $post_id = '' ) {
			global $wpdb;

			$total_time_spent = 0;

			if ( $user_id != '' && $post_id == '' ) {

				$output = $wpdb->get_results( $wpdb->prepare( 'SELECT time_spent FROM ' . $wpdb->prefix . 'ld_dashboard_time_tracking WHERE course_id = %d AND user_id = %d', $course_id, $user_id ), ARRAY_A );

			} elseif ( $user_id != '' && $post_id != '' ) {

				$output = $wpdb->get_results( $wpdb->prepare( 'SELECT time_spent FROM ' . $wpdb->prefix . 'ld_dashboard_time_tracking WHERE course_id = %d AND post_id = %d AND user_id = %d', $course_id, $post_id, $user_id ), ARRAY_A );

			} else {

				$output = $wpdb->get_results( $wpdb->prepare( 'SELECT sum(time_spent) as time_spent FROM ' . $wpdb->prefix . 'ld_dashboard_time_tracking WHERE course_id = %d ', $course_id ), ARRAY_A );
			}

			if ( empty( $output ) ) {
				return $total_time_spent;
			}

			$total_time_spent = array_sum( array_column( $output, 'time_spent' ) );
			return $total_time_spent;
		}

		public function ld_dashboard_course_lists_info() {
			global $wpdb;

			$course_id = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
			$nonce     = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
			$user      = wp_get_current_user();

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
				wp_send_json_error();
				die();
			}
			if ( $course_id != '' && $course_id != 0 ) {

				$course_pricing = learndash_get_course_price( $course_id );
				if ( isset( $course_pricing['type'] ) && 'open' !== $course_pricing['type'] ) {
					$course_student   = learndash_get_course_users_access_from_meta( $course_id );
					$course_group_ids = learndash_get_course_groups( $course_id );
					if ( is_array( $course_group_ids ) && ! empty( $course_group_ids ) ) {
						foreach ( $course_group_ids as $grp_id ) {
							$group_users = learndash_get_groups_user_ids( $grp_id );
							if ( ! empty( $group_users ) ) {
								$course_student = array_unique( array_merge( $course_student, $group_users ) );
							}
						}
					}
				} else {
					$course_student = array();
					$users          = get_users();
					if ( ! empty( $users ) ) {
						foreach ( $users as $student ) {
							$course_student[] = $student->ID;
						}
					}
				}
				$student_ids = $course_student;
				if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', (array) $user->roles ) ) {
					$group_student_ids = learndash_get_group_leader_groups_users();
					$course_count      = learndash_get_group_leader_groups_courses();
					$student_ids       = array_intersect( $student_ids, $group_student_ids );
				}
				$students      = $student_ids;
				$student_count = count( $student_ids );

				if ( empty( $students ) ) {
					$error = new WP_Error(
						'no-data',
						sprintf(/* translators: %s: custom label for course */
							__( 'No Students enrolled in this %s.', 'ld-dashboard' ),
							\LearnDash_Custom_Label::label_to_lower( 'course' )
						),
					);

					wp_send_json_error( $error );
				}
				$table       = array();
				$quizzes     = learndash_course_get_children_of_step( $course_id, $course_id, 'sfwd-quiz', 'ids', true );
				$quizzes_str = implode( ', ', $quizzes );
				$quiz_count  = count( $quizzes );
				foreach ( $students as $student_id ) {
					$attempts = 0;
					$pass     = 0;
					$score    = 0;
					$counter  = 0;

					$progress        = learndash_user_get_course_progress( $student_id, $course_id, 'summary' );
					$total_steps     = $progress['total'];
					$completed_steps = $progress['completed'];
					$student_info    = get_userdata( $student_id );
					$student_name    = $student_info->first_name . ' ' . $student_info->last_name;
					$student_name    = ! empty( $student_name ) ? $student_name : $student_info->display_name;

					$percentage = 100;
					if ( 0 != $progress['total'] ) {
						$percentage = floatval( number_format( 100 * $progress['completed'] / $progress['total'], 2, '.', '' ) );// Cast to integer if no decimals.
					}

					$since = ld_course_access_from( $course_id, $student_id );
					if ( ! empty( $since ) ) {
						$since = learndash_adjust_date_time_display( $since, 'd M, Y' );
					} else {
						$since = learndash_user_group_enrolled_to_course_from( $student_id, $course_id );
						if ( ! empty( $since ) ) {
							$since = learndash_adjust_date_time_display( $since, 'd M, Y' );
						}
					}

					$completed = get_user_meta( $student_id, 'course_completed_' . $course_id, true );
					if ( ! empty( $completed ) ) {
						$completed = learndash_adjust_date_time_display( $completed, 'd M, Y' );
					}

					$status = learndash_user_get_course_progress( $student_id, $course_id, 'summary' );
					$time   = 0;
					$time   = $time + $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $student_id );

					// $avg_time = $avg_time + $time_tracking->fetch_user_average_course_completion_time( $course_id, $student_id );

					$table[] = array(
						'name'             => $student_name,
						'email'            => $student_info->user_email,
						'started'          => ! empty( $since ) ? $since : '-',
						'steps'            => $completed_steps . ' ' . esc_html__( 'out of', 'ld-dashboard' ) . ' ' . $total_steps,
						'course_progress'  => $percentage . '%',
						'completed'        => ! empty( $completed ) ? $completed : '-',
						'total_time_spent' => 0 == $time ? '-' : date_i18n( 'H:i:s', $time ),
						// 'quiz_attempts'          => $attempts,
						// 'pass_rate'              => empty( $attempts ) ? '-' : floatval( number_format( 100 * $pass / $attempts, 2, '.', '' ) ) . '%',
						// 'avg_score'              => empty( $score ) ? '-' : floatval( number_format( $score, 2, '.', '' ) ) . '%',
					);
					$user_detail_url = add_query_arg(
						array(
							'tab'       => 'course-report',
							'user'      => $student_info->ID,
							'course_id' => $course_id,
							'_lddnonce' => wp_create_nonce( 'course-report-nonce' ),
						),
						Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' )
					);
					if ( $this->is_enable_time_tracking ) {
						$data_table[] = array(
							'<a href="' . esc_url( $user_detail_url ) . '" target="_blank">' . $student_name . '</a>',
							$student_info->user_email,
							! empty( $since ) ? $since : '-',
							$completed_steps . ' ' . esc_html__( 'out of', 'ld-dashboard' ) . ' ' . $total_steps,
							$percentage . '%',
							! empty( $completed ) ? $completed : '-',
							0 == $time ? '-' : date_i18n( 'H:i:s', $time ),
						);
					} else {
						$data_table[] = array(
							'<a href="' . esc_url( $user_detail_url ) . '">' . $student_name . '</a>',
							$student_info->user_email,
							! empty( $since ) ? $since : '-',
							$completed_steps . ' ' . esc_html__( 'out of', 'ld-dashboard' ) . ' ' . $total_steps,
							$percentage . '%',
							! empty( $completed ) ? $completed : '-',
						);
					}
				}
				$table_column[] = array( 'title' => __( 'Name', 'ld-dashboard' ) );
				$table_column[] = array( 'title' => __( 'Email ID', 'ld-dashboard' ) );
				$table_column[] = array( 'title' => __( 'Enrolled On', 'ld-dashboard' ) );
				$table_column[] = array( 'title' => __( 'Steps', 'ld-dashboard' ) );
				$table_column[] = array( 'title' => __( 'Progress %', 'ld-dashboard' ) );
				$table_column[] = array( 'title' => __( 'Completion Date', 'ld-dashboard' ) );
				if ( $this->is_enable_time_tracking ) {
					$table_column[] = array( 'title' => __( 'Total Time Spent', 'ld-dashboard' ) );
				}

				/* Single Course Report Return */
				wp_send_json_success(
					array(
						'table'        => $table,
						'data_table'   => $data_table,
						'table_column' => $table_column,
						'row_name'     => sprintf( __( '%s - Student Progress Report', 'ld-dashboard' ), get_the_title( $course_id ) ),
					),
				);
			} else {
				/* All Course Wise Data*/
				$cours_ids = ldd_get_user_courses_list( get_current_user_id(), true, true );
			}
			$query_args   = array(
				'post_type'      => 'sfwd-courses',
				'posts_per_page' => '-1',
				'post__in'       => $cours_ids,
			);
			$courses      = get_posts( $query_args );
			$table        = array();
			$course_count = count( $courses );
			foreach ( $courses as $course ) {
				$course_id         = $course->ID;
				$course_price_type = learndash_get_course_meta_setting( $course_id, 'course_price_type' );

				$course_pricing = learndash_get_course_price( $course_id );
				if ( 'open' !== $course_pricing['type'] ) {
					$course_student   = learndash_get_course_users_access_from_meta( $course_id );
					$course_group_ids = learndash_get_course_groups( $course_id );
					if ( is_array( $course_group_ids ) && ! empty( $course_group_ids ) ) {
						foreach ( $course_group_ids as $grp_id ) {
							$group_users = learndash_get_groups_user_ids( $grp_id );
							if ( ! empty( $group_users ) ) {
								$course_student = array_unique( array_merge( $course_student, $group_users ) );
							}
						}
					}
				} else {
					$course_student = array();
					$users          = get_users();
					if ( ! empty( $users ) ) {
						foreach ( $users as $student ) {
							$course_student[] = $student->ID;
						}
					}
				}
				$student_ids = $course_student;
				if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', (array) $user->roles ) ) {
					$group_student_ids = learndash_get_group_leader_groups_users();
					$course_count      = learndash_get_group_leader_groups_courses();
					$student_ids       = array_intersect( $student_ids, $group_student_ids );
				}
				$students      = $student_ids;
				$student_count = count( $student_ids );

				$quizzes             = learndash_course_get_children_of_step( $course_id, $course_id, 'sfwd-quiz', 'ids', true );
				$quiz_count          = count( $quizzes );
				$time                = 0;
				$avg_time            = 0;
				$quiz_time           = 0;
				$quiz_average_time   = 0;
				$not_started_count   = 0;
				$in_progress_count   = 0;
				$completed_count     = 0;
				$score               = 0;
				$counter             = 0;
				$completed_user_time = 0;
				if ( $student_count > 0 ) {
					foreach ( $students as $student_id ) {

						$status = learndash_user_get_course_progress( $student_id, $course_id, 'summary' );

						$user_time_spent = $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $student_id );
						$time            = $time + $user_time_spent;
						// $avg_time = $avg_time + $time_tracking->fetch_user_average_course_completion_time( $course_id, $student_id );

						if ( empty( $status ) ) {
							$not_started_count++;
						} else {
							switch ( $status['status'] ) {
								case 'in_progress':
									$in_progress_count++;
									break;
								case 'completed':
									$completed_count++;
									$completed_user_time += $user_time_spent;
									break;
								case 'not_started':
								default:
									$not_started_count++;
									break;
							}
						}
					}
				}
				$table_data = array(
					'course' => $course->post_title,
				);

				$avg_time           = 0 == $completed_count ? 0 : floatval( number_format( $time / $completed_count, 2, '.', '' ) );
				$avg_completed_time = 0 == $completed_count ? 0 : floatval( number_format( $completed_user_time / $completed_count, 2, '.', '' ) ); // The completed average time
				$total_time         = number_format( $time, 2, '.', '' );
				$time               = 0 == $student_count ? 0 : floatval( number_format( $time / $student_count, 2, '.', '' ) );

				$arraydata = array(
					/* translators: %1$d: Completed Count %2$d: Total Student count */
					'completed_users'      => sprintf( __( '%1$d of %2$d', 'ld-dashboard' ), $completed_count, $student_count ),
					'in_progress'          => $in_progress_count,
					'not_started'          => $not_started_count,
					'completion_rate2'     => empty( $student_count ) ? '-' : floatval( number_format( 100 * $completed_count / $student_count, 2, '.', '' ) ) . '%',
					'total_time_spent'     => 0 == $total_time ? '-' : sprintf( '%02d:%02d:%02d', ( $total_time / 3600 ), ( $total_time / 60 % 60 ), $total_time % 60 ),
					'avg_total_time_spent' => 0 == $time ? '-' : sprintf( '%02d:%02d:%02d', ( $time / 3600 ), ( $time / 60 % 60 ), $time % 60 ),
					'avg_time_spent'       => 0 == $avg_time ? '-' : date_i18n( 'H:i:s', $avg_time ),

					'completed_count'      => $completed_count,
					'student_count'        => $student_count,
				);

				$table_data = array_merge(
					$table_data,
					$arraydata
				);
				$table[]    = $table_data;

				if ( $this->is_enable_time_tracking ) {
					$avg_completion_time = '-';

					if ( ! empty( $completed_count ) ) {
						// echo $total_time . '<br>';

						$avg_completion_time = floatval( number_format( 100 * $total_time / $student_count, 2, '.', '' ) );
						$avg_completion_time = sprintf( '%02d:%02d:%02d', ( $avg_completion_time / 3600 ), ( $avg_completion_time / 60 % 60 ), $avg_completion_time % 60 );
					}

					$data_table[] = array(
						$course->post_title,
						sprintf( __( '%1$d of %2$d', 'ld-dashboard' ), $completed_count, $student_count ),
						$in_progress_count,
						$not_started_count,
						empty( $student_count ) ? '-' : floatval( number_format( 100 * $completed_count / $student_count, 2, '.', '' ) ) . '%',
						0 == $total_time ? '-' : sprintf( '%02d:%02d:%02d', ( $total_time / 3600 ), ( $total_time / 60 % 60 ), $total_time % 60 ),
						0 == $completed_user_time ? '-' : sprintf( '%02d:%02d:%02d', ( $completed_user_time / 3600 ), ( $completed_user_time / 60 % 60 ), $completed_user_time % 60 ),
					);

				} else {
					$data_table[] = array(
						$course->post_title,
						sprintf( __( '%1$d of %2$d', 'ld-dashboard' ), $completed_count, $student_count ),
						$in_progress_count,
						$not_started_count,
						empty( $student_count ) ? '-' : floatval( number_format( 100 * $completed_count / $student_count, 2, '.', '' ) ) . '%',
					);
				}
			}

			$table_column[] = array( 'title' => __( 'Course Name', 'ld-dashboard' ) );
			$table_column[] = array( 'title' => __( 'Completed Students', 'ld-dashboard' ) );
			$table_column[] = array( 'title' => __( 'In Progress', 'ld-dashboard' ) );
			$table_column[] = array( 'title' => __( 'Not Started', 'ld-dashboard' ) );
			$table_column[] = array( 'title' => __( 'Progress %', 'ld-dashboard' ) );
			if ( $this->is_enable_time_tracking ) {
				$table_column[] = array( 'title' => __( 'Total Time Spent', 'ld-dashboard' ) );
				$table_column[] = array( 'title' => __( 'Avg. Completion Time', 'ld-dashboard' ) );
			}
			// $table_column[] = [ 'title' => __('avg_time_spent', 'ld-dashboard')];
			// $table_column[] = [ 'title' => __('completed_count', 'ld-dashboard')];
			// $table_column[] = [ 'title' => __('student_count', 'ld-dashboard')];

			wp_send_json_success(
				array(
					'table'        => $table,
					'data_table'   => $data_table,
					'table_column' => $table_column,
					'row_name'     => sprintf( __( 'Course Reports - %s', 'ld-dashboard' ), get_bloginfo( 'name' ) ),
				),
			);

		}

		public function ld_dashboard_show_user_time_tracking( WP_User $user ) {
			if ( current_user_can( 'edit_users' ) ) {
				// Then is the user profile being viewed is not admin.

				$student_course_ids = learndash_user_get_enrolled_courses( $user->ID, array(), true );
				if ( empty( $student_course_ids ) ) {
					return;
				}
				?>
				<h3>
				<?php
				printf(
					// translators: placeholder: Courses.
					esc_html_x( 'Time Tracking Enrolled %s', 'Time Tracking Enrolled Courses', 'ld-dashboard' ),
					LearnDash_Custom_Label::get_label( 'courses' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
				);
				?>
				</h3>	
				<div class="ld-dashboard-course-time-tracking">
					<ul>
					<?php
					foreach ( $student_course_ids as $course_id ) {

						$time       = $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $user->ID );
						$total_time = number_format( $time, 2, '.', '' );
						$total_time = 0 == $total_time ? '00:00:00' : sprintf( '%02d:%02d:%02d', ( $total_time / 3600 ), ( $total_time / 60 % 60 ), $total_time % 60 );
						?>
						<li id="ld-dashboard-course-<?php echo $course_id; ?>">
							<label><a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a></lable>
							<span class="ld-dashboard-time-show"><?php echo esc_html( $total_time ); ?></span>							
							<span><a href="#" class="ld-dashboard-reset-time-tracking" data-course-id="<?php echo $course_id; ?>" data-user-id="<?php echo $user->ID; ?>"><?php esc_html_e( 'Reset time tracking', 'ld-dashboard' ); ?></a></span>
						</li>
						<?php
					}
					?>
					</ul>
				</div>
				<?php

			}
		}

		public function ld_dashboard_reset_user_time_tracking() {

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
				exit();
			}

			global $wpdb;
			$user_id    = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$course_id  = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
			$table_name = $wpdb->prefix . 'ld_dashboard_time_tracking';
			$updated    = $wpdb->update(
				$table_name,
				array(
					'time_spent' => 0,
				),
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
				),
				array(
					'%d',
				),
				array(
					'%d',
					'%d',
				)
			);
			if ( false === $updated ) {
				wp_send_json_error();
				die();
			}
			wp_send_json_success();
			die();
		}

		public function ld_dashboard_student_course_report() {
			global $wpdb;

			$course_id = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
			$user_id   = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$nonce     = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
				wp_send_json_error();
				die();
			}
			if ( $course_id != '' && $course_id != 0 && $user_id != '' && $user_id != 0 ) {
				$student                = get_userdata( $user_id );
				$student_name           = $student->first_name . ' ' . $student->last_name . '-' . get_the_title( $course_id );
				$status                 = array();
				$status['completed']    = __( 'Completed', 'ld-dashboard' );
				$status['notcompleted'] = __( 'Not Completed', 'ld-dashboard' );

				// Get Lessons
				$lessons_list       = learndash_get_course_lessons_list( $course_id, $user_id, array( 'per_page' => - 1 ) );
				$course_quiz_list   = array();
				$course_quiz_list[] = learndash_get_course_quiz_list( $course_id );

				$course_label = \LearnDash_Custom_Label::get_label( 'course' );

				$lessons      = array();
				$topics       = array();
				$temp_topics  = array();
				$lesson_names = array();
				$topic_names  = array();
				$quiz_names   = array();

				$lessons_column[] = array( 'title' => __( 'Lesson Name', 'ld-dashboard' ) );
				$lessons_column[] = array( 'title' => __( 'Status', 'ld-dashboard' ) );
				$lessons_column[] = array( 'title' => __( 'Time Spent', 'ld-dashboard' ) );

				$topics_column[] = array( 'title' => __( 'Topic Name', 'ld-dashboard' ) );
				$topics_column[] = array( 'title' => __( 'Status', 'ld-dashboard' ) );
				$topics_column[] = array( 'title' => __( 'Associated Lesson', 'ld-dashboard' ) );
				$topics_column[] = array( 'title' => __( 'Time Spent', 'ld-dashboard' ) );

				$quizzes_column[] = array( 'title' => __( 'Quiz Name', 'ld-dashboard' ) );
				$quizzes_column[] = array( 'title' => __( 'Score', 'ld-dashboard' ) );
				// $quizzes_column[]  = array( 'title' => __( 'Detailed Report', 'ld-dashboard' ) );
				$quizzes_column[] = array( 'title' => __( 'Date Completed', 'ld-dashboard' ) );
				// $quizzes_column[]  = array( 'title' => __( 'Certificate Link', 'ld-dashboard' ) );
				$quizzes_column[] = array( 'title' => __( 'Time Spent', 'ld-dashboard' ) );

				$lesson_order = 0;
				$topic_order  = 0;
				foreach ( $lessons_list as $lesson ) {
					$time                                = $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $user_id, $lesson['post']->ID );
					$lesson_names[ $lesson['post']->ID ] = $lesson['post']->post_title;
					/*
					$lessons[ $lesson_order ]            = array(
						'name'   => $lesson['post']->post_title,
						'status' => $status[ $lesson['status'] ],
						'time'   => 0 == $time ? '00:00:00' : sprintf( '%02d:%02d:%02d', ( $time / 3600 ), ( $time / 60 % 60 ), $time % 60 ),
					);
					*/
					$lessons[ $lesson_order ] = array(
						$lesson['post']->post_title,
						$status[ $lesson['status'] ],
						0 == $time ? '00:00:00' : sprintf( '%02d:%02d:%02d', ( $time / 3600 ), ( $time / 60 % 60 ), $time % 60 ),
					);

					$course_quiz_list[] = learndash_get_lesson_quiz_list( $lesson['post']->ID, $user_id, $course_id );
					$lesson_topics      = learndash_get_topic_list( $lesson['post']->ID, $course_id );

					foreach ( $lesson_topics as $topic ) {
						$time               = $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $user_id, $topic->ID );
						$course_quiz_list[] = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );

						$topic_progress = learndash_get_course_progress( $user_id, $topic->ID, $course_id );

						$topic_names[ $topic->ID ] = $topic->post_title;

						$temp_topics[ $topic_order ] = array(
							'name'              => $topic->post_title,
							'status'            => $status['notcompleted'],
							'associated_lesson' => $lesson['post']->post_title,
						);

						if ( ( isset( $topic_progress['posts'] ) ) && ( ! empty( $topic_progress['posts'] ) ) ) {
							foreach ( $topic_progress['posts'] as $topic_progress ) {

								if ( $topic->ID !== $topic_progress->ID ) {
									continue;
								}

								if ( 1 === $topic_progress->completed ) {
									$temp_topics[ $topic_order ]['status'] = $status['completed'];
								}
							}
						}
						$temp_topics[ $topic_order ]['time'] = 0 == $time ? '00:00:00' : sprintf( '%02d:%02d:%02d', ( $time / 3600 ), ( $time / 60 % 60 ), $time % 60 );
						$topics[ $topic_order ]              = array(
							$temp_topics[ $topic_order ]['name'],
							$temp_topics[ $topic_order ]['status'],
							$temp_topics[ $topic_order ]['associated_lesson'],
							$temp_topics[ $topic_order ]['time'],
						);

						$topic_order ++;
					}
					$lesson_order ++;
				}

				global $wpdb;

				// Assignments
				$assignments            = array();
				$sql_string             = "
				SELECT post.ID, post.post_title, post.post_date, postmeta.meta_key, postmeta.meta_value
				FROM $wpdb->posts post
				JOIN $wpdb->postmeta postmeta ON post.ID = postmeta.post_id
				WHERE post.post_status = 'publish' AND post.post_type = 'sfwd-assignment'
				AND post.post_author = $user_id
				AND ( postmeta.meta_key = 'approval_status' OR postmeta.meta_key = 'course_id' OR postmeta.meta_key LIKE 'ld_course_%' )";
				$assignment_data_object = $wpdb->get_results( $sql_string );

				foreach ( $assignment_data_object as $assignment ) {

					// Assignment List
					$data               = array();
					$data['ID']         = $assignment->ID;
					$data['post_title'] = $assignment->post_title;

					$assignment_id                                = (int) $assignment->ID;
					$rearranged_assignment_list[ $assignment_id ] = $data;

					// User Assignment Data
					$assignment_id = (int) $assignment->ID;
					$meta_key      = $assignment->meta_key;
					$meta_value    = (int) $assignment->meta_value;

					$date = learndash_adjust_date_time_display( strtotime( $assignment->post_date ) );

					$assignments[ $assignment_id ]['name']           = '<a target="_blank" href="' . get_edit_post_link( $assignment->ID ) . '">' . $assignment->post_title . '</a>';
					$assignments[ $assignment_id ]['completed_date'] = $date;
					$assignments[ $assignment_id ][ $meta_key ]      = $meta_value;

				}

				foreach ( $assignments as $assignment_id => &$assignment ) {
					if ( isset( $assignment['course_id'] ) && $course_id !== (int) $assignment['course_id'] ) {
						unset( $assignments[ $assignment_id ] );
					} else {
						if ( isset( $assignment['approval_status'] ) && 1 == $assignment['approval_status'] ) {
							$assignment['approval_status'] = __( 'Approved', 'ld-dashboard' );
						} else {
							$assignment['approval_status'] = __( 'Not Approved', 'ld-dashboard' );
						}
					}
				}

				// Quizzes Scores Avg
				global $wpdb;

				$q = "SELECT a.activity_id, a.course_id, a.post_id, a.activity_status, a.activity_completed, m.activity_meta_value as activity_percentage
					FROM {$wpdb->prefix}learndash_user_activity a
					LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
					WHERE a.user_id = {$user_id}
					AND a.course_id = {$course_id}
					AND a.activity_type = 'quiz'
					AND m.activity_meta_key = 'percentage'";

				$user_activities = $wpdb->get_results( $q );

				// Quizzes
				$quizzes = array();

				foreach ( $course_quiz_list as $module_quiz_list ) {
					if ( empty( $module_quiz_list ) ) {
						continue;
					}

					foreach ( $module_quiz_list as $quiz ) {

						if ( isset( $quiz['post'] ) ) {

							$quiz_names[ $quiz['post']->ID ] = $quiz['post']->post_title;
							$certificate_link                = '';
							$certificate                     = learndash_certificate_details( $quiz['post']->ID, $user_id );
							if ( ! empty( $certificate ) && isset( $certificate['certificateLink'] ) ) {
								$certificate_link = $certificate['certificateLink'];
							}

							foreach ( $user_activities as $activity ) {

								if ( $activity->post_id == $quiz['post']->ID ) {

									$pro_quiz_id = learndash_get_user_activity_meta( $activity->activity_id, 'pro_quizid', true );
									if ( empty( $pro_quiz_id ) ) {
										// LD is starting to deprecated pro quiz IDs from LD activity Tables. This is a back up if its not there
										$pro_quiz_id = absint( get_post_meta( $quiz['post']->ID, 'quiz_pro_id', true ) );
									}

									$statistic_ref_id = learndash_get_user_activity_meta( $activity->activity_id, 'statistic_ref_id', true );
									if ( empty( $statistic_ref_id ) ) {

										if ( class_exists( '\LDLMS_DB' ) ) {
											$pro_quiz_master_table   = \LDLMS_DB::get_table_name( 'quiz_master' );
											$pro_quiz_stat_ref_table = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
										} else {
											$pro_quiz_master_table   = $wpdb->prefix . 'wp_pro_quiz_master';
											$pro_quiz_stat_ref_table = $wpdb->prefix . 'wp_pro_quiz_statistic_ref';
										}

										// LD is starting to deprecated pro quiz IDs from LD activity Tables. This is a back up if its not there
										$sql_str = $wpdb->prepare(
											'SELECT statistic_ref_id FROM ' . $pro_quiz_stat_ref_table . ' as stat
											INNER JOIN ' . $pro_quiz_master_table . ' as master ON stat.quiz_id=master.id
											WHERE  user_id = %d AND quiz_id = %d AND create_time = %d AND master.statistics_on=1 LIMIT 1',
											$user_id,
											$pro_quiz_id,
											$activity->activity_completed
										);

										$statistic_ref_id = $wpdb->get_var( $sql_str );
									}

									$modal_link = '';

									if ( empty( $statistic_ref_id ) || empty( $pro_quiz_id ) ) {
										if ( ! empty( $statistic_ref_id ) ) {
											$modal_link = '<a class="user_statistic"
												 data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) . '"
												 data-user_id="' . $user_id . '"
												 data-quiz_id="' . $pro_quiz_id . '"
												 data-ref_id="' . intval( $statistic_ref_id ) . '"
												 data-uo-pro-quiz-id="' . intval( $pro_quiz_id ) . '"
												 data-uo-quiz-id="' . intval( $activity->post_id ) . '"
												 data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
												 href="#"> </a>';
										}
									} else {
										if ( ! empty( $statistic_ref_id ) ) {
											$modal_link  = '<a class="user_statistic"
												 data-statistic_nonce="' . wp_create_nonce( 'statistic_nonce_' . $statistic_ref_id . '_' . get_current_user_id() . '_' . $user_id ) . '"
												 data-user_id="' . $user_id . '"
												 data-quiz_id="' . $pro_quiz_id . '"
												 data-ref_id="' . intval( $statistic_ref_id ) . '"
												 data-uo-pro-quiz-id="' . intval( $pro_quiz_id ) . '"
												 data-uo-quiz-id="' . intval( $activity->post_id ) . '"
												 data-nonce="' . wp_create_nonce( 'wpProQuiz_nonce' ) . '"
												 href="#">';
											$modal_link .= '<div class="statistic_icon"></div>';
											$modal_link .= '</a>';
										}
									}
									$time = $this->fetch_ld_dashboard_course_wise_time_spent( $course_id, $user_id, $quiz['post']->ID );
									/*
									$quizzes[] 			= array(
																'name'             => $quiz['post']->post_title,
																'score'            => $activity->activity_percentage,
																'detailed_report'  => $modal_link,
																'completed_date'   => array(
																	'display'   => learndash_adjust_date_time_display( $activity->activity_completed ),
																	'timestamp' => $activity->activity_completed,
																),
																'certificate_link' => $certificate_link,
																'time' 			   => 0 == $time ? '00:00:00' : sprintf( '%02d:%02d:%02d', ( $time / 3600 ), ( $time / 60 % 60 ), $time % 60 )
															);
									*/

									$quizzes[] = array(
										$quiz['post']->post_title,
										$activity->activity_percentage,
										// $modal_link,
										learndash_adjust_date_time_display( $activity->activity_completed ),
										0 == $time ? '00:00:00' : sprintf( '%02d:%02d:%02d', ( $time / 3600 ), ( $time / 60 % 60 ), $time % 60 ),
									);

								}
							}
						}
					}
				}

				$progress = learndash_course_progress(
					array(
						'course_id' => $course_id,
						'user_id'   => $user_id,
						'array'     => true,
					)
				);

				$completed_date = '';

				if ( 100 <= $progress['percentage'] ) {
					$progress_percentage = $progress['percentage'];
					$completed_timestamp = learndash_user_get_course_completed_date( $user_id, $course_id );
					if ( absint( $completed_timestamp ) ) {
						$completed_date = learndash_adjust_date_time_display( learndash_user_get_course_completed_date( $user_id, $course_id ) );
						$status         = __( 'Completed', 'ld-dashboard' );
					} else {
						$status = __( 'In Progress', 'ld-dashboard' );
					}
				} else {
					$progress_percentage = absint( $progress['completed'] / $progress['total'] * 100 );
					$status              = __( 'In Progress', 'ld-dashboard' );
				}

				if ( 0 === $progress_percentage ) {
					$progress_percentage = '';
					$status              = __( 'Not Started', 'ld-dashboard' );
				} else {
					$progress_percentage = $progress_percentage . __( '%', 'ld-dashboard' );

				}

				// Column Quiz Average
				$course_quiz_average = $this->get_avergae_quiz_result( $course_id, $user_activities );

				$avg_score = '';

				if ( $course_quiz_average ) {
					/* Translators: 1. number percentage */
					$avg_score = sprintf( __( '%1$s%%', 'ld-dashboard' ), $course_quiz_average );
				}

				wp_send_json_success(
					array(
						'completed_date'      => $completed_date,
						'progress_percentage' => $progress_percentage,
						'avg_score'           => $avg_score,
						'status'              => $status,
						'lessons'             => $lessons,
						'lessons_column'      => $lessons_column,
						'topics'              => $topics,
						'topics_column'       => $topics_column,
						'quizzes'             => $quizzes,
						'quizzes_column'      => $quizzes_column,
						'assigments'          => $assignments,
						'studentName'         => $student_name,
						// 'course_certificate'  => learndash_get_course_certificate_link( $course_id, $user_id ),
					),
				);
			}
		}

		/**
		 * @param $course_id
		 * @param $user_activities
		 *
		 * @return false|int
		 */
		private static function get_avergae_quiz_result( $course_id, $user_activities ) {

			$quiz_scores = array();

			foreach ( $user_activities as $activity ) {

				if ( $course_id == $activity->course_id ) {

					if ( ! isset( $quiz_scores[ $activity->post_id ] ) ) {

						$quiz_scores[ $activity->post_id ] = $activity->activity_percentage;
					} elseif ( $quiz_scores[ $activity->post_id ] < $activity->activity_percentage ) {

						$quiz_scores[ $activity->post_id ] = $activity->activity_percentage;
					}
				}
			}

			if ( 0 !== count( $quiz_scores ) ) {
				$average = absint( array_sum( $quiz_scores ) / count( $quiz_scores ) );
			} else {
				$average = false;
			}

			return $average;
		}
	}

}
