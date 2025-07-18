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
 * @var WC_Email $email
 * @var string   $email_heading
 * @var string   $username
 * @var string   $card_type
 * @var string   $last4
 * @var string   $expiration_date
 * @var string   $site_title
 * @var string   $update_card_url
 * @var string   $update_card_fg
 * @var string   $update_card_bg
 * @var bool     $already_expired
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	// translators: 1. Username.
	echo esc_html( sprintf( __( 'Hi %s,', 'yith-woocommerce-stripe' ), $username ) );
	?>
</p>
<p>
	<?php
		echo wp_kses_post(
			sprintf(
				$already_expired ?
					// translators: 1. Card type (Visa/Mastercard...) 2. Last 4 digit of card number 3. Formatted expiration date. 4 is the site title.
					__( 'This is a friendly reminder that your <b>%1$s</b> card ending in <b>%2$s</b> expired on <b>%3$s</b>. To continue purchasing, please update your card information on %4$s.', 'yith-woocommerce-stripe' ) :
					// translators: 1. Card type (Visa/Mastercard...) 2. Last 4 digit of card number 3. Formatted expiration date. 4 is the site title.
					__( 'This is a friendly reminder that your <b>%1$s</b> card ending in <b>%2$s</b> expires on <b>%3$s</b>. To continue purchasing, please update your card information on %4$s.', 'yith-woocommerce-stripe' ),
				$card_type,
				$last4,
				$expiration_date,
				$site_title
			)
		);
		?>
</p>

<p style="text-align: center;">
	<a class="button alt" href="<?php echo esc_url( $update_card_url ); ?>" style="color: <?php echo esc_attr( $update_card_fg ); ?> !important; font-weight: normal; text-decoration: none !important; display: inline-block; background: <?php echo esc_attr( $update_card_bg ); ?>; border-radius: 5px; padding: 10px 20px; white-space: nowrap; margin-top: 20px; margin-bottom: 30px;"><?php esc_html_e( 'Update now', 'yith-woocommerce-stripe' ); ?></a>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
