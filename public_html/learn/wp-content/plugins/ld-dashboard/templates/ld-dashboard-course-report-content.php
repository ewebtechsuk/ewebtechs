<?php
/**
 * LD Dashboard Reports Content
 *
 * This file is used to markup the report
 *
 * @link       https://wbcomdesigns.com/
 * @since      5.9.9
 *
 * @package    Custom_Learndash
 * @subpackage Custom_Learndash/public/partials
 */


$user_id      = get_current_user_id();
$report_title = '';
$report_tab   = '';
$report_title = __( 'Course Reports', 'ld-dashboard' );
$report_tab   = 'course-report';
$cours_ids    = ldd_get_user_courses_list( get_current_user_id(), true, true );
?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
			<div class="ld-dashboard-course-content instructor-courses-list"> 
				<div class="ld-dashboard-section-head-title">
					<h3><?php echo esc_html( $report_title ); ?></h3>
				</div>
				<div class="my-courses ld-dashboard-content-inner ld-dashboard-tab-content-wrapper">
					<?php
					do_action( 'ld_dashboard_before_course_report' );


					if ( isset( $_GET['_lddnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_lddnonce'] ) ), 'course-report-nonce' ) ) {
						echo esc_html__( 'You do not have the required permissions.', 'ld-dashboard' );
					} else {
						if ( array_intersect( wp_get_current_user()->roles, array_keys( ld_dashboard_get_dashboard_user_roles() ) ) ) {

							if ( isset( $_GET['user'] ) && ! empty( $_GET['user'] ) && isset( $_GET['course_id'] ) && ! empty( $_GET['course_id'] ) ) {

								$avatar_url = get_avatar_url( sanitize_text_field( wp_unslash( $_GET['user'] ) ) );
								$user       = get_user_by( 'id', sanitize_text_field( wp_unslash( $_GET['user'] ) ) );
								$user_name  = $user->first_name . ' ' . $user->last_name;
								$user_name  = ! empty( $user_name ) ? $user_name : $user->display_name;
								?>
								<div id="ld-dashboard-course-report" class="ld-dashboard-report ld-dashboard-reports ld-dashboard-report-container">
									<div class="ld-dashboard-eassasy-report-filter ld-dashboard-report-filters">
										<div class="ld-dashboard-student-course-report-table-wrapper ld-dashboard-report-table">
											<table id="ld-dashboard-student-course-report-table" class="ld-dashboard-table display ld-dashboard-datatable" cellspacing="0" width="100%" data-user="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['user'] ) ) ); ?>" data-course="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['course_id'] ) ) ); ?>" >
											</table>
											<div class="ld-dashboard-user-info">
												<div class="ld-dashboard-user-avatar">
													<img src="<?php echo esc_url( $avatar_url ); ?>" />
												</div>
												<div class="ld-dashboard-user-card-content">
													<p class="ld-dashboard-user-name"><?php echo esc_html( $user_name ); ?></p>
													<p class="ld-dashboard-user-email"><?php echo esc_html( $user->user_email ); ?></p>
												</div>
											</div>
											
											<div class="ld-dashboard-inline-links">
												<ul class="ld-dashboard-inline-links-ul ld-dashboard-student-course-report-ul">
													<li class="course-nav-active"><a href="#" class="ld-dashboard-student-course-report-tab ld-dashboard-form-tab-switch" data-id="single-user-course-lessons-container"><?php echo LearnDash_Custom_Label::get_label( 'lessons' ); ?></a></li>
													<li><a href="#" class="ld-dashboard-student-course-report-tab ld-dashboard-form-tab-switch" data-id="single-user-course-topics-container"><?php echo LearnDash_Custom_Label::get_label( 'topics' ); ?></a></li>
													<li><a href="#" class="ld-dashboard-student-course-report-tab ld-dashboard-form-tab-switch" data-id="single-user-course-quizzes-container"><?php echo LearnDash_Custom_Label::get_label( 'quizzes' ); ?></a></li>
												</ul>
											</div>
											
											<div id="single-user-course-lessons-container" class="ld-dashboard-student-course-report-tabs active">
												<h3><?php echo LearnDash_Custom_Label::get_label( 'lessons' ); ?></h3>
												<div id="ld-dashboard-course-chart-loader" class="ld-dashboard-chart-loader"><img src="<?php echo apply_filters( 'ld_dashboard_char_loader_url', LD_DASHBOARD_PLUGIN_URL . 'public/icons/chart-loader.svg' ); ?>" /></div>
												<table id="ld-dashboard-student-course-lessons-report-table" class="ld-dashboard-table display ld-dashboard-datatable" cellspacing="0" width="100%" data-user="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['user'] ) ) ); ?>" data-course="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['course_id'] ) ) ); ?>" ></table>
											</div>
											<div id="single-user-course-topics-container" class="ld-dashboard-student-course-report-tabs">
												<h3><?php echo LearnDash_Custom_Label::get_label( 'topics' ); ?></h3>
												<table id="ld-dashboard-student-course-topics-report-table" class="ld-dashboard-table display ld-dashboard-datatable" cellspacing="0" width="100%" data-user="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['user'] ) ) ); ?>" data-course="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['course_id'] ) ) ); ?>" ></table>
											</div>
											<div id="single-user-course-quizzes-container" class="ld-dashboard-student-course-report-tabs">
												<h3><?php echo LearnDash_Custom_Label::get_label( 'quizzes' ); ?></h3>
												<table id="ld-dashboard-student-course-quizzes-report-table" class="ld-dashboard-table display ld-dashboard-datatable" cellspacing="0" width="100%" data-user="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['user'] ) ) ); ?>" data-course="<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['course_id'] ) ) ); ?>" ></table>
											</div>
										</div>
									</div>
								</div>
								<?php
							} else {
								?>
							<div id="ld-dashboard-course-report" class="ld-dashboard-report ld-dashboard-reports ld-dashboard-report-container">
							<div class="ld-dashboard-eassasy-report-filter ld-dashboard-report-filters">
								<div class="ld-dashboard-report-filters-section <?php echo $report_tab; ?>">
									<div class="ld-row ld-select-filter">
										<div class="ld-select">
											<label><?php echo sprintf( _x( '%s', 'Courses', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?></label>
											<select class="ld-change-drop-down " id="ld-dashboard-course-report-select" name="courseId" >
												<option value="0"><?php esc_html_e( 'Select Course', 'ld-dashboard' ); ?></option>
												<?php foreach ( $cours_ids as $course ) : ?>
													<option value="<?php echo esc_attr( $course ); ?>"><?php echo esc_html( get_the_title( $course ) ); ?></option>
												<?php endforeach; ?>												
											</select>
											<div id="ld-course-report-nocourses" class="group-management-rest-message" style="display: none;"><?php echo sprintf( __( 'No %s found.', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?></div>
										</div>
									</div>
								</div>
								<div class="ld-dashboard-course-report-table-wrapper ld-dashboard-report-table">
									<table id="ld-dashboard-course-report-table" class="ld-dashboard-table display ld-dashboard-datatable" cellspacing="0" width="100%" data-table="<?php echo esc_attr( sanitize_title( $report_title ) ); ?>">
									</table>
								</div>
							</div>
						</div>
								<?php
							}
						} else {
							echo __( 'You must be a admin, group leader of instrutor to access this page.', 'ld-dashboard' );
							return;
						}
					}
					?>
					<?php do_action( 'ld_dashboard_after_course_report' ); ?>
				</div>
			</div>
	</div>
</div>
