<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Ld_Dashboard_Essay_Report extends LD_Dashboard_Reports {


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
	 * Constructor of the class
	 *
	 * @since 5.9.9
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'ld_dashboard_eassay_api' ) );
	}

	/**
	 * @since 5.9.9
	 *
	 * @return Ld_Dashboard_Essay_Report
	 */
	public static function getInstance() {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}


	public function ld_dashboard_eassay_api() {
		register_rest_route(
			$this->root_path,
			'/ldd_get_essays_data/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ld_dashboard_get_essay_data' ),
				'permission_callback' => function () {
					return parent::ldd_permission_callback_check();
				},
			)
		);
	}


	public function ld_dashboard_get_essay_data() {
		// Takes raw data from the request
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = $request;

		// validate inputs
		$lesson_ID    = absint( $data['lessonId'] );
		$course_ID    = absint( $data['courseId'] );
		$group_ID     = absint( $data['groupId'] );
		$quiz_ID      = absint( $data['quizId'] );
		$status       = $data['status'];
		$essays_table = $this->ld_dashboard_essays_table( $lesson_ID, $course_ID, $group_ID, $quiz_ID, $status );

		$essays_table = apply_filters( 'ldd_rest_api_get_essays_data', $essays_table, $_POST );

		return $essays_table;
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
	public function ld_dashboard_essays_table( $lesson_ID = 0, $course_ID = 0, $group_ID = 0, $quiz_ID = 0, $status = 'ungraded' ) {

		$essays  = array();
		$user_id = get_current_user_id();

		$q_vars = array(
			'post_type'      => 'sfwd-essays',
			'posts_per_page' => - 1,
		);

		if ( $status === 'all' ) {
			$q_vars['post_status'] = array( 'graded', 'not_graded' );
		} elseif ( $status === 'ungraded' ) {
			$q_vars['post_status'] = array( 'not_graded' );
		} elseif ( $status === 'graded' ) {
			$q_vars['post_status'] = array( 'graded' );
		}

		if ( learndash_is_group_leader_user( $user_id ) || learndash_is_admin_user( $user_id ) || ld_dashboard_instructor_user( $user_id ) ) {
			$group_ids  = learndash_get_administrators_group_ids( $user_id, true );
			$course_ids = array();
			$lesson_ids = array();
			$user_ids   = array();

			if ( ! empty( $group_ids ) && is_array( $group_ids ) ) {
				if ( absint( $group_ID ) !== 0 && absint( $group_ID ) !== '' ) {
					foreach ( $group_ids as $group_id ) {
						if ( $group_ID === absint( $group_id ) ) {
							$group_course_ids = LDD_Learndash_Function_Overrides::learndash_group_enrolled_courses( $group_id );
							if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) {
								$course_ids = array_merge( $course_ids, $group_course_ids );
							}
							$lessons    = $this->ldd_get_object_lessons( $group_id );
							$lesson_ids = array_merge( $lesson_ids, $lessons['ldd_lesson_ids'] );

							$group_users = LDD_Learndash_Function_Overrides::learndash_get_groups_user_ids( $group_id );
							if ( ! empty( $group_users ) && is_array( $group_users ) ) {
								foreach ( $group_users as $group_user_id ) {
									$user_ids[ $group_user_id ] = $group_user_id;
								}
							}
						}
					}
				} else {
					foreach ( $group_ids as $group_id ) {
						$group_course_ids = LDD_Learndash_Function_Overrides::learndash_group_enrolled_courses( $group_id );
						if ( ! empty( $group_course_ids ) && is_array( $group_course_ids ) ) {
							$course_ids = array_merge( $course_ids, $group_course_ids );
						}
						$lessons    = $this->ldd_get_object_lessons( $group_id );
						$lesson_ids = array_merge( $lesson_ids, $lessons['ldd_lesson_ids'] );

						$group_users = LDD_Learndash_Function_Overrides::learndash_get_groups_user_ids( $group_id );
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

				// if ( empty( $user_ids ) ) {

				// }
			}

			if ( ! empty( $course_ids ) && count( $course_ids ) ) {
				if ( ! isset( $q_vars['meta_query'] ) ) {
					$q_vars['meta_query'] = array();
				}

				if ( $course_ID !== '' && $course_ID !== 0 && in_array( $course_ID, $course_ids ) ) {
					$course_ids = array( $course_ID );
				}

				if ( ! empty( $lesson_ids ) && count( $lesson_ids ) && $lesson_ID !== '' && $lesson_ID !== 0 ) {
					$q_vars['meta_query'][] = "'relation' => 'AND'";
					$lesson_ids             = array( $lesson_ID );
					$q_vars['meta_query'][] = array(
						'key'     => 'lesson_id',
						'value'   => $lesson_ids,
						'compare' => 'IN',
					);
				}

				if ( ! empty( $quiz_ID ) && $quiz_ID !== 0 ) {
					$q_vars['meta_query'][] = "'relation' => 'AND'";
					$quiz_IDs               = array( $quiz_ID );
					$q_vars['meta_query'][] = array(
						'key'     => 'quiz_post_id',
						'value'   => $quiz_IDs,
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

		$essay_posts = get_posts( $q_vars );

		if ( ! empty( $essay_posts ) ) {
			foreach ( $essay_posts as $essay ) {
				$essay_id               = $essay->ID;
				$status                 = '';
				$points                 = '';
				$course_id              = get_post_meta( $essay_id, 'course_id', true );
				$lesson_id              = get_post_meta( $essay_id, 'lesson_id', true );
				$quiz_id                = get_post_meta( $essay_id, 'quiz_id', true );
				$essay_quiz_post_id     = get_post_meta( $essay_id, 'quiz_post_id', true );
				$essay_question_post_id = get_post_meta( $essay_id, 'question_post_id', true );
				if ( empty( $essay_quiz_post_id ) ) {

					$essay_quiz_query_args = array(
						'post_type'    => 'sfwd-quiz',
						'post_status'  => 'publish',
						'meta_key'     => 'quiz_pro_id_' . intval( $quiz_id ),
						'meta_value'   => intval( $quiz_id ),
						'meta_compare' => '=',
						'fields'       => 'ids',
						'orderby'      => 'title',
						'order'        => 'ASC',
					);

					$essay_quiz_query   = new WP_Query( $essay_quiz_query_args );
					$essay_quiz_post_id = $essay_quiz_query->posts[0];

				}

				$question_id   = get_post_meta( $essay_id, 'question_id', true );
				$question_text = '';
				$max_points    = '';
				$essay_points  = 0;
				if ( ! empty( $quiz_id ) ) {
					$questionMapper = new \WpProQuiz_Model_QuestionMapper();
					$question       = $questionMapper->fetchById( intval( $question_id ), null );

					if ( $question instanceof \WpProQuiz_Model_Question ) {

						$submitted_essay_data = learndash_get_submitted_essay_data( $quiz_id, $question_id, $essay );
						$max_points           = 0 === $question->getPoints() ? get_post_meta( $essay_question_post_id, 'sfwd-question_points_cld', true ) : $question->getPoints();
						
						$question_text  = $question->getQuestion();
						$current_points = 0;
						if ( isset( $submitted_essay_data['points_awarded'] ) ) {
							$current_points = intval( $submitted_essay_data['points_awarded'] );
							$essay_points   = $current_points;
						}

						if ( $essay->post_status == 'not_graded' ) {
							$current_points = '<input id="essay_points_' . $essay_id . '" class="small-text" type="number" value="' . $current_points . '" max="' . $max_points . '" min="0" step="1" name="essay_points[' . $essay_id . ']" />';
							$points         = sprintf( _x( '%1$s / %2$d', 'placeholders: input points / maximum point for essay', 'ld-dashboard' ), $current_points, $max_points );
						} else {
							$points = sprintf( esc_html_x( '%1$d / %2$d', 'placeholders: current awarded points / maximum point for essay', 'ld-dashboard' ), $current_points, $max_points );
						}
					} else {
						$points = '-';
					}
				}
				$lesson = get_post( $lesson_id );
				$course = get_post( $course_id );
				$quiz   = get_post( $quiz_id );

				// $row_action                         = '<span class="edit"><a href="#" class="edit_essay_single" data-essay-id="' . $essay_id . '">Edit</a> | </span><span class="trash"><a href="#" id="a_essay_trash_' . $essay_id . '" class="delete_essay_single">Trash</a> | </span><span class="view"><a href="' . get_permalink( $essay_id ) . '" rel="bookmark" target="_blank">View</a></span>';
				// $row_action    = '<span class="trash"><a href="#" id="a_essay_trash_' . $essay_id . '" class="delete_essay_single">Trash</a></span>';
				$upload        = get_post_meta( $essay_id, 'upload', true );
				$essay_content = $essay->post_content;

				// if ( ! empty( $upload ) ) {
				// $row_action    .= ' | <a href="' . esc_url( $upload ) . '" target="_blank">' . esc_html__( 'Download', 'ld-dashboard' ) . '</a>';
				// $essay_content .= '<br/><a target="_blank" href="' . $upload . '">' . __( 'User Upload', 'ld-dashboard' ) . ' </a>';
				// }

				$post_status_object = get_post_status_object( $essay->post_status );
				if ( ( ! empty( $post_status_object ) ) && ( is_object( $post_status_object ) ) && ( property_exists( $post_status_object, 'label' ) ) ) {
					$status = $post_status_object->label;
				}

				if ( $essay->post_status == 'not_graded' ) {
					$status = '<button id="essay_approve_' . $essay_id . '" class="small essay_approve_single" data-id="' . esc_attr( $essay_id ) . '" data-max-points="' . esc_attr( $max_points ) . '" data-essay-points="' . esc_attr( $essay_points ) . '">' . esc_html__( 'approve', 'ld-dashboard' ) . '</button>';
				}

				$essays[] = array(
					'id'             => $essay_id,
					// 'title'          => '<a data-essay-id="' . $essay_id . '" class="edit_essay edit_essay_single">' . $essay->post_title . '</a><div class="row-actions">' . $row_action . '</div>',
					'title'          => '<div data-essay-id="' . $essay_id . '" class="edit_essay edit_essay_single">' . $essay->post_title . '</div>',
					'first_name'     => get_the_author_meta( 'first_name', $essay->post_author ),
					'last_name'      => get_the_author_meta( 'last_name', $essay->post_author ),
					'author'         => '<a href="mailto:' . get_the_author_meta( 'email', $essay->post_author ) . '" class="edit_essay">' . get_the_author_meta( 'login', $essay->post_author ) . '</a>',
					'status'         => $status,
					'points'         => $points,
					'action'         => '<a class="ldd-report-view-more dt-button button ld-dashboard-btn-bg" hrer="#" data-row-id="' . esc_attr( $essay_id ) . '">' . __( 'View More', 'ld-dashboard' ) . '</a>',
					'question_text'  => $question_text,
					'content'        => $essay_content,
					'assignedCourse' => ! empty( $course ) ? '<a href="' . get_permalink( $course ) . '">' . $course->post_title . '</a>' : '',
					'assignedlesson' => ! empty( $lesson ) ? '<a href="' . get_permalink( $lesson ) . '">' . $lesson->post_title . '</a>' : '',
					'assignedquiz'   => '<a href="' . get_permalink( $essay_quiz_post_id ) . '">' . get_the_title( $essay_quiz_post_id ) . '</a>',
					'comments'       => '<a target="_blank" href="' . get_permalink( $essay_id ) . '#comments">' . get_comments_number( $essay_id ) . '</a>',
					'date'           => '<span class="ulg-hidden-data" style="display: none">' . get_the_date( 'U', $essay ) . '</span>' . get_the_date( '', $essay ),
				);
			}
		}

		return $essays;

	}
}


return Ld_Dashboard_Essay_Report::getInstance();
