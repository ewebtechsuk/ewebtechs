<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $current_user;
$user_id                        = get_current_user_id();
$current_user                   = wp_get_current_user();
$user_name                      = isset( $current_user->display_name ) ? $current_user->display_name : $current_user->user_firstname . ' ' . $current_user->user_lastname;
$function_obj                   = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data     = $function_obj->ld_dashboard_settings_data();
$welcome_screen                 = $ld_dashboard_settings_data['welcome_screen'];
$ld_dashboard_general_settings  = $ld_dashboard_settings_data['general_settings'];
$monetization_settings          = $ld_dashboard_settings_data['monetization_settings'];
$dashboard_page                 = $function_obj->ld_dashboard_get_url( 'dashboard' );
$dashboard_landing_cover        = '';
$enable_instructor_earning_logs = isset( $ld_dashboard_general_settings['enable-instructor-earning-logs'] ) ? $ld_dashboard_general_settings['enable-instructor-earning-logs'] : '';


if ( isset( $welcome_screen['welcomebar_image'] ) && $welcome_screen['welcomebar_image'] != '' ) {
	$dashboard_landing_cover = "background-image: url({$welcome_screen['welcomebar_image']});";
}
?>
<div class="ld-dashboard-profile-summary-container" style="max-width: 100%;width: 100%;">
	<div class="ld-dashboard-profile-summary" style="<?php echo esc_attr( $dashboard_landing_cover ); ?>">
		<div class="ld-dashboard-profile">
			<div class="ld-dashboard-profile-avatar">
				<?php echo wp_kses_post( get_avatar( $user_id ) ); ?>
			</div>
			<?php if ( $user_name != '' ) : ?>
				<div class="ld-dashboard-profile-info">
					<div class="ld-dashboard-display-name">
						<h4><strong><?php echo esc_html( $user_name ); ?></strong></h4>
					</div>
					<?php do_action( 'ld_dashboard_banner_content' ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $current_user->user_email ) ) : ?>
						<div class="ld-dashboard-profile-email">
							<?php echo esc_html( $current_user->user_email ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( ( ! learndash_is_group_leader_user( $user_id ) && in_array( 'ld_instructor', (array) $current_user->roles ) ) || ( learndash_is_admin_user( $user_id ) || ld_can_user_manage_courses() ) ) : ?>
					<div class="ld-dashboard-header-button">
						<?php $course_nonce = wp_create_nonce( 'course-nonce' ); ?>
						<a class="ld-dashboard-add-course ld-dashboard-btn-bg" href="<?php echo esc_url( get_permalink() ) . '?action=add-course&tab=my-courses&_lddnonce=' . esc_attr( $course_nonce ); ?>">
							<span class="ld-icons ld-icon-add-line"></span> <?php printf( esc_html__( 'Add a new %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?>
						</a>
					</div>
				<?php endif; ?>
				<?php if ( isset( $ld_dashboard_general_settings['become-instructor-button'] ) && 1 == $ld_dashboard_general_settings['become-instructor-button'] && ! learndash_is_group_leader_user( $user_id ) && ! learndash_is_admin_user( $user_id ) && ! in_array( 'ld_instructor', (array) $current_user->roles ) && ! in_array( 'ld_instructor_pending', (array) $current_user->roles ) ) : ?>
					<div class="ld-dashboard-header-button">
						<?php $instructor_nonce = wp_create_nonce( 'instructor-nonce' ); ?>
						<a class="ld-dashboard-add-course ld-dashboard-become-instructor-btn ld-dashboard-btn-bg" href="#">
							<span class="ld-icons ld-icon-account-circle-line"></span> <?php esc_html_e( 'Become An Instructor', 'ld-dashboard' ); ?>
						</a>
					</div>
				<?php endif; ?>
		</div>
		<?php
		if ( isset( $ld_dashboard['welcome-screen'] ) && $ld_dashboard['welcome-screen'] == 1 ) {
			?>
		<div class="ld-dashboard-landing-content">
			<?php do_action( 'ld_dashboard_before_welcome_message' ); ?>
			<div class="ld-dashboard-landing-text">
				<?php
				if ( isset( $welcome_screen['welcome-message'] ) && $welcome_screen['welcome-message'] != '' ) {
					echo sprintf( esc_html__( $welcome_screen['welcome-message'], 'ld-dashboard' ), esc_html( trim( $user_name ) ) );
				} else {
					echo sprintf( esc_html__( 'Welcome back, %s', 'ld-dashboard' ), esc_html( trim( $user_name ) ) );
				}
				?>
			</div>
			<?php do_action( 'ld_dashboard_after_welcome_message' ); ?>
		</div>
		<?php } ?>
	</div>
</div>
<div class="ld-dashboard-content-wrapper">
	<div class="ld-dashboard-left-section ld-dashboard-sidebar-left">
		<?php do_action( 'ld_dashboard_before_profile_section' ); ?>
		<section id="ld-dashboard-profile" class="widget-ld-dashboard-profile ld-dashboard-profile">
			<div class="ld-dashboard-mobile">
				<div class="ld-dashboard-mobile-wrap">
					<?php if ( learndash_is_admin_user( $user_id ) || in_array( 'ld_instructor', (array) $current_user->roles ) ) : ?>
						<a class="mobile-menu-link" href="<?php echo esc_url( $dashboard_page ) . '?tab=my-courses'; ?>">
							<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/course.svg'; ?>">
							<span><?php echo esc_html__( 'My Courses', 'ld-dashboard' ); ?></span>
						</a>
					<?php else : ?>
						<a class="mobile-menu-link" href="<?php echo esc_url( $dashboard_page ) . '?tab=enrolled-courses'; ?>">
							<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/course.svg'; ?>">
							<span><?php echo esc_html__( 'Enrolled Courses', 'ld-dashboard' ); ?></span>
						</a>
					<?php endif; ?>
					<a class="mobile-menu-link" href="<?php echo esc_url( $dashboard_page ) . '?tab=profile'; ?>">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/user-alt.svg'; ?>">
						<span><?php echo esc_html__( 'My Profile', 'ld-dashboard' ); ?></span>
					</a>
					<a id="ld-dashboard-menu" class="mobile-menu-link" href="#">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/bars.svg'; ?>">
						<span><?php echo esc_html__( 'Menu', 'ld-dashboard' ); ?></span>
					</a>
				</div>
			</div>
			<div class="ld-dashboard-location">
				<?php
					do_action( 'ld_dashboard_before_sidebar_menu' );
					ld_dashboard_render_dashboard_menus();
					do_action( 'ld_dashboard_after_sidebar_menu' );
				?>
			</div>
		</section>
		<?php do_action( 'ld_dashboard_after_profile_section' ); ?>
	</div>
