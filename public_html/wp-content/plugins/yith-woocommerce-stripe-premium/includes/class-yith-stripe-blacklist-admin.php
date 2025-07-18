<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Blacklist handling class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Blacklist_Admin' ) ) {
	/**
	 * Blacklist Admin Pages
	 *
	 * @since 1.1.3
	 */
	class YITH_WCStripe_Blacklist_Admin {

		/**
		 * Constructor.
		 *
		 * @since 1.1.3
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 15 );
			add_filter( 'woocommerce_screen_ids', array( $this, 'set_blacklist_table_wc_page' ) );

			// YITH Plugins tab.
			add_action( 'yith_wcstripe_blacklist_tab', array( $this, 'blacklist_page' ) );

			add_action( 'admin_init', array( $this, 'update_blacklist_status' ) );
			// Loads Blacklist table class.
			add_action( 'admin_init', array( $this, 'load_blacklist_table_class' ), 5 );
		}

		/**
		 * Load the blacklist table class to be able to acces its methods.
		 *
		 * @return void
		 */
		public function load_blacklist_table_class() {
			if ( ! class_exists( 'YITH_Stripe_Blacklist_Table' ) ) {
				include_once 'class-yith-stripe-blacklist-table.php';
			}
		}

		/**
		 * Update blacklist status
		 */
		public function update_blacklist_status() {
			if ( empty( $_GET['id'] ) || empty( $_GET['action'] ) || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'update_blacklist_status' ) ) {
				return;
			}

			$rec    = isset( $_GET['id'] ) ? array_map( 'intval', (array) $_GET['id'] ) : false;
			$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false;

			$blacklist_table = YITH_Stripe_Blacklist_Table::get_instance();
			$blacklist_table->change_banned_status( $rec, $action );
		}

		/**
		 * Add the Commissions menu item in dashboard menu
		 *
		 * @return void
		 * @since  1.1.2
		 */
		public function add_menu_item() {
			$args = array(
				'page_title' => __( 'Stripe blacklist', 'yith-woocommerce-stripe' ),
				'menu_title' => __( 'Stripe blacklist', 'yith-woocommerce-stripe' ),
				'capability' => 'manage_woocommerce',
				'menu_slug'  => 'yith_stripe_blacklist',
				'function'   => array( $this, 'blacklist_page' ),
			);

			list( $page_title, $menu_title, $capability, $menu_slug, $function ) = yith_plugin_fw_extract( $args, 'page_title', 'menu_title', 'capability', 'menu_slug', 'function' );

			add_submenu_page( 'woocommerce', $page_title, $menu_title, $capability, $menu_slug, $function );
		}

		/**
		 * Show the Commissions page
		 *
		 * @return void
		 * @since  1.1.2
		 */
		public function blacklist_page() {
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}

			$blacklist_table = YITH_Stripe_Blacklist_Table::get_instance();
			$blacklist_table->prepare_items();

			include YITH_WCSTRIPE_VIEWS . '/blacklist-table.php';
		}

		/**
		 * Check if the current admin page is the blacklist page
		 *
		 * @return bool
		 * @since 1.1.3
		 */
		public function is_blacklist_page() {
			$screen = get_current_screen();

			return false !== strpos( $screen->id, 'yith_stripe_blacklist' ) || isset( $_GET['page'] ) && 'yith_wcstripe_panel' === $_GET['page'] && isset( $_GET['tab'] ) && 'blacklist' === $_GET['tab']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Returns url to blacklist page
		 *
		 * @return string
		 * @since 1.1.3
		 */
		public function blacklist_page_url() {
			if ( isset( $_GET['page'] ) && 'yith_wcstripe_panel' === $_GET['page'] && isset( $_GET['tab'] ) && 'blacklist' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return admin_url( 'admin.php?page=yith_wcstripe_panel&tab=blacklist' );
			} else {
				return admin_url( 'admin.php?page=yith_stripe_blacklist' );
			}
		}

		/**
		 * Include CSS
		 *
		 * @return void
		 * @since 1.1.3
		 */
		public function enqueue_style() {
			if ( ! $this->is_blacklist_page() ) {
				return;
			}

			wp_enqueue_style( 'blacklist-admin', YITH_WCSTRIPE_URL . 'assets/css/admin.css', array(), YITH_WCSTRIPE_VERSION );
		}

		/**
		 * Set the page with blacklist table as woocommerce admin page
		 *
		 * @param array $screen_ids Array of screens supported by WooCommerce.
		 *
		 * @return array
		 * @since 1.1.3
		 */
		public function set_blacklist_table_wc_page( $screen_ids ) {
			$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

			$screen_ids[] = $wc_screen_id . '_page_yith_stripe_blacklist';
			$screen_ids[] = $wc_screen_id . '_page_yith_stripe_blacklist';

			return $screen_ids;
		}
	}
}

new YITH_WCStripe_Blacklist_Admin();
