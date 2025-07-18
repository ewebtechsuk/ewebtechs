<?php
if ( learndash_is_admin_user() ) {
	$args    = array(
		'post_type'      => 'sfwd-courses',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$courses = get_posts( $args );
} elseif ( in_array( 'ld_instructor', $current_user->roles ) ) {
	$courses = Ld_Dashboard_Public::get_instructor_courses_list();
}
$dashboard_page_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
$announcement_nonce = wp_create_nonce( 'announcement-nonce' );
?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
			<div class="ld-dashboard-course-content instructor-courses-list"> 
				<div class="ld-dashboard-section-head-title">
					<h3 class="ld-dashboard-nav-title"><?php echo esc_html__( 'My Announcements', 'ld-dashboard' ); ?></h3>
					<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
						<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $dashboard_page_url ); ?>?action=add-announcement&tab=my-announcements&_lddnonce=<?php echo esc_attr( $announcement_nonce ); ?>">
						<span class="ld-icons ld-icon-add-line edit_square"></span><?php echo esc_html__( 'Add new Announcement', 'ld-dashboard' ); ?></a>
					</div>
				</div>
				<?php do_action( 'ld_dashboard_before_announcements_filter' ); ?>
				<div class="ld-dashboard-content-inner">
					<div class="ld-dashboard-course-filter my-announcements-filter">
						<div class="ld-dashboard-actions-iteam">
						<label><?php printf( esc_html__( 'All %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ); ?></label>
						<select class="ld-dashboard-tab-content-filter ld-dashboard-course-filter-select">
							<option value="0"><?php printf( esc_html__( 'Select %s', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?></option>
						</select>
						</div>
						<button class="ld-dashboard-course-filter-submit ld-dashboard-btn-bg" data-type="announcements"><?php esc_html_e( 'Filter', 'ld-dashboard' ); ?></button>
					</div>
					<?php do_action( 'ld_dashboard_before_announcements_content' ); ?>
					<div class="ld-dashboard-tab-content-wrapper"></div>
					<?php do_action( 'ld_dashboard_after_announcements_content' ); ?>
				</div>
			</div>
			<nav class="custom-learndash-pagination-nav" style="display:none;">
				<ul class="custom-learndash-pagination">
					<li class="custom-learndash-pagination-first"><button class="ld-dashboard-button ld-dashboard-pagination-btn ld-dashboard-first-btn ld-dashboard-btn-bg" data-page="0">&#8606;</button></li> 
					<li class="custom-learndash-pagination-prev"><button class="ld-dashboard-button ld-dashboard-pagination-btn ld-dashboard-prev-btn ld-dashboard-btn-bg" data-page="0">&larr;</button></li>
					<li class="custom-learndash-pagination-pagedisplay">
							<span>
								<?php esc_html_e( 'Page', 'ld-dashboard' ); ?>
								<span class="pagedisplay">
									<span class="current_page"></span> / <span class="total_pages"></span> (<span class="total_items"></span>)
								</span>
							</span>
						</li> 
					<li class="custom-learndash-pagination-next"><button class="ld-dashboard-button ld-dashboard-pagination-btn ld-dashboard-next-btn ld-dashboard-btn-bg" data-page="2">&rarr;</button></li>
					<li class="custom-learndash-pagination-last"><button class="ld-dashboard-button ld-dashboard-pagination-btn ld-dashboard-last-btn ld-dashboard-btn-bg" data-page="4">&#8608;</button></li>
				</ul>
			</nav>
	</div>
</div>
