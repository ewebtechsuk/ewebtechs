<?php
/**
 * WP Grid Builder LearnDash Add-on
 *
 * @package   WP Grid Builder - LearnDash
 * @author    Loïc Blascos
 * @link      https://www.wpgridbuilder.com
 * @copyright 2019-2024 Loïc Blascos
 *
 * @wordpress-plugin
 * Plugin Name:  WP Grid Builder - LearnDash
 * Plugin URI:   https://www.wpgridbuilder.com
 * Description:  Add new blocks to the card builder to display courses information.
 * Version:      1.3.0
 * Author:       Loïc Blascos
 * Author URI:   https://www.wpgridbuilder.com
 * License:      GPL-3.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:  wpgb-learndash
 * Domain Path:  /languages
 */

namespace WP_Grid_Builder_LearnDash;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGB_LEARNDASH_VERSION', '1.3.0' );
define( 'WPGB_LEARNDASH_FILE', __FILE__ );
define( 'WPGB_LEARNDASH_BASE', plugin_basename( WPGB_LEARNDASH_FILE ) );
define( 'WPGB_LEARNDASH_PATH', plugin_dir_path( WPGB_LEARNDASH_FILE ) );
define( 'WPGB_LEARNDASH_URL', plugin_dir_url( WPGB_LEARNDASH_FILE ) );

require_once WPGB_LEARNDASH_PATH . 'includes/class-autoload.php';

/**
 * Load plugin text domain.
 *
 * @since 1.0.0
 */
function textdomain() {

	load_plugin_textdomain(
		'wpgb-learndash',
		false,
		basename( dirname( WPGB_LEARNDASH_FILE ) ) . '/languages'
	);

}
add_action( 'plugins_loaded', __NAMESPACE__ . '\textdomain' );

/**
 * Plugin compatibility notice.
 *
 * @since 1.0.0
 */
function admin_notice() {

	$notice = __( '<strong>Gridbuilder ᵂᴾ - LearnDash</strong> add-on requires at least <code>Gridbuilder ᵂᴾ v1.1.5</code>. Please update Gridbuilder ᵂᴾ to use LearnDash add-on.', 'wpgb-learndash' );

	echo '<div class="error">' . wp_kses_post( wpautop( $notice ) ) . '</div>';

}

/**
 * Initialize plugin
 *
 * @since 1.0.0
 */
function loaded() {

	if ( version_compare( WPGB_VERSION, '1.1.5', '<' ) ) {

		add_action( 'admin_notices', __NAMESPACE__ . '\admin_notice' );
		return;

	}

	new Includes\Plugin();

}
add_action( 'wp_grid_builder/loaded', __NAMESPACE__ . '\loaded' );
