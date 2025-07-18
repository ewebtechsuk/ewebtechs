<?php $dashboard_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' ); ?>
<div class="ld-dashboard-profile-password-update ld-dashboard-content-inner">
<?php

if ( ! is_user_logged_in() ) :
	?>
	<p class="warning">
		<?php esc_html_e( 'You must be logged in to change your password.', 'ld-dashboard' ); ?>
	</p>
	<?php else : ?>
		<p class="ld_dashboard_message_container"></p>
		<?php do_action( 'ld_dashboard_before_password_setting_form' ); ?>
		<form class="ld-dashboard-profile-form-field-list" method="post" id="adduser" action="<?php echo esc_url( $dashboard_url ) . '?tab=settings&action=reset'; ?>">
			<div class="ld-dashboard-profile-form-field form-password">
				<label for="pass1"><?php esc_html_e( 'Current Password', 'ld-dashboard' ); ?> </label>
				<input class="text-input" name="p_text" type="password" />
			</div>
			<div class="ld-dashboard-profile-form-field form-password">
				<label for="pass1"><?php esc_html_e( 'New Password *', 'ld-dashboard' ); ?> </label>
				<input class="text-input ld-pass1" name="pass1" type="password" id="pass1" />
			</div>
			<div class="ld-dashboard-profile-form-field form-password repeat-new-nassword">
				<label for="pass2"><?php esc_html_e( 'Repeat New Password *', 'ld-dashboard' ); ?></label>
				<input class="text-input ld-pass2" name="pass2" type="password" id="pass2" />
			</div>
			<div class="form-submit">
				<button name="updateuser" type="submit" id="ldd_update_user_pass" class="submit button" /><?php esc_html_e( 'Reset Password', 'ld-dashboard' ); ?></button>
				<?php wp_nonce_field( 'update-pass' ); ?>
				<input name="action" type="hidden" id="action" value="update-pass" />
			</div>
		</form>
		<?php do_action( 'ld_dashboard_after_password_setting_form' ); ?>
		<?php endif; ?>
</div>
