<?php
global $course_ids;
$course_ids = ldd_get_user_courses_list( get_current_user_id(), true, true );

if ( isset( $monetization_settings['instructor-earning-report'] ) && 1 == $monetization_settings['instructor-earning-report'] && ld_if_commission_enabled() && ( ld_dashboard_is_user_role_allowed( array( 'administrator' ) ) || ld_dashboard_is_user_role_allowed( array( 'ld_instructor' ) ) ) ) {
	?>
	<div class="ld-dashboard-course-progress">
		<div class="ld-dashboard-instructor-earning-head-wrapper">
			<h3 class="ld-dashboard-instructor-earning-title"><?php esc_html_e( 'Instructor Earning', 'ld-dashboard' ); ?></h3>
			<div class="ld-dashboard-instructor-earning-filter-wrapper">
				<ul class="ld-dashboard-instructor-earning-filters-list" data-type="earning_chart">
					<li class="ld-dashboard-instructor-earning-filters-link filter-selected" data-filter="year"><?php echo esc_html__( 'Year', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="l_month"><?php echo esc_html__( 'Last Month', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="month"><?php echo esc_html__( 'This Month', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="week"><?php echo esc_html__( 'Last 7 Days', 'ld-dashboard' ); ?></li>
				</ul>
			</div>
		</div>
		<div class="ld-dashboard-instructor-earning-chart-wrapper"></div>
	</div>
	<?php
}


if ( isset( $ld_dashboard['course-completion-report'] ) && 1 == $ld_dashboard['course-completion-report'] ) {
	do_action( 'ld_dashboard_course_report_before', $user_id );
	?>
	<div class="ld-dashboard-course-progress">
		<div class="ld-dashboard-instructor-earning-head-wrapper">
			<h3 class="ld-dashboard-instructor-earning-title"><?php printf( '%1s %2s', esc_html( LearnDash_Custom_Label::get_label( 'course' ) ), esc_html__( 'Completion', 'ld-dashboard' ) ); ?></h3>
			<div class="ld-dashboard-instructor-earning-filter-wrapper">
				<div class="ld-dashboard-course-completion-course-filter">
					<?php if ( ! empty( $course_ids ) ) : ?>
					<select id="ld-dashboard-course-completion-course-filter-select">
						<option value="0"><?php esc_html_e( 'Select Course', 'ld-dashboard' ); ?></option>
						<?php foreach ( $course_ids as $course ) : ?>
							<option value="<?php echo esc_attr( $course ); ?>"><?php echo esc_html( get_the_title( $course ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php endif; ?>
				</div>
				<div class="ld-dashboard-course-completion-report-summary chart-summary">
					<div class="ld-dashbord-course-average"></div>
					<div class="ld-dashbord-course-particulars"></div>
				</div>
				<!-- <ul class="ld-dashboard-instructor-earning-filters-list" data-type="course_completion_chart">
					<li class="ld-dashboard-instructor-earning-filters-link filter-selected" data-filter="year"><?php echo esc_html__( 'Year', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="l_month"><?php echo esc_html__( 'Last Month', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="month"><?php echo esc_html__( 'This Month', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="week"><?php echo esc_html__( 'Last 7 Days', 'ld-dashboard' ); ?></li>
				</ul> -->
			</div>
		</div>
		<div class="ld-dashboard-course-completion-report-wrapper"></div>
	</div>
	
	<?php
	do_action( 'ld_dashboard_course_report__after', $user_id );
}

/*
 * Display Total Time Tracking report
 */
if ( isset( $ld_dashboard_settings_data['time_tracking']['enable'] ) && $ld_dashboard_settings_data['time_tracking']['enable'] == 1 ) {
	?>

	<div class="ld-dashboard-course-progress ld-dashboard-course-time-tracking-report">
		<div class="ld-dashboard-instructor-earning-head-wrapper">
			<h3 class="ld-dashboard-instructor-earning-title"><?php printf( '%1s %2s', esc_html__( 'Time Spent On a ', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?></h3>
			<div class="ld-dashboard-course-time-tracking-filter-wrapper">
				<div class="ld-dashboard-course-time-tracking-filter">
					<?php if ( ! empty( $course_ids ) ) : ?>
					<select id="ld-dashboard-course-time-tracking-filter-select">
						<option value=""><?php esc_html_e( 'Select Course', 'ld-dashboard' ); ?></option>
						<?php foreach ( $course_ids as $course ) : ?>
							<option value="<?php echo esc_attr( $course ); ?>"><?php echo esc_html( get_the_title( $course ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php endif; ?>
				</div>				
			</div>
		</div>
		<?php if ( empty( $course_ids ) ) : ?>
			
			<div class="ld-dashboard-chart-notice">
				<?php printf( esc_html__( 'No %1s found', 'ld-dashboard' ),  esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) );?>
			</div>
		<?php else :?>
			<div class="ld-dashboard-course-time-tracking-report-wrapper"></div>
			<div class="ld-dashboard-course-time-tracking-chart"></div>
			<div class="ld-dashboard-course-time-tracking-lists"></div>
		<?php endif;?>
	</div>
	
	<?php
}

if ( isset( $ld_dashboard['top-courses-report'] ) && 1 == $ld_dashboard['top-courses-report'] && ld_dashboard_is_user_role_allowed( $ld_dashboard['top-courses-report-roles'] ) ) {
	?>
	<div class="ld-dashboard-course-progress">
		<div class="ld-dashboard-instructor-earning-head-wrapper">
			<h3 class="ld-dashboard-instructor-earning-title"><?php printf( '%1s %2s', esc_html__( 'Top', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></h3>
			<div class="ld-dashboard-instructor-earning-filter-wrapper">
				<ul class="ld-dashboard-instructor-earning-filters-list" data-type="top_courses_chart">
					<li class="ld-dashboard-instructor-earning-filters-link filter-selected" data-filter="year"><?php echo esc_html__( 'Year', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="l_month"><?php echo esc_html__( 'Last Month', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="month"><?php echo esc_html__( 'This Month', 'ld-dashboard' ); ?></li>
					<li class="ld-dashboard-instructor-earning-filters-link" data-filter="week"><?php echo esc_html__( 'Last 7 Days', 'ld-dashboard' ); ?></li>
				</ul>
			</div>
		</div>
		<div class="ld-dashboard-top-courses-report-wrapper"></div>
	</div>
	<?php
}
