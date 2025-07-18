<div class="announcements-wrapper">
	<div class="ld-dashboard-section-head-title">
		<h3 class="ld-dashboard-nav-title"><?php esc_html_e( 'Announcements', 'ld-dashboard' ); ?></h3>
	</div>
	<div class="ld-dashboard-content-inner">
		<div class="ld-dashboard-announcement-container">
			<?php
			$page_num         = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$enrolled_courses = learndash_user_get_enrolled_courses( $user_id );
			if ( is_array( $enrolled_courses ) && ! empty( $enrolled_courses ) ) {
				$args                 = array(
					'post_type'      => 'announcements',
					'post_status'    => 'publish',
					'paged'          => $page_num,
					'posts_per_page' => 10,
					'meta_query'     => array(
						array(
							'key'     => 'course_id',
							'value'   => $enrolled_courses,
							'compare' => 'IN',
						),
					),
				);
				$announcements        = new WP_Query( $args );
				$viewed_announcements = get_user_meta( get_current_user_id(), 'ld_viewed_announcements', true );
				$viewed_announcements = ( is_array( $viewed_announcements ) && ! empty( $viewed_announcements ) ) ? $viewed_announcements : array();
				if ( is_array( $announcements->posts ) && ! empty( $announcements->posts ) ) {
					foreach ( $announcements->posts as $announcement ) {
						?>
						<div class="ld-dashboard-announcement-single <?php echo ( ! in_array( $announcement->ID, $viewed_announcements ) ) ? 'ld-unread-announcement' : ''; ?> ">
							<div class="ld-dashboard-announcement-single-title" data-id="<?php echo esc_attr( $announcement->ID ); ?>"><?php echo esc_html( $announcement->post_title ); ?></div>
						</div>
						<?php
					}
					if ( count( $announcements->posts ) > 0 && $announcements->max_num_pages > 1 ) :
						?>
						<nav class="custom-learndash-pagination-nav">
							<ul class="custom-learndash-pagination course-pagination-wrapper">
								<li class="custom-learndash-pagination-prev"><?php previous_posts_link( '&laquo; PREV', $announcements->max_num_pages ); ?></li> 
								<li class="custom-learndash-pagination-next"><?php next_posts_link( 'NEXT &raquo;', $announcements->max_num_pages ); ?></li>
							</ul>
						</nav>
						<?php
						endif;
				} else {
					?>
					<p class="ld-dashboard-warning"><?php esc_html_e( 'No new announcements found.', 'ld-dashboard' ); ?></p>
					<?php
				}
			} else {
				?>
				<p class="ld-dashboard-warning"><?php esc_html_e( 'No new announcements found.', 'ld-dashboard' ); ?></p>
				<?php
			}
			?>
			
		</div>
		<div class="ld-dashboard-announcement-content-wrapper">
			<div class="ld-announcement-content-wrapper">
				<div class="ld-dashboard-announcement-content-header">
					<h4></h4>
					<span class="ld-dashboard-announcement-content-close"></span>
				</div>
				<div class="ld-dashboard-announcement-content-body"></div>
			</div>
		</div>
	</div>
</div>
