<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $students,$course_ids, $wpdb;
$courseIDs    = $course_ids;
if ( empty($courseIDs )) {
	$courseIDs = [];
}
$curr_user_id = get_current_user_id();
$curr_user    = wp_get_current_user();
$group_ids    = array();

if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $curr_user->roles ) ) {
	$group_student = learndash_get_group_leader_groups_users();

	$group_student = ( is_array( $group_student ) && ! empty( $group_student ) ) ? $group_student : array( 0 );
	$args          = array(
		'orderby' => 'user_nicename',
		'order'   => 'ASC',
		'fields'  => array( 'ID', 'display_name' ),
		'include' => $group_student,
	);
	$group_ids     = learndash_get_administrators_group_ids( $curr_user_id );

} elseif ( learndash_is_admin_user() ) {
	$user_course_ids = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id, REPLACE( REPLACE(meta_key, 'course_', ''), '_access_from', '' ) as courseid FROM " . $wpdb->usermeta . ' WHERE meta_key LIKE %s ',
			'course_%_access_from'
		),
		ARRAY_A
	);


	$admin_students = array();
	if ( ! empty( $user_course_ids ) ) {
		foreach ( $user_course_ids as $user_course_id ) {
			if ( ! is_null( get_post( $user_course_id['courseid'] ) ) ) {
				$admin_students[] = $user_course_id['user_id'];
			}
		}
	}

	$args = array(
		'orderby' => 'user_nicename',
		'order'   => 'ASC',
		'fields'  => array( 'ID', 'display_name' ),
		'include' => ! empty( $admin_students ) ? $admin_students : array( 0 ),
	);

} elseif ( in_array( 'ld_instructor', $curr_user->roles ) ) {
	$stdnts  = array( 0 );
	$courses = Ld_Dashboard_Public::get_instructor_courses_list();
	if ( is_array( $courses ) && ! empty( $courses ) ) {
		foreach ( $courses as $course ) {
			$course_pricing = learndash_get_course_price( $course->ID );
			if ( 'open' !== $course_pricing['type'] ) {
				$course_user_ids  = learndash_get_course_users_access_from_meta( $course->ID );
				$course_group_ids = learndash_get_course_groups( $course->ID );
				if ( is_array( $course_group_ids ) && ! empty( $course_group_ids ) ) {
					foreach ( $course_group_ids as $grp_id ) {
						$group_users = learndash_get_groups_user_ids( $grp_id );
						if ( ! empty( $group_users ) ) {
							$course_user_ids = array_unique( array_merge( $course_user_ids, $group_users ) );
						}
					}
				}
			} else {
				$course_user_ids = array();
				$users           = get_users();
				if ( ! empty( $users ) ) {
					foreach ( $users as $student ) {
						$course_user_ids[] = $student->ID;
					}
				}
			}
			if ( $course_user_ids ) {
				$students = $course_user_ids;
				if ( is_array( $students ) && ! empty( $students ) ) {
					foreach ( $students as $std ) {
						if ( in_array( $std, $stdnts ) ) {
							continue;
						}
						$stdnts[] = $std;
					}
				}
			}
		}
	}
	if ( learndash_is_group_leader_user() ) {
		$group_users = learndash_get_group_leader_groups_users();
		if ( is_array( $group_users ) && ! empty( $group_users ) ) {
			$stdnts = array_merge( $stdnts, $group_users );
			$stdnts = array_unique( $stdnts );
		}
	}
	$args = array(
		'orderby' => 'user_nicename',
		'order'   => 'ASC',
		'fields'  => array( 'ID', 'display_name' ),
		'include' => $stdnts,
	);

	$group_ids = learndash_get_administrators_group_ids( $curr_user_id );
} else {
	$instructor_students = $this->ld_dashboard_get_instructor_students_by_id( $curr_user_id );
	$course_student_ids  = array( 0 );
	if ( ! empty( $instructor_students ) ) {
		$course_student_ids = array();
		foreach ( $instructor_students as $key => $course_student ) {
			$course_student_ids[] = $course_student->ID;
		}
	}
	$args = array(
		'meta_key' => 'course_%_access_from',
		'orderby'  => 'user_nicename',
		'order'    => 'ASC',
		'fields'   => array( 'ID', 'display_name' ),
		'include'  => $course_student_ids,
	);

	$sql_str       = $wpdb->prepare(
		'SELECT post_id,meta_key FROM ' . $wpdb->postmeta . ' as postmeta INNER JOIN ' . $wpdb->posts . " as posts ON posts.ID=postmeta.post_id
				WHERE posts.post_type = %s AND posts.post_status = %s AND meta_key LIKE 'learndash_group_enrolled_%'",
		'sfwd-courses',
		'publish'
	);
	$group_courses = $wpdb->get_results( $sql_str );
	if ( ! empty( $group_courses ) ) {
		foreach ( $group_courses as $grp_course ) {
			$group_id = explode( 'learndash_group_enrolled_', $grp_course->meta_key );

			// $group_user_meta_key = 'learndash_group_users_'. $group_id[1];
			// $group_users = get_post_meta( $group_id[1], $group_user_meta_key, true);
			// $group_users = learndash_get_groups_user_ids( $group_id[1] );
			if ( in_array( $grp_course->post_id, $courseIDs ) && ! in_array( $group_id[1], $group_ids ) ) {
				$group_ids[] = $group_id[1];
			}
		}
	}
}


$args['number'] = 200;
$students       = get_users( $args );

/* Check Student enrolled in any courses */
$isstudent = false;
if ( ! empty( $students ) ) {
	foreach ( $students as $student ) {
		$course_ids = learndash_user_get_enrolled_courses( $student->ID );
		if ( ! empty( $course_ids ) ) {
			$isstudent = true;
			break;
		}
	}
}


$loader = includes_url( 'images/spinner-2x.gif' );
?>
<div class="ld-dashboard-student-status">
	<div class="ld-dashboard-seperator"><span><?php esc_html_e( 'Student Details', 'ld-dashboard' ); ?></span></div>

	<?php do_action( 'ld_dashboard_student_status_before', $curr_user_id ); ?>
	<?php if ( ! empty( $students ) ) { ?>
		<div class="ld-dashboard-student-status-block">
			<div class="ld-dashboard-student-lists">
				<?php if ( ! empty( $group_ids ) ) : ?>
					<select name="ld-dashboard-groups" class="ld-dashboard-groups ld-dashboard-select" data-course-id="<?php echo esc_attr( join( ',', $courseIDs ) ); ?>">
						<option value="all"><?php esc_html_e( 'All', 'ld-dashboard' ); ?></option>
						<?php foreach ( $group_ids as $grp_id ) : ?>
							<option value="<?php echo esc_attr( $grp_id ); ?>" ><?php echo esc_html( get_the_title( $grp_id ) ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<select name="ld-dashboard-student" class="ld-dashboard-student ld-dashboard-select">
					<?php foreach ( $students as $student ) { ?>
						<option value="<?php echo esc_attr( $student->ID ); ?>" ><?php echo esc_html( $student->display_name ); ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="ld-dashboard-student-loader">
				<img src="<?php echo apply_filters( 'ld_dashboard_loader_img_url', $loader ); ?>">
				<p><?php echo apply_filters( 'ld_dashboard_waiting_text', __( 'Please wait, while details are loading...', 'ld-dashboard' ) ); ?></p>
			</div>
			<div class="ld-dashboard-student-details"></div>
		</div>
	<?php } else { ?>
		<p class="ld-dashboard-warning">
			<?php
			if ( in_array( 'ld_instructor', (array) $curr_user->roles ) ) {
				echo apply_filters( 'ld_dashboard_student_status_no_student_instructor_message', sprintf( esc_html__( 'Please make sure your %s is enrolled by students . ', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) ) );
			} else {
				echo apply_filters( 'ld_dashboard_student_status_no_student_admin_message', esc_html__( 'No registered student on the site. ', 'ld-dashboard' ) );
			}
			?>
			</p>
	<?php } ?>
	<?php do_action( 'ld_dashboard_student_status_after', $curr_user_id ); ?>
</div>
