<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $current_user;
$current_user_role = $current_user->roles;

$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$settings                   = $ld_dashboard_settings_data['ld_dashboard_feed_settings'];
$match_userroles            = array();
$user_role_class            = '';
$class                      = '';
if ( learndash_is_admin_user( $current_user->ID ) ) {
	$user_role_class = 'ld-dashboard-admin';
} elseif ( in_array( 'ld_instructor', (array) $current_user->roles ) ) {
	$user_role_class = 'ld-dashboard-instructor';
} elseif ( learndash_is_group_leader_user( $current_user->ID ) ) {
	$user_role_class = 'ld-dashboard-group-leader';
} else {
	$user_role_class = 'ld-dashboard-student';
}
if ( ! empty( $settings['disable_user_roles_live_feed'] ) ) {

	$match_userroles = array_intersect( $current_user_role, $settings['disable_user_roles_live_feed'] );
}
if ( ( isset( $settings['disable-live-feed'] ) && $settings['disable-live-feed'] == 1 ) || ( ! empty( $match_userroles ) ) ) {
	$class = 'ld-live-feed-hide';
}
?>
<div class="ld-dashboard-main-wrapper <?php echo esc_attr( $class ); ?> <?php echo esc_attr( $user_role_class ); ?>">
	<div class="ld-dashboard-wrapper">
		<?php
		$enable_live_feed = false;
		if ( ( ! isset( $settings['disable-live-feed'] ) || $settings['disable-live-feed'] != 1 ) && ( empty( $match_userroles ) ) ) {
			$enable_live_feed = true;
		}
		if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-profile.php' ) ) {
			include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-profile.php' );
		} else {
			require LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-profile.php';
		}
		if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-content-section.php' ) ) {
			include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-content-section.php' );
		} else {
			require LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-content-section.php';
		}
		?>
	</div>
</div>
