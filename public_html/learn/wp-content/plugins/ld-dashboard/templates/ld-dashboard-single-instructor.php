<?php
$instructor_id      = $instructor->ID;
$current_user       = wp_get_current_user();
$enrolled_courses   = learndash_user_get_enrolled_courses( $current_user->ID, array(), true );
$total_students     = array();
$students_arr       = array();
$instructor         = get_user_by( 'id', $instructor_id );
$instructor_courses = Ld_Dashboard_Public::get_instructor_courses_list( $instructor_id );
$bio                = get_the_author_meta( 'description', $instructor_id );
$bio                = ( '' !== $bio ) ? $bio : '';
$website            = ( '' !== get_the_author_meta( 'user_url', $instructor->ID ) ) ? get_the_author_meta( 'user_url', $instructor->ID ) : '';

if ( ! empty( $instructor_courses ) ) {
	foreach ( $instructor_courses as $course ) {
		$course_students = ld_dashboard_get_course_students( $course->ID );
		if ( is_array( $course_students ) ) {
			$result         = array_diff( $course_students, $total_students );
			$total_students = array_merge( $total_students, $result );
		}
	}
}

?>
<div class="ld-dashboard-full-width-instructor-profile ld-dashboard-instructor-profile-wrap">
	<div class="ld-dashboard-instructor-profile-area">
		<div class="ld-dashboard-single-instructor-toparea">
			<div class="ld-dashboard-instructor-details-wrap">
				<div class="ld-dashboard-instructor-profile-pic">
					<img src="<?php echo esc_url( get_avatar_url( $instructor_id ) ); ?>">
				</div>
				<div class="ld-dashboard-instructor-profile-details">
					<div class="ld-dashboard-instructor-display-name">
						<small><?php echo esc_html__( 'Instructor', 'ld-dashboard' ); ?></small>
						<h2><?php echo esc_html( $instructor->display_name ); ?></h2>
					</div>
					<?php do_action( 'ld_dashboard_after_instructor_title' ); ?>
					<div class="ld-dashboard-instructor-details-item">
						<div class="instructor-details-item">
							<span><?php echo esc_html( LearnDash_Custom_Label::get_label( 'course' ) ); ?></span>
							<strong><?php echo esc_html( count( $instructor_courses ) ); ?></strong>
						</div>
						<div class="instructor-details-item">
							<span><?php echo esc_html__( 'Student', 'ld-dashboard' ); ?></span>
							<strong><?php echo esc_html( count( $total_students ) ); ?></strong>
						</div>
						<?php do_action( 'ld_dashboard_after_single_instructor_bio', $instructor_id ); ?>
						<div class="ld-dashboard-instructor-socail-link">
							<div class="ld-dashboard-instructor-profile-social-links">								
								<?php if ( '' !== $website ) : ?>
								<a href="<?php echo esc_url( $website ); ?>">
									<div class="instructor-details-item">
										<span><?php echo esc_html__( 'Website', 'ld-dashboard' ); ?></span>
										<strong><span class="ld-icons ld-icon-global-line"></span></strong>
									</div>									
								</a>
									<?php
								endif;
								do_action( 'ld_dashboard_single_instructor_social_links', $instructor_id );
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	<div class="ld-dashboard-container-instructor-biography-wrap">
		<?php do_action( 'ld_dashboard_before_single_instructor_bio', $instructor_id ); ?>
		<?php if ( '' !== $bio ) : ?>
		<div class="ld-dashboard-instructor-biography">
			<h3><?php echo esc_html__( 'About me', 'ld-dashboard' ); ?></h3>
			<p><?php echo wp_kses_post( $bio ); ?></p>
		</div>
		<?php endif; ?>		
	</div>

	<div class="ld-dashboard-instructor-course-container">
		<div class="ld-dashboard-instructor-profile-courses-content">
			<h3><?php echo esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ); ?></h3>
			<?php do_action( 'ld_dashboard_before_instructor_course_grid', $instructor_id ); ?>
			<div class="ld-dashboard-instructor-course-listing-grid">

				<?php
				if ( is_array( $instructor_courses ) && ! empty( $instructor_courses ) ) {
					echo do_shortcode( '[ld_course_list col=3]' );
				} else {
					?>
					<div class="ld-dashboard-warning"><?php printf( esc_html__( 'No %s yet.', 'ld-dashboard' ), esc_html( strtolower( LearnDash_Custom_Label::get_label( 'course' ) ) ) ); ?></p>
					<?php
				}
				?>
			</div>
			<?php do_action( 'ld_dashboard_after_instructor_course_grid', $instructor_id ); ?>
		</div>
	</div>
</div>
