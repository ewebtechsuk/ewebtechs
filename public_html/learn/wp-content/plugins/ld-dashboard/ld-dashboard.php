<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wbcomdesigns.com/plugins
 * @since             1.0.0
 * @package           Ld_Dashboard
 *
 * @wordpress-plugin
 * Plugin Name:       Learndash Dashboard
 * Plugin URI:        https://wbcomdesigns.com/downloads/learndash-dashboard/
 * Description:       This plugin creates a dashboard panel for Learndash instructors and students. The instructors can manage the courses, view their courses progress and student logs.
 * Version:           6.4.1
 * Author:            Wbcom Designs
 * Author URI:        https://wbcomdesigns.com/plugins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ld-dashboard
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
update_site_option( 'edd_wbcom_ldd_license_key', 'xxxxxxxxxxxxxxxxxxxxxxxxx' );
update_site_option( 'edd_wbcom_ldd_license_status', 'valid' );
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LD_DASHBOARD_VERSION', '6.4.1' );

define( 'LD_DASHBOARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LD_DASHBOARD_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
if ( ! defined( 'LD_DASHBOARD_PLUGIN_FILE' ) ) {
	define( 'LD_DASHBOARD_PLUGIN_FILE', __FILE__ );
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ld-dashboard-activator.php
 */
function activate_ld_dashboard() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ld-dashboard-activator.php';
	Ld_Dashboard_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ld-dashboard-deactivator.php
 */
function deactivate_ld_dashboard() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ld-dashboard-deactivator.php';
	Ld_Dashboard_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ld_dashboard' );
register_deactivation_hook( __FILE__, 'deactivate_ld_dashboard' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ld-dashboard.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ld_dashboard() {

	require plugin_dir_path( __FILE__ ) . 'edd-license/edd-plugin-license.php';
	$plugin = new Ld_Dashboard();
	$plugin->run();

}
// run_ld_dashboard();

/**
 * Include needed files if required plugin is active
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
add_action( 'plugins_loaded', 'ld_dashboard_plugin_init' );
function ld_dashboard_plugin_init() {
	if ( ! class_exists( 'ACF' ) || ! class_exists( 'SFWD_LMS' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'ld_dashboard_admin_notice' );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	} else {
		run_ld_dashboard();
	}
}

add_action( 'init', 'ld_dashboard_register_custom_post_type' );

function ld_dashboard_register_custom_post_type() {

	// Register Withdrawal post type
	$withdrawals_labels = array(
		'name'               => _x( 'Withdrawals', 'Post Type General Name', 'ld-dashboard' ),
		'singular_name'      => _x( 'Withdrawal', 'Post Type Singular Name', 'ld-dashboard' ),
		'menu_name'          => __( 'Withdrawals', 'ld-dashboard' ),
		'parent_item_colon'  => __( 'Parent Withdrawal', 'ld-dashboard' ),
		'all_items'          => __( 'All Withdrawals', 'ld-dashboard' ),
		'view_item'          => __( 'View Withdrawal', 'ld-dashboard' ),
		'add_new_item'       => __( 'Add New Withdrawal Request', 'ld-dashboard' ),
		'add_new'            => __( 'Add New', 'ld-dashboard' ),
		'edit_item'          => __( 'Edit Withdrawal Request', 'ld-dashboard' ),
		'update_item'        => __( 'Update Withdrawal Request', 'ld-dashboard' ),
		'search_items'       => __( 'Search Withdrawal', 'ld-dashboard' ),
		'not_found'          => __( 'Not Found', 'ld-dashboard' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'ld-dashboard' ),
	);

	$withdrawals_args = array(
		'label'              => __( 'withdrawals', 'ld-dashboard' ),
		'description'        => __( 'Withdrawals requests', 'ld-dashboard' ),
		'labels'             => $withdrawals_labels,
		'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'revisions', 'custom-fields' ),
		'taxonomies'         => array(),
		'hierarchical'       => false,
		'public'             => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-money-alt',
		'publicly_queryable' => true,
		'capability_type'    => 'post',
		'show_in_rest'       => true,
	);
	register_post_type( 'withdrawals', $withdrawals_args );

	$announcements_labels = array(
		'name'               => _x( 'Announcements', 'Post Type General Name', 'ld-dashboard' ),
		'singular_name'      => _x( 'Announcement', 'Post Type Singular Name', 'ld-dashboard' ),
		'menu_name'          => __( 'Announcements', 'ld-dashboard' ),
		'parent_item_colon'  => __( 'Parent Movie', 'ld-dashboard' ),
		'all_items'          => __( 'All Announcements', 'ld-dashboard' ),
		'view_item'          => __( 'View Announcement', 'ld-dashboard' ),
		'add_new_item'       => __( 'Add New Announcement', 'ld-dashboard' ),
		'add_new'            => __( 'Add New', 'ld-dashboard' ),
		'edit_item'          => __( 'Edit Announcement', 'ld-dashboard' ),
		'update_item'        => __( 'Update Announcement', 'ld-dashboard' ),
		'search_items'       => __( 'Search Announcement', 'ld-dashboard' ),
		'not_found'          => __( 'Not Found', 'ld-dashboard' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'ld-dashboard' ),
	);

	$announcements_args = array(
		'label'              => __( 'announcements', 'ld-dashboard' ),
		'description'        => __( 'Announcements for students', 'ld-dashboard' ),
		'labels'             => $announcements_labels,
		'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),
		'taxonomies'         => array(),
		'hierarchical'       => false,
		'public'             => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-megaphone',
		'publicly_queryable' => true,
		'capability_type'    => 'post',
		'show_in_rest'       => true,
	);
	register_post_type( 'announcements', $announcements_args );

}

/**
 * Show admin notice when Learndash not active or install.
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function ld_dashboard_admin_notice() {
	?>
	<div class="error notice is-dismissible">
		<p><?php echo sprintf( __( 'The %1$s plugin requires %2$s and %3$s plugin to be installed and active.', 'ld-dashboard' ), '<b>LearnDash Dashboard</b>', '<b>LearnDash</b>', '<b><a href="https://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a></b>' ); ?></p>
	</div>
	<?php
	// The LearnDash Dashboard plugin requires LearnDash and Advanced Custom Fields plugin to be installed and active.
}

add_action( 'admin_init', 'ld_dashboard_update_admin_init' );
/*
 * Update To save wdm instructor id into ld instructor id.
 */
function ld_dashboard_update_admin_init() {
	global $wpdb, $pagenow;
	$update_ld_dashboard = get_option( 'update_ld_dashboard' );
	if ( ! $update_ld_dashboard && ( $pagenow == 'plugins.php' || ( isset( $_GET['page'] ) && $_GET['page'] == 'ld-dashboard-settings' ) ) ) {

		ld_dashboard_update_wdm_instructor_to_ld_instructor();
		update_option( 'update_ld_dashboard', true );
	}
}

function ld_dashboard_update_wdm_instructor_to_ld_instructor() {
	$args   = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'ir_shared_instructor_ids',
				'value'   => '',
				'compare' => '!=',
			),
		),
	);
	$course = new WP_Query( $args );
	if ( $course->have_posts() ) {

		while ( $course->have_posts() ) {
			$course->the_post();
			$_ld_instructor_ids = get_post_meta( get_the_ID(), '_ld_instructor_ids', true );
			if ( empty( $_ld_instructor_ids ) ) {
				$_ld_instructor_ids = array();
			}
			$ir_shared_instructor_ids = get_post_meta( get_the_ID(), 'ir_shared_instructor_ids', true );

			if ( $ir_shared_instructor_ids != '' ) {
				$ir_shared_instructor_ids = explode( ',', $ir_shared_instructor_ids );

				foreach ( $ir_shared_instructor_ids as $user_id ) {
					$ld_user = new WP_User( $user_id );
					$ld_user->add_role( 'ld_instructor' );
				}
			} else {
				$ir_shared_instructor_ids = array();
			}

			$_ld_instructor_ids = array_merge( $ir_shared_instructor_ids, $_ld_instructor_ids );
			update_post_meta( get_the_ID(), '_ld_instructor_ids', array_unique( $_ld_instructor_ids ) );
		}
		wp_reset_postdata();
	}

	$args = array(
		'orderby'  => 'user_nicename',
		'role__in' => 'wdm_instructor',
		'order'    => 'ASC',
		'fields'   => array( 'ID', 'display_name' ),
	);

	$instructors = get_users( $args );
	if ( ! empty( $instructors ) ) {
		foreach ( $instructors as $instructor ) {
			$ld_user = new WP_User( $instructor->ID );
			$ld_user->add_role( 'ld_instructor' );
		}
	}
}

/*
 * Added Plugin settings Link
 */
function ld_dashboard_settings_link( $links ) {
	$links['settings'] = '<a href="' . admin_url( 'admin.php?page=ld-dashboard-settings&tab=ld-dashboard-general' ) . '">' . __( 'Settings', 'ld-dashboard' ) . '</a>';
	return $links;
}

add_filter( 'plugin_action_links_ld-dashboard/ld-dashboard.php', 'ld_dashboard_settings_link' );


/**
 * Find and replace usermeta.meta_key = 'course_{ to usermeta.meta_key LIKE 'course_{
 */
function ld_dashboard_user_queries( $user_query ) {
	global $wpdb;
	if ( strpos( $user_query->query_where, "usermeta.meta_key = 'course_{" ) ) {
		$user_query->query_fields = str_replace( "{$wpdb->prefix}users.ID", "DISTINCT {$wpdb->prefix}users.ID ", $user_query->query_fields );
		$user_query->query_where  = str_replace( "usermeta.meta_key = 'course_{", "usermeta.meta_key LIKE 'course_{", $user_query->query_where );
	}
}

add_action( 'activated_plugin', 'ld_dashboard_activation_redirect_settings' );

/**
 * Redirect to plugin settings page after activated
 *
 * @param plugin $plugin plugin.
 */
function ld_dashboard_activation_redirect_settings( $plugin ) {
	if ( ! isset( $_GET['plugin'] ) ) {
		return;
	}
	if ( $plugin == plugin_basename( __FILE__ ) && class_exists( 'ACF' ) && class_exists( 'SFWD_LMS' ) ) {
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'activate' && isset( $_REQUEST['plugin'] ) && $_REQUEST['plugin'] == $plugin ) {
			wp_redirect( admin_url( 'admin.php?page=ld-dashboard-settings' ) );
			exit;
		}
	}
}



/*
 * Create Instructor Commission Logs table
 *
 */
add_action( 'admin_init', 'ld_dashboard_create_instructor_commision_logs' );
function ld_dashboard_create_instructor_commision_logs() {
	global $wpdb, $pagenow;

	if ( $pagenow == 'plugins.php' || ( isset( $_GET['page'] ) && $_GET['page'] == 'ld-dashboard-settings' ) && isset( $_GET['tab'] ) && $_GET['tab'] == 'ld-dashboard-welcome' ) {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();

		/* Create LearnDash Dashboard Email Logs table */
		$ld_dashboard_instructor_commission_logs = $wpdb->prefix . 'ld_dashboard_instructor_commission_logs';
		if ( $wpdb->get_var( "show tables like '$ld_dashboard_instructor_commission_logs'" ) != $ld_dashboard_instructor_commission_logs ) {

			$instructor_commission_logs_sql = "CREATE TABLE $ld_dashboard_instructor_commission_logs (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						user_id bigint(20) NOT NULL,
						course_id bigint(20) NOT NULL,
						course_price text NOT NULL,
						commission text NOT NULL,
						commission_rate text NOT NULL,
						commission_type text NOT NULL,
						fees_type text NOT NULL,
						fees_amount text NULL,
						source_type text NULL,
						reference text NULL,
						coupon text NULL,
						created DATETIME NOT NULL,
						UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $instructor_commission_logs_sql );
		}

		/* Create LearnDash Dashboard Time tracking table */
		$ld_dashboard_time_tracking = $wpdb->prefix . 'ld_dashboard_time_tracking';
		if ( $wpdb->get_var( "show tables like '$ld_dashboard_time_tracking'" ) != $ld_dashboard_time_tracking ) {

			$ld_dashboard_time_tracking_sql = "CREATE TABLE $ld_dashboard_time_tracking (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				course_id bigint(20) unsigned NOT NULL DEFAULT '0',
				post_id bigint(20) unsigned NOT NULL DEFAULT '0',
				user_id bigint(20) unsigned NOT NULL DEFAULT '0',
				time_spent bigint(20) unsigned DEFAULT NULL,
				activity_updated int(11) unsigned DEFAULT NULL,
				created DATETIME NOT NULL,
			  	PRIMARY KEY  (id),
			  	KEY user_id (user_id),
			  	KEY post_id (post_id),
				KEY course_id (course_id),
			  	KEY activity_updated (activity_updated)
			) $charset_collate;";
			dbDelta( $ld_dashboard_time_tracking_sql );
		}

		/* Create LearnDash Dashboard Invite user table */
		$ld_dashboard_invite_user = $wpdb->prefix . 'ld_dashboard_invite_user';
		if ( $wpdb->get_var( "show tables like '$ld_dashboard_invite_user'" ) != $ld_dashboard_invite_user ) {

			$ld_dashboard_invite_user_sql = "CREATE TABLE $ld_dashboard_invite_user (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL DEFAULT '0',
				courses text NOT NULL,
				invited_email varchar(100) NOT NULL default '',
				invited_email_status varchar(100) NOT NULL default '',
				invite_accepted varchar(100) NOT NULL default '',
				invite_accepted_date datetime NOT NULL default '0000-00-00 00:00:00',
				created datetime NOT NULL default '0000-00-00 00:00:00',
			  	PRIMARY KEY  (id),
			  	KEY user_id (user_id)				
			) $charset_collate;";
			

			dbDelta( $ld_dashboard_invite_user_sql );			
		}
	}
}

add_action( 'admin_init', 'ld_dashboard_options_migration', 99 );
/**
 * Function migrate menu options to new array
 *
 * @return void
 */
function ld_dashboard_options_migration() {
	global $wpdb, $pagenow;

	if ( $pagenow == 'plugins.php' || ( isset( $_GET['page'] ) && $_GET['page'] == 'ld-dashboard-settings' ) ) {
		$menu_options   = get_option( 'ld_dashboard_menu_options' );
		$tiles_options  = get_option( 'ld_dashboard_tiles_options' );
		$ldd_flag       = get_option( 'ldd_options_flag' );
		$role_menu_flag = get_option( 'ld_dashboard_role_menu_flag' );
		$menus          = array();

		if ( ! empty( $menu_options ) && false === $ldd_flag && 'yes' !== $ldd_flag ) {
			$flag_check = false;
			if ( ! function_exists( 'ld_dashboard_get_dashboard_user_roles' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'includes/ld-dashboard-functions.php';
			}
			foreach ( ld_dashboard_get_dashboard_user_roles() as $role_key => $role ) {
				$role = str_replace( '-', '_', sanitize_title( $role ) );
				if ( isset( $menu_options ) && ! empty( $menu_options ) ) {
					foreach ( $menu_options as $options ) {
						if ( is_array( $options ) && ! empty( $options ) ) {
							foreach ( $options as $key => $option ) {
								if ( 'my-dashboard' === $key ) {
									$option = 1;
								}

								if ( 'instructor' === $role ) {
									$role = 'ld_instructor';
								}

								$opt_arr[ $key ] = $option;
								$menus[ $role ]  = $opt_arr;
								$flag_check      = true;
							}
						}
					}
				} elseif ( 'administrator' === $role ) {
					foreach ( $menu_options as $options ) {
						foreach ( $options as $key => $option ) {
							if ( 'my-dashboard' === $key ) {
								$option = 1;
							}
							$opt_arr[ $key ] = 1;
							$menus[ $role ]  = $opt_arr;
							$flag_check      = true;
						}
					}
				}
			}

			if ( $flag_check ) {
				update_option( 'ld_dashboard_menu_options', $menus );
				update_option( 'ldd_options_flag', 'yes' );
			}
		} elseif ( false === $role_menu_flag && 'yes' !== $role_menu_flag ) {
			$flag_check = false;
			if ( ! function_exists( 'ld_dashboard_get_dashboard_user_roles' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'includes/ld-dashboard-functions.php';
			}
			foreach ( ld_dashboard_get_dashboard_user_roles() as $role_key => $role ) {
				$role = str_replace( '-', '_', sanitize_title( $role ) );

				if ( isset( $menu_options ) && ! empty( $menu_options ) ) {
					foreach ( $menu_options as $options ) {
						if ( is_array( $options ) && ! empty( $options ) ) {
							foreach ( $options as $key => $option ) {
								$opt_arr[ $key ] = $option;
								if ( ! isset( $menu_options[ $role ] ) && empty( $menu_options[ $role ] ) ) {
									if ( 'my-dashboard' === $key ) {
										$option = 1;
									}

									if ( 'instructor' === $role ) {
										$role = 'ld_instructor';
									}

									$menus[ $role ] = $opt_arr;
									$flag_check     = true;
								}
							}
						}
					}
				}
			}
			if ( $flag_check && ! empty( $menus ) ) {
				$dashboard_menus = array_merge( $menu_options, $menus );
				update_option( 'ld_dashboard_menu_options', $dashboard_menus );
				update_option( 'ld_dashboard_role_menu_flag', 'yes' );
			}
		}

		if ( ! empty( $tiles_options ) && false === $ldd_flag && 'yes' !== $ldd_flag ) {
			$flag_check    = true;
			$tiles_options = array(
				'instructor-total-sales'          => '1',
				'instructor-total-sales-bgcolor'  => '#3a3a46',
				'course-count'                    => '1',
				'course-count-bgcolor'            => '#3a3a46',
				'quizzes-count'                   => '1',
				'quizzes-count-bgcolor'           => '#3a3a46',
				'assignments-count'               => '1',
				'assignments-completed-count'     => '1',
				'assignments-count-bgcolor'       => '#3a3a46',
				'essays-pending-count'            => '1',
				'essays-pending-count-bgcolor'    => '#3a3a46',
				'lessons-count'                   => '1',
				'lessons-count-bgcolor'           => '#3a3a46',
				'topics-count'                    => '1',
				'topics-count-bgcolor'            => '#3a3a46',
				'student-count'                   => '1',
				'student-count-bgcolor'           => '#3a3a46',
				'ins-earning'                     => '1',
				'ins-earning-bgcolor'             => '#3a3a46',
				'total-earning'                   => '1',
				'total-earning-bgcolor'           => '#3a3a46',
				'enrolled_courses_count'          => 1,
				'enrolled_courses_count_bgcolor'  => '#3a3a46',
				'active_courses_count'            => 1,
				'active_courses_count_bgcolor'    => '#3a3a46',
				'completed_courses_count'         => 1,
				'completed_courses_count_bgcolor' => '#3a3a46',
			);

			if ( $flag_check ) {
				update_option( 'ld_dashboard_tiles_options', $tiles_options );
				update_option( 'ldd_options_flag', 'yes' );
			}
		}
	}

}

add_action( 'admin_init', 'ld_dashboard_save_default_options' );
/**
 * Save default options on a new key in options table
 * We save default options for reset settings button on each
 * option page
 *
 * @since 6.0.3
 * @return void
 */
function ld_dashboard_save_default_options() {

	/**
	 * LD Dashboard Genral Option Tab Settings
	 */
	if ( false === get_option( 'ld_dashboard_general_settings_default' ) ) {
		$general_settings = array(
			'welcome-screen'                 => 1,
			'statistics-tiles'               => 1,
			'course-progress'                => 1,
			'student-details'                => 1,
			'enable-announcements'           => 1,
			'instructor-statistics'          => 1,
			'enable-email-integration'       => 0,
			'enable-messaging-integration'   => 0,
			'display-to-do'                  => 0,
			'become-instructor-button'       => 1,
			'course-completion-report'       => 1,
			'top-courses-report'             => 1,
			'my_dashboard_page'              => ( is_object( $my_dashboard_page ) ) ? $my_dashboard_page->ID : $my_dashboard_page,
			'instructor_registration_page'   => ( is_object( $instructor_registration_page ) ) ? $instructor_registration_page->ID : $instructor_registration_page,
			'instructor_listing_page'        => ( is_object( $instructor_listing_page ) ) ? $instructor_listing_page->ID : $instructor_listing_page,
			'statistics-tiles-allwoed-roles' => array( 'administrator', 'group_leader', 'ld_instructor', 'other' ),
			'course-progress-roles'          => array( 'administrator', 'group_leader', 'ld_instructor', 'other' ),
			'student-details-roles'          => array( 'administrator', 'group_leader', 'ld_instructor' ),
			'course-completion-report-roles' => array( 'administrator', 'group_leader', 'ld_instructor' ),
			'top-courses-report-roles'       => array( 'administrator', 'group_leader', 'ld_instructor' ),
		);
		update_option( 'ld_dashboard_general_settings_default', $general_settings, false );
	}

	/**
	 * LD Dashboard Design Option Tab Preset Colors Settings
	 */
	if ( empty( get_option( 'ld_dashboard_design_settings_default' ) ) ) {
		$ld_dashboard_default_design_settings = array(
			'preset'      => 'default',
			'color'       => '#156AE9',
			'hover_color' => '#1d76da',
			'text_color'  => '#515b67',
			'background'  => '#F8F8FB',
			'border'      => '#dcdfe5',
		);
		update_option( 'ld_dashboard_design_settings_default', $ld_dashboard_default_design_settings, false );
	}

	/**
	 * LD Dashboard Design Option Tab Colors Settings
	 */
	if ( false === get_option( 'ld_dashboard_default_design_settings_default' ) ) {
		$default_design_settings = array(
			'preset'      => 'default',
			'color'       => '#156AE9',
			'hover_color' => '#1d76da',
			'text_color'  => '#515b67',
			'background'  => '#F8F8FB',
			'border'      => '#dcdfe5',
		);
		update_option( 'ld_dashboard_default_design_settings_default', $default_design_settings, false );
	}

	/**
	 * LD Dashboard Dashboard Tiles Option Tab Settings
	 */
	if ( false === get_option( 'ld_dashboard_tiles_options_default' ) ) {
		$tiles_options = array(
			'instructor-total-sales'          => '1',
			'instructor-total-sales-bgcolor'  => '#3a3a46',
			'course-count'                    => '1',
			'course-count-bgcolor'            => '#3a3a46',
			'quizzes-count'                   => '1',
			'quizzes-count-bgcolor'           => '#3a3a46',
			'assignments-count'               => '1',
			'assignments-completed-count'     => '1',
			'assignments-count-bgcolor'       => '#3a3a46',
			'essays-pending-count'            => '1',
			'essays-pending-count-bgcolor'    => '#3a3a46',
			'lessons-count'                   => '1',
			'lessons-count-bgcolor'           => '#3a3a46',
			'topics-count'                    => '1',
			'topics-count-bgcolor'            => '#3a3a46',
			'student-count'                   => '1',
			'student-count-bgcolor'           => '#3a3a46',
			'ins-earning'                     => '1',
			'ins-earning-bgcolor'             => '#3a3a46',
			'total-earning'                   => '1',
			'total-earning-bgcolor'           => '#3a3a46',
			'enrolled_courses_count'          => 1,
			'enrolled_courses_count_bgcolor'  => '#3a3a46',
			'active_courses_count'            => 1,
			'active_courses_count_bgcolor'    => '#3a3a46',
			'completed_courses_count'         => 1,
			'completed_courses_count_bgcolor' => '#3a3a46',
		);
		update_option( 'ld_dashboard_tiles_options_default', $tiles_options, false );
	}

	/**
	 * LD Dashboard Dashboard Menues Option Tab Settings
	 */
	if ( false === get_option( 'ld_dashboard_menu_options_default' ) ) {
		$menu_options = array(
			'administrator' => array(
				'my-dashboard'      => 1,
				'profile'           => 1,
				'enrolled-courses'  => 0,
				'my-quiz-attempts'  => 0,
				'my-activity'       => 0,
				'announcements'     => 0,
				'my-courses'        => 1,
				'my-lessons'        => 1,
				'my-topics'         => 1,
				'my-quizzes'        => 1,
				'my-questions'      => 1,
				'assignments'       => 1,
				'meetings'          => 1,
				'withdrawal'        => 0,
				'earnings'          => 0,
				'certificates'      => 1,
				'my-announcements'  => 1,
				'groups'            => 0,
				'essay-report'      => 1,
				'assignment-report' => 1,
				'quizz-report'      => 1,
				'quiz-attempts'     => 1,
				'submitted-essays'  => 1,
				'course-report'     => 1,
				'activity'          => 1,
				'notification'      => 1,
				'private-messages'  => 1,
				'settings'          => 1,
				'logout'            => 1,
			),
			'group_leader'  => array(
				'my-dashboard'      => 1,
				'profile'           => 1,
				'enrolled-courses'  => 0,
				'my-quiz-attempts'  => 0,
				'my-activity'       => 0,
				'announcements'     => 0,
				'my-courses'        => 1,
				'my-lessons'        => 1,
				'my-topics'         => 1,
				'my-quizzes'        => 1,
				'my-questions'      => 1,
				'assignments'       => 1,
				'meetings'          => 0,
				'withdrawal'        => 0,
				'earnings'          => 0,
				'certificates'      => 1,
				'my-announcements'  => 1,
				'groups'            => 1,
				'essay-report'      => 1,
				'assignment-report' => 1,
				'quizz-report'      => 1,
				'quiz-attempts'     => 1,
				'submitted-essays'  => 1,
				'course-report'     => 1,
				'activity'          => 1,
				'notification'      => 1,
				'private-messages'  => 1,
				'settings'          => 1,
				'logout'            => 1,
			),

			'ld_instructor' => array(
				'my-dashboard'      => 1,
				'profile'           => 1,
				'enrolled-courses'  => 0,
				'my-quiz-attempts'  => 0,
				'my-activity'       => 0,
				'announcements'     => 0,
				'my-courses'        => 1,
				'my-lessons'        => 1,
				'my-topics'         => 1,
				'my-quizzes'        => 1,
				'my-questions'      => 1,
				'assignments'       => 1,
				'meetings'          => 0,
				'withdrawal'        => 0,
				'earnings'          => 0,
				'certificates'      => 1,
				'my-announcements'  => 1,
				'groups'            => 0,
				'essay-report'      => 1,
				'assignment-report' => 1,
				'quizz-report'      => 1,
				'quiz-attempts'     => 1,
				'submitted-essays'  => 1,
				'course-report'     => 1,
				'activity'          => 1,
				'notification'      => 1,
				'private-messages'  => 1,
				'settings'          => 1,
				'logout'            => 1,
			),

			'other'         => array(
				'my-dashboard'     => 1,
				'profile'          => 1,
				'enrolled-courses' => 1,
				'my-quiz-attempts' => 1,
				'my-activity'      => 1,
				'settings'         => 1,
				'logout'           => 1,
			),

		);
		update_option( 'ld_dashboard_menu_options_default', $menu_options );
	}

	/**
	 * LD Dashboard Fields Restrictions Option Tab Settings
	 */
	if ( false === get_option( 'ld_dashboard_frontend_form_default_labels_default' ) ) {
		$label_settings = array(
			'field_61b72091bf8a8' => sprintf( esc_html__( 'Forced %s time', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'field_61b72188bf8ac' => esc_html__( 'Number of Days', 'ld-dashboard' ),
			'field_61d808b30be2b' => sprintf( esc_html__( '%s Material Content', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'field_61d809610be2d' => esc_html__( 'Display result position', 'ld-dashboard' ),
			'field_61d809930be2e' => sprintf( esc_html__( '%s per page', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'questions' ) ),
			'field_61d80a66926c3' => esc_html__( 'Randomize order type', 'ld-dashboard' ),
			'field_61d80b24926c4' => sprintf( esc_html__( 'Number of %s in subset', 'ld-dashboard' ), strtolower( LearnDash_Custom_Label::get_label( 'questions' ) ) ),
			'field_61d80c76926cf' => esc_html__( 'Leaderboard position', 'ld-dashboard' ),
			'field_61c032eba9f66' => esc_html__( 'Points type', 'ld-dashboard' ),
			'field_61c032cda9f65' => esc_html__( 'Display points scored in message', 'ld-dashboard' ),
			'field_61c036af6f1c0' => esc_html__( 'Hint content', 'ld-dashboard' ),
			'field_622201dca91fa' => esc_html__( 'Billing Cycle Number', 'ld-dashboard' ),
			'field_622201f2a91fb' => esc_html__( 'Billing Cycle Type', 'ld-dashboard' ),
			'field_622201122211'  => esc_html__( 'Trial Duration Number', 'ld-dashboard' ),
			'field_6222131233123' => esc_html__( 'Trial Duration Type', 'ld-dashboard' ),
		);
		update_option( 'ld_dashboard_frontend_form_default_labels_default', $label_settings, false );
	}

}



/* Include ACF Pro plugin file */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) && ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/acf/acf.php';
}
