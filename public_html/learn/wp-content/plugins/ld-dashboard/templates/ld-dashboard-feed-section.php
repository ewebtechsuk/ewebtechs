<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $courses,$course_ids, $wpdb;
$show_filter = false;
$user_id     = get_current_user_id();
$user        = wp_get_current_user();
$args        = array();
/* Get the Course */
if ( learndash_is_admin_user() ) {
	$args        = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$courses     = get_posts( $args );
	$show_filter = true;

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
	$show_filter  = true;
} elseif ( in_array( 'ld_instructor', $user->roles ) ) {
	$courses     = Ld_Dashboard_Public::get_instructor_courses_list();
	$show_filter = true;
}

/* Get the Student */
if ( empty( $student ) ) {
	if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $user->roles ) ) {
		$group_student = learndash_get_group_leader_groups_users();
		$args          = array(
			'meta_key' => 'course_%_access_from',
			'orderby'  => 'user_nicename',
			'order'    => 'ASC',
			'fields'   => array( 'ID', 'display_name' ),
			'include'  => ( ! empty( $group_student ) ) ? $group_student : array( 0 ),
		);

	} elseif ( learndash_is_admin_user() ) {
		$args = array(
			'meta_key' => 'course_%_access_from',
			'orderby'  => 'user_nicename',
			'order'    => 'ASC',
			'fields'   => array( 'ID', 'display_name' ),
		);
	} elseif ( in_array( 'ld_instructor', $user->roles ) ) {
		$instructor_students = $this->ld_dashboard_get_instructor_students_by_id( $user_id );
		$course_student_ids  = array( 0 );
		if ( ! empty( $instructor_students ) ) {
			$course_student_ids = array();
			foreach ( $instructor_students as $key => $course_student ) {
				$course_student_ids[] = $course_student->ID;
			}
		}

		if ( learndash_is_group_leader_user() ) {
			$group_student      = learndash_get_group_leader_groups_users();
			$course_student_ids = array_merge( $course_student_ids, $group_student );
			$course_student_ids = array_unique( $course_student_ids );
		}

		$args = array(
			'orderby' => 'user_nicename',
			'order'   => 'ASC',
			'fields'  => array( 'ID', 'display_name' ),
			'include' => $course_student_ids,
		);
	}
	add_action( 'pre_user_query', 'ld_dashboard_user_queries' );
	$students = get_users( $args );
	remove_action( 'pre_user_query', 'ld_dashboard_user_queries' );
}

$loader = includes_url( 'images/spinner-2x.gif' );
$loader = apply_filters( 'ld_dashboard_loader_img_url', $loader );

$courseid  = '';
$studentid = '';
if ( isset( $_GET['ld-course'] ) ) {
	$courseid = $_GET['ld-course'];
}
if ( isset( $_GET['ld-student'] ) ) {
	$studentid = $_GET['ld-student'];
}



$course_label = LearnDash_Custom_Label::get_label( 'course' );
?>
	<div class="ld-dashboard-sidebar-right live-course-activity">
		<?php do_action( 'ld_dashboard_before_course_activity_section' ); ?>
		
		<?php if ( $show_filter && ! empty( $courses ) ) : ?>
			<form action="" method="get" class="ld-course-filter-form">
				<input type="hidden" name="tab" value="activity" />
				<?php if ( ! empty( $courses ) ) : ?>
					<div class="ld-dashboard-courses">
						<select id="ld-dashboard-courses-id" name="ld-course" class="ld-dashboard-select">
							<option value=""><?php esc_html_e( 'Select Course', 'ld-dashboard' ); ?></option>
							<?php foreach ( $courses as $index => $course ) { ?>
								<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $courseid, $course->ID ); ?>><?php echo esc_html( $course->post_title ); ?></option>
							<?php } ?>
						</select>
					</div>
				<?php endif; ?>
				
				<?php if ( ! empty( $students ) ) : ?>
					<div class="ld-dashboard-students">
						<select id="ld-dashboard-students-id" name="ld-student" class="ld-dashboard-select">
							<option value=""><?php esc_html_e( 'Select Student', 'ld-dashboard' ); ?></option>
							<?php foreach ( $students as $student ) { ?>
								<option value="<?php echo esc_attr( $student->ID ); ?>" <?php selected( $studentid, $student->ID ); ?>><?php echo esc_html( $student->display_name ); ?></option>
							<?php } ?>
						</select>
					</div>
				<?php endif; ?>
				<input type="hidden" name="is_search_acctivity" value="1" />
				<input class="btn-search-activity" type="submit" value="<?php esc_html_e( 'Search Activity', 'ld-dashboard' ); ?>" />
			</form>
		<?php endif; ?>
		
		<div class="ld-dashboard-feed-wrapper">
			<h3 class="ld-dashboard-feed-title"><?php echo sprintf( esc_html__( 'Live  %s Activity', 'ld-dashboard' ), $course_label ); ?></h3>
			<div id="ld-dashboard-feed" class="ld-dashboard-feed">
				<?php $this->ld_dashboard_activity_rows(); ?>
			</div>
		</div>

		<?php do_action( 'ld_dashboard_after_course_activity_section' ); ?>

	</div>
</div>
