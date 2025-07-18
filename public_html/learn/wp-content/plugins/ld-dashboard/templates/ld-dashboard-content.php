<div class="ld-dashboard-tiles-options ld-dashboard-landing">
	<?php
	do_action( 'ld_dashboard_before_statistics_container' );

	if ( isset( $ld_dashboard['statistics-tiles'] ) && $ld_dashboard['statistics-tiles'] == 1 ) :
		if ( ! ld_dashboard_is_user_role_allowed( $ld_dashboard['statistics-tiles-allwoed-roles'] ) ) {
			return;
		}
		/*
		 * Display total course.
		 */

		?>
			<div class="ld-dashboard-statistics-container">
			<?php
			$user_id    = get_current_user_id();
			$user_meta  = get_userdata( $user_id );
			$user_roles = $user_meta->roles;
			if ( $is_student != '1' && isset( $tiles_options['course-count'] ) && $tiles_options['course-count'] == 1 ) {
				$sfwd_courses_total = count_user_posts( $user_id, 'sfwd-courses' );

				?>
					<div class="col-1-2 ld-dashboard-statistics learndash-courses" 
				<?php
				if ( isset( $tiles_options['course-count-bgcolor'] ) && $tiles_options['course-count-bgcolor'] != '' ) {
					?>
						style="background-color: <?php echo esc_attr( $tiles_options['course-count-bgcolor'] ); ?>" <?php } ?>>
					<div class="statistics-inner">
						<div class="ld-dashboard-icons">
							<span class="ld-icons ld-icon-book-2-line"></span>
							<h2 class="statistics-label">
							<?php echo esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ); ?>
							</h2>
						</div>
						<strong class="learndash-statistics">
							<?php
							if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) ) {
								$course_count = count( learndash_get_group_leader_groups_courses() );
							} elseif ( learndash_is_admin_user() ) {
								$course_count = $this->ld_dashboard_count_post_type( 'sfwd-courses' );
							} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
								$courses      = Ld_Dashboard_Public::get_instructor_courses_list();
								$course_count = count( $courses );

							}
							echo esc_html( $course_count );
							?>
						</strong>
					</div>
				</div>

				<?php
			}
			/**
			 * Display total Quizzes
			 */
			if ( ( $is_student != '1' && isset( $tiles_options['quizzes-count'] ) && $tiles_options['quizzes-count'] == 1 ) ) {
				$sfwd_quiz_total = count_user_posts( $user_id, 'sfwd-quiz' );
				?>

					<div class="col-1-2 ld-dashboard-statistics learndash-quizzes" 
				<?php
				if ( isset( $tiles_options['quizzes-count-bgcolor'] ) && $tiles_options['quizzes-count-bgcolor'] != '' ) {
					?>
						style="background-color: <?php echo esc_attr( $tiles_options['quizzes-count-bgcolor'] ); ?>" <?php } ?>>
						<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-puzzle-piece"></span>
								<h2 class="statistics-label">
								<?php echo esc_html( LearnDash_Custom_Label::get_label( 'quizzes' ) ); ?>
								</h2>
							</div>
							<strong class="learndash-statistics">
								<?php
								if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) ) {
									$quizzes       = Ld_Dashboard_Public::get_group_leader_lessons_contents( 'quizzes' );
									$quizzes_count = count( $quizzes );
								} elseif ( learndash_is_admin_user() ) {
									$quizzes_count = $this->ld_dashboard_count_post_type( 'sfwd-quiz' );
								} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
									$quizzes       = Ld_Dashboard_Public::get_instructor_lessons_contents( 'quizzes' );
									$quizzes_count = count( $quizzes );
								} else {
									$total_quizzes = $this->ld_dashboard_get_student_data( $user_id );
									$quizzes_count = $total_quizzes['quiz_completed'];
								}
								echo esc_html( $quizzes_count );
								?>
							</strong>
						</div>
					</div>
					<?php
			}

			/*
			 * Display Assignments Pending Count
			 */

			if ( ( $is_student == '1' || in_array( 'ld_instructor', $user_roles ) || learndash_is_admin_user() ) && isset( $tiles_options['assignments-count'] ) && $tiles_options['assignments-count'] == 1 )  {
				?>
					<div class="col-1-2 ld-dashboard-statistics learndash-assignments" 
				<?php
				if ( isset( $tiles_options['assignments-count-bgcolor'] ) && $tiles_options['assignments-count-bgcolor'] != '' ) {
					?>
						style="background-color: <?php echo esc_attr( $tiles_options['assignments-count-bgcolor'] ); ?>" <?php } ?>>
						<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-calendar-check-line"></span>
								<h2 class="statistics-label">
								<?php
								$assignments_completed = 0;
								if ( isset( $tiles_options['assignments-completed-count'] ) && $tiles_options['assignments-completed-count'] == 1 ) {
									esc_html_e( 'Assignments Completed', 'ld-dashboard' );
									$assignments_completed = 1;
								} else {
									esc_html_e( 'Assignments Pending', 'ld-dashboard' );
								}
								?>
								</h2>
							</div>
							<strong class="learndash-statistics">
							<?php
							if ( $is_student == 1 ) {
								$assignments_args = array(
									'post_type'   => 'sfwd-assignment',
									'post_status' => 'publish',
									'fields'      => 'ids',
									'author'      => $user_id,
									'meta_query'  => array(
										array(
											'key'     => 'approval_status',
											'compare' => ( $assignments_completed ) ? 1 : 'NOT EXISTS',
										),
									),
								);
								echo learndash_get_assignments_pending_count( $assignments_args );
							} else {

								$assignments_args = array(
									'post_type'   => 'sfwd-assignment',
									'post_status' => 'publish',
									'fields'      => 'ids',
									'meta_query'  => array(
										array(
											'key'     => 'approval_status',
											'compare' => ( $assignments_completed ) ? 1 : 'NOT EXISTS',
										),
									),
								);

								if ( in_array( 'ld_instructor', (array) $current_user->roles ) ) {
									$get_courses_sql = "select ID from {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta pm6 ON ( {$wpdb->prefix}posts.ID = pm6.post_id ) where ( post_author={$user_id} OR ( pm6.meta_key = '_ld_instructor_ids' AND pm6.meta_value REGEXP '.*;s:[0-9]+:\"{$user_id}\"*' ) ) AND post_type='sfwd-courses' AND {$wpdb->prefix}posts.post_status = 'publish' Group By {$wpdb->prefix}posts.ID";

									$cousres    = $wpdb->get_results( $get_courses_sql );
									$course_ids = array( 0 );
									if ( ! empty( $cousres ) ) {
										$course_ids = array();
										foreach ( $cousres as $course ) {
											$course_ids[] = $course->ID;
										}
									}
									$assignments_args['meta_query'][] = array(
										'key'     => 'course_id',
										'value'   => $course_ids,
										'compare' => 'IN',
									);
								}
								echo learndash_get_assignments_pending_count( $assignments_args );
							}
							?>
							</strong>
						</div>
					</div>
					<?php
			}

			/*
			 * Display essays pending count
			 */
			if ( ( ( $is_student == '1' || in_array( 'ld_instructor', $user_roles ) || learndash_is_admin_user() ) && isset( $tiles_options['essays-pending-count'] ) && $tiles_options['essays-pending-count'] == 1 ) ) {
				?>
					<div class="col-1-2 ld-dashboard-statistics learndash-essays" 
				<?php
				if ( isset( $tiles_options['essays-pending-count-bgcolor'] ) && $tiles_options['essays-pending-count-bgcolor'] != '' ) {
					?>
						style="background-color: <?php echo esc_attr( $tiles_options['essays-pending-count-bgcolor'] ); ?>" <?php } ?>>
						<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-file-warning-line"></span>
								<h2 class="statistics-label">
								<?php esc_html_e( 'Essays Pending', 'ld-dashboard' ); ?>
								</h2>
								</div>
							<strong class="learndash-statistics">
							<?php
							if ( $is_student == 1 ) {
								$essays_args = array(
									'post_type'   => 'sfwd-essays',
									'post_status' => 'not_graded',
									'fields'      => 'ids',
									'author'      => $user_id,
								);
								echo learndash_get_essays_pending_count( $essays_args );
							} else {
								echo learndash_get_essays_pending_count();
							}
							?>
							</strong>
						</div>
					</div>
					<?php
			}

			/*
			 * Display total lessons count
			 */
			if ( ( $is_student != '1' && isset( $tiles_options['lessons-count'] ) && $tiles_options['lessons-count'] == 1 ) ) {
				?>

					<div class="col-1-2 ld-dashboard-statistics learndash-lessons" 
				<?php
				if ( isset( $tiles_options['lessons-count-bgcolor'] ) && $tiles_options['lessons-count-bgcolor'] != '' ) {
					?>
						style="background-color: <?php echo esc_attr( $tiles_options['lessons-count-bgcolor'] ); ?>" <?php } ?>>
						<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-file-list-3-line"></span>
								<h2 class="statistics-label">
						<?php echo esc_html( LearnDash_Custom_Label::get_label( 'lessons' ) ); ?>
								</h2>
							</div>
							<strong class="learndash-statistics">
				<?php
				if ( learndash_is_admin_user() ) {
					$lesson_count = $this->ld_dashboard_count_post_type( 'sfwd-lessons' );
				} elseif ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) ) {
					$lessons      = Ld_Dashboard_Public::get_group_leader_courses_lessons();
					$lesson_count = count( $lessons );
				} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
					$lessons = Ld_Dashboard_Public::get_instructor_lessons_list();
					if ( is_array( $lessons ) && ! empty( $lessons ) ) {
						$lesson_count = count( $lessons );
					} else {
						$lesson_count = 0;
					}
				} else {
					$lesson_count = 0;
				}
				echo esc_html( $lesson_count );
				?>
								</strong>
						</div>
					</div>

				<?php
			}

			/*
			 * Display total topics count
			 */
			if ( ( $is_student != '1' && isset( $tiles_options['topics-count'] ) && $tiles_options['topics-count'] == 1 ) ) {
				?>

					<div class="col-1-2 ld-dashboard-statistics learndash-topics" 
				<?php
				if ( isset( $tiles_options['topics-count-bgcolor'] ) && $tiles_options['topics-count-bgcolor'] != '' ) {
					?>
						style="background-color: <?php echo esc_attr( $tiles_options['topics-count-bgcolor'] ); ?>" <?php } ?>>
						<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-file-copy-2-line"></span>
								<h2 class="statistics-label">
						<?php echo esc_html( LearnDash_Custom_Label::get_label( 'topics' ) ); ?>
								</h2>
							</div>
							<strong class="learndash-statistics">
							<?php
							if ( learndash_is_admin_user() ) {
								$topics_count = $this->ld_dashboard_count_post_type( 'sfwd-topic' );
							} elseif ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) ) {
								$topics       = Ld_Dashboard_Public::get_group_leader_lessons_contents( 'topics' );
								$topics_count = count( $topics );
							} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
								$topics       = Ld_Dashboard_Public::get_instructor_lessons_contents( 'topics' );
								$topics_count = count( $topics );
							} else {
								$topics_count = 0;
							}
							echo esc_html( $topics_count );
							?>
								</strong>
						</div>
					</div>

				<?php
			}

			$enrolled_courses = learndash_user_get_enrolled_courses( $user_id, array(), true );
			if ( ( isset( $tiles_options['enrolled_courses_count'] ) && $tiles_options['enrolled_courses_count'] == 1 && ( $is_student == '1' || in_array( 'ld_instructor', $user_roles ) || learndash_is_admin_user() ) ) ) {
				$enrolled_bgcolor = ( isset( $tiles_options['enrolled_courses_count_bgcolor'] ) && $tiles_options['enrolled_courses_count_bgcolor'] != '' ) ? $tiles_options['enrolled_courses_count_bgcolor'] : '';
				$enrolled_bgcolor = ( '' != $enrolled_bgcolor ) ? 'background-color:' . $enrolled_bgcolor : '';
				?>
				<div class="col-1-2 ld-dashboard-statistics learndash-students" style="<?php echo esc_attr( $enrolled_bgcolor ); ?>">
					<div class="statistics-inner">
						<div class="ld-dashboard-icons">
							<span class="ld-icons ld-icon-book-reader"></span>
							<h2 class="statistics-label">
							<?php printf( esc_html__( 'Enrolled %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?>
							</h2>
						</div>
						<strong class="statistics">
						<?php
							echo count( $enrolled_courses );
						?>
						</strong>
					</div>
				</div>
						<?php } ?>
				<?php
					$active_courses    = 0;
					$completed_courses = 0;
				if ( is_array( $enrolled_courses ) && count( $enrolled_courses ) > 0 ) {
					foreach ( $enrolled_courses as $course_id ) {
						$course_data = learndash_user_get_course_progress( $user_id, $course_id, 'summary' );
						if ( $course_data['completed'] == $course_data['total'] ) {
							$completed_courses++;
						}
						if ( $course_data['completed'] < $course_data['total'] ) {
							$active_courses++;
						}
					}
				}
				if ( ( isset( $tiles_options['active_courses_count'] ) && $tiles_options['active_courses_count'] == 1 && ( $is_student == '1' || in_array( 'ld_instructor', $user_roles ) || learndash_is_admin_user() ) ) ) {
					$active_bgcolor = ( isset( $tiles_options['active_courses_count_bgcolor'] ) && $tiles_options['active_courses_count_bgcolor'] != '' ) ? $tiles_options['active_courses_count_bgcolor'] : '';
					$active_bgcolor = ( '' != $active_bgcolor ) ? 'background-color:' . $active_bgcolor : '';
					?>
				<div class="col-1-2 ld-dashboard-statistics learndash-students" style="<?php echo esc_attr( $active_bgcolor ); ?>">
					<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-book-mark-line"></span>
								<h2 class="statistics-label">
								<?php printf( esc_html__( 'Active %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?>
								</h2>
							</div>
							<strong class="statistics"><?php echo esc_html( $active_courses ); ?></strong>
					</div>
				</div>
				<?php } ?>
				<?php
				if ( ( isset( $tiles_options['completed_courses_count'] ) && $tiles_options['completed_courses_count'] == 1 && ( $is_student == '1' || in_array( 'ld_instructor', $user_roles ) || learndash_is_admin_user()) ) ) {
					$completed_bgcolor = ( isset( $tiles_options['completed_courses_count_bgcolor'] ) && $tiles_options['completed_courses_count_bgcolor'] != '' ) ? $tiles_options['completed_courses_count_bgcolor'] : '';
					$completed_bgcolor = ( '' != $completed_bgcolor ) ? 'background-color:' . $completed_bgcolor : '';
					?>
				<div class="col-1-2 ld-dashboard-statistics learndash-students" style="<?php echo esc_attr( $completed_bgcolor ); ?>">
					<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-file-certificate"></span>
								<h2 class="statistics-label">
								<?php printf( esc_html__( 'Completed %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?>
								</h2>
							</div>
							<strong class="statistics"><?php echo esc_html( $completed_courses ); ?></strong>
					</div>
				</div>
				<?php } ?>
				<?php if ( learndash_is_admin_user() || learndash_is_group_leader_user() || in_array( 'ld_instructor', (array) $current_user->roles ) ) { ?>
					<?php
					$students_arr   = array();
					$total_students = 0;
					$all_courses    = array();
					if ( in_array( 'ld_instructor', $current_user->roles ) ) {
						$all_courses = Ld_Dashboard_Public::get_instructor_courses_list();
					} elseif ( learndash_is_admin_user() ) {
						$course_args = array(
							'post_type'   => 'sfwd-courses',
							'numberposts' => -1,
							'post_status' => 'publish',
							'author'      => get_current_user_id(),
						);
						$all_courses = get_posts( $course_args );
					}
					if ( is_array( $all_courses ) && ! empty( $all_courses ) ) {
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
										if ( ! in_array( $std, $students_arr ) ) {
											$students_arr[] = $std;
											$total_students++;
										}
									}
								}
							}
						}
					}
					if ( learndash_is_group_leader_user() ) {
						$group_student = learndash_get_group_leader_groups_users();
						if ( ! empty( $students_arr ) ) {
							$group_student = array_diff( $group_student, $students_arr );
						}
						$total_students += (int) count( $group_student );
					}

					/*
					* Display total Student count
					*/
					if ( ( isset( $tiles_options['student-count'] ) && $tiles_options['student-count'] == 1 && $is_student != '1' ) ) {
						$tstudents_bgcolor = ( isset( $tiles_options['student-count-bgcolor'] ) && $tiles_options['student-count-bgcolor'] != '' ) ? $tiles_options['student-count-bgcolor'] : '';
						$tstudents_bgcolor = ( '' != $tstudents_bgcolor ) ? 'background-color:' . $tstudents_bgcolor : '';
						?>
						<div class="col-1-2 ld-dashboard-statistics learndash-students" style="<?php echo esc_attr( $tstudents_bgcolor ); ?>">
							<div class="statistics-inner">
									<div class="ld-dashboard-icons">
										<span class="ld-icons ld-icon-group-line"></span>
										<h2 class="statistics-label">
										<?php esc_html_e( 'Total Students', 'ld-dashboard' ); ?>
										</h2>
									</div>
									<strong class="statistics"><?php echo esc_html( $total_students ); ?></strong>
							</div>
						</div>
						<?php
					}
				}

				/*
				* Tiles to display total instructor earnings
				*/
				if ( ( in_array( 'ld_instructor', (array) $current_user->roles ) ) && ( isset( $tiles_options['total-earning'] ) && $tiles_options['total-earning'] == 1 ) && ld_if_commission_enabled() ) {
					?>
					<div class="col-1-2 ld-dashboard-statistics learndash-instructor-earning" 
					<?php
					if ( isset( $tiles_options['total-earning-bgcolor'] ) && $tiles_options['total-earning-bgcolor'] != '' ) {
						?>
							style="background-color: <?php echo esc_attr( $tiles_options['total-earning-bgcolor'] ); ?>"
						<?php } ?> >
						<div class="statistics-inner">
							<div class="ld-dashboard-icons">
								<span class="ld-icons ld-icon-money-dollar-box-line"></span>
								<h2 class="statistics-label">
									<?php echo esc_html__( 'Total Earnings', 'ld-dashboard' ); ?>
								</h2>
							</div>
							<?php
								$currency = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
							?>
							<strong id="earnings-usd" class="learndash-statistics"><?php echo '<span class="small">' . wp_kses_post( $currency ) . '</span> ' . esc_html( ld_dashboard_get_instructor_earnings() ); ?></strong>
						</div>
					</div>
					<?php
				}
				?>
				<?php do_action( 'ld_dashboard_after_statistics_tiles' ); ?>
			</div><!-- .ld-dashboard-statistics-container -->	
			<?php
			endif; /*  Display Statistics Tiles */

		do_action( 'ld_dashboard_after_statistics_container' );
	?>
	<div class="ld-dashboard-content-inner">
		<div class="ld-dashboard-info-table-wrap popular-courses-wrap">
			<?php
			$most_popular_courses = array();
			$current_user_data    = get_userdata( $user_id );
			$current_user_roles   = $current_user_data->roles;
			if ( learndash_is_admin_user() ) {
				$all_courses_query_args = array(
					'post_type'      => 'sfwd-courses',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				);
				$most_popular_courses   = get_posts( $all_courses_query_args );
			} elseif ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) && ld_dashboard_most_popular_course_enable_for( 'group-leader' ) ) {
				$group_course           = learndash_get_group_leader_groups_courses( $user_id );
				$all_courses_query_args = array(
					'post_type'      => 'sfwd-courses',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post__in'       => ( ! empty( $group_course ) ) ? $group_course : array( 0 ),
				);
				$most_popular_courses   = get_posts( $all_courses_query_args );

			} elseif ( in_array( 'ld_instructor', $current_user_roles ) ) {
				$most_popular_courses = Ld_Dashboard_Public::get_instructor_courses_list();
			} elseif ( ! learndash_is_admin_user() && ! learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $current_user->roles ) && ld_dashboard_most_popular_course_enable_for( 'student' ) ) {
				$all_courses_query_args = array(
					'post_type'   => 'sfwd-courses',
					'post_status' => 'publish',
					'numberposts' => -1,
				);
				$most_popular_courses   = get_posts( $all_courses_query_args );
			}
			$functions               = Ld_Dashboard_Functions::instance();
			$course_settings         = $functions->ld_dashboard_settings_data();
			$course_general_settings = $course_settings['general_settings'];
			if ( isset( $course_general_settings['popular-course-report'] ) && 1 == $course_general_settings['popular-course-report'] && count( $most_popular_courses ) > 0 ) {
				$students_arr = array();
				foreach ( $most_popular_courses as $crss ) :
					$stdnts = 0;
					if ( $course_general_settings['popular_course_tag'] > 0 ) {
						if ( has_term( $course_general_settings['popular_course_tag'], 'ld_course_tag', $crss->ID ) ) {
							$course_students           = ld_dashboard_get_course_students( $crss->ID );
							$stdnts                    = count( $course_students );
							$students_arr[ $crss->ID ] = $stdnts;
						}
					} else {
						$course_pricing = learndash_get_course_price( $crss->ID );
						if ( 'open' === $course_pricing['type'] ) {
							continue;
						}
						$course_students = ld_dashboard_get_course_students( $crss->ID );
						if ( $course_students ) {
							$stdnts                    = count( $course_students );
							$students_arr[ $crss->ID ] = $stdnts;
						}
					}
				endforeach;
				?>
				<div class="ld-dashboard-seperator popular-courses-heading">
					<span><?php printf( esc_html__( 'Most Popular %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></span>
				</div>
				<?php
				if ( count( $students_arr ) > 0 ) {
					arsort( $students_arr );
					?>
					<table class="ld-dashboard-info-table" cellspacing="0" cellpadding="0">
						<thead>
						<tr>
							<td><?php printf( '%1s %2s', esc_html( LearnDash_Custom_Label::get_label( 'course' ) ), esc_html__( 'Name', 'ld-dashboard' ) ); ?></td>
							<td><?php esc_html_e( 'Enrolled', 'ld-dashboard' ); ?></td>
							<td><?php esc_html_e( 'Status', 'ld-dashboard' ); ?></td>
						</tr>
						</thead>
						<tbody>
							<?php
							$popular_course_iteration = 0;
							foreach ( $students_arr as $key => $count ) :
								if ( 5 === $popular_course_iteration ) {
									break;
								}
								$crs = get_post( $key );
								?>
								<tr>
									<td>
										<a href="<?php echo esc_url( get_permalink( $crs->ID ) ); ?>" target="_blank"><?php echo esc_html( $crs->post_title ); ?></a>
									</td>
									<td><?php echo esc_html( $count ); ?></td>
									<td>
										<small class="label-course-status label-course-<?php echo esc_attr( $crs->post_status ); ?>"> <?php echo esc_html( ucfirst( $crs->post_status ) ); ?></small>
									</td>
								</tr>
								<?php
								$popular_course_iteration++;
							endforeach;
							?>
						</tbody>
					</table>
					<?php
				} else {
					?>
					<p class="ld-dashboard-warning"><?php printf( esc_html__( 'No %s found.', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'courses' ) ) ) ); ?></p>
					<?php
				}
			}
			?>
		</div>
	</div>
</div>
