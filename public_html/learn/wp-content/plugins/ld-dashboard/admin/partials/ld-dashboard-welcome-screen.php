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
$settings                   = $ld_dashboard_settings_data['welcome_screen'];

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wbcom-tab-content">
	<div class="wrap ld-dashboard-settings">
		<div class="ld-dashboard-content container">
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( 'ld_dashboard_welcome_screen' );
				do_settings_sections( 'ld_dashboard_welcome_screen' );
				?>
				<div class="form-table">
					<div class="ld-grid-view-wrapper Welcome-Message-Pannel">
						<div class="ld-single-grid">
							<div class="ld-grid-label" scope="row">
								<label><?php esc_html_e( 'Welcome Message', 'ld-dashboard' ); ?></label>
							</div>	
							<div class="ld-grid-content">
								<input type="text" name="ld_dashboard_welcome_screen[welcome-message]" value="<?php echo $settings['welcome-message']; ?>" placeholder="Welcome Back, Admin" />						
							</div>
						</div>
						<div class="ld-single-grid">
							<div class="ld-grid-label" scope="row">
								<label><?php esc_html_e( 'Welcome Screen Cover Image', 'ld-dashboard' ); ?></label>
							</div>

							<div class="ld-grid-content">
								<label class="ld-dashboard-welcomebar-image">
									<input type="button" data-slug="welcomebar_image" class="button-secondary ld_dashboard_upload_image" value="<?php esc_attr_e( 'Upload Welcome Cover Image', 'ld-dashboard' ); ?>" />
									<input type="hidden" id="welcomebar_image" name="ld_dashboard_welcome_screen[welcomebar_image]" value="<?php echo esc_url( $settings['welcomebar_image'] ); ?>" >
								</label>
								<p class="description"><?php esc_html_e( 'Change Image For Welcome cover Message.', 'ld-dashboard' ); ?></p>

								<div class="ld-dashboard-welcomebar-image ld-display-welcomebar_image" 
								<?php
								if ( empty( $settings['welcomebar_image'] ) ) :
									?>
									style="display:none;" <?php endif; ?> >
									<img class="welcomebar_image" src="
									<?php
									if ( ! empty( $settings['welcomebar_image'] ) ) :
										echo esc_url( $settings['welcomebar_image'] );
									endif;
									?>
									" height="150" width="150"/>
									<span class="ld-dashboard-image-close" data-slug="welcomebar_image">x</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php submit_button(); ?>
				<?php wp_nonce_field( 'ld-dashboard-settings-submit', 'ld-dashboard-settings-submit' ); ?>
			</form>
		</div>
	</div>
</div>
