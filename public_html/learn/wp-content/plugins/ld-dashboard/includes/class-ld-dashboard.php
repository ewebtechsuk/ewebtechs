<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wbcomdesigns.com/plugins
 * @since      1.0.0
 *
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Ld_Dashboard {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ld_Dashboard_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LD_DASHBOARD_VERSION' ) ) {
			$this->version = LD_DASHBOARD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ld-dashboard';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ld_Dashboard_Loader. Orchestrates the hooks of the plugin.
	 * - Ld_Dashboard_i18n. Defines internationalization functionality.
	 * - Ld_Dashboard_Admin. Defines all hooks for the admin area.
	 * - Ld_Dashboard_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ld-dashboard-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ld-dashboard-i18n.php';

		/* Enqueue wbcom plugin folder file. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wbcom/wbcom-admin-settings.php';

		/* Enqueue plugins essential functions file. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ld-dashboard-functions.php';

		/* Form functions */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ld-dashboard-profile-form-functions.php';

		/**
		 * The class responsible for defining functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ld-dashboard-functions.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-learndash-function-overrides.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ld-dashboard-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ld-dashboard-course-playlist.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/reports/class-ld-dashboard-reports.php';
		/**
		 * The class responsible for defining instructor earning chart widget that displays in the frontend area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ld-dashboard-earning-chart-widget.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/ld-dashboard-instructors.php';

		$obj                        = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['general_settings'];
		if ( isset( $settings['enable-zoom'] ) && 1 == $settings['enable-zoom'] ) {
			/**
			 * The class responsible for defining zoom integration methods.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'zoom/class-ld-dashboard-zoom-integration.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/ld-dashboard-single-meeting.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/ld-dashboard-meeting-shortcode.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/ld-dashboard-embed-meeting.php';

			$init_zoom = new Zoom_Api();
		}
		
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ld-dashboard-time-tracking.php';
		$init_time_tracking = new Ld_Dashboard_Time_Tracking();
		
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ld-dashboard-export.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-course-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-lesson-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-topic-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-quiz-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-question-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-certificate-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-announcement-group-fields.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-share-course-field.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-dashboard-register-group-fields.php';

		$this->loader = new Ld_Dashboard_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ld_Dashboard_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ld_Dashboard_i18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ld_Dashboard_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ld_dashboard_menu_page', 20 );
		// Add commission meta box to course.
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ld_dashboard_add_post_commission_meta_box' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ld_dashboard_add_instructor_role' );
		$this->loader->add_action( 'init', $plugin_admin, 'ld_dashboard_nav_menus' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ld_dashboard_register_admin_setting' );

		$this->loader->add_action( 'update_option_ld_dashboard_zoom_meeting_settings', $plugin_admin, 'ld_dashboard_set_zoom_co_hosts', 10, 3 );

		// Ajax request to update individual instrcutor commission.
		$this->loader->add_action( 'wp_ajax_ld_ajax_update_instructor_commission', $plugin_admin, 'ld_ajax_update_instructor_commission' );

		// Ajax request to generate commission data.
		$this->loader->add_action( 'wp_ajax_ld_ajax_generate_instructor_data', $plugin_admin, 'ld_ajax_generate_instructor_data' );

		// Ajax request to pay unpaid amout.
		$this->loader->add_action( 'wp_ajax_ld_ajax_pay_instructor_amount', $plugin_admin, 'ld_ajax_pay_instructor_amount' );

		$this->loader->add_filter( 'wp_dropdown_users_args', $plugin_admin, 'ld_dashboard_dropdown_users_args' );

		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'ld_dahsboard_admin_pre_get_posts' );

		$this->loader->add_action( 'wp_ajax_set_frontend_form_fields', $plugin_admin, 'set_frontend_form_fields_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_load_instructors_modal', $plugin_admin, 'ld_dashboard_load_instructors_modal' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_add_instructors_to_course', $plugin_admin, 'ld_dashboard_add_instructors_to_course' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_detach_instructor', $plugin_admin, 'ld_dashboard_detach_instructor' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_set_instructor_role', $plugin_admin, 'ld_dashboard_set_instructor_role_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_instructors_listing', $plugin_admin, 'ld_dashboard_get_instructors_listing_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_add_new_instructor_user', $plugin_admin, 'ld_dashboard_add_new_instructor_user_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_set_instructor_commission', $plugin_admin, 'ld_dashboard_set_instructor_commission_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_process_withrawal_request', $plugin_admin, 'ld_dashboard_process_withrawal_request_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_custom_preset_fields', $plugin_admin, 'ld_dashboard_get_custom_preset_fields_callback' );

		$this->loader->add_action( 'save_post_sfwd-courses', $plugin_admin, 'ld_dashboard_save_course_instructor_meta', 10, 2 );

		$this->loader->add_filter( 'manage_withdrawals_posts_columns', $plugin_admin, 'ld_dashboard_add_withdrawal_custom_column', 10 );
		$this->loader->add_action( 'manage_withdrawals_posts_custom_column', $plugin_admin, 'ld_dashboard_add_withdrawal_custom_column_content', 10, 2 );

		$this->loader->add_filter( 'manage_zoom_meet_posts_columns', $plugin_admin, 'ld_dashboard_add_meeting_custom_column', 10 );
		$this->loader->add_action( 'manage_zoom_meet_posts_custom_column', $plugin_admin, 'ld_dashboard_add_meeting_custom_column_content', 10, 2 );

		$this->loader->add_filter( 'learndash_header_tab_menu', $plugin_admin, 'ld_dashboard_learndash_header_tab_menu', 10, 3 );
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'ld_dashboard_modify_withrawal_post_row_actions', 10, 2 );

		if ( is_user_logged_in() ) {
			$this->loader->add_action( 'acf/init', $plugin_admin, 'ld_dashboard_register_acf_groups', 999 );
		}

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ld_dashboard_meetings_meta_box' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'wbcom_hide_all_admin_notices_from_setting_page' );
		$this->loader->add_action( 'wp_ajax_reset_settings', $plugin_admin, 'ld_dashboard_reset_settings' );
		$this->loader->add_action( 'transition_post_status', $plugin_admin, 'ld_dashboard_course_publish', 10, 3 );
	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		global $ld_plugin_public;
		$ld_plugin_public           = $plugin_public = new Ld_Dashboard_Public( $this->get_plugin_name(), $this->get_version() );
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['general_settings'];
		$default_avatar             = true;
		if ( class_exists( 'BuddyPress' ) && isset( $settings['default-avatar'] ) && 1 == $settings['default-avatar'] ) {
			$default_avatar = false;
		}

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'ld_dashboard_register_shortcodes' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_course_details', $plugin_public, 'ld_dashboard_course_details' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_student_details', $plugin_public, 'ld_dashboard_student_details' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_save_tasks', $plugin_public, 'ld_dashboard_save_tasks' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_activity_rows_ajax', $plugin_public, 'ld_dashboard_activity_rows_ajax' );

		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_public, 'ld_update_instructor_meta_wc_course_order', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_public, 'ld_update_instructor_meta_wc_course_order', 10, 1 );
		$this->loader->add_action( 'woocommerce_payment_complete', $plugin_public, 'ld_update_instructor_meta_wc_course_order', 10, 1 );
		$this->loader->add_action( 'save_post_sfwd-transactions', $plugin_public, 'ld_update_instructor_meta_learndash_paypal_course_order', 10, 1 );
		$this->loader->add_action( 'learndash_transaction_created', $plugin_public, 'ld_update_instructor_meta_learndash_paypal_course_order', 10, 1 );

		$this->loader->add_filter( 'learndash_get_activity_query_args', $plugin_public, 'ld_dashboard_get_activity_query_args', 10, 1 );

		$this->loader->add_action( 'learndash_update_course_access', $plugin_public, 'ld_dashboard_update_author_earning', 10, 4 );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_couse_students', $plugin_public, 'ld_dashboard_couse_students' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_group_id_course_student', $plugin_public, 'ld_dashboard_group_id_course_student' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_email_send', $plugin_public, 'ld_dashboard_email_send' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_buddypress_message_send', $plugin_public, 'ld_dashboard_buddypress_message_send' );
		$this->loader->add_action( 'ld_dashboard_send_email', $plugin_public, 'ld_dashboard_send_single_email', 10, 4 );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_student_course_progress', $plugin_public, 'ld_dashboard_student_course_progress' );

		$this->loader->add_action( 'wp_loaded', $plugin_public, 'ld_dashboard_register_instructor' );
		$this->loader->add_action( 'wp_loaded', $plugin_public, 'ld_dashboard_apply_instructor' );
		$this->loader->add_filter( 'sfwd_lms_has_access', $plugin_public, 'ld_dashboard_auto_enroll_instructor_courses', 10, 3 );

		$this->loader->add_action( 'init', $plugin_public, 'add_endpoint' );
		$this->loader->add_action( 'query_vars', $plugin_public, 'query_vars' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_group_course_student', $plugin_public, 'ld_dashboard_group_course_student' );

		$this->loader->add_filter( 'learndash_assignment_permissions_redirect_url', $plugin_public, 'ld_dashboard_assignment_permissions_redirect_url', 99, 1 );

		$this->loader->add_filter( 'learndash_essay_permissions_redirect_url', $plugin_public, 'ld_dashboard_assignment_permissions_redirect_url', 99, 1 );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_remove_post', $plugin_public, 'ld_dashboard_remove_post_callback' );

		$this->loader->add_action( 'wp_ajax_get_student_quiz_attempt', $plugin_public, 'ld_dashboard_get_student_quiz_attempt_callback' );

		$this->loader->add_action( 'wp_ajax_ld_remove_user_avatar', $plugin_public, 'ld_remove_user_avatar_callback' );

		$this->loader->add_action( 'wp_ajax_ld_set_user_avatar', $plugin_public, 'ld_set_user_avatar_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_instructor_tab_content', $plugin_public, 'ld_dashboard_get_instructor_tab_content_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_course_lessons', $plugin_public, 'ld_dashboard_get_course_lessons_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_set_user_password', $plugin_public, 'ld_dashboard_set_user_password_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_course_lesson_quizzes', $plugin_public, 'ld_dashboard_get_course_lesson_quizzes_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_request_withdrawal', $plugin_public, 'ld_dashboard_request_withdrawal_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_instructor_earning_chart_data', $plugin_public, 'ld_dashboard_get_instructor_earning_chart_data_callback' );
		$this->loader->add_action( 'wp_ajax_course_complition_rate', $plugin_public, 'ld_dashboard_course_complition_rate_callback' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_get_top_courses_chart_data', $plugin_public, 'ld_dashboard_get_top_courses_chart_data_callback' );
		$this->loader->add_action( 'acf/save_post', $plugin_public, 'ld_dashboard_acf_on_save' );

		$this->loader->add_filter( 'acf/prepare_field', $plugin_public, 'ld_dashboard_prepare_acf_fields' );

		if ( $default_avatar ) {
			$this->loader->add_filter( 'pre_get_avatar_data', $plugin_public, 'ld_dashboard_get_avatar_data', 10, 2 );
			if ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) {
				$this->loader->add_filter( 'bp_core_fetch_avatar_url_check', $plugin_public, 'ld_dashboard_set_bb_avatar_callback', 10, 2 );
			}
		}

		$this->loader->add_filter( 'after_setup_theme', $plugin_public, 'ld_dashboard_set_avatar_sizes' );

		$this->loader->add_action( 'acf/render_field/name=sfwd-question_answer_cld', $plugin_public, 'ld_dashboard_acf_render_field' );

		$this->loader->add_action( 'acf/render_field/key=field_620239860ddd2', $plugin_public, 'ld_dashboard_acf_render_course_builder_field' );

		$this->loader->add_action( 'acf/render_field/name=ldd_post_content', $plugin_public, 'ld_dashboard_acf_render_course_field_61b6e31f0659d_field' );

		$this->loader->add_action( 'acf/render_field/key=field_620239860eee2', $plugin_public, 'ld_dashboard_acf_render_quiz_builder_field' );

		$this->loader->add_filter( 'acf/fields/post_object/query', $plugin_public, 'ld_dashboard_custom_course_object_query', 10, 3 );

		$this->loader->add_action( 'acf/render_field/key=field_61d7fdc7a6579', $plugin_public, 'ld_dashboard_acf_render_associated_lesson_field' );

		$this->loader->add_action( 'acf/render_field/key=field_61b7398cc2d56', $plugin_public, 'ld_dashboard_acf_render_associated_lesson_field' );

		$this->loader->add_action( 'acf/load_field/key=field_61b6e3040659c', $plugin_public, 'ld_dashboard_acf_render_course_status' );

		$this->loader->add_filter( 'learndash_settings_fields', $plugin_public, 'ld_dashboard_learndash_settings_fields', 10, 2 );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_tab_content_filter', $plugin_public, 'ld_dashboard_tab_content_filter_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_approve_assignment', $plugin_public, 'ld_dashboard_approve_assignment_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_set_as_instructor_pending', $plugin_public, 'ld_dashboard_set_as_instructor_pending_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_add_new_announcement', $plugin_public, 'ld_dashboard_add_new_announcement_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_display_announcement_content', $plugin_public, 'ld_dashboard_display_announcement_content_callback' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_save_withdraw_method', $plugin_public, 'ld_dashboard_save_withdraw_method_callback' );

		$this->loader->add_action( 'init', $plugin_public, 'ld_dashboard_allow_instructor_pending_role' );

		add_filter( 'acf/pre_render_fields', array( $this, 'ld_dashboard__acf_pre_render_fields' ), 10, 2 );

		add_action( 'acf/validate_save_post', array( $this, 'ld_dashboard_acf_validate_save_post' ) );

		add_action( 'wp_footer', array( $this, 'ld_dashboard_footer_content' ) );

		add_filter( 'ajax_query_attachments_args', array( $this, 'ld_dashboard_custom_media_query' ) );

		add_filter( 'the_content', array( $this, 'ld_dashboard_page_mapping_callback' ) );

		// add_filter( 'ld_dashboard_sidebar_tab_set', array( $this, 'ld_dashboard_sidebar_tab_set_callback' ), 10, 3 );

		$this->loader->add_action( 'wp_loaded', $plugin_public, 'ld_dashboard_set_acf_header', 10 );
		$this->loader->add_filter( 'learndash_shortcode_atts', $plugin_public, 'ld_dashboard_set_course_grid_courses' );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_save_course_lesson', $plugin_public, 'ld_dashboard_save_course_lesson' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_save_course_lesson_topic', $plugin_public, 'ld_dashboard_save_course_lesson_topic' );
		$this->loader->add_action( 'wp_ajax_ld_dashboard_save_course_lesson_quiz', $plugin_public, 'ld_dashboard_save_course_lesson_quiz' );
		add_action( 'wp_loaded', array( $this, 'ld_create_course_playlist' ) );

		$this->loader->add_action( 'wp_ajax_ld_dashboard_essay_approve_single', $plugin_public, 'ld_dashboard_essay_approve_single' );

		$this->loader->add_action( 'init', $plugin_public, 'ld_dashboard_render_youtube_data' );

		$this->loader->add_filter( 'acf/prepare_field/key=field_61d80a47926d1', $plugin_public, 'ld_dashboard_render_quiz_summary_field' );
		$this->loader->add_filter( 'acf/prepare_field/key=field_61d80a47926d2', $plugin_public, 'ld_dashboard_render_skip_question_field' );

		/**
		 * Add Body Classes
		 */
		$this->loader->add_filter( 'body_class', $plugin_public, 'ld_dashboard_body_classes' );
		
		$this->loader->add_action( 'wp_loaded', $plugin_public, 'ld_dashboard_invite_students_send',10 );
		$this->loader->add_action( 'register_new_user', $plugin_public, 'ld_dashboard_register_new_user_invitation',10 );
		$this->loader->add_filter( 'bp_core_validate_user_signup', $plugin_public, 'ld_dashboard_register_check_invitation',10 );
		$this->loader->add_filter( 'bp_core_activated_user', $plugin_public, 'ld_dashboard_bp_core_activate_account',10 );
		$this->loader->add_filter( 'bp_core_activate_account', $plugin_public, 'ld_dashboard_bp_core_activate_account',10 );
	}

	public function ld_create_course_playlist() {
		
		if ( isset( $_REQUEST['ld_course_wizard_create_course_btn'] ) ) {
			$course_nonce = wp_create_nonce( 'course-nonce' );
			$obj          = new Ld_Dashboard_Course_Playlist();
			$course_id    = $obj->create_course_action();
			$course_tab   = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' ) . '?action=edit-course&ld-course=' . $course_id . '&tab=my-courses&_lddnonce=' . $course_nonce;
			learndash_safe_redirect( $course_tab );
		}
	}

	public function ld_dashboard_sidebar_tab_set_callback( $restrict, $section, $item ) {
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['menu_options'];
		$current_user               = wp_get_current_user();
		$role_slug                  = '';
		if ( learndash_is_group_leader_user( $current_user->ID ) && ! in_array( 'ld_instructor', (array) $current_user->roles ) ) {
			$role_slug = 'group_leader';
		}
		if ( in_array( 'ld_instructor', (array) $current_user->roles ) ) {
			$role_slug = 'instructor';
		}
		if ( ! learndash_is_admin_user( $current_user->ID ) && '' === $role_slug ) {
			$role_slug = 'others';
		}
		if ( ! isset( $settings[ $role_slug ][ $section ][ $item ] ) || ( isset( $settings[ $role_slug ][ $section ][ $item ] ) && 1 == $settings[ $role_slug ][ $section ][ $item ] ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function ld_dashboard_footer_content() {
		//Bail off if screen is certificates
		if ( isset( $_GET['tab'] ) && 'certificates' === $_GET['tab'] ) {
			return;
		}

		if ( isset( $_GET['tab'] ) && isset( $_GET['action'] ) ) {
			echo '<div id="learndash_shortcodes_holder" style="display: none;"><div id="learndash_shortcodes"></div></div>';
		}

		if ( isset( $_GET['action'] ) && isset( $_GET['tab'] ) && isset( $_GET['is_submit'] ) && 1 == $_GET['is_submit'] ) {
			$post_id           = 0;
			$current_post_type = '';
			$post_types        = array( 'ld-course', 'ld-lesson', 'ld-quiz', 'ld-topic', 'ld-question' );
			$params            = array_keys( $_GET );
			$exists            = false;

			foreach ( $post_types as $post_type ) {
				if ( in_array( $post_type, $params ) ) {
					$current_post_type = str_replace( 'ld-', '', $post_type );
					$post_id           = ( isset( $_GET[ $post_type ] ) ) ? sanitize_text_field( wp_unslash( $_GET[ $post_type ] ) ) : 0;
					break;
				}
			}
			$transient_name = 'ld_dashboard_post_' . $post_id . '_saved';

			if ( false !== get_transient( $transient_name ) ) {
				$exists = true;
				delete_transient( $transient_name );
			} else {
				set_transient( $transient_name, '1', 60 );
			}

			if ( ! $exists && (int) $post_id > 0 ) {
				$post_type_string = LearnDash_Custom_Label::get_label( $current_post_type );

				echo '<div class="ld-dashboard-submit-msg-wrapper"><div class="ld-dashboard-submit-msg-content">';
				if ( 'question' !== $current_post_type ) {
					if ( isset( $_GET['action'] ) && str_contains( wp_unslash( $_GET['action'] ), 'add' ) ) {
						echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '" class="ld-dashboard-submit-msg-link"><span class="ld-dashboard-submit-msg-text">' . esc_html( $post_type_string ) . ' ' . esc_html__( 'Published', 'ld-dashboard' ) . esc_html__( ' View', 'ld-dashboard' ) . '</span></a>';
					} elseif ( isset( $_GET['action'] ) && str_contains( wp_unslash( $_GET['action'] ), 'edit' ) ) {
						echo '<a href="' . esc_url( get_preview_post_link( $post_id ) ) . '" class="ld-dashboard-submit-msg-link"><span class="ld-dashboard-submit-msg-text">' . esc_html( $post_type_string ) . ' ' . esc_html__( 'updated', 'ld-dashboard' ) . ' - ' . esc_html__( ' View', 'ld-dashboard' ) . '</span></a>';
					}
				}
				echo '</div></div>';
			}
		}
	}

	/**
	 * Ld_dashboard_page_mapping_callback
	 *
	 * @param  mixed $content context.
	 */
	public function ld_dashboard_page_mapping_callback( $content ) {
		global $post;
		if ( ! is_object( $post ) ) {
			return $content;
		}
		$function_obj = Ld_Dashboard_Functions::instance();
		$options      = $function_obj->ld_dashboard_settings_data();
		if ( 'zoom_meet' === $post->post_type ) {
			ob_start();
			echo do_shortcode( '[ld_dashboard_meeting_single id=' . $post->ID . ']' );
			return ob_get_clean();
		}
		if ( 'page' !== $post->post_type ) {
			return $content;
		}
		if ( array_key_exists( 'my_dashboard_page', $options['general_settings'] ) && isset( $options['general_settings']['my_dashboard_page'] ) ) {
			$page = $options['general_settings']['my_dashboard_page'];
			if ( $page == $post->ID ) {
				if ( ! has_shortcode( $post->post_content, 'ld_dashboard' ) ) {
					$content .= do_shortcode( '[ld_dashboard]' );
				}
			}
		}

		if ( array_key_exists( 'instructor_registration_page', $options['general_settings'] ) && isset( $options['general_settings']['instructor_registration_page'] ) ) {
			$page = $options['general_settings']['instructor_registration_page'];
			if ( $page == $post->ID ) {
				if ( ! has_shortcode( $post->post_content, 'ld_instructor_registration' ) ) {
					$content .= do_shortcode( '[ld_instructor_registration]' );
				}
			}
		}

		if ( array_key_exists( 'instructor_listing_page', $options['general_settings'] ) && isset( $options['general_settings']['instructor_listing_page'] ) ) {
			$instructor_page = $options['general_settings']['instructor_listing_page'];
			if ( $instructor_page == $post->ID ) {
				$instructor_username = get_query_var( 'instructor_id', '' );
				if ( '' !== $instructor_username ) {
					$content = do_shortcode( '[ld_dashboard_instructors_list]' );
				}
			}
		}

		return $content;
	}

	public function ld_dashboard_custom_media_query( $args ) {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( ! in_array( 'administrator', $user->roles ) ) {
				$args['author'] = get_current_user_id();
			}
		}
		return $args;
	}

	public function ld_dashboard__acf_pre_render_fields( $fields, $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( 'sfwd-courses' === $post_type ) {
			$data = get_post_meta( $post_id, '_sfwd-courses', true );
		} elseif ( 'sfwd-lessons' === $post_type ) {
			$data = get_post_meta( $post_id, '_sfwd-lessons', true );
		} elseif ( 'sfwd-topic' === $post_type ) {
			$data = get_post_meta( $post_id, '_sfwd-topic', true );
		} elseif ( 'sfwd-quiz' === $post_type ) {
			$data = get_post_meta( $post_id, '_sfwd-quiz', true );
		} elseif ( 'sfwd-question' === $post_type ) {
			$data = get_post_meta( $post_id, '_sfwd-question', true );
		} elseif ( 'sfwd-certificates' === $post_type ) {
			$data = get_post_meta( $post_id, 'learndash_certificate_options', true );
		} elseif ( 'groups' === $post_type ) {
			$data = get_post_meta( $post_id, '_groups', true );
		} else {
			return $fields;
		}

		$total_secs = ( isset( $data['sfwd-quiz_timeLimit'] ) ) ? $data['sfwd-quiz_timeLimit'] : 0;
		$hr         = 0;
		$min        = 0;
		$sec        = 0;
		if ( $total_secs > 0 ) {
			$hr            = floor( $total_secs / 3600 );
			$minute_in_sec = $total_secs % 3600;
			$min           = floor( $minute_in_sec / 60 );
			$sec           = $minute_in_sec % 60;
		}

		foreach ( $fields as $key => $field ) {
			if ( strpos( $field['name'], 'ldd_' ) !== false ) {
				$post_data = get_post( $post_id );
				$changed   = false;
				if ( 'ldd_post_title' === $field['name'] ) {
					$value   = $post_data->post_title;
					$changed = true;
				}
				if ( 'ldd_topic_status' === $field['name'] ) {
					$value   = $post_data->post_status;
					$changed = true;
				}
				if ( 'ldd_post_content' === $field['name'] ) {
					$value   = $post_data->post_content;
					$changed = true;
				}
				if ( 'ldd_excerpt' === $field['name'] ) {
					$value   = $post_data->post_excerpt;
					$changed = true;
				}
				if ( $changed ) {
					unset( $fields[ $key ]['value'] );
					$fields[ $key ]['value'] = $value;
				}
			}

			if ( 'pdf_page_format' === $field['name'] ) {
				if ( isset( $data['pdf_page_format'] ) ) {
					unset( $fields[ $key ]['value'] );
					$fields[ $key ]['value'] = $data['pdf_page_format'];
				}
			}
			if ( 'pdf_page_orientation' === $field['name'] ) {
				if ( isset( $data['pdf_page_orientation'] ) ) {
					unset( $fields[ $key ]['value'] );
					$fields[ $key ]['value'] = $data['pdf_page_orientation'];
				}
			}

			if ( 'quiz_timeLimit_timer_hr' === $field['name'] ) {
				unset( $fields[ $key ]['value'] );
				$fields[ $key ]['value'] = $hr;
			}
			if ( 'quiz_timeLimit_timer_min' === $field['name'] ) {
				unset( $fields[ $key ]['value'] );
				$fields[ $key ]['value'] = $min;
			}
			if ( 'quiz_timeLimit_timer_sec' === $field['name'] ) {
				unset( $fields[ $key ]['value'] );
				$fields[ $key ]['value'] = $sec;
			}
			if ( strpos( $field['name'], 'sfwd-' ) !== false ) {
				$name = str_replace( '_cld', '', $field['name'] );
				if ( isset( $data[ $name ] ) ) {
					$value = $data[ $name ];
					unset( $fields[ $key ]['value'] );
					$fields[ $key ]['value'] = $value;
				}
			} else {
				$name = str_replace( '_cld', '', $field['name'] );
				if ( isset( $data[ $name ] ) ) {
					$value = $data[ $name ];
					unset( $fields[ $key ]['value'] );
					$fields[ $key ]['value'] = $value;
				}
			}
			if ( 'group_courses_cld' === $field['name'] ) {
				$group_course_ids = learndash_group_enrolled_courses( $post_id );
				unset( $fields[ $key ]['value'] );
				$fields[ $key ]['value'] = $group_course_ids;
			}
			if ( 'group_users_cld' === $field['name'] ) {
				$group_user_ids = learndash_get_groups_user_ids( $post_id );
				unset( $fields[ $key ]['value'] );
				$fields[ $key ]['value'] = $group_user_ids;
			}
			if ( 'group_leaders_cld' === $field['name'] ) {
				$group_leader_ids = learndash_get_groups_administrator_ids( $post_id );
				unset( $fields[ $key ]['value'] );
				$fields[ $key ]['value'] = $group_leader_ids;
			}
		}
		return $fields;
	}

	public function ld_dashboard_acf_validate_save_post() {
		if ( isset( $_POST['acf'] ) ) {
			if ( isset( $_POST['acf'] ) ) {
				$fields = wp_unslash( $_POST['acf'] );
				foreach ( $fields as $key => $value ) {
					if ( '' == $value ) {
						if ( false !== strpos( $key, 'field_' ) ) {
							$field = get_field_object( $key );
							if ( 1 == $field['required'] ) {
								acf_add_validation_error( '', $field['label'] . esc_html__( ' cannot be empty.', 'ld-dashboard' ) );
							}
						}
					}
				}
			}
		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ld_Dashboard_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
