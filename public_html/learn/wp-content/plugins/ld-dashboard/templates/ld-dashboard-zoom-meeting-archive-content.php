<?php
/**
 * The Template for joining meeting via browser
 *
 * This template can be overridden by copying it to yourtheme/ld-dashboard/ld-dashboard-zoom-meeting-archive-content.php.
 *
 * @package    LD-Dashboard/Templates
 * @since      6.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $ldd_meeting;
global $current_user;

if ( ld_dashboard_video_conference_zoom_check_login() ) {
	if ( ! empty( $ldd_meeting['api']->state ) && $ldd_meeting['api']->state === 'ended' ) {
		echo '<h3>' . __( 'This meeting has been ended by host.', 'ld-dashboard' ) . '</h3>';
		die;
	}

	/**
	 * Trigger before the content
	 */
	do_action( 'ldd_before_meeting_content', $ldd_meeting );
	?>
	<div id="ldd-zoom-browser-meeting" class="ldd-zoom-browser-meeting-wrapper" style="position:absolute; z-index:1;">
		<div id="ldd-zoom-browser-meeting--container">
			<?php
			$bypass_notice = apply_filters( 'ldd_api_bypass_notice', false );
			if ( ! $bypass_notice ) {
				?>
				<div class="ldd-zoom-browser-meeting--info">
					<?php if ( ! is_ssl() ) { ?>
						<p class="alert-message" style="line-height: 1.5;">
							<strong style="color:red;"><?php esc_html_e( '!!!ALERT!!! → ', 'ld-dashboard' ); ?></strong>
							<?php
								esc_html_e(
									'Browser did not detect a valid SSL certificate. Audio and Video for Zoom meeting will not work on a non HTTPS site, please install a valid SSL certificate to allow audio and video in your Meetings via browser.',
									'ld-dashboard'
								);
							?>
						</p>
					<?php } ?>
					<div class="ldd-zoom-browser-meeting--info__browser"></div>
				</div>
			<?php } ?>
			<form class="ldd-zoom-browser-meeting--meeting-form" id="ldd-zoom-browser-meeting-join-form" action="">
				<?php $full_name = ! empty( $current_user->first_name ) ? $current_user->first_name . ' ' . $current_user->last_name : $current_user->display_name; ?>
				<div class="form-group">
					<input type="text" name="display_name" id="ldd-meeting-display-name" value="<?php echo esc_attr( $full_name ); ?>" placeholder="<?php _e( 'Your Name Here', 'ld-dashboard' ); ?>" class="form-control" required>
				</div>
				<div class="form-group">
					<input type="email" name="display_email" id="ldd-meeting-user-email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php _e( 'Your Email Here', 'ld-dashboard' ); ?>" class="form-control">
				</div>
				<?php
				if ( ! isset( $_GET['pak'] ) && ! empty( $ldd_meeting['password'] ) ) {
					?>
					<div class="form-group">
						<input type="password" name="ldd_meeting_password" id="ldd-meeting-password" value="" placeholder="<?php _e( 'Meeting Password', 'ld-dashboard' ); ?>" class="form-control" required>
					</div>
					<?php
				}
				?>
				<div class="form-group">
					<select id="meeting_lang" name="meeting-lang" class="form-control">
						<option value="en-US">English</option>
						<option value="de-DE">German Deutsch</option>
						<option value="es-ES">Spanish Español</option>
						<option value="fr-FR">French Français</option>
						<option value="jp-JP">Japanese 日本語</option>
						<option value="pt-PT">Portuguese Portuguese</option>
						<option value="ru-RU">Russian Русский</option>
						<option value="zh-CN">Chinese 简体中文</option>
						<option value="zh-TW">Chinese 繁体中文</option>
						<option value="ko-KO">Korean 한국어</option>
						<option value="vi-VN">Vietnamese Tiếng Việt</option>
						<option value="it-IT">Italian italiano</option>
					</select>
				</div>
				<button type="submit" class="btn btn-primary" id="ldd-zoom-browser-meeting-join-mtg">
					<?php esc_html_e( 'Join', 'ld-dashboard' ); ?>
				</button>
			</form>
		</div>
	</div>
	<?php
	/**
	 * Trigger after the content
	 */
	do_action( 'ldd_after_meeting_content' );
} else {
	echo '<h3>' . __( 'You do not have enough priviledge to access this page. Please login to continue or contact administrator.', 'ld-dashboard' ) . '</h3>';
	die;
}

