<?php
/**
 * Stripe Webhook section view
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Views
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly.

$webhook_doc = 'https://stripe.com/docs/webhooks';
?>

<p><?php esc_html_e( 'It\'s important to note that only test webhooks will be sent to your development webhook URL. Yet, if you are working on a live website,', 'yith-woocommerce-stripe' ); ?><strong><?php esc_html_e( ' both live and test', 'yith-woocommerce-stripe' ); ?></strong> <?php esc_html_e( 'webhooks will be sent to your production webhook URL.', 'yith-woocommerce-stripe' ); ?></p>
<p><?php esc_html_e( 'This is because you can create both live and test objects under a production application.', 'yith-woocommerce-stripe' ); ?></p>
<p class="yith-wcstripe-webhook" style="margin:1em 0;"><?php esc_html_e( 'For more information about webhooks, see the ', 'yith-woocommerce-stripe' ); ?><a href="<?php echo esc_url( $webhook_doc ); ?>" target="_blank"><?php esc_html_e( 'webhook documentation.', 'yith-woocommerce-stripe' ); ?></a></p>
<p><?php esc_html_e( 'You can automatically configure your webhooks for test environment by using the following shortcut:', 'yith-woocommerce-stripe' ); ?></p>
<button id="config_webhook" class="yith-plugin-fw__button--primary webhook-btn"><?php esc_html_e( 'Configure webhooks', 'yith-woocommerce-stripe' ); ?></button>
