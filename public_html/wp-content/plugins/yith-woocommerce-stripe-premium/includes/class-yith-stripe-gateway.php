<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Gateway class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes\Gateways
 * @version 1.0.0
 */

use Stripe\Error;

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Gateway' ) ) {
	/**
	 * WooCommerce Stripe gateway class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Gateway extends WC_Payment_Gateway_CC {

		/**
		 * API Library
		 *
		 * @var YITH_Stripe_API
		 */
		public $api = null;

		/**
		 * List of standard localized message errors of Stripe SDK
		 *
		 * @var array
		 */
		public $errors = array();

		/**
		 * List of standard localized decline codes of Stripe SDK
		 *
		 * @var array
		 */
		protected $decline_messages = array();

		/**
		 * List of localized suggestions provided to the customer when an error occurs
		 *
		 * @var array
		 */
		protected $further_steps = array();

		/**
		 * The domain of this site used to identifier the website from Stripe
		 *
		 * @var string
		 */
		public $instance = '';

		/**
		 * List cards
		 *
		 * @var array
		 */
		public $cards = array(
			'visa'       => 'Visa',
			'mastercard' => 'MasterCard',
			'discover'   => 'Discover',
			'amex'       => 'American Express',
			'diners'     => 'Diners Club',
			'jcb'        => 'JCB',
		);

		/**
		 * Current gateway mode (standard|elements|checkout|hosted)
		 *
		 * @var string
		 */
		public $mode;

		/**
		 * Current environment (live|test)
		 *
		 * @var string
		 */
		public $env;

		/**
		 * Secret private API key
		 *
		 * @var string $private_key
		 */
		public $private_key;

		/**
		 * Sharable public API key
		 *
		 * @var string $public_key
		 */
		public $public_key;

		/**
		 * Token for current transaction
		 *
		 * @var string $token
		 */
		public $token;

		/**
		 * Image to use to describe CVV field to customers
		 *
		 * @var string $modal_image
		 */
		public $modal_image;

		/**
		 * Name of the session id param
		 *
		 * @var string $session_param
		 */
		public $session_param;

		/**
		 * Current order object
		 *
		 * @var WC_Order
		 */
		protected $current_order = null;

		/**
		 * Variable to store the renew mode
		 *
		 * @var string $renew_mode
		 */
		public $renew_mode;

		/**
		 * Variable to store the logs
		 *
		 * @var string $log
		 */
		public $log;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id         = YITH_WCStripe::$gateway_id;
			$this->has_fields = true;
			/**
			 * APPLY_FILTERS: yith_stripe_method_title
			 *
			 * Filters Stripe gateway method title text.
			 *
			 * @param string Default text 'Stripe'.
			 *
			 * @return string
			 */
			$this->method_title = apply_filters( 'yith_stripe_method_title', __( 'Stripe', 'yith-woocommerce-stripe' ) );
			/**
			 * APPLY_FILTERS: yith_stripe_method_description
			 *
			 * Filters Stripe gateway method description text.
			 *
			 * @param string Default text 'Take payments via Stripe - uses stripe.js to create card tokens and the Stripe SDK. Requires SSL when sandbox is disabled.'.
			 *
			 * @return string
			 */
			$this->method_description = apply_filters( 'yith_stripe_method_description', __( 'Take payments via Stripe - it uses stripe.js to create card tokens and the Stripe SDK. Requires SSL when sandbox is disabled.', 'yith-woocommerce-stripe' ) );
			$this->supports           = array(
				'products',
			);
			$this->instance           = preg_replace( '/http(s)?:\/\//', '', site_url() );

			// Define user set variables.
			/**
			 * APPLY_FILTERS: yith_wcstripe_gateway_enabled
			 *
			 * Filters if Stripe gateway is enabled.
			 *
			 * @param bool Default value taken from gateway settings.
			 *
			 * @return bool
			 */
			$this->enabled     = apply_filters( 'yith_wcstripe_gateway_enabled', $this->enabled );
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			/**
			 * APPLY_FILTERS: yith_wcstripe_environment
			 *
			 * Filters Stripe environment. Possible values: 'test' and 'live'.
			 *
			 * @param string Environment value taken from plugin settings or if site in development mode.
			 *
			 * @return string
			 */
			$this->env                  = apply_filters( 'yith_wcstripe_environment', ( 'yes' === $this->get_option( 'enabled_test_mode' ) || ( defined( 'WP_ENV' ) && 'development' === WP_ENV ) ) ? 'test' : 'live' );
			$this->private_key          = $this->get_option( $this->env . '_secrect_key' );
			$this->public_key           = $this->get_option( $this->env . '_publishable_key' );
			$this->modal_image          = $this->get_option( 'modal_image' );
			$this->mode                 = 'hosted';
			$this->view_transaction_url = 'https://dashboard.stripe.com/' . ( 'test' === $this->env ? 'test/' : '' ) . 'payments/%s';
			/**
			 * APPLY_FILTERS: yith_wcstripe_session_param
			 *
			 * Filters Stripe session parameter name.
			 *
			 * @param string The session parameter name. Default value 'session_id'.
			 *
			 * @return string
			 */
			$this->session_param = apply_filters( 'yith_wcstripe_session_param', 'session_id' );

			// post data.
			$this->token = isset( $_POST['stripe_token'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// save.
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// others.
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_payment_scripts' ) );
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'return_handler' ) );
		}

		/* === GATEWAY METHODS === */

		/**
		 * Checks if this gateway is enabled
		 *
		 * @since 3.14.0
		 */
		public function is_enabled() {
			if ( 'yes' !== $this->enabled ) {
				return false;
			}

			if ( 'standard' === $this->mode && ! is_ssl() && 'test' !== $this->env ) {
				return false;
			}

			if ( ! $this->public_key || ! $this->private_key ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if this gateway is available
		 *
		 * @since 1.0.0
		 */
		public function is_available() {
			if ( ! $this->is_enabled() ) {
				return false;
			}

			if ( defined( 'YITH_DOING_RENEWS' ) && YITH_DOING_RENEWS ) {
				return true;
			}

			if ( WC()->cart && 0 < $this->get_order_total() && 0 < $this->max_amount && $this->max_amount < $this->get_order_total() ) {
				return false;
			}

			if ( $this->is_blocked() ) {
				return false;
			}

			return true;
		}

		/**
		 * Payment form on checkout page
		 *
		 * @since 1.0.0
		 */
		public function payment_fields() {
			$description = $this->get_description();

			if ( $description ) {
				echo wp_kses_post( wpautop( wptexturize( trim( $description ) ) ) );
			}
		}

		/**
		 * Return the gateway's description.
		 *
		 * @return string
		 */
		public function get_description() {
			$description = parent::get_description();

			if ( $description && 'test' === $this->env ) {
				// translators: 1. Link to Stripe testing article.
				$description .= ' ' . sprintf( __( 'TEST MODE ENABLED. Use a test card: %s', 'yith-woocommerce-stripe' ), '<a href="https://stripe.com/docs/testing">https://stripe.com/docs/testing</a>' );
			}

			return $description;
		}

		/**
		 * Returns valid card icons.
		 *
		 * @access public
		 * @return string
		 */
		public function get_icon() {
			switch ( WC()->countries->get_base_country() ) {

				case 'US':
					/**
					 * APPLY_FILTERS: yith_wcstripe_gateway_us_icons
					 *
					 * Filters US gateway card icons.
					 *
					 * @param array Card icons to display if base country is US.
					 *
					 * @return array
					 */
					$allowed = apply_filters(
						'yith_wcstripe_gateway_us_icons',
						array(
							'visa',
							'mastercard',
							'amex',
							'discover',
							'diners',
							'jcb',
						)
					);
					break;

				default:
					/**
					 * APPLY_FILTERS: yith_wcstripe_gateway_default_icons
					 *
					 * Filters default gateway card icons.
					 *
					 * @param array Card icons to display by default.
					 *
					 * @return array
					 */
					$allowed = apply_filters(
						'yith_wcstripe_gateway_default_icons',
						array(
							'visa',
							'mastercard',
							'amex',
						)
					);
					break;
			}

			$icon = '';
			foreach ( $allowed as $name ) {
				/**
				 * APPLY_FILTERS: yith_wcstripe_gateway_icon
				 *
				 * Filters gateway card icon.
				 *
				 * @param string          HTML code to display the icon.
				 * @param string $name    Card slug name.
				 * @param string          Card title.
				 * @param array  $allowed Allowed cards array.
				 *
				 * @return string
				 */
				$icon .= apply_filters( 'yith_wcstripe_gateway_icon', '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/' . $name . '.svg' ) . '" alt="' . $this->cards[ $name ] . '" style="width:40px;" />', $name, $this->cards[ $name ], $allowed );
			}

			/**
			 * APPLY_FILTERS: woocommerce_gateway_icon
			 *
			 * Filters WooCommerce gateway icon.
			 *
			 * @param string $icon The icon.
			 * @param int    $id   Gateway ID.
			 *
			 * @return string
			 */
			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
		}

		/* === PAYMENT METHODS === */

		/**
		 * Handling payment and processing the order.
		 *
		 * @param int $order_id Id of the order to process.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function process_payment( $order_id ) {
			$order               = wc_get_order( $order_id );
			$this->current_order = $order;

			return $this->process_hosted_payment();
		}

		/**
		 * Return handler for Hosted Payments
		 */
		public function return_handler() {
			if ( in_array( $this->mode, array( 'standard', 'elements' ), true ) ) {
				return;
			}

			ob_clean();
			status_header( 200 );

			if ( isset( $_REQUEST[ $this->session_param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$session_id = sanitize_text_field( wp_unslash( $_REQUEST[ $this->session_param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$order      = $this->get_order_by_session_id( $session_id );

				if ( $order ) {
					if ( $order->has_status( array( 'completed', 'processing' ) ) ) {
						wp_safe_redirect( $this->get_return_url( $order ) );
						exit();
					}

					// Initialize SDK and set private key.
					$this->init_stripe_sdk();

					$session = $this->api->get_session( $session_id );

					if ( $session && $session->payment_intent ) {
						$intent = $this->api->get_intent( $session->payment_intent );

						if ( $intent && in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
							// register intent for the order.
							$order->update_meta_data( 'intent_id', $intent->id );

							// update intent data.
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
									// translators: 1. Blog name. 2. Order id.
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
											'order_id'    => $order->get_id(),
											'order_email' => $order->get_billing_email(),
											'instance'    => $this->instance,
										),
										'charge'
									),
								)
							);

							// retrieve charge to use for next steps.
							$charge = $intent->latest_charge;
							$charge = is_object( $charge ) ? $charge : $this->api->get_charge( $charge );

							// Payment complete.
							$order->payment_complete( $charge->id );

							// Add order note.
							// translators: 1. Charge id.
							$order->add_order_note( sprintf( __( 'Stripe payment approved (ID: %s)', 'yith-woocommerce-stripe' ), $charge->id ) );

							// update post meta.
							$order->update_meta_data( '_captured', ( $charge->captured ? 'yes' : 'no' ) );
							$order->update_meta_data( '_stripe_customer_id', $charge->customer ? $charge->customer : false );
							$order->save();

							// Remove cart.
							WC()->cart->empty_cart();

							wp_safe_redirect( $this->get_return_url( $order ) );
							exit();
						}
					}
				}
			}

			wc_add_notice( __( 'There was an error during payment; please, try again later.', 'yith-woocommerce-stripe' ) );

			wp_safe_redirect( wc_get_checkout_url() );
			exit();
		}

		/**
		 * Process standard payments
		 *
		 * @param WC_Order $order Order to pay.
		 *
		 * @return array
		 */
		protected function process_hosted_payment( $order = null ) {
			if ( empty( $order ) ) {
				$order = $this->current_order;
			}

			try {
				$session = $this->create_checkout_session(
					array(
						'order_id' => $order->get_id(),
					)
				);
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

			if ( ! $session ) {
				wc_add_notice( __( 'There was a problem during payment; please, try again later.', 'yith-woocommerce-stripe' ), 'error' );

				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			}

			$order->update_meta_data( 'session_id', $session->id );
			$order->save_meta_data();

			return array(
				'result'   => 'success',
				'redirect' => add_query_arg( $this->session_param, $session->id, $order->get_checkout_payment_url( true ) ),
			);
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
			return $this->is_available() && ( is_checkout() || has_block( 'woocommerce/checkout' ) || apply_filters( 'yith_wcstripe_load_assets', false ) );
		}

		/**
		 * Javascript library
		 *
		 * @since 1.0.0
		 */
		public function register_payment_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// scripts.
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
					'mode'        => 'hosted',
				)
			);
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
			global $wp;

			$order_id = false;

			if ( isset( $args['order_id'] ) ) {
				$order_id = $args['order_id'];
				unset( $args['order_id'] );
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

			$currency       = $order->get_currency();
			$customer_email = $order->get_billing_email();

			$allow_itemized = true;

			// gift card fix.
			if ( $order->get_meta( '_ywgc_applied_gift_cards', true ) ) {
				$allow_itemized = false;
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_checkout_session_detailed_line_items
			 *
			 * Filters checkout session detailed line items.
			 *
			 * @param bool     $allow_itemized False if gift card applied.
			 * @param WC_Order $order_id       The order ID.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcstripe_checkout_session_detailed_line_items', $allow_itemized, $order_id ) ) {
				$items      = $order->get_items( array( 'line_item', 'shipping', 'tax', 'fee' ) );
				$line_items = array();

				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$line_item = array(
							'quantity'   => $item->get_quantity(),
							'price_data' => array(
								'currency' => $currency,
							),
						);

						$total = method_exists( $item, 'get_total' ) ? (float) $item->get_total() : 0.00;

						if ( $item->is_type( 'line_item' ) ) {
							/**
							 * If product is a line item, we can retrieve related product
							 *
							 * @var $product WC_Product Product object for the item.
							 */
							$product  = $item->get_product();
							$image_id = $product->get_image_id();

							if ( ! $total ) {
								continue;
							}

							$product_data = array(
								'name'   => apply_filters( 'yith_wcstripe_product_name', $item->get_name() ),
								'images' => array(
									$image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src(),
								),
							);

							/**
							 * APPLY_FILTERS: yith_wcstripe_line_item_description
							 *
							 * Filters if product has short description.
							 *
							 * @param string|bool     Product short description. False if none.
							 *
							 * @return bool
							 */
							if ( apply_filters( 'yith_wcstripe_line_item_description', $product->get_short_description() ) ) {
								$product_data['description'] = wp_strip_all_tags( $product->get_short_description() );
							}

							$line_item['price_data']['unit_amount']  = YITH_WCStripe::get_amount( $total / $item->get_quantity(), $currency, $order );
							$line_item['price_data']['product_data'] = $product_data;
						} elseif ( $item->is_type( array( 'fee' ) ) ) {
							if ( ! $total ) {
								continue;
							}

							$line_item['price_data']['unit_amount']  = YITH_WCStripe::get_amount( $total, $currency, $order );
							$line_item['price_data']['product_data'] = array(
								// translators: 1. Fee name.
								'name' => sprintf( __( 'Fee: %s', 'yith-woocommerce-stripe' ), $item->get_name() ),
							);
						} elseif ( $item->is_type( 'shipping' ) ) {
							if ( ! $total ) {
								continue;
							}

							$line_item['price_data']['unit_amount']  = YITH_WCStripe::get_amount( $total, $currency, $order );
							$line_item['price_data']['product_data'] = array(
								// translators: 1. Shipping name.
								'name' => sprintf( __( 'Shipping: %s', 'yith-woocommerce-stripe' ), $item->get_name() ),
							);
						} elseif ( $item->is_type( 'tax' ) ) {
							$total = (float) $item->get_tax_total() + (float) $item->get_shipping_tax_total();

							if ( ! $total ) {
								continue;
							}

							$line_item['price_data']['unit_amount']  = YITH_WCStripe::get_amount( $total, $currency, $order );
							$line_item['price_data']['product_data'] = array(
								/**
								 * APPLY_FILTERS: yith_wcstripe_line_item_tax_name
								 *
								 * Filters item tax text.
								 *
								 * @param string Item tax text.
								 *
								 * @return string
								 */
								// translators: 1. Tax name.
								'name' => apply_filters( 'yith_wcstripe_line_item_tax_name', sprintf( __( 'Tax: %s', 'yith-woocommerce-stripe' ), $item->get_name() ) ),
							);
						}

						$line_items[] = $line_item;
					}
				}
			} else {
				$line_items = array(
					array(
						'quantity'   => 1,
						'price_data' => array(
							'currency'     => $currency,
							'unit_amount'  => YITH_WCStripe::get_amount( $order->get_total(), $currency, $order ),
							'product_data' => array(
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
								// translators: 1. Blog name. 2. Order id.
								'name' => apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),
							),
						),
					),
				);
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_create_checkout_session
			 *
			 * Filters checkout session paramaters before its creation.
			 *
			 * @param array Default array of options.
			 *
			 * @return array
			 */
			$defaults = apply_filters(
				'yith_wcstripe_create_checkout_session',
				array_merge(
					array(
						'mode'                       => 'payment',
						'currency'                   => $currency,
						'payment_method_types'       => array( 'card' ),
						'line_items'                 => $line_items,
						/**
						 * APPLY_FILTERS: yith_stripe_locale
						 *
						 * Filters language code.
						 *
						 * @param string Language code.
						 *
						 * @return string
						 */
						'locale'                     => apply_filters( 'yith_stripe_locale', substr( get_locale(), 0, 2 ) ),
						/**
						 * APPLY_FILTERS: yith_stripe_cancel_url
						 *
						 * Filters cancel URL.
						 *
						 * @param string Order checkout payment URL.
						 *
						 * @return string
						 */
						'cancel_url'                 => apply_filters( 'yith_stripe_cancel_url', $order->get_checkout_payment_url() ),
						'success_url'                => add_query_arg( $this->session_param, '{CHECKOUT_SESSION_ID}', WC()->api_request_url( get_class( $this ) ) ),
						'billing_address_collection' => 'auto',
						'shipping'                   => null,
						'payment_intent_data'        => array(
							'capture_method' => $this->capture ? 'automatic' : 'manual',
						),
						$customer_email ? array( 'customer_email' => $customer_email ) : array(),
					)
				)
			);

			$args = wp_parse_args( $args, $defaults );

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$session = $this->api->create_session( $args );

			return $session;
		}

		/* === HELPER METHODS === */

		/**
		 * Init Stripe SDK.
		 *
		 * @param string $private_key Private key to use to init SDK.
		 *
		 * @return void
		 */
		public function init_stripe_sdk( $private_key = '' ) {
			if ( is_a( $this->api, 'YITH_Stripe_Api' ) ) {
				return;
			}

			// Include lib.
			require_once YITH_WCSTRIPE_DIR . 'includes/class-yith-stripe-api.php';

			$private_key = ! $private_key ? $this->private_key : $private_key;
			$this->api   = new YITH_Stripe_API( $private_key );
		}

		/**
		 * Advise if the plugin cannot be performed
		 *
		 * @since 1.0.0
		 */
		public function admin_notices() {
			if ( 'no' === $this->enabled ) {
				return;
			}

			if ( ! function_exists( 'curl_init' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Stripe needs the CURL PHP extension.', 'yith-woocommerce-stripe' ) . '</p></div>';
			}

			if ( ! function_exists( 'json_decode' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Stripe needs the JSON PHP extension.', 'yith-woocommerce-stripe' ) . '</p></div>';
			}

			if ( ! function_exists( 'mb_detect_encoding' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Stripe needs the Multibyte String PHP extension.', 'yith-woocommerce-stripe' ) . '</p></div>';
			}

			if ( ! $this->public_key || ! $this->private_key ) {
				echo '<div class="error"><p>' . esc_html__( 'Please enter the public and private keys for Stripe gateway.', 'yith-woocommerce-stripe' ) . '</p></div>';
			}

			if ( 'standard' === $this->mode && 'test' !== $this->env && ! wc_checkout_is_https() && ! class_exists( 'WordPressHTTPS' ) ) {
				// translators: 1. Url to WC settings page. 2. Url to related Stripe doc.
				echo '<div class="error"><p>' . wp_kses_post( sprintf( __( 'Stripe sandbox testing is disabled and can performe live transactions but the <a href="%1$s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate. <a href="%2$s">Learn more</a>.', 'yith-woocommerce-stripe' ), admin_url( 'admin.php?page=wc-settings' ), 'https://stripe.com/help/ssl' ) ) . '</p></div>';
			}
		}

		/**
		 * Method to check blacklist (only for premium)
		 *
		 * @since 1.1.3
		 */
		public function is_blocked() {
			return false;
		}

		/**
		 * Receipt page
		 *
		 * @param int $order_id Order id.
		 */
		public function receipt_page( $order_id ) {
			if ( in_array( $this->mode, array( 'elements', 'standard' ), true ) ) {
				return;
			}

			$session_id = isset( $_GET[ $this->session_param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $this->session_param ] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order      = wc_get_order( $order_id );

			if ( $session_id && $order ) {
				echo '<p>' . esc_html__( 'Thank you for your order, please click the button below to pay with credit card using Stripe.', 'yith-woocommerce-stripe' ) . '</p>';
				echo '<a href="#" class="button" id="yith_wcstripe_open_checkout" data-session_id="' . esc_attr( $session_id ) . '">' . esc_html__( 'Proceed to payment', 'yith-woocommerce-stripe' ) . '</a>';

				$this->should_load_scripts() && wp_enqueue_script( 'yith-stripe-js' );
			}
		}

		/**
		 * Standard error handling for exceptions thrown by API class
		 *
		 * @param Exception $e    Exception to handle.
		 * @param array     $args Additional params for a better error handling.
		 *
		 * @return string Final error message
		 */
		public function error_handling( $e, $args = array() ) {
			$body     = $e->getJsonBody();
			$message  = $e->getMessage();
			$defaults = array(
				'order'  => null,
				// order: required for note mode.
				'mode'   => 'notice',
				// error handling mode: notice to print message via wc_add_notice / note to add message as order note / both execute both handlings.
				'format' => '',
				// message format: when not empty, this format string will be used for sprintf(), using message as only parameter.
			);

			list( $order, $mode, $format ) = yith_plugin_fw_extract( $args, 'order', 'mode', 'format' );

			if ( $body ) {
				$err = $body['error'];

				if ( isset( $err['code'] ) ) {
					$message = $this->get_error_message( $err['code'], $message, $err );
				}
			}

			if ( ! empty( $format ) ) {
				$message = sprintf( $format, $message );
			}

			switch ( $mode ) {
				case 'both':
				case 'note':
					if ( $order && $order instanceof WC_Order ) {
						$note_error_code = isset( $err ) && isset( $err['decline_code'] ) ? sprintf( ' (%s)', $err['decline_code'] ) : '';

						// translators: 1. Error code. 2. Error message.
						$note = sprintf( __( 'Stripe Error: %1$s - %2$s', 'yith-woocommerce-stripe' ), $e->getHttpStatus() . $note_error_code, $message );

						/**
						 * APPLY_FILTERS: yith_wcstripe_error_message_order_note
						 *
						 * Filters order note error message.
						 *
						 * @param string       $note Default error message.
						 * @param Exception    $e    Exception to handle.
						 * @param string|false $err  Error message if exists.
						 *
						 * @return string
						 */
						$order->add_order_note( apply_filters( 'yith_wcstripe_error_message_order_note', $note, $e, isset( $err ) ? $err : false ) );
					}

					if ( 'note' === $mode ) {
						break;
					}

					// if mode is both, continue with next case, to add the notice.

				case 'notice':
				default:
					wc_add_notice( $message, 'error' );
					break;
			}

			/**
			 * DO_ACTION: yith_wcstripe_gateway_error
			 *
			 * Triggered when exception thrown by API class.
			 *
			 * @param string   $message Exception message.
			 * @param WC_Order $order   Order to pay, when relevant.
			 * @param string   $mode    Error handling mode.
			 * @param string   $format  Message format.
			 */
			do_action( 'yith_wcstripe_gateway_error', $message, $order, $mode, $format );

			return $message;
		}

		/**
		 * Initialize and localize error messages
		 *
		 * @since 1.0.0
		 */
		protected function init_errors() {
			/**
			 * APPLY_FILTERS: yith_wcstripe_error_messages
			 *
			 * Filters Stripe error codes and messages.
			 *
			 * @param array containing error slug codes and error messages.
			 *
			 * @return array
			 */
			$this->errors = apply_filters(
				'yith_wcstripe_error_messages',
				array(
					// Codes.
					'incorrect_number'     => __( 'The card number is incorrect.', 'yith-woocommerce-stripe' ),
					'invalid_number'       => __( 'The card number is not a valid credit card number.', 'yith-woocommerce-stripe' ),
					'invalid_expiry_month' => __( 'The card\'s expiration month is invalid.', 'yith-woocommerce-stripe' ),
					'invalid_expiry_year'  => __( 'The card\'s expiration year is invalid.', 'yith-woocommerce-stripe' ),
					'invalid_cvc'          => __( 'The card\'s security code is invalid.', 'yith-woocommerce-stripe' ),
					'expired_card'         => __( 'The card has expired.', 'yith-woocommerce-stripe' ),
					'incorrect_cvc'        => __( 'The card\'s security code is incorrect.', 'yith-woocommerce-stripe' ),
					'incorrect_zip'        => __( 'The card\'s ZIP code failed validation.', 'yith-woocommerce-stripe' ),
					'card_declined'        => __( 'An error occurred while processing the card.', 'yith-woocommerce-stripe' ),
					'missing'              => __( 'There is no card on a customer that is being charged.', 'yith-woocommerce-stripe' ),
					'processing_error'     => __( 'An error occurred while processing the card.', 'yith-woocommerce-stripe' ),
					'rate_limit'           => __( 'An error occurred due to requests hitting the API too quickly. Please, let us know if you\'re consistently running into this error.', 'yith-woocommerce-stripe' ),
				)
			);

			/**
			 * APPLY_FILTERS: yith_wcstripe_decline_messages
			 *
			 * Filters Stripe declines codes and messages.
			 *
			 * @param array containing decline slug codes, decline messages and further steps code.
			 *
			 * @return array
			 */
			$this->decline_messages = apply_filters(
				'yith_wcstripe_decline_messages',
				array(
					// Codes.
					'approve_with_id'                   => array(
						'message'       => __( 'The payment cannot be authorized.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'try_again',
					),
					'call_issuer'                       => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'card_not_supported'                => array(
						'message'       => __( 'The card does not support this type of purchase.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'card_velocity_exceeded'            => array(
						'message'       => __( 'You have exceeded the balance or credit limit available on your card.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'currency_not_supported'            => array(
						'message'       => __( 'The card does not support the specified currency.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'do_not_honor'                      => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'do_not_try_again'                  => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'duplicate_transaction'             => array(
						'message'       => __( 'A transaction with an identical amount and credit card information was submitted very recently.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_us',
					),
					'expired_card'                      => array(
						'message'       => __( 'The card has expired.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'fraudulent'                        => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'generic_decline'                   => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'incorrect_number'                  => array(
						'message'       => __( 'The card number is incorrect.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'incorrect_cvc'                     => array(
						'message'       => __( 'The CVC number is incorrect.', 'yith-woocommerce-stripe' ),
						'further_steps' => '',
					),
					'incorrect_zip'                     => array(
						'message'       => __( 'The ZIP/postal code is incorrect.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'insufficient_funds'                => array(
						'message'       => __( 'The card has insufficient funds to complete the purchase.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'invalid_account'                   => array(
						'message'       => __( 'The card, or the account the card is connected to, is invalid.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'invalid_amount'                    => array(
						'message'       => __( 'The payment amount is invalid, or it exceeds the amount allowed.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'invalid_cvc'                       => array(
						'message'       => __( 'The CVC number is incorrect.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'invalid_expiry_year'               => array(
						'message'       => __( 'The expiration year is invalid.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'invalid_number'                    => array(
						'message'       => __( 'The card number is incorrect.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'issuer_not_available'              => array(
						'message'       => __( 'The card issuer could not be reached, so the payment could not be authorized.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'try_again',
					),
					'lost_card'                         => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'merchant_blacklist'                => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'new_account_information_available' => array(
						'message'       => __( 'The card, or the account the card is connected to, is invalid.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'no_action_taken'                   => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'not_permitted'                     => array(
						'message'       => __( 'The payment is not permitted.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'pickup_card'                       => array(
						'message'       => __( 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'processing_error'                  => array(
						'message'       => __( 'An error occurred while processing the card.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'try_again',
					),
					'reenter_transaction'               => array(
						'message'       => __( 'The payment could not be processed by the issuer.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'try_again',
					),
					'restricted_card'                   => array(
						'message'       => __( 'The card cannot be used to make this payment (it is possible it has been reported lost or stolen).', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'revocation_of_all_authorizations'  => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'revocation_of_authorization'       => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'security_violation'                => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'service_not_allowed'               => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'stolen_card'                       => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'stop_payment_order'                => array(
						'message'       => __( 'The card has been declined.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'testmode_decline'                  => array(
						'message'       => __( 'A Stripe test card number was used.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
					'transaction_not_allowed'           => array(
						'message'       => __( 'The card has been declined for an unknown reason.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'contact_bank',
					),
					'try_again_later'                   => array(
						'message'       => __( 'The card has been declined for an unknown reason.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'try_again',
					),
					'withdrawal_count_limit_exceeded'   => array(
						'message'       => __( 'You have exceeded the balance or credit limit available on your card.', 'yith-woocommerce-stripe' ),
						'further_steps' => 'change_card',
					),
				)
			);

			/**
			 * APPLY_FILTERS: yith_wcstripe_decline_messages
			 *
			 * Filters Stripe further steps codes and messages.
			 *
			 * @param array containing further steps codes and messages.
			 *
			 * @return array
			 */
			$this->further_steps = apply_filters(
				'yith_wcstripe_further_steps',
				array(
					'try_again'    => __( 'Please, try again later. If the problem persists, contact you card issuer for more information.', 'yith-woocommerce-stripe' ),
					'contact_bank' => __( 'Please, contact your card issuer for more information.', 'yith-woocommerce-stripe' ),
					'contact_us'   => __( 'Please, contact us to get help with this issue.', 'yith-woocommerce-stripe' ),
					'change_card'  => __( 'Please, double-check the information entered for your card, or try again using another card.', 'yith-woocommerce-stripe' ),
				)
			);
		}

		/**
		 * Returns error messages from a valid error code
		 * This is required in order to have localized messages shown at checkout
		 *
		 * @param string $error_code    Error code from Stripe API.
		 * @param string $error_message Error code coming from Stripe API, to be used when we cannot retrieve a better message.
		 * @param array  $error_object  Error object, as it was retrieved from Stripe API call response body.
		 *
		 * @return string Error message to be shown to the customer
		 * @since 1.8.2
		 */
		protected function get_error_message( $error_code, $error_message = '', $error_object = null ) {
			$error = $error_message;

			/**
			 * APPLY_FILTERS: yith_wcstripe_use_plugin_error_codes
			 *
			 * Filters if should use plugin error codes.
			 *
			 * @param bool Default value: true.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcstripe_use_plugin_error_codes', true ) ) {
				if ( empty( $this->errors ) ) {
					$this->init_errors();
				}

				$error = isset( $this->errors[ $error_code ] ) ? $this->errors[ $error_code ] : $error_message;

				if ( ! empty( $error_object ) && isset( $error_object['decline_code'] ) && isset( $this->decline_messages[ $error_object['decline_code'] ] ) ) {
					$additional_notes   = $this->decline_messages[ $error_object['decline_code'] ];
					$additional_message = $additional_notes['message'];
					$further_steps      = '';

					if ( ! empty( $additional_notes['further_steps'] ) && isset( $this->further_steps[ $additional_notes['further_steps'] ] ) ) {
						$further_steps = $this->further_steps[ $additional_notes['further_steps'] ];
						$further_steps = ' ' . $further_steps;
					}

					$error .= sprintf( ' (%s%s)', $additional_message, $further_steps );
				}
			}

			if ( ! $error ) {
				/**
				 * APPLY_FILTERS: yith_wcstripe_generic_error_message
				 *
				 * Filters Stripe generic error message shown if no error message is retrieved from Stripe API.
				 *
				 * @param string                Default message: 'An error occurred during your transaction. Please, try again later'.
				 * @param string $error_message Error code coming from Stripe API, to be used when we cannot retrieve a better message.
				 * @param string $error_code    Error code from Stripe API.
				 * @param string $error_object  Error object, as it was retrieved from Stripe API call response body.
				 *
				 * @return string
				 */
				$error = apply_filters( 'yith_wcstripe_generic_error_message', __( 'An error occurred during your transaction. Please, try again later.', 'yith-woocommerce-stripe' ), $error_message, $error_code, $error_object );
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_error_message
			 *
			 * Filters Stripe error message.
			 *
			 * @param string $error         Error message.
			 * @param string $error_message Error code coming from Stripe API, to be used when we cannot retrieve a better message.
			 * @param string $error_code    Error code from Stripe API.
			 * @param string $error_object  Error object, as it was retrieved from Stripe API call response body.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcstripe_error_message', $error, $error_message, $error_code, $error_object );
		}

		/**
		 * Return currency for the order; if no order was sent, use default store currency
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @return string Currency
		 */
		protected function get_currency( $order = null ) {
			$currency = $order ? $order->get_currency() : get_woocommerce_currency();

			/**
			 * APPLY_FILTERS: yith_wcstripe_gateway_currency
			 *
			 * Filters Stripe gateway currency.
			 *
			 * @param string $order_currency Subscription order currency.
			 * @param int    $order_id Subscription order ID.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcstripe_gateway_currency', $currency, $order );
		}

		/**
		 * Retrieves order by session id
		 *
		 * @param string $session_id Session id.
		 *
		 * @return WC_Order|bool Order, or false on failure
		 */
		protected function get_order_by_session_id( $session_id ) {
			$args = array();

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$args = array(
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'   => 'session_id',
							'value' => $session_id,
						),
					),
				);
			} else {
				$args = array(
					'stripe_session_id' => $session_id,
				);
			}

			$orders = wc_get_orders( $args );

			if ( empty( $orders ) ) {
				return false;
			}

			return array_shift( $orders );
		}
	}
}
