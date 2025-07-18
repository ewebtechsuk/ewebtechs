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
$site_name 					= get_bloginfo('name');
$message_text = sprintf( esc_html__( 'You have been invited by %s to join the %s community.', 'ld-dashboard' ), '[%INVITER_NAME%]', $site_name ); /* Do not translate the strings embedded in %% ... %% ! */

$footer_message_text = apply_filters( 'ld_dashboard_accept_invite_footer_message', esc_html__( 'To accept this invitation, please visit [%ACCEPT_URL%]', 'ld-dashboard' ) );
$footer_message_text .= '

';
$footer_message_text .= apply_filters( 'ld_dashboard_opt_out_footer_message', esc_html__( 'To opt out of future invitations to this site, please visit [%OPTOUT_URL%]', 'ld-dashboard' ) );
		
		
$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$settings              		= $ld_dashboard_settings_data['invite_user'];
$invitation_subject	   		= isset($settings['invitation_subject']) ? $settings['invitation_subject'] : sprintf( esc_html__( 'An invitation to join the %s community.', 'ld-dashboard' ), $site_name );
$invitation_message	   		= isset($settings['invitation_message']) ? $settings['invitation_message'] : $message_text;
$footer_invitation_message	= isset($settings['footer_invitation_message']) ? $settings['footer_invitation_message'] : $footer_message_text;
$max_invites				= isset($settings['max_invites']) ? $settings['max_invites'] : 5;
$subject_is_customizable	= isset($settings['subject_is_customizable']) ? $settings['subject_is_customizable'] : '';
$message_is_customizable	= isset($settings['message_is_customizable']) ? $settings['message_is_customizable'] : '';

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper">
		<div class="container settings-all-wrap wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( 'ld_dashboard_invite_user_settings' );
				do_settings_sections( 'ld_dashboard_invite_user_settings' );
				?>
				<div class="wbcom-admin-title-section title-tutorial-section-wrap">
					<h3><?php esc_html_e( 'Invite User', 'ld-dashboard' ); ?></h3>					
				</div>
				<div class="form-table ld-dashboard-setting-accordian-wrapper components-section">
					<div class="wbcom-settings-section-wrap ">
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Replacement patterns for email text fields', 'ld-dashboard' ); ?></label>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<ul>
								<li><strong>[%SITE_NAME%]</strong> - <?php esc_html_e('name of your website','ld-dashboard');?></li>
								<li><strong>[%INVITER_NAME%]</strong> - <?php esc_html_e('display name of the inviter','ld-dashboard');?></li>
								<!--li><strong>[%INVITER_URL%]</strong> - <?php //esc_html_e('URL to the profile of the inviter','ld-dashboard');?></li-->
								<li><strong>[%ACCEPT_URL%]</strong> - <?php esc_html_e('Link that invited users can click to accept the invitation','ld-dashboard');?></li>
								<li><strong>[%OPTOUT_URL%]</strong> - <?php esc_html_e('Link that invited users can click to opt out of future invitations','ld-dashboard');?></li>
							</ul>
						</div>
					</div>
				
					<div class="wbcom-settings-section-wrap ">
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Text of email invitation subject line', 'ld-dashboard' ); ?></label>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<?php
							$content  = '';							
							$settings = array(
								'wpautop' => true, // enable auto paragraph?
								'media_buttons' => false,
								'textarea_name' => 'ld_dashboard_invite_user_settings[invitation_subject]', // id of the target textarea
								'textarea_rows' => 20,
								'editor_height' => 150,
								'tinymce' => array(
									// Items for the Visual Tab
									'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
								),
								'quicktags' => array(
									// Items for the Text Tab
									'buttons' => 'strong,em,underline,ul,ol,li,link,code'
								)
							);
							wp_editor( $invitation_subject, 'ld_dashboard_invite_user_settings_invitation_subject', $settings );
							?>
							<!--textarea name="ld_dashboard_invite_user_settings[invitation_subject]"><?php echo esc_html($invitation_subject);?></textarea-->
						</div>
					</div>
					
					<div class="wbcom-settings-section-wrap ">
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Main text of email invitation message', 'ld-dashboard' ); ?></label>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<?php
							$content  = '';
							$settings = array(
								'wpautop' => true, // enable auto paragraph?
								'media_buttons' => false,
								'textarea_name' => 'ld_dashboard_invite_user_settings[invitation_message]', // id of the target textarea
								'textarea_rows' => 20,
								'editor_height' => 150,
								'tinymce' => array(
									// Items for the Visual Tab
									'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
								),
								'quicktags' => array(
									// Items for the Text Tab
									'buttons' => 'strong,em,underline,ul,ol,li,link,code'
								)
							);
							wp_editor( $invitation_message, 'ld_dashboard_invite_user_settings_invitation_message', $settings );
							?>
							<!--textarea name="ld_dashboard_invite_user_settings[invitation_message]"><?php echo esc_html($invitation_message);?></textarea-->
						</div>
					</div>
					
					<div class="wbcom-settings-section-wrap ">
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Footer text of email invitation message (not editable by users)', 'ld-dashboard' ); ?></label>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<?php
							$content  = '';
							$settings = array(
								'wpautop' => true, // enable auto paragraph?
								'media_buttons' => false,
								'textarea_name' => 'ld_dashboard_invite_user_settings[footer_invitation_message]', // id of the target textarea
								'textarea_rows' => 20,
								'editor_height' => 150,
								'tinymce' => array(
									// Items for the Visual Tab
									'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
								),
								'quicktags' => array(
									// Items for the Text Tab
									'buttons' => 'strong,em,underline,ul,ol,li,link,code'
								)
							);
							wp_editor( $footer_invitation_message, 'ld_dashboard_invite_user_settings_footer_invitation_message', $settings );
							?>
							<!--textarea name="ld_dashboard_invite_user_settings[footer_invitation_message]"><?php echo esc_html($footer_invitation_message);?></textarea-->
						</div>
					</div>
					
					<div class="wbcom-settings-section-wrap" >
						<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
							<label><?php esc_html_e( 'Number of email invitations users are permitted to send at a time', 'ld-dashboard' ); ?></label>
						</div>	
						<div class="ld-grid-content wbcom-settings-section-options">
							<input type="number" name="ld_dashboard_invite_user_settings[max_invites]" value="<?php echo esc_html($max_invites);?>"  min="0"/>
						</div>
					</div>
					
					<div class="ld-grid-view-wrapper">
						<div class="ld-dashboard-general-setting-accordian-content">
							<div class="wbcom-settings-section-wrap announcements">
								<div class="ld-grid-label wbcom-settings-section-options-heading" scope="row">
									<label><?php esc_html_e( 'Allow users to customize invitation', 'ld-dashboard' ); ?></label>									
								</div>
								<div class="ld-grid-content wbcom-settings-section-options">
									<input type="checkbox"  name="ld_dashboard_invite_user_settings[subject_is_customizable]" value="1" <?php checked( $subject_is_customizable, '1' ); ?> id="ld_dashboard_enable_time_tracking"/>&nbsp;<?php esc_html_e('Subject line', 'ld-dashboard');?>
								</div>
								<br />
								<div class="ld-grid-content wbcom-settings-section-options">
									<input type="checkbox"  name="ld_dashboard_invite_user_settings[message_is_customizable]" value="1" <?php checked( $message_is_customizable, '1' ); ?> id="ld_dashboard_enable_time_tracking"/>&nbsp;<?php esc_html_e('Message body', 'ld-dashboard');?>
								</div>
							</div>

							
						</div>
					</div>
					
				</div>
				
				
				<?php submit_button();?>
				<?php wp_nonce_field( 'ld-dashboard-settings-submit', 'ld-dashboard-settings-submit' ); ?>
			</form>			
		</div>
	</div>
</div>
