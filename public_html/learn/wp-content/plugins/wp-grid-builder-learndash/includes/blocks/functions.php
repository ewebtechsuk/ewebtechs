<?php
/**
 * LearnDash blocks functions
 *
 * @package   WP Grid Builder - LearnDash
 * @author    Loïc Blascos
 * @copyright 2019-2024 Loïc Blascos
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get translation
 *
 * @since 1.0.0
 *
 * @param string  $type The text type to be used for translation.
 * @param integer $number The number to compare against to use either the singular or plural form.
 * @return string
 */
function wpgb_ld_i18n( $type, $number = 0 ) {

	$i18n = [
		/* translators: %1$s completed course steps, %2$s total course steps */
		'steps'       => sprintf( _n( '%1$s/%2$s Step', '%1$s/%2$s Steps', $number, 'wpgb-learndash' ), '[number]', '[total]' ),
		/* translators: %s course number of lessons. */
		'lessons'     => sprintf( _n( '%s Lesson', '%s Lessons', $number, 'wpgb-learndash' ), '[total]' ),
		/* translators: %s course last activity date. */
		'activity'    => sprintf( __( 'Last activity on %s', 'wpgb-learndash' ), '[date]' ),
		/* translators: %s course progress in percent. */
		'progress'    => sprintf( __( '%s%% Complete', 'wpgb-learndash' ), '[progress]' ),
		/* translators: %s course progress in percent. */
		'access_from' => sprintf( __( 'Available on %s', 'wpgb-learndash' ), '[date]' ),
	];

	if ( isset( $i18n[ $type ] ) ) {
		return $i18n[ $type ];
	}

	return '';

}

if ( ! function_exists( 'learndash_30_get_currency_symbol' ) && version_compare( LEARNDASH_VERSION, '4.0.0', '<' ) ) {

	/**
	 * Get currency symbol
	 *
	 * @since 1.0.3
	 *
	 * @return boolean
	 */
	function learndash_30_get_currency_symbol() {

		$options          = get_option( 'sfwd_cpt_options' );
		$currency_setting = class_exists( 'LearnDash_Settings_Section' ) ? LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_PayPal', 'paypal_currency' ) : null;
		$currency         = '';
		$stripe_settings  = get_option( 'learndash_stripe_settings' );

		if ( ! empty( $stripe_settings ) && ! empty( $stripe_settings['currency'] ) ) {
			$currency = $stripe_settings['currency'];
		} elseif ( isset( $currency_setting ) || ! empty( $currency_setting ) ) {
			$currency = $currency_setting;
		} elseif ( isset( $options['modules'] ) && isset( $options['modules']['sfwd-courses_options'] ) && isset( $options['modules']['sfwd-courses_options']['sfwd-courses_paypal_currency'] ) ) {
			$currency = $options['modules']['sfwd-courses_options']['sfwd-courses_paypal_currency'];
		}

		if ( class_exists( 'NumberFormatter' ) ) {

			$locale        = get_locale();
			$number_format = new NumberFormatter( $locale . '@currency=' . $currency, NumberFormatter::CURRENCY );
			$currency      = $number_format->getSymbol( NumberFormatter::CURRENCY_SYMBOL );

		}

		return $currency;

	}
}

/**
 * Check LearnDash courses post type
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function wpgb_ld_is_course() {

	return 'sfwd-courses' === wpgb_get_post_type();

}

/**
 * Check LearnDash lessons post type
 *
 * @since 1.1.0
 *
 * @return boolean
 */
function wpgb_ld_is_lesson() {

	return 'sfwd-lessons' === wpgb_get_post_type();

}

/**
 * Check LearnDash topic post type
 *
 * @since 1.1.0
 *
 * @return boolean
 */
function wpgb_ld_is_topic() {

	return 'sfwd-topic' === wpgb_get_post_type();

}

/**
 * Check if user has access to course
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function wpgb_ld_has_access() {

	$post = wpgb_get_post();

	if ( ! wpgb_ld_is_course() ) {
		return false;
	}

	if ( isset( $post->ld_has_access ) ) {
		return $post->ld_has_access;
	}

	$post->ld_has_access = false;

	if ( function_exists( 'sfwd_lms_has_access' ) ) {
		$post->ld_has_access = sfwd_lms_has_access( $post->ID );
	}

	return $post->ld_has_access;

}

/**
 * Check if user is enrolled in course
 *
 * @since 1.1.0 Added support for Lessons and Topic post types.
 * @since 1.0.0
 *
 * @return boolean
 */
function wpgb_ld_is_user_enrolled() {

	static $courses;

	if ( wpgb_is_overview() ) {
		return true;
	}

	if ( ! wpgb_ld_is_course() && ! wpgb_ld_is_lesson() && ! wpgb_ld_is_topic() ) {
		return false;
	}

	$course  = wpgb_get_the_id();
	$user_id = get_current_user_id();

	if ( wpgb_ld_is_lesson() || wpgb_ld_is_topic() ) {

		$course_id = learndash_get_course_id( wpgb_get_the_id() );

		if ( ! function_exists( 'sfwd_lms_has_access' ) ) {
			return false;
		}

		return sfwd_lms_has_access( $course_id, $user_id );

	}

	// Courses belong to an user, so we cache them for a WP instance.
	if ( empty( $courses ) ) {
		$courses = learndash_user_get_enrolled_courses( $user_id, [], true );
	}

	return in_array( $course, $courses, true );

}


/**
 * Get course id
 *
 * @since 1.0.0
 *
 * @return integer
 */
function wpgb_ld_get_course_id() {

	$post = wpgb_get_post();

	if ( ! isset( $post->ld_course_id ) ) {
		return 0;
	}

	return $post->ld_course_id;

}

/**
 * Get course status
 *
 * @since 1.1.0 Added support for Lessons and Topic post types.
 * @since 1.0.0
 *
 * @return string
 */
function wpgb_ld_get_course_status() {

	if ( wpgb_is_overview() ) {
		return 'progress';
	}

	if ( ! wpgb_ld_is_course() ) {
		return wpgb_ld_get_lesson_status();
	}

	if ( ! wpgb_ld_is_user_enrolled() ) {
		return 'unenrolled';
	}

	switch ( wpgb_ld_get_course_progress() ) {
		case 0:
			return 'enrolled';
		case 100:
			return 'complete';
		default:
			return 'progress';
	}
}

/**
 * Get lesson status
 *
 * @since 1.1.0
 *
 * @return string
 */
function wpgb_ld_get_lesson_status() {

	if ( wpgb_is_overview() ) {
		return 'progress';
	}

	if ( ! wpgb_ld_is_lesson() ) {
		return wpgb_ld_get_topic_status();
	}

	if ( ! wpgb_ld_is_user_enrolled() ) {
		return 'unenrolled';
	}

	if ( ! function_exists( 'learndash_is_lesson_complete' ) ) {
		return '';
	}

	$user_id   = get_current_user_id();
	$lesson_id = wpgb_get_the_id();
	$course_id = learndash_get_course_id( $lesson_id );

	if ( learndash_is_lesson_complete( $user_id, $lesson_id, $course_id ) ) {
		return 'complete';
	}

	return 'progress';

}

/**
 * Get lesson status
 *
 * @since 1.1.0
 *
 * @return string
 */
function wpgb_ld_get_topic_status() {

	if ( wpgb_is_overview() ) {
		return 'progress';
	}

	if ( ! wpgb_ld_is_topic() ) {
		return '';
	}

	if ( ! wpgb_ld_is_user_enrolled() ) {
		return 'unenrolled';
	}

	if ( ! function_exists( 'learndash_is_topic_complete' ) ) {
		return '';
	}

	$user_id   = get_current_user_id();
	$topic_id  = wpgb_get_the_id();
	$course_id = learndash_get_course_id( $topic_id );

	if ( learndash_is_topic_complete( $user_id, $topic_id, $course_id ) ) {
		return 'complete';
	}

	return 'progress';

}

/**
 * Get number of course steps
 *
 * @since 1.0.0
 *
 * @return integer
 */
function wpgb_ld_get_course_steps_total() {

	if ( wpgb_is_overview() ) {
		return 10;
	}

	if ( ! wpgb_ld_is_course() ) {
		return 0;
	}

	$post = wpgb_get_post();

	if ( isset( $post->ld_course_steps_total ) ) {
		return $post->ld_course_steps_total;
	}

	$post->ld_course_steps_total = 0;

	if ( function_exists( 'learndash_get_course_steps_count' ) ) {
		$post->ld_course_steps_total = (int) learndash_get_course_steps_count( $post->ID );
	}

	return $post->ld_course_steps_total;

}

/**
 * Get number of completed course steps
 *
 * @since 1.0.0
 *
 * @return integer
 */
function wpgb_ld_get_course_steps_completed() {

	if ( wpgb_is_overview() ) {
		return 6;
	}

	if ( ! wpgb_ld_is_course() ) {
		return 0;
	}

	$post = wpgb_get_post();

	if ( isset( $post->ld_course_steps_completed ) ) {
		return $post->ld_course_steps_completed;
	}

	$post->ld_course_steps_completed = 0;

	if ( function_exists( 'learndash_course_get_completed_steps' ) ) {

		$user_id = get_current_user_id();
		$post->ld_course_steps_completed = (int) learndash_course_get_completed_steps( $user_id, $post->ID );

	}

	return $post->ld_course_steps_completed;

}

/**
 * Get course progress in percent
 *
 * @since 1.0.0
 *
 * @return integer
 */
function wpgb_ld_get_course_progress() {

	if ( wpgb_is_overview() ) {
		return 60;
	}

	if ( ! wpgb_ld_is_course() ) {
		return 0;
	}

	$post_id  = wpgb_get_the_id();
	$user_id  = get_current_user_id();
	$progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	if ( empty( $progress[ $post_id ] ) ) {
		return 0;
	}

	$completed = 0;
	$progress  = $progress[ $post_id ];

	if ( isset( $progress['completed'] ) ) {
		$completed = absint( $progress['completed'] );
	}

	$total = wpgb_ld_get_course_steps_total();

	if ( $total < 1 ) {
		return 0;
	}

	$percentage = $completed * 100 / $total;
	$percentage = max( 0, min( 100, $percentage ) );

	return (int) $percentage;

}

/**
 * Check if course is completed
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function wpgb_ld_is_course_completed() {

	return 100 === wpgb_ld_get_course_progress();

}

/**
 * Display course lessons number
 *
 * @since 1.0.0
 *
 * @return integer
 */
function wpgb_ld_get_course_lessons() {

	if ( wpgb_is_overview() ) {
		return 4;
	}

	if ( ! wpgb_ld_is_course() ) {
		return 0;
	}

	$post = wpgb_get_post();

	if ( function_exists( 'learndash_course_get_steps_by_type' ) ) {
		return count( (array) learndash_course_get_steps_by_type( $post->ID, 'sfwd-lessons' ) );
	}

	return count(
		( new \WP_Query(
			[
				'post_type'              => 'sfwd-lessons',
				'post_status'            => 'published',
				'meta_key'               => 'course_id',
				'meta_value'             => wpgb_ld_get_course_id(),
				'posts_per_page'         => -1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'cache_results'          => false,
				'no_found_rows'          => true,
				'fields'                 => 'ids',
			]
		) )->posts
	);
}

/**
 * Get course user last activity
 *
 * @since 1.0.0
 *
 * @return object
 */
function wpgb_ld_get_user_activity() {

	$post = wpgb_get_post();

	if ( isset( $post->ld_user_activity ) ) {
		return $post->ld_user_activity;
	}

	$post->ld_user_activity = (object) [];

	if ( wpgb_is_overview() ) {

		return (object) [
			'activity_id'        => 0,
			'user_id'            => 0,
			'post_id'            => 0,
			'course_id'          => 0,
			'activity_type'      => 'course',
			'activity_status'    => '',
			'activity_started'   => 0,
			'activity_completed' => 0,
			'activity_updated'   => gmdate( 'U' ),
		];
	}

	if ( ! wpgb_ld_is_course() ) {
		return $post->ld_user_activity;
	}

	if ( function_exists( 'learndash_get_user_activity' ) ) {

		$post->ld_user_activity = (object) learndash_get_user_activity(
			[
				'activity_type' => 'course',
				'course_id'     => $post->ld_course_id,
				'post_id'       => $post->ld_course_id,
				'user_id'       => get_current_user_id(),
			]
		);
	}

	return $post->ld_user_activity;

}

/**
 * Get course options
 *
 * @since 1.0.0
 *
 * @param string $key Option name.
 * @return mixed
 */
function wpgb_ld_get_course_options( $key = '' ) {

	$post = wpgb_get_post();

	if ( ! isset( $post->ld_course_options ) ) {
		return false;
	}

	$options = $post->ld_course_options;

	if ( empty( $key ) ) {
		return $options;
	}

	if ( isset( $options[ $key ] ) ) {
		return $options[ $key ];
	}

	return '';

}

/**
 * Get course price type
 *
 * @since 1.0.0
 *
 * @return string
 */
function wpgb_ld_get_course_price_type() {

	return wpgb_ld_get_course_options( 'sfwd-courses_course_price_type' );

}

/**
 * Get course price
 *
 * @return string
 */
function wpgb_ld_get_course_price() {

	if ( wpgb_is_overview() ) {
		return '99$';
	}

	if ( ! wpgb_ld_is_course() ) {
		return '';
	}

	$price = wpgb_ld_get_course_options( 'sfwd-courses_course_price' );
	$currency = '';

	if ( is_numeric( $price ) && ! empty( $price ) ) {

		if ( function_exists( 'learndash_get_currency_symbol' ) ) {
			$currency = learndash_get_currency_symbol();
		} elseif ( function_exists( 'learndash_30_get_currency_symbol' ) ) {
			$currency = learndash_30_get_currency_symbol();
		}

		$format = apply_filters( 'learndash_course_grid_price_text_format', '{currency}{price}' );
		$price  = str_replace( [ '{currency}', '{price}' ], [ $currency, $price ], $format );

	} elseif ( empty( $price ) ) {
		$price = __( 'Free', 'wpgb-learndash' );
	}

	return $price;

}

/**
 * Get course ribbon type (LearnDash - Course grid add-on)
 *
 * @return string
 */
function wpgb_ld_get_ribbon_type() {

	$post = wpgb_get_post();

	if ( ! wpgb_ld_is_course() ) {
		return '';
	}

	if ( ! empty( $post->ld_course_ribbon_text ) ) {
		return 'custom';
	}

	$is_completed = wpgb_ld_is_course_completed();
	$price_type = wpgb_ld_get_course_price_type();
	$price = wpgb_ld_get_course_options( 'sfwd-courses_course_price' );

	if ( 'open' !== $price_type && wpgb_ld_has_access() ) {
		return $is_completed ? 'completed' : 'enrolled';
	} elseif ( 'open' === $price_type ) {

		if ( is_user_logged_in() ) {
			return $is_completed ? 'completed' : 'enrolled';
		}

		return '';

	} elseif ( 'closed' === $price_type && empty( $price ) ) {
		return is_numeric( $price ) ? 'price' : '';
	}

	if ( empty( $price ) ) {
		return 'free';
	}

	return 'price';

}

/**
 * Get course ribbon text (LearnDash - Course grid add-on)
 *
 * @return string
 */
function wpgb_ld_get_ribbon_text() {

	if ( wpgb_is_overview() ) {
		return __( 'Course Ribbon', 'wpgb-learndash' );
	}

	if ( ! wpgb_ld_is_course() ) {
		return '';
	}

	$post = wpgb_get_post();

	if ( isset( $post->ld_course_ribbon_text ) ) {
		$ribbon = $post->ld_course_ribbon_text;
	}

	if ( empty( $ribbon ) ) {

		$status = [
			'completed' => __( 'Completed', 'learndash-course-grid' ),
			'enrolled'  => __( 'Enrolled', 'learndash-course-grid' ),
			'free'      => __( 'Free', 'learndash-course-grid' ),
			'price'     => wpgb_ld_get_course_price(),
		];

		$ribbon = wpgb_ld_get_ribbon_type();
		$ribbon = isset( $status[ $ribbon ] ) ? $status[ $ribbon ] : '';

	}

	return $ribbon;

}

/**
 * Get short description (LearnDash - Course grid add-on)
 *
 * @since 1.1.0 Added support for Lessons and Topic post types.
 * @since 1.0.0
 *
 * @return string
 */
function wpgb_ld_get_short_description() {

	if ( wpgb_is_overview() ) {
		return wpgb_get_the_excerpt( 17, '' );
	}

	if ( ! wpgb_ld_is_course() && ! wpgb_ld_is_lesson() && ! wpgb_ld_is_topic() ) {
		return '';
	}

	return wpgb_get_metadata( '_learndash_course_grid_short_description' );

}

/**
 * Get custom button text (LearnDash - Course grid add-on)
 *
 * @since 1.1.0 Added support for Lessons and Topic post types.
 * @since 1.0.0
 *
 * @return string
 */
function wpgb_get_ld_custom_button_text() {

	if ( wpgb_is_overview() ) {
		return __( 'See more...', 'wpgb-learndash' );
	}

	if ( ! wpgb_ld_is_course() && ! wpgb_ld_is_lesson() && ! wpgb_ld_is_topic() ) {
		return '';
	}

	return wpgb_get_metadata( '_learndash_course_grid_custom_button_text' );

}

/**
 * Get progress bar markup
 *
 * @since 1.0.0
 *
 * @param array $block Holds block args.
 * @return string
 */
function wpgb_ld_get_progress_bar( $block = [] ) {

	$progress   = wpgb_ld_get_course_progress();
	$cr_width   = ! empty( $block['ld_course_progress_stroke_width'] ) ? $block['ld_course_progress_stroke_width'] : 1;
	$cr_radius  = 10 - (float) $cr_width / 2;
	$dasharray  = 2 * pi() * $cr_radius;
	$dashoffset = $dasharray * ( 1 - $progress / 100 );

	if ( isset( $block['ld_course_progress_type'] ) && 'circle' === $block['ld_course_progress_type'] ) {

		$content  = '<div class="wpgb-ld-course-progress-bar" role="progressbar" tabindex="0" aria-valuenow="%1$d" aria-valuemin="0" aria-valuemax="100">';
		$content .= '<svg aria-hidden="true" width="100%%" height="100%%" viewBox="0 0 20 20" >';
		$content .= ! empty( $block['ld_course_progress_stroke_background'] ) ? '<circle class="wpgb-ld-circle-background" fill="none" stroke="currentColor" r="%2$g" cx="10" cy="10"></circle>' : '';
		$content .= '<circle class="wpgb-ld-circle-color" fill="none" stroke="currentColor" r="%2$g" cx="10" cy="10" stroke-dasharray="%3$g" stroke-dashoffset="%4$g"></circle>';
		$content .= '</svg>';
		$content .= '</div>';

	} else {
		$content = '<div class="wpgb-ld-course-progress-bar" role="progressbar" tabindex="0" aria-valuenow="%1$d" aria-valuemin="0" aria-valuemax="100" style="width:%1$d%%"></div>';
	}

	return sprintf(
		$content,
		esc_attr( $progress ),
		esc_attr( $cr_radius ),
		esc_attr( $dasharray ),
		esc_attr( $dashoffset )
	);
}

/**
 * Get custom button text (LearnDash - Course grid add-on)
 *
 * @since 1.1.0
 *
 * @return string
 */
function wpgb_ld_get_lesson_access_from() {

	if ( wpgb_is_overview() ) {
		return date_i18n( get_option( 'date_format' ) );
	}

	if ( ! wpgb_ld_is_lesson() ) {
		return '';
	}

	$user_id     = get_current_user_id();
	$lesson_id   = wpgb_get_the_id();
	$course_id   = learndash_get_course_id( $lesson_id );
	$timestamp   = ld_lesson_access_from( $lesson_id, $user_id, $course_id );
	$bypass_from = learndash_can_user_bypass( $user_id, 'learndash_course_lesson_not_available', $lesson_id, wpgb_get_object() );
	$bypass_from = apply_filters( 'learndash_prerequities_bypass', $bypass_from, $user_id, $lesson_id, wpgb_get_object() );

	if ( empty( $timestamp ) || $bypass_from ) {
		return '';
	}

	return learndash_adjust_date_time_display( $timestamp );

}

if ( ! function_exists( 'wpgb_ld_course_progress_bar' ) ) {

	/**
	 * Display course progress bar
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_progress_bar( $block = [], $action = [] ) {

		if ( ! wpgb_ld_is_course() && ! wpgb_is_overview() ) {
			return;
		}

		if ( ! wpgb_ld_is_user_enrolled() ) {
			return;
		}

		wpgb_block_start( $block, $action );
			echo wpgb_ld_get_progress_bar( $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_progress' ) ) {

	/**
	 * Display course progress in percent
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_progress( $block = [], $action = [] ) {

		if ( ! wpgb_ld_is_course() && ! wpgb_is_overview() ) {
			return;
		}

		if ( ! wpgb_ld_is_user_enrolled() ) {
			return;
		}

		$content  = ! empty( $block['ld_course_progress_text'] ) ? $block['ld_course_progress_text'] : wpgb_ld_i18n( 'progress' );
		$progress = wpgb_ld_get_course_progress();

		wpgb_block_start( $block, $action );
			echo wp_kses_post( str_replace( '[progress]', $progress, $content ) );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_steps' ) ) {

	/**
	 * Display course steps number
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_steps( $block = [], $action = [] ) {

		$total   = wpgb_ld_get_course_steps_total();
		$number  = wpgb_ld_get_course_steps_completed();

		if ( empty( $total ) ) {
			return;
		}

		if ( ! wpgb_ld_is_user_enrolled() ) {
			return;
		}

		$content = wpgb_ld_i18n( 'steps', $total );
		$content = $total < 2 && ! empty( $block['ld_course_steps_singlular'] ) ? $block['ld_course_steps_singlular'] : $content;
		$content = $total > 1 && ! empty( $block['ld_course_steps_plural'] ) ? $block['ld_course_steps_plural'] : $content;

		wpgb_block_start( $block, $action );
			echo wp_kses_post( str_replace( [ '[number]', '[total]' ], [ $number, $total ], $content ) );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_lessons' ) ) {

	/**
	 * Display course lessons number
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_lessons( $block = [], $action = [] ) {

		$lessons = wpgb_ld_get_course_lessons();

		if ( empty( $lessons ) ) {
			return;
		}

		$content = wpgb_ld_i18n( 'lessons', $lessons );
		$content = $lessons < 2 && ! empty( $block['ld_course_lessons_singlular'] ) ? $block['ld_course_lessons_singlular'] : $content;
		$content = $lessons > 1 && ! empty( $block['ld_course_lessons_plural'] ) ? $block['ld_course_lessons_plural'] : $content;

		wpgb_block_start( $block, $action );
			echo wp_kses_post( str_replace( '[total]', $lessons, $content ) );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_status' ) ) {

	/**
	 * Display course status (unenrolled, enrolled, started, completed)
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_status( $block = [], $action = [] ) {

		$status = wpgb_ld_get_course_status();

		if ( empty( $block[ 'ld_course_status_' . $status . '_label' ] ) ) {
			return;
		}

		$block['class']  = isset( $block['class'] ) ? $block['class'] : '';
		$block['class'] .= ' wpgb-ld-status-' . $status;

		wpgb_block_start( $block, $action );
			echo wp_kses_post( $block[ 'ld_course_status_' . $status . '_label' ] );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_price' ) ) {

	/**
	 * Display course price
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_price( $block = [], $action = [] ) {

		$price = wpgb_ld_get_course_price();

		if ( empty( $price ) ) {
			return;
		}

		if ( ! wpgb_is_overview() && wpgb_ld_is_user_enrolled() ) {
			return '';
		}

		wpgb_block_start( $block, $action );
			echo esc_html( $price );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_activity' ) ) {

	/**
	 * Display course user last activity
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_activity( $block = [], $action = [] ) {

		$course = wpgb_ld_get_user_activity();

		if ( empty( $course->activity_updated ) ) {
			return;
		}

		$updated = date_i18n( get_option( 'date_format' ), $course->activity_updated );
		$content = ! empty( $block['ld_course_activity_text'] ) ? $block['ld_course_activity_text'] : wpgb_ld_i18n( 'activity' );

		wpgb_block_start( $block, $action );
			echo wp_kses_post( str_replace( '[date]', $updated, $content ) );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_course_ribbon' ) ) {

	/**
	 * Display course ribbon (LearnDash - Course grid add-on)
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_course_ribbon( $block = [], $action = [] ) {

		$type   = wpgb_ld_get_ribbon_type();
		$ribbon = wpgb_ld_get_ribbon_text();

		if ( empty( $ribbon ) ) {
			return;
		}

		$block['class']  = isset( $block['class'] ) ? $block['class'] : '';
		$block['class'] .= ' wpgb-ld-ribbon-' . $type;

		wpgb_block_start( $block, $action );
			echo esc_html( $ribbon );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_short_description' ) ) {

	/**
	 * Display short description (LearnDash - Course grid add-on)
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_short_description( $block = [], $action = [] ) {

		$description = wpgb_ld_get_short_description();

		if ( empty( $description ) ) {
			return;
		}

		wpgb_block_start( $block, $action );
			echo wp_kses_post( $description );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_custom_button_text' ) ) {

	/**
	 * Display custom button text (LearnDash - Course grid add-on)
	 *
	 * @since 1.0.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_custom_button_text( $block = [], $action = [] ) {

		$button_text = wpgb_get_ld_custom_button_text();

		if ( empty( $button_text ) && isset( $block['ld_course_button_default_text'] ) ) {
			$button_text = $block['ld_course_button_default_text'];
		}

		if ( empty( $button_text ) ) {
			return;
		}

		wpgb_block_start( $block, $action );
			echo wp_kses_post( $button_text );
		wpgb_block_end( $block, $action );

	}
}

if ( ! function_exists( 'wpgb_ld_lesson_access_from' ) ) {

	/**
	 * Display date of when a user can access the lesson.
	 *
	 * @since 1.1.0
	 *
	 * @param array $block  Holds block args.
	 * @param array $action Holds action args.
	 */
	function wpgb_ld_lesson_access_from( $block = [], $action = [] ) {

		$access_from = wpgb_ld_get_lesson_access_from();

		if ( empty( $access_from ) ) {
			return;
		}

		$content = ! empty( $block['ld_lesson_access_from_text'] ) ? $block['ld_lesson_access_from_text'] : wpgb_ld_i18n( 'access_from' );

		wpgb_block_start( $block, $action );
			echo wp_kses_post( str_replace( '[date]', $access_from, $content ) );
		wpgb_block_end( $block, $action );

	}
}
