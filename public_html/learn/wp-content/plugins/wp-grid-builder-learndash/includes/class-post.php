<?php
/**
 * Post
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
 * Extend post object of WP Grid Builder
 *
 * @class WP_Grid_Builder_LearnDash\Includes\Post
 * @since 1.0.0
 */
final class Post {

	/**
	 * LearnDash post types
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array
	 */
	public $ld_post_types = [
		'sfwd-courses' => true,
		'sfwd-lessons' => true,
		'sfwd-topic'   => true,
		'sfwd-quiz'    => true,
	];

	/**
	 * OEmbed providers
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array
	 */
	public $providers = [
		// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned, WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow
		'#https?://?(?:.+)?(?:wistia\.com|wistia\.net|wi\.st)/?(?:embed/)?(?:iframe|playlists)/?([\w\-_]+)+#i' => 'wistia',
		'#https?://?(?:www\.|m\.)?youtube\.com/?(?:watch\?v=|embed/)?([\w\-_]+)+#i'                            => 'youtube',
		'#https?://youtu\.be/?([\w\-_]+)+#i'                                                                   => 'youtube',
		'#https?://?player.vimeo\.com/video/?([\w\-_]+)+#i'                                                    => 'vimeo',
		'#https?://?(?:www\.)?vimeo\.com/?([\w\-_]+)+#i'                                                       => 'vimeo',
		// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned, WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow
	];

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		add_filter( 'wp_grid_builder/grid/the_object', [ $this, 'get_learndash_meta' ] );

	}

	/**
	 * Check if is LearnDash post type
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $post Holds post object.
	 * @return boolean
	 */
	public function is_learndash_post( $post ) {

		if ( ! isset( $post->post_type ) ) {
			return false;
		}

		return isset( $this->ld_post_types[ $post->post_type ] );

	}

	/**
	 * Get LearnDash metadata
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $post Holds post object.
	 * @return object
	 */
	public function get_learndash_meta( $post ) {

		if ( ! $this->is_learndash_post( $post ) ) {
			return $post;
		}

		$this->get_post_format( $post );
		$this->get_post_meta( $post );

		return $post;

	}

	/**
	 * Get LearnDash post format
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $post Holds post object.
	 */
	public function get_post_format( &$post ) {

		$embed_code   = get_post_meta( $post->ID, '_learndash_course_grid_video_embed_code', true );
		$enable_video = get_post_meta( $post->ID, '_learndash_course_grid_enable_video_preview', true );

		if ( 'video' !== $post->post_format && $enable_video && ! empty( $embed_code ) ) {

			foreach ( $this->providers as $provider => $media ) {

				if ( ! preg_match( $provider, $embed_code, $match ) ) {
					continue;
				}

				$post->post_format = 'video';
				$post->post_media  = [
					'type'    => 'embedded',
					'format'  => 'video',
					'sources' => [
						'provider' => $media,
						'url'      => $match[0],
						'id'       => $match[1],
					],
				];

			}
		}
	}

	/**
	 * Get LearnDash post metadata
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $post Holds post object.
	 */
	public function get_post_meta( &$post ) {

		if ( 'sfwd-courses' !== $post->post_type ) {
			return;
		}

		if ( function_exists( 'learndash_get_course_id' ) ) {
			$post->ld_course_id = learndash_get_course_id( $post->ID );
		}

		if ( function_exists( 'learndash_get_step_permalink' ) && ! empty( $post->ld_course_id ) ) {
			$post->permalink = learndash_get_step_permalink( $post->ID, $post->ld_course_id );
		}

		$post->ld_course_options = get_post_meta( $post->ID, '_sfwd-courses', true );
		$post->ld_course_ribbon_text = get_post_meta( $post->ID, '_learndash_course_grid_custom_ribbon_text', true );

	}
}
