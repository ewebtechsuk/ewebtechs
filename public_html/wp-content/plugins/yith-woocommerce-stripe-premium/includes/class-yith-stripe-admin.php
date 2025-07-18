<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Admin' ) ) {
	/**
	 * WooCommerce Stripe main class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Admin {

		/**
		 * Instance of panel object
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// register gateway panel.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_woocommerce_stripe_premium', array( $this, 'premium_tab' ) );

			// admin notices.
			add_action( 'admin_notices', array( $this, 'add_notices' ) );
			add_action( 'admin_action_yith_wcstripe_handle_warning_state', array( $this, 'handle_warning_state' ) );

			// register panel.
			$action = 'yith_wcstripe_gateway';
			if ( defined( 'YITH_WCSTRIPE_PREMIUM' ) ) {
				$action .= YITH_WCStripe_Premium::addons_installed() ? '_addons' : '_advanced';
			}
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'capture_status' ) );

			// Add action links.
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCSTRIPE_DIR . 'init.php' ), array( $this, 'action_links' ) );

			// Print webhook section.
			add_action( 'yith_stripe_webhook_section', array( $this, 'yith_stripe_print_webhook_section' ) );

			// Change URL on manage button in WC payment options.
			add_filter( 'admin_init', array( $this, 'redirect_stripe_panel' ) );
		}


		/**
		 * Register subpanel for YITH Stripe into YI Plugins panel
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_panel() {
			/**
			 * APPLY_FILTERS: yith_stripe_admin_panels
			 *
			 * Filters subpanel for YITH Stripe into YI Plugins panel
			 *
			 * @param array Array of subpanels.
			 *
			 * @return array
			 */
			$admin_tabs = apply_filters(
				'yith_stripe_admin_panels',
				array(
					'settings' => __( 'Settings', 'yith-woocommerce-stripe' ),
				)
			);

			// add blacklist tab when needed.
			$option = get_option( 'woocommerce_yith-stripe_settings' );

			if ( isset( $option['enable_blacklist'] ) && 'yes' === $option['enable_blacklist'] ) {
				$admin_tabs['blacklist'] = __( 'Blacklist', 'yith-woocommerce-stripe' );
			}

			if ( defined( 'YITH_WCSTRIPE_FREE_INIT' ) ) {
				$admin_tabs['premium'] = __( 'Premium version', 'yith-woocommerce-stripe' );
			}

			// add the subscription integration tab only if the premium version is active.
			if ( defined( 'YITH_YWSBS_PREMIUM' ) && defined( 'YITH_YWSBS_VERSION' ) && version_compare( YITH_YWSBS_VERSION, '1.4.6', '>=' ) ) {
				$admin_tabs['subscriptions'] = __( 'Subscriptions', 'yith-woocommerce-stripe' );
			}

			$args = apply_filters(
				'yith_wcstripe_admin_menu_args',
				array(
					'create_menu_page' => true,
					'parent_slug'      => '',
					'page_title'       => 'YITH WooCommerce Stripe',
					'menu_title'       => 'Stripe',
					'capability'       => 'manage_options',
					'parent'           => '',
					'parent_page'      => 'yith_plugin_panel',
					'class'            => yith_set_wrapper_class(),
					'page'             => 'yith_wcstripe_panel',
					'admin-tabs'       => $admin_tabs,
					'options-path'     => YITH_WCSTRIPE_DIR . 'plugin-options',
					'plugin_slug'      => YITH_WCSTRIPE_SLUG,
					'is_premium'       => defined( 'YITH_WCSTRIPE_PREMIUM' ),
					'help_tab'         => array(
						'hc_url' => 'https://support.yithemes.com/hc/en-us/categories/360003469697-YITH-WOOCOMMERCE-STRIPE',
					),
				)
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WCSTRIPE_DIR . 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Print warning notice when system detects an url change
		 *
		 * @return void
		 * @since 1.8.2
		 */
		public function add_notices() {
			$site_changed = get_option( 'yith_wcstripe_site_changed', 'no' );

			if ( 'no' === $site_changed ) {
				return;
			}

			$keep_test_url = add_query_arg(
				array(
					'action'  => 'yith_wcstripe_handle_warning_state',
					'request' => 'keep_test',
				),
				admin_url( 'admin.php' )
			);
			$set_live_url  = add_query_arg(
				array(
					'action'  => 'yith_wcstripe_handle_warning_state',
					'request' => 'set_live',
				),
				admin_url( 'admin.php' )
			);

			?>
			<div class="notice notice-warning">
				<p>
					<?php echo wp_kses_post( __( 'The base URL has been changed, so YITH WooCommerce Stripe has been switched to <b>Test Mode</b> to prevent any unexpected transactions on the new URL.<br/>Choose what to do now:', 'yith-woocommerce-stripe' ) ); ?>
				</p>
				<p class="submit">
					<a class="button-secondary" href="<?php echo esc_url( $keep_test_url ); ?>">
						<?php esc_html_e( 'Keep test mode', 'yith-woocommerce-stripe' ); ?>
					</a>
					<a class="button-secondary" href="<?php echo esc_url( $set_live_url ); ?>">
						<?php esc_html_e( 'Set live mode', 'yith-woocommerce-stripe' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		/**
		 * Handle actions for warning state
		 *
		 * @return void
		 * @since 1.8.2
		 */
		public function handle_warning_state() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$redirect = add_query_arg( 'page', 'yith_wcstripe_panel', admin_url( 'admin.php' ) );

			if ( ! isset( $_GET['request'] ) || empty( $_GET['request'] ) || ! in_array( $_GET['request'], array( 'keep_test', 'set_live' ), true ) ) {
				wp_safe_redirect( $redirect );
				die;
			}

			$gateway_id      = YITH_WCStripe::$gateway_id;
			$gateway_options = get_option( "woocommerce_{$gateway_id}_settings", array() );
			$site_changed    = get_option( 'yith_wcstripe_site_changed', 'no' );

			if ( 'no' === $site_changed ) {
				wp_safe_redirect( $redirect );
				die;
			}

			$request = sanitize_text_field( wp_unslash( $_GET['request'] ) );

			if ( 'set_live' === $request ) {
				$gateway_options['enabled_test_mode'] = 'no';
				update_option( "woocommerce_{$gateway_id}_settings", $gateway_options );
			}
			update_option( 'yith_wcstripe_site_changed', 'no' );
			update_option( 'yith_wcstripe_registered_url', get_site_url() );

			wp_safe_redirect( $redirect );
			die;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Add capture information in order box
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @since 1.0.0
		 */
		public function capture_status( $order ) {
			if ( $order->get_payment_method() !== YITH_WCStripe::$gateway_id ) {
				return;
			}

			?>
			<div style="clear:both"></div>
			<h4><?php esc_html_e( 'Authorize & Capture status', 'yith-woocommerce-stripe' ); ?></h4>
			<p class="form-field form-field-wide order-captured">
				<?php
				$captured = 'yes' === $order->get_meta( '_captured' );
				$paid     = $order->get_date_paid( 'edit' );

				if ( $paid ) {
					echo $captured ? esc_html__( 'Captured', 'yith-woocommerce-stripe' ) : esc_html__( 'Authorized only (not captured yet)', 'yith-woocommerce-stripe' );
				} else {
					esc_html_e( 'N/A', 'yith-woocommerce-stripe' );
				}
				?>
			</p>
			<?php
		}

		/**
		 * Add the action links to plugin admin page
		 *
		 * @param array $links Array of plugin links.
		 * @return array
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, 'yith_wcstripe_panel', defined( 'YITH_WCSTRIPE_PREMIUM' ), YITH_WCSTRIPE_SLUG );

			return $links;
		}

		/**
		 * Add custom links to plugin meta row on admin
		 *
		 * @param array  $new_row_meta_args Array of meta for current plugin.
		 * @param array  $plugin_meta Not in use.
		 * @param string $plugin_file Current plugin iit file path.
		 * @param array  $plugin_data Plugin info.
		 * @param string $status Plugin status.
		 * @param string $init_file Wishlist plugin init file.
		 * @return array
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCSTRIPE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WCSTRIPE_SLUG;

			}

			if ( defined( 'YITH_WCSTRIPE_PREMIUM' ) ) {
				$new_row_meta_args['is_premium'] = true;

			}

			return $new_row_meta_args;
		}

		/**
		 * Print Webhook section in the Settings panel
		 *
		 * @return void
		 */
		public function yith_stripe_print_webhook_section() {
			yith_stripe_get_view( 'webhooks-section.php' );
		}

		/**
		 * Redirect to Stripe panel when visiting the Stripe gateway in WC settings
		 */
		public function redirect_stripe_panel() {
			if ( isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'checkout' === $_GET['tab'] && 'yith-stripe' === $_GET['section'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$panel_url = add_query_arg( array( 'page' => 'yith_wcstripe_panel' ), admin_url( 'admin.php' ) );

				wp_safe_redirect( $panel_url );
				exit;
			}
		}
	}
}
