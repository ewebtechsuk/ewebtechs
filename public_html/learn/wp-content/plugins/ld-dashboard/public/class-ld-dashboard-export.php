<?php

class Ld_Dashboard_Export {

	public function __construct() {

		add_action( 'wp_loaded', array( $this, 'ld_dashboard_export_course_progress' ), 0 );
		add_action( 'wp_loaded', array( $this, 'ld_dashboard_export_student_progress' ), 0 );
		add_action( 'wp_loaded', array( $this, 'ld_dashboard_export_instructor_earnings' ), 0 );
	}

	public function ld_dashboard_export_course_progress() {

		if ( isset( $_GET['ld-export'] ) && $_GET['ld-export'] == 'course-progress' && isset( $_GET['course-id'] ) && $_GET['course-id'] != '' ) {
			global $ld_plugin_public;

			$course_id = sanitize_text_field( $_GET['course-id'] );
			$user      = wp_get_current_user();

			/* Get Group Leader user ID only */
			$student_ids = array();
			if ( learndash_is_group_leader_user() ) {
				$student_ids  = learndash_get_group_leader_groups_users();
				$course_count = learndash_get_group_leader_groups_courses();
			}

			$course_access_users = get_users(
				array(
					'fields'  => array( 'ID', 'display_name' ),
					// 'meta_key'     => 'is_student',
					// 'meta_value' => true,
					'include' => $student_ids,
				)
			);
			if ( in_array( 'ld_instructor', (array) $user->roles ) ) {
				$course_access_users = $ld_plugin_public->ld_dashboard_get_instructor_students_by_id( $user->ID );
			}

			$course_userInfo = array();
			$uids            = array();
			$user_data       = array();

			ob_start();
			ob_end_clean();
			$course_name = get_the_title( $course_id );
			$filename    = sanitize_title( $course_name ) . '-student-progress.csv';
			$header_row  = array( 'Couse Name', 'User ID', 'UserName', 'User Email', 'Total Steps', 'Completed Steps', 'Progress', 'Completed On' );
			$fh          = @fopen( 'php://output', 'w' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/csv' );
			header( "Content-Disposition: attachment; filename={$filename}" );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			fputcsv( $fh, $header_row );

			if ( ! empty( $course_access_users ) ) {
				foreach ( $course_access_users as $uid ) {
					$course_ids = learndash_user_get_enrolled_courses( $uid->ID );
					if ( ! empty( $course_ids ) && in_array( $course_id, $course_ids ) ) {
						$course_userInfo[] = $ld_plugin_public->ld_dashboard_get_user_info( $uid->ID, $course_id );
					}
				}
			}

			$course_name = get_the_title( $course_id );
			$file        = sanitize_title( $course_name ) . '-student-progress.csv';

			// fputcsv( $fp, array( 'Couse Name', 'User ID', 'UserName', 'User Email', 'Total Steps', 'Completed Steps', 'Progress', 'Completed On' ) );
			if ( ! empty( $course_userInfo ) ) {
				foreach ( $course_userInfo as $user ) {
					$fields = array( $course_name, $user['userid'], $user['user_name'], $user['user_email'], $user['total_steps'], $user['completed_steps'], $user['completed_per'] . '%', $user['course_completed_on'] );
					fputcsv( $fh, $fields );
				}
			}
			fclose( $fh );
			die();

		}

	}

	public function ld_dashboard_export_student_progress() {
		if ( isset( $_GET['ld-export'] ) && $_GET['ld-export'] == 'student-progress' && isset( $_GET['student-id'] ) && $_GET['student-id'] != '' ) {
			global $ld_plugin_public, $current_user;
			$user_id    = get_current_user_id();
			$course_ids = array();
			$student_id = sanitize_text_field( $_GET['student-id'] );
			if ( learndash_is_group_leader_user() ) {
				$course_ids = learndash_get_group_leader_groups_courses();
			} elseif ( in_array( 'ld_instructor', (array) $current_user->roles ) ) {
				$args    = array(
					'post_type'      => 'sfwd-courses',
					'author'         => $user_id,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				);
				$courses = get_posts( $args );
				if ( ! empty( $courses ) ) {
					foreach ( $courses as $index => $course ) {
						$course_ids[] = $course->ID;
					}
				}
				$student_course_ids = learndash_user_get_enrolled_courses( $student_id );
				$course_ids         = array_intersect( $course_ids, $student_course_ids );

			} else {
				$course_ids = learndash_user_get_enrolled_courses( $student_id );
			}

			$student_courses               = $course_ids;
			$total_courses                 = count( $student_courses );
			$completed_course              = 0;
			$in_progress_course            = 0;
			$not_started_course            = 0;
			$completed_assignment          = 0;
			$total_assignment              = 0;
			$approved_assignment           = 0;
			$unapproved_assignment         = 0;
			$pending_assignment_percentage = 0;
			$completed_quizze              = 0;
			$total_quizze                  = 0;

			$student_info = get_userdata( $student_id );
			// $file         = $student_info->user_login . '-courses-progress.csv';
			$ld_dir_path = LD_DASHBOARD_PLUGIN_DIR . 'public/csv/'; // Change the path to fit your websites document structure.
			// $fp           = fopen( $ld_dir_path . $file, 'a' ) or die( "Error Couldn't open $file for writing!" );
			ob_start();
			ob_end_clean();
			$filename   = 'courses-progress.csv';
			$header_row = array( 'Couse Name', 'Total Steps', 'Completed Steps', 'Progress' );
			$fh         = @fopen( 'php://output', 'w' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/csv' );
			header( "Content-Disposition: attachment; filename={$filename}" );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			fputcsv( $fh, $header_row );
			if ( is_array( $student_courses ) && ! empty( $student_courses ) ) {
				foreach ( $student_courses as $course_id ) :

					$course_progress_data = $ld_plugin_public->ld_dashboard_check_course_progress_data( $student_id, $course_id );
					$course_progress      = ( isset( $course_progress_data['percentage'] ) ) ? $course_progress_data['percentage'] : 0;
					$total_steps          = ( isset( $course_progress_data['total_steps'] ) ) ? $course_progress_data['total_steps'] : 0;
					$completed_steps      = ( isset( $course_progress_data['completed_steps'] ) ) ? $course_progress_data['completed_steps'] : 0;

					$fields = array( get_the_title( $course_id ), $total_steps, $completed_steps, $course_progress );
					fputcsv( $fh, $fields );

				endforeach;
			}
			fclose( $fh );
			die();
		}
	}

	public function ld_dashboard_export_instructor_earnings() {
		if ( isset( $_GET['export-format'] ) && $_GET['export-format'] == 'csv' && isset( $_GET['ld-export'] ) && $_GET['ld-export'] == 'instructor-commission' ) {
			global $wpdb;

			$instructor_commission_table_name = $wpdb->prefix . 'ld_dashboard_instructor_commission_logs ';

			if ( is_admin() ) {
				$instructor_id = ( isset( $_GET['instructor_id'] ) && $_GET['instructor_id'] != '' ) ? $_GET['instructor_id'] : '';
			} else {
				$instructor_id = ( isset( $_GET['instructor_id'] ) && $_GET['instructor_id'] != '' ) ? $_GET['instructor_id'] : get_current_user_id();
			}
			$where_search = '';

			if ( $instructor_id != '' ) {
				$where_search .= " user_id = {$instructor_id} ";
			}
			if ( isset( $_GET['is_search'] ) && $_GET['is_search'] == 1 ) {

				if ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' && isset( $_GET['end-date'] ) && $_GET['end-date'] == '' ) {
					if ( $where_search != '' ) {
						$where_search .= ' AND ';
					}
					$where_search .= " Date(created) = '" . $_GET['start-date'] . "'";
				} elseif ( isset( $_GET['start-date'] ) && $_GET['start-date'] == '' && isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) {
					if ( $where_search != '' ) {
						$where_search .= ' AND ';
					}
					$where_search .= " Date(created) = '" . $_GET['end-date'] . "'";
				} elseif ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' && isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) {
					if ( $where_search != '' ) {
						$where_search .= ' AND ';
					}
					$where_search .= "  (Date(created) between '" . $_GET['start-date'] . "' AND '" . $_GET['end-date'] . "') ";
				}
				if ( $where_search != '' ) {
					$where_search = "Where {$where_search}";
				}
			}

			$query = $wpdb->prepare( 'SELECT * FROM ' . $instructor_commission_table_name . " {$where_search} order by ID DESC" );

			$course_purchase_data = $wpdb->get_results( $query, ARRAY_A );

			if ( $instructor_id != '' ) {
				$user            = get_user_by( 'id', $instructor_id );
				$instructor_name = $user->display_name;
			} else {
				$instructor_name = 'all-instructors-';
			}

			ob_start();
			ob_end_clean();

			$filename   = sanitize_title( $instructor_name ) . '-earning-logs.csv';
			$header_row = array( 'Couse Name', 'Course Price', 'Commission', 'Rate', 'Commission Type', 'Fee Type', 'Fee', 'Mode', 'reference', 'created' );
			$fh         = @fopen( 'php://output', 'w' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/csv' );
			header( "Content-Disposition: attachment; filename={$filename}" );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			fputcsv( $fh, $header_row );

			if ( ! empty( $course_purchase_data ) ) {
				foreach ( $course_purchase_data as $key => $value ) {
					$fields = array( get_the_title( $value['course_id'] ), $value['course_price'], $value['commission'], $value['commission_rate'], $value['commission_type'], $value['fees_type'], $value['fees_amount'], $value['source_type'], $value['reference'], $value['created'] );
					fputcsv( $fh, $fields );
				}
			}
			fclose( $fh );
			die();

		}
	}


}

$plugin_public = new Ld_Dashboard_Export();
