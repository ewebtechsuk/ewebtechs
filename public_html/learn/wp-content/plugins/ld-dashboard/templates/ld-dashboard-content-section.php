<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $current_user, $wpdb, $wp;

$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$my_dashboard_url           = $function_obj->ld_dashboard_get_url( 'dashboard' );
$ld_dashboard               = $ld_dashboard_settings_data['general_settings'];
$tiles_options              = $ld_dashboard_settings_data['tiles_options'];
$monetization_settings      = $ld_dashboard_settings_data['monetization_settings'];
$welcome_screen             = $ld_dashboard_settings_data['welcome_screen'];
$user_id                    = get_current_user_id();
$is_student                 = get_user_meta( $user_id, 'is_student', true );
$user_name                  = $current_user->user_firstname . ' ' . $current_user->user_lastname;
$user_name                  = ( $current_user->user_firstname == '' && $current_user->user_lastname == '' ) ? $current_user->user_login : $user_name;
$group_leader_user_caps     = get_option( 'learndash_groups_group_leader_user', array() );

if ( ! learndash_is_group_leader_user( $user_id ) && ! learndash_is_admin_user( $user_id ) && ! in_array( 'ld_instructor', (array) $current_user->roles ) ) {
	$is_student = 1;
}
$dashboard_landing_cover = '';
if ( isset( $welcome_screen['welcomebar_image'] ) && $welcome_screen['welcomebar_image'] != '' ) {
	$dashboard_landing_cover = "background-image: url({$welcome_screen['welcomebar_image']});";
}
?>
<div class="ld-dashboard-content ld-dashborad-add-edit-course">
	<?php
	if ( isset( $_GET['tab'] ) && isset( $_GET['action'] ) && 'groups' !== $_GET['tab'] && 'add-course-playlist' !== $_GET['action'] && 'reset' !== $_GET['action'] && 'withdraw' !== $_GET['action'] && 'zoom' !== $_GET['action'] ) {
		$slug = '';
		if ( 'my-courses' === $_GET['tab'] ) {
			$slug = sprintf( esc_html__( '%s page', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'course' ) );
		} elseif ( 'my-lessons' === $_GET['tab'] ) {
			$slug = sprintf( esc_html__( '%s page', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lesson' ) );
		} elseif ( 'my-quizzes' === $_GET['tab'] ) {
			$slug = sprintf( esc_html__( '%s page', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quiz' ) );
		} elseif ( 'my-topics' === $_GET['tab'] ) {
			$slug = sprintf( esc_html__( '%s page', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'topic' ) );
		} elseif ( 'my-questions' === $_GET['tab'] ) {
			$slug = sprintf( esc_html__( '%s page', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'question' ) );
		} elseif ( 'certificates' === $_GET['tab'] ) {
			$slug = esc_html__( 'Certificate', 'ld-dashboard' );
		} elseif ( 'my-announcements' === $_GET['tab'] ) {
			$slug = esc_html__( 'Add Announcements', 'ld-dashboard' );
		}
		?>
		<div class="ld-dashboard-inline-links">
			<ul class="ld-dashboard-inline-links-ul">
				<li class="course-nav-active"><a href="#" class="ld-dashboard-form-tab-switch" data-tab="post"><?php echo esc_html( $slug ); ?></a> </li>
				<?php
				if ( isset( $_GET['tab'] ) && ( ( 'my-courses' === $_GET['tab'] && isset( $_GET['action'] ) && 'edit-course' === $_GET['action'] ) || ( 'my-quizzes' === $_GET['tab'] && isset( $_GET['action'] ) ) ) ) {
					?>
					<li class=""><a href="#" class="ld-dashboard-form-tab-switch" data-tab="builder"><?php esc_html_e( 'Builder', 'ld-dashboard' ); ?></a></li>
					<?php
				}
				if ( 'add-certificate' !== $_GET['action'] && 'edit-certificate' !== $_GET['action'] && 'add-announcement' !== $_GET['action'] && 'edit-announcement' !== $_GET['action'] ) {
					?>
					<li class=""><a href="#" class="ld-dashboard-form-tab-switch" data-tab="setting"><?php esc_html_e( 'Settings', 'ld-dashboard' ); ?></a></li>
					<?php
				}
				?>
			</ul>
		</div>
	<?php }; ?>
	<?php
	do_action( 'ld_dashboard_before_content' );
	if ( ! isset( $_GET['tab'] ) ) {
		if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-content.php' ) ) {
			include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-content.php' );
		} else {
			include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-content.php';
		}
		if ( ld_dashboard_is_user_role_allowed( $ld_dashboard['course-completion-report-roles'] ) ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-chart-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-chart-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-chart-content.php';
			}
		}
		if ( isset( $ld_dashboard['course-progress'] ) && $ld_dashboard['course-progress'] == 1 ) {
			if ( $is_student != 1 ) {
				if ( ld_dashboard_is_user_role_allowed( $ld_dashboard['course-progress-roles'] ) ) {
					/*
					 Course Progress Report */
					// if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-course-report.php' ) ) {
					// include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-course-report.php' );
					// } else {
					// include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-course-report.php';
					// }
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-student-course-report.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-student-course-report.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-student-course-report.php';
				}
			}
		}
		if ( isset( $ld_dashboard['student-details'] ) && $ld_dashboard['student-details'] == 1 ) {
			if ( $is_student != 1 ) {
				if ( ld_dashboard_is_user_role_allowed( $ld_dashboard['student-details-roles'] ) ) {
					/* Insttuctor Student statistic */
					if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-student-status.php' ) ) {
						include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-student-status.php' );
					} else {
						include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-student-status.php';
					}
				}
			}
		}
	} elseif ( isset( $_GET['tab'] ) ) {
		if ( 'my-courses' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$course_nonce     = wp_create_nonce( 'course-nonce' );
				$course_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'course-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-course&tab=my-courses&ld-course=%post_id%&is_submit=1&_lddnonce=' . $course_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$course_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $course_nonce, 'course-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-course' === $_GET['action'] ) {
							$course_form_args['post_id']      = 'new_post';
							$course_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$course_form_args['new_post']     = array(
								'post_type'   => 'sfwd-courses',
								'post_status' => 'publish',

							);
							acf_form( $course_form_args );
						} elseif ( 'add-course-playlist' === $_GET['action'] ) {
							if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-course-playlist-form.php' ) ) {
								include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-course-playlist-form.php' );
							} else {
								include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-course-playlist-form.php';
							}
						} elseif ( 'edit-course' === $_GET['action'] ) {
							$course_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-course'] ) );
							$course_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $course_form_args );
							if ( is_multisite() ) {
								$share_course_settings = get_site_option( 'learndash_settings_courses_management_display' );
							} else {
								$share_course_settings = get_option( 'learndash_settings_courses_management_display' );
							}
							if ( isset( $share_course_settings['course_builder_shared_steps'] ) && 'yes' === $share_course_settings['course_builder_shared_steps'] ) {
								if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-share-courses-builder.php' ) ) {
									include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-share-courses-builder.php' );
								} else {
									include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-share-courses-builder.php';
								}
							}
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-courses.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-courses.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-courses.php';
				}
			}
		}

		if ( 'my-lessons' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$lesson_nonce     = wp_create_nonce( 'lesson-nonce' );
				$lesson_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'lesson-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-lesson&tab=my-lessons&ld-lesson=%post_id%&is_submit=1&_lddnonce=' . $lesson_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$lesson_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $lesson_nonce, 'lesson-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-lesson' === $_GET['action'] ) {
							$lesson_form_args['post_id']      = 'new_post';
							$lesson_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$lesson_form_args['new_post']     = array(
								'post_type'   => 'sfwd-lessons',
								'post_status' => 'publish',
							);
							acf_form( $lesson_form_args );
						} elseif ( 'edit-lesson' === $_GET['action'] ) {
							$lesson_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-lesson'] ) );
							$lesson_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $lesson_form_args );
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-lessons.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-lessons.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-lessons.php';
				}
			}
		}

		if ( 'my-quizzes' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$quiz_nonce     = wp_create_nonce( 'quiz-nonce' );
				$quiz_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'quizz-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-quiz&tab=my-quizzes&ld-quiz=%post_id%&is_submit=1&_lddnonce=' . $quiz_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$quiz_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $quiz_nonce, 'quiz-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-quiz' === $_GET['action'] ) {
							$quiz_form_args['post_id']      = 'new_post';
							$quiz_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$quiz_form_args['new_post']     = array(
								'post_type'   => 'sfwd-quiz',
								'post_status' => 'publish',
							);
							acf_form( $quiz_form_args );
						} elseif ( 'edit-quiz' === $_GET['action'] ) {
							$quiz_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-quiz'] ) );
							$quiz_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $quiz_form_args );
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-quizzes.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-quizzes.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-quizzes.php';
				}
			}
		}

		if ( 'my-topics' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$topic_nonce     = wp_create_nonce( 'topic-nonce' );
				$topic_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'topic-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-topic&tab=my-topics&ld-topic=%post_id%&is_submit=1&_lddnonce=' . $topic_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$topic_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $topic_nonce, 'topic-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-topic' === $_GET['action'] ) {
							$topic_form_args['post_id']      = 'new_post';
							$topic_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$topic_form_args['new_post']     = array(
								'post_type'   => 'sfwd-topic',
								'post_status' => 'publish',
							);
							acf_form( $topic_form_args );
						} elseif ( 'edit-topic' === $_GET['action'] ) {
							$topic_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-topic'] ) );
							$topic_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $topic_form_args );
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-topics.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-topics.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-topics.php';
				}
			}
		}

		if ( 'my-questions' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$question_nonce     = wp_create_nonce( 'question-nonce' );
				$question_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'question-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-question&tab=my-questions&ld-question=%post_id%&is_submit=1&_lddnonce=' . $question_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$question_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $question_nonce, 'question-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-question' === $_GET['action'] ) {
							$question_form_args['post_id']      = 'new_post';
							$question_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$question_form_args['new_post']     = array(
								'post_type'   => 'sfwd-question',
								'post_status' => 'publish',
							);
							acf_form( $question_form_args );
						} elseif ( 'edit-question' === $_GET['action'] ) {
							$question_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-question'] ) );
							$question_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $question_form_args );
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-questions.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-questions.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-questions.php';
				}
			}
		}

		if ( 'assignments' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-assignments-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-assignments-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-assignments-content.php';
			}
		}

		if ( 'meetings' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-meetings-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-meetings-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-meetings-content.php';
			}
		}

		if ( 'withdrawal' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-withdrawal-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-withdrawal-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-withdrawal-content.php';
			}
		}

		if ( 'earnings' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-earnings-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-earnings-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-earnings-content.php';
			}
		}

		if ( 'certificates' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$certificate_nonce     = wp_create_nonce( 'certificate-nonce' );
				$certificate_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'certificate-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-certificate&tab=certificates&ld-certificate=%post_id%&is_submit=1&_lddnonce=' . $certificate_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$certificate_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $certificate_nonce, 'certificate-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-certificate' === $_GET['action'] ) {
							$certificate_form_args['post_id']      = 'new_post';
							$certificate_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$certificate_form_args['new_post']     = array(
								'post_type'   => 'sfwd-certificates',
								'post_status' => 'publish',
							);
							acf_form( $certificate_form_args );
						} elseif ( 'edit-certificate' === $_GET['action'] ) {
							$certificate_form_args['post_id']      = ( isset( $_GET['ld-certificate'] ) ) ? sanitize_text_field( wp_unslash( $_GET['ld-certificate'] ) ) : 0;
							$certificate_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $certificate_form_args );
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-certificates.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-instructor-certificates.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-instructor-certificates.php';
				}
			}
		}

		if ( 'my-announcements' === $_GET['tab'] ) {
			if ( isset( $_GET['action'] ) ) {
				$announcement_nonce     = wp_create_nonce( 'announcement-nonce' );
				$announcement_form_args = array(
					'id'                  => 'acf-form',
					'field_groups'        => array( 'announcement-field-group' ),
					'form'                => true,
					'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
					'html_submit_spinner' => '<span class="acf-spinner"></span>',
					'return'              => $my_dashboard_url . '/?action=edit-announcement&tab=my-announcements&ld-announcement=%post_id%&is_submit=1&_lddnonce=' . $announcement_nonce,
					'kses'                => false,
				);

				if ( ( isset( $_GET['_lddnonce'] ) ) ) {
					$announcement_nonce = sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) );
					if ( ! wp_verify_nonce( $announcement_nonce, 'announcement-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( 'add-announcement' === $_GET['action'] ) {
							$announcement_form_args['post_id']      = 'new_post';
							$announcement_form_args['submit_value'] = esc_html__( 'Submit', 'ld-dashboard' );
							$announcement_form_args['new_post']     = array(
								'post_type'   => 'announcements',
								'post_status' => 'publish',
							);
							acf_form( $announcement_form_args );
						} elseif ( 'edit-announcement' === $_GET['action'] ) {
							$announcement_form_args['post_id']      = sanitize_text_field( wp_unslash( $_GET['ld-announcement'] ) );
							$announcement_form_args['submit_value'] = esc_html__( 'Update', 'ld-dashboard' );
							acf_form( $announcement_form_args );
						}
					}
				}
			} else {
				if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-assignments-content.php' ) ) {
					include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-assignments-content.php' );
				} else {
					include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-my-announcements-content.php';
				}
			}
		}

		if ( 'announcements' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-announcements-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-announcements-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-announcements-content.php';
			}
		}

		if ( 'profile' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-profile-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-profile-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-profile-content.php';
			}
		}
		if ( 'enrolled-courses' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-enrolled-courses-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-enrolled-courses-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-enrolled-courses-content.php';
			}
		}
		if ( 'notification' === $_GET['tab'] ) {
			echo do_shortcode( '[ld_email]' );
		}
		if ( 'private-messages' === $_GET['tab'] ) {
			echo do_shortcode( '[ld_message]' );
		}
		if ( 'groups' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-groups-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-groups-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-groups-content.php';
			}
		}
		if ( 'activity' === $_GET['tab'] || 'my-activity' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-feed-section.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-feed-section.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-feed-section.php';
			}
		}
		if ( 'my-quiz-attempts' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-my-quiz-attempts-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-my-quiz-attempts-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-my-quiz-attempts-content.php';
			}
		}
		if ( 'quiz-attempts' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-quiz-attempts-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-quiz-attempts-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-quiz-attempts-content.php';
			}
		}

		if ( 'submitted-essays' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-submitted-essays-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-submitted-essays-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-submitted-essays-content.php';
			}
		}

		if ( 'question-answer' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-question-answer-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-question-answer-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-question-answer-content.php';
			}
		}
		if ( 'settings' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-settings-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-settings-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-settings-content.php';
			}
		}

		if ( 'course-report' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-course-report-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-course-report-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-course-report-content.php';
			}
		}

		if ( 'essay-report' === $_GET['tab'] || 'assignment-report' === $_GET['tab'] || 'quizz-report' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-report-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-report-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-report-content.php';
			}
		}
		
		if ( 'invite-students' === $_GET['tab'] ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-invite-students-content.php' ) ) {
				include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-invite-students-content.php' );
			} else {
				include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-invite-students-content.php';
			}
		}
	}
	do_action( 'ld_dashboard_after_content' );
	?>
</div>
