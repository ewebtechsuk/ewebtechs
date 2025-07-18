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
$settings              		= $ld_dashboard_settings_data['time_tracking'];
$settings['enable']	   		= isset($settings['enable']) ? $settings['enable'] : '';
$settings['idle_time']	   	= isset($settings['idle_time']) ? $settings['idle_time'] : '60';
$settings['idle_messsage']	= isset($settings['idle_messsage']) ? $settings['idle_messsage'] : esc_html__('Are you still on this page?', 'ld-dashboard');
$settings['idle_button_label']	= isset($settings['idle_button_label']) ? $settings['idle_button_label'] : esc_html__('Yes, I am', 'ld-dashboard');

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper">
		<div class="container settings-all-wrap wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( 'ld_dashboard_time_tracking_settings' );
				do_settings_sections( 'ld_dashboard_time_tracking_settings' );
				?>
				<div class="wbcom-admin-title-section title-tutorial-section-wrap">
					<h3><?php esc_html_e( 'Time Tracking', 'ld-dashboard' ); ?></h3>
					<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/ld-settings-learndash-dashboard/ld-global-settings/" class="ld-tutorial-btn" target="_blank"><?php esc_html_e( 'View Tutorial', 'ld-dashboard' ); ?></a>
				</div>
				<div class="form-table ld-dashboard-setting-accordian-wrapper components-section">
					<div class="ld-grid-view-wrapper">
						<div class="ld-dashboard-general-setting-accordian-content">
							<div class="wbcom-settings-section-wrap announcements">
								<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
									<label><?php esc_html_e( 'Enable Time Tracking', 'ld-dashboard' ); ?></label>
									<p class="description"><?php esc_html_e( 'Tracks time spent in all LearnDash courses and detects when a user is idle. Course completion time and total course time are both added to LearnDash reports.', 'ld-dashboard' ); ?></p>
								</div>
								<div class="ld-grid-content wbcom-settings-section-options">
									<input type="checkbox"  name="ld_dashboard_time_tracking_settings[enable]" value="1" <?php checked( $settings['enable'], '1' ); ?> id="ld_dashboard_enable_time_tracking"/>
								</div>
							</div>

							
						</div>
					</div>
					<div class="wbcom-settings-section-wrap ld-dashboard-timetracking" <?php if(isset($settings['enable']) && $settings['enable'] != 1):?> style="display:none;" <?php endif;?>>
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Idle Time (in seconds)', 'ld-dashboard' ); ?></label>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<input type="number" name="ld_dashboard_time_tracking_settings[idle_time]" value="<?php echo esc_attr( $settings['idle_time'] ); ?>" placeholder="<?php esc_attr_e( 'Enter idle time', 'ld-dashboard' ); ?>" min="0"/>
						</div>
					</div>
					
					<div class="wbcom-settings-section-wrap ld-dashboard-timetracking" <?php if(isset($settings['enable']) && $settings['enable'] != 1):?> style="display:none;" <?php endif;?>>
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Idle Message', 'ld-dashboard' ); ?></label>
							<p class="description"><?php esc_html_e( 'This is the message that learners will see in the popup if they are idle.', 'ld-dashboard' ); ?></p>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<input type="text" name="ld_dashboard_time_tracking_settings[idle_messsage]" value="<?php echo esc_attr( $settings['idle_messsage'] ); ?>" placeholder="<?php esc_attr_e( 'Enter idle message', 'ld-dashboard' ); ?>" min="0"/>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap ld-dashboard-timetracking" <?php if(isset($settings['enable']) && $settings['enable'] != 1):?> style="display:none;" <?php endif;?>>
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Active Button Label', 'ld-dashboard' ); ?></label>
							<p class="description"><?php esc_html_e( 'Clicking on this button will resume the time being tracked on a course for the learner.', 'ld-dashboard' ); ?></p>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<input type="text" name="ld_dashboard_time_tracking_settings[idle_button_label]" value="<?php echo esc_attr( $settings['idle_button_label'] ); ?>" placeholder="<?php esc_attr_e( 'Enter idle button label', 'ld-dashboard' ); ?>" min="0"/>
						</div>
					</div>
					
				</div>
				
				
				<?php submit_button();?>
				<?php wp_nonce_field( 'ld-dashboard-settings-submit', 'ld-dashboard-settings-submit' ); ?>
			</form>
			<script>
			(function ($) {
				$(document).ready(function () {
					$('#ld_dashboard_enable_time_tracking').on('change', function (e) {						
						if ( $(this).prop("checked")) {
						 	$( '.ld-dashboard-timetracking').show();
						} else {
							$( '.ld-dashboard-timetracking').hide();
						}
					});	
				});
				  
			})(jQuery);
			</script>
		</div>
	</div>
</div>
