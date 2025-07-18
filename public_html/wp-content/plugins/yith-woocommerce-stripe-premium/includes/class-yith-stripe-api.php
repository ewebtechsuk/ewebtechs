<?php
/**
 * API hadnler class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\Customer;
use Stripe\Card;
use Stripe\Plan;
use Stripe\Subscription;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Event;
use Stripe\Product;
use Stripe\BalanceTransaction;
use Stripe\WebhookEndpoint;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Checkout\Session;

if ( ! class_exists( 'YITH_Stripe_API' ) ) {
	/**
	 * Expose methods to interact with Stripe API
	 *
	 * @since 1.0.0
	 */
	class YITH_Stripe_API {

		/**
		 * Private key used to authenticate on Stripe servers
		 *
		 * @var string
		 */
		protected $private_key = '';

		/**
		 * Set the Stripe library
		 *
		 * @param string $key Private key for api connection.
		 *
		 * @since 1.0.0
		 */
		public function __construct( $key ) {
			if ( ! class_exists( 'Stripe' ) ) {
				include_once dirname( __DIR__ ) . '/vendor/autoload.php';
			}

			$this->private_key = $key;
			Stripe::setAppInfo( 'YITH WooCommerce Stripe', YITH_WCSTRIPE_VERSION, 'https://yithemes.com' );
			Stripe::setApiVersion( YITH_WCSTRIPE_API_VERSION );
			Stripe::setApiKey( $this->private_key );
		}

		/**
		 * Returns Stripe's Private Key
		 *
		 * @return string
		 * @since 1.6.0
		 */
		public function get_private_key() {
			/**
			 * APPLY_FILTERS: yith_wcstripe_private_key
			 *
			 * Filters Stripe's Private Key.
			 *
			 * @param string The key.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcstripe_private_key', $this->private_key );
		}

		/* === CHARGES METHODS === */

		/**
		 * Create the charge
		 *
		 * @param array $params Array of parameters used to create carge.
		 *
		 * @return Charge
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function charge( $params ) {
			return Charge::create(
				$params,
				array(
					'idempotency_key' => self::generate_random_string(),
				)
			);
		}

		/**
		 * Retrieve the charge
		 *
		 * @param string $transaction_id Id of the charge to retrieve.
		 *
		 * @return Charge
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function get_charge( $transaction_id ) {
			return Charge::retrieve( $transaction_id );
		}

		/**
		 * Capture a charge
		 *
		 * @param string $transaction_id Id of the charge to capture.
		 *
		 * @return Charge
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function capture_charge( $transaction_id ) {
			$charge = $this->get_charge( $transaction_id );

			// exist if already captured.
			if ( ! $charge->captured ) {
				$charge->capture();
			}

			return $charge;
		}

		/**
		 * Change a charge
		 *
		 * @param string $transaction_id Id of the charge to update.
		 * @param array  $params         Array of parameters to update.
		 * @param bool   $return         Whether to return charge object or not.
		 *
		 * @return Charge|null
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function update_charge( $transaction_id, $params = array(), $return = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.returnFound
			$allowed_properties = array(
				'customer',
				'description',
				'metadata',
				'receipt_email',
				'shipping',
				'transfer_group',
				'fraud_details',
			);

			$to_update = array_intersect_key( $params, array_flip( $allowed_properties ) );

			Charge::update( $transaction_id, $to_update );

			if ( $return ) {
				return $this->get_charge( $transaction_id );
			}

			return null;
		}

		/**
		 * Retrieve Balance Transaction
		 *
		 * @param string $transaction_id Id of the charge that generated balance transaction.
		 * @param array  $params         Additional parameters to be sent within the request.
		 *
		 * @return BalanceTransaction Balance object
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function get_balance_transaction( $transaction_id, $params = array() ) {
			return BalanceTransaction::retrieve( $transaction_id, $params );
		}

		/**
		 * Perform a refund
		 *
		 * @param string $transaction_id Id of the charge to refund.
		 * @param array  $params         Array of parameters used to create refund request.
		 *
		 * @return Refund
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function refund( $transaction_id, $params ) {
			return Refund::create( array_merge( array( 'charge' => $transaction_id ), $params ) );
		}

		/* === CUSTOMER METHODS === */

		/**
		 * New customer
		 *
		 * @param array $params Array of parameters used to build a cutomer.
		 *
		 * @return Customer
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function create_customer( $params ) {
			return Customer::create( $params );
		}

		/**
		 * Retrieve customer
		 *
		 * @param Customer|string $customer Customer object or ID.
		 *
		 * @return Customer
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function get_customer( $customer ) {
			if ( is_a( $customer, '\Stripe\Customer' ) ) {
				return $customer;
			}

			return Customer::retrieve(
				array(
					'id'     => $customer,
					'expand' => array( 'sources', 'subscriptions' ),
				)
			);
		}

		/**
		 * Update customer
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param array           $params   Array of parameters to update.
		 * @param bool            $return   Whether to return customer object or not.
		 *
		 * @return Customer|null
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function update_customer( $customer, $params, $return = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.returnFound
			$allowed_properties = array(
				'address',
				'description',
				'email',
				'metadata',
				'name',
				'phone',
				'shipping',
				'balance',
				'cash_balance',
				'coupon',
				'default_source',
				'invoice_prefix',
				'invoice_settings',
				'next_invoice_sequence',
				'preferred_locales',
				'promotion_code',
				'source',
				'tax',
				'tax_exempt',
			);

			$customer_id = $customer instanceof Customer ? $customer->id : $customer;
			$to_update   = array_intersect_key( $params, array_flip( $allowed_properties ) );

			Customer::update( $customer_id, $to_update );

			if ( $return ) {
				return $this->get_customer( $customer );
			}

			return null;
		}

		/* === CARDS METHODS === */

		/**
		 * Create a card
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param string          $token    Token represeting card to add to the customer.
		 *
		 * @return Card
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function create_card( $customer, $token ) {
			$customer = $this->get_customer( $customer );

			$result = $customer->sources->create(
				array(
					'card' => $token,
				)
			);

			/**
			 * DO_ACTION: yith_wcstripe_card_created
			 *
			 * Triggered after card creation.
			 *
			 * @param Customer|string $customer Customer object or ID.
			 * @param string          $token    Token represeting card to add to the customer.
			 */
			do_action( 'yith_wcstripe_card_created', $customer, $token );

			return $result;
		}

		/**
		 * Update card object
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param string          $card_id  Card id.
		 * @param array           $params   Parameter to update.
		 * @param bool            $return   Whether to return customer object or not.
		 *
		 * @return Customer Customer|null
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function update_card( $customer, $card_id, $params = array(), $return = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.returnFound
			$allowed_properties = array(
				'address_city',
				'address_country',
				'address_line1',
				'address_line2',
				'address_state',
				'address_zip',
				'exp_month',
				'exp_year',
				'metadata',
				'name',
			);

			$customer_id = $customer instanceof Customer ? $customer->id : $customer;
			$to_update   = array_intersect_key( $params, array_flip( $allowed_properties ) );

			Customer::updateSource( $customer_id, $card_id, $to_update );

			if ( $return ) {
				return $this->get_customer( $customer );
			}

			return null;
		}

		/**
		 * Create a card
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param string          $card_id  Id of the card to delete.
		 *
		 * @return Customer
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function delete_card( $customer, $card_id ) {
			$customer    = $this->get_customer( $customer );
			$customer_id = $customer->id;

			// delete card.
			$customer->sources->retrieve( $card_id )->delete();

			/**
			 * DO_ACTION: yith_wcstripe_card_deleted
			 *
			 * Triggered after card deletion.
			 *
			 * @param Customer|string $customer Customer object or ID.
			 * @param string          $card_id  Id of the card to delete.
			 */
			do_action( 'yith_wcstripe_card_deleted', $customer, $card_id );

			return $this->get_customer( $customer_id );
		}

		/**
		 * Se the default card for the customer
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param string          $card_id  Id of the card to set as default.
		 *
		 * @return Customer
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function set_default_card( $customer, $card_id ) {
			$result = $this->update_customer(
				$customer,
				array(
					'default_source' => $card_id,
				)
			);

			/**
			 * DO_ACTION: yith_wcstripe_card_set_default
			 *
			 * Triggered after card set as default.
			 *
			 * @param Customer|string $customer Customer object or ID.
			 * @param string          $card_id  Id of the card to set as default.
			 */
			do_action( 'yith_wcstripe_card_set_default', $customer, $card_id );

			return $result;
		}

		/**
		 * Retuns all customer cards
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param array           $params   Array of additional parameters for the request.
		 *
		 * @return array
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function get_cards( $customer, $params = array( 'limit' => 100 ) ) {
			$customer = $this->get_customer( $customer );

			return $customer->sources->all( $params )->data;
		}

		/**
		 * Retrieve a card object for the customer
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param string          $card_id  ID of the card to retrieve.
		 * @param array           $params   Array of additional parameters for the request.
		 *
		 * @return Card
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function get_card( $customer, $card_id, $params = array() ) {
			$customer = $this->get_customer( $customer );

			$card = $customer->sources->retrieve( $card_id, $params );

			return $card;
		}

		/* === BILLING METHODS === */

		/**
		 * Retrieve product
		 *
		 * @param Product|string $product Product object or ID.
		 *
		 * @return Product
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.5.1
		 */
		public function get_product( $product ) {
			if ( is_a( $product, '\Stripe\Product' ) ) {
				return $product;
			}

			return Product::retrieve( $product );
		}

		/**
		 * Create a plan
		 *
		 * @param array $params Array of parameters to use to create plan.
		 *
		 * @return Plan
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function create_plan( $params = array() ) {
			return Plan::create( $params );
		}

		/**
		 * Delete a plan
		 *
		 * @param Plan|string $plan_id Plan object or ID.
		 *
		 * @return Plan
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function delete_plan( $plan_id ) {
			$plan = $this->get_plan( $plan_id );

			return $plan->delete();
		}

		/**
		 * Get a plan
		 *
		 * @param Plan|string $plan_id Plan object or ID.
		 *
		 * @return Plan|bool
		 */
		public function get_plan( $plan_id ) {
			try {
				return Plan::retrieve( $plan_id );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Create an invoice
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param array           $params   Array of parameters.
		 *
		 * @return Invoice
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function create_invoice( $customer, $params = array() ) {
			$customer = $this->get_customer( $customer );

			return Invoice::create( array_merge( array( 'customer' => $customer->id ), $params ) );
		}

		/**
		 * Create an invoice item
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param array           $params   Array of parameters.
		 *
		 * @return InvoiceItem
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function create_invoice_item( $customer, $params = array() ) {
			$customer = $this->get_customer( $customer );

			return InvoiceItem::create( array_merge( array( 'customer' => $customer->id ), $params ) );
		}

		/**
		 * Create a subscription
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param string          $plan_id  Id of the plan to use to create subscription.
		 * @param array           $params   Additional parameters used for subscription creation.
		 *
		 * @return Subscription
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function create_subscription( $customer, $plan_id, $params = array() ) {
			$customer = $this->get_customer( $customer );

			return Subscription::create(
				array_merge(
					array(
						'plan'     => $plan_id,
						'customer' => $customer->id,
					),
					$params
				)
			);
		}

		/**
		 * Create a subscription
		 *
		 * @param Customer|string $customer        Deprecated.
		 * @param string          $subscription_id Id of the subscription to retrieve.
		 *
		 * @return Subscription
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function get_subscription( $customer, $subscription_id ) {
			return Subscription::retrieve( $subscription_id );
		}

		/**
		 * Retrieves subscriptions for a specific customer
		 *
		 * @param string $customer Customer id.
		 * @param array  $params   Array of additional parameters to use to filter subscriptions.
		 *
		 * @return array|bool
		 */
		public function get_subscriptions( $customer, $params = array() ) {
			try {
				$customer = $this->get_customer( $customer );

				$params = array_merge(
					$params,
					array( 'customer' => $customer->id )
				);

				$subscription = Subscription::all( $params );
			} catch ( Exception $e ) {
				return false;
			}

			return isset( $subscription->data ) ? $subscription->data : false;
		}

		/**
		 * Modify a subscription on stripe
		 *
		 * @param string $customer        Customer id.
		 * @param string $subscription_id Subscription id.
		 * @param array  $params          Array of parameters to update.
		 * @param bool   $return          Whether to return subscription object or not.
		 *
		 * @return Subscription|null
		 *
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function update_subscription( $customer, $subscription_id, $params = array(), $return = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.returnFound
			$allowed_properties = array(
				'cancel_at_period_end',
				'description',
				'items',
				'metadata',
				'payment_behavior',
				'proration_behavior',
				'add_invoice_items',
				'application_fee_percent',
				'automatic_tax',
				'billing_cycle_anchor',
				'billing_thresholds',
				'cancel_at',
				'collection_method',
				'coupon',
				'days_until_due',
				'default_source',
				'default_tax_rates',
				'off_session',
				'on_behalf_of',
				'pause_collection',
				'payment_settings',
				'pending_invoice_item_interval',
				'promotion_code',
				'proration_date',
				'transfer_data',
				'trial_end',
				'trial_from_plan',
			);

			$to_update = array_intersect_key( $params, array_flip( $allowed_properties ) );

			Subscription::update( $subscription_id, $to_update );

			if ( $return ) {
				return $this->get_subscription( $customer, $subscription_id );
			}

			return null;
		}

		/**
		 * Cancel a subscription
		 *
		 * @param string $customer        Customer id.
		 * @param string $subscription_id Subscription id.
		 * @param array  $params          Additional parameters for cancellarion operation.
		 *
		 * @return Subscription
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function cancel_subscription( $customer, $subscription_id, $params = array() ) {
			$subscription = $this->get_subscription( $customer, $subscription_id );

			if ( 'canceled' === $subscription->status ) {
				return $subscription;
			}

			return $subscription->cancel( $params );
		}

		/**
		 * Get an invoice for subscription
		 *
		 * @param string $invoice_id Id of the invoice to retrieve.
		 *
		 * @return Invoice
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function get_invoice( $invoice_id ) {
			return Invoice::retrieve( $invoice_id );
		}

		/**
		 * Pay an invoice for subscription
		 *
		 * @param string $invoice_id Invoice id.
		 *
		 * @return Invoice
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 * @since 1.0.0
		 */
		public function pay_invoice( $invoice_id ) {
			$invoice = $this->get_invoice( $invoice_id );
			$invoice->pay();

			return $invoice;
		}

		/* === PAYMENT INTENTS METHODS === */

		/**
		 * Retrieve a payment intent object on stripe, using id passed as argument
		 *
		 * @param string $payment_intent_id Payment intent id.
		 *
		 * @return PaymentIntent|bool Payment intent or false
		 */
		public function get_intent( $payment_intent_id ) {
			try {
				return PaymentIntent::retrieve(
					array(
						'id'     => $payment_intent_id,
						'expand' => array( 'latest_charge' ),
					)
				);
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Create a payment intent object on stripe, using parameters passed as argument
		 *
		 * @param array $params Array of parameters used to create Payment intent.
		 *
		 * @return PaymentIntent Brand new payment intent.
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function create_intent( $params ) {
			return PaymentIntent::create(
				$params,
				array(
					'idempotency_key' => self::generate_random_string(),
				)
			);
		}

		/**
		 * Update a payment intent object on stripe, using parameters passed as argument
		 *
		 * @param string $payment_intent_id Id of the intent to update.
		 * @param array  $params            Array of parameters used to update Payment intent.
		 *
		 * @return PaymentIntent|bool Updated payment intent or false on failure.
		 */
		public function update_intent( $payment_intent_id, $params ) {
			try {
				return PaymentIntent::update( $payment_intent_id, $params );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Return all payments method for a customer
		 *
		 * @param Customer|string $customer Customer object or ID.
		 * @param array           $params   Array of additional parameters to use to filter subscriptions.
		 * @return array|bool Payment methods for the customer, or false on failure
		 */
		public function get_payment_methods( $customer, $params = array() ) {
			try {
				$customer = $this->get_customer( $customer );

				$params = array_merge(
					$params,
					array(
						'customer' => $customer->id,
						'type'     => 'card',
					)
				);

				$payment_methods = PaymentMethod::all( $params );
			} catch ( Exception $e ) {
				return false;
			}

			return isset( $payment_methods->data ) ? $payment_methods->data : false;
		}

		/**
		 * Retrieve a payment method object on stripe, using id passed as argument
		 *
		 * @param string $payment_method_id Payment method id.
		 *
		 * @return PaymentMethod|bool Payment intent or false
		 */
		public function get_payment_method( $payment_method_id ) {
			try {
				return PaymentMethod::retrieve( $payment_method_id );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Detach a payment method from the customer
		 *
		 * @param string $payment_method_id Payment method id.
		 *
		 * @return PaymentMethod|bool Detached payment method, or false on failure
		 */
		public function delete_payment_method( $payment_method_id ) {
			try {
				return PaymentMethod::retrieve( $payment_method_id )->detach();
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Retrieve a setup intent object on stripe, using id passed as argument
		 *
		 * @param string $setup_intent_id Setup intent id.
		 *
		 * @return SetupIntent|bool Setup intent or false
		 */
		public function get_setup_intent( $setup_intent_id ) {
			try {
				return SetupIntent::retrieve( $setup_intent_id );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Create a payment intent object on stripe, using parameters passed as argument
		 *
		 * @param array $params Array of parameters used to create Payment intent.
		 *
		 * @return SetupIntent|bool Brand new payment intent or false on failure
		 */
		public function create_setup_intent( $params ) {
			try {
				return SetupIntent::create(
					$params,
					array(
						'idempotency_key' => self::generate_random_string(),
					)
				);
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Update a setup intent object on stripe, using parameters passed as argument
		 *
		 * @param string $setup_intent_id Id of the setup intent to update.
		 * @param array  $params          Array of parameters used to update Payment intent.
		 *
		 * @return SetupIntent|bool Updated payment intent or false on failure
		 */
		public function update_setup_intent( $setup_intent_id, $params ) {
			try {
				return SetupIntent::update( $setup_intent_id, $params );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Retrieve a PaymentIntent or a SetupIntent, depending on the id that it receives
		 *
		 * @param string $id Id of the intent that method should retrieve.
		 *
		 * @return SetupIntent|PaymentIntent|bool Intent or false on failure
		 */
		public function get_correct_intent( $id ) {
			try {
				if ( strpos( $id, 'seti' ) !== false ) {
					return $this->get_setup_intent( $id );
				} else {
					return $this->get_intent( $id );
				}
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Update a PaymentIntent or a SetupIntent, depending on the id that it receives
		 *
		 * @param string $id     Id of the intent that method should retrieve.
		 * @param array  $params Array of parameters that should be used to update intent.
		 *
		 * @return SetupIntent|PaymentIntent|bool Intent or false on failure
		 */
		public function update_correct_intent( $id, $params ) {
			try {
				if ( strpos( $id, 'seti' ) !== false ) {
					return $this->update_setup_intent( $id, $params );
				} else {
					return $this->update_intent( $id, $params );
				}
			} catch ( Exception $e ) {
				return false;
			}
		}

		/* === SESSION METHODS === */

		/**
		 * Retrieves a payment session by session id
		 *
		 * @param string $session_id Session id.
		 *
		 * @return Session|bool Session object, or false on failure
		 */
		public function get_session( $session_id ) {
			try {
				return Session::retrieve( $session_id );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Create checkout session, used by Stripe Checkout to process payment
		 *
		 * @param array $params Array of parameters used to create session.
		 *
		 * @return Session|bool Session created, or false on failure
		 */
		public function create_session( $params ) {
			try {
				return Session::create( $params );
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/* === REFUNDS METHODS === */

		/**
		 * Retrieves refunds objects for a specific transaction
		 *
		 * @param string $transaction_id Id of the transaction.
		 * @param array  $params         Array of additional parameters to use to filter subscriptions.
		 * @return array|bool An array of refund objects, or false on failure.
		 */
		public function get_refunds( $transaction_id, $params = array() ) {
			$params = array_merge(
				$params,
				array( 'charge' => $transaction_id )
			);

			try {
				$refunds = Refund::all( $params );
			} catch ( Exception $e ) {
				return false;
			}

			return isset( $refunds->data ) ? $refunds->data : false;
		}

		/* === MISC METHODS === */

		/**
		 * Retrieve an event from event ID
		 *
		 * @param string $event_id Event id.
		 *
		 * @return Event
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function get_event( $event_id ) {
			return Event::retrieve( $event_id );
		}

		/**
		 * Create webhook on Stripe
		 *
		 * @param array $params Parameters for webhook creations.
		 *
		 * @return WebhookEndpoint
		 * @throws \Stripe\Exception\ApiErrorException Throws exception on API error.
		 */
		public function create_webhook( $params ) {
			return WebhookEndpoint::create( $params );
		}

		/**
		 * Genereate a semi-random string
		 *
		 * @param int $length Length of the random string.
		 * @return string Generated string
		 *
		 * @since 1.0.0
		 */
		protected static function generate_random_string( $length = 24 ) {
			$characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTU';
			$characters_length = strlen( $characters );
			$random_string     = '';

			for ( $i = 0; $i < $length; $i++ ) {
				$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
			}

			return $random_string;
		}
	}
}
