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
class Ld_Dashboard_Register_Course_Group_Fields {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		if ( function_exists( 'acf_add_local_field_group' ) ) :
			$function_obj               = Ld_Dashboard_Functions::instance();
			$my_dashboard_page          = $function_obj->ld_dashboard_get_page_id( 'dashboard' );
			$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
			$settings                   = $ld_dashboard_settings_data['general_settings'];
			$user_id                    = get_current_user_id();
			$user_meta                  = get_userdata( $user_id );
			$course_label               = LearnDash_Custom_Label::get_label( 'course' );
			$courses_label              = LearnDash_Custom_Label::get_label( 'courses' );
			$user_roles                 = $user_meta->roles;
			$allow_tag_creation         = ( isset( $settings['enable-instructor-course-tags'] ) && 1 == $settings['enable-instructor-course-tags'] || in_array( 'administrator', $user_roles ) ) ? 1 : 0;
			$allowed_status             = array(
				'publish' => esc_html__( 'Published', 'ld-dashboard' ),
				'pending' => esc_html__( 'Pending', 'ld-dashboard' ),
				'draft'   => esc_html__( 'Draft', 'ld-dashboard' ),
			);

			// if ( ! isset( $settings['enable-instructor-course-publish'] ) || ( isset( $settings['enable-instructor-course-publish'] ) && 1 != $settings['enable-instructor-course-publish'] ) && ! learndash_is_admin_user( $user_id ) && isset( $_GET['action'] ) && 'add-course' === $_GET['action'] ) {
			// unset( $allowed_status['publish'] );
			// }
			if ( ! isset( $settings['enable-instructor-course-publish'] ) || ( isset( $settings['enable-instructor-course-publish'] ) && 1 != $settings['enable-instructor-course-publish'] ) && ! learndash_is_admin_user( $user_id ) ) {
				unset( $allowed_status['publish'] );
			}

			$access_modes = array(
				'open'      => __( 'Open', 'ld-dashboard' ),
				'free'      => __( 'Free', 'ld-dashboard' ),
				'paynow'    => __( 'Buy Now', 'ld-dashboard' ),
				'subscribe' => __( 'Recurring', 'ld-dashboard' ),
				'closed'    => __( 'Closed', 'ld-dashboard' ),
			);

			$access_modes = apply_filters( 'ld_dashboard_course_access_modes', $access_modes );

			$fields = array(
				array(
					'key'                         => 'field_61b6e2e70659b',
					'label'                       => sprintf( esc_html__( '%s Title', 'ld-dashboard' ), $course_label ),
					'name'                        => 'ldd_post_title',
					'type'                        => 'text',
					'instructions'                => '',
					'required'                    => 1,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
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
					'key'                         => 'field_61b6e3040659c',
					'label'                       => sprintf( esc_html__( '%s Status', 'ld-dashboard' ), $course_label ),
					'name'                        => 'ldd_course_status',
					'type'                        => 'radio',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'choices'                     => $allowed_status,
					'allow_null'                  => 0,
					'other_choice'                => 0,
					'default_value'               => '',
					'layout'                      => 'vertical',
					'return_format'               => 'value',
					'save_other_choice'           => 0,
				),
				array(
					'key'                         => 'field_61b6e31f0659d',
					'label'                       => sprintf( esc_html__( '%s Content', 'ld-dashboard' ), $course_label ),
					'name'                        => 'ldd_post_content',
					'type'                        => 'wysiwyg',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 1,
					'readonly'                    => 0,
					'default_value'               => '',
					'tabs'                        => 'all',
					'toolbar'                     => 'full',
					'media_upload'                => 1,
					'delay'                       => 0,
				),
				array(
					'key'               => 'field_61fcef414383d',
					'label'             => esc_html__( 'Featured Image', 'ld-dashboard' ),
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
					'key'                         => 'field_61b6e3390659e',
					'label'                       => sprintf( esc_html__( '%s Materials', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_course_materials_enabled_cld',
					'type'                        => 'true_false',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 1,
					'message'                     => sprintf( esc_html__( 'Any content added below is displayed on the main %s page', 'ld-dashboard' ), strtolower( $course_label ) ),
					'default_value'               => 0,
					'ui'                          => 0,
					'ui_on_text'                  => '',
					'ui_off_text'                 => '',
				),
				array(
					'key'                         => 'field_61b6e3840659f',
					'label'                       => esc_html__( 'Materials', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_course_materials_cld',
					'type'                        => 'wysiwyg',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e3390659e',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-sub-form custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 1,
					'readonly'                    => 0,
					'default_value'               => '',
					'tabs'                        => 'all',
					'toolbar'                     => 'full',
					'media_upload'                => 1,
					'delay'                       => 0,
				),
				array(
					'key'                         => 'field_61b6e3f6065a0',
					'label'                       => sprintf( esc_html__( '%s Certificate', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_certificate_cld',
					'type'                        => 'post_object',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 1,
					'post_type'                   => array(
						0 => 'sfwd-certificates',
					),
					'taxonomy'                    => '',
					'allow_null'                  => 0,
					'multiple'                    => 0,
					'return_format'               => 'id',
					'ui'                          => 1,
				),
				array(
					'key'                         => 'field_61b6e448065a1',
					'label'                       => sprintf( esc_html__( '%s Content', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_course_disable_content_table_cld',
					'type'                        => 'radio',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'choices'                     => array(
						'after' => esc_html__( 'Always visible', 'ld-dashboard' ),
						'on'    => esc_html__( 'Only visible to enrollees', 'ld-dashboard' ),
					),
					'allow_null'                  => 0,
					'other_choice'                => 0,
					'default_value'               => '',
					'layout'                      => 'vertical',
					'return_format'               => 'value',
					'save_other_choice'           => 0,
				),
				array(
					'key'                         => 'field_61b6e4c4065a2',
					'label'                       => esc_html__( 'Custom Pagination', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_course_lesson_per_page_cld',
					'type'                        => 'true_false',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'message'                     => sprintf( esc_html__( 'Customize the pagination options for this %1$s content table and %2$s navigation widget.', 'ld-dashboard' ), strtolower( $course_label ), strtolower( $course_label ) ),
					'default_value'               => 0,
					'ui'                          => 0,
					'ui_on_text'                  => '',
					'ui_off_text'                 => '',
				),
				array(
					'key'                         => 'field_61b6e4ed065a3',
					'label'                       => LearnDash_Custom_Label::get_label( 'lessons' ),
					'name'                        => 'sfwd-courses_course_lesson_per_page_custom_cld',
					'type'                        => 'text',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e4c4065a2',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-lesson-per-pages custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => '',
					'prepend'                     => '',
					'append'                      => esc_html__( 'Per Pages', 'ld-dashboard' ),
					'maxlength'                   => '',
				),
				array(
					'key'                         => 'field_61b6e52b065a4',
					'label'                       => LearnDash_Custom_Label::get_label( 'topics' ),
					'name'                        => 'sfwd-courses_course_topic_per_page_custom_cld',
					'type'                        => 'text',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e4c4065a2',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form custom-learndash-topic-per-pages',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => '',
					'prepend'                     => '',
					'append'                      => esc_html__( 'Per Pages', 'ld-dashboard' ),
					'maxlength'                   => '',
				),
				array(
					'key'                         => 'field_61b6e561065a5',
					'label'                       => esc_html__( 'Access Mode', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_course_price_type_cld',
					'type'                        => 'select',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'choices'                     => $access_modes,
					'default_value'               => false,
					'allow_null'                  => 0,
					'multiple'                    => 0,
					'ui'                          => 0,
					'return_format'               => 'value',
					'ajax'                        => 0,
					'placeholder'                 => '',
				),
				array(
					'key'                         => 'field_61b6e7a0065a6',
					'label'                       => sprintf( esc_html__( '%s Price', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_course_price_cld',
					'type'                        => 'text',
					'instructions'                => sprintf( esc_html__( 'The %1$s is protected via the LearnDash built-in PayPal and/or Stripe. Users need to purchase the %2$s (one-time fee) in order to gain access.', 'ld-dashboard' ), strtolower( $course_label ), strtolower( $course_label ) ),
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'paynow',
							),
						),
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'closed',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => '',
					'prepend'                     => '',
					'append'                      => '',
					'maxlength'                   => '',
				),
				array(
					'key'               => 'field_62220196a91f9',
					'label'             => esc_html__( 'Billing Cycle', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_billing_cycle_cld',
					'type'              => 'group',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form ld-dashboard-group-field ld-dashboard-billing-cycle-field',
						'id'    => '',
					),
					'layout'            => 'row',
				),
				array(
					'key'               => 'field_622201dca91fa',
					'label'             => esc_html__( 'Billing Cycle Number', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_price_billing_p3_cld',
					'type'              => 'number',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form ld-dashboard-group-field-single ldd-hide-label',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '0',
					'prepend'           => '',
					'append'            => '',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				),
				array(
					'key'               => 'field_622201f2a91fb',
					'label'             => esc_html__( 'Billing Cycle Type', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_price_billing_t3_cld',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form ld-dashboard-group-field-single ldd-hide-label',
						'id'    => '',
					),
					'choices'           => array(
						'D' => 'day (s)',
						'W' => 'week (s)',
						'M' => 'month (s)',
						'Y' => 'year (s)',
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
					'key'               => 'field_623973542370e',
					'label'             => esc_html__( 'Recurring Times', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_no_of_cycles_cld',
					'type'              => 'number',
					'instructions'      => 'How many times the billing cycle repeats. Leave empty for unlimited repeats',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				),
				array(
					'key'               => 'field_623973d72370f',
					'label'             => sprintf( esc_html__( '%s Trial Price', 'ld-dashboard' ), $course_label ),
					'name'              => 'sfwd-courses_course_trial_price_cld',
					'type'              => 'number',
					'instructions'      => esc_html__( 'Enter the price for the trial period for this Course', 'ld-dashboard' ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				),

				array(
					'key'               => 'field_62220111111',
					'label'             => esc_html__( 'Trial Duration', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_billing_cycle_cld',
					'type'              => 'text',
					'instructions'      => sprintf( esc_html__( 'The length of the trial period, after the trial is over, the normal %s price billing goes into effect.', 'ld-dashboard' ), $course_label ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form ld-dashboard-group-field ld-dashboard-trial-duration-field',
						'id'    => '',
					),
					'layout'            => 'row',
				),
				array(
					'key'               => 'field_622201122211',
					'label'             => esc_html__( 'Trial Duration Number', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_trial_duration_p1_cld',
					'type'              => 'number',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form ld-dashboard-group-field-single ldd-hide-label',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '0',
					'prepend'           => '',
					'append'            => '',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				),
				array(
					'key'               => 'field_6222131233123',
					'label'             => esc_html__( 'Trial Duration Type', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_course_trial_duration_t1_cld',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form ld-dashboard-group-field-single ldd-hide-label',
						'id'    => '',
					),
					'choices'           => array(
						'D' => 'day (s)',
						'W' => 'week (s)',
						'M' => 'month (s)',
						'Y' => 'year (s)',
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
					'key'               => 'field_6233323221',
					'label'             => esc_html__( 'Button URL', 'ld-dashboard' ),
					'name'              => 'sfwd-courses_custom_button_url_cld',
					'type'              => 'url',
					'instructions'      => esc_html__( 'Redirect the "Take this Course" button to a specific URL.', 'ld-dashboard' ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'closed',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_62397c3323710',
					'label'             => sprintf( esc_html__( '%s Enrollment URL', 'ld-dashboard' ), $course_label ),
					'name'              => 'sfwd-courses_course_price_type_subscribe_enrollment_url_cld',
					'type'              => 'url',
					'instructions'      => sprintf( esc_html__( 'Enter the URL of the page you want to redirect your enrollees after signing up for this specific %s', 'ld-dashboard' ), $course_label ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'subscribe',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_122324242445',
					'label'             => sprintf( esc_html__( '%s Enrollment URL', 'ld-dashboard' ), $course_label ),
					'name'              => 'sfwd-courses_course_price_type_paynow_enrollment_url_cld',
					'type'              => 'url',
					'instructions'      => sprintf( esc_html__( 'Enter the URL of the page you want to redirect your enrollees after signing up for this specific %s', 'ld-dashboard' ), $course_label ),
					'required'          => 0,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_61b6e561065a5',
								'operator' => '==',
								'value'    => 'paynow',
							),
						),
					),
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
				),
				array(
					'key'                         => 'field_61b6e7d5065a7',
					'label'                       => sprintf( esc_html__( '%s Prerequisites', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_course_prerequisite_enabled_cld',
					'type'                        => 'true_false',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'message'                     => sprintf( esc_html__( 'Enable prerequisites for this %s', 'ld-dashboard' ), $course_label ),
					'default_value'               => 0,
					'ui'                          => 0,
					'ui_on_text'                  => '',
					'ui_off_text'                 => '',
				),
				array(
					'key'                         => 'field_61b6e7fb065a8',
					'label'                       => esc_html__( 'Compare Mode', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_course_prerequisite_compare_cld',
					'type'                        => 'radio',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e7d5065a7',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'choices'                     => array(
						'ALL' => sprintf( esc_html__( 'All Selected (The user must complete all selected %1$s in order to access this %2$s)', 'ld-dashboard' ), strtolower( $course_label ), strtolower( $course_label ) ),
						'ANY' => sprintf( esc_html__( 'Any Selected (The user must complete any one of the selected %1$s in order to access this %2$s)', 'ld-dashboard' ), strtolower( $courses_label ), strtolower( $course_label ) ),
					),
					'allow_null'                  => 0,
					'other_choice'                => 0,
					'default_value'               => '',
					'layout'                      => 'vertical',
					'return_format'               => 'value',
					'save_other_choice'           => 0,
				),
				array(
					'key'                         => 'field_61b6e827065a9',
					'label'                       => sprintf( esc_html__( '%s to Complete', 'ld-dashboard' ), $courses_label ),
					'name'                        => 'sfwd-courses_course_prerequisite_cld',
					'type'                        => 'relationship',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e7d5065a7',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'post_type'                   => array(
						0 => 'sfwd-courses',
					),
					'taxonomy'                    => '',
					'filters'                     => array(
						0 => 'search',
						1 => 'post_type',
						2 => 'taxonomy',
					),
					'elements'                    => '',
					'min'                         => '',
					'max'                         => '',
					'return_format'               => 'id',
					'post_author'                 => '',
					'add_edit_post'               => 0,
				),
				array(
					'key'                         => 'field_61b6e85a065aa',
					'label'                       => sprintf( esc_html__( '%s Points', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_course_points_enabled_cld',
					'type'                        => 'true_false',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'message'                     => sprintf( esc_html__( 'Enable %s points.', 'ld-dashboard' ), $course_label ),
					'default_value'               => 0,
					'ui'                          => 0,
					'ui_on_text'                  => '',
					'ui_off_text'                 => '',
				),
				array(
					'key'                         => 'field_61b6e880065ab',
					'label'                       => esc_html__( 'Required for Access', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_course_points_access_cld',
					'type'                        => 'number',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e85a065aa',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => sprintf( esc_html__( 'Number of points required in order to gain access to this %s.', 'ld-dashboard' ), $course_label ),
					'prepend'                     => '',
					'append'                      => esc_html__( 'points (s)', 'ld-dashboard' ),
					'min'                         => '',
					'max'                         => '',
					'step'                        => '',
				),
				array(
					'key'                         => 'field_61b6ecf1065ac',
					'label'                       => esc_html__( 'Awarded on Completion', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_course_points_cld',
					'type'                        => 'number',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6e85a065aa',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => sprintf( esc_html__( 'Number of points awarded for completing this %s.', 'ld-dashboard' ), $course_label ),
					'prepend'                     => '',
					'append'                      => 'points (s)',
					'min'                         => '',
					'max'                         => '',
					'step'                        => '',
				),
				array(
					'key'                         => 'field_61b6ed3b065ad',
					'label'                       => sprintf( esc_html__( '%s Access Expiration', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_expire_access_cld',
					'type'                        => 'true_false',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'message'                     => sprintf( esc_html__( 'Enable %s access expiration.', 'ld-dashboard' ), $course_label ),
					'default_value'               => 0,
					'ui'                          => 0,
					'ui_on_text'                  => '',
					'ui_off_text'                 => '',
				),
				array(
					'key'                         => 'field_61b6ed70065ae',
					'label'                       => esc_html__( 'Access Period', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_expire_access_days_cld',
					'type'                        => 'text',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6ed3b065ad',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'readonly'                    => 0,
					'default_value'               => '',
					'placeholder'                 => sprintf( esc_html__( 'Set the number of days a user will have access to the %s from enrollment date.', 'ld-dashboard' ), $course_label ),
					'prepend'                     => '',
					'append'                      => 'Days',
					'maxlength'                   => '',
				),
				array(
					'key'                         => 'field_61b6edb1065af',
					'label'                       => esc_html__( 'Data Deletion', 'ld-dashboard' ),
					'name'                        => 'sfwd-courses_expire_access_delete_progress_cld',
					'type'                        => 'true_false',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => array(
						array(
							array(
								'field'    => 'field_61b6ed3b065ad',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab custom-learndash-course-sub-form',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'message'                     => sprintf( esc_html__( 'All user %s data will be deleted upon access expiration.', 'ld-dashboard' ), $course_label ),
					'default_value'               => 0,
					'ui'                          => 0,
					'ui_on_text'                  => '',
					'ui_off_text'                 => '',
				),
				array(
					'key'                         => 'field_61b6ede2065b0',
					'label'                       => sprintf( esc_html__( '%s Progression', 'ld-dashboard' ), $course_label ),
					'name'                        => 'sfwd-courses_course_disable_lesson_progression_cld',
					'type'                        => 'radio',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'choices'                     => array(
						'sfwd_linear' => sprintf( esc_html__( 'Linear (Requires the user to progress through the %s in the designated step sequence)', 'ld-dashboard' ), $course_label ),
						'on'          => sprintf( esc_html__( 'Free form (Allows the user to move freely through the %s without following the designated step sequence)', 'ld-dashboard' ), $course_label ),
					),
					'allow_null'                  => 0,
					'other_choice'                => 0,
					'default_value'               => '',
					'layout'                      => 'vertical',
					'return_format'               => 'value',
					'save_other_choice'           => 0,
				),
				array(
					'key'                         => 'field_61b887732a65b',
					'label'                       => sprintf( esc_html__( 'Share %s', 'ld-dashboard' ), $course_label ),
					'name'                        => '_ld_instructor_ids',
					'type'                        => 'user',
					'instructions'                => esc_html__( 'Add co-author for this course.', 'ld-dashboard' ),
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-settings-data-tab',
						'id'    => '',
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'role'                        => array(
						0 => 'ld_instructor',
					),
					'allow_null'                  => 0,
					'multiple'                    => 1,
					'return_format'               => 'id',
				),
				array(
					'key'               => 'field_61f90e3dad31b',
					'label'             => sprintf( esc_html__( '%s Categories', 'ld-dashboard' ), $course_label ),
					'name'              => 'ldd_course_category',
					'type'              => 'taxonomy',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'taxonomy'          => 'ld_course_category',
					'field_type'        => 'multi_select',
					'allow_null'        => 0,
					'add_term'          => 0,
					'save_terms'        => 1,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'multiple'          => 0,
				),
				array(
					'key'               => 'field_61f910155bb7c',
					'label'             => sprintf( esc_html__( '%s Tags', 'ld-dashboard' ), $course_label ),
					'name'              => 'ldd_course_tags',
					'type'              => 'taxonomy',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-course-tag-field ld-dashboard-form-post-data-tab',
						'id'    => '',
					),
					'taxonomy'          => 'ld_course_tag',
					'field_type'        => 'multi_select',
					'allow_null'        => 0,
					'add_term'          => $allow_tag_creation,
					'save_terms'        => 1,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'multiple'          => 0,
				),
				array(
					'key'               => 'field_620239860ddd2',
					'label'             => sprintf( esc_html__( '%s Builder', 'ld-dashboard' ), $course_label ),
					'name'              => 'ldd_builder',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form ld-dashboard-course-builder-wrapper',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
			);

			if ( 'buddyboss-theme' === ld_dashboard_get_current_theme_directory() || class_exists( 'LearnMate_LearnDash_Addon' ) ) {
				$meta_key = '';
				if ( 'buddyboss-theme' === ld_dashboard_get_current_theme_directory() ) {
					$meta_key = 'sfwd-courses_course-cover-image_thumbnail_id';
					$fields[] = array(
						'key'               => 'field_61fcef414383g',
						'label'             => esc_html__( 'Course Short Description', 'ld-dashboard' ),
						'name'              => 'ldd_excerpt',
						'type'              => 'wysiwyg',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => 'custom-learndash-course-form ld-dashboard-form-post-data-tab',
							'id'    => '',
						),
						'frontend_admin_display_mode' => 'edit',
						'only_front'                  => 1,
						'readonly'                    => 0,
						'default_value'               => '',
						'tabs'                        => 'all',
						'toolbar'                     => 'full',
						'media_upload'                => 1,
						'delay'                       => 0,
					);
				} elseif ( 'reign-theme' === ld_dashboard_get_current_theme_directory() ) {
					$meta_key = '_course_image_id';
				}

				$fields[] = array(
					'key'               => 'field_61fcef414383f',
					'label'             => esc_html__( 'Cover Image', 'ld-dashboard' ),
					'name'              => $meta_key,
					'type'              => 'image',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'custom-learndash-course-form add-course-cover-img ld-dashboard-form-post-data-tab',
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
				);
			}

			// Add/Edit course form fields for frontend.
			$fields = apply_filters( 'ld_dashboard_course_form_fields', $fields );

			// Register course form fields for frontend.
			acf_add_local_field_group(
				array(
					'key'                   => 'course-field-group',
					'title'                 => esc_html__( 'Course Form', 'ld-dashboard' ),
					'fields'                => $fields,
					'location'              => array(
						array(
							array(
								'param'    => 'page',
								'operator' => '==',
								'value'    => $my_dashboard_page,
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
