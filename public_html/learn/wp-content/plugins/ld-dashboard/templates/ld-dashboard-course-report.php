<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $courses,$course_ids, $wpdb;
$user_id = get_current_user_id();
$user    = wp_get_current_user();

if ( learndash_is_admin_user() ) {
	$args    = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$courses = get_posts( $args );
} elseif ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $user->roles ) ) {
	$group_course = learndash_get_group_leader_groups_courses();
	$group_course = ( is_array( $group_course ) && ! empty( $group_course ) ) ? $group_course : array( 0 );
	$args         = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__in'       => $group_course,
	);
	$courses      = get_posts( $args );
} elseif ( in_array( 'ld_instructor', $user->roles ) ) {
	$courses = Ld_Dashboard_Public::get_instructor_courses_list();
	if ( ! empty( $courses ) ) {
		$course_ids      = array( 0 );
		foreach ( $courses as $course ) {
			if ( 'publish' === $course->post_status ) {
				$course_ids[] = $course->ID;
			}
		}

		$args    = array(
			'post_type'      => 'sfwd-courses',
			'post__in'       => $course_ids,
			'posts_per_page' => -1,
		);
		$courses = get_posts( $args );
	}
} else {
	$get_courses_sql = "select ID from {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta pm6 ON ( {$wpdb->prefix}posts.ID = pm6.post_id ) where ( post_author={$user_id} OR ( pm6.meta_key = '_ld_instructor_ids' AND pm6.meta_value REGEXP '.*;s:[0-9]+:\"{$user_id}\"*' ) ) AND post_type='sfwd-courses' AND {$wpdb->prefix}posts.post_status = 'publish' Group By {$wpdb->prefix}posts.ID";
	$cousres         = $wpdb->get_results( $get_courses_sql );
	$course_ids      = array( 0 );
	if ( ! empty( $cousres ) ) {
		$course_ids = array();
		foreach ( $cousres as $course ) {
			$course_ids[] = $course->ID;
		}
	}

	$args    = array(
		'post_type'      => 'sfwd-courses',
		'post__in'       => $course_ids,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$courses = get_posts( $args );
}
$loader = includes_url( 'images/spinner-2x.gif' );
$loader = apply_filters( 'ld_dashboard_loader_img_url', $loader );
?>
<div class="ld-dashboard-course-report">
	<div class="ld-dashboard-seperator"><span><?php printf( '%1s %2s', esc_html( LearnDash_Custom_Label::get_label( 'course' ) ), esc_html__( 'Details', 'ld-dashboard' ) ); ?></span></div>

	<?php
	do_action( 'ld_dashboard_course_report_before', $user_id );

	if ( ! empty( $courses ) ) {
		?>

		<div class="ld-dashboard-courses">
			<select id="ld-dashboard-courses-id" class="ld-dashboard-select">
				<?php
				foreach ( $courses as $index => $course ) {
						$course_ids[] = $course->ID;
					?>
					<option value="<?php echo esc_attr( $course->ID ); ?>"><?php echo esc_html( $course->post_title ); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="ld-dashboard-loader">
			<img src="<?php echo esc_url( $loader ); ?>">
			<p><?php echo apply_filters( 'ld_dashboard_waiting_text', __( 'Please wait, while details are loading...', 'ld-dashboard' ) ); ?></p>
		</div>
		<div class="ld-dashboard-course-details"></div>

	<?php } else { ?>
		<?php
			$course_nonce       = wp_create_nonce( 'course-nonce' );
			$my_dashboard_page  = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
			$create_course_link = '<a href="' . esc_url( $my_dashboard_page ) . '/?action=add-course&tab=my-courses&_lddnonce=' . esc_attr( $course_nonce ) . '">' . sprintf( esc_html__( 'create your first %s', 'ld-dashboard' ), strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) . '</a>'
		?>
		<p class="ld-dashboard-warning"><?php echo apply_filters( 'ld_dashboard_no_course_created_text', sprintf( esc_html__( 'You have not created any %1$1s yet, %2$2s.', 'ld-dashboard' ), strtolower( LearnDash_Custom_Label::get_label( 'course' ) ), $create_course_link ) ); ?></p>

		<?php
	}

	do_action( 'ld_dashboard_course_report__after', $user_id );
	?>

</div>
