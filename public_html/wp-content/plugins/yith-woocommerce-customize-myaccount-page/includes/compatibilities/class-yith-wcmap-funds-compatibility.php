<?php
/**
 * YITH WooCommerce Account Funds Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Customize My Account Page
 * @version 1.0.0
 */

defined( 'YITH_WCMAP' ) || exit;

if ( ! class_exists( 'YITH_WCMAP_Funds_Compatibility' ) ) {
	/**
	 * Class YITH_WCMAP_Funds_Compatibility
	 *
	 * @since 3.0.0
	 */
	class YITH_WCMAP_Funds_Compatibility extends YITH_WCMAP_Compatibility {

		/**
		 * Constructor
		 *
		 * @since 3.0.0
		 */
		public function __construct() {

			if( defined( 'YITH_FUNDS_VERSION') && version_compare( YITH_FUNDS_VERSION, '2.0.0', '>=' ) ) {
				$this->endpoint_key = array( 'funds' );
				$this->endpoint     = array(
					'funds'             => array(
						'slug'    => 'funds',
						'label'   => __( 'Funds Dashboard', 'yith-woocommerce-customize-myaccount-page' ),
						'icon'    => 'money',
					)
				);
			}else{
				$this->endpoint_key = array( 'make-a-deposit', 'income-expenditure-history' );
				$this->endpoint     = array(
					'make-a-deposit'             => array(
						'slug'    => 'make-a-deposit',
						'label'   => __( 'Make a Deposit', 'yith-woocommerce-customize-myaccount-page' ),
						'icon'    => 'money',
						'content' => '[yith_ywf_make_a_deposit_endpoint]',
					),
					'income-expenditure-history' => array(
						'slug'    => 'income-expenditure-history',
						'label'   => __( 'Income/Expenditure History', 'yith-woocommerce-customize-myaccount-page' ),
						'icon'    => 'list-ol',
						'content' => '[yith_ywf_show_history pagination="yes"]',
					),
				);
			}


			// Register endpoint.
			$this->register_endpoint();
		}
	}
}
