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
$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];
?>
<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper">
		<div class="wbcom-admin-title-section title-tutorial-section-wrap">
			<h3><?php esc_html_e( 'Zoom Meeting Settings', 'ld-dashboard' ); ?></h3>
			<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/zoom-integration/zoom-meeting-configuration/" class="ld-tutorial-btn" target="_blank"><?php esc_html_e( 'View Tutorial', 'ld-dashboard' ); ?></a>
		</div>
		<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( 'ld_dashboard_zoom_meeting_settings' );
				do_settings_sections( 'ld_dashboard_zoom_meeting_settings' );
				?>
				<div class="container zoom-meeting-settings-fields">
					<div class="ld-grid-view-wrapper Welcome-Message-Pannel form-table">
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-content wbcom-settings-section-options">
								<div class="ld-dashboard-notice" style="color:red;"><?php esc_attr_e( 'Zoom will no longer support JWT app types as of June 1, 2023. As a result, we are migrating from JWT to the S2S app type. Please generate your Server-to-Server OAuth credentials and paste them into the respective field. To incorporate the Zoom client into your website, we additionally need SDK app credentials.', 'ld-dashboard' ); ?></div>
							</div>
						</div>
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Zoom Api Status', 'ld-dashboard' ); ?></label>
							</div>	
							<div class="ld-grid-content wbcom-settings-section-options">
								<?php
								$status_class = 'zoom-api-inactive';
								$status_icon  = '<span class="dashicons dashicons-dismiss"></span>';
								$status_text  = esc_html__( 'Inactive', 'ld-dashboard' );
								if ( isset( $settings['zoom-api-key'] ) && '' !== $settings['zoom-api-key'] ) {
									$zoom_meeting = new Zoom_Api();
									$response     = $zoom_meeting->get_all_users( '?page_size=2&page_number=1' );
									if ( ! empty( $response ) && property_exists( $response, 'users' ) ) {
										$status_class = 'zoom-api-active';
										$status_icon  = '<span class="dashicons dashicons-yes-alt"></span>';
										$status_text  = esc_html__( 'Active', 'ld-dashboard' );
									}
								}
								?>
								<div class="ld-dashboard-zoom-api-status <?php echo esc_attr( $status_class ); ?>"><?php echo wp_kses_post( $status_icon ); ?> <?php echo esc_html( $status_text ); ?></div>
							</div>
						</div>
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Account ID (Server to Server OAuth)', 'ld-dashboard' ); ?></label>
							</div>	
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="password" name="ld_dashboard_zoom_meeting_settings[zoom-account-id]" value="<?php echo ( isset( $settings['zoom-account-id'] ) ) ? esc_attr( $settings['zoom-account-id'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter your zoom app Account ID', 'ld-dashboard' ); ?>" />
								<span class="ld-dashboard-password-toggle dashicons dashicons-visibility"></span>
							</div>
						</div>
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Client ID (Server to Server OAuth)', 'ld-dashboard' ); ?></label>
							</div>	
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="password" name="ld_dashboard_zoom_meeting_settings[zoom-api-key]" value="<?php echo ( isset( $settings['zoom-api-key'] ) ) ? esc_attr( $settings['zoom-api-key'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter your zoom app Client ID', 'ld-dashboard' ); ?>" />
								<span class="ld-dashboard-password-toggle dashicons dashicons-visibility"></span>
							</div>
						</div>
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Client secret (Server to Server OAuth)', 'ld-dashboard' ); ?></label>
							</div>	
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="password" name="ld_dashboard_zoom_meeting_settings[zoom-api-secret]" value="<?php echo ( isset( $settings['zoom-api-secret'] ) ) ? esc_attr( $settings['zoom-api-secret'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter your zoom app Client Secret', 'ld-dashboard' ); ?>" />
								<span class="ld-dashboard-password-toggle dashicons dashicons-visibility"></span>
							</div>
						</div>
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'User Email', 'ld-dashboard' ); ?></label>
								<p class="description" id="tagline-description"><?php esc_html_e( 'Enter your zoom app email', 'ld-dashboard' ); ?></p>
							</div>	
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="email" name="ld_dashboard_zoom_meeting_settings[zoom-user-email]" value="<?php echo ( isset( $settings['zoom-user-email'] ) ) ? esc_attr( $settings['zoom-user-email'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter your zoom app user email', 'ld-dashboard' ); ?>" />
							</div>
						</div>
						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Embed Meeting', 'ld-dashboard' ); ?></label>
								<p class="description" id="tagline-description"><?php esc_html_e( 'By enabling this option you can embed zoom client on any page.', 'ld-dashboard' ); ?></p>
							</div>
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="checkbox" id="ld-dashboard-embed-meeting-enable" class="ld-dashboard-setting" name="ld_dashboard_zoom_meeting_settings[embed-meeting]" value="1" <?php ( isset( $settings['embed-meeting'] ) ) ? checked( $settings['embed-meeting'], '1' ) : ''; ?>/>
							</div>
							<div class="ld-grid-content wbcom-settings-section-options" id="ld-dashboard-embed-meeting-box" <?php echo ( isset( $settings['embed-meeting'] ) && 0 === $settings['embed-meeting'] ) ? 'style="display:none"' : ''; ?> >
								<input type="text" class="ld-dashboard-setting embed-meeting-shortcode-box" value="[ld_dashboard_meeting_embed meeting_id='']">
								<span class="ld-dashboard-copy-shortcode"><button class="ld-dashboard-copy-shortcode-button button"><?php esc_html_e( 'Copy', 'ld-dashboard' ); ?></button>
								<div class="ld-dashboard-tooltip" title="<?php echo esc_html__( 'Copied', 'ld-dashboard' ); ?>" flow="up" style="display:none;"></div>
								</span>								
							</div>
							<p class="description" id="tagline-description"><?php esc_html_e( 'Paste this shortcode in page and the zoom meeting client will embed within the page.', 'ld-dashboard' ); ?></p>
						</div>

						<div class="wbcom-settings-section-wrap ld-dashboard-embed-meeting" <?php echo ( isset( $settings['embed-meeting'] ) && 0 === $settings['embed-meeting'] ) ? 'style="display:none"' : ''; ?> >
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'SDK key / Client ID', 'ld-dashboard' ); ?></label>
								<p class="description" id="tagline-description"><?php esc_html_e( 'SDK App Credentials are required to embed zoom client.', 'ld-dashboard' ); ?></p>
							</div>
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="password"  class="ld-dashboard-setting" name="ld_dashboard_zoom_meeting_settings[sdk-client-id]" value="<?php echo ( isset( $settings['sdk-client-id'] ) ) ? esc_html( $settings['sdk-client-id'] ) : ''; ?>"/>
								<span class="ld-dashboard-password-toggle dashicons dashicons-visibility"></span>
							</div>
						</div>

						<div class="wbcom-settings-section-wrap ld-dashboard-embed-meeting" <?php echo ( isset( $settings['embed-meeting'] ) && 0 === $settings['embed-meeting'] ) ? 'style="display:none"' : ''; ?> >
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'SDK / Client Secret', 'ld-dashboard' ); ?></label>
								<p class="description" id="tagline-description"><?php esc_html_e( 'SDK App Credentials are required to embed zoom client.', 'ld-dashboard' ); ?></p>
							</div>
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="password"  class="ld-dashboard-setting" name="ld_dashboard_zoom_meeting_settings[sdk-client-secret]" value="<?php echo ( isset( $settings['sdk-client-secret'] ) ) ? esc_html( $settings['sdk-client-secret'] ) : ''; ?>"/>
								<span class="ld-dashboard-password-toggle dashicons dashicons-visibility"></span>
							</div>
						</div>

						<div class="wbcom-settings-section-wrap">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Create meetings using admin account', 'ld-dashboard' ); ?></label>
								<p class="description" id="tagline-description"><?php esc_html_e( 'Allow instructors to create meetings using admin zoom account.', 'ld-dashboard' ); ?></p>
							</div>
							<div class="ld-grid-content wbcom-settings-section-options">
								<input type="checkbox" class="ld-dashboard-setting use-admin-account-checkbox" name="ld_dashboard_zoom_meeting_settings[use-admin-account]" value="1" <?php ( isset( $settings['use-admin-account'] ) ) ? checked( $settings['use-admin-account'], '1' ) : ''; ?> />
							</div>
						</div>
						<div class="wbcom-settings-section-wrap ld-dashboard-instructors-listing">
							<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
								<label><?php esc_html_e( 'Zoom Account Co-hosts', 'ld-dashboard' ); ?></label>
								<p class="description" id="tagline-description"><?php esc_html_e( 'You have to select all instructors which you want to start meeting as co-host. If not selected all instructors will start meeting.', 'ld-dashboard' ); ?></p>
							</div>
							<div class="ld-grid-content wbcom-settings-section-options"></div>
						</div>
					</div>
				</div>
				<?php submit_button(); ?>
				<?php wp_nonce_field( 'ld-dashboard-settings-submit', 'ld-dashboard-settings-submit' ); ?>
			</form>
		</div>
	</div>
</div>
