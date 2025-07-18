<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Gateway class - Advanced
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes\Gateways
 * @version 1.0.0
 */

use Stripe\PaymentIntent;
use Stripe\SetupIntent;

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Gateway_Advanced' ) ) {
	/**
	 * WooCommerce Stripe gateway class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Gateway_Advanced extends YITH_WCStripe_Gateway {

		/**
		 * Whether to save cards or not
		 *
		 * @var string $save_cards (yes|no)
		 */
		public $save_cards;

		/**
		 * Whether to capture payment or not
		 *
		 * @var string $capture (yes|no)
		 */
		public $capture;

		/**
		 * Wheter to enable debug or not
		 *
		 * @var string $debug (yes|no)
		 */
		public $debug;

		/**
		 * Whether to add billing fields to standard form or not
		 *
		 * @var bool $add_billing_fields
		 */
		public $add_billing_fields;

		/**
		 * Whether to hosted billing form on Stripe Checkout or not
		 *
		 * @var bool $hosted_billing
		 */
		public $hosted_billing;

		/**
		 * Whether to hosted shipping form on Stripe Checkout or not
		 *
		 * @var bool $hosted_shipping
		 */
		public $hosted_shipping;

		/**
		 * Whether to show ZIP field on elements form or not
		 *
		 * @var bool $elements_show_zip
		 */
		public $elements_show_zip;

		/**
		 * Whether to show "Name on card" field for Standard and Elements mode
		 *
		 * @var bool $show_name_on_card
		 */
		public $show_name_on_card;

		/**
		 * Whether to automatically save cards, or asck the customer
		 *
		 * @var string $save_cards_mode (prompt|register)
		 */
		public $save_cards_mode;

		/**
		 * Current Stripe customer
		 *
		 * @var $current_customer \Stripe\Customer
		 */
		protected $current_customer = null;

		/**
		 * Current intent secret
		 *
		 * @var $current_intent_secret string
		 */
		protected $current_intent_secret = null;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct();

			// gateway properties.
			$this->order_button_text = $this->get_option( 'button_label', __( 'Place order', 'yith-woocommerce-stripe' ) );
			$this->new_method_label  = __( 'Use a new card', 'yith-woocommerce-stripe' );
			$this->supports          = array(
				'products',
				'default_credit_card_form',
				'refunds',
			);

			// Define user set variables.
			/**
			 * APPLY_FILTERS: yith_wcstripe_mode
			 *
			 * Filters Stripe checkout mode.
			 *
			 * @param string                         Chosen mode in Stripe plugin configuration. 'standard' if none.
			 * @param YITH_WCStripe_Gateway_Advanced Class instance.
			 *
			 * @return string
			 */
			$this->mode               = apply_filters( 'yith_wcstripe_mode', $this->get_option( 'mode', 'standard' ), $this );
			$this->debug              = 'yes' === $this->get_option( 'debug' );
			$this->save_cards         = 'yes' === $this->get_option( 'save_cards', 'yes' );
			$this->capture            = 'yes' === $this->get_option( 'capture', 'no' );
			$this->add_billing_fields = 'yes' === $this->get_option( 'add_billing_fields', 'no' );
			$this->hosted_billing     = 'yes' === $this->get_option( 'add_billing_hosted_fields', 'no' );
			$this->hosted_shipping    = 'yes' === $this->get_option( 'add_shipping_hosted_fields', 'no' );
			$this->show_name_on_card  = 'yes' === $this->get_option( 'show_name_on_card', 'yes' );
			$this->elements_show_zip  = 'yes' === $this->get_option( 'elements_show_zip', 'yes' );
			$this->save_cards_mode    = $this->get_option( 'save_cards_mode', 'register' );
			$this->renew_mode         = $this->get_option( 'renew_mode', 'stripe' );

			if ( 'hosted_std' === $this->mode ) {
				$this->update_option( 'mode', 'hosted' );
				$this->mode = 'hosted';
			}

			// enable tokenization support if the option is enabled.
			if ( in_array( $this->mode, array( 'standard', 'elements' ), true ) && $this->save_cards ) {
				$this->supports[] = 'tokenization';
			}

			// logs.
			if ( $this->debug ) {
				$this->log = new WC_Logger();
			}

			// hooks.
			add_filter( 'woocommerce_credit_card_form_fields', array( $this, 'add_credit_card_form_fields' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_payment_styles' ) );
		}

		/* === GATEWAY METHODS === */

		/**
		 * Get return url for payment intent
		 *
		 * @param \WC_Order $order Order we're currently processing.
		 *
		 * @return string Return url
		 */
		public function get_return_url( $order = null ) {
			$redirect = parent::get_return_url( $order );

			if ( ! $order || empty( $this->current_intent_secret ) ) {
				return $redirect;
			}

			// Retrieve verification URL, where customer are redirected after payment is complete.
			$verification_url = $this->get_verification_url( $order );

			// Combine into a hash.
			$redirect = sprintf( '#yith-confirm-pi-%s/%s', $this->current_intent_secret, rawurlencode( $verification_url ) );

			return $redirect;
		}

		/**
		 * Returns verification url
		 * At that endpoint status of the payment will be checked, and if everything is fine
		 * customer will be redirected to thank you page
		 *
		 * @param \WC_Order $order Order we're currently processing.
		 * @return string Verification url
		 */
		public function get_verification_url( $order = null ) {
			$redirect = parent::get_return_url( $order );

			if ( ! $order ) {
				return $redirect;
			}

			return WP_Http::make_absolute_url(
				add_query_arg(
					array(
						'order'       => $order->get_id(),
						'order_id'    => $order->get_id(),
						'redirect_to' => rawurlencode( $redirect ),
						'_wpnonce'    => wp_create_nonce( 'verify_intent' ),
					),
					WC_AJAX::get_endpoint( 'yith_wcstripe_verify_intent' )
				),
				home_url()
			);
		}

		/* === PAYMENT METHODS === */

		/**
		 * Handling payment and processing the order.
		 *
		 * @param int    $order_id       Id of order to pay.
		 * @param string $payment_method Unique identifier of the payment method that should be used for this payment; if empty, posted value will be used instead.
		 *
		 * @throws Exception When an error happens while processing API payment.
		 * @return array
		 * @since 1.0.0
		 */
		public function process_payment( $order_id, $payment_method = '' ) {
			$order               = wc_get_order( $order_id );
			$this->current_order = $order;

			$this->log( 'Generating payment form for order ' . $order->get_order_number() . '.' );

			if ( 'hosted_std' === $this->mode || 'hosted' === $this->mode ) {
				return $this->process_hosted_payment();
			} else {
				return $this->process_standard_payment( $order, $payment_method );
			}
		}

		/**
		 * Process refund
		 *
		 * Overriding refund method
		 *
		 * @access      public
		 *
		 * @param int    $order_id Order to refund.
		 * @param float  $amount   Amount to refund.
		 * @param string $reason   Reason for refund.
		 *
		 * @return      mixed True or False based on success, or WP_Error
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			$order          = wc_get_order( $order_id );
			$transaction_id = $order->get_transaction_id();
			$order_currency = $this->get_currency( $order );

			if ( ! $transaction_id ) {
				return new WP_Error( 'yith_stripe_no_transaction_id', __( "There isn't any charge linked to this order.", 'yith-woocommerce-stripe' ) );
			}

			if ( $order->get_meta( 'bitcoin_inbound_address' ) || $order->get_meta( 'bitcoin_uri' ) ) {
				return new WP_Error( 'yith_stripe_no_bitcoin', __( 'Refund not supported for bitcoin', 'yith-woocommerce-stripe' ) );
			}

			try {

				// Initializate SDK and set private key.
				$this->init_stripe_sdk();

				$params = array();

				// get the last refund object created before to process this method, to get own object.
				$refunds = $order->get_refunds();
				$refund  = array_shift( $refunds );

				/**
				 * First refund for $order.
				 *
				 * @var WC_Order_Refund $refund
				 */

				// If the amount is set, refund that amount, otherwise the entire amount is refunded.
				if ( $amount ) {
					$params['amount'] = YITH_WCStripe::get_amount( $amount, $order_currency, $order );
				}

				// If a reason is provided, add it to the Stripe metadata for the refund.
				if ( $reason && in_array( $reason, array( 'duplicate', 'fraudulent', 'requested_by_customer' ), true ) ) {
					$params['reason'] = $reason;
				}

				/**
				 * APPLY_FILTERS: yith_wcstripe_metadata
				 *
				 * Filters Stripe charge metadata.
				 *
				 * @param array  Default metadata.
				 * @param string Action type.
				 *
				 * @return array
				 */
				$params['metadata'] = apply_filters(
					'yith_wcstripe_metadata',
					array(
						'order_id' => $order_id,
						'instance' => $this->instance,
					),
					'refund'
				);

				$this->log( 'Stripe Refund Request: ' . print_r( $params, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				// Send the refund to the Stripe API.
				$stripe_refund = $this->api->refund( $transaction_id, $params );

				$refund->update_meta_data( '_refund_stripe_id', $stripe_refund->id );
				$refund->save();

				$this->log( 'Stripe Refund Response: ' . print_r( $stripe_refund, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				// translators: 1. Refund amount. 2. Refund id.
				$order->add_order_note( sprintf( __( 'Refunded %1$s - Refund ID: %2$s', 'woocommerce' ), $amount, $stripe_refund['id'] ) );

				return true;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$message = $this->error_handling(
					$e,
					array(
						'mode'   => 'note',
						'order'  => $order,
						// translators: 1. Error details.
						'format' => __( 'Stripe Credit Card refund failed with message: "%s"', 'yith-woocommerce-stripe' ),
					)
				);

				// Something failed somewhere, send a message.
				return new WP_Error( 'yith_stripe_refund_error', $message );
			}
		}

		/**
		 * Handling payment and processing the order.
		 *
		 * @param WC_Order $order          Order object, or null when not relevant.
		 * @param string   $payment_method Optional payment method ID.
		 *
		 * @return array
		 * @throws Exception When an error with payment occurs.
		 * @since 1.0.0
		 */
		protected function process_standard_payment( $order = null, $payment_method = '' ) {
			if ( empty( $order ) ) {
				$order = $this->current_order;
			}

			try {

				// Initialize SDK and set private key.
				$this->init_stripe_sdk();

				// retrieve payment intent.
				$intent = $this->get_intent( $order );

				// no intent yet; return error.
				if ( ! $intent || ( 0 === strpos( $intent->id, 'seti' ) && $order->get_total() > 0 ) ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ), null );
				}

				// intent refers to another transaction: return error.
				if ( $order->get_id() !== (int) $intent->metadata->order_id && yith_wcstripe_get_cart_hash() !== $intent->metadata->cart_hash ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ), null );
				}

				// nonce verification is not required, as we're running during checkout handling, and nonce was verified already by WC.
				// phpcs:disable WordPress.Security.NonceVerification.Missing
				if ( empty( $payment_method ) ) {
					$payment_method = isset( $_POST['stripe_payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_payment_method'] ) ) : false;
				}
				if ( ! $payment_method && isset( $_POST['wc-yith-stripe-payment-token'] ) && 'new' !== $_POST['wc-yith-stripe-payment-token'] ) {
					$token_id = intval( $_POST['wc-yith-stripe-payment-token'] );
					$token    = WC_Payment_Tokens::get( $token_id );

					if ( $token && $token->get_user_id() === get_current_user_id() && $token->get_gateway_id() === $this->id ) {
						$payment_method = $token->get_token();
					}
				}
				// phpcs:enable WordPress.Security.NonceVerification.Missing

				// it intent is missing payment method, or requires update, proceed with update.
				if (
					'requires_payment_method' === $intent->status && $payment_method ||
					(
						(
							YITH_WCStripe::get_amount( $order->get_total(), $order->get_currency(), $order ) !== (float) $intent->amount ||
							strtolower( $order->get_currency() ) !== $intent->currency ||
							$order->get_id() !== (int) $intent->metadata->order_id
						) &&
						! in_array( $intent->status, array( 'requires_action', 'requires_capture', 'succeeded', 'canceled' ), true )
					)
				) {
					// updates session intent.
					$intent = $this->update_session_intent( $payment_method, $order->get_id() );
				}

				// if intent is still missing payment method, return an error.
				if ( 'requires_payment_method' === $intent->status ) {
					throw new Exception( __( 'No payment method could be applied to this payment; please, try again by selecting another payment method.', 'yith-woocommerce-stripe' ) );
				}

				// intent requires confirmation; try to confirm it.
				if ( 'requires_confirmation' === $intent->status ) {
					$intent->confirm(
						array(
							'return_url' => $this->get_verification_url( $order ),
						)
					);
				}

				// register intent for the order.
				$order->update_meta_data( 'intent_id', $intent->id );

				// confirmation requires additional action; return to customer.
				if ( 'requires_action' === $intent->status ) {
					$order->save();

					// manual confirm after checkout.
					$this->current_intent_secret = $intent->client_secret;

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				}

				// everything done with the intent (payment has been approved); try to pay.
				$response = $this->pay( $order );

				if ( true === $response ) {
					$response = array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

				} elseif ( is_a( $response, 'WP_Error' ) ) {
					throw new Exception( $response->get_error_message( 'stripe_error' ) );
				}

				return $response;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$this->error_handling(
					$e,
					array(
						'mode'  => 'both',
						'order' => $order,
					)
				);

				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );

				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			}
		}

		/**
		 * Performs the payment on Stripe
		 *
		 * @param WC_Order $order  Order to pay, when relevant.
		 * @param float    $amount Amount to pay; if null, order total will be used instead.
		 *
		 * @return bool|WP_Error
		 * @throws Exception When something fails with intent handling or APIs.
		 * @since 1.0.0
		 */
		public function pay( $order = null, $amount = null ) {
			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$order_id = $order->get_id();

			// get amount.
			$amount = ! is_null( $amount ) ? (float) $amount : (float) $order->get_total();

			if ( ! $amount ) {
				// Payment complete.
				$order->payment_complete();

				return true;
			}

			// retrieve payment intent.
			$intent = $this->get_intent( $order );

			if ( ! $intent || 0 === strpos( $intent->id, 'seti' ) ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			}

			if ( 'requires_confirmation' === $intent->status ) {
				$intent->confirm(
					array(
						'return_url' => $this->get_verification_url( $order ),
					)
				);
			}

			if ( 'requires_action' === $intent->status ) {
				/**
				 * DO_ACTION: yith_wcstripe_intent_requires_action
				 *
				 * Triggered when payment requires action.
				 *
				 * @param \Stripe\PaymentIntent|bool $intent Payment intent or false on failure
				 * @param WC_Order                   $order  Order to pay, when relevant.
				 */
				do_action( 'yith_wcstripe_intent_requires_action', $intent, $order );

				return new WP_Error( 'stripe_error', __( 'Please, validate your payment method before proceeding further; in order to do this, refresh the page and proceed with checkout as usual', 'yith-woocommerce-stripe' ) );
			} elseif ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			}

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// update intent data.
			if ( ! isset( $intent->metadata ) || empty( $intent->metadata->order_id ) ) {
				$this->api->update_intent(
					$intent->id,
					array(
						/**
						 * APPLY_FILTERS: yith_wcstripe_charge_description
						 *
						 * Filters charge payment description.
						 *
						 * @param string Default description.
						 * @param string Site title.
						 * @param int    Order number.
						 *
						 * @return string
						 */
						// translators: 1. Blog name. 2. Order number.
						'description' => apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_metadata
						 *
						 * Filters Stripe charge metadata.
						 *
						 * @param array  Default metadata.
						 * @param string Action type.
						 *
						 * @return array
						 */
						'metadata'    => apply_filters(
							'yith_wcstripe_metadata',
							array(
								'order_id'    => $order_id,
								'order_email' => $order->get_billing_email(),
								'instance'    => $this->instance,
							),
							'charge'
						),
					)
				);
			}

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $this->api->get_charge( $charge );

			// attach payment method to customer.
			$customer = $this->get_customer( $order );

			// save card token.
			$token = $this->save_token( $intent->payment_method );

			if ( $token ) {
				$order->add_payment_token( $token );
			}

			// Payment complete.
			$order->payment_complete( $charge->id );

			// Add order note.
			// translators: 1. Charge id.
			$order->add_order_note( sprintf( __( 'Stripe payment approved (ID: %s)', 'yith-woocommerce-stripe' ), $charge->id ) );

			// Remove cart.
			WC()->cart->empty_cart();

			// delete session.
			$this->delete_session_intent();

			// update post meta.
			$order->update_meta_data( '_captured', $charge->captured ? 'yes' : 'no' );
			$order->update_meta_data( '_stripe_customer_id', $customer ? $customer->id : false );
			$order->save();

			// Return thank you page redirect.
			return true;
		}

		/**
		 * Performs the payment on ajax call
		 *
		 * @param \WC_Order $order Order to pay.
		 *
		 * @return bool|WP_Error True or WP_Error with details about the error
		 */
		public function pay_ajax( $order ) {
			try {
				return self::pay( $order );
			} catch ( Exception $e ) {
				return new WP_Error( 'stripe_error', $e->getMessage() );
			}
		}

		/* === FRONTEND METHODS === */

		/**
		 * Checks whether Stripe assets should be enqueued or not
		 *
		 * @return bool Whether to load scripts regarding this gateway.
		 */
		protected function should_load_scripts() {
			/**
			 * APPLY_FILTERS: yith_wcstripe_load_assets
			 *
			 * Filters if loading or not Stripe Assets.
			 *
			 * @param bool Default value: false.
			 *
			 * @return bool
			 */
			return $this->is_available() && ( is_checkout() || is_wc_endpoint_url( 'payment-methods' ) || is_wc_endpoint_url( 'add-payment-method' ) || has_block( 'woocommerce/checkout' ) || apply_filters( 'yith_wcstripe_load_assets', false ) );
		}

		/**
		 * Enqueue styles required by payment modules.
		 *
		 * @since 3.14.0
		 */
		public function enqueue_payment_styles() {
			if ( ! $this->should_load_scripts() ) {
				return;
			}
			// gateway style.
			if ( in_array( $this->mode, array( 'standard', 'elements' ), true ) ) {
				wp_register_style( 'stripe-css', YITH_WCSTRIPE_URL . 'assets/css/stripe.css', array(), YITH_WCSTRIPE_VERSION );
				wp_enqueue_style( 'stripe-css' );
			}

			// prettyPhoto.
			if ( 'standard' === $this->mode && ! wp_style_is( 'woocommerce_prettyPhoto_css', 'registered' ) ) {
				wp_enqueue_style( 'woocommerce_prettyPhoto_css', WC()->plugin_url() . '/assets/css/prettyPhoto.css', array(), WC()->version );
			}
		}

		/**
		 * Registers payment scripts, ready to be enqueued when needed
		 *
		 * @since 1.0.0
		 */
		public function register_payment_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( 'standard' === $this->mode && ! wp_script_is( 'prettyPhoto', 'registered' ) ) {
				wp_register_script( 'prettyPhoto', WC()->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.5', true );
			}

			// scripts.
			if ( 'hosted' === $this->mode || 'hosted_std' === $this->mode ) {
				wp_register_script( 'stripe-js', 'https://js.stripe.com/v3/', array( 'jquery' ), YITH_WCSTRIPE_VERSION, true );
				wp_register_script( 'yith-stripe-js', YITH_WCSTRIPE_URL . 'assets/js/stripe-checkout.bundle' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'stripe-js' ), YITH_WCSTRIPE_VERSION, true );

				wp_localize_script(
					'stripe-js',
					'yith_stripe_info',
					array(
						'slug'        => $this->id,
						'title'       => $this->get_title(),
						'description' => $this->get_description(),
						'public_key'  => $this->public_key,
						'mode'        => $this->mode,
						'ajaxurl'     => admin_url( 'admin-ajax.php' ),
					)
				);
			} elseif ( 'elements' === $this->mode || 'standard' === $this->mode ) {
				global $wp;

				$deps = array( 'jquery', 'stripe-js' );

				if ( $this->add_billing_fields ) {
					$deps[] = 'wc-country-select';
				}

				if ( 'standard' === $this->mode ) {
					$deps[] = 'prettyPhoto';
				}

				wp_register_script( 'stripe-js', 'https://js.stripe.com/v3/', array( 'jquery' ), YITH_WCSTRIPE_VERSION, true );
				wp_register_script( 'yith-stripe-js', YITH_WCSTRIPE_URL . 'assets/js/stripe-elements.bundle' . $suffix . '.js', $deps, YITH_WCSTRIPE_VERSION, true );

				wp_localize_script(
					'stripe-js',
					'yith_stripe_info',
					array(
						'slug'                  => $this->id,
						'title'                 => $this->get_title(),
						'description'           => $this->get_description(),
						'public_key'            => $this->public_key,
						'mode'                  => $this->mode,
						'show_name_on_card'     => $this->show_name_on_card,
						'elements_container_id' => '#' . esc_attr( $this->id ) . '-card-elements',
						'currency'              => strtolower( $this->get_currency() ),
						'show_zip'              => $this->elements_show_zip,
						'ajaxurl'               => admin_url( 'admin-ajax.php' ),
						'is_checkout'           => is_checkout(),
						'refresh_intent'        => wp_create_nonce( 'refresh-intent' ),
						'order'                 => isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false,
						'labels'                => array(
							'confirm_modal' => array(
								'title'          => __( 'Confirmation needed', 'yith-woocommerce-stripe' ),
								'body'           => __( 'Please, confirm if you want to delete this credit card. This action cannot be undone.', 'yith-woocommerce-stripe' ),
								'cancel_button'  => __( 'Cancel', 'yith-woocommerce-stripe' ),
								'confirm_button' => __( 'Confirm', 'yith-woocommerce-stripe' ),
							),
							'fields'        => $this->field_details(),
						),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_background_color
						 *
						 * Filters Stripe Elements checkout background color.
						 *
						 * @param string Default value: 'none'.
						 *
						 * @return string
						 */
						'background_color'      => apply_filters( 'yith_wcstripe_elements_background_color', 'none' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_font_size
						 *
						 * Filters Stripe Elements checkout font size.
						 *
						 * @param string Default value: '16px'.
						 *
						 * @return string
						 */
						'font_size'             => apply_filters( 'yith_wcstripe_elements_font_size', '16px' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_color
						 *
						 * Filters Stripe Elements checkout text color.
						 *
						 * @param string Default value: '#333'.
						 *
						 * @return string
						 */
						'color'                 => apply_filters( 'yith_wcstripe_elements_color', '#333' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_font_family
						 *
						 * Filters Stripe Elements checkout font family.
						 *
						 * @param string Default value: 'sans-serif'.
						 *
						 * @return string
						 */
						'font_family'           => apply_filters( 'yith_wcstripe_elements_font_family', 'sans-serif' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_placeholder_color
						 *
						 * Filters Stripe Elements checkout text placeholder color.
						 *
						 * @param string Default value: '#000000'.
						 *
						 * @return string
						 */
						'placeholder_color'     => apply_filters( 'yith_wcstripe_elements_placeholder_color', '#000000' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_icon_color
						 *
						 * Filters Stripe Elements checkout icon color.
						 *
						 * @param string Default value: '#3399ff'.
						 *
						 * @return string
						 */
						'icon_color'            => apply_filters( 'yith_wcstripe_elements_icon_color', ' #3399ff' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_invalid_color
						 *
						 * Filters Stripe Elements checkout invalid color.
						 *
						 * @param string Default value: '#e30000'.
						 *
						 * @return string
						 */
						'invalid_color'         => apply_filters( 'yith_wcstripe_elements_invalid_color', '#e30000' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_invalid_icon_color
						 *
						 * Filters Stripe Elements checkout invalid icon color.
						 *
						 * @param string Default value: '#e30000'.
						 *
						 * @return string
						 */
						'invalid_icon_color'    => apply_filters( 'yith_wcstripe_elements_invalid_icon_color', '#e30000' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_complete_color
						 *
						 * Filters Stripe Elements checkout complete color.
						 *
						 * @param string Default value: '#009124'.
						 *
						 * @return string
						 */
						'complete_color'        => apply_filters( 'yith_wcstripe_elements_complete_color', '#009124' ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_elements_show_icon
						 *
						 * Filters Stripe Elements checkout show icon.
						 *
						 * @param bool Default value: 'true'.
						 *
						 * @return bool
						 */
						'show_icon'             => apply_filters( 'yith_wcstripe_elements_show_icon', true ),
					)
				);
			}

			if ( ! wp_script_is( 'jquery-blockui', 'registered' ) ) {
				wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
			}

			wp_register_script( 'yith-stripe-block-js', YITH_WCSTRIPE_URL . 'assets/js/stripe-block.bundle' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'stripe-js' ), YITH_WCSTRIPE_VERSION, true );
		}

		/**
		 * Payment form on checkout page
		 *
		 * @since 1.0.0
		 */
		public function payment_fields() {
			$description = $this->get_description();

			if ( $description ) {
				echo wp_kses_post( apply_filters( 'yith_wcstripe_checkout_description', wpautop( wptexturize( trim( $description ) ) ), $description ) );
			}

			if ( in_array( $this->mode, array( 'standard', 'elements' ), true ) ) {
				WC_Payment_Gateway_CC::payment_fields();
			}

			$this->should_load_scripts() && wp_enqueue_script( 'yith-stripe-js' );
		}

		/**
		 * Return details about a specific field in the Credit Card form
		 *
		 * @param string $field  Field id.
		 * @param string $detail Optional detail to retrieve.
		 * @return array|string|bool All field details if $detail is empty, only the requested detail otherwise.
		 *                           Details of all fields if no $field is specified.
		 *                           False if field does not exist.
		 */
		public function field_details( $field = false, $detail = false ) {
			$fields = array(
				'name-on-card' => array(
					'label'       => __( 'Name on card', 'yith-woocommerce-stripe' ),
					'placeholder' => __( 'Name on card', 'yith-woocommerce-stripe' ),
				),
				'card-number'  => array(
					'label'       => __( 'Card number', 'yith-woocommerce-stripe' ),
					'placeholder' => '•••• •••• •••• ••••',
				),
				'card-expiry'  => array(
					'label'       => __( 'Exp date', 'yith-woocommerce-stripe' ),
					'placeholder' => __( 'MM / YY', 'yith-woocommerce-stripe' ),
				),
				'card-cvc'     => array(
					'label'       => __( 'Security Code', 'yith-woocommerce-stripe' ),
					'placeholder' => __( 'CVC', 'woocommerce' ),
				),
				'card-details' => array(
					'label'       => __( 'Card details', 'yith-woocommerce-stripe' ),
					'placeholder' => '',
				),
			);

			if ( ! $field ) {
				return $fields;
			}

			if ( ! isset( $fields[ $field ] ) ) {
				return false;
			}

			if ( ! empty( $detail ) ) {
				if ( ! isset( $fields[ $field ][ $detail ] ) ) {
					return false;
				}

				return apply_filters( "yith_wcstripe_{$field}_{$detail}", $fields[ $field ][ $detail ] );
			}

			return $fields[ $field ];
		}

		/**
		 * Add checkbox to choose if save credit card or not
		 *
		 * @param array  $fields Option array.
		 * @param string $id     Gateway id.
		 * @return array
		 * @since 1.0.0
		 */
		public function add_credit_card_form_fields( $fields, $id ) {
			if ( ! $this->is_available() || $id !== $this->id ) {
				return $fields;
			}

			$form_row_first    = ! wp_is_mobile() ? 'form-row-first' : '';
			$form_row_last     = ! wp_is_mobile() ? 'form-row-last' : '';
			$show_name_on_card = $this->show_name_on_card_field();

			$fields = array( 'fields-container' => '<div class="' . esc_attr( $this->id ) . '-form-container ' . $this->mode . ( $show_name_on_card ? ' has-name-on-card ' : '' ) . '">' );

			if ( 'standard' === $this->mode ) {
				$fields = array_merge(
					$fields,
					/**
					 * APPLY_FILTERS: yith_wcstripe_name_on_card_label
					 *
					 * Filters Stripe name on card text.
					 *
					 * @param string Default value: 'Name on Card'.
					 *
					 * @return string
					 */
					$show_name_on_card ? array(
						'card-name-field' => '<p class="form-row ' . $form_row_first . ' ">
                            <label for="' . esc_attr( $this->id ) . '-card-name">' . esc_html( $this->field_details( 'name-on-card', 'label' ) ) . ' <span class="required">*</span></label>
                            <input id="' . esc_attr( $this->id ) . '-card-name" class="input-text wc-credit-card-form-card-name" type="text" autocomplete="off" placeholder="' . esc_attr( $this->field_details( 'name-on-card', 'placeholder' ) ) . '" ' . $this->field_name( 'name-on-card' ) . ' />
                        </p>',
					) : array(),
					/**
					 * APPLY_FILTERS: yith_wcstripe_card_number_label
					 *
					 * Filters Stripe card number text.
					 *
					 * @param string Default value: 'Card Number'.
					 *
					 * @return string
					 */
					array(
						'card-number-field' => '<p class="form-row ' . ( $show_name_on_card ? $form_row_last : $form_row_first ) . '">
                            <label for="' . esc_attr( $this->id ) . '-card-number">' . esc_html( $this->field_details( 'card-number', 'label' ) ) . ' <span class="required">*</span></label>
                            <input id="' . esc_attr( $this->id ) . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="' . esc_attr( $this->field_details( 'card-number', 'placeholder' ) ) . '" ' . $this->field_name( 'card-number' ) . ' />
                        </p>',
						/**
						 * APPLY_FILTERS: yith_wcstripe_card_expiry_label
						 *
						 * Filters Stripe card expiry text.
						 *
						 * @param string Default value: 'Expiration Date (MM/YY)'.
						 *
						 * @return string
						 */
						'card-expiry-field' => '<p class="form-row ' . ( $show_name_on_card ? $form_row_first : $form_row_last ) . '">
                            <label for="' . esc_attr( $this->id ) . '-card-expiry">' . esc_html( $this->field_details( 'card-expiry', 'label' ) ) . ' <span class="required">*</span></label>
                            <input id="' . esc_attr( $this->id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" maxlength="7" autocomplete="off" placeholder="' . esc_attr( $this->field_details( 'card-expiry', 'placeholder' ) ) . '" ' . $this->field_name( 'card-expiry' ) . ' />
                        </p>',
					)
				);

			} elseif ( 'elements' === $this->mode ) {
				$fields = array_merge(
					$fields,
					/**
					 * APPLY_FILTERS: yith_wcstripe_name_on_card_label
					 *
					 * Filters Stripe name on card text.
					 *
					 * @param string Default value: 'Name on Card'.
					 *
					 * @return string
					 */
					$show_name_on_card ? array(
						'card-name-field' => '<p class="form-row form-row-full">
                            <label for="' . esc_attr( $this->id ) . '-card-name">' . esc_html( $this->field_details( 'name-on-card', 'label' ) ) . ' <span class="required">*</span></label>
                            <input id="' . esc_attr( $this->id ) . '-card-name" class="input-text wc-credit-card-form-card-name" type="text" autocomplete="off" placeholder="' . esc_attr( $this->field_details( 'name-on-card', 'label' ) ) . '" ' . $this->field_name( 'name-on-card' ) . ' />
                        </p>',
					) : array(),
					/**
					 * APPLY_FILTERS: yith_wcstripe_card_details_label
					 *
					 * Filters Stripe card details text.
					 *
					 * @param string Default value: 'Card Details'.
					 *
					 * @return string
					 */
					array(
						'card-elements' => '<div class="form-row form-row-full">
                            <label for="' . esc_attr( $this->id ) . '-card-elements">' . esc_html( $this->field_details( 'card-details', 'label' ) ) . ' <span class="required">*</span></label>
                            <div id="' . esc_attr( $this->id ) . '-card-elements"></div>
                        </div>',
					)
				);
			}

			// add cvc popup suggestion.
			if ( 'standard' === $this->mode && ! $this->supports( 'credit_card_form_cvc_on_saved_method' ) ) {
				/**
				 * APPLY_FILTERS: yith_wcstripe_card_cvc_label
				 *
				 * Filters Stripe card cvc text.
				 *
				 * @param string Default value: 'Security Code'.
				 *
				 * @return string
				 */
				$fields['card-cvc-field'] = '<p class="form-row ' . ( $show_name_on_card ? $form_row_last : $form_row_first ) . '">
					<label for="' . esc_attr( $this->id ) . '-card-cvc">' . esc_html( apply_filters( 'yith_wcstripe_card_cvc_label', __( 'Security Code', 'yith-woocommerce-stripe' ) ) ) . ' <span class="required">*</span> <a href="#cvv-suggestion" class="cvv2-help" rel="prettyPhoto">' .
					/**
					 * APPLY_FILTERS: yith_wcstripe_what_is_my_cvv_label
					 *
					 * Filters Stripe what is my cvv code text.
					 *
					 * @param string Default value: 'What is my CVV code?'.
					 *
					 * @return string
					 */
					esc_html( apply_filters( 'yith_wcstripe_what_is_my_cvv_label', __( 'What is my CVV code?', 'yith-woocommerce-stripe' ) ) ) . '</a></label>
					<input id="' . esc_attr( $this->id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . ' />
				</p>
				<div id="cvv-suggestion">
					<p style="font-size: 13px;">
						<strong>' . esc_html__( 'Visa&reg;, Mastercard&reg;, and Discover&reg; cardholders:', 'yith-woocommerce-stripe' ) . '</strong><br>
						<a href="//www.cvvnumber.com/" target="_blank"><img height="192" src="//www.cvvnumber.com/csc_1.gif" width="351" align="left" border="0" alt="cvv" style="width: 220px; height:auto;"></a>
						' . esc_html__( 'Turn your card over and look at the signature box. You should see either the entire 16-digit credit card number or just the last four digits followed by a special 3-digit code. This 3-digit code is your CVV number / Card Security Code.', 'yith-woocommerce-stripe' ) . '
					</p>
					<p>&nbsp;</p>
					<p style="font-size: 13px;">
						<strong>' . esc_html__( 'American Express&reg; cardholders:', 'yith-woocommerce-stripe' ) . '</strong><br>
						<a href="//www.cvvnumber.com/" target="_blank"><img height="140" src="//www.cvvnumber.com/csc_2.gif" width="200" align="left" border="0" alt="cid" style="width: 220px; height:auto;"></a>
						' . esc_html__( 'Look for the 4-digit code printed on the front of your card just above and to the right of your main credit card number. This 4-digit code is your Card Identification Number (CID). The CID is the four-digit code printed just above the Account Number.', 'yith-woocommerce-stripe' ) . '
					</p>
				</div>';
			}

			// add checkout fields for credit cart.
			if ( in_array( $this->mode, array( 'standard', 'elements' ), true ) && $this->add_billing_fields ) {
				$fields_to_check = array(
					'billing_country',
					'billing_city',
					'billing_address_1',
					'billing_address_2',
					'billing_state',
					'billing_postcode',
				);
				$original_fields = WC()->countries->get_default_address_fields();

				$shown_fields = is_checkout() ? WC()->checkout()->checkout_fields['billing'] : array();

				$fields['separator'] = '<hr style="clear: both;" />';

				foreach ( $fields_to_check as $i => $field_name ) {
					if ( isset( $shown_fields[ $field_name ] ) ) {
						unset( $fields_to_check[ $i ] );
						continue;
					}

					$field_index = str_replace( array( 'billing_' ), array( '' ), $field_name );

					try {
						$customer = is_user_logged_in() ? new WC_Customer( get_current_user_id() ) : false;
					} catch ( Exception $e ) {
						$customer = false;
					}

					if ( is_checkout() ) {
						$value = WC()->checkout()->get_value( $field_name );
					} elseif ( $customer ) {
						$method_name = 'get_' . $field_name;
						$value       = method_exists( $customer, $method_name ) ? $customer->{$method_name}() : '';
					} else {
						$value = '';
					}

					if ( isset( $original_fields[ $field_index ] ) ) {
						$fields[ $field_name ] = woocommerce_form_field( $field_name, array_merge( array( 'return' => true ), $original_fields[ $field_index ] ), $value );
					}
				}

				if ( empty( $fields_to_check ) ) {
					unset( $fields['separator'] );
				}
			}

			$fields = array_merge(
				$fields,
				array(
					'fields-container-end' => '</div>',
				)
			);

			return $fields;
		}

		/**
		 * Outputs a checkbox for saving a new payment method to the database.
		 */
		public function save_payment_method_checkbox() {
			if ( 'prompt' === $this->save_cards_mode ) {
				parent::save_payment_method_checkbox();
			} else {
				return;
			}
		}

		/* === BLACKLIST METHODS === */

		/**
		 * Method to check blacklist (only for premium)
		 *
		 * @param bool|int     $user_id User id.
		 * @param bool |string $ip      Ip to check.
		 *
		 * @return bool
		 * @since 1.1.3
		 */
		public function is_blocked( $user_id = false, $ip = false ) {
			if ( 'no' === $this->get_option( 'enable_blacklist', 'no' ) ) {
				return false;
			}

			global $wpdb;

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( ! $ip ) {
				$ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			}

			if ( ! $ip ) {
				return false;
			}

			$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->yith_wc_stripe_blacklist} WHERE ( user_id = %d OR ip = %s ) AND unbanned = 0", $user_id, $ip ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			return $res > 0 ? true : false;
		}

		/**
		 * Check if the user is unbanned by admin
		 *
		 * @param bool|int    $user_id User id.
		 * @param bool|string $ip      Ip to check.
		 *
		 * @return bool
		 */
		public function is_unbanned( $user_id = false, $ip = false ) {
			if ( 'no' === $this->get_option( 'enable_blacklist', 'no' ) ) {
				return false;
			}

			global $wpdb;

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( ! $ip ) {
				$ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			}

			if ( ! $ip ) {
				return false;
			}

			$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->yith_wc_stripe_blacklist} WHERE ( user_id = %d OR ip = %s ) AND unbanned = %d", $user_id, $ip, 1 ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			return $res > 0 ? true : false;
		}

		/**
		 * Register the block on blacklist
		 *
		 * @param array $args Arguments describing the row to add.
		 *
		 * @return bool
		 * @since 1.1.3
		 */
		public function add_block( $args = array() ) {
			$args = wp_parse_args(
				$args,
				array(
					'user_id'  => get_current_user_id(),
					'ip'       => ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
					'order_id' => 0,
					'ua'       => ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				)
			);

			list( $user_id, $ip, $order_id, $ua ) = yith_plugin_fw_extract( $args, 'user_id', 'ip', 'order_id', 'ua' );

			if ( 'no' === $this->get_option( 'enable_blacklist', 'no' ) || $this->have_purchased( $user_id ) || $this->is_blocked( $user_id, $ip ) || $this->is_unbanned( $user_id, $ip ) ) {
				return false;
			}

			global $wpdb;

			// add the user and the ip.
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->yith_wc_stripe_blacklist,
				array(
					'user_id'      => $user_id,
					'ip'           => $ip,
					'order_id'     => $order_id,
					'ua'           => $ua,
					'ban_date'     => current_time( 'mysql' ),
					'ban_date_gmt' => current_time( 'mysql', 1 ),
				)
			);

			return true;
		}

		/* === PAYMENT INTENT MANAGEMENT === */

		/**
		 * Retrieve intent for current operation; if none, creates one
		 *
		 * @param \WC_Order|bool $order Current order.
		 *
		 * @throws Exception When an error occurs while retrieving session intent.
		 * @return \Stripe\PaymentIntent|bool Payment intent or false on failure
		 */
		public function get_intent( $order = false ) {
			$intent_id = false;

			// check order first.
			if ( $order ) {
				$intent_id = $order->get_meta( 'intent_id', true );
			}

			// then $_POST.
			// nonce verification is not required, as we're running during checkout handling, and nonce was verified already by WC.
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( ! $intent_id && isset( $_POST['stripe_intent'] ) ) {
				$intent_id = sanitize_text_field( wp_unslash( $_POST['stripe_intent'] ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			// and finally session.
			if ( ! $intent_id ) {
				$intent    = $this->get_session_intent( $order ? $order->get_id() : false );
				$intent_id = $intent ? $intent->id : false;
			}

			if ( ! $intent_id ) {
				return false;
			}

			// retrieve intent from id.
			if ( ! isset( $intent ) ) {
				$intent = $this->api->get_correct_intent( $intent_id );
			}

			if ( ! $intent ) {
				return false;
			}

			return $intent;
		}

		/**
		 * Get intent for current session
		 *
		 * @param int $order_id Order id, if any specified.
		 *
		 * @throws Exception When an error occurs while handling intents.
		 * @return \Stripe\PaymentIntent|bool Session payment intent or false on failure
		 */
		public function get_session_intent( $order_id = false ) {
			global $wp;

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$session = WC()->session;

			if ( ! $session ) {
				return false;
			}

			$intent_id       = $session->get( 'yith_wcstripe_intent' );
			$locked_statuses = array( 'requires_payment_method', 'requires_confirmation', 'requires_action' );

			if ( ! $order_id && is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( $order_id ) {
				$order    = wc_get_order( $order_id );
				$currency = strtolower( $order->get_currency() );
				$total    = YITH_WCStripe::get_amount( $order->get_total(), $currency, $order );

				/**
				 * APPLY_FILTERS: yith_wcstripe_charge_description
				 *
				 * Filters charge payment description.
				 *
				 * @param string Default description.
				 * @param string Site title.
				 * @param int    Order number.
				 *
				 * @return string
				 */
				// translators: 1. Blog name. 2. Order number.
				$description = apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );

				$metadata = array(
					'cart_hash'   => '',
					'order_id'    => $order_id,
					'order_email' => $order->get_billing_email(),
				);
			} else {
				$cart = WC()->cart;
				$cart && $cart->calculate_totals();
				$total    = $cart ? YITH_WCStripe::get_amount( $cart->total ) : 0.00;
				$currency = strtolower( get_woocommerce_currency() );

				// translators: 1. Cart hash ( unique identifier of current cart ).
				$description = $cart ? sprintf( __( 'Payment intent for cart %s.', 'yith-woocommerce-stripe' ), yith_wcstripe_get_cart_hash() ) : '';

				$metadata = array(
					'cart_hash'   => $cart ? yith_wcstripe_get_cart_hash() : '',
					'order_id'    => '',
					'order_email' => '',
				);
			}

			$is_checkout = is_checkout() || ( defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ) || ( defined( 'YITH_WCSTRIPE_DOING_CHECKOUT' ) && YITH_WCSTRIPE_DOING_CHECKOUT );

			if ( ! $total || ! $is_checkout ) {
				return $this->get_session_setup_intent( $order_id );
			}

			// if total don't match requirements, skip intent creation.
			if ( ! $total || $total > 99999999 ) {
				$this->delete_session_intent();

				return false;
			}

			if ( $intent_id ) {
				$intent = $this->api->get_intent( $intent_id );

				if ( $intent ) {

					// if intent isn't longer available, generate a new one.
					if ( ! in_array( $intent->status, $locked_statuses, true ) && ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
						$this->delete_session_intent( $intent );

						return $this->create_session_intent( array( 'order_id' => $order_id ) );
					} elseif ( 'succeeded' === $intent->status || intval( $order_id ) !== intval( $intent->metadata->order_id ) ) {
						return $this->create_session_intent( array( 'order_id' => $order_id ) );
					}

					if ( (float) $intent->amount !== $total || $intent->currency !== $currency || (int) $intent->metadata->order_id !== $order_id ) {
						if ( ! in_array( $intent->status, $locked_statuses, true ) ) {
							$intent = $this->api->update_intent(
								$intent->id,
								array(
									'amount'      => $total,
									'currency'    => $currency,
									'description' => $description,
									/**
									 * APPLY_FILTERS: yith_wcstripe_metadata
									 *
									 * Filters Stripe charge metadata.
									 *
									 * @param array  Default metadata.
									 * @param string Action type.
									 *
									 * @return array
									 */
									'metadata'    => apply_filters(
										'yith_wcstripe_metadata',
										array_merge(
											array( 'instance' => $this->instance ),
											$metadata
										),
										'create_payment_intent'
									),
								)
							);
						} else {
							$this->delete_session_intent( $intent );

							return $this->create_session_intent( array( 'order_id' => $order_id ) );
						}
					}

					return $intent;
				}
			}

			return $this->create_session_intent( array( 'order_id' => $order_id ) );
		}

		/**
		 * Get setup intent for current session
		 *
		 * @param int|bool $order_id Optional order id.
		 *
		 * @throws Exception When an error occurs while deleting setup intent.
		 * @return \Stripe\SetupIntent|bool Session setup intent or false on failure
		 */
		public function get_session_setup_intent( $order_id = false ) {
			$session   = WC()->session;
			$intent_id = $session->get( 'yith_wcstripe_setup_intent' );

			if ( $intent_id ) {
				$intent = $this->api->get_setup_intent( $intent_id );

				if ( $intent ) {
					// if intent isn't longer available, generate a new one.
					if ( ! in_array( $intent->status, array( 'requires_payment_method', 'requires_confirmation', 'requires_action' ), true ) ) {
						$this->delete_session_setup_intent( $intent );

						return $this->create_session_setup_intent( array( 'order_id' => $order_id ) );
					}

					return $intent;
				}
			}

			return $this->create_session_setup_intent( array( 'order_id' => $order_id ) );
		}

		/**
		 * Create a new intent for current session
		 *
		 * @param array $args array of argument to use for intent creation. Following a list of accepted params<br/>
		 *              [
		 *              'amount' // total to pay
		 *              'currency' // order currency
		 *              'description' // transaction description; will be modified after confirm
		 *              'metadata' // metadata for the transaction; will be modified after confirm
		 *              'setup_future_usage' // default to 'off_session', to reuse in renews when needed
		 *              'customer' // stripe customer id for current user, if any
		 *              ].
		 *
		 * @return \Stripe\PaymentIntent|bool Generate payment intent, or false on failure
		 */
		public function create_session_intent( $args = array() ) {
			global $wp;

			$customer_id = false;
			$order_id    = false;

			if ( is_user_logged_in() ) {
				$customer_id = $this->get_customer_id( get_current_user_id() );
			}

			if ( isset( $args['order_id'] ) ) {
				$order_id = $args['order_id'];
				unset( $args['order_id'] );
			} elseif ( is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( $order_id ) {
				$order    = wc_get_order( $order_id );
				$currency = $order->get_currency();
				$total    = YITH_WCStripe::get_amount( $order->get_total(), $currency, $order );
				/**
				 * APPLY_FILTERS: yith_wcstripe_charge_description
				 *
				 * Filters charge payment description.
				 *
				 * @param string Default description.
				 * @param string Site title.
				 * @param int    Order number.
				 *
				 * @return string
				 */
				// translators: 1. Blog name. 2. Order number.
				$description = apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );
				$metadata    = array(
					'order_id'    => $order_id,
					'order_email' => $order->get_billing_email(),
					'cart_hash'   => '',
				);
			} else {
				$cart     = WC()->cart;
				$total    = $cart ? YITH_WCStripe::get_amount( $cart->total ) : 0;
				$currency = strtolower( get_woocommerce_currency() );
				// translators: 1. Cart hash.
				$description = $cart ? sprintf( __( 'Payment intent for cart %s.', 'yith-woocommerce-stripe' ), yith_wcstripe_get_cart_hash() ) : '';
				$metadata    = array(
					'cart_hash'   => $cart ? yith_wcstripe_get_cart_hash() : '',
					'order_id'    => '',
					'order_email' => '',
				);
			}

			// Guest user.
			if ( ! $customer_id && $order_id ) {
				$order    = wc_get_order( $order_id );
				$customer = $this->get_customer( $order );
				if ( $customer ) {
					$customer_id = $customer->id;
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_create_payment_intent
			 *
			 * Filters Stripe session setup intent default args.
			 *
			 * @param array Default arguments containing metadata, usage and customer (if exists).
			 *
			 * @return array
			 */
			$defaults = apply_filters(
				'yith_wcstripe_create_payment_intent',
				array_merge(
					array(
						'amount'              => $total,
						'currency'            => $currency,
						'description'         => $description,
						/**
						 * APPLY_FILTERS: yith_wcstripe_metadata
						 *
						 * Filters Stripe charge metadata.
						 *
						 * @param array  Default metadata.
						 * @param string Action type.
						 *
						 * @return array
						 */
						'metadata'            => apply_filters(
							'yith_wcstripe_metadata',
							array_merge(
								array(
									'instance' => $this->instance,
								),
								$metadata
							),
							'create_payment_intent'
						),
						'setup_future_usage'  => 'off_session',
						'capture_method'      => $this->capture ? 'automatic' : 'manual',
						'confirmation_method' => 'manual',
					),
					$customer_id ? array(
						'customer' => $customer_id,
					) : array()
				)
			);

			$args = wp_parse_args( $args, $defaults );

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$session = WC()->session;

			try {
				$intent = $this->api->create_intent( $args );
			} catch ( Exception $e ) {
				return false;
			}

			if ( ! $intent ) {
				return false;
			}

			if ( $session ) {
				$session->set( 'yith_wcstripe_intent', $intent->id );
			}

			return $intent;
		}

		/**
		 * Create a new setup intent for current session
		 *
		 * @param array $args Array of argument to use for intent creation. Following a list of accepted params
		 *              [
		 *              'metadata' // metadata for the transaction; will be modified after confirm
		 *              'usage' // default to 'off_session', to reuse in renews when needed
		 *              'customer' // stripe customer id for current user, if any
		 *              ].
		 *
		 * @return \Stripe\PaymentIntent|bool Generate payment intent, or false on failure
		 */
		public function create_session_setup_intent( $args = array() ) {
			$customer_id = false;
			$order_id    = false;

			if ( is_user_logged_in() ) {
				$customer_id = $this->get_customer_id( get_current_user_id() );
			}

			if ( isset( $args['order_id'] ) ) {
				$order_id = $args['order_id'];
				unset( $args['order_id'] );
			} elseif ( is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( $order_id ) {
				$order    = wc_get_order( $order_id );
				$metadata = array(
					'order_id'    => $order_id,
					'order_email' => $order->get_billing_email(),
					'cart_hash'   => '',
				);
			} else {
				$metadata = array(
					'instance' => $this->instance,
				);
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_create_payment_intent
			 *
			 * Filters Stripe session setup intent default args.
			 *
			 * @param array Default arguments containing metadata, usage and customer (if exists).
			 *
			 * @return array
			 */
			$defaults = apply_filters(
				'yith_wcstripe_create_payment_intent',
				array_merge(
					array(
						/**
						 * APPLY_FILTERS: yith_wcstripe_metadata
						 *
						 * Filters Stripe charge metadata.
						 *
						 * @param array  Default metadata.
						 * @param string Action type.
						 *
						 * @return array
						 */
						'metadata' => apply_filters(
							'yith_wcstripe_metadata',
							$metadata,
							'create_setup_intent'
						),
						'usage'    => 'off_session',
					),
					$customer_id ? array(
						'customer' => $customer_id,
					) : array()
				)
			);

			$args = wp_parse_args( $args, $defaults );

			// add return url when confirmation is required immediately.
			if ( ! empty( $args['confirm'] ) ) {
				$args['return_url'] = $this->get_verification_url( $order ?? null );
			}

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$session = WC()->session;

			$intent = $this->api->create_setup_intent( $args );

			if ( ! $intent ) {
				return false;
			}

			$session->set( 'yith_wcstripe_setup_intent', $intent->id );

			return $intent;
		}

		/**
		 * Update session intent, registering new cart total and currency, and configuring a payment method if needed
		 *
		 * @param int|bool $token Selected token id, or null if new payment method is used.
		 * @param int|bool $order Current order id, or null if cart should be used.
		 *
		 * @return PaymentIntent|SetupIntent|bool Updated intent, or false on failure
		 * @throws Exception When API request fails.
		 */
		public function update_session_intent( $token = false, $order = false ) {
			// retrieve intent; this will automatically update total and currency.
			$intent = $this->get_session_intent( $order );

			if ( ! $intent ) {
				throw new Exception( esc_html__( 'There was an error processing payment; please, try again later.', 'yith-woocommerce-stripe' ) );
			}

			if ( ! $token ) {
				return $intent;
			}

			// prepare payment method to use for update.
			if ( is_int( $token ) ) {
				if ( ! is_user_logged_in() ) {
					throw new Exception( esc_html__( 'You must login before using a registered card.', 'yith-woocommerce-stripe' ) );
				}

				$token = WC_Payment_Tokens::get( $token );

				if ( ! $token || $token->get_user_id() !== get_current_user_id() ) {
					throw new Exception( esc_html__( 'The card you\'re trying to use isn\'t valid; please, try again with another payment method.', 'yith-woocommerce-stripe' ) );
				}

				$payment_method = $token->get_token();
			} elseif ( is_string( $token ) ) {
				$payment_method = $token;
			}

			// if a payment method was provided, try to bind it to payment intent.
			if ( $payment_method ) {
				$result = $this->api->update_correct_intent(
					$intent->id,
					array( 'payment_method' => $payment_method )
				);

				// check if update was successful.
				if ( ! $result ) {
					throw new Exception( esc_html__( 'The card you\'re trying to use isn\'t valid; please, try again with another payment method.', 'yith-woocommerce-stripe' ) );
				}

				// update intent object that will be returned.
				$intent = $result;
			}

			return $intent;
		}

		/**
		 * Removes intent from current session
		 * Method is intended to cancel session, but will also cancel PaymentIntent on Stripe, if object is passed as param
		 *
		 * @param \Stripe\PaymentIntent|bool $intent Payment intent to cancel, or false if it is not required.
		 *
		 * @throws Exception When an error occurs while cancelling intent.
		 * @return void
		 */
		public function delete_session_intent( $intent = false ) {
			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$session = WC()->session;
			$session->set( 'yith_wcstripe_intent', '' );

			if ( $intent && isset( $intent->status ) && ! in_array( $intent->status, array( 'succeeded', 'cancelled' ), true ) ) {
				$intent->cancel();
			}
		}

		/**
		 * Removes intent from current session
		 * Method is intended to cancel session, but will also cancel SetupIntent on Stripe, if object is passed as param
		 *
		 * @param \Stripe\setupIntent|bool $intent Setup intent to cancel, or false if it is not required.
		 *
		 * @throws Exception When an error occurs while cancelling intent.
		 * @return void
		 */
		public function delete_session_setup_intent( $intent = false ) {
			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$session = WC()->session;
			$session->set( 'yith_wcstripe_setup_intent', '' );

			if ( $intent && isset( $intent->status ) && ! in_array( $intent->status, array( 'succeeded', 'cancelled' ), true ) ) {
				$intent->cancel();
			}
		}

		/* === CHECKOUT SESSION METHODS */

		/**
		 * Create checkout session
		 *
		 * @param array $args Params used to create CheckoutSession object.
		 *
		 * @return \Stripe\StripeObject|bool Checkout session or false on failure
		 */
		public function create_checkout_session( $args = array() ) {
			$order_id = false;

			if ( isset( $args['order_id'] ) ) {
				$order_id = $args['order_id'];
			} elseif ( is_checkout_pay_page() ) {
				$order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : false;
			}

			if ( ! $order_id ) {
				return false;
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return false;
			}

			$args = array_merge(
				$args,
				array(
					'billing_address_collection'  => $this->hosted_billing ? 'required' : 'auto',
					'shipping_address_collection' => $this->hosted_shipping && $order->needs_shipping_address() ? array(
						'allowed_countries' => yith_wcstripe_get_shipping_counties(),
					) : null,
					'payment_intent_data'         => array(
						'capture_method' => $this->capture ? 'automatic' : 'manual',
					),
				)
			);

			return parent::create_checkout_session( $args );
		}

		/* === TOKENS MANAGEMENT === */

		/**
		 * Add payment method
		 *
		 * @throws Exception When an error occurs while adding the payment method.
		 * @return array|bool
		 */
		public function add_payment_method() {
			try {
				// Initializate SDK and set private key.
				$this->init_stripe_sdk();

				$intent = $this->get_intent();

				// if no intent was found, crate one on the fly.
				if ( ! $intent || 0 === strpos( $intent->id, 'pi' ) ) {
					$intent = $this->create_session_setup_intent();
				}

				if ( ! $intent ) {
					throw new Exception( __( 'Sorry, There was an error while registering payment method; please, try again', 'yith-woocommerce-stripe' ) );
				} elseif ( 'requires_action' === $intent->status ) {
					/**
					 * DO_ACTION: yith_wcstripe_setup_intent_requires_action
					 *
					 * Triggered when there is a 'requires_action' error when adding a payment method.
					 *
					 * @param \Stripe\PaymentIntent|bool $intent Payment intent or false on failure
					 * @param int                                User id.
					 */
					do_action( 'yith_wcstripe_setup_intent_requires_action', $intent, get_current_user_id() );

					throw new Exception( __( 'Please, validate your payment method before proceeding further; in order to do this, refresh the page and proceed with checkout as usual', 'yith-woocommerce-stripe' ) );
				} elseif ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
					throw new Exception( __( 'Sorry, There was an error while registering payment method; please, try again', 'yith-woocommerce-stripe' ) );
				}

				$token = $this->save_token( $intent->payment_method );

				/**
				 * APPLY_FILTERS: yith_wcstripe_add_payment_method_result
				 *
				 * Filters Stripe result and redirect after successful payment method added.
				 *
				 * @param array                   Array containing result (default value 'success') and redirect (default value is payment methods endpoint).
				 * @param WC_Payment_Token $token Registered token.
				 *
				 * @return array
				 */
				return apply_filters(
					'yith_wcstripe_add_payment_method_result',
					array(
						'result'   => 'success',
						'redirect' => wc_get_endpoint_url( 'payment-methods' ),
					),
					$token
				);

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$this->error_handling( $e );

				return false;
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );

				return false;
			}
		}

		/**
		 * Save the token on db
		 *
		 * @param string $payment_method_id Payment method to save.
		 *
		 * @throws Exception When an error occurs while creating/updating customer.
		 * @return WC_Payment_Token|bool Registered token or false on failure
		 */
		public function save_token( $payment_method_id ) {

			// nonce verification is not required, as it was already verified already by WC.
			if ( ! is_user_logged_in() || ! $this->save_cards || ( is_checkout() && 'prompt' === $this->save_cards_mode && ! isset( $_POST['wc-yith-stripe-new-payment-method'] ) ) ) { // phpcs:disable WordPress.Security.NonceVerification.Missing
				return false;
			}

			// Initializate SDK and set private key.
			$this->init_stripe_sdk();

			$user           = wp_get_current_user();
			$user_name      = $user->billing_first_name . ' ' . $user->billing_last_name;
			$local_customer = YITH_WCStripe()->get_customer()->get_usermeta_info( $user->ID );
			$customer       = ! empty( $local_customer['id'] ) ? $this->api->get_customer( $local_customer['id'] ) : false;
			$payment_method = $this->api->get_payment_method( $payment_method_id );

			if ( $customer && $payment_method->customer !== $customer->id ) {
				$this->attach_payment_method( $customer, $payment_method_id );

				if ( isset( $customer->sources ) ) {
					$customer->sources->data[] = $payment_method->card;
				}
			} elseif ( ! $customer ) {
				$params = array(
					'name'           => $user_name,
					'payment_method' => $payment_method_id,
					'email'          => $user->billing_email,
					'description'    => apply_filters( 'yith_wcstripe_customer_description', substr( $user->user_login . ' (#' . $user->ID . ' - ' . $user->user_email . ') ' . $user_name, 0, 350 ), $user ),
					/**
					 * APPLY_FILTERS: yith_wcstripe_metadata
					 *
					 * Filters Stripe charge metadata.
					 *
					 * @param array  Default metadata.
					 * @param string Action type.
					 *
					 * @return array
					 */
					'metadata'       => apply_filters(
						'yith_wcstripe_metadata',
						array(
							'user_id'  => $user->ID,
							'instance' => $this->instance,
						),
						'create_customer'
					),
				);

				$customer = $this->api->create_customer( $params );
			}

			$already_registered        = false;
			$registered_token          = false;
			$already_registered_tokens = WC_Payment_Tokens::get_customer_tokens( $user->ID, $this->id );

			if ( ! empty( $already_registered_tokens ) ) {
				foreach ( $already_registered_tokens as $registered_token ) {
					/**
					 * Each of customers payment tokens
					 *
					 * @var $registered_token \WC_Payment_Token
					 */
					$registered_fingerprint = $registered_token->get_meta( 'fingerprint', true );

					if ( $registered_fingerprint === $payment_method->card->fingerprint || $registered_token->get_token() === $payment_method_id ) {
						$already_registered = true;
						break;
					}
				}
			}

			if ( ! $already_registered ) {
				// save card.
				$token = new WC_Payment_Token_CC();
				$token->set_token( $payment_method_id );
				$token->set_gateway_id( $this->id );
				$token->set_user_id( $user->ID );

				$token->set_card_type( strtolower( $payment_method->card->brand ) );
				$token->set_last4( $payment_method->card->last4 );
				$token->set_expiry_month( ( 1 === strlen( $payment_method->card->exp_month ) ? '0' . $payment_method->card->exp_month : $payment_method->card->exp_month ) );
				$token->set_expiry_year( $payment_method->card->exp_year );
				$token->set_default( true );
				$token->add_meta_data( 'fingerprint', $payment_method->card->fingerprint );
				$token->add_meta_data( 'confirmed', true );

				if ( ! $token->save() ) {
					throw new Exception( esc_html__( 'Credit card info not valid.', 'yith-woocommerce-stripe' ) );
				}

				// backward compatibility.
				if ( $customer ) {
					YITH_WCStripe()->get_customer()->update_usermeta_info(
						$customer->metadata->user_id,
						array(
							'id'             => $customer->id,
							'cards'          => isset( $customer->sources ) ? $customer->sources->data : array(),
							'default_source' => $customer->invoice_settings->default_payment_method,
						)
					);
				}

				/**
				 * DO_ACTION: yith_wcstripe_created_card
				 *
				 * Triggered when customer card is created.
				 *
				 * @param string          $payment_method_id Payment method to save.
				 * @param Customer|string $customer          Customer object or ID.
				 */
				do_action( 'yith_wcstripe_created_card', $payment_method_id, $customer );

				return $token;
			} else {
				$registered_token->set_default( true );
				$registered_token->save();

				return $registered_token;
			}
		}

		/**
		 * Attach payment method to customer
		 *
		 * @param string|Stripe\Customer $customer          Customer to update.
		 * @param string                 $payment_method_id Payment method to save.
		 *
		 * @return bool Status of the operation
		 *
		 * @throws Exception When failing to update customer on Stripe.
		 */
		public function attach_payment_method( $customer, $payment_method_id ) {

			try {
				$customer       = $this->api->get_customer( $customer );
				$payment_method = $this->api->get_payment_method( $payment_method_id );

				$payment_method->attach(
					array(
						'customer' => $customer->id,
					)
				);
			} catch ( Exception $e ) {
				return false;
			}

			$this->api->update_customer(
				$customer,
				array(
					'invoice_settings' => array(
						'default_payment_method' => $payment_method_id,
					),
				),
				false
			);

			return true;
		}

		/**
		 * Set one of the currently registered tokens as default
		 *
		 * @param string $payment_method_id Payment Method id.
		 *
		 * @return bool Operation status
		 */
		public function set_default_token( $payment_method_id ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			$user                      = wp_get_current_user();
			$already_registered_tokens = WC_Payment_Tokens::get_customer_tokens( $user->ID, $this->id );

			if ( ! empty( $already_registered_tokens ) ) {
				foreach ( $already_registered_tokens as $registered_token ) {
					/**
					 * Each of customers registered token
					 *
					 * @var $registered_token \WC_Payment_Token
					 */
					if ( $registered_token->get_token() === $payment_method_id ) {
						$registered_token->set_default( true );
						$registered_token->save();

						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Sync tokens on website from stripe $customer object
		 *
		 * @param int|WP_User      $user     User object or user id.
		 * @param \Stripe\Customer $customer Customer object.
		 */
		public function sync_tokens( $user, $customer ) {
			if ( ! is_a( $user, 'WP_User' ) ) {
				$user = get_user_by( 'id', $user );
			}

			if ( ! $this->save_cards || ( 'prompt' === $this->save_cards_mode ) ) {
				return;
			}

			$this->init_stripe_sdk();

			$sources = $this->api->get_payment_methods( $customer->id );
			$tokens  = WC_Payment_Tokens::get_customer_tokens( $user->ID, $this->id );
			$to_add  = $sources;

			/**
			 * Each of customer registered tokens
			 *
			 * @var WC_Payment_Token_CC $token
			 */
			foreach ( $tokens as $token_id => $token ) {
				$found = false;

				foreach ( $sources as $k => $source ) {
					if ( $token->get_token() === $source->id ) {
						$found = true;
						break;
					}
				}

				// edit token if found if between stripe ones and if something is changed.
				if ( $found ) {
					// remove the source from global array, to add the remaining on website.
					unset( $to_add[ $k ] );

					$source = $source->card;

					$changed = false;

					if ( (string) $token->get_last4() !== (string) $source->last4 ) {
						$token->set_last4( $source->last4 );
						$changed = true;
					}

					if ( (string) $token->get_expiry_month() !== ( 1 === strlen( $source->exp_month ) ? '0' . $source->exp_month : (string) $source->exp_month ) ) {
						$token->set_expiry_month( ( 1 === strlen( $source->exp_month ) ? '0' . $source->exp_month : $source->exp_month ) );
						$changed = true;
					}

					if ( (string) $token->get_expiry_year() !== (string) $source->exp_year ) {
						$token->set_expiry_year( $source->exp_year );
						$changed = true;
					}

					if ( $token->get_meta( 'fingerprint' ) !== $source->fingerprint ) {
						$token->update_meta_data( 'fingerprint', $source->fingerprint );
						$changed = true;
					}

					if ( $token->get_token() === $customer->default_source && ! $token->is_default() ) {
						$token->set_default( true );
						$changed = true;
					}

					if ( $token->get_token() !== $customer->default_source && $token->is_default() ) {
						$token->set_default( false );
						$changed = true;
					}

					// save it if changed.
					if ( $changed ) {
						$token->save();
					}
				} else {
					// if not found any token between stripe, remove token.
					$token->delete();
				}
			}

			// add remaining sources not added as token on website yet.
			foreach ( $to_add as $source ) {
				$method_id = $source->id;
				$source    = $source->card;

				$token = new WC_Payment_Token_CC();
				$token->set_token( $method_id );
				$token->set_gateway_id( $this->id );
				$token->set_user_id( $user->ID );

				$token->set_card_type( strtolower( $source->brand ) );
				$token->set_last4( $source->last4 );
				$token->set_expiry_month( ( 1 === strlen( $source->exp_month ) ? '0' . $source->exp_month : $source->exp_month ) );
				$token->set_expiry_year( $source->exp_year );
				$token->add_meta_data( 'fingerprint', $source->fingerprint );

				$token->save();
			}

			// back-compatibility.
			YITH_WCStripe()->get_customer()->update_usermeta_info(
				$customer->metadata->user_id,
				array(
					'id'             => $customer->id,
					'cards'          => isset( $customer->sources ) ? $customer->sources->data : array(),
					'default_source' => $customer->invoice_settings->default_payment_method,
				)
			);
		}

		/* === HELPER METHODS === */

		/**
		 * Returns true when we need to show "Name on Card" field in card addition form
		 *
		 * @return bool
		 */
		public function show_name_on_card_field() {
			return apply_filters( 'yith_wcstripe_show_name_on_card_field', $this->show_name_on_card );
		}

		/**
		 * Get customer ID of Stripe account from user ID
		 *
		 * @param int $user_id User id.
		 *
		 * @return integer
		 * @since 1.0.0
		 */
		public function get_customer_id( $user_id ) {
			$customer = YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );

			if ( ! isset( $customer['id'] ) ) {
				return 0;
			}

			return $customer['id'];
		}

		/**
		 * Get customer of Stripe account or create a new one if not exists
		 *
		 * @param WC_Order $order Order to use to retrieve customer.
		 *
		 * @return \Stripe\Customer|bool
		 * @since 1.0.0
		 */
		public function get_customer( $order ) {
			if ( is_int( $order ) ) {
				$order = wc_get_order( $order );
			}

			$current_order_id = ! empty( $this->current_order ) ? $this->current_order->get_id() : false;
			$order_id         = $order->get_id();

			if ( $current_order_id === $order_id && ! empty( $this->current_customer ) ) {
				return $this->current_customer;
			}

			$user_id         = is_user_logged_in() ? $order->get_user_id() : false;
			$local_customer  = is_user_logged_in() ? YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id ) : false;
			$stripe_customer = false;

			// get existing.
			if ( $local_customer && ! empty( $local_customer['id'] ) ) {
				try {
					$stripe_customer = $this->api->get_customer( $local_customer['id'] );

					if ( $current_order_id === $order_id ) {
						$this->current_customer = $stripe_customer;
					}
				} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// do nothing, and try to create a new customer.
				}
			}

			// create new one.
			if ( ! $stripe_customer ) {
				$user      = is_user_logged_in() ? $order->get_user() : false;
				$user_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				if ( is_user_logged_in() ) {
					$description = '(#' . $order->get_user_id() . ' - ' . $user->user_login . ') ' . $user_name;
				} else {
					$description = '(' . __( 'Guest', 'yith-woocommerce-stripe' ) . ') ' . $user_name;
				}

				$params = array(
					'name'        => $user_name,
					'email'       => $order->get_billing_email(),
					'description' => apply_filters( 'yith_wcstripe_customer_description', substr( $description, 0, 350 ), $user, $order ),
					/**
					 * APPLY_FILTERS: yith_wcstripe_metadata
					 *
					 * Filters Stripe charge metadata.
					 *
					 * @param array  Default metadata.
					 * @param string Action type.
					 *
					 * @return array
					 */
					'metadata'    => apply_filters(
						'yith_wcstripe_metadata',
						array(
							'user_id'  => is_user_logged_in() ? $order->get_user_id() : false,
							'instance' => $this->instance,
						),
						'create_customer'
					),
				);

				try {
					$stripe_customer = $this->api->create_customer( $params );

					// update user meta.
					if ( is_user_logged_in() ) {
						YITH_WCStripe()->get_customer()->update_usermeta_info(
							$user_id,
							array(
								'id'             => $stripe_customer->id,
								'cards'          => isset( $stripe_customer->sources ) ? $stripe_customer->sources->data : array(),
								'default_source' => $stripe_customer->invoice_settings->default_payment_method,
							)
						);
					}

					if ( $current_order_id === $order_id ) {
						$this->current_customer = $stripe_customer;
					}
				} catch ( Exception $e ) {
					return false;
				}
			}

			return $stripe_customer;
		}

		/**
		 * Say if the user in parameter have already purchased properly previously
		 *
		 * @param int|bool $user_id User id; false for current user.
		 *
		 * @return bool
		 * @since 1.1.3
		 */
		public function have_purchased( $user_id = false ) {
			global $wpdb;

			$count = 0;

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT COUNT(id) FROM {$wpdb->prefix}wc_orders WHERE status IN ( %s, %s ) AND customer_id = %d",
						'wc-completed',
						'wc-processing',
						$user_id,
					)
				);
			} else {
				$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status IN ( %s, %s ) AND post_author = %d",
						'wc-completed',
						'wc-processing',
						$user_id,
					)
				);
			}

			return $count > 0 ? true : false;
		}

		/**
		 * Log to txt file
		 *
		 * @param string $message Message to log.
		 *
		 * @since 1.0.0
		 */
		public function log( $message ) {
			if ( isset( $this->log, $this->debug ) && $this->debug ) {
				$this->log->add( 'stripe', $message );
			}
		}

		/**
		 * Standard error handling for exceptions thrown by API class
		 *
		 * @param Stripe\Exception\ApiErrorException $e    Exception to handle.
		 * @param array                              $args Arrat of arguments.
		 *
		 * @return string Final error message
		 */
		public function error_handling( $e, $args = array() ) {
			$message = parent::error_handling( $e, $args );
			$body    = $e->getJsonBody();

			// register error within log file.
			$this->log( 'Stripe Error: ' . $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			// add block if there is an error on card.
			if ( $body && isset( $args['order_id'] ) ) {
				$err = $body['error'];

				if ( isset( $err['type'] ) && 'card_error' === $err['type'] ) {
					$this->add_block( "order_id={$args['order_id']}" );
					WC()->session->refresh_totals = true;
				}
			}

			return $message;
		}

		/**
		 * Give ability to add options to $this->form_fields
		 *
		 * @param array  $field Field to add.
		 * @param string $where Position where to add field(first, last, after, before) (optional, default: last).
		 * @param string $who   Other field in option array (optional, default: empty string).
		 *
		 * @since  2.0.0
		 */
		private function add_form_field( $field, $where = 'last', $who = '' ) {
			switch ( $where ) {

				case 'first':
					$this->form_fields = array_merge( $field, $this->form_fields );
					break;

				case 'last':
					$this->form_fields = array_merge( $this->form_fields, $field );
					break;

				case 'before':
				case 'after':
					if ( array_key_exists( $who, $this->form_fields ) ) {

						$who_position = array_search( $who, array_keys( $this->form_fields ), true );

						if ( 'after' === $where ) {
							++$who_position;
						}

						$before = array_slice( $this->form_fields, 0, $who_position );
						$after  = array_slice( $this->form_fields, $who_position );

						$this->form_fields = array_merge( $before, $field, $after );
					}
					break;
			}
		}
	}
}
