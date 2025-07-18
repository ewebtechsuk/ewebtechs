<?php
/**
 * Plugin
 *
 * @package   WP Grid Builder - LearnDash
 * @author    Loïc Blascos
 * @copyright 2019-2024 Loïc Blascos
 */

namespace WP_Grid_Builder_LearnDash\Includes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Instance of the plugin
 *
 * @class WP_Grid_Builder_LearnDash\Includes\Plugin
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		add_action( 'wp_grid_builder/init', [ $this, 'init' ] );
		add_filter( 'wp_grid_builder/register', [ $this, 'register' ] );
		add_filter( 'wp_grid_builder/plugin_info', [ $this, 'plugin_info' ], 10, 2 );
		add_filter( 'wp_grid_builder/demos', [ $this, 'add_demos' ] );
		add_filter( 'wp_grid_builder/cards_demo', [ $this, 'add_cards_demo' ] );
		add_filter( 'wp_grid_builder_i18n/card/register_strings', [ $this, 'register_strings' ] );
	}

	/**
	 * Init instances
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {

		if ( ! $this->is_compatible() ) {

			add_action( 'admin_notices', [ $this, 'admin_notice' ] );
			return;

		}

		include_once WPGB_LEARNDASH_PATH . 'includes/blocks/functions.php';

		new Builder();
		new Post();

	}

	/**
	 * Register add-on
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $addons Holds registered add-ons.
	 * @return array
	 */
	public function register( $addons ) {

		$addons[] = [
			'name'    => 'LearnDash',
			'slug'    => WPGB_LEARNDASH_BASE,
			'option'  => 'wpgb_learndash',
			'version' => WPGB_LEARNDASH_VERSION,
		];

		return $addons;

	}

	/**
	 * Set plugin info
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $info Holds plugin info.
	 * @param string $name Current plugin name.
	 * @return array
	 */
	public function plugin_info( $info, $name ) {

		if ( 'LearnDash' !== $name ) {
			return $info;
		}

		$info['icons'] = [
			'1x' => WPGB_LEARNDASH_URL . 'assets/imgs/icon.png',
			'2x' => WPGB_LEARNDASH_URL . 'assets/imgs/icon.png',
		];

		if ( ! empty( $info['info'] ) ) {

			$info['info']->banners = [
				'low'  => WPGB_LEARNDASH_URL . 'assets/imgs/banner.png',
				'high' => WPGB_LEARNDASH_URL . 'assets/imgs/banner.png',
			];
		}

		return $info;

	}

	/**
	 * Add demos (V2)
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param array $demos Holds demos (grid, cards, facets).
	 * @return array
	 */
	public function add_demos( $demos ) {

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$demo = @file_get_contents( WPGB_LEARNDASH_PATH . 'assets/json/cards.json' );

		if ( false !== $demo ) {
			$demos = array_merge_recursive( $demos, json_decode( $demo, true ) );
		}

		return $demos;

	}

	/**
	 * Add LearnDash cards to cards demo
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $data JSON demo content.
	 * @retrun string JSON demo content.
	 */
	public function add_cards_demo( $data ) {

		if ( ! $this->is_compatible() ) {
			return $data;
		}

		$file = WPGB_LEARNDASH_PATH . 'assets/json/cards.json';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json = file_get_contents( $file );
		$json = json_decode( $json, true );
		$data = json_decode( $data, true );
		$data = array_merge_recursive( $data, $json );

		return wp_json_encode( $data );

	}

	/**
	 * Register card strings
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $registry Holds string ids to translate.
	 * @retrun array
	 */
	public function register_strings( $registry ) {

		$strings = [
			'ld_course_progress_text'           => [],
			'ld_course_activity_text'           => [],
			'ld_course_steps_singlular'         => [],
			'ld_course_steps_plural'            => [],
			'ld_course_lessons_singlular'       => [],
			'ld_course_lessons_plural'          => [],
			'ld_course_status_unenrolled_label' => [],
			'ld_course_status_enrolled_label'   => [],
			'ld_course_status_progress_label'   => [],
			'ld_course_status_complete_label'   => [],
			'ld_course_button_default_text'     => [],
			'ld_lesson_access_from_text'        => [],
		];

		return array_merge( $registry, $strings );

	}

	/**
	 * Check compatibility with LearnDash plugin
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean
	 */
	public function is_compatible() {

		return class_exists( 'SFWD_LMS' ) && defined( 'LEARNDASH_VERSION' ) && version_compare( LEARNDASH_VERSION, '3.0.0', '>=' );

	}

	/**
	 * LearnDash compatibility notice.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice() {

		$notice = __( '<strong>Gridbuilder ᵂᴾ - LearnDash</strong> add-on requires at least <code>LearnDash v3.0.0</code>. Please update or activate LearnDash plugin to use LearnDash add-on.', 'wpgb-learndash' );

		echo '<div class="error">' . wp_kses_post( wpautop( $notice ) ) . '</div>';

	}
}
