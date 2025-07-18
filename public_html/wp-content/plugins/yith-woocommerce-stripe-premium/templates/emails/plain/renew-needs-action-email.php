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

echo '= ' . esc_html( $email_heading ) . " =\n\n";
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
?>

<?php
// translators: 1. Username.
echo esc_html( sprintf( __( 'Hi %s,', 'yith-woocommerce-stripe' ), $username ) );
?>

{opening_text}

<?php
// translators: 1. Updated card url.
echo esc_html( sprintf( __( 'Confirm payment (%s)', 'yith-woocommerce-stripe' ), $pay_renew_url ) );
?>

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

<?php
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
