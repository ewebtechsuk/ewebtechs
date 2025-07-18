<?php
/**
 * Plugin Name: YITH WooCommerce Stripe Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-stripe/
 * Description: <code><strong>YITH WooCommerce Stripe</strong></code> allows your users to pay with credit cards thanks to the integration with Stripe, a powerful and flexible payment gateway. You will be able to get payments with credit cards while assuring your users of the reliability of an international partner. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>.
 * Version: 3.34.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-stripe
 * Domain Path: /languages
 * WC requires at least: 9.7
 * WC tested up to: 9.9
 * Requires Plugins: woocommerce
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe
 * @version 3.34.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! defined( 'YITH_WCSTRIPE_PREMIUM' ) ) {
	define( 'YITH_WCSTRIPE_PREMIUM', true );
}

if ( ! defined( 'YITH_WCSTRIPE_PLUGIN_NAME' ) ) {
	define( 'YITH_WCSTRIPE_PLUGIN_NAME', 'YITH WooCommerce Stripe' );
}

if ( defined( 'YITH_WCSTRIPE_VERSION' ) ) {
	return;
} else {
	define( 'YITH_WCSTRIPE_VERSION', '3.34.0' );
}

if ( ! defined( 'YITH_WCSTRIPE_API_VERSION' ) ) {
	define( 'YITH_WCSTRIPE_API_VERSION', '2024-06-20' );
}

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	define( 'YITH_WCSTRIPE', true );
}

if ( ! defined( 'YITH_WCSTRIPE_FILE' ) ) {
	define( 'YITH_WCSTRIPE_FILE', __FILE__ );
}

if ( ! defined( 'YITH_WCSTRIPE_URL' ) ) {
	define( 'YITH_WCSTRIPE_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCSTRIPE_DIR' ) ) {
	define( 'YITH_WCSTRIPE_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_WCSTRIPE_INC' ) ) {
	define( 'YITH_WCSTRIPE_INC', YITH_WCSTRIPE_DIR . 'includes/' );
}

if ( ! defined( 'YITH_WCSTRIPE_INIT' ) ) {
	define( 'YITH_WCSTRIPE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCSTRIPE_SLUG' ) ) {
	define( 'YITH_WCSTRIPE_SLUG', 'yith-woocommerce-stripe' );
}

if ( ! defined( 'YITH_WCSTRIPE_SECRET_KEY' ) ) {
	define( 'YITH_WCSTRIPE_SECRET_KEY', '' );
}

if ( ! defined( 'YITH_WCSTRIPE_VIEWS' ) ) {
	define( 'YITH_WCSTRIPE_VIEWS', YITH_WCSTRIPE_DIR . 'views/' );
}

// Woocommerce installation check.

if ( ! function_exists( 'WC' ) ) {
	/**
	 * Prints admin notice if plugin is installed without WooCommerce
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function yith_stripe_premium_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p>
				<?php
					// translators: %s is the plugin name.
					echo esc_html( sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-stripe' ), YITH_WCSTRIPE_PLUGIN_NAME ) );
				?>
			</p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'yith_stripe_premium_install_woocommerce_admin_notice' );
	return;
}

// Free version deactivation if installed.

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_WCSTRIPE_FREE_INIT', plugin_basename( __FILE__ ) );

// Register WP_Pointer Handling.

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

if ( ! function_exists( 'YITH_WCStripe' ) ) {
	/**
	 * Unique access to instance of YITH_WCStripe class
	 *
	 * @return \YITH_WCStripe|YITH_WCStripe_Premium
	 * @since 1.0.0
	 */
	function yith_wcstripe() {
		// Load required classes and functions.
		require_once YITH_WCSTRIPE_INC . 'class-yith-stripe.php';

		if ( defined( 'YITH_WCSTRIPE_PREMIUM' ) && file_exists( YITH_WCSTRIPE_INC . 'class-yith-stripe-premium.php' ) ) {
			require_once YITH_WCSTRIPE_INC . 'class-yith-stripe-premium.php';
			return YITH_WCStripe_Premium::get_instance();
		}

		return YITH_WCStripe::get_instance();
	}
}

if ( ! function_exists( 'yith_stripe_constructor' ) ) {
	/**
	 * Starts plugin on plugins_loaded hook
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function yith_stripe_constructor() {
		if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
			yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-stripe', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		yith_wcstripe();
	}
}
add_action( 'plugins_loaded', 'yith_stripe_constructor' );
