<?php
/**
 * Expiring card reminder template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Templates\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Template variblaes.
 *
 * @var string $email_heading
 * @var string $username
 * @var string $card_type
 * @var string $last4
 * @var string $expiration_date
 * @var string $site_title
 * @var string $update_card_url
 * @var string $update_card_fg
 * @var string $update_card_bg
 * @var bool   $already_expired
 */

echo '= ' . esc_html( $email_heading ) . " =\n\n";
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
?>

<?php
// translators: 1. Username.
echo esc_html( sprintf( __( 'Hi %s,', 'yith-woocommerce-stripe' ), $username ) );
?>

<?php
echo wp_kses_post(
	sprintf(
		$already_expired ?
			// translators: 1. Card type (Visa/Mastercard...) 2. Last 4 digit of card number 3. Formatted expiration date. 4 is the site title.
			__( "This is a friendly reminder that your %1\$s card ending in %2\$s expired on %3\$s. To continue purchasing, please update your card information on %4\$s.\n\n", 'yith-woocommerce-stripe' ) :
			// translators: 1. Card type (Visa/Mastercard...) 2. Last 4 digit of card number 3. Formatted expiration date. 4 is the site title.
			__( "This is a friendly reminder that your %1\$s card ending in %2\$s expires on %3\$s. To continue purchasing, please update your card information on %4\$s.\n\n", 'yith-woocommerce-stripe' ),
		$card_type,
		$last4,
		$expiration_date,
		$site_title
	)
);
?>

<?php
// translators: 1. Updated card url.
echo esc_html( sprintf( __( 'Update now (%s)', 'yith-woocommerce-stripe' ), $update_card_url ) );
?>

<?php
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
