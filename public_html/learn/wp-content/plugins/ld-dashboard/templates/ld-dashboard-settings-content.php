<?php

$user_id                    = get_current_user_id();
$current_user               = wp_get_current_user();
$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
$settings                   = $ld_dashboard_settings_data['general_settings'];

if ( ! isset( $_GET['action'] ) ) {
	$active_tab = 'profile';
} elseif ( isset( $_GET['action'] ) ) {
	$active_tab = sanitize_text_field( wp_unslash( $_GET['action'] ) );
}
$is_instructor = false;
if ( in_array( 'ld_instructor', $current_user->roles ) || in_array( 'administrator', $current_user->roles ) ) {
	$is_instructor = true;
}
$dashboard_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
?>
<div class="ld-dashboard-course-content instructor-courses-list ld-dashboard-profile-setting-view">
	<div class="ld-dashboard-section-head-title">
		<h3 class="ld-dashboard-nav-title"><?php esc_html_e( 'Settings', 'ld-dashboard' ); ?></h3>
	</div>
		<div class="ld-dashboard-content-inner">
			<div class="ld-dashboard-inline-links">
				<ul>
					<li class="<?php echo ( ! isset( $_GET['action'] ) ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=settings'; ?>"> <?php esc_html_e( 'Profile', 'ld-dashboard' ); ?></a> </li>
					<li class="<?php echo ( isset( $_GET['action'] ) && 'reset' === $_GET['action'] ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=settings&action=reset'; ?>"> <?php esc_html_e( 'Reset Password', 'ld-dashboard' ); ?> </a></li>
					<?php if ( $is_instructor && ld_if_commission_enabled() ) : ?>
						<li class="<?php echo ( isset( $_GET['action'] ) && 'withdraw' === $_GET['action'] ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=settings&action=withdraw'; ?>"> <?php esc_html_e( 'Withdraw', 'ld-dashboard' ); ?> </a></li>
					<?php endif; ?>
					<?php if ( isset( $settings['enable-zoom'] ) && 1 == $settings['enable-zoom'] && ( in_array( 'ld_instructor', $current_user->roles ) && ! in_array( 'administrator', $current_user->roles ) ) ) : ?>
						<li class="<?php echo ( isset( $_GET['action'] ) && 'zoom' === $_GET['action'] ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=settings&action=zoom'; ?>"> <?php esc_html_e( 'Zoom', 'ld-dashboard' ); ?> </a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="ld-dashboard-settings-content-wrap">
			<?php
			if ( 'profile' === $active_tab ) {
				include 'ld-dashboard-profile-settings-tab.php';
			} elseif ( 'reset' === $active_tab ) {
				include 'ld-dashboard-password-settings-tab.php';
			} elseif ( 'withdraw' === $active_tab && $is_instructor ) {
				if ( ld_if_commission_enabled() ) {
					include 'ld-dashboard-withdraw-settings-tab.php';
				}
			} elseif ( 'zoom' === $active_tab && $is_instructor ) {
				include 'ld-dashboard-zoom-settings-tab.php';
			}
			?>
			</div>
		</div>	
</div>
