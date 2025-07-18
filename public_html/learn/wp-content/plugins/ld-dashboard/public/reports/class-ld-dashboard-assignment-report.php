<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Ld_Dashboard_Assignment_Report extends LD_Dashboard_Reports {


	/**
	 * Contain the instance of the plugin
	 *
	 * @since    5.9.9
	 * @access   private
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * @since 5.9.9
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'ld_dashboard_assignment_api' ), 20 );
	}

	/**
	 * @since 5.9.9
	 */
	public static function getInstance() {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}


	/**
	 * Register assignment data api
	 *
	 * @since 5.9.9
	 * @return void
	 */
	public function ld_dashboard_assignment_api() {
		register_rest_route(
			$this->root_path,
			'/ldd_get_assignment_data/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ld_dashboard_get_assignment_data' ),
				'permission_callback' => function () {
					return parent::ldd_permission_callback_check();
				},
			)
		);
	}




	public function ld_dashboard_get_assignment_data() {
		// Takes raw data from the request
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = $request;

		// validate inputs
		$lesson_ID        = absint( $data['lessonId'] );
		$course_ID        = absint( $data['courseId'] );
		$group_ID         = absint( $data['groupId'] );
		$status           = $data['status'];
		$assingment_table = $this->ld_dashboard_assingment_table( $lesson_ID, $course_ID, $group_ID, $status );

		$assingment_table = apply_filters( 'ldd_rest_api_get_essays_data', $assingment_table, $_POST );

		return $assingment_table;
	}

	/**
	 * Return html for the essay table
	 *
	 * @param $lesson_ID
	 * @param $course_ID
	 * @param $group_ID
	 *
	 * @return array
	 */
	public function ld_dashboard_assingment_table( $lesson_ID = 0, $course_ID = 0, $group_ID = 0, $_status = 'approved' ) {

		$assignments = array();
		$user_id     = get_current_user_id();

		$q_vars = array(
			'post_type'      => 'sfwd-assignment',
			'posts_per_page' => -1,
		);

		if ( learndash_is_group_leader_user( $user_id ) || learndash_is_admin_user( $user_id ) || ld_dashboard_instructor_user( $user_id ) ) {
			$group_ids  = learndash_get_administrators_group_ids( $user_id, true );
			$course_ids = array();
			$lesson_ids = array();
			$user_ids   = array();

			if ( ! empty( $group_ids ) && is_array( $group_ids ) ) {
				if ( absint( $group_ID ) != 0 ) {
					foreach ( $group_ids as $group_id ) {
						if ( $group_ID === absint( $group_id ) ) {
							$group_course_ids = learndash_group_enrolled_courses( $group_id, true );
							if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) {
								$course_ids = array_merge( $course_ids, $group_course_ids );
							}
							$lessons    = $this->ldd_get_object_lessons( $group_id );
							$lesson_ids = array_merge( $lesson_ids, $lessons['ldd_lesson_ids'] );

							$group_users = learndash_get_groups_user_ids( $group_id, true );
							if ( ! empty( $group_users ) && is_array( $group_users ) ) {
								foreach ( $group_users as $group_user_id ) {
									$user_ids[ $group_user_id ] = $group_user_id;
								}
							}
						}
					}
				} else {
					foreach ( $group_ids as $group_id ) {
						$group_course_ids = learndash_group_enrolled_courses( $group_id, true );
						if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) {
							$course_ids = array_merge( $course_ids, $group_course_ids );
						}
						$lessons    = $this->ldd_get_object_lessons( $group_id );
						$lesson_ids = array_merge( $lesson_ids, $lessons['ldd_lesson_ids'] );

						$group_users = learndash_get_groups_user_ids( $group_id, true );
						if ( ! empty( $group_users ) && is_array( $group_users ) ) {
							foreach ( $group_users as $group_user_id ) {
								$user_ids[ $group_user_id ] = $group_user_id;
							}
						}
					}
				}
			}

			if ( empty( $course_ids ) ) {
				$course_ids = ld_dasboard_get_user_course_ids( $user_id, true );

				if ( empty( $lesson_ids ) ) {
					foreach ( $course_ids as $course_id ) {
						$lesson_ids = learndash_course_get_children_of_step( $course_id, $course_id, 'sfwd-lessons' );
					}
				}
			}

			if ( ! empty( $course_ids ) && count( $course_ids ) ) {
				if ( ! isset( $q_vars['meta_query'] ) ) {
					$q_vars['meta_query'] = array();
				}

				if ( $course_ID !== 0 && in_array( $course_ID, $course_ids ) ) {
					$course_ids = array( $course_ID );
				}

				if ( ! empty( $lesson_ids ) && count( $lesson_ids ) && $lesson_ID !== 0 && in_array( $lesson_ID, $lesson_ids ) ) {
					$q_vars['meta_query'][] = "'relation' => 'AND'";
					$lesson_ids             = array( $lesson_ID );
					$q_vars['meta_query'][] = array(
						'key'     => 'lesson_id',
						'value'   => $lesson_ids,
						'compare' => 'IN',
					);
				}

				$q_vars['meta_query'][] = array(
					'key'     => 'course_id',
					'value'   => $course_ids,
					'compare' => 'IN',
				);

			}

			if ( ! empty( $user_ids ) && count( $user_ids ) ) {
				$q_vars['author__in'] = $user_ids;
			}
		}

		$assignment_posts = get_posts( $q_vars );

		if ( ! empty( $assignment_posts ) ) {
			foreach ( $assignment_posts as $a_post ) {
				$assignment_id = $a_post->ID;
				$status        = '';

				$assignment_lesson_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
				$assignment_course_id = intval( get_post_meta( $assignment_id, 'course_id', true ) );
				if ( ! empty( $assignment_lesson_id ) ) {
					$approval_status_flag = learndash_is_assignment_approved_by_meta( $assignment_id );
					if ( $approval_status_flag == 1 ) {
						$approval_status_label = esc_html__( 'Approved', 'ld-dashboard' );
					} else {
						$approval_status_flag  = 0;
						$approval_status_label = esc_html__( 'Not Approved', 'ld-dashboard' );
					}

					$approval_status_url = admin_url( 'edit.php?post_type=sfwd-assignment&approval_status=' . $approval_status_flag );

					$status = $approval_status_label;
					if ( $approval_status_flag != 1 ) {
						$status .= '<button id="assignment_approve_' . $assignment_id . '" class="small assignment_approve_single ld-dashboard-approve-assignment-btn" data-id="' . $assignment_id . '">' . esc_html__( 'Approve', 'ld-dashboard' ) . '</button>';
					}
				}

				if ( 'not-approved' === $_status ) {
					if ( 1 === absint( $approval_status_flag ) ) {
						continue;
					}
				}

				if ( 'approved' === $_status ) {
					if ( 0 === absint( $approval_status_flag ) ) {
						continue;
					}
				}

				if ( learndash_assignment_is_points_enabled( $assignment_id ) ) {
					$max_points = 0;

					$assignment_settings_id = intval( get_post_meta( $assignment_id, 'lesson_id', true ) );
					if ( ! empty( $assignment_settings_id ) ) {
						$max_points = learndash_get_setting( $assignment_settings_id, 'lesson_assignment_points_amount' );
					}

					$current_points = get_post_meta( $assignment_id, 'points', true );
					if ( ( $current_points == 'pending' ) || ( $current_points == '' ) ) {
						$approval_status_flag = learndash_is_assignment_approved_by_meta( $assignment_id );
						if ( $approval_status_flag != 1 ) {
							$current_points = '<input id="assignment_points_' . $assignment_id . '" class="small-text" type="number" value="0" max="' . $max_points . '" min="0" step="1" name="assignment_points[' . $assignment_id . ']"  data-point="enabled"/>';
						} else {
							$current_points = '0';
						}
					}
					$points = sprintf( esc_html_x( '%1$s / %2$s', 'placeholders: current points / maximum point for assignment', 'ld-dashboard' ), $current_points, $max_points );

				} else {
					$points = esc_html__( 'Not Enabled', 'ld-dashboard' );
					$points .= '<input id="assignment_points_'. $assignment_id .'" class="small-text" type="hidden" data-point="not-enabled">';
				}

				$lesson        = get_post( $assignment_lesson_id );
				$course        = get_post( $assignment_course_id );
				$row_action    = '<span class="trash"><a href="#" id="a_assignment_trash_' . $assignment_id . '" class="delete_assignment_single">' . __( 'Trash', 'ld-dashboard' ) . '</a> </span>';
				$download_link = get_post_meta( $assignment_id, 'file_link', true );
				if ( ! empty( $download_link ) ) {
					$row_action .= " | <a href='" . $download_link . "' target='_blank'>" . esc_html__( 'Download', 'ld-dashboard' ) . '</a>';
				}

				$file_link = get_post_meta( $a_post->ID, 'file_link', true );

				$assignments[] = array(
					'id'             => $assignment_id,
					'title'          => '<a href="' . esc_url( $file_link ) . '" title="' . $a_post->post_title . '" data-assignment-id="' . $a_post->ID . '" class="edit_assignment_single" download>' . $a_post->post_title . '</a>',
					'first_name'     => get_the_author_meta( 'first_name', $a_post->post_author ),
					'last_name'      => get_the_author_meta( 'last_name', $a_post->post_author ),
					'author'         => '<a href="mailto:' . get_the_author_meta( 'email', $a_post->post_author ) . '" class="edit_assignment">' . get_the_author_meta( 'login', $a_post->post_author ) . '</a>',
					'status'         => $status,
					'points'         => $points,
					'action'         => '<a class="ldd-report-view-more dt-button button ld-dashboard-btn-bg" hrer="#" data-row-id="' . esc_attr( $assignment_id ) . '">' . __( 'View More', 'ld-dashboard' ) . '</a>',
					'assignedCourse' => '<a href="' . get_permalink( $course ) . '">' . $course->post_title . '</a>',
					'assignedlesson' => '<a href="' . get_permalink( $lesson ) . '">' . $lesson->post_title . '</a>',
					'comments'       => '<a target="_blank" href="' . get_permalink( $assignment_id ) . '#comments">' . get_comments_number( $assignment_id ) . '</a>',
					'date'           => get_the_date( '', $a_post ),
					// '$approval_status_flag' => $approval_status_flag,
					// '$_status'              => $_status,
				);
			}
		}

		return $assignments;

	}
}


return Ld_Dashboard_Assignment_Report::getInstance();
