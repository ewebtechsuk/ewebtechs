<?php
/**
 * Registers Stripe Gateway for usage in WooCommerce Checkout block
 *
 * @author  YITH
 * @package YITH\StripePayments\Classes\Blocks\PaymentTypes
 * @version 2.0.0
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined( 'YITH_WCSTRIPE' ) || exit;

if ( ! class_exists( 'YITH_Stripe_Payment_Method_Type' ) ) {
	/**
	 * Main gateway class of the plugin
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Payment_Method_Type extends AbstractPaymentMethodType {
		/**
		 * Instance of the gateway
		 *
		 * @var YITH_WCStripe_Gateway
		 */
		private $gateway;

		/**
		 * When called invokes any initialization/setup for the integration.
		 */
		public function initialize() {
			$this->gateway = YITH_WCStripe()->get_gateway();
			$this->name    = ! ! $this->gateway ? $this->gateway->get_title() : '';
		}

		/**
		 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
		 *
		 * @return boolean
		 */
		public function is_active() {
			return ! ! $this->gateway ? $this->gateway->is_enabled() : false;
		}

		/**
		 * Returns an array of script handles to enqueue for this payment method in
		 * the frontend context
		 *
		 * @return string[]
		 */
		public function get_payment_method_script_handles() {
			$this->gateway->register_payment_scripts();

			return array(
				'stripe-js',
				'yith-stripe-block-js',
			);
		}

		/**
		 * Returns an array of script handles to enqueue in the admin context.
		 *
		 * @return string[]
		 */
		public function get_payment_method_script_handles_for_admin() {
			return $this->get_payment_method_script_handles();
		}
	}
}
