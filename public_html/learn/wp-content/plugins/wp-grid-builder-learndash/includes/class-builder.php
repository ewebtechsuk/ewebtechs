<?php
/**
 * Builder
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
 * Handle builder blocks
 *
 * @class WP_Grid_Builder_LearnDash\Includes\Builder
 * @since 1.0.0
 */
final class Builder {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		add_filter( 'wp_grid_builder/blocks', [ $this, 'register_blocks' ] );
		add_filter( 'wp_grid_builder/block_categories', [ $this, 'block_categories' ] );
		add_filter( 'wp_grid_builder/block/types', [ $this, 'register_block_type' ] );
		add_filter( 'wp_grid_builder/block/sources', [ $this, 'register_block_source' ] );
		add_filter( 'wp_grid_builder/settings/block_fields', [ $this, 'register_block_fields' ] );
		add_filter( 'wp_grid_builder/builder/register_scripts', [ $this, 'register_script' ] );
		add_action( 'wp_grid_builder/admin/enqueue_script', [ $this, 'enqueue_script' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'localize_script' ], 99 );

	}

	/**
	 * Check if Course Grid add-on is activated
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean
	 */
	public function has_course_grid() {

		return (
			( defined( 'LEARNDASH_COURSE_GRID_VERSION' ) && ! empty( LEARNDASH_COURSE_GRID_VERSION ) ) ||
			( defined( 'LEARNDASH_COURSE_GRID_FILE' ) && ! empty( LEARNDASH_COURSE_GRID_FILE ) )
		);
	}

	/**
	 * Insert in array from key name
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $arr Array to insert to.
	 * @param array  $new Array to insert.
	 * @param string $key Key to insert from.
	 * @return array
	 */
	public function insert_at( $arr, $new, $key ) {

		$index = array_search( $key, array_keys( $arr ), true );
		$index = $index ? $index + 1 : count( $arr );

		return array_merge( array_slice( $arr, 0, $index ), $new, array_slice( $arr, $index ) );

	}

	/**
	 * Registrer builder blocks
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $blocks Holds custom blocks.
	 * @return array
	 */
	public function register_blocks( $blocks ) {

		$ld_blocks = include WPGB_LEARNDASH_PATH . 'includes/blocks/blocks.php';

		return array_merge( $blocks, $ld_blocks );

	}

	/**
	 * Registrer block category
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param array $categories Holds registered block categories.
	 * @return array
	 */
	public function block_categories( $categories ) {

		array_push(
			$categories,
			[
				'slug'  => 'learndash_blocks',
				'title' => 'LearnDash',
			]
		);

		return $categories;

	}

	/**
	 * Registrer block type
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $types Holds builder block types.
	 * @return array
	 */
	public function register_block_type( $types ) {

		$entry = [ 'learndash_blocks' => esc_html__( 'LearnDash Blocks', 'wpgb-learndash' ) ];

		return $this->insert_at( $types, $entry, 'term_blocks' );

	}

	/**
	 * Registrer block source
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $sources Holds builder block sources.
	 * @return array
	 */
	public function register_block_source( $sources ) {

		$entry = [ 'learndash_block' => esc_html__( 'LearnDash Field', 'wpgb-learndash' ) ];

		return $this->insert_at( $sources, $entry, 'term_field' );

	}

	/**
	 * Registrer builder fields
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $fields Holds builder block fields.
	 * @return array
	 */
	public function register_block_fields( $fields ) {

		$ld_fields = include WPGB_LEARNDASH_PATH . 'includes/blocks/fields.php';

		return array_merge( $fields, $ld_fields );

	}

	/**
	 * Register builder script
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $scripts Holds script to register.
	 * @return array
	 */
	public function register_script( $scripts ) {

		$scripts[] = [
			'handle'  => 'wpgb-learndash',
			'source'  => WPGB_LEARNDASH_URL . 'assets/js/build.js',
			'version' => WPGB_LEARNDASH_VERSION,
		];

		return $scripts;

	}

	/**
	 * Enqueue block script
	 *
	 * @since 1.1.5
	 * @access public
	 *
	 * @param string $handle Script handle to localize.
	 */
	public function enqueue_script( $handle ) {

		if ( 'app' !== $handle ) {
			return;
		}

		wp_enqueue_script(
			'wpgb-learndash-blocks',
			WPGB_LEARNDASH_URL . 'assets/js/blocks.js',
			[ 'wpgb-app' ],
			WPGB_LEARNDASH_VERSION,
			true
		);

		wp_set_script_translations( 'wpgb-learndash-blocks', 'wpgb-learndash', WPGB_LEARNDASH_PATH . '/languages' );

	}

	/**
	 * Localize builder script
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function localize_script() {

		wp_localize_script(
			'wpgb-learndash',
			'wpgb_ld_data',
			[
				'date'        => date_i18n( get_option( 'date_format' ) ),
				'price'       => __( '$99', 'wpgb-learndash' ),
				'button'      => __( 'See more...', 'wpgb-learndash' ),
				'ribbon'      => __( 'Course Ribbon', 'wpgb-learndash' ),
				'status'      => __( 'In Progress', 'wpgb-learndash' ),
				'steps'       => esc_html( wpgb_ld_i18n( 'steps', 2 ) ),
				'lessons'     => esc_html( wpgb_ld_i18n( 'lessons', 2 ) ),
				'progress'    => esc_html( wpgb_ld_i18n( 'progress' ) ),
				'activity'    => esc_html( wpgb_ld_i18n( 'activity' ) ),
				'access_from' => esc_html( wpgb_ld_i18n( 'access_from' ) ),
				'courseGrid'  => $this->has_course_grid(),
			]
		);
	}
}
