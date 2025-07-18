<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Webhook class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Webhook' ) ) {
	/**
	 * Manage webhooks of stripe
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Webhook {

		/**
		 * Current stripe event
		 *
		 * @var \Stripe\Event
		 */
		protected static $event = null;

		/**
		 * Gateway object
		 *
		 * @var YITH_WCStripe_Gateway
		 */
		protected static $gateway = null;

		/**
		 * Avoid performing a webhook if already runned
		 *
		 * @var bool
		 */
		protected static $running = false;

		/**
		 * Constructor.
		 *
		 * Route the webhook to the own method
		 *
		 * @return \YITH_WCStripe_Webhook
		 * @since 1.0.0
		 */
		public static function route() {
			if ( self::$running ) {
				return;
			}

			self::$running = true;

			$body        = @file_get_contents( 'php://input' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			self::$event = json_decode( $body );

			// retrieve the callback to use fo this event.
			$callback = isset( self::$event->type ) ? str_replace( '.', '_', self::$event->type ) : '';

			if ( ! $callback || ! method_exists( __CLASS__, $callback ) ) {
				// translators: 1. Method invoked.
				self::send_success( sprintf( __( 'No action to perform with this event (method invoked is: %s).', 'yith-woocommerce-stripe' ), $callback ) );
			}

			self::$gateway = YITH_WCStripe()->get_gateway();

			if ( ! self::$gateway ) {
				self::send_success( __( 'No gateway.', 'yith-woocommerce-stripe' ) );
			}

			self::$gateway->init_stripe_sdk();

			try {
				// call the method event.
				call_user_func( array( __CLASS__, $callback ) );
				self::send_success( __( 'Webhook performed without error.', 'yith-woocommerce-stripe' ) );
			} catch ( Stripe\Exception\ApiErrorException $e ) {
				self::$gateway->log( 'Charge updated: ' . $e->getMessage() );
				self::send_error( var_export( $e->getJsonBody(), true ) . "\n\n" . $e->getTraceAsString() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
			} catch ( Exception $e ) {
				self::$gateway->log( 'Charge updated: ' . $e->getMessage() );
				self::send_error( $e->getCode() . ': ' . $e->getMessage() . "\n\n" . $e->getTraceAsString() );
			}
		}

		/**
		 * Handle the captured charge
		 *
		 * @var $charge \Stripe\Charge
		 * @since 1.0.0
		 */
		protected static function charge_captured() {
			$charge  = self::$event->data->object;
			$gateway = self::$gateway;

			// validate instance.
			self::validate_instance( $charge );

			// get order.
			if ( ! isset( $charge->metadata->order_id ) || empty( $charge->metadata->order_id ) ) {
				self::send_success( 'No order ID set' );
			}

			$order_id = $charge->metadata->order_id;
			$order    = wc_get_order( $charge->metadata->order_id );

			if ( false === $order ) {
				self::send_success( 'No order for this event' );
			}

			$order->update_meta_data( '_captured', 'yes' );
			$order->save();

			// check if refunds.
			$refunds = $gateway->api->get_refunds( $charge->id );

			if ( $refunds ) {
				$amount_captured = YITH_WCStripe::get_original_amount( $charge->amount - $charge->amount_refunded );

				/**
				 * Each of order refunds.
				 *
				 * @var $refund \Stripe\Refund
				 */
				foreach ( $refunds as $refund ) {
					$amount_refunded = YITH_WCStripe::get_original_amount( $refund->amount, $refund->currency );

					try {
						// add refund to order.
						$order_refund = wc_create_refund(
							array(
								'amount'   => $amount_refunded,
								// translators: 1. Amount captured via Stripe.
								'reason'   => sprintf( __( 'Captured only %1$s via Stripe. %2$s', 'yith-woocommerce-stripe' ), wp_strip_all_tags( wc_price( $amount_captured ) ), ! empty( $refund->reason ) ? "<br/>{$refund->reason}" : '' ),
								'order_id' => $order_id,
							)
						);

						$order_refund->update_meta_data( '_refund_stripe_id', $refund->id );
						$order_refund->save();
					} catch ( Exception $e ) {
						continue;
					}
				}
			}

			// complete order.
			$order->update_status( 'completed', __( 'Charge captured via Stripe account.', 'yith-woocommerce-stripe' ) );
		}

		/**
		 * Handle the refunded charge
		 *
		 * @since 1.0.0
		 */
		protected static function charge_refunded() {
			$charge  = self::$event->data->object;
			$gateway = self::$gateway;

			// validate instance.
			self::validate_instance( $charge );

			// get order.
			if ( ! isset( $charge->metadata->order_id ) || empty( $charge->metadata->order_id ) ) {
				self::send_success( 'No order ID set' );
			}

			$order    = wc_get_order( $charge->metadata->order_id );
			$order_id = $charge->metadata->order_id;

			if ( false === $order ) {
				self::send_success( 'No order for this event' );
			}

			// retrieve order refunds.
			$order_refunds = $order->get_refunds();

			// If already captured, set as refund.
			if ( $charge->captured ) {
				$order->update_meta_data( '_captured', 'yes' );
				$order->save();

				// check if refunds.
				$refunds = $gateway->api->get_refunds( $charge->id );

				if ( $refunds ) {
					foreach ( $refunds as $stripe_refund ) {
						$amount_refunded = YITH_WCStripe::get_original_amount( $stripe_refund->amount, $stripe_refund->currency );

						foreach ( $order_refunds as $order_refund ) {
							if ( in_array( $stripe_refund->id, (array) $order_refund->get_meta( '_refund_stripe_id' ), true ) ) {
								continue 2;
							}
						}

						// add refund to order.
						try {
							$order_refund = wc_create_refund(
								array(
									'amount'   => YITH_WCStripe::get_original_amount( $stripe_refund->amount, $stripe_refund->currency ),
									'reason'   => __( 'Refunded via Stripe.', 'yith-woocommerce-stripe' ) . ( ! empty( $stripe_refund->reason ) ? $stripe_refund->reason : '' ),
									'order_id' => $order_id,
								)
							);

							// translators: 1. Amount refunded. 2. Refund id.
							$order->add_order_note( sprintf( __( 'Refunded %1$s - Refund ID: %2$s', 'woocommerce' ), $amount_refunded, $stripe_refund->id ) );

							// set metadata.
							$order_refund->update_meta_data( '_refund_stripe_id', $stripe_refund->id );
							$order_refund->save();
						} catch ( Exception $e ) {
							continue;
						}
					}

					// refund order if is fully refunded.
					if ( (float) $charge->amount === (float) $charge->amount_refunded ) {
						$order->update_status( 'refunded' );
					}
				}
			} else {
				// if isn't captured yet, set as cancelled.
				$order->update_meta_data( '_captured', 'no' );

				// set cancelled.
				$order->update_status( 'cancelled', __( 'Authorization released via Stripe.', 'yith-woocommerce-stripe' ) );
			}
		}

		/**
		 * Handle dispute created event
		 *
		 * @since 1.6.0
		 */
		protected static function charge_dispute_created() {
			global $wpdb;

			$dispute = self::$event->data->object;
			$gateway = self::$gateway;

			$charge_id = $dispute->charge;

			if ( empty( $charge_id ) ) {
				self::send_success( 'No charge ID in the request' );
			}

			try {
				$charge = $gateway->api->get_charge( $charge_id );
			} catch ( Exception $e ) {
				self::send_success( 'Could not retrieve charge ' . $charge_id );
			}

			// validate instance.
			self::validate_instance( $charge );

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$query = "SELECT order_id FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s AND meta_value = %s";
			} else {
				$query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s";
			}

			$query_args = array(
				'_transaction_id',
				$charge_id,
			);

			$order_id = $wpdb->get_var( $wpdb->prepare( $query, $query_args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			if ( ! $order_id ) {
				self::send_success( 'No order ID found for the charge ID' );
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				self::send_success( 'No order ID for this event' );
			}

			$order->update_status( 'on-hold', __( 'Payment reversed via Stripe.', 'yith-woocommerce-stripe' ) );
		}

		/**
		 * Handle dispute closed event
		 *
		 * @since 1.6.0
		 */
		protected static function charge_dispute_closed() {
			global $wpdb;

			$dispute = self::$event->data->object;
			$gateway = self::$gateway;

			$charge_id = $dispute->charge;
			$status    = $dispute->status;

			try {
				$charge = $gateway->api->get_charge( $charge_id );
			} catch ( Exception $e ) {
				self::send_success( 'Could not retrieve charge ' . $charge_id );
			}

			// validate instance.
			self::validate_instance( $charge );

			if ( ! in_array( $status, array( 'won', 'lost' ), true ) ) {
				self::send_success( 'No processable dispute status in the request' );

			}

			if ( empty( $charge_id ) ) {
				self::send_success( 'No charge ID in the request' );

			}

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$query = "SELECT order_id FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s AND meta_value = %s";
			} else {
				$query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s";
			}

			$query_args = array(
				'_transaction_id',
				$charge_id,
			);

			$order_id = $wpdb->get_var( $wpdb->prepare( $query, $query_args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			if ( ! $order_id ) {
				self::send_success( 'No order ID found for the charge ID' );
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				self::send_success( 'No order ID for this event' );
			}

			if ( 'won' === $status ) {
				$order->update_status( 'completed', __( 'Payment completed after winning dispute.', 'yith-woocommerce-stripe' ) );
			} elseif ( 'lost' === $status ) {
				$order->update_status( 'refunded', __( 'Payment refunded after losing dispute.', 'yith-woocommerce-stripe' ) );
			}
		}

		/**
		 * Handle the change of customer data
		 *
		 * @since 1.0.0
		 */
		protected static function customer_updated() {
			$customer = self::$event->data->object;

			self::update_customer( $customer );
		}

		/**
		 * Handle the change of customer data
		 *
		 * @since 1.0.0
		 */
		protected static function customer_source_created() {
			$card = self::$event->data->object;

			self::update_customer( $card->customer );
		}

		/**
		 * Handle the change of customer data
		 *
		 * @since 1.0.0
		 */
		protected static function customer_source_updated() {
			$card = self::$event->data->object;

			self::update_customer( $card->customer );
		}

		/**
		 * Handle the change of customer data
		 *
		 * @since 1.0.0
		 */
		protected static function customer_source_deleted() {
			$card = self::$event->data->object;

			self::update_customer( $card->customer );
		}

		/**
		 * Subscription recurring payed success
		 *
		 * @param Stripe\Invoice|bool $invoice Optional invoice object.
		 */
		protected static function invoice_payment_succeeded( $invoice = false ) {
			/**
			 * Event contains invoice object
			 * We use it only when it is not provided as parameter
			 *
			 * @var Stripe\Invoice $invoice
			 */
			$manual  = (bool) $invoice;
			$invoice = $invoice ? $invoice : self::$event->data->object;
			$gateway = self::$gateway;

			if ( ! $gateway instanceof YITH_WCStripe_Gateway_Addons ) {
				self::send_success( 'Subscriptions disabled' );
			}

			// get subscription line from invoice.
			foreach ( $invoice->lines->data as $line ) {
				if ( 'subscription' === $line->type ) {
					$stripe_subscription_line_obj = $line;
					break;
				}
			}

			if ( empty( $stripe_subscription_line_obj ) ) {
				self::send_success( 'No subscriptions for this event.' );
			}

			// validate instance.
			self::validate_instance( $stripe_subscription_line_obj );

			// amount_due == 0 to avoid duplication on.
			if ( ! $manual && ( empty( (float) $invoice->amount_due ) || ! property_exists( $invoice, 'subscription' ) || ! property_exists( $invoice, 'paid' ) || true !== $invoice->paid || ! property_exists( $invoice, 'charge' ) ) ) {
				self::send_success( 'Duplication' );
			}

			$stripe_subscription_id = $invoice->subscription;
			$subscription_id        = $gateway->get_subscription_id( $stripe_subscription_id );

			if ( empty( $subscription_id ) ) {
				self::send_success( 'No subscription ID on website' );
			}

			$subscription       = ywsbs_get_subscription( (int) $subscription_id );
			$invoices_processed = isset( $subscription->stripe_invoices_processed ) ? $subscription->stripe_invoices_processed : array();

			if ( in_array( $invoice->id, $invoices_processed, true ) ) {
				self::send_success( 'Invoice already processed.' );
			}

			$order       = wc_get_order( $subscription->order_id );
			$customer_id = $invoice->customer;
			$user        = $order->get_user();

			if ( 'cancelled' === $subscription->status ) {
				$msg = 'YSBS - Webhook stripe subscription payment error #' . $subscription_id . ' is cancelled';
				$gateway->log( $msg );
				self::send_success( $msg );
			}

			$pending_order = $subscription->renew_order;
			$last_order    = $pending_order ? wc_get_order( intval( $pending_order ) ) : false;

			if ( $last_order ) {
				$order_id = $pending_order;
				$order    = $last_order;

			} else {
				// if the renew_order is not created try to create it.
				try {
					$order_id = YWSBS_Subscription_Order()->renew_order( $subscription->id );
					$order    = wc_get_order( $order_id );
				} catch ( Exception $e ) {
					self::send_success( 'Couldn\'t create renew order for subscription ' . $subscription_id );
				}
			}

			$metadata = array(
				'metadata'    => array(
					'order_id' => $order_id,
					'instance' => $gateway->instance,
				),
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
				// translators: 1. Blog name. 2 Order id.
				'description' => apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-stripe' ), esc_html( get_bloginfo( 'name' ) ), $order_id ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ),
			);

			try {
				$charge = $gateway->api->update_charge( $invoice->charge, $metadata );
			} catch ( Exception $e ) {
				$charge = false;
			}

			if ( $charge && isset( $charge->payment_intent ) ) {
				$gateway->api->update_intent( $charge->payment_intent, $metadata );
			}

			// check if it will be expired on next renew.
			if ( ! empty( $subscription->expired_date ) && ( $invoice->period_end >= $subscription->expired_date || $invoice->period_end + DAY_IN_SECONDS > $subscription->expired_date ) ) {
				try {
					$gateway->api->cancel_subscription( $customer_id, $stripe_subscription_id );
				} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// do nothing.
				}
			}

			$order->update_meta_data( 'Subscriber ID', $customer_id );
			$order->update_meta_data( 'Subscriber payment type', $gateway->id );
			$order->update_meta_data( 'Stripe Subscribtion ID', $stripe_subscription_id );
			$order->update_meta_data( '_captured', 'yes' );
			$order->update_meta_data( 'next_payment_attempt', $invoice->next_payment_attempt );

			if ( $user ) {
				$order->update_meta_data( 'Subscriber address', $user->billing_email );
				$order->update_meta_data( 'Subscriber first name', $user->first_name );
				$order->update_meta_data( 'Subscriber last name', $user->last_name );
			}

			// filter to increase performance during "payment_complete" action.
			add_filter( 'woocommerce_delete_version_transients_limit', 'yith_wcstripe_return_10' );

			$invoices_processed[] = $invoice->id;
			$subscription->set( 'stripe_invoices_processed', $invoices_processed );
			$subscription->set( 'stripe_charge_id', $invoice->charge );
			$subscription->set( 'payment_method', $gateway->id );
			$subscription->set( 'payment_method_title', $gateway->get_title() );

			// remove the invoice from failed invoices list, if it exists.
			$failed_invoices = get_user_meta( $order->get_user_id(), 'failed_invoices', true );

			if ( isset( $failed_invoices[ $subscription->id ] ) ) {
				unset( $failed_invoices[ $subscription->id ] );
				update_user_meta( $order->get_user_id(), 'failed_invoices', $failed_invoices );
			}

			// add a user meta to show him a success notice for renew.
			add_user_meta( $order->get_user_id(), 'invoice_charged', true );

			try {
				$order->add_order_note( __( 'Stripe subscription payment completed.', 'yith-woocommerce-stripe' ) );
				$order->set_payment_method( $gateway );
				$order->payment_complete( $invoice->charge );
			} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// do nothing.
			}

			$order->save();

			// must be after payment_complete, because subscription plugin add the period to payment_due_date, on payment_complete.
			$subscription->set( 'payment_due_date', $stripe_subscription_line_obj->period->end );

			method_exists( $subscription, 'save' ) && $subscription->save();
		}

		/**
		 * Subscription recurring payed failed
		 */
		protected static function invoice_payment_failed() {
			/**
			 * Event contains invoice object
			 *
			 * @var Stripe\Invoice $invoice
			 */
			$invoice = self::$event->data->object;
			$gateway = self::$gateway;

			if ( ! $gateway instanceof YITH_WCStripe_Gateway_Addons ) {
				self::send_success( 'Subscriptions disabled' );
			}

			// get subscription line from invoice.
			foreach ( $invoice->lines->data as $line ) {
				if ( 'subscription' === $line->type ) {
					$stripe_subscription_line_obj = $line;
					break;
				}
			}

			if ( empty( $stripe_subscription_line_obj ) ) {
				self::send_success( 'No subscriptions for this event.' );
			}

			// validate instance.
			self::validate_instance( $stripe_subscription_line_obj );

			$stripe_subscription_id = $invoice->subscription;
			$subscription_id        = $gateway->get_subscription_id( $stripe_subscription_id );

			if ( empty( $subscription_id ) ) {
				self::send_success( 'No subscription ID on website' );
			}

			$subscription = ywsbs_get_subscription( (int) $subscription_id );

			if ( empty( $subscription ) ) {
				self::send_success( 'No subscription on website' );
			}

			$order_id       = $subscription->order_id;
			$order          = wc_get_order( $order_id );
			$renew_order_id = $subscription->renew_order;
			$renew_order    = wc_get_order( $renew_order_id );
			$customer_id    = $order->get_customer_id();
			$retry_renew    = 'yes' === $gateway->get_option( 'retry_with_other_cards' );

			// if we cannot find any renew order, subscription was already processed.
			if ( ! $renew_order ) {
				self::send_success( 'No renew order; subscription was already processed' );
			}

			// check if we're currently processing other cards (in this case should ignore failure webhooks).
			$cards_to_test = $renew_order->get_meta( '_cards_to_test' );

			// before registering fail, try to pay with other registered cards.
			if ( ! is_array( $cards_to_test ) && $customer_id && $retry_renew ) {
				$cards_to_test = array();

				$customer_tokens = WC_Payment_Tokens::get_customer_tokens( $customer_id, YITH_WCStripe::$gateway_id );

				$current_year  = gmdate( 'Y' );
				$current_month = gmdate( 'm' );

				if ( count( $customer_tokens ) > 1 ) {
					foreach ( $customer_tokens as $customer_token ) {
						$card_id   = $customer_token->get_token();
						$exp_year  = $customer_token->get_expiry_year();
						$exp_month = $customer_token->get_expiry_month();

						if ( ! $card_id ) {
							continue;
						}

						if ( $exp_year < $current_year || ( (int) $exp_year === (int) $current_year && $exp_month < $current_month ) ) {
							continue;
						}

						$cards_to_test[] = $card_id;
					}
				}

				if ( ! empty( $cards_to_test ) ) {
					$renew_order->update_meta_data( '_cards_to_test', $cards_to_test );
					$renew_order->save();
				}
			}

			// backward compatibility before YITH Subscription  1.1.3.
			if ( method_exists( $subscription, 'register_failed_attemp' ) ) {
				$subscription->register_failed_attempt( $invoice->attempt_count );
			} else {
				$order->update_meta_data( 'failed_attemps', $invoice->attempt_count );
				// translators: 1. Order id.
				YITH_WC_Activity()->add_activity( $subscription_id, 'failed-payment', 'success', $order_id, sprintf( __( 'Failed payment for order %d', 'yith-woocommerce-stripe' ), $order_id ) );
			}

			// suspend subscription.
			/**
			 * APPLY_FILTERS: ywsbs_suspend_for_failed_recurring_payment
			 *
			 * Filters if should suspend subscription due to recurring payment fail.
			 *
			 * @param string Value coming from plugin options.
			 *
			 * @return string
			 */
			$suspend_subscription = apply_filters( 'ywsbs_suspend_for_failed_recurring_payment', get_option( 'ywsbs_suspend_for_failed_recurring_payment', 'no' ) );
			if ( 'yes' === $suspend_subscription ) {
				$subscription->update_status( 'suspended', 'yith-stripe' );
			}

			// register next attempt date.
			$order->update_meta_data( 'next_payment_attempt', $invoice->next_payment_attempt );
			$order->save();

			if ( $renew_order ) {
				$renew_order->add_order_note( __( 'YSBS - Webhook Failed payment.', 'yith-woocommerce-stripe' ) );
			} else {
				$order->add_order_note( __( 'YSBS - Webhook Failed payment.', 'yith-woocommerce-stripe' ) );
			}

			// save a user meta to notify the failure on my account page.
			$failed_invoices = get_user_meta( $order->get_user_id(), 'failed_invoices', true );
			$failed_invoices = is_array( $failed_invoices ) ? $failed_invoices : array();

			if ( ! isset( $failed_invoices[ $subscription->id ] ) ) {
				$failed_invoices[ $subscription->id ] = $invoice->id;
				update_user_meta( $order->get_user_id(), 'failed_invoices', $failed_invoices );
			}

			// Subscription Cancellation Completed.
			$gateway->log( 'YSBS - Webhook stripe subscription failed payment ' . $order_id );

			// retry payment using other customer cards; failure or success will be handled by subsequent webhooks sent from Stripe.
			if ( is_array( $cards_to_test ) ) {
				if ( empty( $cards_to_test ) ) {
					delete_post_meta( $renew_order_id, '_cards_to_test' );
				} else {
					$card_id = array_shift( $cards_to_test );

					try {
						$invoice = $gateway->api->get_invoice( $invoice->id );
					} catch ( Exception $e ) {
						self::send_success( 'Invoice failed.' );
					}

					// remove card from queue.
					$renew_order->update_meta_data( '_cards_to_test', $cards_to_test );
					$renew_order->save();

					try {
						$result = $invoice->pay(
							array(
								'off_session' => true,
								'source'      => $card_id,
							)
						);

						if ( (float) $result->amount_due === (float) $result->amount_paid ) {
							delete_post_meta( $renew_order_id, '_cards_to_test' );
							self::send_success( "Invoice failed. New payment attempted with card {$card_id}: success!" );
						}
					} catch ( Exception $e ) {
						self::send_success( "Invoice failed. New payment attempted with card {$card_id}: fail!" );
					}
				}
			} else {
				self::send_success( 'Invoice failed.' );
			}
		}

		/**
		 * Subscription deleted
		 */
		protected static function customer_subscription_deleted() {
			$stripe_subscription = self::$event->data->object;
			$gateway             = self::$gateway;

			if ( ! $gateway instanceof YITH_WCStripe_Gateway_Addons ) {
				self::send_success( 'Subscriptions disabled' );
			}

			// validate instance.
			self::validate_instance( $stripe_subscription );

			// remove subscription on WordPress site.
			$subscription_id = $gateway->get_subscription_id( $stripe_subscription->id );

			if ( empty( $subscription_id ) ) {
				return;
			}

			$subscription = ywsbs_get_subscription( (int) $subscription_id );
			if ( 'cancelled' !== $subscription->status ) {
				$subscription->cancel();
			}

			// remove the invoice from failed invoices list, if it exists.
			$order           = wc_get_order( $subscription->order_id );
			$failed_invoices = get_user_meta( $order->get_user_id(), 'failed_invoices', true );
			if ( isset( $failed_invoices[ $subscription->id ] ) ) {
				unset( $failed_invoices[ $subscription->id ] );
				update_user_meta( $order->get_user_id(), 'failed_invoices', $failed_invoices );
			}
		}

		/**
		 * Util method for customer update.
		 *
		 * Get profile data from stripe and update in the database
		 *
		 * @param string|Stripe\Customer $customer The ID of customer or customer object.
		 *
		 * @since 1.0.0
		 */
		protected static function update_customer( $customer ) {
			$gateway = self::$gateway;

			// retrieve customer from stripe profile.
			$gateway->init_stripe_sdk();

			if ( is_string( $customer ) ) {
				try {
					$customer = $gateway->api->get_customer( $customer );
				} catch ( Exception $e ) {
					self::send_success( 'Couldn\'t retrieve customer' );
				}
			}

			// exit if no user id found.
			if ( empty( $customer->metadata->user_id ) ) {
				self::send_success( 'No user for this event' );
			}

			// validate instance.
			self::validate_instance( $customer );

			// update tokens.
			if ( method_exists( $gateway, 'sync_tokens' ) ) {
				$gateway->sync_tokens( $customer->metadata->user_id, $customer );
			}

			// back-compatibility.
			YITH_WCStripe()->get_customer()->update_usermeta_info(
				$customer->metadata->user_id,
				array(
					'id'             => $customer->id,
					'cards'          => isset( $customer->sources ) ? $customer->sources->data : array(),
					'default_source' => $customer->default_source,
				)
			);
		}

		/**
		 * Validates instance from the request; if instance do not match, end execution with a proper message
		 *
		 * @param mixed $object Request object.
		 */
		protected static function validate_instance( $object ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.objectFound
			$gateway = self::$gateway;

			if ( ! isset( $object->metadata, $object->metadata->instance ) ) {
				self::send_success( 'Instance missing' );
			}

			if ( ! self::check_instance( $object->metadata->instance ) ) {
				self::send_success( 'Instance does not match -> ' . $object->metadata->instance . ' : ' . $gateway->instance );
			}
		}

		/**
		 * Check if instance submitted with webhook matches expected instance
		 *
		 * @param string $submitted_instance Submitted instance.
		 * @return bool Whether instance matches or not
		 */
		protected static function check_instance( $submitted_instance ) {
			$gateway = self::$gateway;

			/**
			 * APPLY_FILTERS: yith_wcstripe_does_webhook_instance_match
			 *
			 * Filters if instance submitted with webhook matches expected instance.
			 *
			 * @param bool                  $bool               Default value comming from gateway and submitted instance comparison.
			 * @param string                $submitted_instance Submitted instance.
			 * @param string                $instance           Gateway instance.
			 * @param YITH_WCStripe_Gateway $gateway            The gateway.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcstripe_does_webhook_instance_match', $submitted_instance === $gateway->instance, $submitted_instance, $gateway->instance, $gateway );
		}

		/**
		 * Return success
		 *
		 * @param string $msg Message to output.
		 *
		 * @since 1.2.6
		 */
		protected static function send_success( $msg = '' ) {
			status_header( 200 );
			header( 'Content-Type: text/plain' );

			if ( ! empty( $msg ) ) {
				echo esc_html( $msg );
			}

			self::$running = false;

			exit( 0 );
		}

		/**
		 * Return error
		 *
		 * @param string $msg Message to output.
		 *
		 * @since 1.2.6
		 */
		protected static function send_error( $msg = '' ) {
			header( 'Content-Type: plain/text' );
			status_header( 500 );

			if ( ! empty( $msg ) ) {
				echo esc_html( $msg );
			}

			self::$running = false;

			exit( 0 );
		}
	}
}
