<?php

/**
 * Function to return admin commuission on course.
 *
 * @since  1.0.0
 * @author Wbcom Designs
 */
function ld_get_admin_course_commission( $course_id ) {
	$value = get_post_meta( $course_id, 'admin-course-commission', true );
	return apply_filters( 'ld_get_admin_course_commission', (int) $value );
}

function ld_dashboard_set_cron_schedule( $schedules ) {
	$schedules['every_six_hours'] = array(
		'interval' => 21600,
		'display'  => esc_html__( 'Every 6 hours', 'ld-dashboard' ),
	);
	return $schedules;
}

add_filter( 'cron_schedules', 'ld_dashboard_set_cron_schedule' );

/**
 * Function to check if author is instructor.
 *
 * @since  1.0.0
 * @author Wbcom Designs
 */
function ld_check_if_author_is_instructor( $course_author ) {
	$course_author_data  = get_userdata( $course_author );
	$course_author_roles = $course_author_data->roles;
	if ( in_array( 'ld_instructor', (array) $course_author_roles ) ) {
		return apply_filters( 'ld_check_if_author_is_instructor', true );
	}
	return false;
}

function ld_dashboard_update_on_stripe_payment( $post_id, $post ) {
	$stripe_course_id = get_post_meta( $post_id, 'stripe_course_id', true );
	update_option( 'test_stripe_payment', $stripe_course_id );
}

if ( ! function_exists( 'ld_if_commission_enabled' ) ) {
	/**
	 * Check if monetization is enable
	 */
	function ld_if_commission_enabled() {
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['general_settings'];

		if ( isset( $settings['enable-revenue-sharing'] ) && '1' === $settings['enable-revenue-sharing'] ) {
			return true;
		}
		return false;
	}
}

function ld_dashboard_get_course_students( $course_id ) {
	$students       = array();
	$course_pricing = learndash_get_course_price( $course_id );
	if ( 'open' !== $course_pricing['type'] ) {
		$course_students = learndash_get_course_users_access_from_meta( $course_id );
		if ( ! empty( $course_students ) ) {
			$students = array_merge( $students, $course_students );
		}
		$course_group_ids = learndash_get_course_groups( $course_id );
		if ( ! empty( $course_group_ids ) ) {
			foreach ( $course_group_ids as $group_id ) {
				$group_users = learndash_get_groups_user_ids( $group_id );
				if ( ! empty( $group_users ) ) {
					$students = array_merge( $students, $group_users );
				}
			}
		}
	} else {
		$users = get_users();
		if ( ! empty( $users ) ) {
			foreach ( $users as $student ) {
				$students[] = $student->ID;
			}
		}
	}
	if ( ! empty( $students ) ) {
		$students = array_unique( $students );
	}
	return $students;
}

function ld_dashboard_check_if_zoom_credentials_exists() {
	$current_user               = wp_get_current_user();
	$function_obj               = Ld_Dashboard_Functions::instance();
	$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
	$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];

	$has_set_zoom_credentials  = true;
	$can_use_admin_credentials = ( isset( $settings['use-admin-account'] ) && 1 == $settings['use-admin-account'] ) ? true : false;
	if ( in_array( 'ld_instructor', $current_user->roles ) ) {
		if ( '' === get_user_meta( $current_user->ID, 'zoom_api_key', true ) || empty( get_user_meta( $current_user->ID, 'zoom_api_key', true ) ) ) {
			$has_set_zoom_credentials = false;
		}
		if ( '' === get_user_meta( $current_user->ID, 'zoom_api_secret', true ) || empty( get_user_meta( $current_user->ID, 'zoom_api_secret', true ) ) ) {
			$has_set_zoom_credentials = false;
		}
		if ( '' === get_user_meta( $current_user->ID, 'zoom_account_id', true ) || empty( get_user_meta( $current_user->ID, 'zoom_account_id', true ) ) ) {
			$has_set_zoom_credentials = false;
		}
	}
	if ( $can_use_admin_credentials && ! $has_set_zoom_credentials ) {
		$has_set_zoom_credentials = true;
	}
	if ( in_array( 'administrator', $current_user->roles ) || $can_use_admin_credentials ) {
		if ( ! isset( $settings['zoom-api-key'] ) || ( isset( $settings['zoom-api-key'] ) && '' === $settings['zoom-api-key'] ) ) {
			$has_set_zoom_credentials = false;
		}
		if ( ! isset( $settings['zoom-api-secret'] ) || ( isset( $settings['zoom-api-secret'] ) && '' === $settings['zoom-api-secret'] ) ) {
			$has_set_zoom_credentials = false;
		}
		if ( ! isset( $settings['zoom-account-id'] ) || ( isset( $settings['zoom-account-id'] ) && '' === $settings['zoom-account-id'] ) ) {
			$has_set_zoom_credentials = false;
		}
	}
	return $has_set_zoom_credentials;
}

function ld_get_local_time_difference( $time ) {
	$local_timezone = get_option( 'gmt_offset' );
	$is_negative    = false;
	if ( $local_timezone < 0 ) {
		$local_timezone = -1 * $local_timezone;
		$is_negative    = true;
	}
	$new_local_timezone  = $local_timezone * 10;
	$min                 = $new_local_timezone % 10;
	$new_local_timezone -= $min;
	$hr                  = $new_local_timezone / 10;
	if ( $min === 5 ) {
		$min = 30;
	}
	$time_difference = ( ( $hr * 60 ) + $min ) * 60;
	if ( $is_negative ) {
		$time_difference = -1 * $time_difference;
	}
	$str = strtotime( $time ) + $time_difference;
	return $str;
}

function ld_dashboard_is_instructor_group_leader() {
	$current_user = wp_get_current_user();
	if ( learndash_is_group_leader_user( $current_user->ID ) && in_array( 'ld_instructor', (array) $current_user->roles ) ) {
		return true;
	}
	return false;
}

function ld_get_global_commission_rate() {
	$function_obj               = Ld_Dashboard_Functions::instance();
	$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
	$settings                   = $ld_dashboard_settings_data['monetization_settings'];
	$global_commission          = ( isset( $settings['sharing-percentage-instructor'] ) ) ? $settings['sharing-percentage-instructor'] : 0;
	return apply_filters( 'ld_get_global_commission_rate', (int) $global_commission );
}

function ld_dashboard_get_instructor_earnings() {
	global $wpdb;
	$user_id          = get_current_user_id();
	$total_earnings   = get_user_meta( $user_id, 'instructor_total_earning', true );
	$total_commission = $wpdb->prepare( 'SELECT sum(commission) as commission FROM ' . $wpdb->prefix . 'ld_dashboard_instructor_commission_logs WHERE user_id = %d order by ID DESC', $user_id );
	$total_earnings   = $wpdb->get_var( $total_commission );

	return number_format_i18n( $total_earnings, 2 );
}

function ld_dashboard_get_sidebar_tabs() {
	$current_user                   = wp_get_current_user();
	$user_id                        = $current_user->ID;
	$function_obj                   = Ld_Dashboard_Functions::instance();
	$ld_dashboard_settings_data     = $function_obj->ld_dashboard_settings_data();
	$welcome_screen                 = $ld_dashboard_settings_data['welcome_screen'];
	$ld_dashboard_general_settings  = $ld_dashboard_settings_data['general_settings'];
	$monetization_settings          = $ld_dashboard_settings_data['monetization_settings'];
	$feed_settings                  = $ld_dashboard_settings_data['ld_dashboard_feed_settings'];
	$dashboard_page                 = $function_obj->ld_dashboard_get_url( 'dashboard' );
	$enable_instructor_earning_logs = isset( $ld_dashboard_general_settings['enable-instructor-earning-logs'] ) ? $ld_dashboard_general_settings['enable-instructor-earning-logs'] : '';
	$match_userroles                = array();
	$profile_url                    = '';
	if ( ! empty( $settings['disable_user_roles_live_feed'] ) ) {
		$match_userroles = array_intersect( $current_user_role, $settings['disable_user_roles_live_feed'] );
	}
	$enable_live_feed = false;
	if ( ( ! isset( $settings['disable-live-feed'] ) || $settings['disable-live-feed'] != 1 ) && ( empty( $match_userroles ) ) ) {
		$enable_live_feed = true;
	}

	if ( isset( $ld_dashboard_general_settings['redirect-profile'] ) && 1 == $ld_dashboard_general_settings['redirect-profile'] && class_exists( 'BuddyPress' ) ) {
		$profile_url = home_url( '/members/me/profile/' );
	} else {
		$profile_url = $dashboard_page . '?tab=profile';
	}

	$menu_items                        = array();
	$menu_items['all']['my-dashboard'] = array(
		'url'   => ( '' != $dashboard_page ) ? $dashboard_page : get_the_permalink(),
		'icon'  => '<span class="ld-icons ld-icon-dashboard-line"></span>',
		'label' => esc_html__( 'Dashboard', 'ld-dashboard' ),
	);

	$menu_items['all']['profile'] = array(
		'url'   => $profile_url,
		'icon'  => '<span class="ld-icons ld-icon-account-circle-line"></span>',
		'label' => esc_html__( 'Profile', 'ld-dashboard' ),
	);

	$menu_items['all']['enrolled-courses'] = array(
		'url'   => $dashboard_page . '?tab=enrolled-courses',
		'icon'  => '<span class="ld-icons ld-icon-book-mark-line"></span>',
		'label' => LearnDash_Custom_Label::get_label( 'courses' ),
	);

	$menu_items['all']['my-quiz-attempts'] = array(
		'url'   => $dashboard_page . '?tab=my-quiz-attempts',
		'icon'  => '<span class="ld-icons ld-icon-file-chart-line"></span>',
		'label' => sprintf( '%1s %2s', LearnDash_Custom_Label::get_label( 'quiz' ), esc_html__( 'Attempts', 'ld-dashboard' ) ),
	);
	if ( $enable_live_feed ) {
		$menu_items['all']['my-activity'] = array(
			'url'   => $dashboard_page . '?tab=my-activity',
			'icon'  => '<span class="ld-icons ld-icon-bell-exclamation"></span>',
			'label' => sprintf( '%1s %2s', LearnDash_Custom_Label::get_label( 'course' ), esc_html__( 'Activity', 'ld-dashboard' ) ),
		);
	}
	if ( $ld_dashboard_general_settings['enable-announcements'] == 1 ) {
		$menu_items['all']['announcements'] = array(
			'url'   => $dashboard_page . '?tab=announcements',
			'icon'  => '<span class="ld-icons ld-icon-bullhorn"></span>',
			'label' => esc_html__( 'My Announcements', 'ld-dashboard' ),
		);
	}
	$theme_locations = get_nav_menu_locations();
	if ( isset( $theme_locations['ld-dashboard-profile-menu'] ) ) {
		$menu_obj = get_term( $theme_locations['ld-dashboard-profile-menu'], 'nav_menu' );
		if ( is_object( $menu_obj ) && isset( $menu_obj->term_id ) ) {
			$custom_menu_items = wp_get_nav_menu_items( $menu_obj->term_id );
			if ( ! empty( $custom_menu_items ) ) {
				foreach ( $custom_menu_items as $menu_item ) :
					$menu_items['all'][ $menu_item->post_name ] = array(
						'url'   => $menu_item->url,
						'icon'  => '<span class="ld-icons ld-icon-link"></span>',
						'label' => $menu_item->title,
					);
				endforeach;
			}
		}
	}

	/***********************************************************
	 *  Course Management
	 */
	if ( learndash_is_admin_user( $user_id ) || ld_dashboard_instructor_user( $user_id ) || ld_dashboard_is_instructor_group_leader() || learndash_is_group_leader_user( $user_id ) ) {
		$menu_items['course-management']['my-courses']   = array(
			'url'   => $dashboard_page . '?tab=my-courses',
			'icon'  => '<span class="ld-icons ld-icon-book-reader"></span>',
			'label' => LearnDash_Custom_Label::get_label( 'courses' ),
		);
		$menu_items['course-management']['my-lessons']   = array(
			'url'   => $dashboard_page . '?tab=my-lessons',
			'icon'  => '<span class="ld-icons ld-icon-file-mark-line"></span>',
			'label' => LearnDash_Custom_Label::get_label( 'lessons' ),
		);
		$menu_items['course-management']['my-topics']    = array(
			'url'   => $dashboard_page . '?tab=my-topics',
			'icon'  => '<span class="ld-icons ld-icon-file-text-line"></span>',
			'label' => LearnDash_Custom_Label::get_label( 'topics' ),
		);
		$menu_items['course-management']['my-quizzes']   = array(
			'url'   => $dashboard_page . '?tab=my-quizzes',
			'icon'  => '<span class="ld-icons ld-icon-puzzle-piece"></span>',
			'label' => LearnDash_Custom_Label::get_label( 'quizzes' ),
		);
		$menu_items['course-management']['my-questions'] = array(
			'url'   => $dashboard_page . '?tab=my-questions',
			'icon'  => '<span class="ld-icons ld-icon-questionnaire-line"></span>',
			'label' => LearnDash_Custom_Label::get_label( 'questions' ),
		);
		$menu_items['course-management']['certificates'] = array(
			'url'   => $dashboard_page . '?tab=certificates',
			'icon'  => '<span class="ld-icons ld-icon-award"></span>',
			'label' => esc_html__( 'Certificates', 'ld-dashboard' ),
		);
		$menu_items['course-management']['assignments']  = array(
			'url'   => $dashboard_page . '?tab=assignments',
			'icon'  => '<span class="ld-icons ld-icon-file-edit-line"></span>',
			'label' => esc_html__( 'Assignments', 'ld-dashboard' ),
		);

		if ( $ld_dashboard_general_settings['enable-announcements'] == 1 ) {
			$menu_items['course-management']['my-announcements'] = array(
				'url'   => $dashboard_page . '?tab=my-announcements',
				'icon'  => '<span class="ld-icons ld-icon-volume-up-line"></span>',
				'label' => esc_html__( 'Announcements', 'ld-dashboard' ),
			);
		}
	}

	if ( learndash_is_admin_user( $user_id ) || learndash_is_group_leader_user( $user_id ) ) {
		$menu_items['course-management']['groups'] = array(
			'url'   => $dashboard_page . '?tab=groups',
			'icon'  => '<span class="ld-icons ld-icon-group-line"></span>',
			'label' => LearnDash_Custom_Label::get_label( 'groups' ),
		);
	}

	/***********************************************************
	 *  Report Tabs
	 */
	if ( learndash_is_admin_user( $user_id ) || learndash_is_group_leader_user( $user_id ) || ld_dashboard_instructor_user( $user_id ) ) {

		$menu_items['reports']['essay-report']      = array(
			'url'   => $dashboard_page . '?tab=essay-report',
			'icon'  => '<span class="ld-icons ld-icon-file-chart-line"></span>',
			'label' => __( 'Essay Report', 'ld-dashboard' ),
		);
		$menu_items['reports']['assignment-report'] = array(
			'url'   => $dashboard_page . '?tab=assignment-report',
			'icon'  => '<span class="ld-icons ld-icon-file-edit-line"></span>',
			'label' => __( 'Assignment Report', 'ld-dashboard' ),
		);
		$menu_items['reports']['quizz-report']      = array(
			'url'   => $dashboard_page . '?tab=quizz-report',
			'icon'  => '<span class="ld-icons ld-icon-puzzle-piece"></span>',
			'label' => __( 'Quiz Report', 'ld-dashboard' ),
		);

		$menu_items['reports']['quiz-attempts'] = array(
			'url'   => $dashboard_page . '?tab=quiz-attempts',
			'icon'  => '<span class="ld-icons ld-icon-feedback-line"></span>',
			'label' => sprintf( '%1s %2s', LearnDash_Custom_Label::get_label( 'quiz' ), esc_html__( 'Attempts', 'ld-dashboard' ) ),
		);

		$menu_items['reports']['submitted-essays'] = array(
			'url'   => $dashboard_page . '?tab=submitted-essays',
			'icon'  => '<span class="ld-icons ld-icon-file-copy-2-line"></span>',
			'label' => esc_html__( 'Submitted Essays', 'ld-dashboard' ),
		);

		$menu_items['reports']['course-report'] = array(
			'url'   => $dashboard_page . '?tab=course-report',
			'icon'  => '<span class="ld-icons ld-icon-file-chart-line"></span>',
			'label' => __( 'Course Reports', 'ld-dashboard' ),
		);

		if ( $enable_live_feed ) {
			$menu_items['reports']['activity'] = array(
				'url'   => $dashboard_page . '?tab=activity',
				'icon'  => '<span class="ld-icons ld-icon-pulse-line"></span>',
				'label' => sprintf( esc_html__( '%s Activity', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'course' ) ),
			);
		}
	}

	/***********************************************************
	 *  Monetization Tabs
	 */

	if ( ld_if_commission_enabled() ) {
		$menu_items['monetization']['withdrawal'] = array(
			'url'   => $dashboard_page . '?tab=withdrawal',
			'icon'  => '<span class="ld-icons ld-icon-wallet-line"></span>',
			'label' => esc_html__( 'Withdrawals', 'ld-dashboard' ),
		);

		if ( $enable_instructor_earning_logs == 1 ) {
			$menu_items['monetization']['earnings'] = array(
				'url'   => $dashboard_page . '?tab=earnings',
				'icon'  => '<span class="ld-icons ld-icon-file-list-3-line"></span>',
				'label' => esc_html__( 'Earning Logs', 'ld-dashboard' ),
			);
		}
	}

	/****************** Communication Tabs */
	if ( isset( $ld_dashboard_general_settings['enable-zoom'] ) && 1 == $ld_dashboard_general_settings['enable-zoom'] && apply_filters( 'ld_dashboard_zoom_meetings_tab', true ) ) {
			$menu_items['communication']['meetings'] = array(
				'url'   => $dashboard_page . '?tab=meetings',
				'icon'  => '<span class="ld-icons ld-icon-video-chat-line"></span>',
				'label' => esc_html__( 'Meetings', 'ld-dashboard' ),
			);
	}

	if ( isset( $ld_dashboard_general_settings['enable-email-integration'] ) && $ld_dashboard_general_settings['enable-email-integration'] == 1 ) {
		$menu_items['communication']['notification'] = array(
			'url'   => $dashboard_page . '?tab=notification',
			'icon'  => '<span class="ld-icons ld-icon-mail-send-line"></span>',
			'label' => esc_html__( 'Send Mail', 'ld-dashboard' ),
		);
	}
	if ( isset( $ld_dashboard_general_settings['enable-messaging-integration'] ) && $ld_dashboard_general_settings['enable-messaging-integration'] == 1 && class_exists( 'BuddyPress' ) && bp_is_active( 'messages' ) ) {
		$menu_items['communication']['private-messages'] = array(
			'url'   => $dashboard_page . '?tab=private-messages',
			'icon'  => '<span class="ld-icons ld-icon-mail-lock-line"></span>',
			'label' => esc_html__( 'Private Messages', 'ld-dashboard' ),
		);
	}
	$menu_items['communication']['invite-students'] = array(
		'url'   => $dashboard_page . '?tab=invite-students',
		'icon'  => '<span class="ld-icons ld-icon-file-chart-line"></span>',
		'label' => __( 'Invite Students', 'ld-dashboard' ),
	);

	/**
	 * Common tabs will dispaly to all users
	 */
	$menu_items['common']['settings'] = array(
		'url'   => $dashboard_page . '?tab=settings',
		'icon'  => '<span class="ld-icons ld-icon-user-settings-line"></span>',
		'label' => esc_html__( 'Settings', 'ld-dashboard' ),
	);
	$menu_items['common']['logout']   = array(
		'url'   => wp_logout_url( get_the_permalink() ),
		'icon'  => '<span class="ld-icons ld-icon-logout-box-r-line"></span>',
		'label' => __( 'Logout', 'ld-dashboard' ),
	);

	if ( ! learndash_is_group_leader_user( $user_id ) && ! learndash_is_admin_user( $user_id ) && ! in_array( 'ld_instructor', (array) $current_user->roles ) ) {
		unset( $menu_items['instructor'] );
	}
	return apply_filters( 'learndash_dashboard_nav_menu', $menu_items );
}

/**
 * Ld_dashboard_can_user_start_meeting
 *
 * @param  mixed $meeting_id meeting id.
 */
function ld_dashboard_can_user_start_meeting( $meeting_id ) {
	$current_user               = wp_get_current_user();
	$function_obj               = Ld_Dashboard_Functions::instance();
	$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
	$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];
	$meeting_post               = get_post( $meeting_id );
	$using_admin_credentials    = get_post_meta( $meeting_id, 'using_admin_credentials', true );
	$is_author                  = ( $meeting_post->post_author == $current_user->ID ) ? true : false;
	if ( in_array( 'administrator', $current_user->roles ) && 'yes' === $using_admin_credentials ) {
		$is_author = true;
	}
	if ( $is_author && in_array( 'ld_instructor', $current_user->roles ) && 'yes' === $using_admin_credentials ) {
		if ( empty( $settings['zoom-co-hosts'] ) || ( ! empty( $settings['zoom-co-hosts'] ) && ! in_array( $current_user->ID, $settings['zoom-co-hosts'] ) ) ) {
			$is_author = false;
		}
	}
	return $is_author;
}

function ld_group_leader_has_admin_cap() {
	$current_user = wp_get_current_user();
	if ( is_multisite() ) {
		$group_settings = get_site_option( 'learndash_groups_group_leader_user' );
	} else {
		$group_settings = get_option( 'learndash_groups_group_leader_user' );
	}
	if ( learndash_is_group_leader_user( $current_user->ID ) && isset( $group_settings['manage_courses_capabilities'] ) && 'advanced' === $group_settings['manage_courses_capabilities'] ) {
		return true;
	}
	return false;
}

function ld_can_user_manage_courses() {
	$current_user = wp_get_current_user();
	if ( is_multisite() ) {
		$group_settings = get_site_option( 'learndash_groups_group_leader_user' );
	} else {
		$group_settings = get_option( 'learndash_groups_group_leader_user' );
	}
	if ( in_array( 'ld_instructor', $current_user->roles ) ) {
		return true;
	}
	if ( learndash_is_group_leader_user( $current_user->ID ) && isset( $group_settings['manage_courses_enabled'] ) && 'yes' == $group_settings['manage_courses_enabled'] ) {
		return true;
	}
	return false;
}

function ld_dashboard_check_course_transaction_exists( $user_id, $course_id ) {
	$course_price       = learndash_get_setting( $course_id, 'course_price' );
	$course_access_type = learndash_get_setting( $course_id, 'course_price_type' );
	$settinf            = learndash_get_setting( $course_id );
	$key                = 'course_' . $course_id . '_access_from';

	$payed             = false;
	$transaction_query = array(
		'post_type'   => 'sfwd-transactions',
		'post_status' => 'publish',
		'meta_query'  => array(
			array(
				'key'     => 'user_id',
				'value'   => $user_id,
				'compare' => '==',
			),
			array(
				'key'     => 'course_id',
				'value'   => $course_id,
				'compare' => '==',
			),
		),
	);

	$transactions = get_posts( $transaction_query );

	if ( 'closed' !== $course_access_type && is_array( $transactions ) && ! empty( $transactions ) ) {

		$payed               = true;
		list( $transaction ) = $transactions;
		$payment_processor   = get_post_meta( $transaction->ID, 'ld_payment_processor', true );

	} elseif ( 'closed' === $course_access_type ) {

		$product_url = isset( $settinf['custom_button_url'] ) ? $settinf['custom_button_url'] : '';
		if ( '' !== $product_url ) {
			$product_slugs = explode( '/', $product_url );
			$count         = count( $product_slugs ) - 1;
			$slug          = '';
			while ( '' === $slug ) {
				if ( isset( $product_slugs[ $count ] ) && '' !== $product_slugs[ $count ] ) {
					$slug = $product_slugs[ $count ];
				}
				$count--;
			}

			$args     = array(
				'name'        => $slug,
				'post_type'   => 'product',
				'post_status' => 'publish',
				'numberposts' => 1,
			);
			$products = get_posts( $args );
			if ( is_array( $products ) && ! empty( $products ) ) {
				list( $product ) = $products;
				$order_args      = array(
					'customer_id' => $user_id,
					'type'        => 'shop_order',
					'limit'       => - 1,
				);
				$orders          = wc_get_orders( $order_args );
				if ( is_array( $orders ) && ! empty( $orders ) ) {
					foreach ( $orders as $order ) {
						if ( $payed ) {
							break;
						}
						$current_order = wc_get_order( $order->get_id() );
						foreach ( $current_order->get_items() as $item_key => $item_values ) {
							$order_product_id = $item_values->get_product_id();
							if ( $product->ID === $order_product_id ) {
								$payed = true;
								break;
							}
						}
					}
				}
			}
		}
	}
	return $payed;
}

/**
 * Function to find source of commission for admin.
 *
 * @since  1.0.0
 * @author Wbcom Designs
 */
function ld_if_instructor_course_commission_set( $instrcutor_id ) {
	$value = get_user_meta( $instrcutor_id, 'instructor-course-commission', true );
	if ( $value ) {
		return apply_filters( 'ld_if_instructor_course_commission_set', (int) $value );
	}
	return false;
}

function ld_bptodo_get_course_list( $course_id, $user_id, $group_id, $can_modify ) {
	$args               = array(
		'post_type'      => 'bp-todo',
		'post_status'    => 'publish',
		'author'         => $user_id,
		'posts_per_page' => -1,
		'meta_key'       => 'todo_group_id',
		'meta_value'     => $group_id,
	);
	$todos              = get_posts( $args );
	$todo_list          = array();
	$all_todo_count     = 0;
	$all_completed_todo = 0;
	$all_remaining_todo = 0;
	$completed_todo_ids = array();
	foreach ( $todos as $todo ) {
		$curr_date   = date_create( date( 'Y-m-d' ) );
		$due_date    = date_create( get_post_meta( $todo->ID, 'todo_due_date', true ) );
		$todo_status = get_post_meta( $todo->ID, 'todo_status', true );
		$diff        = date_diff( $curr_date, $due_date );
		$diff_days   = $diff->format( '%R%a' );
		if ( $diff_days < 0 ) {
			$todo_list['past'][] = $todo->ID;
		} elseif ( 0 == $diff_days ) {
			$todo_list['today'][] = $todo->ID;
		} elseif ( 1 == $diff_days ) {
			$todo_list['tomorrow'][] = $todo->ID;
		} else {
			$todo_list['future'][] = $todo->ID;
		}
	}
	return apply_filters( 'alter_ld_bptodo_get_course_list', $todo_list );
}

function ld_generate_tbody_for_ld_course_todos( $todo_list, $can_modify, $group_id ) {
	global $bptodo;
	$profile_menu_slug = $bptodo->profile_menu_slug;

	$group       = groups_get_group( array( 'group_id' => $group_id ) );
	$groups_link = bp_get_group_permalink( $group );
	$admin_link  = trailingslashit( $groups_link . $profile_menu_slug );

	$all_remaining_todo = 0;
	$all_completed_todo = 0;
	ob_start();
	?>

	<!-- PAST TASKS -->
	<?php
	if ( ! empty( $todo_list['past'] ) ) {
		$count = 1;
		foreach ( $todo_list['past'] as $tid ) {
			?>
			<?php
			$todo          = get_post( $tid );
			$todo_title    = $todo->post_title;
			$todo_edit_url = $admin_link . '/add?args=' . $tid;

			$todo_status    = get_post_meta( $todo->ID, 'todo_status', true );
			$todo_priority  = get_post_meta( $todo->ID, 'todo_priority', true );
			$due_date_str   = $due_date_td_class = '';
			$curr_date      = date_create( date( 'Y-m-d' ) );
			$due_date       = date_create( get_post_meta( $todo->ID, 'todo_due_date', true ) );
			$diff           = date_diff( $curr_date, $due_date );
			$diff_days      = $diff->format( '%R%a' );
			$priority_class = '';
			if ( $diff_days < 0 ) {
				$due_date_str      = sprintf( esc_html__( 'Expired %d days ago!', 'wb-todo' ), abs( $diff_days ) );
				$due_date_td_class = 'bptodo-expired';
			} elseif ( 0 == $diff_days ) {
				$due_date_str      = esc_html__( 'Today is the last day to complete. Hurry Up!', 'wb-todo' );
				$due_date_td_class = 'bptodo-expires-today';
			} else {
				if ( $diff_days == 1 ) {
					$day_string = __( 'day', 'wb-todo' );
				} else {
					$day_string = __( 'days', 'wb-todo' );
				}
				$due_date_str = sprintf( esc_html__( '%1$d %2$s left to complete the task!', 'wb-todo' ), abs( $diff_days ), $day_string );
										// $all_remaining_todo++;
			}
			if ( 'complete' == $todo_status ) {
				$due_date_str      = esc_html__( 'Completed!', 'wb-todo' );
				$due_date_td_class = '';
				$all_completed_todo++;
			}
			if ( ! empty( $todo_priority ) ) {
				if ( 'critical' == $todo_priority ) {
					$priority_class = 'bptodo-priority-critical';
					$priority_text  = esc_html__( 'Critical', 'wb-todo' );
				} elseif ( 'high' == $todo_priority ) {
					$priority_class = 'bptodo-priority-high';
					$priority_text  = esc_html__( 'High', 'wb-todo' );
				} else {
					$priority_class = 'bptodo-priority-normal';
					$priority_text  = esc_html__( 'Normal', 'wb-todo' );
				}
			}
			?>
			<tr id="bptodo-row-<?php echo esc_attr( $tid ); ?>">
				<td class="bptodo-priority"><span class="<?php echo esc_attr( $priority_class ); ?>"><?php echo $priority_text; ?></span></td>
				<td class="
				<?php
				if ( 'complete' == $todo_status ) {
					echo esc_attr( $class );
				}
				?>
				"><?php echo esc_html( $todo_title ); ?></td>
				<td class="
				<?php
				echo esc_attr( $due_date_td_class );
				if ( 'complete' == $todo_status ) {
					echo esc_attr( $class );
				}
				?>
				"><?php echo $due_date_str; ?></td>
				<td class="bp-to-do-actions">
					<ul>
						<?php if ( $can_modify ) { ?>
							<li><a href="javascript:void(0);" class="bptodo-remove-todo" data-tid="<?php echo esc_attr( $tid ); ?>"    title="<?php echo sprintf( esc_html__( 'Remove: %s', 'wb-todo' ), $todo_title ); ?>"
								><i class="fa fa-times"></i></a></li>
							<?php } ?>
							<?php if ( 'complete' !== $todo_status ) { ?>
								<?php if ( $can_modify ) { ?>
									<li><a href="<?php echo esc_attr( $todo_edit_url ); ?>" title="<?php echo sprintf( esc_html__( 'Edit: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-edit"></i></a></li>
								<?php } ?>
								<li id="bptodo-complete-li-<?php echo esc_attr( $tid ); ?>"><a href="javascript:void(0);" class="bptodo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-check"></i></a></li>
							<?php } else { ?>
								<li><a href="javascript:void(0);" class="bptodo-undo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Undo Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-undo"></i></a></li>
							<?php } ?>
						</ul>
					</td>
				</tr>
			<?php } ?>

		<?php } ?>

		<?php if ( ! empty( $todo_list['today'] ) ) { ?>
			<?php $count = 1; ?>
			<?php foreach ( $todo_list['today'] as $tid ) { ?>
				<?php
				$todo          = get_post( $tid );
				$todo_title    = $todo->post_title;
				$todo_edit_url = $admin_link . '/add?args=' . $tid;

				$todo_status   = get_post_meta( $todo->ID, 'todo_status', true );
				$todo_priority = get_post_meta( $todo->ID, 'todo_priority', true );
				$due_date_str  = $due_date_td_class  = '';
				$curr_date     = date_create( date( 'Y-m-d' ) );
				$due_date      = date_create( get_post_meta( $todo->ID, 'todo_due_date', true ) );
				$diff          = date_diff( $curr_date, $due_date );
				$diff_days     = $diff->format( '%R%a' );
				if ( $diff_days < 0 ) {
					$due_date_str      = sprintf( esc_html__( 'Expired %d days ago!', 'wb-todo' ), abs( $diff_days ) );
					$due_date_td_class = 'bptodo-expired';
				} elseif ( 0 == $diff_days ) {
					$due_date_str      = esc_html__( 'Today is the last day to complete. Hurry Up!', 'wb-todo' );
					$due_date_td_class = 'bptodo-expires-today';
					$all_remaining_todo++;
				} else {
					if ( $diff_days == 1 ) {
						$day_string = esc_html__( 'day', 'wb-todo' );
					} else {
						$day_string = esc_html__( 'days', 'wb-todo' );
					}
					$due_date_str = sprintf( esc_html__( '%1$d %2$s left to complete the task!', 'wb-todo' ), abs( $diff_days ), $day_string );
					$all_remaining_todo++;
				}
				if ( 'complete' == $todo_status ) {
					$due_date_str      = esc_html__( 'Completed!', 'wb-todo' );
					$due_date_td_class = '';
					$all_completed_todo++;
				}
				if ( ! empty( $todo_priority ) ) {
					if ( 'critical' == $todo_priority ) {
						$priority_class = 'bptodo-priority-critical';
						$priority_text  = esc_html__( 'Critical', 'wb-todo' );
					} elseif ( 'high' == $todo_priority ) {
						$priority_class = 'bptodo-priority-high';
						$priority_text  = esc_html__( 'High', 'wb-todo' );
					} else {
						$priority_class = 'bptodo-priority-normal';
						$priority_text  = esc_html__( 'Normal', 'wb-todo' );
					}
				}
				?>
				<tr id="bptodo-row-<?php echo esc_attr( $tid ); ?>">
					<td class="bptodo-priority"><span class="<?php echo esc_attr( $priority_class ); ?>"><?php echo $priority_text; ?></span></td>
					<td class="
					<?php
					if ( 'complete' == $todo_status ) {
						echo esc_attr( $class );
					}
					?>
					"><?php echo esc_html( $todo_title ); ?></td>
					<td class="
					<?php
					echo esc_attr( $due_date_td_class );
					if ( 'complete' == $todo_status ) {
						echo esc_attr( $class );
					}
					?>
					"><?php echo $due_date_str; ?></td>
					<td class="bp-to-do-actions">
						<ul>
							<?php if ( $can_modify ) { ?>
								<li><a href="javascript:void(0);" class="bptodo-remove-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Remove: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-times"></i></a></li>
							<?php } ?>
							<?php if ( 'complete' !== $todo_status ) { ?>
								<?php if ( $can_modify ) { ?>
									<li><a href="<?php echo esc_attr( $todo_edit_url ); ?>" title="<?php echo sprintf( esc_html__( 'Edit: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-edit"></i></a></li>
								<?php } ?>
								<li id="bptodo-complete-li-<?php echo esc_attr( $tid ); ?>"><a href="javascript:void(0);" class="bptodo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-check"></i></a></li>
							<?php } else { ?>
								<li><a href="javascript:void(0);" class="bptodo-undo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Undo Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-undo"></i></a></li>
							<?php } ?>
						</ul>
					</td>
				</tr>
			<?php } ?>

		<?php } ?>
		<!-- TASKS FOR TOMORROW -->
		<?php if ( ! empty( $todo_list['tomorrow'] ) ) { ?>

			<?php $count = 1; ?>
			<?php foreach ( $todo_list['tomorrow'] as $tid ) { ?>
				<?php
				$todo          = get_post( $tid );
				$todo_title    = $todo->post_title;
				$todo_edit_url = $admin_link . '/add?args=' . $tid;

				$todo_status   = get_post_meta( $todo->ID, 'todo_status', true );
				$todo_priority = get_post_meta( $todo->ID, 'todo_priority', true );
				$due_date_str  = $due_date_td_class = '';
				$curr_date     = date_create( date( 'Y-m-d' ) );
				$due_date      = date_create( get_post_meta( $todo->ID, 'todo_due_date', true ) );
				$diff          = date_diff( $curr_date, $due_date );
				$diff_days     = $diff->format( '%R%a' );
				if ( $diff_days < 0 ) {
					$due_date_str      = sprintf( esc_html__( 'Expired %d days ago!', 'wb-todo' ), abs( $diff_days ) );
					$due_date_td_class = 'bptodo-expired';
				} elseif ( 0 == $diff_days ) {
					$due_date_str      = esc_html__( 'Today is the last day to complete. Hurry Up!', 'wb-todo' );
					$due_date_td_class = 'bptodo-expires-today';
					$all_remaining_todo++;
				} else {
					if ( $diff_days == 1 ) {
						$day_string = esc_html__( 'day', 'wb-todo' );
					} else {
						$day_string = esc_html__( 'days', 'wb-todo' );
					}
					$due_date_str = sprintf( esc_html__( '%1$d %2$s left to complete the task!', 'wb-todo' ), abs( $diff_days ), $day_string );
					$all_remaining_todo++;
				}
				if ( 'complete' == $todo_status ) {
					$due_date_str      = esc_html__( 'Completed!', 'wb-todo' );
					$due_date_td_class = '';
					$all_completed_todo++;
				}
				if ( ! empty( $todo_priority ) ) {
					if ( 'critical' == $todo_priority ) {
						$priority_class = 'bptodo-priority-critical';
						$priority_text  = esc_html__( 'Critical', 'wb-todo' );
					} elseif ( 'high' == $todo_priority ) {
						$priority_class = 'bptodo-priority-high';
						$priority_text  = esc_html__( 'High', 'wb-todo' );
					} else {
						$priority_class = 'bptodo-priority-normal';
						$priority_text  = esc_html__( 'Normal', 'wb-todo' );
					}
				}
				?>
				<tr id="bptodo-row-<?php echo esc_attr( $tid ); ?>">
					<td class="bptodo-priority"><span class="<?php echo esc_attr( $priority_class ); ?>"><?php echo $priority_text; ?></span></td>
					<td class="
					<?php
					if ( 'complete' == $todo_status ) {
						echo esc_attr( $class );
					}
					?>
					"><?php echo esc_html( $todo_title ); ?></td>
					<td class="
					<?php
					echo esc_attr( $due_date_td_class );
					if ( 'complete' == $todo_status ) {
						echo esc_attr( $class );
					}
					?>
					"><?php echo $due_date_str; ?></td>
					<td class="bp-to-do-actions">
						<ul>
							<?php if ( $can_modify ) { ?>
								<li><a href="javascript:void(0);" class="bptodo-remove-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Remove: %s ', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-times"></i></a></li>
							<?php } ?>
							<?php if ( 'complete' !== $todo_status ) { ?>
								<?php if ( $can_modify ) { ?>
									<li><a href="<?php echo esc_attr( $todo_edit_url ); ?>" title="<?php echo sprintf( esc_html__( 'Edit: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-edit"></i></a></li>
								<?php } ?>
								<li id="bptodo-complete-li-<?php echo esc_attr( $tid ); ?>"><a href="javascript:void(0);" class="bptodo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-check"></i></a></li>
							<?php } else { ?>
								<li><a href="javascript:void(0);" class="bptodo-undo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Undo Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-undo"></i></a></li>
							<?php } ?>
						</ul>
					</td>
				</tr>
			<?php } ?>

		<?php } ?>

		<!-- TASKS FOR SOMEDAY. -->
		<?php if ( ! empty( $todo_list['future'] ) ) { ?>
			<?php $count = 1; ?>
			<?php foreach ( $todo_list['future'] as $tid ) { ?>
				<?php
				$todo          = get_post( $tid );
				$todo_title    = $todo->post_title;
				$todo_edit_url = $admin_link . '/add?args=' . $tid;

				$todo_status   = get_post_meta( $todo->ID, 'todo_status', true );
				$todo_priority = get_post_meta( $todo->ID, 'todo_priority', true );
				$due_date_str  = $due_date_td_class    = '';
				$curr_date     = date_create( date( 'Y-m-d' ) );
				$due_date      = date_create( get_post_meta( $todo->ID, 'todo_due_date', true ) );
				$diff          = date_diff( $curr_date, $due_date );
				$diff_days     = $diff->format( '%R%a' );
				if ( $diff_days < 0 ) {
					$due_date_str      = sprintf( esc_html__( 'Expired %d days ago!', 'wb-todo' ), abs( $diff_days ) );
					$due_date_td_class = 'bptodo-expired';
				} elseif ( 0 == $diff_days ) {
					$due_date_str      = esc_html__( 'Today is the last day to complete. Hurry Up!', 'wb-todo' );
					$due_date_td_class = 'bptodo-expires-today';
					$all_remaining_todo++;
				} else {
					if ( $diff_days == 1 ) {
						$day_string = esc_html__( 'day', 'wb-todo' );
					} else {
						$day_string = esc_html__( 'days', 'wb-todo' );
					}
					$due_date_str = sprintf( esc_html__( '%1$d %2$s left to complete the task!', 'wb-todo' ), abs( $diff_days ), $day_string );
					$all_remaining_todo++;
				}
				if ( 'complete' == $todo_status ) {
					$due_date_str      = esc_html__( 'Completed!', 'wb-todo' );
					$due_date_td_class = '';
					$all_completed_todo++;
				}
				if ( ! empty( $todo_priority ) ) {
					if ( 'critical' == $todo_priority ) {
						$priority_class = 'bptodo-priority-critical';
						$priority_text  = esc_html__( 'Critical', 'wb-todo' );
					} elseif ( 'high' == $todo_priority ) {
						$priority_class = 'bptodo-priority-high';
						$priority_text  = esc_html__( 'High', 'wb-todo' );
					} else {
						$priority_class = 'bptodo-priority-normal';
						$priority_text  = esc_html__( 'Normal', 'wb-todo' );
					}
				}
				?>
				<tr id="bptodo-row-<?php echo esc_attr( $tid ); ?>">
					<td class="bptodo-priority"><span class="<?php echo esc_attr( $priority_class ); ?>"><?php echo $priority_text; ?></span></td>
					<td class="
					<?php
					if ( 'complete' == $todo_status ) {
						echo esc_attr( $class );
					}
					?>
					"><?php echo esc_html( $todo_title ); ?></td>
					<td class="
					<?php
					echo esc_attr( $due_date_td_class );
					if ( 'complete' == $todo_status ) {
						echo esc_attr( $class );
					}
					?>
					"><?php echo $due_date_str; ?></td>
					<td class="bp-to-do-actions">
						<ul>
							<?php if ( $can_modify ) { ?>
								<li><a href="javascript:void(0);" class="bptodo-remove-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Remove: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-times"></i></a></li>
							<?php } ?>
							<?php if ( 'complete' != $todo_status ) { ?>
								<?php if ( $can_modify ) { ?>
									<li><a href="<?php echo esc_attr( $todo_edit_url ); ?>" title="<?php echo sprintf( esc_html__( 'Edit: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-edit"></i></a></li>
								<?php } ?>
								<li id="bptodo-complete-li-<?php echo esc_attr( $tid ); ?>"><a href="javascript:void(0);" class="bptodo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-check"></i></a></li>
							<?php } else { ?>
								<li><a href="javascript:void(0);" class="bptodo-undo-complete-todo" data-tid="<?php echo esc_attr( $tid ); ?>" title="<?php echo sprintf( esc_html__( 'Undo Complete: %s', 'wb-todo' ), $todo_title ); ?>"><i class="fa fa-undo"></i></a></li>
							<?php } ?>
						</ul>
					</td>
				</tr>
			<?php } ?>

		<?php } ?>
	<?php
	$menu_item_slugody_html = ob_get_clean();
	return apply_filters( 'alter_ld_generate_tbody_for_ld_course_todos', $menu_item_slugody_html );
}

function ld_generate_course_group_to_do_list_table( $todo_tbody ) {
	$menu_item_slugody  = '<tbody>';
	$menu_item_slugody .= $todo_tbody;
	$menu_item_slugody .= '</tbody>';

	$thead  = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . __( 'Priority', 'ld-dashboard' ) . '</th>';
	$thead .= '<th>' . __( 'Task', 'ld-dashboard' ) . '</th>';
	$thead .= '<th>' . __( 'Due Date', 'ld-dashboard' ) . '</th>';
	$thead .= '<th>' . __( 'Actions', 'ld-dashboard' ) . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';

	$html  = '';
	$html .= '<div id="bptodo-all">';
	$html .= '<div class="bptodo-admin-row">';
	$html .= '<div class="todo-panel">';
	$html .= '<div class="todo-detail">';
	$html .= '<table class="bp-todo-reminder">';
	$html .= $thead;
	$html .= $menu_item_slugody;
	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

function ld_is_envt_ready_for_to_do() {
	if ( class_exists( 'BuddyPress' ) && bp_is_active( 'groups' ) && class_exists( 'Bptodo_Profile_Menu' ) ) {
		return true;
	}
	return false;
}

/**
 * Function to find if learndash groups is enabled.
 *
 * @since  1.0.0
 * @author Wbcom Designs
 */
function ld_if_display_to_do_enabled() {
	$function_obj               = Ld_Dashboard_Functions::instance();
	$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
	$settings                   = $ld_dashboard_settings_data['general_settings'];
	$ld_dashboard_integration   = $ld_dashboard_settings_data['ld_dashboard_integration'];
	$display_todo               = ( isset( $ld_dashboard_integration['display-to-do'] ) ) ? $ld_dashboard_integration['display-to-do'] : '';
	if ( $display_todo == '1' && class_exists( 'BuddyPress' ) && function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) && class_exists( 'Bptodo_Profile_Menu' ) ) {
		return apply_filters( 'ld_if_display_to_do_enabled', true );
	}
	return false;
}

if ( ! function_exists( 'ld_todo_get_user_average_todos' ) ) {
	/**
	 * Display average todo percentage of each member
	 *
	 * @param  integer $todoID  The id of post(TO DO)
	 * @return float         Average percentage of todo
	 */
	function ld_todo_get_user_average_todos( $todoID ) {
		global $bp, $post;
		$group_id        = get_post_meta( $todoID, 'todo_group_id', true );
		$todo_primary_id = get_post_meta( $todoID, 'todo_primary_id', true );

		$total_args = array(
			'post_type'      => 'bp-todo',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'todo_group_id',
					'value'   => $group_id,
					'compare' => '=',
				),
				array(
					'key'     => 'todo_primary_id',
					'value'   => $todo_primary_id,
					'compare' => '=',
				),
			),
		);

		$todos       = get_posts( $total_args );
		$total_count = 0;
		if ( ! empty( $todos ) ) {
			$total_count = count( $todos );
		}

		$args = array(
			'group_id'            => $group_id,
			'exclude_admins_mods' => true,
		);

		$group_members_result = groups_get_group_members( $args );
		$group_members_ids    = array();

		foreach ( $group_members_result['members'] as $member ) {
			$group_members_ids[] = $member->ID;
		}

		$member_count = count( $group_members_ids );

		$completed_count = ld_todo_completed_todo_count( $group_id, $todo_primary_id );

		$avg_rating = 0;

		if ( ! empty( $member_count ) ) {
			$avg_rating = ( $completed_count * 100 ) / $member_count;
			$avg_rating = round( $avg_rating, 2 ) . '% ';
		}
		return $avg_rating;

		wp_reset_postdata();

	}
}

if ( ! function_exists( 'ld_todo_completed_todo_count' ) ) {
	/**
	 * Get the completed to do count
	 *
	 * @param  [int] $group_id        Accosiated group id
	 * @param  [int] $todo_primary_id Primary to-do id
	 * @return [float]                Count of completed to-dos
	 */
	function ld_todo_completed_todo_count( $group_id, $todo_primary_id ) {
		$associated_todo = get_post_meta( $todo_primary_id, 'botodo_associated_todo', true );

		$completed_args  = array(
			'post_type'      => 'bp-todo',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'todo_status',
					'value'   => 'complete',
					'compare' => '=',
				),
				array(
					'key'     => 'todo_group_id',
					'value'   => $group_id,
					'compare' => '=',
				),
				array(
					'key'     => 'todo_primary_id',
					'value'   => $todo_primary_id,
					'compare' => '=',
				),
			),
		);
		$completed_todos = get_posts( $completed_args );
		$completed_count = 0;
		if ( ! empty( $completed_todos ) ) {
			$completed_count = count( $completed_todos );
		}
		return $completed_count;
		wp_reset_postdata();
	}
}

if ( ! function_exists( 'ld_dashboard_get_course_excerpt' ) ) {
	function ld_dashboard_get_course_excerpt( $content, $num_words = 50 ) {

		$original_text = $content;
		$num_words     = (int) $num_words;

		/*
		 * translators: If your word count is based on single characters (e.g. East Asian characters),
		 * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
		 * Do not translate into your own language.
		 */
		if ( strpos( _x( 'words', 'Word count type. Do not translate!', 'ld-dashboard' ), 'characters' ) === 0 && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
			$content = trim( preg_replace( "/[\n\r\t ]+/", ' ', $content ), ' ' );
			preg_match_all( '/./u', $content, $words_array );
			$words_array = array_slice( $words_array[0], 0, $num_words + 1 );
			$sep         = '';
		} else {
			$words_array = preg_split( "/[\n\r\t ]+/", $content, $num_words + 1, PREG_SPLIT_NO_EMPTY );
			$sep         = ' ';
		}

		if ( count( $words_array ) > $num_words ) {
			array_pop( $words_array );
			$content = implode( $sep, $words_array );
		} else {
			$content = implode( $sep, $words_array );
		}

		/**
		 * Filters the text content after words have been trimmed.
		 *
		 * @since 5.3.0
		 *
		 * @param string $text          The trimmed text.
		 * @param int    $num_words     The number of words to trim the text to. Default 55.
		 * @param string $original_text The text before it was trimmed.
		 */
		return apply_filters( 'wp_trim_words', $content, $num_words, $original_text );
	}
}

if ( ! function_exists( 'ld_dashboard_most_popular_course_enable_for' ) ) {
	function ld_dashboard_most_popular_course_enable_for( $user_role ) {
		$options        = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
		$genral_options = $options['general_settings'];
		$enable         = false;

		if ( 'student' === $user_role && $user_role == $genral_options['enable-popular-courses-student'] ) {
			$enable = true;
		} elseif ( 'group-leader' === $user_role && $user_role == $genral_options['enable-popular-courses-group-leader'] ) {
			$enable = true;
		}
		return apply_filters( 'ld_dashboard_most_popular_course_enable_for', $enable, $user_role );

	}
}


function ld_dashboard_instrictor_paid_earnings( $instructor_id, $withdrawal_status = 1 ) {
	global $wpdb;
	if ( $withdrawal_status == 1 ) {
		$query = "SELECT sum(mt1.meta_value) as total_paid_earnings FROM {$wpdb->prefix}posts p INNER JOIN {$wpdb->prefix}postmeta mt ON ( p.ID = mt.post_id ) INNER JOIN {$wpdb->prefix}postmeta AS mt1 ON ( p.ID = mt1.post_id ) WHERE 1=1 AND p.post_author={$instructor_id} AND ( ( mt.meta_key = 'withdrawal_status' AND mt.meta_value = '1' ) AND mt1.meta_key = 'withdrawal_amount' ) AND ((p.post_type = 'withdrawals' AND (p.post_status = 'publish' ))) GROUP BY p.ID ORDER BY p.post_date DESC ";
	} else {
		$query = "SELECT sum(mt1.meta_value) as total_paid_earnings FROM {$wpdb->prefix}posts p INNER JOIN {$wpdb->prefix}postmeta mt ON ( p.ID = mt.post_id ) INNER JOIN {$wpdb->prefix}postmeta AS mt1 ON ( p.ID = mt1.post_id ) WHERE 1=1 AND p.post_author={$instructor_id} AND ( ( mt.meta_key = 'withdrawal_status' AND mt.meta_value != '2' ) AND mt1.meta_key = 'withdrawal_amount' ) AND ((p.post_type = 'withdrawals' AND (p.post_status = 'publish' ))) GROUP BY p.ID ORDER BY p.post_date DESC ";
	}

	$instructor_earning_data = $wpdb->get_results( $query, ARRAY_A );

	return ( ! empty( $instructor_earning_data ) ) ? $instructor_earning_data[0]['total_paid_earnings'] : 0;
}

function ld_dashboard_get_dashboard_user_roles( $remove_user_role = '', $add_role = '' ) {
	$roles               = array();
	$dasboard_user_roles = array( 'administrator', 'group_leader', 'ld_instructor' );

	if ( ! empty( $add_role ) ) {
		array_push( $dasboard_user_roles, $add_role );
	}

	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	foreach ( get_editable_roles() as $role => $details ) {
		if ( false !== array_search( $role, $dasboard_user_roles ) ) {
			$roles[ $role ] = translate_user_role( $details['name'] );
		} else {
			$roles['other'] = __( 'Other', 'ld-dashboard' ); // We consider other as a user role with no capability of admin, group-leader, instructor.
		}
	}

	if ( ! empty( $remove_user_role ) && is_array( $remove_user_role ) ) {
		foreach ( $remove_user_role as $remove_each_user_role ) {
			unset( $roles[ $remove_each_user_role ] );
		}
	} elseif ( ! empty( $remove_user_role ) && is_string( $remove_user_role ) ) {
		unset( $roles[ $remove_user_role ] );
	}

	return apply_filters( 'ld_dashboard_get_dashboard_user_roles', $roles );
}

function ld_dashboard_is_user_role_allowed( $user_roles ) {
	$allowed = false;
	$user    = wp_get_current_user();
	$roles   = (array) $user->roles;

	if ( ! empty( array_intersect( $user_roles, $roles ) ) ) {
		$allowed = true;
	}

	return apply_filters( 'ld_dashboard_is_user_role_allowed', $allowed, $user_roles );
}

if ( ! function_exists( 'ldd_get_user_courses_list' ) ) {

	function ldd_get_user_courses_list( $user_id = 0, $id = false, $open = false ) {

		if ( 0 == $user_id ) {
			$user_id = get_current_user_id();
		}
		$course_args = array(
			'post_type'      => 'sfwd-courses',
			'author'         => ! learndash_is_admin_user( $user_id ) ? $user_id : null,
			'post_status'    => array( 'publish' ),
			'posts_per_page' => -1,
		);

		$shared_course_args = array(
			'post_type'      => 'sfwd-courses',
			'post_status'    => array( 'publish' ),
			'meta_query'     => array(
				array(
					'key'     => '_ld_instructor_ids',
					'value'   => '"' . $user_id . '"',
					'compare' => 'LIKE',
				),
			),
			'posts_per_page' => -1,
		);
		$courses            = get_posts( $course_args );
		$shared_courses     = get_posts( $shared_course_args );

		if ( count( $shared_courses ) > 0 ) {
			$courses = array_merge( $courses, $shared_courses );
		}
		$unique_ids     = array();
		$unique_array   = array();
		$not_course_tab = true;
		if ( isset( $_GET['tab'] ) && 'my-courses' === $_GET['tab'] ) {
			$not_course_tab = false;
		}

		if ( $not_course_tab && learndash_is_group_leader_user( $user_id ) ) {
			$group_courses     = learndash_get_group_leader_groups_courses();
			$group_courses     = ( is_array( $group_courses ) && ! empty( $group_courses ) ) ? $group_courses : array( 0 );
			$group_course_args = array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'post__in'       => $group_courses,
				'posts_per_page' => -1,
			);
			$group_courses     = get_posts( $group_course_args );
			if ( count( $group_courses ) > 0 ) {
				$courses = array_merge( $courses, $group_courses );
			}
		}

		if ( count( $courses ) > 0 ) {
			foreach ( $courses as $crs ) {
				if ( true === $open && 'open' === learndash_get_course_meta_setting( $crs->ID, 'course_price_type' ) ) {
					continue;
				}

				if ( in_array( $crs->ID, $unique_ids ) ) {
					continue;
				}
				$unique_array[] = $crs;
				$unique_ids[]   = $crs->ID;
			}
			$courses = $unique_array;

			if ( $id ) {
				return $unique_ids;
			} else {
				return $courses;
			}
		} else {
			return array();
		}
	}
}


if ( ! function_exists( 'ld_dashboard_is_dashboard_page' ) ) {
	function ld_dashboard_is_dashboard_page( $page ) {
		if ( empty( $page ) ) {
			return false;
		}

		if ( isset( $_GET ) && isset( $_GET['tab'] ) && $page === $_GET['tab'] ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ld_dasboard_get_user_course_ids' ) ) {
	/**
	 * Get the user course. The courses which the given user have author or co-author
	 *
	 * @param integer $user_id
	 * @param boolean $id
	 * @return array $course id
	 */
	function ld_dasboard_get_user_course_ids( $user_id = 0, $id = false ) {
		if ( 0 == $user_id ) {
			$user_id = get_current_user_id();
		}
		$course_args        = array(
			'post_type'      => 'sfwd-courses',
			'author'         => $user_id,
			'post_status'    => array( 'publish' ),
			'posts_per_page' => -1,
		);
		$shared_course_args = array(
			'post_type'      => 'sfwd-courses',
			'post_status'    => array( 'publish' ),
			'meta_query'     => array(
				array(
					'key'     => '_ld_instructor_ids',
					'value'   => '"' . $user_id . '"',
					'compare' => 'LIKE',
				),
			),
			'posts_per_page' => -1,
		);
		$courses            = get_posts( $course_args );
		$shared_courses     = get_posts( $shared_course_args );
		if ( count( $shared_courses ) > 0 ) {
			$courses = array_merge( $courses, $shared_courses );
		}
		$unique_ids   = array();
		$unique_array = array();

		if ( learndash_is_group_leader_user( $user_id ) ) {
			$group_courses     = learndash_get_group_leader_groups_courses();
			$group_courses     = ( is_array( $group_courses ) && ! empty( $group_courses ) ) ? $group_courses : array( 0 );
			$group_course_args = array(
				'post_type'      => 'sfwd-courses',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'post__in'       => $group_courses,
				'posts_per_page' => -1,
			);
			$group_courses     = get_posts( $group_course_args );
			if ( count( $group_courses ) > 0 ) {
				$courses = array_merge( $courses, $group_courses );
			}
		}

		if ( count( $courses ) > 0 ) {
			foreach ( $courses as $course ) {
				if ( in_array( $course->ID, $unique_ids ) ) {
					continue;
				}
				$unique_array[] = $course;
				$unique_ids[]   = $course->ID;
			}
			$courses = $unique_array;

			if ( $id ) {
				return $unique_ids;
			} else {
				return $courses;
			}
		} else {
			return array();
		}

	}
}

if ( ! function_exists( 'ld_dashboard_instructor_user' ) ) {
	/**
	 * Checks if a user has the instructor capabilities.
	 *
	 * @since 5.9.9
	 *
	 * @param int|WP_User $user Optional. The `WP_User` object or user ID to check. Default 0.
	 *
	 * @return boolean Returns true if the user is group leader otherwise false.
	 */
	function ld_dashboard_instructor_user( $user = 0 ) {
		$user_id = 0;

		if ( ( is_numeric( $user ) ) && ( ! empty( $user ) ) ) {
			$user_id = $user;
		} elseif ( $user instanceof WP_User ) {
			$user_id = $user->ID;
		} else {
			$user_id = get_current_user_id();
		}

		if ( ( ! empty( $user_id ) ) && ( ! learndash_is_admin_user( $user_id ) ) ) {
			return user_can( $user_id, 'ld_instructor' );
		}
		return false;
	}
}

if ( ! function_exists( 'ld_dashboard_get_allowed_dashboard_tabs' ) ) {

	/**
	 * Get allowed dashboard tabs by user role
	 *
	 * @param array $user_roles
	 * @return array
	 */
	function ld_dashboard_get_allowed_dashboard_tabs( $user_roles ) {
		$ldd_functions         = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings = $ldd_functions->ld_dashboard_settings_data();
		$menu_options          = $ld_dashboard_settings['menu_options'];
		$menu_items            = ld_dashboard_get_sidebar_tabs();
		$menu_items            = array_shift( $menu_items );
		$allowed_roles         = array();

		if ( ! empty( $menu_options ) ) {
			foreach ( $menu_options as $key => $menu_option ) {
				$menu_option = array_filter( $menu_option );
				if ( in_array( $key, $user_roles ) ) {
					$allowed_roles = $menu_option;
				}
			}
		}
		return apply_filters( 'ld_dashboard_allowed_dashboard_tabs', $allowed_roles, $menu_options, $user_roles );
	}
}

if ( ! function_exists( 'ld_dashboard_render_dashboard_menus' ) ) {
	function ld_dashboard_render_dashboard_menus() {
		$menu_items          = ld_dashboard_get_sidebar_tabs();
		$current_user        = wp_get_current_user();
		$allowed_tabs        = ld_dashboard_get_allowed_dashboard_tabs( $current_user->roles );
		$announcements_count = ld_dashboard_get_announcements_count( $current_user->ID );
		if ( empty( $allowed_tabs ) && 0 === count( $allowed_tabs ) ) {
			// Other than admin, group leader and instructor will condier as a student.
			$allowed_tabs = ld_dashboard_get_allowed_dashboard_tabs( array( 'other' ) );
		}

		$section = array();
		foreach ( $menu_items as $slug => $menu_item ) {
			$section_title = '';

			switch ( $slug ) {
				case 'all':
					$section_title = __( 'My Dashboard', 'ld-dashboard' );
					break;
				case 'course-management':
					$section_title = __( 'Course Management', 'ld-dashboard' );
					break;
				case 'reports':
					$section_title = __( 'Reports', 'ld-dashboard' );
					break;
				case 'monetization':
					$section_title = __( 'Monetization', 'ld-dashboard' );
					break;
				case 'communication':
					$section_title = __( 'Communication', 'ld-dashboard' );
					break;
				case 'common':
					$section_title = __( 'Account', 'ld-dashboard' );
					break;
			}

			?>
				<ul class="ld-dashboard-left-panel">
			<?php if ( ld_dashboard_have_section( $allowed_tabs, $menu_item ) ) : ?>
					<li class="ld-dashboard-menu-divider-label ld-dashboard-label-color"><?php echo esc_html( $section_title ); ?></li>
					<li class="ld-dashboard-menu-divider"></li>
					<?php endif; ?>
				<?php foreach ( $menu_item as $menu_item_slug => $item ) : ?>
						<?php
						if ( isset( $allowed_tabs[ $menu_item_slug ] ) && 1 == $allowed_tabs[ $menu_item_slug ] ) {
							$section[]  = $menu_item_slug;
							$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
							if ( apply_filters( 'ld_dashboard_sidebar_tab_set', false, $slug, $menu_item_slug ) ) {
								continue;
							}
							?>
						<li class="ld-dashboard-menu-tab <?php echo ( $active_tab == $menu_item_slug ) ? 'ld-dashboard-active' : ''; ?> <?php echo ( ! isset( $_GET['tab'] ) && 'my-dashboard' == $menu_item_slug ) ? 'ld-dashboard-active' : ''; ?> ">
							<a class="<?php echo esc_attr( 'ld-focus-menu-link ld-focus-menu-' . $slug ); ?>" href="<?php echo ( isset( $item['url'] ) ) ? esc_url( $item['url'] ) : ''; ?>">
								<div class="ld-dashboard-menu-icon"><?php echo ( isset( $item['icon'] ) ) ? wp_kses_post( $item['icon'] ) : ''; ?></div>
							<?php echo ( isset( $item['label'] ) ) ? esc_html( $item['label'] ) : ''; ?>
							<?php echo ( 'announcements' === $menu_item_slug && $announcements_count > 0 ) ? '<span id="ld-dashboard-new-announcements-span" class="ld-dashboard-new-announcements-count">' . esc_html( $announcements_count ) . '</span>' : ''; ?>
							</a>
						</li>
						<?php } ?>
					<?php endforeach; ?>
					</ul>
				<?php
		}
	}
}


if ( ! function_exists( 'ld_dashboard_have_section' ) ) {
	function ld_dashboard_have_section( $allowed_tabs, $menu_items ) {
		if ( ! empty( $menu_items ) && count( $allowed_tabs ) > 0 ) {
			foreach ( $menu_items as $menu_item_key => $menu_item ) {
				if ( isset( $allowed_tabs[ $menu_item_key ] ) && 1 == $allowed_tabs[ $menu_item_key ] ) {
					return true;
				}
			}
			return false;
		}
	}
}

if ( ! function_exists( 'ld_dashboard_date_converter' ) ) {
	/**
	 * Calculate Time based on Timezone
	 *
	 * @param        $start_time
	 * @param        $tz
	 * @param string     $format
	 * @param bool       $defaults
	 *
	 * @return DateTime|string
	 * @since   6.1.0
	 */
	function ld_dashboard_date_converter( $start_time, $tz, $format = 'F j, Y, g:i a ( T )', $defaults = true ) {
		try {
			$timezone = ! empty( $tz ) ? $tz : 'America/Los_Angeles';
			$tz       = new DateTimeZone( $timezone );
			$date     = new DateTime( $start_time );
			$date->setTimezone( $tz );

			if ( ! $format ) {
				return $date;
			}

			if ( ! $defaults ) {
				return $date->format( $format );
			}

			$locale = get_locale();
			if ( $defaults ) {
				setlocale( LC_TIME, $locale );
				$start_timestamp = $date->getTimestamp() + $date->getOffset();
				return $date->format( $format );
			} else {
				return $date->format( $format );
			}
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}
}


if ( ! function_exists( 'ld_dashboard_encrypt_decrypt' ) ) {
	/**
	 * Use to Encrypts URL
	 *
	 * @param $action
	 * @param $string
	 *
	 * @return bool|string
	 */
	function ld_dashboard_encrypt_decrypt( $action, $string ) {
		$output = false;

		$encrypt_method = 'AES-256-CBC';
		$secret_key     = 'AMIT_X3!3#23121';
		$secret_iv      = '1231232133213221';

		// hash
		$key = hash( 'sha256', $secret_key );

		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
			$output = base64_encode( $output );
		} elseif ( $action == 'decrypt' ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		return $output;
	}
}


if ( ! function_exists( 'ld_dashboard_video_conference_zoom_check_login' ) ) {
	/**
	 * Function to check if a user is logged in or not
	 *
	 * @since  6.1.0
	 */
	function ld_dashboard_video_conference_zoom_check_login() {
		global $ldd_meeting;
		if ( ! empty( $ldd_meeting ) && ! empty( $ldd_meeting['site_option_logged_in'] ) ) {
			if ( is_user_logged_in() ) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
}



if ( ! function_exists( 'ld_dashboard_is_sdk_enabled' ) ) {
	/**
	 * Check if embed meeting enable and zoom sdk credentials are save
	 *
	 * @since 6.1.0
	 * @return boolean
	 */
	function ld_dashboard_is_sdk_enabled(): bool {
		$settings         = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
		$meeting_settigns = $settings['zoom_meeting_settings'];
		return ! empty( $meeting_settigns['embed-meeting'] ) && ! empty( $meeting_settigns['sdk-client-id'] ) && ! empty( $meeting_settigns['sdk-client-secret'] );
	}
}

if ( ! function_exists( 'ld_dashboard_get_meeting_embed_url' ) ) {
	/**
	 * Get the meeting url that is use for embed
	 *
	 * @since 6.1.0
	 * @return boolean
	 */
	function ld_dashboard_get_meeting_embed_url() {
		global $ldd_meeting;

		if ( ! empty( $ldd_meeting ) ) {
			$meeting_id = $ldd_meeting->id;
		} else {
			$meeting_id = get_post_meta( get_the_ID(), 'zoom_meeting_id', true );
		}

		$post_type_link    = get_post_type_archive_link( 'zoom_meet' );
		$meeting_join_link = array(
			'join' => ld_dashboard_encrypt_decrypt( 'encrypt', $meeting_id ),
			'type' => 'meeting',
		);

		if ( ! empty( $ldd_meeting->shortcode_attributes['passcode'] ) ) {
			$meeting_join_link['pak'] = ld_dashboard_encrypt_decrypt( 'encrypt', $ldd_meeting->shortcode_attributes['passcode'] );
		}
		$meeting_link = add_query_arg( $meeting_join_link, $post_type_link );

		return apply_filters( 'ld_dashboard_meeting_embed_url', $meeting_link );

	}
}


if ( ! function_exists( 'ld_dashboard_get_announcements_count' ) ) {
	/**
	 * Get Announcements Count
	 *
	 * @param int $user_id
	 * @return int Count of Announcements
	 */
	function ld_dashboard_get_announcements_count( $user_id ) {
		$announcements_count = 0;
		$enrolled_courses    = learndash_user_get_enrolled_courses( $user_id );
		if ( is_array( $enrolled_courses ) && ! empty( $enrolled_courses ) ) {
			$args                 = array(
				'post_type'      => 'announcements',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'course_id',
						'value'   => $enrolled_courses,
						'compare' => 'IN',
					),
				),
			);
			$announcements        = new WP_Query( $args );
			$viewed_announcements = get_user_meta( $user_id, 'ld_viewed_announcements', true );
			$viewed_announcements = ( is_array( $viewed_announcements ) && ! empty( $viewed_announcements ) ) ? $viewed_announcements : array();
			if ( is_array( $announcements->posts ) && ! empty( $announcements->posts ) ) {
				if ( count( $announcements->posts ) > count( $viewed_announcements ) ) {
					$announcements_count = count( $announcements->posts ) - count( $viewed_announcements );
				}
			}
		}
		return apply_filters( 'ld_dashboard_announcements_count', $announcements_count, $enrolled_courses, $user_id );
	}
}

if ( ! function_exists( 'ld_dashboard_studnet_user' ) ) {
	/**
	 * Checks if a user has the students capabilities.
	 *
	 * @since 6.2.0
	 *
	 * @param int|WP_User $user Optional. The `WP_User` object or user ID to check. Default 0.
	 *
	 * @return boolean Returns true if the user is group leader otherwise false.
	 */
	function ld_dashboard_studnet_user( $user = 0 ) {
		$user_id = 0;
		if ( ( is_numeric( $user ) ) && ( ! empty( $user ) ) ) {
			$user_id = $user;
		} elseif ( $user instanceof WP_User ) {
			$user_id = $user->ID;
		} else {
			$user_id = get_current_user_id();
		}

		if ( ( ! empty( $user_id ) ) && ( ! learndash_is_admin_user( $user_id ) && ! ld_dashboard_instructor_user( $user_id ) && ! learndash_is_group_leader_user( $user_id ) ) ) {
			return true;
		}
		return false;
	}
}



if ( ! function_exists( 'ld_dashboard_get_current_theme_directory' ) ) {
	/**
	 * Get active theme
	 *
	 * @return void
	 */
	function ld_dashboard_get_current_theme_directory( $theme = '' ) {
		$current_theme_dir = '';
		$current_theme     = ! empty( $theme ) ? wp_get_theme( $theme ) : wp_get_theme();
		if ( $current_theme->exists() && $current_theme->parent() ) {
			$parent_theme = $current_theme->parent();
			if ( $parent_theme->exists() ) {
				$current_theme_dir = $parent_theme->get_stylesheet();
			}
		} elseif ( $current_theme->exists() ) {
			$current_theme_dir = $current_theme->get_stylesheet();
		}

		return apply_filters( 'ld_dashboard_current_theme_directory', $current_theme_dir );
	}
}

