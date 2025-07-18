<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Main premium class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

use Stripe\SetupIntent;

if ( ! class_exists( 'YITH_WCStripe_Premium' ) ) {
	/**
	 * WooCommerce Stripe main class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Premium extends YITH_WCStripe {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCStripe_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCStripe_Premium
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
			parent::__construct();

			// register plugin to licence/update system.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

			add_action( 'init', array( __CLASS__, 'create_blacklist_table' ) );
			register_activation_hook( __FILE__, array( __CLASS__, 'create_blacklist_table' ) );

			// includes.
			include_once 'class-yith-stripe-customer.php';
			include_once 'functions-yith-stripe.php';

			// admin includes.
			if ( is_admin() ) {
				include_once 'class-yith-stripe-admin-premium.php';
				$this->admin = new YITH_WCStripe_Admin_Premium();
			}

			// hooks.
			add_action( 'woocommerce_api_stripe_webhook', array( $this, 'handle_webhooks' ) );
			add_action( 'wp_loaded', array( $this, 'convert_tokens' ) );

			// body class.
			add_action( 'body_class', array( $this, 'add_body_class' ) );

			// renew methods.
			add_action( 'ywsbs_pay_renew_order_with_' . self::$gateway_id, array( $this, 'process_renew' ), 10, 2 );
			add_filter( 'ywsbs_renew_now_order_action', array( $this, 'show_manual_renew_button' ), 10, 2 );
			add_filter( 'yith_wcstripe_default_card', array( $this, 'change_subscription_card' ), 10, 2 );
			add_filter( 'yith_wcstripe_created_card', array( $this, 'change_subscription_card' ), 10, 2 );
			add_filter( 'yith_wcstripe_deleted_last_card', array( $this, 'change_subscription_card' ), 10, 2 );
			add_action( 'wp', array( $this, 'invoice_charged_notice' ) );
			add_filter( 'woocommerce_order_needs_payment', array( $this, 'renew_needs_payment' ), 10, 2 );

			// subscriptions.
			add_filter( 'ywsbs_from_list', array( $this, 'add_from_list' ) );
			add_filter( 'ywsbs_suspend_recurring_payment', array( $this, 'suspend_subscription' ), 10, 2 );
			add_filter( 'ywsbs_resume_recurring_payment', array( $this, 'resume_subscription' ), 10, 2 );
			add_filter( 'ywsbs_cancel_recurring_payment', array( $this, 'cancel_subscription' ), 10, 2 );
			add_filter( 'ywsbs_subscription_status_expired', array( $this, 'expired_subscription' ), 10, 1 );
			add_filter( 'ywsbs_reactivate_suspended_subscription', array( $this, 'reactivate_subscription' ), 10, 3 );
			add_filter( 'ywsbs_renew_order_item_meta_data', array( $this, 'remove_meta_from_subscription_renew' ), 10, 4 );

			// blacklist table.
			add_action( 'init', array( $this, 'blacklist_table_wpdbfix' ), 0 );
			add_action( 'switch_blog', array( $this, 'blacklist_table_wpdbfix' ), 0 );

			// token hooks.
			add_action( 'woocommerce_payment_token_deleted', array( $this, 'delete_token_from_stripe' ), 10, 2 );
			add_action( 'woocommerce_payment_token_set_default', array( $this, 'set_default_token_on_stripe' ), 10, 2 );

			// Payment methods customize.
			add_action( 'woocommerce_account_payment_methods_column_method', array( $this, 'myaccount_method_column' ) );
			add_filter( 'woocommerce_payment_methods_list_item', array( $this, 'myaccount_method' ), 10, 2 );

			// add custom endpoints.
			add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'wp', array( $this, 'handle_card_confirmation' ), 20 );

			// ajax handling.
			add_action( 'wp_ajax_yith_stripe_refresh_details', array( $this, 'send_checkout_details' ) );
			add_action( 'wp_ajax_nopriv_yith_stripe_refresh_details', array( $this, 'send_checkout_details' ) );

			add_action( 'wp_ajax_yith_stripe_refresh_intent', array( $this, 'refresh_intent' ) );
			add_action( 'wp_ajax_nopriv_yith_stripe_refresh_intent', array( $this, 'refresh_intent' ) );

			add_action( 'wc_ajax_yith_wcstripe_verify_intent', array( $this, 'verify_intent' ) );

			add_action( 'wp_ajax_yith_stripe_refresh_session', array( $this, 'refresh_session' ) );
			add_action( 'wp_ajax_nopriv_yith_stripe_refresh_session', array( $this, 'refresh_session' ) );

			// emails init.
			add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ) );
			add_filter( 'woocommerce_email_actions', array( $this, 'register_email_actions' ) );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'register_woocommerce_template' ), 10, 3 );

			// crons.
			add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
			add_action( 'init', array( $this, 'schedule_crons' ) );
			add_action( 'yith_wcstripe_expiring_cards_reminders_scheduler', array( $this, 'schedule_expiring_cards_reminders' ) );
			add_action( 'yith_wcstripe_expiring_cards_reminders_dispatcher', array( $this, 'dispatch_expiring_cards_reminders' ) );
		}

		/* === FRONTEND METHODS === */

		/**
		 * Adds body class to site, when gateway is enabled and available
		 *
		 * @param array $body_classes Current list of body classes.
		 * @return array Filtered list of body classes.
		 */
		public function add_body_class( $body_classes ) {
			$query_vars = WC()->query->get_query_vars();
			$gateway    = $this->get_gateway();

			if ( ! $gateway || ! $gateway->is_available() ) {
				return $body_classes;
			}

			$body_classes[] = 'yith-wcstripe';

			if (
				$gateway->save_cards &&
				'yes' === $gateway->get_option( 'custom_payment_method_style' ) &&
				is_account_page() &&
				isset( $query_vars['payment-methods'] )
			) {
				$body_classes[] = 'yith-wcstripe-custom-payment-method-table';
			}
			return $body_classes;
		}

		/* === BLACKLIST METHODS === */

		/**
		 * Set blacklist table name on £wpdb instance
		 *
		 * @since 1.1.3
		 */
		public function blacklist_table_wpdbfix() {
			global $wpdb;
			$blacklist_table = 'yith_wc_stripe_blacklist';

			$wpdb->{$blacklist_table} = $wpdb->prefix . $blacklist_table;
			$wpdb->tables[]           = $blacklist_table;
		}

		/**
		 * Create the {$wpdb->prefix}yith_wc_stripe_blacklist table
		 *
		 * @return void
		 * @since  1.0
		 * @see    dbDelta()
		 */
		public static function create_blacklist_table() {
			global $wpdb;

			if ( get_option( 'yith_wc_stripe_blacklist_table_created' ) ) {
				return;
			}

			/**
			 * Check if dbDelta() exists
			 */
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			$charset_collate = $wpdb->get_charset_collate();

			$create = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}yith_wc_stripe_blacklist (
                        `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
						`ip` VARCHAR(15) NOT NULL DEFAULT '',
						`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
						`order_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
						`ban_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
						`ban_date_gmt` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
						`ua` VARCHAR(255) NULL DEFAULT '',
						`unbanned` TINYINT(1) NOT NULL DEFAULT '0',
						PRIMARY KEY (`ID`),
						INDEX `user_id` (`user_id`),
						INDEX `order_id` (`order_id`),
						INDEX `ip` (`ip`)
                        ) $charset_collate;";
			dbDelta( $create );

			update_option( 'yith_wc_stripe_blacklist_table_created', true );
		}

		/* === YWSBS SUBSCRIPTION METHODS === */

		/**
		 * Detect if installed some external addons for ecommerce, to give them compatibility with stripe
		 *
		 * @param string $addon Addon to test.
		 *
		 * @return bool If defined $addon, returns if the addon is installed or not. If not defined it, return if any of addon compatible is installed
		 */
		public static function addons_installed( $addon = '' ) {
			$checks = array(
				'yith-subscription' => function_exists( 'YITH_WC_Subscription' ),
				'yith-pre-order'    => function_exists( 'YITH_Pre_Order_Premium' ),
			);

			if ( ! empty( $addon ) ) {
				return isset( $checks[ $addon ] ) ? $checks[ $addon ] : false;
			}

			foreach ( $checks as $check ) {
				if ( $check ) {
					return true;
				}
			}

			return false;
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
			include_once 'class-yith-stripe-gateway-advanced.php';

			if ( self::addons_installed() ) {
				include_once 'class-yith-stripe-gateway-addons.php';
				$methods[] = 'YITH_WCStripe_Gateway_Addons';
			} else {
				$methods[] = 'YITH_WCStripe_Gateway_Advanced';
			}

			return $methods;
		}

		/**
		 * Add this gateway to the list of subscriptions' update sources.
		 *
		 * @param array $list List of possible sources.
		 *
		 * @return mixed
		 */
		public function add_from_list( $list ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.listFound
			$gateway = $this->get_gateway();

			if ( $gateway instanceof YITH_WCStripe_Gateway ) {
				$list[ self::$gateway_id ] = $gateway->get_method_title();
			}

			return $list;
		}

		/**
		 * Cancel subscription when plan expires
		 *
		 * @param int $subscription_id Subscription id.
		 *
		 * @return void
		 */
		public function expired_subscription( $subscription_id ) {
			$subscription = ywsbs_get_subscription( (int) $subscription_id );

			if ( ! $subscription || $subscription->get_payment_method() !== self::$gateway_id ) {
				return;
			}

			$this->cancel_subscription( true, $subscription );
		}

		/**
		 * Cancel recurring payment if the subscription has a stripe subscription
		 *
		 * @param bool               $result       Result of the operation.
		 * @param YWSBS_Subscription $subscription Subscription object.
		 *
		 * @return bool
		 */
		public function cancel_subscription( $result, $subscription ) {
			if ( $subscription->get_payment_method() !== self::$gateway_id || empty( $subscription->stripe_subscription_id ) ) {
				return $result;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return false;
			}

			try {

				// load SDK.
				$gateway->init_stripe_sdk();

				$gateway->api->cancel_subscription( $subscription->stripe_customer_id, $subscription->stripe_subscription_id );

				// remove the invoice from failed invoices list, if it exists.
				$order           = wc_get_order( $subscription->order_id );
				$failed_invoices = get_user_meta( $order->get_user_id(), 'failed_invoices', true );

				if ( isset( $failed_invoices[ $subscription->id ] ) ) {
					unset( $failed_invoices[ $subscription->id ] );
					update_user_meta( $order->get_user_id(), 'failed_invoices', $failed_invoices );
				}

				$gateway->log( 'YSBS - Stripe Subscription Cancel Request ' . $subscription->id . ' with success.' );
				YITH_WC_Activity()->add_activity( $subscription->id, 'cancelled', 'success' );

				return $result;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$gateway->log( 'Stripe Subscription Error: ' . $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				YITH_WC_Activity()->add_activity( $subscription->id, 'cancelled', 'error', '', $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				return false;
			}
		}

		/**
		 * Suspend a subscription, by update the subscription on stripe and setting "trial_end" to undefined date
		 *
		 * @param bool               $result Result of the operation.
		 * @param YWSBS_Subscription $subscription Subscription object.
		 *
		 * @return bool
		 */
		public function suspend_subscription( $result, $subscription ) {
			if ( $subscription->get_payment_method() !== self::$gateway_id || empty( $subscription->stripe_subscription_id ) ) {
				return true;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return false;
			}

			try {

				// load SDK.
				$gateway->init_stripe_sdk();

				// set trial to undefined date, so any payment is triggered from stripe, without cancel subscription.
				$gateway->api->update_subscription(
					$subscription->stripe_customer_id,
					$subscription->stripe_subscription_id,
					array(
						'trial_end' => strtotime( '+730 days' ),  // max supported by stripe.
					),
					false
				);

				$gateway->log( 'YSBS - Stripe Subscription ' . $subscription->id . ' Pause Request with success.' );
				YITH_WC_Activity()->add_activity( $subscription->id, 'paused', 'success' );

				return true;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$gateway->log( 'Stripe Subscription Error: ' . $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				YITH_WC_Activity()->add_activity( $subscription->id, 'paused', 'error', '', $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				return false;
			}
		}

		/**
		 * Resume the subscription, updated it and set "trial_end" to now value
		 *
		 * @param bool               $result       Result of the operation.
		 * @param YWSBS_Subscription $subscription Subscription object.
		 *
		 * @return bool
		 *
		 * @since 1.2.9
		 */
		public function resume_subscription( $result, $subscription ) {
			if ( $subscription->get_payment_method() !== self::$gateway_id || empty( $subscription->stripe_subscription_id ) ) {
				return true;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return false;
			}

			try {

				// load SDK.
				$gateway->init_stripe_sdk();

				$date_offset = method_exists( YWSBS_Subscription_Helper(), 'get_payment_due_date_paused_offset' ) ? YWSBS_Subscription_Helper()->get_payment_due_date_paused_offset( $subscription ) : $subscription->get_payment_due_date_paused_offset();

				// set trial to undefined date, so any payment is triggered from stripe, without cancel subscription.
				$gateway->api->update_subscription(
					$subscription->stripe_customer_id,
					$subscription->stripe_subscription_id,
					array(
						'trial_end' => ( $subscription->payment_due_date > time() ) ? $subscription->payment_due_date + $date_offset : time(),
					),
					false
				);

				$gateway->log( 'YSBS - Stripe Subscription ' . $subscription->id . ' Resumed with success.' );
				YITH_WC_Activity()->add_activity( $subscription->id, 'resumed', 'success' );

				return true;

			} catch ( Stripe\Exception\ApiErrorException $e ) {
				$gateway->log( 'Stripe Subscription Error: ' . $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				YITH_WC_Activity()->add_activity( $subscription->id, 'resumed', 'error', '', $e->getHttpStatus() . ' - ' . print_r( $e->getJsonBody(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				return false;
			}
		}

		/**
		 * Process payment of the subscription immediately after reactivation
		 *
		 * @param string             $status       Current autorenew status.
		 * @param YWSBS_Subscription $subscription Subscription object.
		 * @param WC_order           $order        Renew order.
		 *
		 * @return void
		 */
		public function reactivate_subscription( $status, $subscription, $order ) {
			if ( 'yes' === $status && $subscription->get_payment_method() === self::$gateway_id ) {
				$gateway = $this->get_gateway();

				if ( ! $gateway instanceof YITH_WCStripe_Gateway_Addons ) {
					return;
				}

				try {
					$gateway->process_renew( $order );
				} catch ( Exception $e ) {
					return;
				}
			}
		}

		/* === YWSBS RENEW METHODS === */

		/**
		 * Process renew
		 *
		 * @param WC_Order $order        Renew order.
		 * @param bool     $manual_renew Whether renew is manual or no.
		 *
		 * @return bool Status of operation
		 */
		public function process_renew( $order, $manual_renew ) {
			$gateway = $this->get_gateway();

			if ( ! $gateway instanceof YITH_WCStripe_Gateway_Addons ) {
				return false;
			}

			try {
				return ! $manual_renew ? $gateway->process_renew( $order ) : $gateway->process_manual_renew( $order );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Skip all metas related to stripe subscription, when creating renews order
		 *
		 * @param bool   $save          Whether to save meta or not.
		 * @param int    $order_item_id Order item id.
		 * @param string $key           Meta key.
		 * @param mixed  $value         Meta value.
		 *
		 * @return bool Whether to save meta or not
		 */
		public function remove_meta_from_subscription_renew( $save, $order_item_id, $key, $value ) {
			if ( '_subscription_charge_id' === $key ) {
				return false;
			}

			return $save;
		}

		/**
		 * Update subscription default payment method, when a new default card is added
		 *
		 * @param string           $card_id  Id for the new source on stripe.
		 * @param \Stripe\Customer $customer Stripe customer object.
		 *
		 * @return void
		 */
		public function change_subscription_card( $card_id, $customer ) {
			if ( ! function_exists( 'YITH_WC_Subscription' ) ) {
				return;
			}

			// retrieve user id.
			$user_id = isset( $customer->metadata ) && isset( $customer->metadata->user_id ) ? $customer->metadata->user_id : false;

			if ( ! $user_id ) {
				return;
			}

			// retrieve actual card_id to use.
			$current_action = current_action();
			$card_id        = 'yith_wcstripe_deleted_last_card' === $current_action ? '' : $card_id;

			// retrieve subscriptions for the user.
			$subscriptions = YITH_WC_Subscription()->get_user_subscriptions( $user_id );

			// set new payment method for all customers' subscriptions.
			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $subscription_id ) {
					$subscription = ywsbs_get_subscription( (int) $subscription_id );

					if ( ! $subscription || self::$gateway_id !== $subscription->payment_method ) {
						continue;
					}

					$subscription->set( 'yith_stripe_token', $card_id );

					do_action( 'yith_wcstripe_subscription_token_updated', $card_id, $customer, $subscription );

					method_exists( $subscription, 'save' ) && $subscription->save();
				}
			}
		}

		/**
		 * Print a success notice if the invoice is charged successfully
		 *
		 * @since 1.2.9
		 */
		public function invoice_charged_notice() {
			if ( get_user_meta( get_current_user_id(), 'invoice_charged', true ) && function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'Subscription renewed successfully!', 'yith-woocommerce-stripe' ) );
				delete_user_meta( get_current_user_id(), 'invoice_charged' );
			}
		}

		/**
		 * Shows renew button for old subscriptions, where failed invoices are registered in user meta
		 *
		 * @param bool     $show_button Whether to show manual renew button for old subscriptions or not.
		 * @param WC_Order $order       Order object.
		 *
		 * @return bool Filtered value
		 */
		public function show_manual_renew_button( $show_button, $order ) {
			$gateway = $this->get_gateway();

			$order_id        = $order->get_id();
			$user_id         = $order->get_user_id();
			$subscriptions   = $order->get_meta( 'subscriptions' );
			$subscription_id = ! empty( $subscriptions ) ? array_pop( $subscriptions ) : false;

			/**
			 * APPLY_FILTERS: yith_wcstripe_maybe_show_subscription_action
			 *
			 * Filters if should show subscription action.
			 *
			 * @param bool                  Default value: true.
			 * @param int  $subscription_id The subscription ID.
			 *
			 * @return bool
			 */
			if ( $user_id && $subscription_id && $gateway instanceof YITH_WCStripe_Gateway_Addons && apply_filters( 'yith_wcstripe_maybe_show_subscription_action', true, $subscription_id ) && $gateway->has_active_subscription( $subscription_id ) ) {
				$subscription    = ywsbs_get_subscription( (int) $subscription_id );
				$parent_order_id = $subscription ? $subscription->order_id : 0;
				$parent_order    = wc_get_order( $parent_order_id );

				$parent_order_failed_attempts = $parent_order->get_meta( 'failed_attemps' );
				$failed_invoices              = get_user_meta( $user_id, 'failed_invoices', true );

				$show_button = ( $order->get_meta( 'failed_attemps' ) > 0 || $parent_order_failed_attempts > 0 ) && is_array( $failed_invoices ) && isset( $failed_invoices[ $subscription_id ] ) && (int) $subscription->renew_order === $order_id;
			}

			return $show_button;
		}

		/**
		 * Filters order_needs_payment to make renew payable when card needs confirmation
		 *
		 * @param bool     $needs_payment Whether order needs payment.
		 * @param WC_Order $order         Order object.
		 *
		 * @return bool Filtered value.
		 */
		public function renew_needs_payment( $needs_payment, $order ) {
			if ( 'yes' !== $order->get_meta( 'is_a_renew' ) || 'yes' !== $order->get_meta( 'yith_wcstripe_card_requires_action' ) ) {
				return $needs_payment;
			}

			$subscriptions   = $order->get_meta( 'subscriptions' );
			$subscription_id = ! empty( $subscriptions ) ? array_shift( $subscriptions ) : false;
			$subscription    = $subscription_id ? ywsbs_get_subscription( (int) $subscription_id ) : false;

			if ( ! $subscription ) {
				return $needs_payment;
			}

			$renew_status = YWSBS_Subscription_Order()->get_renew_order_status( $subscription );

			if ( ! $order->has_status( $renew_status ) ) {
				return $needs_payment;
			}

			return true;
		}

		/* === UTILITY METHODS === */

		/**
		 * Get customer object
		 *
		 * @return YITH_WCStripe_Customer
		 */
		public function get_customer() {
			return YITH_WCStripe_Customer();
		}

		/**
		 * Handle the webhooks from stripe account
		 *
		 * @since 1.0.0
		 */
		public function handle_webhooks() {
			include_once 'class-yith-stripe-webhook.php';

			YITH_WCStripe_Webhook::route();
		}

		/**
		 * Add confirmation endpoint for payment methods
		 *
		 * @param array $query_vars Array of available endpoints.
		 *
		 * @return array Array of filtered endpoints
		 */
		public function add_query_vars( $query_vars ) {
			$query_vars['confirm-payment-method'] = 'confirm-payment-method';

			return $query_vars;
		}

		/**
		 * Handle confirmation endpoint
		 * When we call this endpoint, we suppose that method was correctly confirmed; anyway, if any server process is needed,
		 * yith_wcstripe_method_correctly_confirmed filter is available for further processing
		 *
		 * @return void
		 */
		public function handle_card_confirmation() {
			global $wp;

			if ( isset( $wp->query_vars['confirm-payment-method'] ) ) {
				wc_nocache_headers();

				$token_id = absint( $wp->query_vars['confirm-payment-method'] );
				$token    = WC_Payment_Tokens::get( $token_id );

				if ( is_null( $token ) || get_current_user_id() !== $token->get_user_id() || ! isset( $_REQUEST['_wpnonce'] ) || false === wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'confirm-payment-method-' . $token_id ) ) {
					wc_add_notice( __( 'Invalid payment method.', 'yith-woocommerce-stripe' ), 'error' );
				} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
					/**
					 * APPLY_FILTERS: yith_wcstripe_method_correctly_confirmed
					 *
					 * Filters if the method is successfuly confirmed.
					 *
					 * @param bool          Default value: true.
					 * @param string $token The method token.
					 *
					 * @return bool
					 */
					if ( apply_filters( 'yith_wcstripe_method_correctly_confirmed', true, $token ) ) {
						$token->update_meta_data( 'confirmed', true );
						$token->save();
						wc_add_notice( __( 'This payment method was confirmed successfully.', 'yith-woocommerce-stripe' ) );
					} else {
						wc_add_notice( __( 'There was an error while confirming the payment method; please, try again later.', 'yith-woocommerce-stripe' ) );
					}
				}

				wp_safe_redirect( wc_get_account_endpoint_url( 'payment-methods' ) );
				exit();
			}
		}

		/* === HANDLE TOKENS METHODS === */

		/**
		 * Handle setting a token as default
		 *
		 * @param int                 $token_id Payment token id.
		 * @param WC_Payment_Token_CC $token    Payment token object.
		 *
		 * @return bool
		 */
		public function set_default_token_on_stripe( $token_id, $token = null ) {
			// retrieve token when not provided.
			if ( empty( $token ) ) {
				$token = WC_Payment_Tokens::get( $token_id );
			}

			// check if token was registered by Stripe before trying to delete it.
			if ( $token->get_gateway_id() !== self::$gateway_id ) {
				return false;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return false;
			}

			try {
				// Initializate SDK and set private key.
				$gateway->init_stripe_sdk();

				$customer = YITH_WCStripe()->get_customer()->get_usermeta_info( $token->get_user_id() );

				if ( empty( $customer ) ) {
					return false;
				}

				$customer = $gateway->api->update_customer(
					$customer['id'],
					array_merge(
						array(
							'invoice_settings' => array(
								'default_payment_method' => $token->get_token(),
							),
						),
						strpos( $token->get_token(), 'card' ) === 0 ? array(
							'default_source' => $token->get_token(),
						) : array()
					)
				);

				/**
				 * DO_ACTION: yith_wcstripe_default_card
				 *
				 * Triggered when setting default card in Stripe.
				 *
				 * @param WC_Payment_Token_CC $token    Payment token object.
				 * @param Customer|string     $customer Customer object or ID.
				 */
				do_action( 'yith_wcstripe_default_card', $token->get_token(), $customer );

				// backward compatibility.
				YITH_WCStripe()->get_customer()->update_usermeta_info(
					$customer->metadata->user_id,
					array_merge(
						array(
							'id'             => $customer->id,
							'cards'          => isset( $customer->sources ) ? $customer->sources->data : array(),
							'default_source' => $token->get_token(),
						)
					)
				);

				return true;
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Handle the card removing from stripe databases for the customer
		 *
		 * @param int                 $token_id Payment token id.
		 * @param WC_Payment_Token_CC $token    Payment token object.
		 *
		 * @return bool
		 */
		public function delete_token_from_stripe( $token_id, $token ) {
			// retrieve token when not provided.
			if ( empty( $token ) ) {
				$token = WC_Payment_Tokens::get( $token_id );
			}

			// check if token was registered by Stripe before trying to delete it.
			if ( $token->get_gateway_id() !== self::$gateway_id ) {
				return false;
			}

			$gateway = $this->get_gateway();
			$user_id = $token->get_user_id();

			if ( ! $gateway ) {
				return false;
			}

			try {
				// Initializate SDK and set private key.
				$gateway->init_stripe_sdk();

				// delete card.
				$gateway->api->delete_payment_method( $token->get_token() );

				// get customer.
				$customer = YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );

				if ( $token->is_default() && $customer ) {
					// set arbitrarily last saved card as default.
					$tokens        = WC_Payment_Tokens::get_customer_tokens( $user_id, self::$gateway_id );
					$default_token = end( $tokens );

					if ( $default_token ) {
						$customer = $gateway->api->update_customer(
							$customer['id'],
							array_merge(
								array(
									'invoice_settings' => array(
										'default_payment_method' => $default_token->get_token(),
									),
								),
								strpos( $default_token->get_token(), 'card' ) === 0 ? array(
									'default_source' => $default_token->get_token(),
								) : array()
							)
						);

						/**
						 * DO_ACTION: yith_wcstripe_default_card
						 *
						 * Triggered when setting a default card after deleting card from Stripe.
						 *
						 * @param WC_Payment_Token_CC $token    Payment token object.
						 * @param Customer|string     $customer Customer object or ID.
						 */
						do_action( 'yith_wcstripe_default_card', $default_token->get_token(), $customer );

						$default_token->set_default( true );
						$default_token->save();
					} else {
						$customer = $gateway->api->update_customer(
							$customer['id'],
							array_merge(
								array(
									'invoice_settings' => array(
										'default_payment_method' => '',
									),
								)
							)
						);

						/**
						 * DO_ACTION: yith_wcstripe_deleted_last_card
						 *
						 * Triggered when the card deleted from Stripe was the last one.
						 *
						 * @param WC_Payment_Token_CC $token    Payment token object.
						 * @param Customer|string     $customer Customer object or ID.
						 */
						do_action( 'yith_wcstripe_deleted_last_card', $token->get_token(), $customer );
					}
				}

				if ( is_string( $customer ) ) {
					$customer = $gateway->api->get_customer( $customer );
				} else {
					$customer = $gateway->api->get_customer( $customer['id'] );
				}

				/**
				 * DO_ACTION: yith_wcstripe_deleted_card
				 *
				 * Triggered after a card was deleted from Stripe.
				 *
				 * @param WC_Payment_Token_CC $token    Payment token object.
				 * @param Customer|string     $customer Customer object or ID.
				 */
				do_action( 'yith_wcstripe_deleted_card', $token->get_token(), $customer );

				// backward compatibility.
				YITH_WCStripe()->get_customer()->update_usermeta_info(
					$customer->metadata->user_id,
					array(
						'id'             => $customer->id,
						'cards'          => isset( $customer->sources ) ? $customer->sources->data : array(),
						'default_source' => ! empty( $default_token ) ? $default_token->get_token() : false,
					)
				);

				return true;
			} catch ( Stripe\Exception\ApiErrorException $e ) {
				return false;
			}
		}

		/**
		 * Convert tokens to new system of WC 2.6, as soon as the user is logged in
		 *
		 * @param int $user_id User id whose token must be converted, or null, if you want to use current user.
		 */
		public function convert_tokens( $user_id = null ) {
			$user_id = $user_id ? $user_id : get_current_user_id();

			if ( ! class_exists( 'WC_Payment_Tokens' ) || ! $user_id || 'yes' === get_user_meta( $user_id, '_tokens_converted', true ) ) {
				return;
			}

			$gateway = $this->get_gateway();

			if ( ! $gateway ) {
				return;
			}

			// Initialize SDK and set private key.
			$gateway->init_stripe_sdk();

			$customer = (array) YITH_WCStripe()->get_customer()->get_usermeta_info( $user_id );
			$tokens   = WC_Payment_Tokens::get_customer_tokens( $user_id );

			if ( ! empty( $customer['cards'] ) && empty( $tokens ) ) {
				foreach ( $customer['cards'] as $card ) {
					$token = new WC_Payment_Token_CC();

					$token->set_token( $card->id );
					$token->set_gateway_id( $gateway->id );
					$token->set_user_id( $user_id );
					$token->set_card_type( strtolower( $card->brand ) );
					$token->set_last4( $card->last4 );
					$token->set_expiry_month( ( 1 === strlen( $card->exp_month ) ? '0' . $card->exp_month : $card->exp_month ) );
					$token->set_expiry_year( $card->exp_year );

					$token->set_default( $customer['default_source'] === $card->id );

					$token->save();
				}
			}

			update_user_meta( $user_id, '_tokens_converted', 'yes' );
		}

		/**
		 * Customize column method on Payment Methods my account page
		 *
		 * @param array $method Payment method array.
		 */
		public function myaccount_method_column( $method ) {
			if ( self::$gateway_id !== $method['method']['gateway'] ) {
				if ( ! empty( $method['method']['last4'] ) ) {
					/* translators: 1: credit card type 2: last 4 digits */
					echo esc_html( sprintf( __( '%1$s ending in %2$s', 'woocommerce' ), wc_get_credit_card_type_label( $method['method']['brand'] ), $method['method']['last4'] ) );
				} else {
					echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
				}

				return;
			}

			$icon_brands = array(
				'american express' => 'amex',
			);
			$icon        = WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/' . str_replace( array_keys( $icon_brands ), array_values( $icon_brands ), strtolower( $method['method']['brand'] ) ) . '.svg' );
			/**
			 * APPLY_FILTERS: yith_wcstripe_card_number_dots
			 *
			 * Filters card number dots.
			 *
			 * @param string Default value: '&bull;&bull;&bull;&bull;'.
			 *
			 * @return string
			 */
			$dots                         = apply_filters( 'yith_wcstripe_card_number_dots', '&bull;&bull;&bull;&bull;' );
			$current_year                 = gmdate( 'y' );
			$current_month                = gmdate( 'm' );
			list( $exp_month, $exp_year ) = explode( '/', $method['expires'] );

			printf( '<img src="%s" alt="%s" style="width:40px;"/>', esc_url( $icon ), esc_attr( strtolower( $method['method']['brand'] ) ) );
			printf(
				'<span class="card-type"><strong>%s</strong></span> <span class="card-number"><small><em>%s</em>%s</small></span>',
				esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) ),
				esc_html( $dots ),
				esc_html( $method['method']['last4'] )
			);

			if ( $method['is_default'] ) {
				printf( '<span class="tag-label default">%s</span>', esc_html__( 'default', 'yith-woocommerce-stripe' ) );
			}

			if ( $exp_year < $current_year || ( (int) $exp_year === (int) $current_year && $exp_month < $current_month ) ) {
				printf( '<span class="tag-label expired">%s</span>', esc_html__( 'expired', 'yith-woocommerce-stripe' ) );
			}
		}

		/**
		 * Changes method before printing it on Payment Methods my account page
		 *
		 * @param array            $method Payment method array.
		 * @param WC_Payment_Token $token  Payment token.
		 *
		 * @return array
		 */
		public function myaccount_method( $method, $token ) {
			if ( self::$gateway_id !== $method['method']['gateway'] ) {
				return $method;
			}

			$current_year                 = gmdate( 'y' );
			$current_month                = gmdate( 'm' );
			list( $exp_month, $exp_year ) = explode( '/', $method['expires'] );

			// unset Set Default button, when card is expired.
			if ( $exp_year < $current_year || ( (int) $exp_year === (int) $current_year && $exp_month < $current_month ) ) {
				unset( $method['actions']['default'] );
			}

			if ( ! $token->get_meta( 'confirmed' ) ) {
				$method['actions']['confirm'] = array(
					'url'  => wp_nonce_url( wc_get_endpoint_url( 'confirm-payment-method', $token->get_id() ), 'confirm-payment-method-' . $token->get_id() ),
					'name' => __( 'Confirm', 'yith-woocommerce-stripe' ),
				);
			}

			return $method;
		}

		/* === WC EMAILS === */

		/**
		 * Register email classes for stripe
		 *
		 * @param array $classes Array of email class instances.
		 *
		 * @return mixed Filtered array of email class instances
		 * @since 1.0.0
		 */
		public function register_email_classes( $classes ) {
			require_once YITH_WCSTRIPE_INC . 'emails/class-yith-stripe-email.php';

			$classes['YITH_WCStripe_Expiring_Card_Email']      = include_once YITH_WCSTRIPE_INC . 'emails/class-yith-stripe-expiring-card-email.php';
			$classes['YITH_WCStripe_Renew_Needs_Action_Email'] = include_once YITH_WCSTRIPE_INC . 'emails/class-yith-stripe-renew-needs-action-email.php';

			return $classes;
		}

		/**
		 * Register email action for stripe
		 *
		 * @param array $emails Array of registered actions.
		 *
		 * @return mixed Filtered array of registered actions
		 * @since 1.0.0
		 */
		public function register_email_actions( $emails ) {
			$emails = array_merge(
				$emails,
				array(
					'yith_wcstripe_expiring_card',
					'yith_wcstripe_renew_intent_requires_action',
				)
			);

			return $emails;
		}

		/**
		 * Locate default templates of woocommerce in plugin, if exists
		 *
		 * @param string $core_file      Template inside WooCommerce's base path.
		 * @param string $template       Name of the template system is searching for.
		 * @param string $template_base  Base path of the template system is searching for.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function register_woocommerce_template( $core_file, $template, $template_base ) {
			$located = yith_wcstripe_locate_template( $template );

			if ( $located && file_exists( $located ) ) {
				return $located;
			} else {
				return $core_file;
			}
		}

		/* === CRONS === */

		/**
		 * Add new schedules for stripe crons
		 *
		 * @param array $schedules Array of currently defined schedules.
		 *
		 * @return array Filtered array of schedules
		 */
		public function add_schedules( $schedules ) {
			$schedules['yith_wcstripe_3_times_for_hour'] = array(
				'interval' => 20 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 20 minutes', 'yith-woocommerce-stripe' ),
			);
			$schedules['yith_wcstripe_every_15_days']    = array(
				'interval' => 15 * DAY_IN_SECONDS,
				'display'  => __( 'Every 15 days', 'yith-woocommerce-stripe' ),
			);

			return $schedules;
		}

		/**
		 * Set up crons for the plugin
		 *
		 * @return void
		 */
		public function schedule_crons() {
			// schedule cron for expiring cards reminder.
			$expiring_card_email_preferences = get_option( 'woocommerce_expiring_card_settings', array() );

			$schedule_crons = isset( $expiring_card_email_preferences['enabled'] ) && 'yes' === $expiring_card_email_preferences['enabled'];

			/**
			 * APPLY_FILTERS: yith_wcstripe_schedule_crons
			 *
			 * Filters if should schedule expiring cards reminder crons.
			 *
			 * @param bool Default value retrieved from card email preferences.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcstripe_schedule_crons', $schedule_crons ) ) {
				if ( ! wp_next_scheduled( 'yith_wcstripe_expiring_cards_reminders_scheduler' ) ) {
					wp_schedule_event( time(), 'yith_wcstripe_every_15_days', 'yith_wcstripe_expiring_cards_reminders_scheduler' );
				}
				if ( ! wp_next_scheduled( 'yith_wcstripe_expiring_cards_reminders_dispatcher' ) ) {
					wp_schedule_event( time(), 'yith_wcstripe_3_times_for_hour', 'yith_wcstripe_expiring_cards_reminders_dispatcher' );
				}
			} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
				if ( function_exists( 'wp_unschedule_hook' ) ) {
					wp_unschedule_hook( 'yith_wcstripe_expiring_cards_reminders_scheduler' );
					wp_unschedule_hook( 'yith_wcstripe_expiring_cards_reminders_dispatcher' );
				}
			}
		}

		/**
		 * Schedule reminder emails to send
		 *
		 * @return void
		 */
		public function schedule_expiring_cards_reminders() {
			global $wpdb;

			$last_execution = get_option( 'yith_wcstripe_expiring_card_reminder_last_execution' );
			$current_month  = gmdate( 'Y-m' );

			if ( $last_execution && $last_execution >= $current_month ) {
				return;
			}

			$options = get_option( 'woocommerce_expiring_card_settings', array() );

			$months_before_expiration = isset( $options['months_before_expiration'] ) ? $options['months_before_expiration'] : 1;
			$expiration_month         = gmdate( 'Y-m', strtotime( "+{$months_before_expiration} MONTHS" ) );

			list( $exp_year, $exp_month ) = explode( '-', $expiration_month );

			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT token_id, token, user_id
					FROM {$wpdb->prefix}woocommerce_payment_tokens AS t
					LEFT JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta as tm1 ON t.token_id = tm1.payment_token_id
					LEFT JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta as tm2 ON t.token_id = tm2.payment_token_id
					WHERE tm1.meta_key = %s
					AND tm1.meta_value = %d
					AND tm2.meta_key = %s
					AND tm2.meta_value = %d",
					'expiry_month',
					$exp_month,
					'expiry_year',
					$exp_year,
				),
				ARRAY_A
			);

			if ( ! empty( $results ) ) {
				$queue = get_option( 'yith_wcstripe_expiring_card_reminder_queue', array() );
				$queue = is_array( $queue ) ? $queue : array();
				$queue = array_merge( $queue, array_combine( wp_list_pluck( $results, 'token' ), $results ) );

				update_option( 'yith_wcstripe_expiring_card_reminder_queue', $queue );
			}

			update_option( 'yith_wcstripe_expiring_card_reminder_last_execution', $current_month );
		}

		/**
		 * Dispatch reminder emails
		 *
		 * @return void
		 */
		public function dispatch_expiring_cards_reminders() {
			$options = get_option( 'woocommerce_expiring_card_settings', array() );
			$queue   = get_option( 'yith_wcstripe_expiring_card_reminder_queue', array() );

			if ( empty( $queue ) ) {
				return;
			}

			$to_execute = array_splice( $queue, 0, 10 );

			update_option( 'yith_wcstripe_expiring_card_reminder_queue', $queue );

			foreach ( $to_execute as $id => $item ) {
				if (
					isset( $options['subscribed_only'] ) &&
					'yes' === $options['subscribed_only'] &&
					function_exists( 'YITH_WC_Subscription' ) &&
					method_exists( YITH_WC_Subscription(), 'get_user_subscriptions' )
				) {
					$subscriptions = YITH_WC_Subscription()->get_user_subscriptions( $item['user_id'], 'active' );

					if ( ! $subscriptions ) {
						continue;
					}
				}

				$exclusions = isset( $options['exclusions'] ) ? explode( ',', $options['exclusions'] ) : array();
				$userdata   = get_userdata( $item['user_id'] );

				if ( ! empty( $exclusions ) && in_array( $userdata->user_email, $exclusions, true ) ) {
					continue;
				}

				/**
				 * DO_ACTION: yith_wcstripe_expiring_card
				 *
				 * Triggered after expiring cards reminder sent.
				 *
				 * @param int                 User ID.
				 * @param WC_Payment_Token_CC Payment token object.
				 */
				do_action( 'yith_wcstripe_expiring_card', $item['user_id'], new WC_Payment_Token_CC( $item['token_id'] ) );
			}
		}

		/* === AJAX METHODS === */

		/**
		 * Returns order details for hosted checkout
		 */
		public function send_checkout_details() {
			check_ajax_referer( 'refresh-details', 'yith_stripe_refresh_details', true );

			WC()->cart->calculate_totals();

			wp_send_json(
				array(
					'amount'   => $this->get_amount( WC()->cart->total ),
					'currency' => strtolower( get_woocommerce_currency() ),
				)
			);
		}

		/**
		 * Refresh intent before moving forward with checkout process
		 *
		 * @return void
		 */
		public function refresh_intent() {
			check_ajax_referer( 'refresh-intent', 'yith_stripe_refresh_intent', true );

			/**
			 * DO_ACTION: yith_wcstripe_before_refresh_intent
			 *
			 * Triggered when refreshing intent before moving forward with checkout process.
			 */
			do_action( 'yith_wcstripe_before_refresh_intent' );

			$token       = isset( $_POST['selected_token'] ) ? intval( $_POST['selected_token'] ) : false;
			$is_checkout = isset( $_POST['is_checkout'] ) ? intval( $_POST['is_checkout'] ) : false;
			$order       = isset( $_POST['order'] ) ? intval( $_POST['order'] ) : false;
			$gateway     = $this->get_gateway();

			wc_maybe_define_constant( 'YITH_WCSTRIPE_DOING_CHECKOUT', $is_checkout );

			try {
				$intent = $gateway->update_session_intent( $token, $order );
			} catch ( Exception $e ) {
				wp_send_json(
					array(
						'res'   => false,
						'error' => array(
							'code'    => $e->getCode(),
							'message' => $e->getMessage(),
						),
					)
				);
			}

			if ( ! $intent ) {
				wp_send_json(
					array(
						'res'   => false,
						'error' => array(
							'code'    => 0,
							'message' => __( 'There was an error during payment; please, try again later.', 'yith-woocommerce-stripe' ),
						),
					)
				);
			}

			wp_send_json(
				array(
					'res'           => true,
					'amount'        => isset( $intent->amount ) ? $intent->amount : 0,
					'currency'      => isset( $intent->currency ) ? $intent->currency : '',
					'intent_secret' => $intent->client_secret,
					'intent_id'     => $intent->id,
					'is_setup'      => $intent instanceof SetupIntent,
					/**
					 * APPLY_FILTERS: yith_wcstripe_reload_after_refresh_intent
					 *
					 * Filters if should reload after refreshing intent.
					 *
					 * @param bool Default value: false.
					 *
					 * @return bool
					 */
					'refresh'       => apply_filters( 'yith_wcstripe_reload_after_refresh_intent', false ),
				)
			);
		}

		/**
		 * Verify intent after customer authentication
		 * Process actions required after authentication; if everything was fine redirect to thank you page, otherwise redirects
		 * to checkout with an error message
		 *
		 * @return void
		 * @throws Exception When for whatever reason system couldn't validate intent.
		 */
		public function verify_intent() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$gateway      = $this->get_gateway();
			$order_id     = isset( $_GET['order'] ) ? intval( $_GET['order'] ) : false;
			$order_id     = ( empty( $order_id ) && isset( $_GET['order_id'] ) ) ? intval( $_GET['order_id'] ) : $order_id;
			$is_ajax      = isset( $_GET['is_ajax'] );
			$redirect_url = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : false;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			try {
				if ( ! $gateway ) {
					throw new Exception( __( 'Error while initializing gateway.', 'yith-woocommerce-stripe' ) );
				}

				// Retrieve the order.
				$order = wc_get_order( $order_id );

				if ( ! $order ) {
					throw new Exception( __( 'Missing order ID for payment confirmation.', 'yith-woocommerce-stripe' ) );
				}

				wc_maybe_define_constant( 'YITH_WCSTRIPE_DOING_CHECKOUT', true );

				$result = $gateway->pay_ajax( $order );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				if ( ! $is_ajax ) {
					$redirect_url = $redirect_url ? $redirect_url : $gateway->get_return_url( $order );

					wp_safe_redirect( $redirect_url );
				}

				exit;

			} catch ( Exception $e ) {
				// translators: 1. Error message.
				wc_add_notice( sprintf( __( 'Payment verification error: %s', 'woocommerce-gateway-stripe' ), $e->getMessage() ), 'error' );

				$redirect_url = WC()->cart->is_empty() ? wc_get_cart_url() : wc_get_checkout_url();

				if ( $is_ajax ) {
					exit;
				}

				wp_safe_redirect( $redirect_url );
				exit;
			}
		}

		/**
		 * Refresh intent before moving forward with checkout process
		 *
		 * @return void
		 */
		public function refresh_session() {
			check_ajax_referer( 'refresh-session', 'yith_stripe_refresh_session', true );

			$is_checkout = isset( $_POST['is_checkout'] ) ? intval( $_POST['is_checkout'] ) : false;
			$order       = isset( $_POST['order'] ) ? intval( $_POST['order'] ) : false;
			$gateway     = $this->get_gateway();

			wc_maybe_define_constant( 'YITH_WCSTRIPE_DOING_CHECKOUT', $is_checkout );

			if ( $is_checkout && ! $order ) {
				if ( ! class_exists( 'YITH_WCStripe_Checkout' ) ) {
					include_once YITH_WCSTRIPE_DIR . 'includes/class-yith-stripe-checkout.php';
				}

				$checkout = new YITH_WCStripe_Checkout();

				if ( ! $checkout->is_checkout_valid() ) {
					wp_send_json(
						array(
							'res'            => false,
							'checkout_valid' => false,
						)
					);
				}
			}

			try {
				$args = array();

				if ( $order ) {
					$args['order_id'] = $order;
				}

				$session = $gateway->create_checkout_session( $args );
			} catch ( Exception $e ) {
				wp_send_json(
					array(
						'res'   => false,
						'error' => array(
							'code'    => $e->getCode(),
							'message' => $e->getMessage(),
						),
					)
				);
			}

			if ( ! $session ) {
				wp_send_json(
					array(
						'res'   => false,
						'error' => array(
							'code'    => 0,
							'message' => __( 'There was an error during payment; please, try again later.', 'yith-woocommerce-stripe' ),
						),
					)
				);
			}

			wp_send_json(
				array(
					'res'        => true,
					'session_id' => $session->id,
				)
			);
		}

		/* === LICENCE HANDLING === */

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCSTRIPE_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_WCSTRIPE_INIT, YITH_WCSTRIPE_SECRET_KEY, YITH_WCSTRIPE_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_WCSTRIPE_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_WCSTRIPE_SLUG, YITH_WCSTRIPE_INIT );
		}
	}
}
