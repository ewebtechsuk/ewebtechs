<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Admin_Premium' ) ) {
	/**
	 * WooCommerce Stripe main class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Admin_Premium extends YITH_WCStripe_Admin {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			parent::__construct();

			// enqueue admin scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

			// ajax actions.
			add_action( 'wp_ajax_yith_wcstripe_set_webhook', array( $this, 'set_webhook' ) );

			include_once 'class-yith-stripe-blacklist-admin.php';
		}

		/**
		 * Enqueue admin scripts
		 *
		 * @return void
		 * @since 1.5.1
		 */
		public function enqueue() {
			$current_screen    = get_current_screen();
			$current_screen_id = $current_screen->id;
			$suffix            = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'yith-wcstripe-admin', YITH_WCSTRIPE_URL . 'assets/css/admin.css', array(), YITH_WCSTRIPE_VERSION );

			wp_register_script( 'stripe-js', YITH_WCSTRIPE_URL . 'assets/js/admin/yiths' . $suffix . '.js', array( 'jquery', 'jquery-blockui' ), YITH_WCSTRIPE_VERSION, true );
			wp_localize_script(
				'stripe-js',
				'yith_stripe',
				array(
					'actions'  => array(
						'set_webhook' => 'yith_wcstripe_set_webhook',
					),
					'security' => array(
						'set_webhook' => wp_create_nonce( 'set_webhook' ),
					),
				)
			);

			if ( 'yith-plugins_page_yith_wcstripe_panel' === $current_screen_id || ( 'woocommerce_page_wc-settings' === $current_screen_id && isset( $_GET['section'] ) && 'yith-stripe' === $_GET['section'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_script( 'stripe-js' );
			}
		}

		/**
		 * Set webhook url on Stripe, to allow site receive notification from Stripe's servers
		 *
		 * @return void
		 */
		public function set_webhook() {
			if ( ! isset( $_GET['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['security'] ) ), 'set_webhook' ) ) {
				$res = false;
			} else {
				$gateway = YITH_WCStripe()->get_gateway();

				if ( ! $gateway ) {
					$res = false;
				} else {
					/**
					 * APPLY_FILTERS: yith_wcstripe_environment
					 *
					 * Filters Stripe environment. Test or live.
					 *
					 * @param string 'test' if enabled test mode or set as in development. Otherwise 'live'.
					 *
					 * @return string
					 */
					$env = apply_filters( 'yith_wcstripe_environment', ( 'yes' === $gateway->get_option( 'enabled_test_mode' ) || ( defined( 'WP_ENV' ) && 'development' === WP_ENV ) ) ? 'test' : 'live' );

					try {
						$gateway->init_stripe_sdk();
						$res = $gateway->api->create_webhook(
							array(
								'enabled_events' => array(
									'charge.captured',
									'charge.refunded',
									'charge.dispute.created',
									'charge.dispute.closed',
									'customer.updated',
									'customer.source.created',
									'customer.source.updated',
									'customer.source.deleted',
									'customer.subscription.deleted',
									'invoice.payment_succeeded',
									'invoice.payment_failed',
								),
								'url'            => esc_url( add_query_arg( 'wc-api', 'stripe_webhook', site_url( '/' ) ) ),
							)
						);

						if ( $res ) {
							update_option( "yith_wcstripe_{$env}_webhook_processed", true );
						}
					} catch ( Exception $e ) {
						$res = false;
					}
				}
			}

			wp_send_json(
				array(
					'status'  => $res,
					'message' => $res ? __( 'Webhook correctly configured', 'yith-woocommerce-stripe' ) : __( 'It wasn\'t possible to configure webhooks correctly; please, try again later.', 'yith-woocommerce-stripe' ),
				)
			);
		}
	}
}
