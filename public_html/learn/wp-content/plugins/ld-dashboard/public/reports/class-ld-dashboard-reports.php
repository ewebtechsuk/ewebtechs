<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( __( 'Access Denied', 'ld-dashboard' ) );
}
class LD_Dashboard_Reports {

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
	 * plugin name
	 *
	 * @since    5.9.9
	 * @access   private
	 *
	 * @var string
	 */
	private $plugin_name = 'ld-dashboard';

	/**
	 * Rest API root path
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	protected $root_path = 'ldd_report/v1';


	/**
	 * Group drop down
	 *
	 * @since    5.9.9
	 * @access   public
	 * @var      string
	 */
	public static $ld_dashboard_drop_downs = false;


	public function __construct() {
		$this->ld_dashboard_include_reports();
		add_filter( 'ld_dashboard_get_dashboard_user_roles', array( $this, 'ld_dashboard_add_dashboard_user_roles' ) );

		if ( self::ld_dasboard_is_report_page() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9999 );
		}
	}


	public static function getInstance() {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}


	public function ld_dashboard_include_reports() {
		require_once 'class-ld-dashboard-eassay-report.php';
		require_once 'class-ld-dashboard-assignment-report.php';
		require_once 'class-ld-dashboard-quiz-report.php';
	}

	public function enqueue_scripts() {

		wp_enqueue_style( 'ld-dashboard-reports' );
		wp_enqueue_style( 'dashboard-datatable-style' );
		wp_enqueue_script( 'dashboard-datatable-script' );
		wp_enqueue_script( 'ldd-sweetalert' );

		$ldd_drop_downs    = $this->ld_dasboard_report_dropdown( get_current_user_id() );
		$localized_strings = $this->ldd_get_frontend_localized_strings();
		wp_enqueue_script( $this->plugin_name . '-report' );
		wp_localize_script(
			$this->plugin_name . '-report',
			'lddRepots',
			array(
				'root'               => esc_url_raw( rest_url() . $this->root_path . '/' ),
				'nonce'              => wp_create_nonce( 'wp_rest' ),
				'ajax_nonce'         => wp_create_nonce( 'ld-dashboard-nonce' ),
				'ajaxurl'            => admin_url( 'admin-ajax.php' ),
				'currentUser'        => get_current_user_id(),
				'localized'          => $localized_strings,
				'currentUser'        => get_current_user_id(),
				'ldd_url'            => LD_DASHBOARD_PLUGIN_URL,
				'relationships'      => isset( $ldd_drop_downs['relationships'] ) ? $ldd_drop_downs['relationships'] : array(),
				'quiz_relationships' => isset( $ldd_drop_downs['quiz_relationships'] ) ? $ldd_drop_downs['quiz_relationships'] : array(),
				'ld_dashboard_page'  => isset( $_GET ) && ! empty( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : '',
			)
		);

		$table_responsive  = '#ld-dashboard-report td:nth-of-type(1):after { content: "' . $localized_strings['idColumn'] . '";}';
		$table_responsive .= '#ld-dashboard-report td:nth-of-type(2):after { content: "' . $localized_strings['title'] . '"; }';
		$table_responsive .= '#ld-dashboard-report td:nth-of-type(3):after { content: "' . $localized_strings['author'] . '"; }';
		$table_responsive .= '#ld-dashboard-report td:nth-of-type(4):after { content: "' . $localized_strings['status'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="assignment-report"] td:nth-of-type(1):after { content: "' . $localized_strings['title'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="assignment-report"] td:nth-of-type(2):after { content: "' . $localized_strings['author'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="assignment-report"] td:nth-of-type(3):after { content: "' . $localized_strings['action'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="assignment-report"] td:nth-of-type(4):after { content: "' . $localized_strings['points'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="quizz-report"] td:nth-of-type(1):after { content: "' . $localized_strings['author'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="quizz-report"] td:nth-of-type(2):after { content: "' . $localized_strings['email'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="quizz-report"] td:nth-of-type(3):after { content: "' . $localized_strings['quiz_score'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="quizz-report"] td:nth-of-type(4):after {content: "' . $localized_strings['action'] . '"; }';
		$table_responsive .= '#ld-dashboard-report-table[data-table="quizz-report"] td:nth-of-type(5):after { content: "' . $localized_strings['date'] . '"; }';

		wp_add_inline_style( 'dashboard-datatable-style', $table_responsive );

	}

	public function ld_dashboard_add_dashboard_user_roles( $roles ) {
		$roles['administrator'] = 'Administrator';
		return $roles;
	}


	/**
	 * @return mixed|void
	 */
	private function ldd_get_frontend_localized_strings() {

		$localized_strings = array();

		$localized_strings['email'] = __( 'Email', 'ld-dashboard' );

		$localized_strings['title'] = __( 'Title', 'ld-dashboard' );

		$localized_strings['idColumn'] = __( 'ID', 'ld-dashboard' );

		$localized_strings['first_name'] = __( 'First name', 'ld-dashboard' );

		$localized_strings['last_name'] = __( 'Last name', 'ld-dashboard' );

		$localized_strings['author'] = __( 'Username', 'ld-dashboard' );

		$localized_strings['status'] = __( 'Status', 'ld-dashboard' );

		$localized_strings['customizeColumns'] = __( 'Customize columns', 'ld-dashboard' );

		$localized_strings['hideCustomizeColumns'] = __( 'Hide customize columns', 'ld-dashboard' );

		$localized_strings['points'] = __( 'Points', 'ld-dashboard' );

		$localized_strings['assignedCourse'] = LearnDash_Custom_Label::get_label( 'course' );

		$localized_strings['assignedlesson'] = LearnDash_Custom_Label::get_label( 'lesson' );

		$localized_strings['assignedquiz'] = LearnDash_Custom_Label::get_label( 'quiz' );

		$localized_strings['quiz_score'] = sprintf( __( '%s Score', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		$localized_strings['comments'] = __( 'Comments', 'ld-dashboard' );

		$localized_strings['question_text'] = LearnDash_Custom_Label::get_label( 'question' );

		$localized_strings['content'] = __( 'Content', 'ld-dashboard' );

		$localized_strings['date'] = __( 'Date', 'ld-dashboard' );

		$localized_strings['csvExport'] = __( 'CSV export', 'ld-dashboard' );

		$localized_strings['selectCourse'] = sprintf( __( 'Select %s', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'course' ) );

		$localized_strings['noCourse'] = sprintf( __( 'No %s available', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) );

		$localized_strings['selectUser'] = __( 'Select user', 'ld-dashboard' );

		$localized_strings['noUsers'] = __( 'No users available', 'ld-dashboard' );

		$localized_strings['all'] = __( 'All', 'ld-dashboard' );

		$localized_strings['selectLesson'] = sprintf( __( 'Select %s', 'ld-dashboard' ), \LearnDash_Custom_Label::get_label( 'lesson' ) );

		$localized_strings['noGroupsFound']     = sprintf( __( 'No %s found', 'ld-dashboard' ), \LearnDash_Custom_Label::get_label( 'groups' ) );
		$localized_strings['noCoursesFound']    = sprintf( __( 'No %s found', 'ld-dashboard' ), \LearnDash_Custom_Label::get_label( 'courses' ) );
		$localized_strings['noLessonsFound']    = sprintf( __( 'No %s found', 'ld-dashboard' ), \LearnDash_Custom_Label::get_label( 'lessons' ) );
		$localized_strings['noQuizzesFound']    = sprintf( __( 'No %s found', 'ld-dashboard' ), \LearnDash_Custom_Label::get_label( 'quizzes' ) );
		$localized_strings['searchPlaceholder'] = __( 'Search by name, username, status or points', 'ld-dashboard' );
		$localized_strings['action']            = __( 'Action', 'ld-dashboard' );

		$localized_strings = apply_filters( 'ldd_essay_report_table_strings', $localized_strings );

		return $localized_strings;
	}


	/**
	 * Check permission of a current logged in user for rest_api call
	 *
	 * @param bool $admin_only
	 *
	 * @since 5.9.9
	 * @return bool|WP_Error
	 */
	public static function ldd_permission_callback_check( $admin_only = false ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'ldd_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'ld-dashboard' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( $admin_only ) {
			return current_user_can( 'manage_options' );
		}

		$user          = wp_get_current_user();
		$allowed_roles = array_keys( ld_dashboard_get_dashboard_user_roles() );

		if ( array_intersect( $allowed_roles, $user->roles ) ) {
			return true;
		}

		return new WP_Error( 'ldd_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'ld-dashboard' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * Get all drop downs for eassay report
	 *
	 * @param integer $user_id
	 * @return bool|string|void
	 */
	public function ld_dasboard_report_dropdown( $user_id = 0 ) {

		if ( false !== self::$ld_dashboard_drop_downs ) {
			return self::$ld_dashboard_drop_downs;
		}

		if ( ! user_can( $user_id, 'group_leader' ) && ! user_can( $user_id, 'manage_options' ) && ! user_can( $user_id, 'ld_instructor' ) ) {
			return false;
		}

		$user_groups = learndash_get_administrators_group_ids( $user_id, true );
		$post_type   = '';
		$drop_down   = array();
		$course_ids  = array();
		$lesson_ids  = array();
		$quiz_ids    = array();
		if ( ! empty( $user_groups ) ) {
			$post_type           = 'groups';
			$posts_in            = array_map( 'intval', $user_groups );
			$drop_down['groups'] = '<option value="0">' . __( 'Select group', 'ld-dashboard' ) . '</option><option value="" class="ldd-reports-no-results" style="display:none">' . sprintf( __( 'No %s found', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'groups' ) ) . '</option>';
		} else {
			$user_courses         = ldd_get_user_courses_list( $user_id, true );
			$post_type            = 'sfwd-courses';
			$posts_in             = array_map( 'intval', $user_courses );
			$drop_down['courses'] = '<option value="0">' . sprintf( __( 'Select %s', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'course' ) ) . '</option><option value="" class="ldd-reports-no-results" style="display:none">' . sprintf( __( 'No %s found', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ) . '</option>';
		}

		if ( ! empty( $posts_in ) ) {
			$args = array(
				'post_type'      => $post_type,
				'post__in'       => $posts_in,
				'posts_per_page' => 9999,
				'orderby'        => 'title',
				'order'          => 'ASC',
			);

			$args         = apply_filters( 'ld_dashboard_group_dropdown', $args, $user_id, $posts_in );
			$post_objects = get_posts( $args );

			if ( $post_objects ) {
				foreach ( $post_objects as $post_object ) {
					$post_id = $post_object->ID;

					if ( 'groups' === $post_object->post_type ) {
						$drop_down['groups'] .= '<option value="' . $post_id . '">' . $post_object->post_title . '</option>';
						$lessons              = $this->ldd_get_object_lessons( $post_id );
						$quizzes              = $this->ldd_get_object_quizzes( $post_id );
						$course_ids           = array_merge( $course_ids, $quizzes['ldd_course_quizzes'] );
						$course_ids           = array_merge( $course_ids, $lessons['ldd_course_lessons'] );
						$course_ids           = array_unique( $course_ids );
						$courses              = $this->get_objects( $course_ids, 'sfwd-courses', 'title', 'ASC' );

						if ( ! empty( $courses ) ) {
							$drop_down['courses_class'] = '';
							$drop_down['courses']       = '<option value="0">' . sprintf( __( 'Select %s', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'course' ) ) . '</option><option value="" class="ldd-reports-no-results" style="display:none">' . sprintf( __( 'No %s found', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ) . '</option>';
							// $drop_down['courses'] = '';
							foreach ( $courses as $course ) {
								$drop_down['courses'] .= '<option value="' . $course->ID . '">' . $course->post_title . '</option>';

							}
						} else {
							$drop_down['courses_class'] = 'select-h3';
							$drop_down['courses']       = '<option value="0">' . sprintf( __( 'No %s in group', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ) . '</option>';
						}
					} else {
						$drop_down['courses'] .= '<option value="' . $post_id . '">' . $post_object->post_title . '</option>';
						$lessons               = $this->ldd_get_object_lessons( $post_id );
						$quizzes               = $this->ldd_get_object_quizzes( $post_id );
					}

					// Get Lesson/topics
					$drop_down['lessons_objects'][ $post_id ] = $lessons['ldd_lesson_ids'];
					$lesson_ids                               = array_merge( $lesson_ids, $lessons['ldd_lesson_ids'] );
					$drop_down['relationships'][ $post_id ]   = $lessons['relationships'];

					$drop_down['quizzes_objects'][ $post_id ]        = $quizzes['ldd_quiz_ids'];
					$drop_down['course_quizzes_objects'][ $post_id ] = $quizzes['ldd_course_quizzes'];
					$quiz_ids                                        = array_merge( $quiz_ids, $quizzes['ldd_quiz_ids'] );
					$drop_down['quiz_relationships'][ $post_id ]     = $quizzes['relationships'];
				}
			}

			// Get lessons
			$relations = array();

			if ( ! empty( $drop_down['relationships'] ) ) {
				// below line commented BY_AC
				$drop_down['lessons'] = '<option value="" class="ldd-reports-no-results" style="display:none">' . sprintf( __( 'No %s found', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lessons' ) ) . '</option>';
				$unique_lesson        = array();
				foreach ( $drop_down['relationships'] as $group_id => $groups_courses ) {
					if ( ! empty( $groups_courses ) ) {
						foreach ( $groups_courses as $course_id => $course_lesson ) {
							if ( ! empty( $course_lesson ) ) {
								foreach ( $course_lesson as $lesson_id => $lesson ) {
									$relations[ $group_id ][ $course_id ][] = $lesson_id;
									if ( ! in_array( $lesson_id, $unique_lesson ) ) {
										$unique_lesson[]       = $lesson_id;
										$drop_down['lessons'] .= '<option value="' . $lesson_id . '">' . $lesson . '</option>';
									}
								}
							}
						}
					}
				}

				if ( empty( $drop_down['lessons'] ) ) {
					$drop_down['lessons_class'] = 'select-h3';
					$drop_down['lessons']       = '<option value="0">' . sprintf( __( 'No %s in group', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lessons' ) ) . '</option>';
				}
			} else {
				$drop_down['lessons_class'] = 'select-h3';
				$drop_down['lessons']       = '<option value="0">' . sprintf( __( 'No %s in group', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lessons' ) ) . '</option>';
			}
			$drop_down['relationships'] = $relations;
			// Get lessons
			$relations = array();
			if ( ! empty( $drop_down['quiz_relationships'] ) ) {
				// below line commented BY_AC

				$drop_down['quizzes'] = '<option value="" class="ldd-reports-no-results" style="display:none">' . sprintf( __( 'No %s found', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ) . '</option>';
				$unique_quiz          = array();
				foreach ( $drop_down['quiz_relationships'] as $group_id => $groups_courses ) {
					if ( ! empty( $groups_courses ) ) {
						foreach ( $groups_courses as $course_id => $course_quiz ) {
							if ( ! empty( $course_quiz ) ) {
								foreach ( $course_quiz as $quiz_id => $quiz ) {
									$relations[ $group_id ][ $course_id ][] = $quiz_id;
									if ( ! in_array( $quiz_id, $unique_quiz ) ) {
										$unique_quiz[]         = $quiz_id;
										$drop_down['quizzes'] .= '<option value="' . $quiz_id . '" style="">' . $quiz . '</option>';
									}
								}
							}
						}
					}
				}
				if ( empty( $drop_down['lessons'] ) ) {
					$drop_down['quizzes_class'] = 'select-h3';
					$drop_down['quizzes']       = '<option value="0">' . sprintf( __( 'No %s in group', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ) . '</option>';
				}
			} else {
				$drop_down['quizzes_class'] = 'select-h3';
				$drop_down['quizzes']       = '<option value="0">' . sprintf( __( 'No %s in group', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ) . '</option>';
			}
			$drop_down['quiz_relationships'] = $relations;
		}

		// Cache results so we don't re-query
		self::$ld_dashboard_drop_downs = $drop_down;

		return $drop_down;

	}



	/**
	 * Get groups course lessons
	 *
	 * @param int $group_id
	 *
	 * @return mixed
	 */
	public function ldd_get_object_lessons( $post_id = 0 ) {
		$ldd_lesson_ids   = array();
		$group_course_ids = array();
		$include_topics   = true;

		$relationships = array();
		if ( ! empty( $post_id ) ) {
			$group_course_ids = LDD_Learndash_Function_Overrides::learndash_group_enrolled_courses( intval( $post_id ) );

			if ( ! empty( $group_course_ids ) ) {
				foreach ( $group_course_ids as $course_id ) {

					if ( ! isset( $relationships[ $post_id ][ $course_id ] ) ) {
						$relationships[ $course_id ][0] = sprintf( _x( 'Select %1$s / %2$s', 'LearnDash lesson and topic labels', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lesson' ), LearnDash_Custom_Label::get_label( 'topic' ) );
					}

					$lesson_ids = learndash_course_get_children_of_step( $course_id, $course_id, 'sfwd-lessons' );

					if ( ! empty( $lesson_ids ) ) {
						foreach ( $lesson_ids as $lesson_id ) {
							$ldd_lesson_ids[] = $lesson_id;

							if ( ! isset( $relationships[ $course_id ][ $lesson_id ] ) ) {
								$relationships[ $course_id ][ $lesson_id ] = get_the_title( $lesson_id );
							}

							if ( $include_topics ) {
								$topic_ids = learndash_course_get_children_of_step( $course_id, $lesson_id, 'sfwd-topic' );
								if ( ! empty( $topic_ids ) ) {
									foreach ( $topic_ids as $topic_id ) {
										$ldd_lesson_ids[] = $topic_id;
										if ( ! isset( $relationships[ $course_id ][ $topic_id ] ) ) {
											$relationships[ $course_id ][ $topic_id ] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_the_title( $topic_id );
										}
									}
								}
							}
						}

						$ldd_lesson_ids = array_unique( $ldd_lesson_ids );
					} else {
						$relationships[ $course_id ] = array();
					}
				}
			} else {
				$lesson_ids = learndash_course_get_children_of_step( $post_id, $post_id, 'sfwd-lessons' );

				if ( ! empty( $lesson_ids ) ) {
					foreach ( $lesson_ids as $lesson_id ) {
						$ldd_lesson_ids[] = $lesson_id;

						if ( ! isset( $relationships[ $post_id ][ $lesson_id ] ) ) {
							$relationships[ $post_id ][ $lesson_id ] = get_the_title( $lesson_id );
						}

						if ( $include_topics ) {
							$topic_ids = learndash_course_get_children_of_step( $post_id, $lesson_id, 'sfwd-topic' );
							if ( ! empty( $topic_ids ) ) {
								foreach ( $topic_ids as $topic_id ) {
									$ldd_lesson_ids[] = $topic_id;
									if ( ! isset( $relationships[ $post_id ][ $topic_id ] ) ) {
										$relationships[ $post_id ][ $topic_id ] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_the_title( $topic_id );
									}
								}
							}
						}
					}

					$ldd_lesson_ids = array_unique( $ldd_lesson_ids );
				} else {
					$relationships[ $post_id ] = array();
				}
			}
		}

		$data = array(
			'ldd_lesson_ids'     => $ldd_lesson_ids,
			'ldd_course_lessons' => $group_course_ids,
			'relationships'      => $relationships,
		);

		return $data;
	}

	/**
	 * Get groups course quizzes
	 *
	 * @param int $group_id
	 *
	 * @return mixed
	 */
	public function ldd_get_object_quizzes( $post_id = 0 ) {
		$group_quiz_ids   = array();
		$group_course_ids = array();
		$include_topics   = true;
		$relation         = array();

		$relationships = array();
		if ( ! empty( $post_id ) ) {

			$group_course_ids = LDD_Learndash_Function_Overrides::learndash_group_enrolled_courses( intval( $post_id ) );

			if ( ! empty( $group_course_ids ) ) {
				foreach ( $group_course_ids as $course_id ) {

					if ( ! isset( $relation[ $course_id ] ) ) {
						$relation[ $course_id ]         = array();
						$relationships[ $course_id ][0] = sprintf( __( 'Select %s', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quiz' ) );
					}

					$lesson_ids = learndash_course_get_children_of_step( $course_id, $course_id, 'sfwd-lessons' );
					$quiz_ids   = learndash_course_get_children_of_step( $course_id, $course_id, 'sfwd-quiz' );
				}
				if ( ! empty( $quiz_ids ) ) {
					$group_quiz_ids = array_merge( $group_quiz_ids, $quiz_ids );
				}
				if ( ! empty( $lesson_ids ) ) {
					foreach ( $lesson_ids as $lesson_id ) {
						$quiz_ids = learndash_course_get_children_of_step( $course_id, $lesson_id, 'sfwd-quiz' );
						if ( ! empty( $quiz_ids ) ) {
							$group_quiz_ids = array_merge( $group_quiz_ids, $quiz_ids );
							// $group_quiz_ids = array_unique( $group_quiz_ids );
						}
						if ( $include_topics ) {
							$topic_ids = learndash_course_get_children_of_step( $course_id, $lesson_id, 'sfwd-topic' );
							if ( ! empty( $topic_ids ) ) {
								foreach ( $topic_ids as $topic_id ) {
									$quiz_ids = learndash_course_get_children_of_step( $course_id, $topic_id, 'sfwd-quiz' );
									if ( ! empty( $quiz_ids ) ) {
										$group_quiz_ids = array_merge( $group_quiz_ids, $quiz_ids );
									}
								}
							}
						}
					}
				}

				$group_quiz_ids         = array_unique( $group_quiz_ids );
				$relation[ $course_id ] = $group_quiz_ids;

				if ( ! empty( $relation[ $course_id ] ) ) {
					foreach ( $relation[ $course_id ] as $quiz_id ) {
						if ( ! isset( $relationships[ $course_id ][ $quiz_id ] ) ) {
							$relationships[ $course_id ][ $quiz_id ] = get_the_title( $quiz_id );
						}
					}
				}
			} else {
				$group_course_ids = array( $post_id );
				$lesson_ids       = learndash_course_get_children_of_step( $post_id, $post_id, 'sfwd-lessons' );
				$quiz_ids         = learndash_course_get_children_of_step( $post_id, $post_id, 'sfwd-quiz' );

				if ( ! empty( $quiz_ids ) ) {
					$group_quiz_ids = array_merge( $group_quiz_ids, $quiz_ids );
				}
				if ( ! empty( $lesson_ids ) ) {
					foreach ( $lesson_ids as $lesson_id ) {
						$quiz_ids = learndash_course_get_children_of_step( $post_id, $lesson_id, 'sfwd-quiz' );

						if ( ! empty( $quiz_ids ) ) {
							$group_quiz_ids = array_merge( $group_quiz_ids, $quiz_ids );
							// $group_quiz_ids = array_unique( $group_quiz_ids );
						}
						if ( $include_topics ) {
							$topic_ids = learndash_course_get_children_of_step( $post_id, $lesson_id, 'sfwd-topic' );
							if ( ! empty( $topic_ids ) ) {
								foreach ( $topic_ids as $topic_id ) {
									$quiz_ids = learndash_course_get_children_of_step( $post_id, $topic_id, 'sfwd-quiz' );
									if ( ! empty( $quiz_ids ) ) {
										$group_quiz_ids = array_merge( $group_quiz_ids, $quiz_ids );
									}
								}
							}
						}
					}
				}

				$group_quiz_ids       = array_unique( $group_quiz_ids );
				$relation[ $post_id ] = $group_quiz_ids;

				if ( ! empty( $relation[ $post_id ] ) ) {
					foreach ( $relation[ $post_id ] as $quiz_id ) {
						if ( ! isset( $relationships[ $post_id ][ $quiz_id ] ) ) {
							$relationships[ $post_id ][ $quiz_id ] = get_the_title( $quiz_id );
						}
					}
				}
			}
		}

		$data = array(
			'ldd_quiz_ids'       => $group_quiz_ids,
			'ldd_course_quizzes' => $group_course_ids,
			'relationships'      => $relationships,
		);

		return $data;
	}

	/**
	 * Get all lessons/courses post objects
	 *
	 * @param array  $ids
	 * @param string $post_type
	 * @param string $order_by
	 * @param string $order
	 *
	 * @return array $_lessons
	 */
	public function get_objects( $ids, $post_type, $order_by = 'title', $order = 'ASC' ) {

		if ( empty( $order_by ) ) {
			$order_by = 'title';
		}

		if ( empty( $order ) ) {
			$order = 'ASC';
		}

		if ( empty( $ids ) ) {
			return array();
		}

		$args = array(
			'post_type'      => $post_type,
			'post__in'       => $ids,
			'posts_per_page' => - 1,
			'orderby'        => $order_by,
			'order'          => $order,
		);

		$lessons = get_posts( $args );

		// Set the Key as the post ID so we don't have to run a nested loop
		$_lessons = array();
		foreach ( $lessons as $quiz ) {
			$_lessons[ $quiz->ID ] = $quiz;
		}

		return $_lessons;
	}

	public static function ld_dasboard_is_report_page() {
		if ( isset( $_GET ) && ! empty( $_GET['tab'] ) && ( 'course-report' === $_GET['tab'] || 'essay-report' === $_GET['tab'] || 'assignment-report' === $_GET['tab'] || 'quizz-report' === $_GET['tab'] ) ) {
			return true;
		}

		return false;
	}
}


LD_Dashboard_Reports::getInstance();
