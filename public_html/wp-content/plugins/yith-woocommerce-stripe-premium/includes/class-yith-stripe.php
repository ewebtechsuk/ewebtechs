<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Main class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe' ) ) {
	/**
	 * WooCommerce Stripe main class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCStripe
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Stripe gateway id
		 *
		 * @var string Id of specific gateway
		 * @since 1.0
		 */
		public static $gateway_id = 'yith-stripe';

		/**
		 * The gateway object
		 *
		 * @var YITH_WCStripe_Gateway|YITH_WCStripe_Gateway_Advanced
		 * @since 1.0
		 */
		protected $gateway = null;

		/**
		 * Admin main class
		 *
		 * @var YITH_WCStripe_Admin
		 */
		public $admin = null;

		/**
		 * Zero decimals currencies
		 *
		 * @var array Zero decimals currencies
		 */
		public static $zero_decimals = array(
			'BIF',
			'CLP',
			'DJF',
			'GNF',
			'JPY',
			'KMF',
			'KRW',
			'MGA',
			'PYG',
			'RWF',
			'VND',
			'VUV',
			'XAF',
			'XOF',
			'XPF',
		);

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCStripe
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'privacy_loader' ), 20 );

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );

			// custom query param.
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'handle_custom_query_var' ), 10, 2 );

			// capture charge if completed, only if set the option.
			add_action( 'woocommerce_order_status_processing_to_completed', array( $this, 'capture_charge' ) );
			add_action( 'woocommerce_payment_complete', array( $this, 'capture_charge' ) );

			// includes.
			if ( file_exists( YITH_WCSTRIPE_INC . 'functions-yith-stripe.php' ) ) {
				include_once YITH_WCSTRIPE_INC . 'functions-yith-stripe.php';
			}

			// handle legacy filters.
			yith_wcstripe_legacy_filters();

			// admin includes.
			if ( is_admin() ) {
				include_once 'class-yith-stripe-admin.php';
				if ( ! defined( 'YITH_WCSTRIPE_PREMIUM' ) || ! YITH_WCSTRIPE_PREMIUM ) {
					$this->admin = new YITH_WCStripe_Admin();
				}
			}

			// security check.
			add_action( 'init', array( $this, 'security_check' ) );

			// add filter to append wallet as payment gateway.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_to_gateways' ) );
			add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'add_to_blocks' ) );
		}

		/* === PRIVACY LOADER === */

		/**
		 * Loads privacy class
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function privacy_loader() {
			if ( class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
				require_once YITH_WCSTRIPE_INC . 'class-yith-stripe-privacy.php';
				new YITH_WCStripe_Privacy();
			}
		}

		/**
		 * Declare support for WooCommerce features.
		 */
		public function declare_wc_features_support() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_WCSTRIPE_INIT, true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', YITH_WCSTRIPE_INIT, true );
			}
		}

		/**
		 * Adds Stripe Gateway to payment gateways available for woocommerce checkout
		 *
		 * @param array $methods Previously available gataways, to filter with the function.
		 *
		 * @return array New list of available gateways
		 * @since 1.0.0
		 */
		public function add_to_gateways( $methods ) {
			/**
			 * APPLY_FILTERS: yith_wcstripe_gateway_id
			 *
			 * Filters Stripe gateway ID.
			 *
			 * @param string $gateway_id The gateway ID. Default value: 'yith-stripe'.
			 *
			 * @return string
			 */
			self::$gateway_id = apply_filters( 'yith_wcstripe_gateway_id', self::$gateway_id );

			include_once 'class-yith-stripe-gateway.php';
			$methods[] = 'YITH_WCStripe_Gateway';

			return $methods;
		}

		/**
		 * Adds Stripe Gateway to payment gateways available for woocommerce block checkout
		 *
		 * @param Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry Payment method registry.
		 * @since 3.14.0
		 */
		public function add_to_blocks( $payment_method_registry ) {
			include_once YITH_WCSTRIPE_DIR . 'includes/class-yith-stripe-payment-method-type.php';

			$payment_method_registry->register( new YITH_WCStripe_Payment_Method_Type() );
		}

		/**
		 * Get the gateway object
		 *
		 * @return YITH_WCStripe_Gateway|YITH_WCStripe_Gateway_Advanced|YITH_WCStripe_Gateway_Addons
		 * @since 1.0.0
		 */
		public function get_gateway() {
			if ( ! is_a( $this->gateway, 'YITH_WCStripe_Gateway' ) && ! is_a( $this->gateway, 'YITH_WCStripe_Gateway_Advanced' ) && ! is_a( $this->gateway, 'YITH_WCStripe_Gateway_Addons' ) ) {
				$gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( ! isset( $gateways[ self::$gateway_id ] ) ) {
					return false;
				}

				$this->gateway = $gateways[ self::$gateway_id ];
			}

			return $this->gateway;
		}

		/**
		 * Checks whether plugin is currently active on the site it was originally installed
		 *
		 * If site url has changed from original one, it could happen that db was cloned on another installation
		 * To avoid this installation (maybe a staging one) to interact with original stripe data, we enable
		 *
		 * @return void
		 * @since 1.8.2
		 */
		public function security_check() {
			$registered_url = get_option( 'yith_wcstripe_registered_url', '' );

			if ( ! $registered_url ) {
				update_option( 'yith_wcstripe_registered_url', get_site_url() );

				return;
			}

			$registered_url = str_replace( array( 'https://', 'http://', 'www.' ), '', $registered_url );
			$current_url    = str_replace( array( 'https://', 'http://', 'www.' ), '', get_site_url() );

			/**
			 * APPLY_FILTERS: yith_wcstripe_do_instances_match
			 *
			 * Filters if current URL matches the URL where it was originally installed.
			 *
			 * @param bool                   Value comming from both URLs comparison.
			 * @param string $current_url    Current URL.
			 * @param string $registered_url Registered URL.
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'yith_wcstripe_do_instances_match', $current_url === $registered_url, $current_url, $registered_url ) ) {
				$gateway_id      = self::$gateway_id;
				$gateway_options = get_option( "woocommerce_{$gateway_id}_settings", array() );

				if ( isset( $gateway_options['enabled_test_mode'] ) && 'no' === $gateway_options['enabled_test_mode'] ) {
					$gateway_options['enabled_test_mode'] = 'yes';

					update_option( "woocommerce_{$gateway_id}_settings", $gateway_options );
					update_option( 'yith_wcstripe_site_changed', 'yes' );
				}
			}
		}

		/**
		 * Capture charge if the payment is been only authorized
		 *
		 * @param int $order_id Order id.
		 *
		 * @throws Exception When an error occurs with API handling.
		 *
		 * @since 1.0.0
		 */
		public function capture_charge( $order_id ) {

			// get order data.
			$order = wc_get_order( $order_id );

			// check if payment method is Stripe.
			if ( self::$gateway_id !== $order->get_payment_method() ) {
				return;
			}

			// exit if the order is in processing.
			if ( $order->has_status( 'processing' ) ) {
				return;
			}

			// lets third party plugin skip this execution.
			/**
			 * APPLY_FILTERS: yith_stripe_skip_capture_charge
			 *
			 * Filters if should skip capture charge.
			 *
			 * @param bool           Default value: true.
			 * @param int  $order_id The order ID.
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'yith_stripe_skip_capture_charge', true, $order_id ) ) {
				return;
			}

			$order_total = (float) $order->get_total();

			// Support to subscriptions.
			if ( ! $order_total || $order_total === (float) $order->get_meta( '_stripe_subscription_total' ) ) {
				$order->update_meta_data( '_captured', 'yes' );
				$order->save();

				return;
			}

			$transaction_id = $order->get_transaction_id();
			$intent_id      = $order->get_meta( 'intent_id' );
			$captured       = 'yes' === $order->get_meta( '_captured' );

			if ( $captured ) {
				return;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return;
			}

			try {
				// init Stripe api.
				$gateway->init_stripe_sdk();

				if ( $intent_id ) {
					$intent = $gateway->api->get_intent( $intent_id );

					if ( $intent && 'requires_capture' === $intent->status ) {
						/**
						 * APPLY_FILTERS: yith_wcstripe_capture_charge_params
						 *
						 * Filters capture charge parameters.
						 *
						 * @param null                          Default value: null.
						 * @param \Stripe\PaymentIntent $intent Payment intent.
						 * @param WC_Order              $order  The order object.
						 *
						 * @return null|array
						 */
						$params = apply_filters( 'yith_wcstripe_capture_charge_params', null, $intent, $order );
						$intent->capture( $params );
					}
				} else {
					if ( ! $transaction_id ) {
						$order_total = (float) $order->get_total();

						// Support to subscriptions with trial period.
						if ( ! $order_total || $order_total === (float) $order->get_meta( '_stripe_subscription_total' ) ) {
							$order->update_meta_data( '_captured', 'yes' );
							$order->save();

							return;
						} else {
							throw new Exception( __( 'Stripe Credit Card charge failed because the transaction ID is missing.', 'yith-woocommerce-stripe' ) );
						}
					}

					// capture.
					$charge = $gateway->api->capture_charge( $transaction_id );
				}

				// update post meta.
				$order->update_meta_data( '_captured', 'yes' );
				$order->save();

			} catch ( Exception $e ) {
				$message = $e->getMessage();
				// translators: 1. Error message.
				$order->add_order_note( sprintf( __( 'Stripe Error - Charge not captured. %s', 'yith-woocommerce-wishlist' ), $message ) );

				if ( is_admin() ) {
					wp_die( esc_html( $message ) );
				}

				if ( function_exists( 'wc_add_notice' ) ) {
					wc_add_notice( $message, 'error' );
				}
			}
		}

		/**
		 * Returns order details for hosted checkout
		 */
		public function send_checkout_details() {
			check_ajax_referer( 'yith-stripe-refresh-details', 'refresh-details', true );

			WC()->cart->calculate_totals();

			wp_send_json(
				array(
					'amount'   => self::get_amount( WC()->cart->total ),
					'currency' => strtolower( get_woocommerce_currency() ),
				)
			);
		}

		/**
		 * Register custom query vars for orders filtering
		 *
		 * @param array $query      Current query configuration.
		 * @param array $query_vars Query vars passed to wc_get_orders function.
		 *
		 * @return array Array of filtered query configuration
		 */
		public function handle_custom_query_var( $query, $query_vars ) {
			if ( ! empty( $query_vars['stripe_session_id'] ) ) {
				$query['meta_query'][] = array(
					'key'   => 'session_id',
					'value' => esc_attr( $query_vars['stripe_session_id'] ),
				);
			}

			return $query;
		}

		/**
		 * Get Stripe amount to pay
		 *
		 * @param float    $total    Amount to convert to Stripe "no-decimal" format.
		 * @param string   $currency Currency for the order.
		 * @param WC_Order $order    Order object.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public static function get_amount( $total, $currency = '', $order = null ) {
			if ( empty( $currency ) ) {
				$currency = get_woocommerce_currency();
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_gateway_amount
			 *
			 * Filters Stripe gateway's amount.
			 *
			 * @param int      $amount Total amount.
			 * @param WC_Order $order  Order object.
			 *
			 * @return int
			 */
			$total = apply_filters( 'yith_wcstripe_gateway_amount', $total, $order );

			if ( ! in_array( $currency, self::$zero_decimals, true ) ) {
				$total *= 100;
			}

			return round( $total );
		}

		/**
		 * Get original amount
		 *
		 * @param float  $total    Total to convert back from "no-decimal" format.
		 * @param string $currency Currency for the amount.
		 *
		 * @return float
		 * @since 1.0.0
		 */
		public static function get_original_amount( $total, $currency = '' ) {
			if ( empty( $currency ) ) {
				$currency = get_woocommerce_currency();
			}

			if ( in_array( $currency, self::$zero_decimals, true ) ) {
				$total = absint( $total );
			} else {
				$total /= 100;
			}

			return $total;
		}
	}
}
