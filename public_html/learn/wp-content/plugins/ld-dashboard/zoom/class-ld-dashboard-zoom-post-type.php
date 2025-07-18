<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class LD_Dashboard_Post_Type_Zoom {

	/**
	 * Contain the instance of the plugin
	 *
	 * @since    6.1.0
	 * @access   private
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Class Constructor
	 *
	 * @since    6.1.0
	 * @access   public
	 *
	 * @var array
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_zoom_meeting_post_type' ) );
	}


	public static function getInstance(): LD_Dashboard_Post_Type_Zoom {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}

    /**
     * CPT Zoom Meeting
     *
     * @return void
     */
	public function register_zoom_meeting_post_type() {
		if ( ! post_type_exists( 'zoom_meet' ) && ld_dashboard_check_if_zoom_credentials_exists() ) {
			$zoom_meet_labels = array(
				'name'               => _x( 'Zoom Meetings', 'Post Type General Name', 'ld-dashboard' ),
				'singular_name'      => _x( 'Zoom Meeting', 'Post Type Singular Name', 'ld-dashboard' ),
				'menu_name'          => __( 'Zoom Meetings', 'ld-dashboard' ),
				'parent_item_colon'  => __( 'Parent Meeting', 'ld-dashboard' ),
				'all_items'          => __( 'All Meetings', 'ld-dashboard' ),
				'view_item'          => __( 'View Meeting', 'ld-dashboard' ),
				'add_new_item'       => __( 'Add New Meeting', 'ld-dashboard' ),
				'add_new'            => __( 'Add New', 'ld-dashboard' ),
				'edit_item'          => __( 'Edit Meeting', 'ld-dashboard' ),
				'update_item'        => __( 'Update Meeting', 'ld-dashboard' ),
				'search_items'       => __( 'Search Meeting', 'ld-dashboard' ),
				'not_found'          => __( 'Not Found', 'ld-dashboard' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'ld-dashboard' ),
			);

			$zoom_meet_args = array(
				'label'              => __( 'zoom_meet', 'ld-dashboard' ),
				'description'        => __( 'Zoom Meeting', 'ld-dashboard' ),
				'labels'             => $zoom_meet_labels,
				'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'custom-fields' ),
				'taxonomies'         => array(),
				'hierarchical'       => false,
				'public'             => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_in_admin_bar'  => true,
				'has_archive'        => true,
				'menu_position'      => 5,
				'menu_icon'          => 'dashicons-video-alt2',
				'publicly_queryable' => true,
				'capability_type'    => 'post',
				'show_in_rest'       => true,
			);
			register_post_type( 'zoom_meet', $zoom_meet_args );
		}
	}


}


LD_Dashboard_Post_Type_Zoom::getInstance();