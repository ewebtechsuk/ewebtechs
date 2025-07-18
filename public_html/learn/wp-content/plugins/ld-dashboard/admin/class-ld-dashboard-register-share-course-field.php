<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Custom_Learndash
 * @subpackage Custom_Learndash/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Ld_Dashboard_Register_Share_Course_Field {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		if ( function_exists( 'acf_add_local_field_group' ) ) :
			$course_label = LearnDash_Custom_Label::get_label( 'course' );
			acf_add_local_field_group(
				array(
					'key'                   => 'group_61f7d9efb3aa9',
					'title'                 => esc_html__( 'Share Course', 'ld-dashboard' ),
					'fields'                => array(
						array(
							'key'               => 'field_61f7d9f714081',
							'label'             => sprintf( esc_html__( 'Share %s', 'ld-dashboard' ), $course_label ),
							'name'              => '_ld_instructor_ids',
							'type'              => 'user',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'role'              => array(
								0 => 'ld_instructor',
							),
							'allow_null'        => 0,
							'multiple'          => 1,
							'return_format'     => 'id',
						),
					),
					'location'              => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => 'sfwd-courses',
							),
							array(
								'param'    => 'page',
								'operator' => '==',
								'value'    => Ld_Dashboard_Functions::instance()->ld_dashboard_get_page_id( 'dashboard' ),
							),
							array(
								'param'    => 'current_user',
								'operator' => '==',
								'value'    => 'viewing_front',
							),
						),
					),
					'menu_order'            => 0,
					'position'              => 'normal',
					'style'                 => 'default',
					'label_placement'       => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen'        => '',
					'active'                => true,
					'description'           => '',
					'show_in_rest'          => 0,
				)
			);
			endif;
	}
}
