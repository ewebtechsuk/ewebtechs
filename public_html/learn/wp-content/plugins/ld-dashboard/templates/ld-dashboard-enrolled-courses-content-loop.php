<?php
if ( empty( $enrolled_course_ids ) ) {
	?>
	<div class="ld-dashboard-all-courses-content"><p class="ld-dashboard-warning"><?php printf( esc_html__( 'You haven\'t purchased any %s.', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?></p></div>
	<?php
	return;
}
$paged            = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$enrolled_courses = new WP_Query(
	array(
		'post_type'      => 'sfwd-courses',
		'post__in'       => $enrolled_course_ids,
		'posts_per_page' => 5,
		'paged'          => $paged,
	)
);

if ( $enrolled_courses->have_posts() ) :
	while ( $enrolled_courses->have_posts() ) :
		$enrolled_courses->the_post();
		$attachment_image = '';
		if ( has_post_thumbnail() ) {
			$attachment_image = wp_get_attachment_url( get_post_thumbnail_id() );
		} else {
			$attachment_image = apply_filters( 'ld_dasboard_active_course_image_placeholder', LD_DASHBOARD_PLUGIN_URL . 'public/img/course-default.png' );
		}
		// Course Last Activity
		$last_activity = learndash_activity_course_get_latest_completed_step( $user_id, get_the_ID() );
		if ( is_array( $last_activity ) && isset( $last_activity['activity_completed'] ) ) {
			$activity_date      = gmdate( 'F d, Y', $last_activity['activity_completed'] );
			$last_activity_text = sprintf( esc_html__( 'Last activity on %s', 'ld-dashboard' ), $activity_date );
		} else {
			$last_activity_text = '';
		}
		$enrolled_course_progress = learndash_user_get_course_progress( $user_id, get_the_ID(), 'summary' );
		$progress_percentage      = ( $enrolled_course_progress['total'] > 0 ) ? ( $enrolled_course_progress['completed'] * 100 ) / $enrolled_course_progress['total'] : 0;
		$progress_percentage      = ( ( 0 == $enrolled_course_progress['total'] && 0 == $enrolled_course_progress['completed'] ) ) ? 0 : round( $progress_percentage );
		if ( 0 == $progress_percentage ) {
			$course_status_text  = esc_html__( 'Start Course', 'ld-dashboard' );
			$course_status_class = 'start-course';
		} elseif ( $progress_percentage < 100 ) {
			$course_status_text  = esc_html__( 'In Progress', 'ld-dashboard' );
			$course_status_class = 'in-progress';
		} elseif ( 100 == $progress_percentage ) {
			$course_status_text  = esc_html__( 'Complete', 'ld-dashboard' );
			$course_status_class = 'complete';
		}

		?>
		<div id="ld-dashboard-course-<?php echo esc_html( get_the_ID() ); ?>" class="ld-mycourse-wrap ld-mycourse-<?php echo esc_html( get_the_ID() ); ?> __web-inspector-hide-shortcut__">
			<div class="ld-mycourse-thumbnail" style="background-image: url(<?php echo esc_url( $attachment_image ); ?>);"></div>
			<div class="ld-mycourse-content">
			<?php do_action( 'ld_add_course_content_before' ); ?>
				<div class="ld-dashboard-enrolled-course-status <?php echo esc_html( $course_status_class ); ?>"><?php echo esc_html( $course_status_text ); ?></div>
				<h3><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h3>
				<div class="ld-meta ld-course-metadata">
					<ul>
						<li><span class="ld-dashboard-progress-percent"><?php echo esc_html( $progress_percentage ) . __( '%  Complete', 'ld-dashboard' ); ?></span></li>
						<li><span><?php echo esc_html( $enrolled_course_progress['completed'] . '/' . $enrolled_course_progress['total'] ); ?> <?php printf( esc_html__( 'Steps', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'lessons' ) ) ); ?></span></li>
					</ul>
				</div>
				<div class="ld-dashboard-course-last-activity-time">
					<div class="ld-dashboard-last-activity"><?php echo esc_html( $last_activity_text ); ?></div>
					<?php if ( $progress_percentage < 100 ) : ?>
						<div class="ld-dashboard-enrolled-course-resume-btn"><?php // echo do_shortcode( '[ld_course_resume course_id="' . $course_id . '" user_id="' . $user_id . '"]' ); ?></div>
						<?php endif; ?>
					</div>
					<div class="ld-dashboard-enrolled-course-author-content-user">
						<img class="ld-dashboard-course-author-avatar" src="<?php echo esc_url( get_avatar_url( get_the_author_meta( 'ID' ), 320 ) ); ?>">
						<span class="ld-dashboard-course-author-name"> <?php echo esc_html( get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) ) ); ?></span>
					</div>
				</div>
			</div>
		<?php
	endwhile;
	if ( ! empty( $enrolled_courses->posts ) && $enrolled_courses->found_posts > 5 && $enrolled_courses->max_num_pages > 1 ) :
		?>
		<nav class="custom-learndash-pagination-nav">
			<ul class="custom-learndash-pagination course-pagination-wrapper">
				<?php if ( $paged > 1 ) : ?>
				<li class="custom-learndash-pagination-first"><a href="<?php echo esc_url( $dashboard_url . '/?tab=enrolled-courses' ); ?>" title="<?php esc_html_e( 'First', 'ld-dashboard' ); ?>">&#8606;</a></li> 
				<?php endif; ?>
				<li class="custom-learndash-pagination-prev"><?php previous_posts_link( '&larr;', $enrolled_courses->max_num_pages ); ?></li>
				<li class="custom-learndash-pagination-pagedisplay">
					<span>
						<?php esc_html_e( 'Page', 'ld-dashboard' ); ?>
						<span class="pagedisplay">
							<span class="current_page"><?php echo esc_html( $paged ); ?></span> / 
							<span class="total_pages"><?php echo esc_html( $enrolled_courses->max_num_pages ); ?></span>
							(<span class="total_items"><?php echo esc_html( $enrolled_courses->found_posts ); ?></span>)
						</span>
					</span>
				</li>
				<li class="custom-learndash-pagination-next"><?php next_posts_link( '&rarr;', $enrolled_courses->max_num_pages ); ?></li>
				<?php if ( $enrolled_courses->query_vars['paged'] != $enrolled_courses->max_num_pages ) : ?>
				<li class="custom-learndash-pagination-last"><a href="<?php echo esc_url( $dashboard_url . '/page/' . $enrolled_courses->max_num_pages . '/?tab=enrolled-courses' ); ?>" title="<?php esc_html_e( 'Last', 'ld-dashboard' ); ?>">&#8608;</a></li>
				<?php endif; ?> 
			</ul>
		</nav>
		<?php
	endif;
endif;
wp_reset_postdata();
