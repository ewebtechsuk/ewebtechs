<?php
$active_tab          = '';
$user_id             = get_current_user_id();
$dashboard_url       = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
$enrolled_courses    = learndash_user_get_enrolled_courses( $user_id, array(), true );
$enrolled_course_ids = array();
$courses             = new WP_Query(
	array(
		'post_type'      => 'sfwd-courses',
		'post__in'       => $enrolled_courses,
		'posts_per_page' => -1,
	)
);

if ( ! isset( $_GET['sub'] ) ) {
	$active_tab   = 'all-courses';
	$active_title = sprintf( esc_html__( 'All %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) );
} elseif ( isset( $_GET['sub'] ) && 'active-courses' == $_GET['sub'] ) {
	$active_tab   = 'active-courses';
	$active_title = sprintf( esc_html__( 'Active %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) );
} elseif ( isset( $_GET['sub'] ) && 'completed-courses' == $_GET['sub'] ) {
	$active_tab   = 'completed-courses';
	$active_title = sprintf( esc_html__( 'Completed %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) );
}
?>
<div class="ld-dashboard-enrolled-course instructor-courses-list">
	<h3 class="ld-dashboard-tab-heading"><?php echo esc_html( $active_title ); ?></h3>
	<div class="ld-dashboard-enrolled-course-inner">
		<div class="ld-dashboard-inline-links">
			<ul>
				<li class="<?php echo ( ! isset( $_GET['sub'] ) ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=enrolled-courses'; ?>"> <?php printf( esc_html__( 'All %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></a> </li>
				<li class="<?php echo ( isset( $_GET['sub'] ) && 'active-courses' === $_GET['sub'] ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=enrolled-courses&sub=active-courses'; ?>"> <?php printf( esc_html__( 'Active %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?> </a></li>
				<li class="<?php echo ( isset( $_GET['sub'] ) && 'completed-courses' === $_GET['sub'] ) ? 'course-nav-active' : ''; ?>"><a href="<?php echo esc_url( $dashboard_url ) . '?tab=enrolled-courses&sub=completed-courses'; ?>"><?php printf( esc_html__( 'Completed %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?> </a></li>
			</ul>
		</div>
		<div class="my-courses ld-dashboard-enrolled-course-wrap">
			<?php do_action( 'ld_dashboard_before_enrolled_courses_content' ); ?>
			<div class="ld-dashboard-enrolled-course-content ld-dashboard-content-inner">
				<?php
				if ( ! empty( $courses->posts ) && count( $courses->posts ) > 0 ) {
					foreach ( $courses->posts as $course ) {
						$course_progress     = learndash_user_get_course_progress( $user_id, $course->ID, 'summary' );
						if ( 'all-courses' === $active_tab ) {
							$enrolled_course_ids[] = $course->ID;
						} elseif ( 'completed-courses' === $active_tab && 'completed' === $course_progress['status'] ) {
							$enrolled_course_ids[] = $course->ID;
						} elseif ( 'active-courses' === $active_tab && ( 'not_started' === $course_progress['status'] || 'in_progress' === $course_progress['status'] ) ) {
							$enrolled_course_ids[] = $course->ID;
						}
					}
					if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-enrolled-courses-content-loop.php' ) ) {
						include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-enrolled-courses-content-loop.php' );
					} else {
						include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-enrolled-courses-content-loop.php';
					}
				}
				?>
			</div>
			<?php do_action( 'ld_dashboard_after_enrolled_courses_content' ); ?>
		</div>
	</div>
</div>




