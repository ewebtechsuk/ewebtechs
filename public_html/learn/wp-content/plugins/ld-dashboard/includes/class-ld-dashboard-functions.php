<?php
/**
 * Class to define all the global variables related to plugin.
 *
 * @since      1.0.0
 * @author     Wbcom Designs
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Dashboard_Functions' ) ) {
	/**
	 * Class to add global variables of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @author   Wbcom Designs
	 */
	class Ld_Dashboard_Functions {
		/**
		 * The single instance of the class.
		 *
		 * @var   Ld_Dashboard_Functions
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Main Ld_Dashboard_Functions Instance.
		 *
		 * Ensures only one instance of Ld_Dashboard_Functions is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
		}

		/**
		 * Get default general settings.
		 *
		 * @access public
		 * @since  1.0.0
		 * @author Wbcom Designs
		 * @return array
		 */
		public function default_general_settings() {
			$default_arr = array(
				'instructor-total-sales'         => 1,
				'instructor-total-sales-bgcolor' => '#f0f0f0',
				'course-count'                   => 1,
				'course-count-bgcolor'           => '#f0f0f0',
				'quizzes-count'                  => 1,
				'quizzes-count-bgcolor'          => '#f0f0f0',
				'assignments-count'              => 1,
				'assignments-completed-count'    => 1,
				'assignments-count-bgcolor'      => '#f0f0f0',
				'essays-pending-count'           => 1,
				'essays-pending-count-bgcolor'   => '#f0f0f0',
				'lessons-count'                  => 1,
				'lessons-count-bgcolor'          => '#f0f0f0',
				'topics-count'                   => 1,
				'topics-count-bgcolor'           => '#f0f0f0',
				'student-count'                  => 1,
				'student-details'                => 1,
				'student-count-bgcolor'          => '#f0f0f0',
				'total-earning'                  => 1,
				'total-earning-bgcolor'          => '#3a3a46',
				'instructor-statistics'          => 1,
				'course-progress'                => 1,
				'welcome-message'                => esc_html__( 'Welcome back, %s', 'ld-dashboard' ),
				'welcomebar_image'               => '',
				'ld-course-grid-columns'         => '4',
				'enable-email-integration'       => 1,
				'enable-messaging-integration'   => 1,
			);

			return apply_filters( 'ld_dashboard_default_general_settings', $default_arr );
		}

		/**
		 * Get default activities settings.
		 *
		 * @access public
		 * @since  1.0.0
		 * @author Wbcom Designs
		 * @return array
		 */
		public function default_activities_settings() {
			$default_arr = array(
				'enable-activity' => 1,
				'activity-limit'  => 10,
			);

			return apply_filters( 'ld_dashboard_default_activities_settings', $default_arr );
		}

		/**
		 * Get all admin settings data.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 * @return   array
		 */
		public function ld_dashboard_settings_data() {
			$general                     = array();
			$activities                  = array();
			$zoom_meeting_settings       = array();
			$default_general_settings    = $this->default_general_settings();
			$default_activities_settings = $this->default_activities_settings();
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$general_settings = get_site_option( 'ld_dashboard_general_settings' );
			} else {
				$general_settings = get_option( 'ld_dashboard_general_settings' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$default_design_options = get_site_option( 'ld_dashboard_default_design_settings' );
			} else {
				$default_design_options = get_option( 'ld_dashboard_default_design_settings' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$design_options = get_site_option( 'ld_dashboard_design_settings' );
			} else {
				$design_options = get_option( 'ld_dashboard_design_settings' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$tiles_options = get_site_option( 'ld_dashboard_tiles_options' );
			} else {
				$tiles_options = get_option( 'ld_dashboard_tiles_options' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$menu_options = get_site_option( 'ld_dashboard_menu_options' );
			} else {
				$menu_options = get_option( 'ld_dashboard_menu_options' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$welcome_screen = get_site_option( 'ld_dashboard_welcome_screen' );
			} else {
				$welcome_screen = get_option( 'ld_dashboard_welcome_screen' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$instructor_settings = get_site_option( 'ld_dashboard_instructor_settings' );
			} else {
				$instructor_settings = get_option( 'ld_dashboard_instructor_settings' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_feed_settings = get_site_option( 'ld_dashboard_feed_settings' );
			} else {
				$ld_dashboard_feed_settings = get_option( 'ld_dashboard_feed_settings' );
			}

			$ld_dashboard_feed_settings = ( $ld_dashboard_feed_settings != '' && is_array( $ld_dashboard_feed_settings ) ) ? $ld_dashboard_feed_settings : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_page_mapping = get_site_option( 'ld_dashboard_page_mapping' );
			} else {
				$ld_dashboard_page_mapping = get_option( 'ld_dashboard_page_mapping' );
			}

			$ld_dashboard_page_mapping = ( $ld_dashboard_page_mapping != '' && is_array( $ld_dashboard_page_mapping ) ) ? $ld_dashboard_page_mapping : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_course_form_settings = get_site_option( 'ld_dashboard_course_form_settings' );
			} else {
				$ld_dashboard_course_form_settings = get_option( 'ld_dashboard_course_form_settings' );
			}

			$ld_dashboard_course_form_settings = ( $ld_dashboard_course_form_settings != '' && is_array( $ld_dashboard_course_form_settings ) ) ? $ld_dashboard_course_form_settings : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_lesson_form_settings = get_site_option( 'ld_dashboard_lesson_form_settings' );
			} else {
				$ld_dashboard_lesson_form_settings = get_option( 'ld_dashboard_lesson_form_settings' );
			}

			$ld_dashboard_lesson_form_settings = ( $ld_dashboard_lesson_form_settings != '' && is_array( $ld_dashboard_lesson_form_settings ) ) ? $ld_dashboard_lesson_form_settings : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_topic_form_settings = get_site_option( 'ld_dashboard_topic_form_settings' );
			} else {
				$ld_dashboard_topic_form_settings = get_option( 'ld_dashboard_topic_form_settings' );
			}

			$ld_dashboard_topic_form_settings = ( $ld_dashboard_topic_form_settings != '' && is_array( $ld_dashboard_topic_form_settings ) ) ? $ld_dashboard_topic_form_settings : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_quiz_form_settings = get_site_option( 'ld_dashboard_quiz_form_settings' );
			} else {
				$ld_dashboard_quiz_form_settings = get_option( 'ld_dashboard_quiz_form_settings' );
			}

			$ld_dashboard_quiz_form_settings = ( $ld_dashboard_quiz_form_settings != '' && is_array( $ld_dashboard_quiz_form_settings ) ) ? $ld_dashboard_quiz_form_settings : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_question_form_settings = get_site_option( 'ld_dashboard_question_form_settings' );
			} else {
				$ld_dashboard_question_form_settings = get_option( 'ld_dashboard_question_form_settings' );
			}

			$ld_dashboard_question_form_settings = ( $ld_dashboard_question_form_settings != '' && is_array( $ld_dashboard_question_form_settings ) ) ? $ld_dashboard_question_form_settings : array();

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_manage_monetization = get_site_option( 'ld_dashboard_manage_monetization' );
			} else {
				$ld_dashboard_manage_monetization = get_option( 'ld_dashboard_manage_monetization' );
			}

			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_zoom_meeting_settings = get_site_option( 'ld_dashboard_zoom_meeting_settings' );
			} else {
				$ld_dashboard_zoom_meeting_settings = get_option( 'ld_dashboard_zoom_meeting_settings' );
			}

			if ( ! empty( $ld_dashboard_zoom_meeting_settings ) ) {
				if ( isset( $ld_dashboard_zoom_meeting_settings['embed-meeting'] ) ) {
					$zoom_meeting_settings['embed-meeting'] = $ld_dashboard_zoom_meeting_settings['embed-meeting'];
				} else {
					$zoom_meeting_settings['embed-meeting'] = 0;
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['use-admin-account'] ) ) {
					$zoom_meeting_settings['use-admin-account'] = $ld_dashboard_zoom_meeting_settings['use-admin-account'];
				} else {
					$zoom_meeting_settings['use-admin-account'] = 0;
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['zoom-account-id'] ) ) {
					$zoom_meeting_settings['zoom-account-id'] = $ld_dashboard_zoom_meeting_settings['zoom-account-id'];
				} else {
					$zoom_meeting_settings['zoom-account-id'] = '';
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['zoom-api-key'] ) ) {
					$zoom_meeting_settings['zoom-api-key'] = $ld_dashboard_zoom_meeting_settings['zoom-api-key'];
				} else {
					$zoom_meeting_settings['zoom-api-key'] = '';
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['zoom-api-secret'] ) ) {
					$zoom_meeting_settings['zoom-api-secret'] = $ld_dashboard_zoom_meeting_settings['zoom-api-secret'];
				} else {
					$zoom_meeting_settings['zoom-api-secret'] = '';
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['zoom-user-email'] ) ) {
					$zoom_meeting_settings['zoom-user-email'] = $ld_dashboard_zoom_meeting_settings['zoom-user-email'];
				} else {
					$zoom_meeting_settings['zoom-user-email'] = '';
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['zoom-co-hosts'] ) ) {
					$zoom_meeting_settings['zoom-co-hosts'] = $ld_dashboard_zoom_meeting_settings['zoom-co-hosts'];
				} else {
					$zoom_meeting_settings['zoom-co-hosts'] = array();
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['sdk-client-id'] ) ) {
					$zoom_meeting_settings['sdk-client-id'] = $ld_dashboard_zoom_meeting_settings['sdk-client-id'];
				} else {
					$zoom_meeting_settings['sdk-client-id'] = '';
				}

				if ( isset( $ld_dashboard_zoom_meeting_settings['sdk-client-secret'] ) ) {
					$zoom_meeting_settings['sdk-client-secret'] = $ld_dashboard_zoom_meeting_settings['sdk-client-secret'];
				} else {
					$zoom_meeting_settings['sdk-client-secret'] = '';
				}
			}
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_time_tracking_settings = get_site_option( 'ld_dashboard_time_tracking_settings' );
			} else {
				$ld_dashboard_time_tracking_settings = get_option( 'ld_dashboard_time_tracking_settings' );
			}
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$ld_dashboard_invite_user_settings = get_site_option( 'ld_dashboard_invite_user_settings' );
			} else {
				$ld_dashboard_invite_user_settings = get_option( 'ld_dashboard_invite_user_settings' );
			}

			/* General settings */
			if ( ! empty( $general_settings ) ) {
				if ( isset( $general_settings['welcome-screen'] ) ) {
					$general['welcome-screen'] = $general_settings['welcome-screen'];
				} else {
					$general['welcome-screen'] = 0;
				}

				if ( isset( $general_settings['statistics-tiles'] ) ) {
					$general['statistics-tiles'] = $general_settings['statistics-tiles'];
				} else {
					$general['statistics-tiles'] = 0;
				}

				if ( isset( $general_settings['statistics-tiles-allwoed-roles'] ) ) {
					$general['statistics-tiles-allwoed-roles'] = $general_settings['statistics-tiles-allwoed-roles'];
				} else {
					$general['statistics-tiles-allwoed-roles'] = array();
				}

				if ( isset( $general_settings['enable-email-integration'] ) ) {
					$general['enable-email-integration'] = $general_settings['enable-email-integration'];
				} else {
					$general['enable-email-integration'] = 0;
				}

				if ( isset( $general_settings['enable-zoom'] ) ) {
					$general['enable-zoom'] = $general_settings['enable-zoom'];
				} else {
					$general['enable-zoom'] = 0;
				}

				if ( isset( $general_settings['enable-messaging-integration'] ) ) {
					$general['enable-messaging-integration'] = $general_settings['enable-messaging-integration'];
				} else {
					$general['enable-messaging-integration'] = 0;
				}
				if ( isset( $general_settings['enable-instructor-course-publish'] ) ) {
					$general['enable-instructor-course-publish'] = $general_settings['enable-instructor-course-publish'];
				} else {
					$general['enable-instructor-course-publish'] = 0;
				}
				if ( isset( $general_settings['enable-instructor-course-tags'] ) ) {
					$general['enable-instructor-course-tags'] = $general_settings['enable-instructor-course-tags'];
				} else {
					$general['enable-instructor-course-tags'] = 0;
				}
				if ( isset( $general_settings['display-to-do'] ) ) {
					$general['display-to-do'] = $general_settings['display-to-do'];
				} else {
					$general['display-to-do'] = 0;
				}
				if ( isset( $general_settings['become-instructor-button'] ) ) {
					$general['become-instructor-button'] = $general_settings['become-instructor-button'];
				} else {
					$general['become-instructor-button'] = 0;
				}

				if ( ! empty( $general_settings['instructor-total-sales'] ) ) {
					$general['instructor-total-sales'] = $general_settings['instructor-total-sales'];
				} else {
					$general['instructor-total-sales'] = 0;
				}
				if ( isset( $general_settings['instructor-total-sales-bgcolor'] ) ) {
					$general['instructor-total-sales-bgcolor'] = $general_settings['instructor-total-sales-bgcolor'];
				} else {
					$general['instructor-total-sales-bgcolor'] = $default_general_settings['instructor-total-sales-bgcolor'];
				}

				if ( isset( $general_settings['student-details'] ) ) {
					$general['student-details'] = $general_settings['student-details'];
				} else {
					$general['student-details'] = 0;
				}

				if ( isset( $general_settings['student-details-roles'] ) ) {
					$general['student-details-roles'] = $general_settings['student-details-roles'];
				} else {
					$general['student-details-roles'] = array();
				}

				if ( isset( $general_settings['enable-announcements'] ) ) {
					$general['enable-announcements'] = $general_settings['enable-announcements'];
				} else {
					$general['enable-announcements'] = 0;
				}

				if ( isset( $general_settings['popular-course-report'] ) ) {
					$general['popular-course-report'] = $general_settings['popular-course-report'];
				} else {
					$general['popular-course-report'] = 0;
				}

				if ( isset( $general_settings['course-completion-report'] ) ) {
					$general['course-completion-report'] = $general_settings['course-completion-report'];
				} else {
					$general['course-completion-report'] = 0;
				}

				if ( isset( $general_settings['course-completion-report-roles'] ) ) {
					$general['course-completion-report-roles'] = $general_settings['course-completion-report-roles'];
				} else {
					$general['course-completion-report-roles'] = array();
				}

				if ( isset( $general_settings['top-courses-report'] ) ) {
					$general['top-courses-report'] = $general_settings['top-courses-report'];
				} else {
					$general['top-courses-report'] = 0;
				}

				if ( isset( $general_settings['top-courses-report-roles'] ) ) {
					$general['top-courses-report-roles'] = $general_settings['top-courses-report-roles'];
				} else {
					$general['top-courses-report-roles'] = array();
				}

				if ( isset( $general_settings['instructor-statistics'] ) ) {
					$general['instructor-statistics'] = $general_settings['instructor-statistics'];
				} else {
					$general['instructor-statistics'] = 0;
				}
				if ( isset( $general_settings['course-progress'] ) ) {
					$general['course-progress'] = $general_settings['course-progress'];
				} else {
					$general['course-progress'] = 0;
				}

				if ( isset( $general_settings['course-progress-roles'] ) ) {
					$general['course-progress-roles'] = $general_settings['course-progress-roles'];
				} else {
					$general['course-progress-roles'] = array();
				}

				if ( isset( $general_settings['instructor_registration_page'] ) ) {
					$general['instructor_registration_page'] = $general_settings['instructor_registration_page'];
				} else {
					$general['instructor_registration_page'] = 0;
				}

				if ( isset( $general_settings['instructor_listing_page'] ) ) {
					$general['instructor_listing_page'] = $general_settings['instructor_listing_page'];
				} else {
					$general['instructor_listing_page'] = 0;
				}

				/* Popular Course Setting */
				if ( isset( $general_settings['enable-popular-courses-student'] ) ) {
					$general['enable-popular-courses-student'] = $general_settings['enable-popular-courses-student'];
				} else {
					$general['enable-popular-courses-student'] = '';
				}

				if ( isset( $general_settings['enable-popular-courses-group-leader'] ) ) {
					$general['enable-popular-courses-group-leader'] = $general_settings['enable-popular-courses-group-leader'];
				} else {
					$general['enable-popular-courses-group-leader'] = '';
				}

				if ( isset( $general_settings['popular_course_tag'] ) ) {
					$general['popular_course_tag'] = $general_settings['popular_course_tag'];
				} else {
					$general['popular_course_tag'] = 0;
				}

				/* LD welcome screen */
				if ( isset( $general_settings['welcome-message'] ) ) {
					$general['welcome-message'] = $general_settings['welcome-message'];
				} else {
					$general['welcome-message'] = $default_general_settings['welcome-message'];
				}
				if ( isset( $general_settings['welcomebar_image'] ) ) {
					$general['welcomebar_image'] = $general_settings['welcomebar_image'];
				} else {
					$general['welcomebar_image'] = $default_general_settings['welcomebar_image'];
				}

				/*  LD Page Mapping */
				if ( isset( $general_settings['my_dashboard_page'] ) ) {
					$general['my_dashboard_page'] = $general_settings['my_dashboard_page'];
				} else {
					$general['my_dashboard_page'] = 0;
				}
				if ( isset( $general_settings['instructor_registration_page'] ) ) {
					$general['instructor_registration_page'] = $general_settings['instructor_registration_page'];
				} else {
					$general['instructor_registration_page'] = 0;
				}

				if ( isset( $general_settings['enable-revenue-sharing'] ) ) {
					$general['enable-revenue-sharing'] = $general_settings['enable-revenue-sharing'];
				} else {
					$general['enable-revenue-sharing'] = 0;
				}

				if ( isset( $general_settings['student-per-page-count'] ) ) {
					$general['student-per-page-count'] = $general_settings['student-per-page-count'];
				} else {
					$general['student-per-page-count'] = 10;
				}

				if ( isset( $general_settings['default-avatar'] ) ) {
					$general['default-avatar'] = $general_settings['default-avatar'];
				} else {
					$general['default-avatar'] = 0;
				}

				if ( isset( $general_settings['redirect-profile'] ) ) {
					$general['redirect-profile'] = $general_settings['redirect-profile'];
				} else {
					$general['redirect-profile'] = 0;
				}

				if ( isset( $general_settings['enable-instructor-earning-logs'] ) ) {
					$general['enable-instructor-earning-logs'] = $general_settings['enable-instructor-earning-logs'];
				} else {
					$general['enable-instructor-earning-logs'] = 0;
				}

				if ( isset( $general_settings['ldd_page_title'] ) ) {
					$general['ldd_page_title'] = $general_settings['ldd_page_title'];
				} else {
					$general['ldd_page_title'] = 0;
				}
			} else {
				$general = $default_general_settings;
			}

			/* LD welcome screen */
			if ( isset( $general_settings['welcome-message'] ) ) {
				$welcome_screen['welcome-message'] = $general_settings['welcome-message'];
			} else {
				$welcome_screen['welcome-message'] = $default_general_settings['welcome-message'];
			}

			if ( isset( $general_settings['welcomebar_image'] ) ) {
				$welcome_screen['welcomebar_image'] = $general_settings['welcomebar_image'];
			} else {
				$welcome_screen['welcomebar_image'] = $default_general_settings['welcomebar_image'];
			}

			/** LD Single Instructor Course Grid */

			if ( isset( $general_settings['ld-course-grid-columns'] ) ) {
				$general['ld-course-grid-columns'] = $general_settings['ld-course-grid-columns'];
			} else {
				$general['ld-course-grid-columns'] = $default_general_settings['ld-course-grid-columns'];
			}
			if ( isset( $general_settings['ld-course-grid-progress-bar'] ) ) {
				$general['ld-course-grid-progress-bar'] = $general_settings['ld-course-grid-progress-bar'];
			} else {
				$general['ld-course-grid-progress-bar'] = 0;
			}
			if ( isset( $general_settings['ld-course-grid-course-content'] ) ) {
				$general['ld-course-grid-course-content'] = $general_settings['ld-course-grid-course-content'];
			} else {
				$general['ld-course-grid-course-content'] = 0;
			}

			/* LD tiles options */
			if ( isset( $tiles_options['course-count'] ) ) {
				$tiles_options['course-count'] = $tiles_options['course-count'];
			} else {
				$tiles_options['course-count'] = 0;
			}
			if ( isset( $tiles_options['course-count-bgcolor'] ) ) {
				$tiles_options['course-count-bgcolor'] = $tiles_options['course-count-bgcolor'];
			} else {
				$tiles_options['course-count-bgcolor'] = $default_general_settings['course-count-bgcolor'];
			}
			if ( isset( $tiles_options['quizzes-count'] ) ) {
				$tiles_options['quizzes-count'] = $tiles_options['quizzes-count'];
			} else {
				$tiles_options['quizzes-count'] = 0;
			}
			if ( isset( $tiles_options['quizzes-count-bgcolor'] ) ) {
				$tiles_options['quizzes-count-bgcolor'] = $tiles_options['quizzes-count-bgcolor'];
			} else {
				$tiles_options['quizzes-count-bgcolor'] = $default_general_settings['quizzes-count-bgcolor'];
			}
			if ( isset( $tiles_options['assignments-count'] ) ) {
				$tiles_options['assignments-count'] = $tiles_options['assignments-count'];
			} else {
				$tiles_options['assignments-count'] = 0;
			}
			if ( isset( $tiles_options['assignments-completed-count'] ) ) {
				$tiles_options['assignments-completed-count'] = $tiles_options['assignments-completed-count'];
			} else {
				$tiles_options['assignments-completed-count'] = 0;
			}
			if ( isset( $tiles_options['assignments-count-bgcolor'] ) ) {
				$tiles_options['assignments-count-bgcolor'] = $tiles_options['assignments-count-bgcolor'];
			} else {
				$tiles_options['assignments-count-bgcolor'] = $default_general_settings['assignments-count-bgcolor'];
			}
			if ( isset( $tiles_options['essays-pending-count'] ) ) {
				$tiles_options['essays-pending-count'] = $tiles_options['essays-pending-count'];
			} else {
				$tiles_options['essays-pending-count'] = 0;
			}
			if ( isset( $tiles_options['essays-pending-count-bgcolor'] ) ) {
				$tiles_options['essays-pending-count-bgcolor'] = $tiles_options['essays-pending-count-bgcolor'];
			} else {
				$tiles_options['essays-pending-count-bgcolor'] = $default_general_settings['essays-pending-count-bgcolor'];
			}
			if ( isset( $tiles_options['lessons-count'] ) ) {
				$tiles_options['lessons-count'] = $tiles_options['lessons-count'];
			} else {
				$tiles_options['lessons-count'] = 0;
			}
			if ( isset( $tiles_options['lessons-count-bgcolor'] ) ) {
				$tiles_options['lessons-count-bgcolor'] = $tiles_options['lessons-count-bgcolor'];
			} else {
				$tiles_options['lessons-count-bgcolor'] = $default_general_settings['lessons-count-bgcolor'];
			}
			if ( isset( $tiles_options['topics-count'] ) ) {
				$tiles_options['topics-count'] = $tiles_options['topics-count'];
			} else {
				$tiles_options['topics-count'] = 0;
			}
			if ( isset( $tiles_options['topics-count-bgcolor'] ) ) {
				$tiles_options['topics-count-bgcolor'] = $tiles_options['topics-count-bgcolor'];
			} else {
				$tiles_options['topics-count-bgcolor'] = $default_general_settings['topics-count-bgcolor'];
			}
			if ( isset( $tiles_options['student-count'] ) ) {
				$tiles_options['student-count'] = $tiles_options['student-count'];
			} else {
				$tiles_options['student-count'] = 0;
			}
			if ( isset( $tiles_options['student-count-bgcolor'] ) ) {
				$tiles_options['student-count-bgcolor'] = $tiles_options['student-count-bgcolor'];
			} else {
				$tiles_options['student-count-bgcolor'] = $default_general_settings['student-count-bgcolor'];
			}
			if ( isset( $tiles_options['total-earning'] ) ) {
				$tiles_options['total-earning'] = $tiles_options['total-earning'];
			} else {
				$tiles_options['total-earning'] = 0;
			}
			if ( isset( $tiles_options['total-earning-bgcolor'] ) ) {
				$tiles_options['total-earning-bgcolor'] = $tiles_options['total-earning-bgcolor'];
			} else {
				$tiles_options['total-earning-bgcolor'] = $default_general_settings['total-earning-bgcolor'];
			}

			/*  LD Feed settings */
			if ( isset( $ld_dashboard_feed_settings['disable-live-feed'] ) ) {
				$ld_dashboard_feed_settings['disable-live-feed'] = $ld_dashboard_feed_settings['disable-live-feed'];
			} else {
				$ld_dashboard_feed_settings['disable-live-feed'] = 0;
			}

			if ( isset( $ld_dashboard_feed_settings['disable_user_roles_live_feed'] ) ) {
				$ld_dashboard_feed_settings['disable_user_roles_live_feed'] = $ld_dashboard_feed_settings['disable_user_roles_live_feed'];
			} else {
				$ld_dashboard_feed_settings['disable_user_roles_live_feed'] = 0;
			}

			/*  LD Page Mapping */
			if ( isset( $general_settings['my_dashboard_page'] ) ) {
				$ld_dashboard_page_mapping['my_dashboard_page'] = $general_settings['my_dashboard_page'];
			} else {
				$ld_dashboard_page_mapping['my_dashboard_page'] = 0;
			}
			if ( isset( $general_settings['instructor_registration_page'] ) ) {
				$ld_dashboard_page_mapping['instructor_registration_page'] = $general_settings['instructor_registration_page'];
			} else {
				$ld_dashboard_page_mapping['instructor_registration_page'] = 0;
			}

			/* Images settings */
			if ( isset( $activities_settings ) ) {
				if ( ! empty( $activities_settings['enable-activity'] ) ) {
					$activities['enable-activity'] = $activities_settings['enable-activity'];
				} else {
					$activities['enable-activity'] = 0;
				}
				if ( ! empty( $activities_settings['activity-limit'] ) ) {
					$activities['activity-limit'] = $activities_settings['activity-limit'];
				} else {
					$activities['activity-limit'] = $default_activities_settings['activity-limit'];
				}
			} else {
				$activities = $default_activities_settings;
			}

			$settings = array(
				'general_settings'           => $general,
				'default_design_options'     => $default_design_options,
				'design_options'             => $design_options,
				'tiles_options'              => $tiles_options,
				'menu_options'               => $menu_options,
				'welcome_screen'             => $welcome_screen,
				'ld_dashboard_feed_settings' => $ld_dashboard_feed_settings,
				// 'ld_dashboard_integration' => $ld_dashboard_integration,
				'ld_dashboard_page_mapping'  => $ld_dashboard_page_mapping,
				'monetization_settings'      => $ld_dashboard_manage_monetization,
				'zoom_meeting_settings'      => $zoom_meeting_settings,
				'time_tracking'      		 => $ld_dashboard_time_tracking_settings,
				'invite_user'      		 	  => $ld_dashboard_invite_user_settings,
				'course_fields_setting'      => $ld_dashboard_course_form_settings,
				'lesson_fields_setting'      => $ld_dashboard_lesson_form_settings,
				'topic_fields_setting'       => $ld_dashboard_topic_form_settings,
				'quiz_fields_setting'        => $ld_dashboard_quiz_form_settings,
				'question_fields_setting'    => $ld_dashboard_question_form_settings,
				'instructor_settings'        => $instructor_settings,
				'activities_settings'        => $activities,
			);

			return $settings;
		}

		/**
		 * Get shortcode page urls
		 */
		public function ld_dashboard_get_url( $slug ) {
			$options = $this->ld_dashboard_settings_data();
			$url     = $page = '';
			if ( 'dashboard' === $slug ) {
				if ( array_key_exists( 'my_dashboard_page', $options['general_settings'] ) && isset( $options['general_settings']['my_dashboard_page'] ) ) {
					$page = $options['general_settings']['my_dashboard_page'];
					$url  = get_the_permalink( $page );
				}
			} elseif ( 'register' === $slug ) {
				if ( array_key_exists( 'instructor_registration_page', $options['general_settings'] ) && isset( $options['general_settings']['instructor_registration_page'] ) ) {
					$page = $options['general_settings']['instructor_registration_page'];
					$url  = get_the_permalink( $page );
				}
			} elseif ( 'instructors' === $slug ) {
				if ( array_key_exists( 'instructor_listing_page', $options['general_settings'] ) && isset( $options['general_settings']['instructor_listing_page'] ) ) {
					$page = $options['general_settings']['instructor_listing_page'];
					$url  = get_the_permalink( $page );
				}
			}

			return apply_filters( 'ld_dashboard_url', $url, $page );

		}

		/**
		 * Get shortcode page ids
		 */
		public function ld_dashboard_get_page_id( $slug ) {
			$options = $this->ld_dashboard_settings_data();
			$url     = $page = '';
			if ( 'dashboard' === $slug ) {
				if ( array_key_exists( 'my_dashboard_page', $options['general_settings'] ) && isset( $options['general_settings']['my_dashboard_page'] ) ) {
					$page = $options['general_settings']['my_dashboard_page'];
				}
			} elseif ( 'register' === $slug ) {
				if ( array_key_exists( 'instructor_listing_page', $options['general_settings'] ) && isset( $options['general_settings']['instructor_listing_page'] ) ) {
					$page = $options['general_settings']['instructor_listing_page'];
				}
			} elseif ( 'instructors' === $slug ) {
				if ( array_key_exists( 'instructor_listing_page', $options['general_settings'] ) && isset( $options['general_settings']['instructor_listing_page'] ) ) {
					$page = $options['general_settings']['instructor_listing_page'];
				}
			}

			return apply_filters( 'ld_dashboard_page_id', $page );

		}
	}

}
