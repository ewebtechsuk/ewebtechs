<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$user_id       = get_current_user_id();
$total_courses = learndash_user_get_enrolled_courses( $user_id );
$loader        = includes_url( 'images/spinner-2x.gif' );
$loader        = apply_filters( 'ld_dashboard_loader_img_url', $loader );

$ld_dashboard_settings_data = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
$settings                   = $ld_dashboard_settings_data['tiles_options'];
$course_not_started_color   = ( isset( $settings['course_not_started_color'] ) ) ? $settings['course_not_started_color'] : '#FF7272';
$course_progress_color      = ( isset( $settings['course_progress_color'] ) ) ? $settings['course_progress_color'] : '#0EACF5';
$course_completed_color     = ( isset( $settings['course_completed_color'] ) ) ? $settings['course_completed_color'] : '#00BB30';
?>
<div class="ld-dashboard-course-quizzes-assignment-progress">
	<div class="ld-dashboard-courses">
		<select id="ld-dashboard-student-courses-id" class="ld-dashboard-select">
			<?php
			if ( ! empty( $total_courses ) ) :
				foreach ( $total_courses as $index => $course_id ) {
					?>
				<option value="<?php echo esc_attr( $course_id ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></option>
					<?php
				}
				else :
					echo "<option value='' disabled selected>" . sprintf( esc_html__( 'No enrolled %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ) . '</option>';
				endif;
				?>
		</select>
	</div>

	<div class="ld-dashboard-loader">
		<img src="<?php echo esc_url( $loader ); ?>">
		<p><?php echo apply_filters( 'ld_dashboard_waiting_text', __( 'Please wait, while details are loading...', 'ld-dashboard' ) ); ?></p>
	</div>
	<div class="ld-dashboard-student-course-report-container"></div>
</div>


<div class="ld-dashboard-course-progress">
	<h3 class="ldid-dashboard-title"><?php printf( esc_html__( '%s Progress', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?></h3>
	<ul>
		<?php
		if ( ! empty( $total_courses ) ) :
			foreach ( $total_courses as $course_id ) :
				$course_progress_data = $this->ld_dashboard_check_course_progress_data( $user_id, $course_id );
				$course_progress      = ( isset( $course_progress_data['percentage'] ) ) ? $course_progress_data['percentage'] : 0;
				$total_steps          = ( isset( $course_progress_data['total_steps'] ) ) ? $course_progress_data['total_steps'] : 0;
				$completed_steps      = ( isset( $course_progress_data['completed_steps'] ) ) ? $course_progress_data['completed_steps'] : 0;

				$student_course_progress_color = $course_progress_color;
				if ( $course_progress == 100 ) {
					$student_course_progress_color = $course_completed_color;
				}
				?>
				<li>
					<strong>
						<a href="<?php echo get_the_permalink( $course_id ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>&nbsp;
						<span class="ld-dashboard-progress-percentage">
						<?php
						echo sprintf(
							__(
								'
                %1$s%% Complete',
								'ld-dashboard'
							),
							$course_progress
						);
						?>
						</span>
						<span class="ld-dashboard-progress-steps">
						<?php
						echo sprintf(
							__(
								'
                %1$s/%2$s Steps',
								'ld-dashboard'
							),
							$completed_steps,
							$total_steps
						);
						?>
						</span>
					</strong>
					<div class="ld-dashboard-progress progress_bar_wrap" data-course="<?php echo esc_attr( $course_id ); ?>">
						<div class="ld-dashboard-progressbar ld-dashboard-animate ld-dashboard-stretch-right" data-percentage-value="<?php echo esc_attr( $course_progress ); ?>" style="background-color:<?php echo esc_attr( $student_course_progress_color ); ?>; width:0;"></div>
					</div>
				</li>
				<?php
			endforeach;

		else :
			?>
			<p><?php printf( esc_html__( 'You are not enrolled in any %s.', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?></p>
		<?php endif; ?>

	<ul>
</div>

<?php do_action( 'ld_dashboard_show_course_to_do_list', $total_courses ); ?>
