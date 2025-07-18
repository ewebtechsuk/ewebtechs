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
class Ld_Dashboard_Register_Announcement_Group_Fields {
	public function __construct() {
		if ( function_exists( 'acf_add_local_field_group' ) ) :

			$fields = array(
				array(
					'key'                         => 'field_63198d0078502',
					'label'                       => esc_html__( 'Title', 'ld-dashboard' ),
					'name'                        => 'ldd_post_title',
					'type'                        => 'text',
					'instructions'                => '',
					'required'                    => 1,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-announcement-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 1,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => '',
					'prepend'                     => '',
					'append'                      => '',
					'maxlength'                   => '',
				),
				array(
					'key'                         => 'field_63198d1578503',
					'label'                       => esc_html__( 'Content', 'ld-dashboard' ),
					'name'                        => 'ldd_post_content',
					'type'                        => 'wysiwyg',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-announcement-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 1,
					'readonly'                    => 0,
					'default_value'               => '',
					'tabs'                        => 'all',
					'toolbar'                     => 'full',
					'media_upload'                => 1,
				),
				array(
					'key'                         => 'field_6319920d4814e',
					'label'                       => esc_html__( 'Select Course', 'ld-dashboard' ),
					'name'                        => 'ldd_announcement_course',
					'type'                        => 'select',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-announcement-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'choices'                     => $this->ld_dashboard_get_course_choices(),
					'default_value'               => false,
					'allow_null'                  => 1,
					'multiple'                    => 0,
					'ui'                          => 0,
					'return_format'               => 'value',
					'ajax'                        => 0,
					'placeholder'                 => '',
				),
			);

			// Add/Edit question form fields for frontend.
			$fields = apply_filters( 'ld_dashboard_announcement_form_fields', $fields );

			// Register question form fields for frontend.
			acf_add_local_field_group(
				array(
					'key'                   => 'announcement-field-group',
					'title'                 => esc_html__( 'Announcement', 'ld-dashboard' ),
					'fields'                => $fields,
					'location'              => array(
						array(
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
				)
			);

			endif;
	}

	public function ld_dashboard_get_course_choices() {
		$courses      = '';
		$choises      = array();
		$current_user = wp_get_current_user();
		if ( learndash_is_admin_user() ) {
			$args    = array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$courses = get_posts( $args );
		} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
			$courses = Ld_Dashboard_Public::get_instructor_courses_list();
		}

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course ) {
				$choises[ $course->ID ] = $course->post_title;
			}
		}

		return apply_filters( 'ld_dashboard_get_course_choices', $choises );
	}
}


