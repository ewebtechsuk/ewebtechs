<?php
$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$monetization_setting       = $ld_dashboard_settings_data['monetization_settings'];
$withdrawal_data            = get_user_meta( get_current_user_id(), 'ld_withdrawals_data', true );
$withdrawal_data            = ( is_array( $withdrawal_data ) ) ? $withdrawal_data : array();
$methods_exist              = false;
$methods                    = array();
if ( is_array( $monetization_setting ) ) {
	if ( isset( $monetization_setting['enable-bank-transfer-method'] ) && 1 == $monetization_setting['enable-bank-transfer-method'] ) {
		$methods_exist = true;
		$methods[]     = 'bank_transfer';
	}
	if ( isset( $monetization_setting['enable-e-check-method'] ) && 1 == $monetization_setting['enable-e-check-method'] ) {
		$methods_exist = true;
		$methods[]     = 'e_check';
	}
	if ( isset( $monetization_setting['enable-paypal-method'] ) && 1 == $monetization_setting['enable-paypal-method'] ) {
		$methods_exist = true;
		$methods[]     = 'paypal';
	}
	if ( isset( $monetization_setting['enable-stripe-method'] ) && 1 == $monetization_setting['enable-stripe-method'] ) {
		$methods_exist = true;
		$methods[]     = 'stripe';
	}
}
$min_withdraw_text  = esc_html__( 'Min Withdraw ', 'ld-dashboard' );
$min_withdraw_text .= isset( $monetization_setting['minimum-withdrawal-amount'] ) ? $monetization_setting['minimum-withdrawal-amount'] : 10;
$bank_instructions  = isset( $monetization_setting['bank-instructions'] ) ? $monetization_setting['bank-instructions'] : '';
$dashboard_url      = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );

?>
<div class="ld-dashboard-add-withdraw-method-form">
	<?php if ( $methods_exist ) : ?>
	<div class="ld-dashboard-add-withdraw-method-head">
		<div class="ld-dashboard-add-withdraw-method-head-content">
			<?php echo esc_html__( 'Select a withdraw method', 'ld-dashboard' ); ?>
		</div>
	</div>
	<form class="ld-dashboard-withdraw-form-field-list" method="post" id="add_withdraw" action="<?php echo esc_url( $dashboard_url ) . '?tab=settings&action=withdraw'; ?>">
		<div class="ld-dashboard-withdraw-method-container">
			<?php if ( in_array( 'bank_transfer', $methods ) ) : ?>
			<div class="ld-dashboard-withdraw-method-single <?php echo ( isset( $withdrawal_data['ldd_withdraw_method'] ) && 'bank_transfer' === $withdrawal_data['ldd_withdraw_method'] ) ? 'ld-dashboard-withdraw-method-active' : ''; ?>">
				<input type="hidden" class="ld-dashboard-selected-method" value="<?php echo ( isset( $withdrawal_data['ldd_withdraw_method'] ) ) ? esc_attr( $withdrawal_data['ldd_withdraw_method'] ) : ''; ?>">
				<input type="radio" class="ld-dashboard-withdraw-method-radio" name="ldd_withdraw_method" value="bank_transfer" <?php ( isset( $withdrawal_data['ldd_withdraw_method'] ) ) ? checked( $withdrawal_data['ldd_withdraw_method'], 'bank_transfer' ) : ''; ?>>
				<div class="ld-dashboard-withdraw-method-content">
					<span class="ld-dashboard-withdraw-method-title"><?php echo esc_html__( 'Bank Transfer', 'ld-dashboard' ); ?></span>
					<span class="ld-dashboard-withdraw-method-desc"><?php echo esc_html( $min_withdraw_text ); ?></span>
				</div>
			</div>
			<?php endif; ?>
			<?php if ( in_array( 'e_check', $methods ) ) : ?>
			<div class="ld-dashboard-withdraw-method-single <?php echo ( isset( $withdrawal_data['ldd_withdraw_method'] ) && 'e_check' === $withdrawal_data['ldd_withdraw_method'] ) ? 'ld-dashboard-withdraw-method-active' : ''; ?>">
				<input type="radio" class="ld-dashboard-withdraw-method-radio" name="ldd_withdraw_method" value="e_check" <?php ( isset( $withdrawal_data['ldd_withdraw_method'] ) ) ? checked( $withdrawal_data['ldd_withdraw_method'], 'e_check' ) : ''; ?>>
				<div class="ld-dashboard-withdraw-method-content">
					<span class="ld-dashboard-withdraw-method-title"><?php echo esc_html__( 'E-Check', 'ld-dashboard' ); ?></span>
					<span class="ld-dashboard-withdraw-method-desc"><?php echo esc_html( $min_withdraw_text ); ?></span>
				</div>
			</div>
			<?php endif; ?>
			<?php if ( in_array( 'paypal', $methods ) ) : ?>
			<div class="ld-dashboard-withdraw-method-single <?php echo ( isset( $withdrawal_data['ldd_withdraw_method'] ) && 'paypal' === $withdrawal_data['ldd_withdraw_method'] ) ? 'ld-dashboard-withdraw-method-active' : ''; ?>">
				<input type="radio" class="ld-dashboard-withdraw-method-radio" name="ldd_withdraw_method" value="paypal" <?php ( isset( $withdrawal_data['ldd_withdraw_method'] ) ) ? checked( $withdrawal_data['ldd_withdraw_method'], 'paypal' ) : ''; ?>>
				<div class="ld-dashboard-withdraw-method-content">
					<span class="ld-dashboard-withdraw-method-title"><?php echo esc_html__( 'Paypal', 'ld-dashboard' ); ?></span>
					<span class="ld-dashboard-withdraw-method-desc"><?php echo esc_html( $min_withdraw_text ); ?></span>
				</div>
			</div>
			<?php endif; ?>
			<?php if ( in_array( 'stripe', $methods ) ) : ?>
			<div class="ld-dashboard-withdraw-method-single <?php echo ( isset( $withdrawal_data['ldd_withdraw_method'] ) && 'stripe' === $withdrawal_data['ldd_withdraw_method'] ) ? 'ld-dashboard-withdraw-method-active' : ''; ?>">
				<input type="radio" class="ld-dashboard-withdraw-method-radio" name="ldd_withdraw_method" value="stripe" <?php ( isset( $withdrawal_data['ldd_withdraw_method'] ) ) ? checked( $withdrawal_data['ldd_withdraw_method'], 'stripe' ) : ''; ?>>
				<div class="ld-dashboard-withdraw-method-content">
					<span class="ld-dashboard-withdraw-method-title"><?php echo esc_html__( 'Stripe', 'ld-dashboard' ); ?></span>
					<span class="ld-dashboard-withdraw-method-desc"><?php echo esc_html( $min_withdraw_text ); ?></span>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<div class="ld-dashboard-withdraw-method-fields" data-type="bank_transfer">
			<?php if ( '' !== $bank_instructions ) : ?>
			<div class="ld-dashboard-bak-instructions"><?php echo esc_html( $bank_instructions ); ?></div>
			<?php endif; ?>
			<div class="ld-dashboard-profile-form-field form-password">
				<label for="pass1"><?php esc_html_e( 'Account Name', 'ld-dashboard' ); ?> </label>
				<input class="text-input" name="ldd_account_name" type="text" value="<?php echo ( isset( $withdrawal_data['ldd_account_name'] ) ) ? esc_attr( $withdrawal_data['ldd_account_name'] ) : ''; ?>" />
			</div>
			<div class="ld-dashboard-profile-form-field">
				<label for="pass1"><?php esc_html_e( 'Account Number', 'ld-dashboard' ); ?> </label>
				<input class="text-input ld-pass1" name="ldd_account_number" type="text" value="<?php echo ( isset( $withdrawal_data['ldd_account_number'] ) ) ? esc_attr( $withdrawal_data['ldd_account_number'] ) : ''; ?>" />
			</div>
			<div class="ld-dashboard-profile-form-field">
				<label for="pass2"><?php esc_html_e( 'Bank Name', 'ld-dashboard' ); ?></label>
				<input class="text-input ld-pass2" name="ldd_bank_name" type="text" value="<?php echo ( isset( $withdrawal_data['ldd_bank_name'] ) ) ? esc_attr( $withdrawal_data['ldd_bank_name'] ) : ''; ?>" />
			</div>
			<div class="ld-dashboard-profile-form-field">
				<label for="pass2"><?php esc_html_e( 'IBAN', 'ld-dashboard' ); ?></label>
				<input class="text-input ld-pass2" name="ldd_iban" type="text" value="<?php echo ( isset( $withdrawal_data['ldd_iban'] ) ) ? esc_attr( $withdrawal_data['ldd_iban'] ) : ''; ?>" />
			</div>
			<div class="ld-dashboard-profile-form-field">
				<label for="pass2"><?php esc_html_e( 'BIC / SWIFT', 'ld-dashboard' ); ?></label>
				<input class="text-input ld-pass2" name="ldd_bic_swift" type="text" value="<?php echo ( isset( $withdrawal_data['ldd_bic_swift'] ) ) ? esc_attr( $withdrawal_data['ldd_bic_swift'] ) : ''; ?>" />
			</div>
		</div>
		<div class="ld-dashboard-withdraw-method-fields" data-type="e_check">
			<div class="ld-dashboard-profile-form-field form-password repeat-new-nassword">
				<label for="pass1"><?php esc_html_e( 'Your Physical Address', 'ld-dashboard' ); ?> </label>
				<input class="text-input" name="ldd_physical_address" type="text" value="<?php echo ( isset( $withdrawal_data['ldd_physical_address'] ) ) ? esc_attr( $withdrawal_data['ldd_physical_address'] ) : ''; ?>" />
				<small><?php esc_html_e( 'We will send you an E-Check to this address directly.', 'ld-dashboard' ); ?> </small>
			</div>
		</div>
		<div class="ld-dashboard-withdraw-method-fields" data-type="paypal">
			<div class="ld-dashboard-profile-form-field form-password repeat-new-nassword">
				<label for="pass1"><?php esc_html_e( 'PayPal E-Mail Address', 'ld-dashboard' ); ?> </label>
				<input class="text-input" name="ldd_paypal_email" type="email" value="<?php echo ( isset( $withdrawal_data['ldd_paypal_email'] ) ) ? esc_attr( $withdrawal_data['ldd_paypal_email'] ) : ''; ?>" />
				<small><?php esc_html_e( 'We will use this email address to send the money to your Paypal account', 'ld-dashboard' ); ?> </small>
			</div>
		</div>
		<div class="ld-dashboard-withdraw-method-fields" data-type="stripe">
			<div class="ld-dashboard-profile-form-field form-password repeat-new-nassword">
				<label for="ld-dashboard-stripe-payment-link"><?php esc_html_e( 'Payment Link', 'ld-dashboard' ); ?> </label>
				<input id="ld-dashboard-stripe-payment-link" class="text-input" name="ldd_stripe_payment_link" type="url" value="<?php echo ( isset( $withdrawal_data['ldd_stripe_payment_link'] ) ) ? esc_attr( $withdrawal_data['ldd_stripe_payment_link'] ) : ''; ?>" />
				<small><?php esc_html_e( 'We will use this payment link to send the money to your stripe account.', 'ld-dashboard' ); ?> </small>
			</div>
		</div>
		<div class="form-submit">
			<button name="save-withdrawal-method" type="submit" id="ldd_save_withdraw_method" class="submit button"><?php esc_html_e( 'Save Withdrawal Account', 'ld-dashboard' ); ?></button>
			<?php wp_nonce_field( 'withdraw-method' ); ?>
			<input name="action" type="hidden" id="action" value="save-withdrawal-method" />
		</div>
	</form>
	<?php else : ?>
		<div class="ld-dashboard-withdraw-messages">
			<?php printf( __( 'Seems there is not enable any withdrawal method, please contact the site admin.', 'ld-dashboard' ) ); ?>
		</div>
	<?php endif; ?>
</div>
