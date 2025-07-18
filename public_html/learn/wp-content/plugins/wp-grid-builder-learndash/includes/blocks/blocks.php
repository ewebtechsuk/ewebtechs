<?php
/**
 * LearnDash blocks settings
 *
 * @package   WP Grid Builder - LearnDash
 * @author    Loïc Blascos
 * @copyright 2019-2024 Loïc Blascos
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ld_course_progress_bar = [
	'title'           => __( 'Course Progress Bar', 'wpgb-learndash' ),
	'description'     => __( 'Displays the progress bar of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-progress-bar-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'                               => 'learndash_block',
				'learndash_block'                      => 'ld_course_progress_bar',
				'ld_course_progress_type'              => 'bar',
				'ld_course_progress_color'             => 'linear-gradient(90deg, rgba(1, 158, 124, 0.2) 0%, rgb(1, 158, 124) 100%)',
				'ld_course_progress_stroke_background' => '#e2e7ed',
				'ld_course_progress_stroke_color'      => '#019e7c',
				'ld_course_progress_stroke_width'      => 2,
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'height'                     => '0.375em',
					'margin-bottom'              => '0.5em',
					'margin-top'                 => '0.5em',
					'background'                 => '#e2e7ed',
					'border-top-left-radius'     => '0.5em',
					'border-top-right-radius'    => '0.5em',
					'border-bottom-left-radius'  => '0.5em',
					'border-bottom-right-radius' => '0.5em',
					'font-size'                  => '1em',
					'line-height'                => 1,
				],
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'learndash_block'                      => [
					'type'   => 'text',
					'hidden' => true,
				],
				'ld_course_progress_type'              => [
					'type'    => 'button',
					'label'   => __( 'Progress Type', 'wpgb-learndash' ),
					'value'   => 'bar',
					'options' => [
						[
							'value' => 'bar',
							'label' => __( 'Bar', 'wpgb-learndash' ),
						],
						[
							'value' => 'circle',
							'label' => __( 'Circle', 'wpgb-learndash' ),
						],
					],
				],
				'ld_course_progress_stroke_background' => [
					'type'      => 'color',
					'label'     => __( 'Progress Background', 'wpgb-learndash' ),
					'condition' => [
						[
							'field'   => 'ld_course_progress_type',
							'compare' => '===',
							'value'   => 'circle',
						],
					],
				],
				'ld_course_progress_stroke_color'      => [
					'type'      => 'color',
					'label'     => __( 'Progress Color', 'wpgb-learndash' ),
					'condition' => [
						[
							'field'   => 'ld_course_progress_type',
							'compare' => '===',
							'value'   => 'circle',
						],
					],
				],
				'ld_course_progress_stroke_width'      => [
					'type'      => 'range',
					'label'     => __( 'Circle Thickness', 'wpgb-learndash' ),
					'step'      => 0.01,
					'value'     => 1,
					'min'       => 0.1,
					'max'       => 10,
					'condition' => [
						[
							'field'   => 'ld_course_progress_type',
							'compare' => '===',
							'value'   => 'circle',
						],
					],
				],
				'ld_course_progress_color'             => [
					'tab'       => 'content',
					'type'      => 'color',
					'label'     => __( 'Progress Color', 'wpgb-learndash' ),
					'gradient'  => true,
					'condition' => [
						[
							'field'   => 'ld_course_progress_type',
							'compare' => '===',
							'value'   => 'bar',
						],
					],

				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_progress_bar',
];

$ld_course_progress_circle = [
	'title'           => esc_html__( 'Course Progress Bar', 'wpgb-learndash' ),
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-progress-circle-icon',
	'category'        => 'learndash_blocks',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'                               => 'learndash_block',
				'learndash_block'                      => 'ld_course_progress_bar',
				'ld_course_progress_type'              => 'circle',
				'ld_course_progress_color'             => '#019e7c',
				'ld_course_progress_stroke_background' => '#e2e7ed',
				'ld_course_progress_stroke_color'      => '#019e7c',
				'ld_course_progress_stroke_width'      => 2,
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'height'                     => '3.5em',
					'width'                      => '3.5em',
					'padding-right'              => '0.25em',
					'padding-top'                => '0.25em',
					'padding-bottom'             => '0.25em',
					'padding-left'               => '0.25em',
					'background'                 => '#ffffff',
					'border-top-left-radius'     => '50%',
					'border-top-right-radius'    => '50%',
					'border-bottom-left-radius'  => '50%',
					'border-bottom-right-radius' => '50%',
					'font-size'                  => '1em',
					'line-height'                => 1,
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_progress_bar',
];

$ld_course_progress = [
	'title'           => __( 'Course Progress', 'wpgb-learndash' ),
	'description'     => __( 'Displays the percentage progress of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-percent-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'learndash_block' => 'ld_course_progress',
				'idle_scheme'     => 'scheme-1',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'        => 'inline-block',
					'vertical-align' => 'top',
					'color_scheme'   => 'scheme-1',
					'font-size'      => '0.875em',
					'font-weight'    => '600',
					'line-height'    => 1,
				],
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'ld_course_progress_text' => [
					'type'        => 'text',
					'label'       => __( 'Progress Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'progress' ) : '',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_progress',
];

$ld_course_steps = [
	'title'           => __( 'Course Steps', 'wpgb-learndash' ),
	'description'     => __( 'Displays the number of steps of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-steps-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'learndash_block' => 'ld_course_steps',
				'idle_scheme'     => 'scheme-3',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'        => 'inline-block',
					'vertical-align' => 'top',
					'color_scheme'   => 'scheme-3',
					'font-size'      => '0.875em',
					'line-height'    => 1,
				],
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'ld_course_steps_singlular' => [
					'type'        => 'text',
					'label'       => __( 'Singular Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'steps', 1 ) : '',
				],
				'ld_course_steps_plural'    => [
					'type'        => 'text',
					'label'       => __( 'Plural Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'steps', 2 ) : '',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_steps',
];

$ld_course_lessons = [
	'title'           => __( 'Course Lessons', 'wpgb-learndash' ),
	'description'     => __( 'Displays the number of lessons of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-lessons-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'learndash_block' => 'ld_course_lessons',
				'idle_scheme'     => 'scheme-3',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'                    => 'inline-block',
					'vertical-align'             => 'top',
					'padding-right'              => '1.25em',
					'padding-top'                => '0.5em',
					'padding-bottom'             => '0.5em',
					'padding-left'               => '1.25em',
					'background'                 => '#F4F8FB',
					'border-style'               => 'solid',
					'border-color'               => '#E2E7ED',
					'border-top-width'           => '0.125rem',
					'border-right-width'         => '0.125rem',
					'border-bottom-width'        => '0.125rem',
					'border-left-width'          => '0.125rem',
					'border-top-left-radius'     => '2em',
					'border-top-right-radius'    => '2em',
					'border-bottom-left-radius'  => '2em',
					'border-bottom-right-radius' => '2em',
					'color'                      => '#777777',
					'font-size'                  => '0.875em',
					'font-weight'                => '500',
					'line-height'                => 1,
				],
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'ld_course_lessons_singlular' => [
					'type'        => 'text',
					'label'       => __( 'Singular Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'lessons', 1 ) : '',
				],
				'ld_course_lessons_plural'    => [
					'type'        => 'text',
					'label'       => __( 'Plural Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'lessons', 2 ) : '',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_lessons',
];

$ld_course_status = [
	'title'           => __( 'Course Status', 'wpgb-learndash' ),
	'description'     => __( 'Displays the status of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-status-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'                            => 'learndash_block',
				'learndash_block'                   => 'ld_course_status',
				'ld_course_status_unenrolled_label' => __( 'Not Enrolled', 'wpgb-learndash' ),
				'ld_course_status_unenrolled_bg'    => '#DD9933',
				'ld_course_status_unenrolled_color' => '#ffffff',
				'ld_course_status_enrolled_label'   => __( 'Not Started', 'wpgb-learndash' ),
				'ld_course_status_enrolled_bg'      => '#0069ff',
				'ld_course_status_enrolled_color'   => '#ffffff',
				'ld_course_status_progress_label'   => __( 'In Progress', 'wpgb-learndash' ),
				'ld_course_status_progress_bg'      => '#0069ff',
				'ld_course_status_progress_color'   => '#ffffff',
				'ld_course_status_complete_label'   => __( 'Complete', 'wpgb-learndash' ),
				'ld_course_status_complete_bg'      => '#019e7c',
				'ld_course_status_complete_color'   => '#ffffff',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'                    => 'inline-block',
					'padding-top'                => '0.75em',
					'padding-bottom'             => '0.75em',
					'padding-right'              => '1.5em',
					'padding-left'               => '1.5em',
					'border-top-left-radius'     => '0.5em',
					'border-top-right-radius'    => '0.5em',
					'border-bottom-right-radius' => '0.5em',
					'border-bottom-left-radius'  => '0.5em',
					'font-size'                  => '0.8em',
					'font-weight'                => '700',
					'line-height'                => '1.4',
					'text-transform'             => 'uppercase',
				],
			],
		],
	],
	'controls'        => [
		'not_enrolled_status' => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'Not Enrolled Status', 'wpgb-learndash' ),
					'fields' => [
						'ld_course_status_unenrolled_label' => [
							'type'  => 'text',
							'label' => __( 'Status Label', 'wpgb-learndash' ),
						],
						'grid' => [
							'type'   => 'grid',
							'fields' => [
								'ld_course_status_unenrolled_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_status_unenrolled_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
		'enrolled_status'     => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'Enrolled Status', 'wpgb-learndash' ),
					'fields' => [
						'ld_course_status_enrolled_label' => [
							'type'  => 'text',
							'label' => __( 'Status Label', 'wpgb-learndash' ),
						],
						'grid'                            => [
							'type'   => 'grid',
							'fields' => [
								'ld_course_status_enrolled_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_status_enrolled_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
		'in_progress_status'  => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'In Progress Status', 'wpgb-learndash' ),
					'fields' => [
						'ld_course_status_progress_label' => [
							'type'  => 'text',
							'label' => __( 'Status Label', 'wpgb-learndash' ),
						],
						'grid'                            => [
							'type'   => 'grid',
							'fields' => [
								'ld_course_status_progress_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_status_progress_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
		'complete_status'     => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'Complete Status', 'wpgb-learndash' ),
					'fields' => [
						'ld_course_status_complete_label' => [
							'type'  => 'text',
							'label' => __( 'Status Label', 'wpgb-learndash' ),
						],
						'grid'                            => [
							'type'   => 'grid',
							'fields' => [
								'ld_course_status_complete_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_status_complete_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_status',
];

$ld_course_price = [
	'title'           => __( 'Course Price', 'wpgb-learndash' ),
	'description'     => __( 'Displays the price of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-price-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'idle_scheme'     => 'scheme-1',
				'learndash_block' => 'ld_course_price',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'color_scheme' => 'scheme-1',
					'font-size'    => '1.625em',
					'font-weight'  => '400',
					'line-height'  => '1.4',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_price',
];

$ld_course_activity = [
	'title'           => __( 'Course Activity', 'wpgb-learndash' ),
	'description'     => __( 'Displays the activity of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-activity-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'learndash_block' => 'ld_course_activity',
				'idle_scheme'     => 'scheme-3',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'        => 'inline-block',
					'vertical-align' => 'top',
					'color_scheme'   => 'scheme-3',
					'font-size'      => '0.875em',
					'line-height'    => 1,
				],
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'ld_course_activity_text' => [
					'type'        => 'text',
					'label'       => __( 'Activity Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'activity' ) : '',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_activity',
];

$ld_lesson_access_from = [
	'title'           => __( 'Lesson Release Date', 'wpgb-learndash' ),
	'description'     => __( 'Displays the release date of the lesson.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-schedule-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'learndash_block' => 'ld_lesson_access_from',
				'idle_scheme'     => 'scheme-3',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'        => 'inline-block',
					'vertical-align' => 'top',
					'color_scheme'   => 'scheme-3',
					'font-size'      => '0.875em',
					'line-height'    => 1,
				],
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'ld_lesson_access_from_text' => [
					'type'        => 'text',
					'label'       => __( 'Release Date Text', 'wpgb-learndash' ),
					'placeholder' => function_exists( 'wpgb_ld_i18n' ) ? wpgb_ld_i18n( 'access_from' ) : '',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_lesson_access_from',
];

$ld_course_ribbon = [
	'title'           => __( 'Course Ribbon', 'wpgb-learndash' ),
	'description'     => __( 'Displays the ribbon of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-ribbon-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'                           => 'learndash_block',
				'learndash_block'                  => 'ld_course_ribbon',
				'ld_course_ribbon_completed_bg'    => '#019e7c',
				'ld_course_ribbon_completed_color' => '#ffffff',
				'ld_course_ribbon_enrolled_bg'     => '#0069ff',
				'ld_course_ribbon_enrolled_color'  => '#ffffff',
				'ld_course_ribbon_custom_bg'       => '#2a2a2a',
				'ld_course_ribbon_custom_color'    => '#ffffff',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'display'                    => 'inline-block',
					'padding-top'                => '0.75em',
					'padding-bottom'             => '0.75em',
					'padding-right'              => '1.5em',
					'padding-left'               => '1.5em',
					'border-top-left-radius'     => '0.5em',
					'border-top-right-radius'    => '0.5em',
					'border-bottom-right-radius' => '0.5em',
					'border-bottom-left-radius'  => '0.5em',
					'background'                 => '#019e7c',
					'color'                      => '#ffffff',
					'font-size'                  => '0.8em',
					'font-weight'                => '700',
					'line-height'                => '1.4',
					'text-transform'             => 'uppercase',
				],
			],
		],
	],
	'controls'        => [
		'enrolled_ribbon'  => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'Enrolled Ribbon', 'wpgb-learndash' ),
					'fields' => [
						[
							'type'   => 'grid',
							'fields' => [
								'ld_course_ribbon_enrolled_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_ribbon_enrolled_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
		'completed_ribbon' => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'Complete Ribbon', 'wpgb-learndash' ),
					'fields' => [
						[
							'type'   => 'grid',
							'fields' => [
								'ld_course_ribbon_completed_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_ribbon_completed_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
		'custom_ribbon'    => [
			'type'   => 'panel',
			'fields' => [
				'fieldset' => [
					'type'   => 'fieldset',
					'legend' => __( 'Custom Ribbon', 'wpgb-learndash' ),
					'fields' => [
						[
							'type'   => 'grid',
							'fields' => [
								'ld_course_ribbon_custom_bg'    => [
									'type'     => 'color',
									'label'    => __( 'Background', 'wpgb-learndash' ),
									'gradient' => true,
								],
								'ld_course_ribbon_custom_color' => [
									'type'     => 'color',
									'label'    => __( 'Color', 'wpgb-learndash' ),
									'gradient' => true,
								],
							],
						],
					],
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_course_ribbon',
];

$ld_course_description = [
	'title'           => __( 'Course Description', 'wpgb-learndash' ),
	'description'     => __( 'Displays the description of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-description-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'          => 'learndash_block',
				'idle_scheme'     => 'scheme-2',
				'learndash_block' => 'ld_course_description',
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle' => [
					'padding-top'    => 0,
					'padding-right'  => 0,
					'padding-bottom' => 0,
					'padding-left'   => 0,
					'margin-top'     => 0,
					'margin-right'   => 0,
					'margin-bottom'  => 0,
					'margin-left'    => 0,
					'color_scheme'   => 'scheme-2',
					'font-size'      => '1.125em',
					'font-weight'    => '300',
					'line-height'    => '1.6',
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_short_description',
];

$ld_course_button_text = [
	'title'           => __( 'Course Button', 'wpgb-learndash' ),
	'description'     => __( 'Displays the button of the course.', 'wpgb-learndash' ),
	'category'        => 'learndash_blocks',
	'tagName'         => 'div',
	'icon'            => WPGB_LEARNDASH_URL . '/assets/svg/sprite.svg#wpgb-button-icon',
	'attributes'      => [
		'content' => [
			'type'    => 'object',
			'default' => [
				'source'                        => 'learndash_block',
				'idle_scheme'                   => 'scheme-1',
				'hover_scheme'                  => 'accent-1',
				'learndash_block'               => 'ld_course_button_text',
				'ld_course_button_default_text' => __( 'See more...', 'wpgb-learndash' ),
			],
		],
		'style'   => [
			'type'    => 'object',
			'default' => [
				'idle'  => [
					'border-top-left-radius'     => '0.3em',
					'border-top-right-radius'    => '0.3em',
					'border-bottom-right-radius' => '0.3em',
					'border-bottom-left-radius'  => '0.3em',
					'border-top-width'           => '0.125em',
					'border-right-width'         => '0.125em',
					'border-bottom-width'        => '0.125em',
					'border-left-width'          => '0.125em',
					'border-style'               => 'solid',
					'margin-bottom'              => 0,
					'margin-top'                 => 0,
					'padding-top'                => '0.8em',
					'padding-right'              => '0.5em',
					'padding-bottom'             => '0.8em',
					'padding-left'               => '0.5em',
					'color_scheme'               => 'scheme-1',
					'font-size'                  => '1em',
					'font-weight'                => 500,
					'line-height'                => '1.4',
					'text-align'                 => 'center',
				],
				'hover' => [
					'hover_selector' => 'itself',
					'color_scheme'   => 'accent-1',
				],
			],
		],
		'action'  => [
			'type'    => 'object',
			'default' => [
				'action_type' => 'link',
			],
		],
	],
	'controls'        => [
		'panel' => [
			'type'   => 'panel',
			'fields' => [
				'ld_course_button_default_text' => [
					'type'  => 'text',
					'label' => __( 'Default Text', 'wpgb-learndash' ),
				],
			],
		],
	],
	'render_callback' => 'wpgb_ld_custom_button_text',
];

$ld_blocks = [
	'ld_course_progress_bar'    => $ld_course_progress_bar,
	'ld_course_progress_circle' => $ld_course_progress_circle,
	'ld_course_progress'        => $ld_course_progress,
	'ld_course_steps'           => $ld_course_steps,
	'ld_course_lessons'         => $ld_course_lessons,
	'ld_course_status'          => $ld_course_status,
	'ld_course_price'           => $ld_course_price,
	'ld_course_activity'        => $ld_course_activity,
	'ld_lesson_access_from'     => $ld_lesson_access_from,

];

if (
	( defined( 'LEARNDASH_COURSE_GRID_VERSION' ) && ! empty( LEARNDASH_COURSE_GRID_VERSION ) ) ||
	( defined( 'LEARNDASH_COURSE_GRID_FILE' ) && ! empty( LEARNDASH_COURSE_GRID_FILE ) )
) {

	$ld_blocks['ld_course_ribbon']      = $ld_course_ribbon;
	$ld_blocks['ld_course_description'] = $ld_course_description;
	$ld_blocks['ld_course_button_text'] = $ld_course_button_text;

}

if ( version_compare( WPGB_VERSION, '2.0.0-alpha', '<' ) ) {

	$ld_blocks = array_map(
		function( $block ) {

			$block['name']     = $block['title'];
			$block['type']     = $block['category'];
			$block['settings'] = array_map(
				function( $args ) {
					return $args['default'];
				},
				$block['attributes']
			);

			unset(
				$block['title'],
				$block['tagName'],
				$block['category'],
				$block['controls'],
				$block['attributes']
			);

			return $block;

		},
		$ld_blocks
	);
} else {
	unset( $ld_blocks['ld_course_progress_circle'] );
}

return $ld_blocks;
