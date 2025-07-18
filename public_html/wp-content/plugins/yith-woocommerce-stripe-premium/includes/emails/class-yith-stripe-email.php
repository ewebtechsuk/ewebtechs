<?php
/**
 * Abstract for all plugin emails
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes\Emails
 */

if ( ! class_exists( 'YITH_Stripe_Email' ) ) {
	/**
	 * YITH_Stripe_Email class
	 */
	abstract class YITH_Stripe_Email extends WC_Email {

		/**
		 * Check if the request is for the email preview
		 */
		protected function is_email_preview() {
			return has_filter( 'woocommerce_is_email_preview' );
		}

		/**
		 * Get a dummy product.
		 *
		 * @return WC_Product
		 */
		protected function get_dummy_product() {
			$product = new WC_Product();
			$product->set_id( 62345 );
			$product->set_name( __( 'Dummy Product', 'woocommerce' ) );
			$product->set_price( 25 );

			return $product;
		}

		/**
		 * Get a dummy address.
		 *
		 * @return array
		 */
		protected function get_dummy_address() {
			$address = array(
				'first_name' => 'John',
				'last_name'  => 'Doe',
				'company'    => 'Company',
				'email'      => 'john@company.com',
				'phone'      => '555-555-5555',
				'address_1'  => '123 Fake Street',
				'city'       => 'Faketown',
				'postcode'   => '12345',
				'country'    => 'US',
				'state'      => 'CA',
			);

			return $address;
		}

		/**
		 * Get a dummy order object without the need to create in the database.
		 *
		 * @return WC_Order
		 */
		protected function get_dummy_order() {
			$product = $this->get_dummy_product();

			$order = new WC_Order();
			$order->add_product( $product, 2 );
			$order->set_id( 42345 );
			$order->set_date_created( time() );
			$order->set_currency( 'USD' );
			$order->set_shipping_total( 5 );
			$order->set_total( 65 );
			$order->set_payment_method_title( __( 'Direct bank transfer', 'woocommerce' ) );

			$address = $this->get_dummy_address();
			$order->set_billing_address( $address );
			$order->set_shipping_address( $address );

			return $order;
		}

		/**
		 * Get email order
		 *
		 * @return bool|object
		 */
		public function get_order() {
			return $this->is_email_preview() ? $this->get_dummy_order() : $this->order;
		}

		/**
		 * Get card type
		 *
		 * @param WC_Payment_Token_CC $token Card token.
		 *
		 * @return string
		 */
		public function get_card_type( $token ) {
			return $this->is_email_preview() ? $this->get_dummy_card_type() : $token->get_card_type();
		}

		/**
		 * Get dummy card type
		 *
		 * @return string
		 */
		protected function get_dummy_card_type() {
			return 'visa';
		}

		/**
		 * Get token expiry year
		 *
		 * @param WC_Payment_Token_CC $token Card token.
		 *
		 * @return string
		 */
		public function get_expiry_year( $token ) {
			return $this->is_email_preview() ? $this->get_dummy_token_expiry_year() : $token->get_expiry_year();
		}

		/**
		 * Get dummy expiry year for the card token
		 *
		 * @return string
		 */
		public function get_dummy_token_expiry_year() {
			return gmdate( 'Y', strtotime( '+1 year' ) );
		}

		/**
		 * Get token expiry month
		 *
		 * @param WC_Payment_Token_CC $token Card token.
		 *
		 * @return string
		 */
		public function get_expiry_month( $token ) {
			return $this->is_email_preview() ? $this->get_dummy_token_expiry_month() : $token->get_expiry_month();
		}

		/**
		 * Get dummy expiry month for the card token
		 *
		 * @return string
		 */
		public function get_dummy_token_expiry_month() {
			return '6';
		}

		/**
		 * Get token last 4 digits
		 *
		 * @param WC_Payment_Token_CC $token Card token.
		 *
		 * @return string
		 */
		public function get_last4( $token ) {
			return $this->is_email_preview() ? $this->get_dummy_last4() : $token->get_last4();
		}

		/**
		 * Get dummy token last 4 digits
		 *
		 * @return string
		 */
		protected function get_dummy_last4() {
			return '5712';
		}

		/**
		 * Get username
		 *
		 * @param WP_User $userdata User object.
		 *
		 * @return string
		 */
		public function get_username( $userdata ) {
			return $this->is_email_preview() ? $this->get_dummy_username() : ( $userdata->first_name ? $userdata->first_name : $userdata->display_name );
		}

		/**
		 * Get dummy username
		 *
		 * @return string
		 */
		protected function get_dummy_username() {
			return 'John';
		}
	}
}
