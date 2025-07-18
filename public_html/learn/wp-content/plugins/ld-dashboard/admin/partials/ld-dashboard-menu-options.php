<?php
/**
 * Provide a admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wbcomdesigns.com/plugins
 * @since      1.0.0
 *
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/admin/partials
 */

$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$settings                   = $ld_dashboard_settings_data['menu_options'];
$sections                   = ld_dashboard_get_sidebar_tabs();
$tabs                       = $sections;
$ld_dashboard_user_rolls    = ld_dashboard_get_dashboard_user_roles();
asort( $ld_dashboard_user_rolls );

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper">
		<div class="wbcom-admin-title-section title-tutorial-section-wrap">
			<h3><?php esc_html_e( 'Dashboard Menu', 'ld-dashboard' ); ?></h3>
			<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/dashboard-menus/show-hide-dashboard-menus-on-instructors-dashboard/" class="ld-tutorial-btn" target="_blank"><?php esc_html_e( 'View Tutorial', 'ld-dashboard' ); ?></a>
		</div>
		<div class="container wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( 'ld_dashboard_menu_options' );
				do_settings_sections( 'ld_dashboard_menu_options' );
				$instructor_menu    = '<div class="ld-dashboard-menu-settings-role-section role-instructor">';
				$group_leader_menu  = '<div class="ld-dashboard-menu-settings-role-section role-group_leader">';
				$other_menu         = '<div class="ld-dashboard-menu-settings-role-section role-others">';
				$group_exclude_tabs = array( 'my-courses', 'my-lessons', 'my-topics', 'my-quizzes', 'my-questions', 'assignments', 'meetings', 'certificates', 'my-announcements' );
				?>
				<div id="wbcom-accordion" class="ldd-dashboard-menu-visibility">
					<?php foreach ( $ld_dashboard_user_rolls as $key => $role ) : ?>
						<h3><?php echo 'other' === $key ? __( 'Students', 'ld-dashboard' ) : esc_html( $role ); ?></h3>
						<div>
							<?php
							switch ( $key ) {
								case 'other':
									unset( $sections['course-management'] );
									unset( $sections['reports'] );
									unset( $sections['monetization'] );
									unset( $sections['communication'] );
									unset( $sections['groups'] );
									break;
								case 'ld_instructor':
									unset( $sections['course-management']['groups'] );
									$sections = $tabs;
									break;
								case 'group_leader':
									unset( $sections['monetization'] );
									break;
								default:
									$sections = $tabs;
							};
							?>
							<?php foreach ( $sections as $section_key => $section ) : ?>
								<section class="wbcom-settings-section-wrap ldd-menu-gorups">
									<strong class="ldd-menu-gorups-heading"><?php echo ucwords( preg_replace( '/[^a-zA-Z]+/', '  ', $section_key ) ); ?> </strong>
									<div class="wbcom-settings-mene-section-wrap">
										<?php foreach ( $section as $menu_key => $lddmenu ) : ?>
											<div class="wbcom-settings-section-wrap" <?php echo 'my-dashboard' === $menu_key ? 'style="display:none"' : ''; ?> >
												<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
													<label><?php echo esc_html( $lddmenu['label'] ); ?></label>
													<p class="description"><?php printf( esc_html__( 'Enable this option if you want to show the `%s` tab in sidebar.', 'ld-dashboard' ), esc_html( $lddmenu['label'] ) ); ?></p>
												</div>
												<div class="grid-full-size ld-grid-content wbcom-settings-section-options">
													<div class="lavel-ld-dashboard-title">
														<input type="checkbox" class="ld-dashboard-setting ld-dashboard-menu-tab-checkbox" name="ld_dashboard_menu_options[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $menu_key ); ?>]" value="1" data-id="<?php echo esc_attr( $menu_key ); ?>" />
														<input type="hidden" class="ld-dashboard-menu-tab-checkbox-hidden" name="ld_dashboard_menu_options[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $menu_key ); ?>]" value="<?php echo isset( $settings[ $key ][ $menu_key ] ) ? $settings[ $key ][ $menu_key ] : '0'; ?>" />
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</section>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php submit_button(); ?>
				<?php
				submit_button(
					__( 'Reset Settings', 'ld-dashboard' ),
					'primary ld-dashboard-reset-button',
					'ld-dashboard-reset-general-settings',
					true,
					array(
						'data-setting' => 'ld_dashboard_menu_options',
					)
				);
				?>
				<?php wp_nonce_field( 'ld-dashboard-settings-submit', 'ld-dashboard-settings-submit' ); ?>
			</form>
		</div>
	</div>
</div>


