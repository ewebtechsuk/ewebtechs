<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class representing customer
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Customer' ) ) {
	/**
	 * Class representing customer
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Customer {

		/**
		 * Current environment (live|test)
		 *
		 * @var string
		 */
		public $env;

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCStripe_Customer
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCStripe_Customer
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
		 * @return string
		 * @since 1.0.0
		 */
		public function get_env() {
			if ( empty( $this->env ) ) {
				// Load form_field settings.
				$settings  = get_option( 'woocommerce_' . YITH_WCStripe::$gateway_id . '_settings', null );
				$this->env = isset( $settings['enabled_test_mode'] ) && 'yes' === $settings['enabled_test_mode'] ? 'test' : 'live';
			}

			return $this->env;
		}

		/**
		 * Get customer info for a user into DB
		 *
		 * @param int $user_id WordPress used id.
		 *
		 * @since 1.0.0
		 */
		public function get_usermeta_info( $user_id ) {
			return get_user_meta( $user_id, $this->get_customer_usermeta_key(), true );
		}

		/**
		 * Update customer info for a user into DB
		 *
		 * @param int   $user_id WordPress used id.
		 * @param array $params  Data to update on local customer profile.
		 *
		 * @since 1.0.0
		 */
		public function update_usermeta_info( $user_id, $params = array() ) {
			return update_user_meta( $user_id, $this->get_customer_usermeta_key(), $params );
		}

		/**
		 * Delete customer info for a user into DB
		 *
		 * @param int $user_id WordPress used id.
		 *
		 * @since 1.0.0
		 */
		public function delete_usermeta_info( $user_id ) {
			return delete_user_meta( $user_id, $this->get_customer_usermeta_key() );
		}

		/**
		 * Update customer info for a user into DB
		 *
		 * @param int $user_id WordPress used id.
		 *
		 * @since 1.0.0
		 */
		public function want_save_cards( $user_id ) {
			$info = $this->get_usermeta_info( $user_id );

			return 'yes' === $info['save_cards'];
		}

		/**
		 * Return the name of user meta for the customer info
		 *
		 * @return string
		 * @since 1.0.0
		 */
		protected function get_customer_usermeta_key() {
			return '_' . $this->get_env() . '_stripe_customer_id';
		}
	}
}

/**
 * Unique access to instance of YITH_WCStripe_Customer class
 *
 * @return \YITH_WCStripe_Customer
 * @since 1.0.0
 */
function yith_wcstripe_customer() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed
	return YITH_WCStripe_Customer::get_instance();
}
