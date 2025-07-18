<div class="my-quiz-attempts-wrapper-view ld-dashboard-course-content instructor-courses-list">
	<div class="ld-dashboard-section-head-title">
		<h3 class="ld-dashboard-nav-title"><?php printf( esc_html__( '%s Attempts', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'quiz' ) ) ); ?></h3>
		<?php do_action( 'ld_dashboard_before_quiz_attempt_filter' ); ?>
		<div class="ld-dashboard-content-inner">
			<?php
			$students_arr = array();

			if ( learndash_is_admin_user() || ld_group_leader_has_admin_cap() ) {
				$courses_query_args = array(
					'post_type'   => 'sfwd-courses',
					'post_status' => 'publish',
					'numberposts' => -1,
				);
				$all_courses        = get_posts( $courses_query_args );
			} elseif ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) ) {
				$all_courses = learndash_get_group_leader_groups_courses();
			} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
				$all_courses = Ld_Dashboard_Public::get_instructor_courses_list();
			}

			foreach ( $all_courses as $course ) {
				if ( ! is_object( $course ) ) {
					$course = get_post( $course );
				}
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
							if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) ) {
								$group_students = learndash_get_group_leader_groups_users();
								if ( ! in_array( $std, $group_students ) ) {
									continue;
								}
							}
							if ( ! in_array( $std, $students_arr ) && $std != get_current_user_id() ) {
								$students_arr[] = $std;
							}
						}
					}
				}
			}
			if ( count( $students_arr ) > 0 ) :
				?>
				<div class="ld-dashboard-instructor-students-container">
					<div class="ld-dashboard-students-dropdown">
						<select id="ld_quiz_attempt_student" name="student">
							<?php
							foreach ( $students_arr as $student_id ) {
								$student_data = get_userdata( $student_id );
								?>
								<option value="<?php echo esc_attr( $student_id ); ?>"><?php echo esc_attr( $student_data->display_name ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php do_action( 'ld_dashboard_before_quiz_attempt_content' ); ?>
	<div class="ld-dashboard-student-quiz-attempt-container">
		<?php
		if ( count( $students_arr ) == 0 ) {
			?>
			<div class="ld-dashboard-instructor-students-container">
				<p class="ld-dashboard-warning"><?php esc_html_e( 'No students found.', 'ld-dashboard' ); ?></p>
			</div>
			<?php
		}
		?>
	</div>
	<?php do_action( 'ld_dashboard_after_quiz_attempt_content' ); ?>
</div>
