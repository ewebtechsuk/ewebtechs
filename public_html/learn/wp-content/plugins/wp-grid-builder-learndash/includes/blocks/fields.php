<?php
/**
 * LearnDash builder fields
 *
 * @package   WP Grid Builder - LearnDash
 * @author    Loïc Blascos
 * @copyright 2019-2024 Loïc Blascos
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array_merge(
	[
		[
			'id'                => 'learndash_block',
			'tab'               => 'content',
			'type'              => 'select',
			'label'             => esc_html__( 'LearnDash Field', 'wpgb-learndash' ),
			'options'           => [
				'ld_course_progress_bar' => esc_html__( 'Course Progress Bar', 'wpgb-learndash' ),
				'ld_course_progress'     => esc_html__( 'Course Progress Percent', 'wpgb-learndash' ),
				'ld_course_steps'        => esc_html__( 'Course Steps', 'wpgb-learndash' ),
				'ld_course_lessons'      => esc_html__( 'Course Lessons', 'wpgb-learndash' ),
				'ld_course_status'       => esc_html__( 'Course Status', 'wpgb-learndash' ),
				'ld_course_price'        => esc_html__( 'Course Price', 'wpgb-learndash' ),
				'ld_course_activity'     => esc_html__( 'Course Activity', 'wpgb-learndash' ),
			] + ( $this->has_course_grid() ? [
				// Only available when LearnDash course grid add-on is activated.
				'ld_course_ribbon'      => esc_html__( 'Course Ribbon', 'wpgb-learndash' ),
				'ld_course_button_text' => esc_html__( 'Course Button', 'wpgb-learndash' ),
				'ld_course_description' => esc_html__( 'Course Description', 'wpgb-learndash' ),
			] : [] ) + [
				'ld_lesson_access_from' => esc_html__( 'Lesson Release Date', 'wpgb-learndash' ),
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
			],
		],
		[
			'id'                => 'ld_course_progress_type',
			'tab'               => 'content',
			'type'              => 'radio',
			'label'             => esc_html__( 'Progress Type', 'wpgb-learndash' ),
			'value'             => 'bar',
			'options'           => [
				'bar'    => esc_html__( 'Bar', 'wpgb-learndash' ),
				'circle' => esc_html__( 'Circle', 'wpgb-learndash' ),
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_progress_bar',
				],
			],
		],
		[
			'id'                => 'ld_course_progress_stroke_background',
			'tab'               => 'content',
			'type'              => 'color',
			'label'             => esc_html__( 'Progress Background', 'wpgb-learndash' ),
			'clear'             => esc_html__( 'Clear', 'wpgb-learndash' ),
			'alpha'             => true,
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_progress_bar',
				],
				[
					'field'   => 'ld_course_progress_type',
					'compare' => '===',
					'value'   => 'circle',
				],
			],
		],
		[
			'id'                => 'ld_course_progress_stroke_color',
			'tab'               => 'content',
			'type'              => 'color',
			'label'             => esc_html__( 'Progress Color', 'wpgb-learndash' ),
			'clear'             => esc_html__( 'Clear', 'wpgb-learndash' ),
			'alpha'             => true,
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_progress_bar',
				],
				[
					'field'   => 'ld_course_progress_type',
					'compare' => '===',
					'value'   => 'circle',
				],
			],
		],
		[
			'id'                => 'ld_course_progress_stroke_width',
			'tab'               => 'content',
			'type'              => 'slider',
			'label'             => esc_html__( 'Circle Thickness', 'wpgb-learndash' ),
			'steps'             => [ 0.01 ],
			'units'             => [ '' ],
			'value'             => 1,
			'min'               => 0.1,
			'max'               => 10,
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_progress_bar',
				],
				[
					'field'   => 'ld_course_progress_type',
					'compare' => '===',
					'value'   => 'circle',
				],
			],
		],
		[
			'id'                => 'ld_course_progress_color',
			'tab'               => 'content',
			'type'              => 'color',
			'label'             => esc_html__( 'Progress Color', 'wpgb-learndash' ),
			'clear'             => esc_html__( 'Clear', 'wpgb-learndash' ),
			'alpha'             => true,
			'gradient'          => true,
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_progress_bar',
				],
				[
					'field'   => 'ld_course_progress_type',
					'compare' => '===',
					'value'   => 'bar',
				],
			],
		],
		[
			'id'                => 'ld_course_progress_text',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Progress Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'progress' ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_progress',
				],
			],
		],
		[
			'id'                => 'ld_course_activity_text',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Activity Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'activity' ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_activity',
				],
			],
		],
		[
			'id'                => 'ld_course_steps_singlular',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Singular Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'steps', 1 ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_steps',
				],
			],
		],
		[
			'id'                => 'ld_course_steps_plural',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Plural Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'steps', 2 ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => 'IN',
					'value'   => 'ld_course_steps',
				],
			],
		],

		[
			'id'                => 'ld_course_lessons_singlular',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Singular Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'lessons', 1 ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_lessons',
				],
			],
		],
		[
			'id'                => 'ld_course_lessons_plural',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Plural Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'lessons', 2 ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => 'IN',
					'value'   => 'ld_course_lessons',
				],
			],
		],
		[
			'id'                => 'ld_course_status_unenrolled',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'Not Enrolled Status', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'    => 'ld_course_status_unenrolled_label',
					'tab'   => 'content',
					'type'  => 'text',
					'label' => esc_html__( 'Status Label', 'wpgb-learndash' ),
				],
				[
					'id'       => 'ld_course_status_unenrolled_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_status_unenrolled_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_status',
				],
			],
		],
		[
			'id'                => 'ld_course_status_enrolled',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'Enrolled Status', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'    => 'ld_course_status_enrolled_label',
					'tab'   => 'content',
					'type'  => 'text',
					'label' => esc_html__( 'Status Label', 'wpgb-learndash' ),
				],
				[
					'id'       => 'ld_course_status_enrolled_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_status_enrolled_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_status',
				],
			],
		],
		[
			'id'                => 'ld_course_status_progress',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'In Progress Status', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'    => 'ld_course_status_progress_label',
					'tab'   => 'content',
					'type'  => 'text',
					'label' => esc_html__( 'Status Label', 'wpgb-learndash' ),
				],
				[
					'id'       => 'ld_course_status_progress_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_status_progress_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_status',
				],
			],
		],
		[
			'id'                => 'ld_course_status_complete',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'Completed Status', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'    => 'ld_course_status_complete_label',
					'tab'   => 'content',
					'type'  => 'text',
					'label' => esc_html__( 'Status Label', 'wpgb-learndash' ),
				],
				[
					'id'       => 'ld_course_status_complete_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_status_complete_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_status',
				],
			],
		],
		[
			'id'                => 'ld_lesson_access_from_text',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Release Date Text', 'wpgb-learndash' ),
			'placeholder'       => esc_html( wpgb_ld_i18n( 'access_from' ) ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_lesson_access_from',
				],
			],
		],
	],
	( $this->has_course_grid() ? [
		// Only available when LearnDash course grid add-on is activated.
		[
			'id'                => 'ld_course_ribbon_enrolled',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'Enrolled Ribbon', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'       => 'ld_course_ribbon_enrolled_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_ribbon_enrolled_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_ribbon',
				],
			],
		],
		[
			'id'                => 'ld_course_ribbon_completed',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'Completed Ribbon', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'       => 'ld_course_ribbon_completed_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_ribbon_completed_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_ribbon',
				],
			],
		],
		[
			'id'                => 'ld_course_ribbon_custom',
			'tab'               => 'content',
			'type'              => 'group',
			'label'             => esc_html__( 'Custom Ribbon', 'wpgb-learndash' ),
			'fields'            => [
				[
					'id'       => 'ld_course_ribbon_custom_bg',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Background', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
				[
					'id'       => 'ld_course_ribbon_custom_color',
					'tab'      => 'content',
					'type'     => 'color',
					'label'    => esc_html__( 'Color', 'wpgb-learndash' ),
					'clear'    => esc_html__( 'Clear', 'wpgb-learndash' ),
					'alpha'    => true,
					'gradient' => true,
				],
			],
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_ribbon',
				],
			],
		],
		[
			'id'                => 'ld_course_button_default_text',
			'tab'               => 'content',
			'type'              => 'text',
			'label'             => esc_html__( 'Default Text', 'wpgb-learndash' ),
			'conditional_logic' => [
				[
					'field'   => 'source',
					'compare' => '===',
					'value'   => 'learndash_block',
				],
				[
					'field'   => 'learndash_block',
					'compare' => '===',
					'value'   => 'ld_course_button_text',
				],
			],
		],
	] : [] )
);
