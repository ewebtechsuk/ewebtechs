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
class Ld_Dashboard_Register_Certificate_Group_Fields {
	public function __construct() {
		if ( function_exists( 'acf_add_local_field_group' ) ) :

			$fields = array(
				array(
					'key'               => 'field_6241599acac49',
					'label'             => esc_html__( 'Certificate Title', 'ld-dashboard' ),
					'name'              => 'ldd_post_title',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'field_624159efcac4a',
					'label'             => esc_html__( 'Certificate Content', 'ld-dashboard' ),
					'name'              => 'ldd_post_content',
					'type'              => 'wysiwyg',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'default_value'     => '',
					'tabs'              => 'all',
					'toolbar'           => 'full',
					'media_upload'      => 1,
					'delay'             => 0,
				),
				array(
					'key'               => 'field_62415a44cac4b',
					'label'             => esc_html__( 'Feature Image', 'ld-dashboard' ),
					'name'              => '_thumbnail_id',
					'type'              => 'image',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form add-course-featured-img ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'library'           => 'uploadedTo',
					'min_width'         => '',
					'min_height'        => '',
					'min_size'          => '',
					'max_width'         => '',
					'max_height'        => '',
					'max_size'          => '',
					'mime_types'        => '',
				),
				array(
					'key'               => 'field_62415b3dcac4c',
					'label'             => esc_html__( 'PDF Page Size', 'ld-dashboard' ),
					'name'              => 'pdf_page_format',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form add-course-featured-img ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'choices'           => array(
						'LETTER' => esc_html__( 'Letter / US Letter (default)', 'ld-dashboard' ),
						'A4'     => esc_html__( 'A4', 'ld-dashboard' ),
					),
					'default_value'     => false,
					'allow_null'        => 0,
					'multiple'          => 0,
					'ui'                => 0,
					'return_format'     => 'value',
					'ajax'              => 0,
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_62415bcccac4d',
					'label'             => esc_html__( 'PDF Page Orientation', 'ld-dashboard' ),
					'name'              => 'pdf_page_orientation',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form add-course-featured-img ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'choices'           => array(
						'L' => esc_html__( 'Landscape (default)', 'ld-dashboard' ),
						'P' => esc_html__( 'Portrait', 'ld-dashboard' ),
					),
					'default_value'     => false,
					'allow_null'        => 0,
					'multiple'          => 0,
					'ui'                => 0,
					'return_format'     => 'value',
					'ajax'              => 0,
					'placeholder'       => '',
				),
			);

			// Add/Edit question form fields for frontend.
			$fields = apply_filters( 'ld_dashboard_certificate_form_fields', $fields );

			// Register question form fields for frontend.
			acf_add_local_field_group(
				array(
					'key'                   => 'certificate-field-group',
					'title'                 => esc_html__( 'Certificate', 'ld-dashboard' ),
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
}


