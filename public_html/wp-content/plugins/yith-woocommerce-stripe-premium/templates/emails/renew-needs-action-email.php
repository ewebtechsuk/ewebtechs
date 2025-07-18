<?php
/**
 * Renew needs action email template
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
 * @var string   $email_heading
 * @var string   $username
 * @var string   $pay_renew_url
 * @var string   $pay_renew_fg
 * @var string   $pay_renew_bg
 * @var WC_Order $order
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var $email   WC_Email
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	// translators: 1. Username.
	echo esc_html( sprintf( __( 'Hi %s,', 'yith-woocommerce-stripe' ), $username ) );
	?>
</p>

{opening_text}

<p style="text-align: center;">
	<a class="button alt" href="<?php echo esc_url( $pay_renew_url ); ?>" style="color: <?php echo esc_attr( $pay_renew_fg ); ?> !important; font-weight: normal; text-decoration: none !important; display: inline-block; background: <?php echo esc_attr( $pay_renew_bg ); ?>; border-radius: 5px; padding: 10px 20px; white-space: nowrap; margin-top: 20px; margin-bottom: 30px;"><?php esc_html_e( 'Confirm payment', 'yith-woocommerce-stripe' ); ?></a>
</p>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
?>

{closing_text}

<?php do_action( 'woocommerce_email_footer', $email ); ?>
