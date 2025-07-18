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
?>
<?php
$essay_report = Ld_Dashboard_Reports::getInstance();
$user_id      = get_current_user_id();
$report_title = '';
$report_tab   = '';

if ( isset( $_GET ) && ! empty( $_GET['tab'] ) ) {
	switch ( $_GET['tab'] ) {
		case 'essay-report':
			$report_title = __( 'Essay Report', 'ld-dashboard' );
			$report_tab   = 'essay-report';
			break;
		case 'assignment-report':
			$report_title = __( 'Assignment Report', 'ld-dashboard' );
			$report_tab   = 'assignment-report';
			break;
		case 'quizz-report':
			$report_title = __( 'Quizz Report', 'ld-dashboard' );
			$report_tab   = 'quizz-report';
			break;
	}
}


?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
			<div class="ld-dashboard-course-content instructor-courses-list"> 
				<div class="ld-dashboard-section-head-title">
					<h3><?php echo $report_title; ?></h3>
				</div>
				<div class="my-courses ld-dashboard-content-inner ld-dashboard-tab-content-wrapper">
					<?php do_action( 'ld_dashboard_before_report' ); ?>
						<?php
						if ( array_intersect( wp_get_current_user()->roles, array_keys( ld_dashboard_get_dashboard_user_roles() ) ) ) {
							$ldd_filter_drop_downs = $essay_report->ld_dasboard_report_dropdown( $user_id );
							?>

						<div id="ld-dashboard-report" class="ld-dashboard-report ld-dashboard-reports ld-dashboard-report-container">
							<div class="ld-dashboard-eassasy-report-filter ld-dashboard-report-filters">
								<div class="ld-dashboard-report-filters-section <?php echo $report_tab; ?>">
								<?php if ( isset( $ldd_filter_drop_downs['groups'] ) ) { ?>
									<div class="ld-row ld-select-filter">
										<div class="ld-select">
											<label><?php _e( 'Group', 'ld-dashboard' ); ?></label>
											<select class="ld-change-drop-down"
													id="ld-dashboard-report-group" name="groupId"><?php echo $ldd_filter_drop_downs['groups']; ?></select>
										</div>
									</div>
								<?php } ?>
								<?php if ( isset( $ldd_filter_drop_downs['courses'] ) ) { ?>
									<div class="ld-row ld-select-filter">
										<div class="ld-select">
											<label><?php echo sprintf( _x( '%s', 'Courses', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?></label>
											<select class="ld-change-drop-down <?php echo isset( $ldd_filter_drop_downs['courses_class'] ) ? esc_attr( $ldd_filter_drop_downs['courses_class'] ) : ''; ?>"
													id="ld-dashboard-report-course" name="courseId" <?php echo isset( $ldd_filter_drop_downs['groups'] ) ? 'disabled="disabled"' : ''; ?>><?php echo $ldd_filter_drop_downs['courses']; ?></select>
											<div id="ld-essay-report-nocourses" class="group-management-rest-message" style="display: none;"><?php echo sprintf( __( 'No %s found.', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?></div>
										</div>
									</div>
								<?php } ?>
								<?php if ( isset( $ldd_filter_drop_downs['lessons'] ) && ( 'assignment-report' === $report_tab || 'essay-report' === $report_tab ) ) { ?>
									<div class="ld-row ld-select-filter">
										<div class="ld-select">
											<label><?php printf( _x( '%1$s / %2$s', 'LearnDash lesson and topic labels', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lesson' ), LearnDash_Custom_Label::get_label( 'topic' ) ); ?></label>
											<select class="ld-change-drop-down <?php echo isset( $ldd_filter_drop_downs['lessons_class'] ) ? esc_attr( $ldd_filter_drop_downs['lessons_class'] ) : ''; ?>"
													id="ld-dashboard-report-lesson" disabled="disabled" name="lessonId"><?php echo $ldd_filter_drop_downs['lessons']; ?></select>
											<div id="ld-essay-report-nolessons" class="group-management-rest-message" style="display: none;"><?php echo sprintf( _x( 'No %s found.', 'No lessons found.', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'lessons' ) ); ?></div>
										</div>
									</div>
								<?php } ?>
								<?php if ( isset( $ldd_filter_drop_downs['quizzes'] ) && ( 'quizz-report' === $report_tab || 'essay-report' === $report_tab ) ) { ?>
									<div class="ld-row ld-select-filter">
										<div class="ld-select">
											<label><?php _e( LearnDash_Custom_Label::get_label( 'quizzes' ), 'ld-dashboard' ); ?></label>
											<select class="ld-change-drop-down <?php echo isset( $ldd_filter_drop_downs['quizzes_class'] ) ? esc_attr( $ldd_filter_drop_downs['quizzes_class'] ) : ''; ?>"
													id="ld-dashboard-report-quiz" disabled="disabled" name="quizId"><?php echo $ldd_filter_drop_downs['quizzes']; ?></select>
											<div id="ld-essay-report-noquizzes" class="group-management-rest-message"
												 style="display: none;"><?php echo sprintf( _x( 'No %s found.', 'No quizzes found', 'ld-dashboard' ), LearnDash_Custom_Label::get_label( 'quizzes' ) ); ?></div>
										</div>
									</div>
								<?php } ?>
									
								
								<?php if ( 'quizz-report' !== $report_tab ) : ?>
								<div class="ld-row ld-select-filter">
									<div class="ld-select">
										<label><?php _e( 'Status', 'ld-dashboard' ); ?></label>
										<select class="ld-change-drop-down" id="ld-dashboard-report-status" name="status">
											<option value="all"><?php _e( 'All', 'ld-dashboard' ); ?></option>
											<?php if ( 'assignment-report' === $report_tab ) : ?>
												<option value="approved"><?php _e( 'Approved', 'ld-dashboard' ); ?></option>
												<option value="not-approved"><?php _e( 'Not approved', 'ld-dashboard' ); ?></option>
											<?php elseif ( 'essay-report' === $report_tab ) : ?>
												<option value="graded"><?php _e( 'Graded', 'ld-dashboard' ); ?></option>
												<option value="ungraded"><?php _e( 'Ungraded', 'ld-dashboard' ); ?></option>
											<?php endif; ?>
										</select>
									</div>
								</div>
								<?php endif; ?>
								</div>
								
								<div class="ld-dashboard-essay-report-table-wrapper ld-dashboard-report-table">
									<table id="ld-dashboard-report-table" class="ld-dashboard-table display ld-dashboard-datatable" cellspacing="0" width="100%" data-table="<?php echo esc_attr( sanitize_title( $report_title ) ); ?>"></table>
								</div>
							</div>
						</div>
							<?php
						} else {
							echo __( 'You must be a admin, group leader of instrutor to access this page.', 'ld-dashboard' );
							return;
						}
						?>
					<?php do_action( 'ld_dashboard_after_report' ); ?>
				</div>
			</div>
	</div>
</div>
