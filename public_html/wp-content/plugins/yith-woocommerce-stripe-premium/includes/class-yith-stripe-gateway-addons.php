<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Gateway class - Addons
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes\Gateways
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Gateway_Addons' ) ) {
	/**
	 * WooCommerce Stripe gateway class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Gateway_Addons extends YITH_WCStripe_Gateway_Advanced {

		/**
		 * Whether currently processing renew needs additional actions by the customer
		 * (An email will be sent when registering failed attempt, if this flag is true)
		 *
		 * @var bool
		 */
		protected $renew_needs_action = false;

		/**
		 * Whether errors during charge should be registered as failed attempts
		 * (usually this turns to false when processing manual renew attempts)
		 *
		 * @var bool
		 */
		protected $register_failed_attempt = true;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct();

			// declare support to YWSBS.
			if ( in_array( $this->mode, array( 'standard', 'elements' ), true ) && $this->save_cards ) {
				$this->supports = array_merge(
					$this->supports,
					array(
						'yith_subscriptions',
						'yith_subscriptions_scheduling',
						'yith_subscriptions_pause',
						'yith_subscriptions_multiple',
						'yith_subscriptions_payment_date',
						'yith_subscriptions_recurring_amount',
						'yith_pre_orders',
					)
				);
			}
			add_action( 'ywpo_process_pre_order_release_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ) );
		}

		/* === PROCESS PAYMENTS === */

		/**
		 * Process the payment
		 *
		 * @param int    $order_id       Order to pay.
		 * @param string $payment_method Unique identifier of the payment method that should be used for this payment; if empty, posted value will be used instead.
		 *
		 * @throws Exception When an error occurs while processing payment.
		 * @return array
		 */
		public function process_payment( $order_id, $payment_method = '' ) {
			$order               = wc_get_order( $order_id );
			$this->current_order = $order;

			if ( $this->is_upon_release_pre_order( $order ) ) { // Processing pre-order.
				return $this->process_upon_release_pre_order( $order );
			} elseif ( $this->is_subscription_payment( $order_id ) ) { // Processing subscription.
				return $this->process_subscription();
			} else {
				return parent::process_payment( $order_id, $payment_method );
			}
		}

		/**
		 * Check if the current order contains a pre-order product, and it is intended to be charged upon release.
		 *
		 * @param WC_Order $order The WC_Order object.
		 *
		 * @return bool
		 */
		public function is_upon_release_pre_order( $order ) {
			$return = false;
			if ( function_exists( 'YITH_Pre_Order_Orders_Manager' ) ) {
				$return = ywpo_is_upon_release_order( $order ) && ! ywpo_is_pay_later_order( $order );
			}
			return $return;
		}

		/**
		 * Processes an upon release pre-order. Stores the payment method and creates a SetupIntent for later use.
		 *
		 * @param WC_Order $order The WC_Order object.
		 *
		 * @throws Exception When an error occurs while processing upon release pre-order.
		 * @return array|string[]
		 */
		public function process_upon_release_pre_order( $order = null ) {
			if ( empty( $order ) ) {
				$order = $this->current_order;
			}

			try {
				$this->init_stripe_sdk();

				// nonce verification is not required, as we're running during checkout handling, and nonce was verified already by WC.
				// phpcs:disable WordPress.Security.NonceVerification.Missing
				$payment_method = isset( $_POST['stripe_payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_payment_method'] ) ) : false;

				if ( ! $payment_method && isset( $_POST['wc-yith-stripe-payment-token'] ) ) {
					$token_id = intval( $_POST['wc-yith-stripe-payment-token'] );
					$token    = WC_Payment_Tokens::get( $token_id );

					if ( $token && $token->get_user_id() === get_current_user_id() && $token->get_gateway_id() === $this->id ) {
						$payment_method = $token->get_token();
					}
				}
				// phpcs:enable WordPress.Security.NonceVerification.Missing

				$this->save_token( $payment_method );

				$customer = $this->get_customer( $order );

				$intent = $this->create_session_setup_intent(
					array(
						'order_id'       => $order->get_id(),
						'payment_method' => $payment_method,
						'customer'       => $customer->id,
						'confirm'        => true,
					)
				);

				// no intent yet; return error.
				if ( ! $intent ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ), null );
				}

				// if intent is still missing payment method, return an error.
				if ( 'requires_payment_method' === $intent->status ) {
					throw new Exception( __( 'No payment method could be applied to this payment; please, try again by selecting another payment method.', 'yith-woocommerce-stripe' ) );
				}

				// register meta for the order.
				$order->update_meta_data( 'intent_id', $intent->id );
				$order->update_meta_data( 'stripe_customer_id', $customer ? $customer->id : false );
				$order->update_meta_data( 'yith_stripe_token', $intent->payment_method );

				$order->save();

				// Remove cart.
				WC()->cart->empty_cart();

				YITH_Pre_Order_Orders_Manager::set_as_pre_order_pending_payment( $order );

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
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
		}

		/**
		 * Process pre-order payment, when YITH Pre-Order for WooCommerce Premium triggers payment
		 *
		 * @param WC_Order $order The WC_Order object.
		 *
		 * @throws Exception When an error occurs while processing pre-order payment.
		 * @return bool Status of the pre-order payment operation
		 */
		public function process_pre_order_release_payment( $order ) {
			$order_id = $order->get_id();

			$this->log( 'Processing upon release payment for pre-order #' . $order_id );

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			try {
				$result = $this->pay_pre_order( $order );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				return true;
			} catch ( Exception $e ) {
				$user_id = $order->get_customer_id();
				$retry   = 'yes' === $this->get_option( 'retry_with_other_cards' );

				// before registering fail, try to pay with other registered cards.
				if ( $user_id && $retry ) {
					$customer_tokens = WC_Payment_Tokens::get_customer_tokens( $user_id, $this->id );

					$current_year  = gmdate( 'Y' );
					$current_month = gmdate( 'm' );

					if ( count( $customer_tokens ) > 1 ) {
						foreach ( $customer_tokens as $customer_token ) {
							/**
							 * Each of Payment Tokens registered for the customer.
							 *
							 * @var $customer_token WC_Payment_Token_CC
							 */
							$card_id   = $customer_token->get_token();
							$exp_year  = $customer_token->get_expiry_year();
							$exp_month = $customer_token->get_expiry_month();

							if ( ! $card_id ) {
								continue;
							}

							if ( $exp_year < $current_year || ( $exp_year === $current_year && $exp_month < $current_month ) ) {
								continue;
							}

							try {
								$result = $this->pay_pre_order( $order, null, $customer_token );

								if ( $result && ! is_wp_error( $result ) ) {
									return true;
								}

								continue;
							} catch ( Exception $e ) {
								continue;
							}
						}
					}
				}

				return false;
			}
		}

		/**
		 * Performs the pre-order payment on Stripe
		 *
		 * @param WC_Order         $order  Order to pay, when relevant.
		 * @param float            $amount Amount to pay; if null, order total will be used instead.
		 * @param WC_Payment_Token $token  Token that should be used to attempt payment; if null, default for the pre-order or for the customer will be used.
		 *
		 * @throws Stripe\Exception\ApiErrorException When there is an error while processing payment.
		 * @since 1.0.0
		 */
		public function pay_pre_order( $order = null, $amount = null, $token = null ) {
			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$order_id    = $order->get_id();
			$user_id     = $order->get_customer_id();
			$customer_id = false;
			$currency    = $this->get_currency( $order );

			// retrieve amount to pay.
			$amount = ! is_null( $amount ) ? (float) $amount : (float) $order->get_total();

			$this->log( 'Pre-order amount ' . $amount );

			// if amount is 0, set payment as completed and skip.
			if ( ! $amount ) {
				// Payment complete.
				$order->payment_complete();

				return true;
			}

			// if amount do not match minimum requirements, throw error.
			if ( $amount * 100 < 50 ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, to use this payment method the minimum order total allowed is 0.50.', 'yith-woocommerce-stripe' ) );
			}

			// try to retrieve Stripe Customer: first try from subscription (if any).
			if ( $order->get_meta( 'stripe_customer_id' ) ) {
				$customer_id = $order->get_meta( 'stripe_customer_id' );
			}

			// try to retrieve Stripe Customer: if we had no luck with subscription, try with order's customer (if any).
			if ( ! $customer_id && $user_id ) {
				$customer    = YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );
				$customer_id = isset( $customer['id'] ) ? $customer['id'] : false;
			}

			// if we have no customer at this point, we cannot proceed with payment; skip.
			if ( ! $customer_id ) {
				// translators: 1. Order number.
				return new WP_Error( 'stripe_error', sprintf( __( 'Couldn\'t find any valid Stripe Customer ID for pre-order #%d.', 'yith-woocommerce-stripe' ), $order_id ) );
			}

			// try to retrieve Stripe Source: from provide token.
			$source = $token ? $token->get_token() : false;

			// try to retrieve Stripe Source: if token wasn't provided, check for method registered within subscription (if any).
			if ( ! $source ) {
				$pre_order_source = $order->get_meta( 'yith_stripe_token' );

				if ( $pre_order_source ) {
					try {
						$card   = $this->api->get_payment_method( $pre_order_source );
						$source = $card ? $card->id : false;
					} catch ( Exception $e ) {
						$source = false;
					}

					// if we found source from subscription, and exists order's customer, try to retrieve token too.
					if ( $source && $user_id ) {
						$tokens = WC_Payment_Tokens::get_tokens(
							array(
								'gateway_id' => $this->id,
								'user_id'    => $user_id,
							)
						);

						if ( ! empty( $tokens ) ) {
							// search source among defined tokes.
							foreach ( $tokens as $user_token ) {
								if ( $source && $user_token->get_token() === $source ) {
									$token = $user_token;
									break;
								}
							}
						}
					}
				}
			}

			// try to retrieve Stripe Source: if we had no luck with subscription, check default method for order's customer (if any).
			if ( ! $source && $user_id ) {
				$default_token = WC_Payment_Tokens::get_customer_default_token( $user_id );

				if ( $default_token ) {
					$source = $default_token->get_token();
					$token  = $default_token;
				}
			}

			// if we have no source at this point, we cannot proceed with payment; skip.
			if ( ! $source ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, we couldn\'t find any valid payment method for your renewal.', 'yith-woocommerce-stripe' ) );
			}

			try {
				$intent = $this->api->create_intent(
					array(
						'amount'               => YITH_WCStripe::get_amount( $order->get_total() ),
						'currency'             => $currency,
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
						'description'          => apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),
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
						'metadata'             => apply_filters(
							'yith_wcstripe_metadata',
							array(
								'order_id'    => $order_id,
								'order_email' => $order->get_billing_email(),
								'instance'    => $this->instance,
							),
							'charge'
						),
						'customer'             => $customer_id,
						'payment_method_types' => array( 'card' ),
						'payment_method'       => $source,
						'off_session'          => true,
						'confirm'              => true,
						/**
						 * APPLY_FILTERS: yith_wcstripe_pay_pre_order_capture_method
						 *
						 * Filters Pre-Order capture method.
						 *
						 * @param string                       Default method 'automatic'.
						 * @param WC_Order                     Order object.
						 * @param string                       Stripe capture parameter.
						 * @param YITH_WCStripe_Gateway_Addons YITH_WCStripe_Gateway_Addons class instance.
						 *
						 * @return string
						 */
						'capture_method'       => apply_filters( 'yith_wcstripe_pay_pre_order_capture_method', 'automatic', $order, $this->capture, $this ),
						'return_url'           => $this->get_verification_url( $order ?? null ),
					)
				);
			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$body = $e->getJsonBody();
				$err  = $body['error'];

				if (
					isset( $err['payment_intent'] ) &&
					isset( $err['payment_intent']['status'] ) &&
					in_array( $err['payment_intent']['status'], array( 'requires_action', 'requires_payment_method' ), true ) &&
					(
						! empty( $err['payment_intent']['next_action'] ) && isset( $err['payment_intent']['next_action']->type ) && 'use_stripe_sdk' === $err['payment_intent']['next_action']->type ||
						'authentication_required' === $err['code']
					)
				) {
					if ( isset( $token ) ) {
						$token->update_meta_data( 'confirmed', false );
						$token->save();
					}
					return new WP_Error( 'stripe_error', __( 'Please, validate your payment method before proceeding any further.', 'yith-woocommerce-stripe' ) );
				} else {
					return new WP_Error( 'stripe_error', $err['message'] );
				}
			} catch ( Exception $e ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			}

			// intent has failed; return error message to customer.
			if ( ! is_a( $intent, 'Stripe\PaymentIntent' ) && is_array( $intent ) && isset( $intent['error'] ) ) {
				// translators: 1. Renew ID.
				$error_message = sprintf( __( 'Error while processing pre-order payment: %s.', 'yith-woocommerce-stripe' ), $intent['error']['message'] );

				return new WP_Error( 'stripe_error', $error_message );
			}

			// check intent confirmation.
			if ( ! $intent ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing the pre-order payment; please, try again.', 'yith-woocommerce-stripe' ) );
			} elseif ( 'requires_action' === $intent->status ) {
				if ( isset( $token ) ) {
					$token->update_meta_data( 'confirmed', false );
					$token->save();
				}
				return new WP_Error( 'stripe_error', __( 'Please, validate your payment method before proceeding any further.', 'yith-woocommerce-stripe' ) );
			} elseif ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			}

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $this->api->get_charge( $charge );

			// payment complete.
			$order->payment_complete( $charge->id );

			// add order note.
			// translators: 1. Stripe charge id.
			$order->add_order_note( sprintf( __( 'Stripe payment approved (ID: %s)', 'yith-woocommerce-stripe' ), $charge->id ) );

			// update order meta.
			$order->update_meta_data( '_captured', $charge->captured ? 'yes' : 'no' );
			$order->update_meta_data( 'stripe_customer_id', $customer_id );
			$order->update_meta_data( 'yith_stripe_token', $source );
			$order->save();

			// Return thank you page redirect.
			return true;
		}

		/**
		 * Process renew, when YITH WooCommerce Subscription triggers payment
		 *
		 * @param \WC_Order $order        Renew order.
		 * @param bool      $output_error Whether to output error with a notice.
		 *
		 * @throws Exception When an error occurs while processing renew payment.
		 * @return bool Status of the renew operation
		 */
		public function process_renew( $order, $output_error = false ) {
			if ( ! $order ) {
				$this->register_failed_renew( $order, __( 'Error while processing renewal payment: this renewal order does not exist.', 'yith-woocommerce-stripe' ) );

				return false;
			}

			$order_id        = $order->get_id();
			$subscription_id = $this->get_subscription_id_by_order( $order_id );

			$this->log( 'Processing payment for renew order #' . $order_id );

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			if ( ! $subscription_id ) {
				$this->log( 'Subscription not found #' );

				return false;
			}

			if ( $this->has_active_subscription( $subscription_id ) ) {
				// translators: 1. Order id.
				$this->log( sprintf( __( 'Error while processing renewal payment: order #%d is part of an active Stripe Subscription. No manual renewal is required.', 'yith-woocommerce-stripe' ), $order_id ) );

				return false;
			}

			try {
				$result = $this->pay_renew( $order );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				return true;
			} catch ( Exception $e ) {
				$user_id     = $order->get_customer_id();
				$retry_renew = 'yes' === $this->get_option( 'retry_with_other_cards' );

				// before registering fail, try to pay with other registered cards.
				if ( $user_id && $retry_renew ) {
					$customer_tokens = WC_Payment_Tokens::get_customer_tokens( $user_id, $this->id );

					$current_year  = gmdate( 'Y' );
					$current_month = gmdate( 'm' );

					if ( count( $customer_tokens ) > 1 ) {
						foreach ( $customer_tokens as $customer_token ) {
							/**
							 * Each of Payment Tokens registered for the customer.
							 *
							 * @var $customer_token \WC_Payment_Token_CC
							 */
							$card_id   = $customer_token->get_token();
							$exp_year  = $customer_token->get_expiry_year();
							$exp_month = $customer_token->get_expiry_month();

							if ( ! $card_id ) {
								continue;
							}

							if ( $exp_year < $current_year || ( $exp_year === $current_year && $exp_month < $current_month ) ) {
								continue;
							}

							try {
								$result = $this->pay_renew( $order, null, $customer_token );

								if ( $result && ! is_wp_error( $result ) ) {
									return true;
								}

								continue;
							} catch ( Exception $e ) {
								continue;
							}
						}
					}
				}

				// translators: 1. Error details.
				$error_message = sprintf( __( 'Error while processing renew payment: %s.', 'yith-woocommerce-stripe' ), $e->getMessage() );
				$this->register_failed_renew( $order, $error_message );

				if ( function_exists( 'wc_add_notice' ) && $output_error ) {
					wc_add_notice( $error_message, 'error' );
				}

				return false;
			}
		}

		/**
		 * Process manual renew, when user trigger it using Renew button on order page
		 *
		 * @param \WC_Order $order Renew order.
		 *
		 * @return bool Status of the renew operation
		 */
		public function process_manual_renew( $order ) {
			$this->register_failed_attempt = false;

			$order_id        = $order->get_id();
			$user_id         = $order->get_user_id();
			$subscription_id = $this->get_subscription_id_by_order( $order_id );

			if ( ! $subscription_id ) {
				return false;
			}

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			if ( $this->has_active_subscription( $subscription_id ) ) {
				// old style subscription, that already registered failed attempts: we have invoice id in user meta!
				$subscription    = ywsbs_get_subscription( (int) $subscription_id );
				$parent_order_id = $subscription ? $subscription->order_id : 0;
				$parent_order    = wc_get_order( $parent_order_id );

				$parent_order_failed_attempts = $parent_order->get_meta( 'failed_attemps' );

				if ( $user_id && ( $order->get_meta( 'failed_attemps' ) > 0 || $parent_order_failed_attempts > 0 ) ) {
					$failed_invoices = get_user_meta( $user_id, 'failed_invoices', true );
					$invoice_id      = isset( $failed_invoices[ $subscription_id ] ) ? $failed_invoices[ $subscription_id ] : false;

					if ( $invoice_id ) {
						try {
							$this->api->pay_invoice( $invoice_id );

							unset( $failed_invoices[ $subscription_id ] );
							update_user_meta( get_current_user_id(), 'failed_invoices', $failed_invoices );

							return true;
						} catch ( Exception $e ) {
							// translators: 1. Error details.
							$error_message = sprintf( __( 'Error while processing manual renew: %s.', 'yith-woocommerce-stripe' ), $e->getMessage() );
							$this->register_failed_renew( $order, $error_message );

							wc_add_notice( $error_message, 'error' );

							return false;
						}
					}
				}
			} else {
				// new style subscription, it doesn't matter if we have failed attempts, let's try to process charge.
				return $this->process_renew( $order, true );
			}

			$this->register_failed_attempt = true;

			return false;
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
		 * @param string $reason   Refund reason.
		 *
		 * @throws Exception When an error occurs while refunding order.
		 * @return mixed True or False based on success, or WP_Error
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			$order          = wc_get_order( $order_id );
			$transaction_id = $order->get_transaction_id();
			$order_currency = $this->get_currency( $order );
			$refunds        = $order->get_refunds();
			$refund         = array_shift( $refunds );

			if ( isset( $order->bitcoin_inbound_address ) || isset( $order->bitcoin_uri ) ) {
				return new WP_Error( 'yith_stripe_no_bitcoin', __( 'Refund not supported for bitcoin', 'yith-woocommerce-stripe' ) );
			}

			// subdivide refund among items.
			$amounts = array();

			foreach ( $order->get_items( array( 'line_item', 'shipping', 'fee', 'tax' ) ) as $item_id => $item ) {
				$charge_id = wc_get_order_item_meta( $item_id, '_subscription_charge_id', true );
				$index     = $charge_id ? $charge_id : $transaction_id;

				if ( ! isset( $amounts[ $index ] ) ) {
					$amounts[ $index ] = array(
						'total'  => 0,
						'refund' => 0,
					);
				}

				$amounts[ $index ]['total'] += $order->get_line_total( $item, true );

				foreach ( $refund->get_items( array( 'line_item', 'shipping', 'fee', 'tax' ) ) as $refunded_item ) {
					if ( isset( $refunded_item['refunded_item_id'] ) && $refunded_item['refunded_item_id'] === $item_id ) {
						$amounts[ $index ]['refund'] += abs( $refund->get_line_total( $refunded_item, true ) );
					}
				}
			}

			$remaining_amount = abs( $refund->get_total() ) - array_sum( array_column( $amounts, 'refund' ) );

			if ( $remaining_amount > 0 ) {
				foreach ( $amounts as & $amount ) {
					$amount['refund'] += $remaining_amount * $amount['total'] / $order->get_total();
				}
			}

			try {

				// Initializate SDK and set private key.
				$this->init_stripe_sdk();
				$refund_ids = array();

				foreach ( $amounts as $charge_id => $data ) {
					$refund_amount = round( $data['refund'], 2 );
					$params        = array(
						'amount' => YITH_WCStripe::get_amount( $refund_amount, $order_currency, $order ),
					);

					// If a reason is provided, add it to the Stripe metadata for the refund.
					if ( $reason && in_array( $reason, array( 'duplicate', 'fraudulent', 'requested_by_customer' ), true ) ) {
						$params['reason'] = $reason;
					}

					/**
					 * APPLY_FILTERS: yith_wcstripe_metadata
					 *
					 * Filters Stripe charge metadata.
					 *
					 * @param array  Default metadata containing order_id, order_email and instance.
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
					$stripe_refund = $this->api->refund( $charge_id, $params );
					$refund_ids[]  = $stripe_refund->id;

					$this->log( 'Stripe Refund Response: ' . print_r( $stripe_refund, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

					// translators: 1. Amount refunded. 2. Refund id.
					$order->add_order_note( sprintf( __( 'Refunded %1$s - Refund ID: %2$s', 'woocommerce' ), $refund_amount, $stripe_refund['id'] ) );
				}

				if ( 1 === count( $refund_ids ) ) {
					$refund_ids = array_pop( $refund_ids );
				}

				$refund->update_meta_data( '_refund_stripe_id', $refund_ids );
				$refund->save();

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
		 * Process the subscription
		 *
		 * @param WC_Order $order Order to process, when relevant.
		 *
		 * @throws Exception When there is an error while processing payment.
		 * @return array
		 * @internal param string $cart_token
		 */
		protected function process_subscription( $order = null ) {
			if ( empty( $order ) ) {
				$order = $this->current_order;
			}

			try {

				// retrieve payment intent.
				$intent = $this->get_intent( $order );

				// if no intent was found, crate one on the fly.
				if ( ! $intent || ( 0 === strpos( $intent->id, 'seti' ) && $order->get_total() > 0 ) ) {
					$intent = $this->create_session_intent( array( 'order_id' => $order->get_id() ) );
				}

				// no intent yet; return error.
				if ( ! $intent ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ), null );
				}

				// intent refers to another transaction: return error.
				if ( (int) $order->get_id() !== (int) $intent->metadata->order_id && yith_wcstripe_get_cart_hash() !== $intent->metadata->cart_hash ) {
					throw new Exception( __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ), null );
				}

				// nonce verification is not required, as we're running during checkout handling, and nonce was verified already by WC.
				// phpcs:disable WordPress.Security.NonceVerification.Missing
				$payment_method = isset( $_POST['stripe_payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_payment_method'] ) ) : false;

				if ( ! $payment_method && isset( $_POST['wc-yith-stripe-payment-token'] ) ) {
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
							strtolower( $order->get_currency() ) !== $intent->currency
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

				// confirmation requires additional action; return to customer.
				if ( 'requires_action' === $intent->status ) {
					// manual confirm after checkout.
					$this->current_intent_secret = $intent->client_secret;

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);
				}

				// register intent for the order.
				$order->update_meta_data( 'intent_id', $intent->id );

				$result = self::pay( $order );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				// process subscriptions.
				$result = $this->create_subscriptions_for_order( $order );

				if ( is_wp_error( $result ) ) {
					return array(
						'result'   => 'fail',
						'redirect' => '',
					);
				}

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
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
		}

		/**
		 * Performs the payment on Stripe
		 *
		 * @param WC_Order $order  Order to pay, when relevant.
		 * @param float    $amount Amount to pay; if null, order total will be used instead.
		 *
		 * @return bool|WP_Error
		 * @throws Stripe\Exception\ApiErrorException|Exception When an error with payment occurs.
		 * @since 1.0.0
		 */
		public function pay( $order = null, $amount = null ) {
			// get amount.
			$amount = ! is_null( $amount ) ? (float) $amount : (float) $order->get_total();

			// process parent method.
			$result = parent::pay( $order, $amount );

			if ( $result ) {
				$subscriptions = $order->get_meta( 'subscriptions' );
				$customer_id   = $order->get_meta( '_stripe_customer_id' );

				// retrieve order subscriptions from session if we couldn't find subs on order meta.
				if ( empty( $subscriptions ) && ! is_null( WC()->session ) ) {
					$order_args = WC()->session->get( 'ywsbs_order_args', array() );
					if ( isset( $order_args['subscriptions'] ) ) {
						$subscriptions = $order_args['subscriptions'];
					}

					WC()->session->set( 'ywsbs_order_args', array() );
				}

				// register metas for the subscriptions.
				if ( ! empty( $subscriptions ) ) {
					$intent = $this->get_intent( $order );

					// we're processing a free sub; let's create customer & card for renews.
					if ( ! $amount || $amount * 100 < 50 ) {
						$customer = $this->get_customer( $order );

						$customer_id = $customer ? $customer->id : false;
						$order->update_meta_data( '_stripe_customer_id', $customer_id );

						if ( isset( $intent->payment_method ) ) {
							$token = $this->save_token( $intent->payment_method );

							if ( $token ) {
								$order->add_payment_token( $token );
							}
						}
					}

					// if guest user, attach payment method to Stripe user for future usege (usually it wouldn't be stored).
					if ( ! is_user_logged_in() ) {
						$this->attach_payment_method( $customer, $intent->payment_method );
					}

					foreach ( $subscriptions as $subscription_id ) {
						$subscription = ywsbs_get_subscription( (int) $subscription_id );
						if ( $subscription ) {
							$subscription->set( 'stripe_customer_id', $customer_id );
							$subscription->set( 'yith_stripe_token', $intent->payment_method );

							method_exists( $subscription, 'save' ) && $subscription->save();
						}
					}
				}
			}

			return $result;
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
				$result = self::pay( $order );

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				return $this->create_subscriptions_for_order( $order );
			} catch ( Exception $e ) {
				return new WP_Error( 'stripe_error', $e->getMessage() );
			}
		}

		/**
		 * Checks whether we should process subscriptions for the order with Stripe Classic
		 *
		 * @param int $order_id Order id.
		 *
		 * @return bool Whether we should process subscriptions for the order with Stripe Classic
		 */
		public function is_subscription_payment( $order_id ) {
			return in_array( $this->mode, array( 'standard', 'elements' ), true ) &&
				$this->order_contains_subscription( $order_id ) &&
				defined( 'YITH_YWSBS_PREMIUM' ) &&
				'stripe' === $this->renew_mode;
		}

		/**
		 * Creates subscriptions on Stripe for the passed order
		 *
		 * @param \WC_Order $order Order containing subscription items.
		 *
		 * @return bool|WP_Error True or WP_Error with details about the error
		 */
		public function create_subscriptions_for_order( $order ) {
			$order_id = $order->get_id();

			if ( $this->is_subscription_payment( $order->get_id() ) ) {
				try {
					// retrieve customer.
					$customer = $this->get_customer( $order );

					$subscription_total = 0;

					// create subscriptions.
					$subscriptions = $order->get_meta( 'subscriptions' );

					if ( ! empty( $subscriptions ) ) {
						foreach ( array_map( 'intval', $subscriptions ) as $subscription_id ) {
							$subscription   = ywsbs_get_subscription( (int) $subscription_id );
							$plan           = $this->get_plan( $subscription, $order );
							$interval       = $subscription->price_time_option;
							$interval_count = $subscription->price_is_per;

							// if interval is month, we need some extra care: check https://stripe.com/docs/billing/subscriptions/billing-cycle for reference.
							if ( 'months' === $interval ) {
								$current_day   = gmdate( 'j' );
								$current_month = gmdate( 'n' );
								$current_year  = gmdate( 'Y' );

								$next_month = (int) $current_month + (int) $interval_count;

								if ( $next_month > 12 ) {
									$year_increase = floor( $next_month / 12 );

									$next_month = $next_month % 12;
									$next_year  = (int) $current_year + (int) $year_increase;
								} else {
									$next_year = $current_year;
								}

								$last_day_target_month = gmdate( 't', strtotime( "{$next_year}-{$next_month}-01" ) );

								$next_day = $current_day > $last_day_target_month ? $last_day_target_month : $current_day;

								$first_payment_time = strtotime( "{$next_year}-{$next_month}-{$next_day}" );
							} else {
								$first_payment_time = strtotime( "+{$interval_count} {$interval}" );
							}

							/**
							 * APPLY_FILTERS: yith_wcstripe_first_payment_time
							 *
							 * Filters subscription first payment time.
							 *
							 * @param int                                    Days of first payment.
							 * @param YWSBS_Subscription $subscription       Subscription object.
							 * @param int                $first_payment_time Time of first payment.
							 * @param WC_Order           $order              Order object.
							 *
							 * @return int
							 */
							$first_payment_days = apply_filters( 'yith_wcstripe_first_payment_time', ( $first_payment_time - time() ) / DAY_IN_SECONDS, $subscription, $first_payment_time, $order );

							// create subscription on stripe; set billing cycle to start after one interval.
							$stripe_subscription = $this->api->create_subscription(
								$customer,
								$plan->id,
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
											array(
												'subscription_id' => $subscription_id,
												'instance' => $this->instance,
											),
											'create_subscription'
										),
									),
									! $plan->trial_period_days ? array(
										'proration_behavior'   => 'none',
										'billing_cycle_anchor' => $first_payment_days * DAY_IN_SECONDS + time(),
									) : array(
										'trial_period_days' => $plan->trial_period_days,
									)
								)
							);

							// set meta data.
							$subscription->set( 'stripe_subscription_id', $stripe_subscription->id );
							$subscription->set( 'stripe_customer_id', $customer ? $customer->id : false );
							$subscription->set( 'payment_due_date', $stripe_subscription->current_period_end );

							// save subscription.
							method_exists( $subscription, 'save' ) && $subscription->save();

							// set meta of order.
							$user = $order->get_user();

							if ( $customer ) {
								$order->update_meta_data( 'Subscriber ID', $customer ? $customer->id : false );
							}

							if ( $user ) {
								$order->update_meta_data( 'Subscriber first name', $user->first_name );
								$order->update_meta_data( 'Subscriber last name', $user->last_name );
								$order->update_meta_data( 'Subscriber address', $user->billing_email );
							}

							$order->update_meta_data( 'Subscriber payment type', $this->id );
							$order->update_meta_data( 'Stripe Subscribtion ID', $stripe_subscription->id );
						}
					}

					// pay.
					$order->update_meta_data( '_stripe_subscription_total', $subscription_total );
					$order->update_meta_data( '_stripe_customer_id', $customer ? $customer->id : false );
					$order->save();
				} catch ( Stripe\Exception\ApiErrorException $e ) {
					$this->error_handling(
						$e,
						array(
							'mode'  => 'both',
							'order' => $order,
						)
					);

					// if were creating a subscription, cancel it, since operation failed, and we cannot leave it active
					// this will also close pending invoices created for that subscription.
					try {
						if ( isset( $stripe_subscription ) ) {
							$this->api->cancel_subscription( $customer, $stripe_subscription->id );
						}
					} catch ( Exception $e ) {
						// log the error.
						// translators: 1. Stripe subscription id.
						$this->log( sprintf( __( 'Error while canceling subscription %s', 'yith-woocommerce-stripe' ), $stripe_subscription->id ) );
					}

					return new WP_Error( 'stripe_connect', $e->getMessage() );

				} catch ( Exception $e ) {
					// if were creating a subscription, cancel it, since operation failed, and we cannot leave it active
					// this will also close pending invoices created for that subscription.
					try {
						if ( isset( $stripe_subscription ) ) {
							$this->api->cancel_subscription( $customer, $stripe_subscription->id );
						}
					} catch ( Exception $e ) {
						// log the error.
						// translators: 1. Stripe subscription id.
						$this->log( sprintf( __( 'Error while canceling subscription %s', 'yith-woocommerce-stripe' ), $stripe_subscription->id ) );
					}

					wc_add_notice( $e->getMessage(), 'error' );

					return new WP_Error( 'stripe_connect', $e->getMessage() );
				}
			}

			return true;
		}

		/**
		 * Performs the payment on Stripe
		 *
		 * @param WC_Order          $order  Order to pay, when relevant.
		 * @param float             $amount Amount to pay; if null, order total will be used instead.
		 * @param \WC_Payment_Token $token  Token that should be used to attempt payment; if null, default for the sub or for the customer will be used.
		 *
		 * @return bool|WP_Error
		 * @throws Stripe\Exception\ApiErrorException When there is an error while processing payment.
		 * @since 1.0.0
		 */
		protected function pay_renew( $order = null, $amount = null, $token = null ) {
			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$order_id        = $order->get_id();
			$user_id         = $order->get_customer_id();
			$subscriptions   = $order->get_meta( 'subscriptions' );
			$subscription_id = ! empty( $subscriptions ) ? array_pop( $subscriptions ) : false;
			$currency        = $this->get_currency( $order );

			// retrieve amount to pay.
			$amount = ! is_null( $amount ) ? (float) $amount : (float) $order->get_total();

			$this->log( 'Renew amount ' . $amount );

			// if amount is 0, set payment as completed and skip.
			if ( ! $amount ) {
				// Payment complete.
				$order->payment_complete();

				return true;
			}

			// if amount do not match minimum requirements, throw error.
			if ( $amount * 100 < 50 ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, to use this payment method the minimum order total allowed is 0.50.', 'yith-woocommerce-stripe' ) );
			}

			// try to retrieve Stripe Customer: first try from subscription (if any).
			if ( $subscription_id ) {
				$subscription = ywsbs_get_subscription( (int) $subscription_id );
				if ( $subscription ) {
					$customer_id = $subscription->stripe_customer_id;
				}
			}

			// try to retrieve Stripe Customer: if we had no luck with subscription, try with order's customer (if any).
			if ( ! $customer_id && $user_id ) {
				$customer    = YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );
				$customer_id = isset( $customer['id'] ) ? $customer['id'] : false;
			}

			// if we have no customer at this point, we cannot proceed with payment; skip.
			if ( ! $customer_id ) {
				// translators: 1. Order number.
				return new WP_Error( 'stripe_error', sprintf( __( 'Couldn\'t find any valid Stripe Customer ID for order #%d.', 'yith-woocommerce-stripe' ), $order_id ) );
			}

			// try to retrieve Stripe Source: from provide token.
			$source = $token ? $token->get_token() : false;

			// try to retrieve Stripe Source: if token wasn't provided, check for method registered within subscription (if any).
			if ( ! $source && $subscription ) {
				$subscription_source = $subscription->yith_stripe_token;

				if ( $subscription_source ) {
					try {
						$card   = $this->api->get_payment_method( $subscription_source );
						$source = $card ? $card->id : false;
					} catch ( Exception $e ) {
						$source = false;
					}

					// if we found source from subscription, and exists order's customer, try to retrieve token too.
					if ( $source && $user_id ) {
						$tokens = WC_Payment_Tokens::get_tokens(
							array(
								'gateway_id' => $this->id,
								'user_id'    => $user_id,
							)
						);

						if ( ! empty( $tokens ) ) {
							// search source among defined tokes.
							foreach ( $tokens as $user_token ) {
								if ( $source && $user_token->get_token() === $source ) {
									$token = $user_token;
									break;
								}
							}
						}
					}
				}
			}

			// try to retrieve Stripe Source: if we had no luck with subscription, check default method for order's customer (if any).
			if ( ! $source && $user_id ) {
				$default_token = WC_Payment_Tokens::get_customer_default_token( $user_id );

				if ( $default_token && $this->id === $default_token->get_gateway_id() ) {
					$source = $default_token->get_token();
					$token  = $default_token;
				}
			}

			// if we have no source at this point, we cannot proceed with payment; skip.
			if ( ! $source ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, we couldn\'t find any valid payment method for your renewal.', 'yith-woocommerce-stripe' ) );
			}

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			try {
				$intent = $this->api->create_intent(
					array(
						'amount'               => YITH_WCStripe::get_amount( $order->get_total() ),
						'currency'             => $currency,
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
						'description'          => apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),
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
						'metadata'             => apply_filters(
							'yith_wcstripe_metadata',
							array(
								'order_id'    => $order_id,
								'order_email' => $order->get_billing_email(),
								'instance'    => $this->instance,
							),
							'charge'
						),
						'customer'             => $customer_id,
						'payment_method_types' => array( 'card' ),
						'payment_method'       => $source,
						'off_session'          => true,
						'confirm'              => true,
						/**
						 * APPLY_FILTERS: yith_wcstripe_renew_capture_method
						 *
						 * Filters Subscription renew capture method in Stripe.
						 *
						 * @param string                       Default method 'automatic'.
						 * @param WC_Order                     Order object.
						 * @param string                       Stripe capture parameter.
						 * @param YITH_WCStripe_Gateway_Addons YITH_WCStripe_Gateway_Addons class instance.
						 *
						 * @return string
						 */
						'capture_method'       => apply_filters( 'yith_wcstripe_renew_capture_method', 'automatic', $order, $this->capture, $this ),
						'return_url'           => $this->get_verification_url( $order ?? null ),
					)
				);
			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$body = $e->getJsonBody();
				$err  = $body['error'];

				if (
					isset( $err['payment_intent'] ) &&
					isset( $err['payment_intent']['status'] ) &&
					in_array( $err['payment_intent']['status'], array( 'requires_action', 'requires_payment_method' ), true ) &&
					(
						! empty( $err['payment_intent']['next_action'] ) && isset( $err['payment_intent']['next_action']->type ) && 'use_stripe_sdk' === $err['payment_intent']['next_action']->type ||
						'authentication_required' === $err['code']
					)
				) {
					$this->renew_needs_action = true;

					if ( isset( $token ) ) {
						$token->update_meta_data( 'confirmed', false );
						$token->save();
					}

					return new WP_Error( 'stripe_error', __( 'Please, validate your payment method before proceeding any further.', 'yith-woocommerce-stripe' ) );
				} else {
					return new WP_Error( 'stripe_error', $err['message'] );
				}
			} catch ( Exception $e ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			}

			// intent has failed; return error message to customer.
			if ( ! is_a( $intent, 'Stripe\PaymentIntent' ) && is_array( $intent ) && isset( $intent['error'] ) ) {
				// translators: 1. Renew ID.
				$error_message = sprintf( __( 'Error while processing renew payment: %s.', 'yith-woocommerce-stripe' ), $intent['error']['message'] );

				return new WP_Error( 'stripe_error', $error_message );
			}

			// check intent confirmation.
			if ( ! $intent ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			} elseif ( 'requires_action' === $intent->status ) {
				$this->renew_needs_action = true;

				if ( isset( $token ) ) {
					$token->update_meta_data( 'confirmed', false );
					$token->save();
				}

				return new WP_Error( 'stripe_error', __( 'Please, validate your payment method before proceeding any further.', 'yith-woocommerce-stripe' ) );
			} elseif ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
				return new WP_Error( 'stripe_error', __( 'Sorry, there was an error while processing payment; please, try again.', 'yith-woocommerce-stripe' ) );
			}

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $this->api->get_charge( $charge );

			// payment complete.
			$order->payment_complete( $charge->id );

			// add order note.
			// translators: 1. Stripe charge id.
			$order->add_order_note( sprintf( __( 'Stripe payment approved (ID: %s)', 'yith-woocommerce-stripe' ), $charge->id ) );

			// update order meta.
			$order->update_meta_data( '_captured', $charge->captured ? 'yes' : 'no' );
			$order->update_meta_data( '_stripe_customer_id', $customer_id );
			$order->save();

			// save subscription meta.
			if ( $subscription ) {
				$subscription->set( 'stripe_customer_id', $customer_id );
				$subscription->set( 'yith_stripe_token', $source );

				method_exists( $subscription, 'save' ) && $subscription->save();
			}

			// Return thank you page redirect.
			return true;
		}

		/* === HELPER METHODS === */

		/**
		 * Check if current renew order has an active subscription on Stripe side
		 *
		 * @param int $subscription_id Subscription id.
		 *
		 * @return bool Whether or not a subscription was found on Stripe
		 */
		public function has_active_subscription( $subscription_id ) {
			$subscription = ywsbs_get_subscription( (int) $subscription_id );

			if ( empty( $subscription ) ) {
				return false;
			}

			$order_id               = $subscription->order_id;
			$stripe_subscription_id = $subscription->stripe_subscription_id;

			if ( $stripe_subscription_id ) {
				return true;
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return false;
			}

			// Initialize SDK and set private key.
			$this->init_stripe_sdk();

			$plan = $this->get_plan( $subscription, $order, false );

			if ( ! $plan ) {
				return false;
			}

			$customer_id = false;

			if ( $subscription_id ) {
				$customer_id = $subscription->stripe_customer_id;
			}

			$user_id = $order->get_user_id();

			if ( ! $customer_id && $user_id ) {
				$customer    = YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );
				$customer_id = isset( $customer['id'] ) ? $customer['id'] : false;
			}

			if ( ! $customer_id ) {
				return false;
			}

			$stripe_subscriptions = $this->api->get_subscriptions(
				$customer_id,
				array(
					'plan'   => $plan,
					'status' => 'active',
					'limit'  => 99,
				)
			);

			if ( ! $stripe_subscriptions ) {
				return false;
			}

			foreach ( $stripe_subscriptions as $subscription ) {
				if ( isset( $subscription->metadata ) && isset( $subscription->metadata->subscription_id ) && (int) $subscription->metadata->subscription_id === $subscription_id ) {
					// subscription found among Stripe's subscriptions.
					return true;
				}
			}

			// we couldn't locate any Stripe Subscription.
			return false;
		}

		/**
		 * Get subscription ID by stripe subscription id
		 *
		 * @param string $stripe_subscription_id Stripe subscription id.
		 * @return int|bool Subscription id, or false on failure
		 */
		public function get_subscription_id( $stripe_subscription_id ) {
			if ( class_exists( 'YITH\Subscription\Main' ) ) {
				$subscriptions = YITH\Subscription\Main::get_instance()->get_db_table( 'subscription-meta' )->read(
					array(
						'meta_key'   => 'stripe_subscription_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => $stripe_subscription_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					)
				);

				$ids = wp_list_pluck( $subscriptions, 'subscription_id' );

				return array_shift( $ids );
			} else {
				global $wpdb;

				// TODO: chances of doing this with get_posts? Why there is no meta_key condition?
				return $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT post_id
						FROM {$wpdb->postmeta} pm
						INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_type = %s
						WHERE pm.meta_value = %s
						ORDER BY pm.post_id DESC LIMIT 1",
						'ywsbs_subscription',
						$stripe_subscription_id
					)
				);
			}
		}

		/**
		 * Get subscription ID by one of his orders
		 *
		 * @param int $order_id Order id.
		 *
		 * @return int|bool Subscription id; false if no subscription is found
		 */
		public function get_subscription_id_by_order( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order instanceof WC_Order ) {
				return false;
			}

			$subscriptions = $order->get_meta( 'subscriptions' );

			if ( ! $subscriptions ) {
				return false;
			}

			$subscription_id = array_pop( $subscriptions );

			/**
			 * APPLY_FILTERS: yith_wcstripe_subscription_from_order
			 *
			 * Filters Subscription ID from order ID.
			 *
			 * @param int $subscription_id The subscription ID.
			 * @param int $order_id        The order ID.
			 *
			 * @return int
			 */
			return apply_filters( 'yith_wcstripe_subscription_from_order', $subscription_id, $order_id );
		}

		/**
		 * Retrieve the plan.
		 *
		 * If it doesn't exist, create a new one and returns it.
		 *
		 * @param YWSBS_Subscription $subscription         Subscription object.
		 * @param \WC_Order          $order                Related order.
		 * @param bool               $create_if_not_exists Create plan if it doesn't exists.
		 *
		 * @return \Stripe\Plan|bool Returns a plan if it finds one; otherwise return false $create_if_not_exists = false, or a brand new plan if it is true
		 */
		public function get_plan( $subscription, $order = null, $create_if_not_exists = true ) {
			// retrieve related product.
			$object_id = ! empty( $subscription->variation_id ) ? $subscription->variation_id : $subscription->product_id;
			$product   = wc_get_product( $object_id );
			$order     = $order ? $order : $this->current_order;

			if ( ! $product || ! $product instanceof WC_Product ) {
				return;
			}

			// calculate the amount of the subscription plan.
			/**
			 * APPLY_FILTERS: yith_wcstripe_subscription_amount
			 *
			 * Filters Subscription plan total amount.
			 *
			 * @param int                $subscription_total Subscription total amount.
			 * @param YWSBS_Subscription $subscription       Subscription object.
			 * @param WC_Order           $order              Order object.
			 * @param WC_Product         $product            Related product.
			 *
			 * @return int
			 */
			$plan_amount = apply_filters( 'yith_wcstripe_subscription_amount', $subscription->subscription_total, $subscription, $order, $product );
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
			$plan_amount = apply_filters( 'yith_wcstripe_gateway_amount', $plan_amount, $order );

			// translate the option saved on subscription options of product to values requested by stripe.
			$interval_periods = array(
				'days'   => 'day',
				'weeks'  => 'week',
				'months' => 'month',
				'years'  => 'year',
			);

			// calculate trial days.
			$interval          = str_replace( array_keys( $interval_periods ), array_values( $interval_periods ), $product->get_meta( '_ywsbs_price_time_option' ) );
			$interval_count    = intval( $product->get_meta( '_ywsbs_price_is_per' ) );
			$trial_period      = $product->get_meta( '_ywsbs_trial_per' );
			$trial_time_option = $product->get_meta( '_ywsbs_trial_time_option' );

			if ( ! empty( $trial_period ) && in_array( $trial_time_option, array( 'days', 'weeks', 'months', 'years' ), true ) ) {
				$trial_end = strtotime( "+{$trial_period} {$trial_time_option}" );
				$trial     = ( $trial_end - time() ) / DAY_IN_SECONDS;
			} else {
				$trial_end = time();
				$trial     = 0;
			}

			/**
			 * APPLY_FILTERS: yith_wcstripe_plan_trial_period
			 *
			 * Filters the subscription trial period days.
			 *
			 * @param int                              Trial time.
			 * @param YWSBS_Subscription $subscription Subscription object.
			 * @param int                $trial_end    Trial end timestamp.
			 * @param WC_Order           $order        Order object.
			 *
			 * @return int
			 */
			$trial_period_days = apply_filters( 'yith_wcstripe_plan_trial_period', intval( $trial ), $subscription, $trial_end, $order );

			// hash used to prevent differences between subscription configuration.
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
			$hash = md5( $plan_amount . $interval . $interval_count . $trial_period_days . apply_filters( 'yith_wcstripe_gateway_currency', $subscription->order_currency, $subscription->order_id ) );

			// get plan if exists.
			$plan_id = "product_{$object_id}_{$hash}";
			$plan    = $this->api->get_plan( $plan_id );

			// if some parameter is changed with save plan, delete it to recreate it.
			if ( $plan ) {
				return $plan;
			}

			if ( ! $create_if_not_exists ) {
				return false;
			}

			// retrieve order currency.
			$currency = $this->get_currency( $order );

			// format the name of plan.
			$product_name = wp_strip_all_tags( html_entity_decode( $product->get_title() ) );
			$plan_name    = '';

			if ( 'variation' === $product->get_type() ) {
				/**
				 * Product variation object.
				 *
				 * @var $product \WC_Product_Variation
				 */
				$plan_name .= wc_get_formatted_variation( $product, true );
				$plan_name .= ' - ';
			}

			$formatted_interval = 1 === $interval_count ? $interval : $interval_count . ' ' . $interval_periods[ $interval ];
			$plan_name         .= sprintf( '%s / %s', wc_price( $plan_amount, array( 'currency' => $currency ) ), $formatted_interval );

			if ( $trial_period_days ) {
				// translators: 1. number of trial days.
				$plan_name .= sprintf( __( ' - %s days trial', 'yith-woocommerce-stripe' ), $trial_period_days );
			}

			$plan_name = wp_strip_all_tags( html_entity_decode( $plan_name ) );

			// retrieve product, if it already exists: otherwise it will created with the plan.
			$product_id = "product_{$subscription->product_id}";

			try {
				$stripe_product = $this->api->get_product( $product_id );
			} catch ( Exception $e ) {
				$stripe_product = false;
			}

			// if it doesn't exist, create it.
			$plan = $this->api->create_plan(
				array(
					'id'                => $plan_id,
					'product'           => $stripe_product ? $stripe_product->id : array(
						'id'   => $product_id,
						'name' => substr( $product_name, 0, 250 ),
					),
					'nickname'          => $plan_name,
					'currency'          => strtolower( $currency ),
					'interval'          => $interval,
					'interval_count'    => $interval_count,
					'amount'            => YITH_WCStripe::get_amount( $plan_amount, $currency ),
					'trial_period_days' => $trial_period_days,
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
					'metadata'          => apply_filters(
						'yith_wcstripe_metadata',
						array(
							'product_id' => $object_id,
						),
						'create_plan'
					),
				)
			);

			return $plan;
		}

		/**
		 * Register failed renew attempt for an order, and related error message
		 *
		 * @param \WC_Order $order   Renew order.
		 * @param string    $message Error message to log.
		 *
		 * @return void
		 */
		public function register_failed_renew( $order, $message ) {
			if ( $this->register_failed_attempt ) {
				ywsbs_register_failed_payment( $order, $message );

				/**
				 * Required in order to make sure that the order object is up to date after
				 * subscription register failed attempt
				 */
				$order = wc_get_order( $order->get_id() );

				if ( $this->renew_needs_action && ! $order->has_status( 'cancelled' ) ) {
					// set specific meta.
					$order->update_meta_data( 'yith_wcstripe_card_requires_action', 'yes' );
					$order->save();

					/**
					 * DO_ACTION: yith_wcstripe_renew_intent_requires_action
					 *
					 * Triggered when subscription renew requires action.
					 *
					 * @param \WC_Order $order Renew order.
					 */
					do_action( 'yith_wcstripe_renew_intent_requires_action', $order );
				}
			}

			$this->log( $message );
		}

		/**
		 * Check if order contains subscriptions.
		 *
		 * @param int $order_id Order id to check.
		 *
		 * @return bool
		 */
		protected function order_contains_subscription( $order_id ) {
			return function_exists( 'YITH_WC_Subscription' ) && YITH_WC_Subscription()->order_has_subscription( $order_id );
		}
	}
}
