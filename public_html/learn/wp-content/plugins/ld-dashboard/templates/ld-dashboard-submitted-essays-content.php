<?php


$page_num           = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$params             = ( isset( $_SERVER['QUERY_STRING'] ) && '' !== $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
$user               = wp_get_current_user();
$user_id            = $user->ID;
$shared_course_ids  = array();
$dashboard_page_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
$my_args            = array(
	'post_type'      => 'sfwd-essays',
	'post_status'    => array( 'publish', 'pending', 'draft', 'not_graded', 'graded' ),
	'paged'          => $page_num,
	'posts_per_page' => 10,
);
if ( learndash_is_group_leader_user() && ! in_array( 'ld_instructor', $user->roles ) ) {
	$group_course = learndash_get_group_leader_groups_courses();
	$group_course = ( is_array( $group_course ) && ! empty( $group_course ) ) ? $group_course : array( 0 );

	$my_args['meta_query'] = array(
		array(
			'key'     => 'course_id',
			'value'   => $group_course,
			'compare' => 'IN',
		),
	);
} elseif ( in_array( 'ld_instructor', $user->roles ) ) {
	$instructor_courses = Ld_Dashboard_Public::get_instructor_courses_list();
	$course             = array();
	if ( ! empty( $instructor_courses ) ) {
		foreach ( $instructor_courses as $courses ) {
			$course[] = $courses->ID;
		}
	}
	$course                = ( is_array( $course ) && ! empty( $course ) ) ? $course : array( 0 );
	$my_args['meta_query'] = array(
		array(
			'key'     => 'course_id',
			'value'   => $course,
			'compare' => 'IN',
		),
	);
}


$essays_query = new WP_Query( $my_args );
?>


<div class="my-submitted-essayss-wrapper-view ld-dashboard-course-content instructor-courses-list">
	<div class="ld-dashboard-section-head-title">
		<h3 class="ld-dashboard-nav-title"><?php esc_html_e( 'Submitted Essays', 'ld-dashboard' ); ?></h3>
		<?php do_action( 'ld_dashboard_before_submitted-essays_filter' ); ?>
		<div class="ld-dashboard-content-inner">
			
		</div>
	</div>
	<?php do_action( 'ld_dashboard_before_submitted-essays_content' ); ?>
	<div class="ld-dashboard-student-submitted-essays-container">
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Essay Question title', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Submited By', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Status/Points', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Assigned Quiz', 'ld-dashboard' ); ?></th>
				</tr>
			</thead>
		<?php
		if ( $essays_query->have_posts() ) {
			?>
			<tbody>
			<?php
			while ( $essays_query->have_posts() ) :
				$essays_query->the_post();
					$post_id   = absint( get_the_ID() );
					$author_id = $essays_query->post_author;
				?>
				<tr>
					<td><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></td>
					<td><?php the_author_meta( 'display_name', $author_id ); ?> </td>
					<td>
					<?php
					if ( ! empty( $post_id ) ) {
						$essay              = get_post( $post_id );
						$post_status_object = get_post_status_object( $essay->post_status );
						if ( ( ! empty( $post_status_object ) ) && ( is_object( $post_status_object ) ) && ( property_exists( $post_status_object, 'label' ) ) ) {
							echo '<div class="ld-approval-status">' . sprintf(
								// translators: placeholder: Status.
								esc_html_x( 'Status: %s', 'placeholder: Status', 'ld-dashboard' ),
								esc_html( $post_status_object->label )
							) . '</div>';
						}

						$quiz_id     = get_post_meta( $post_id, 'quiz_id', true );
						$question_id = get_post_meta( $post_id, 'question_id', true );

						if ( ! empty( $quiz_id ) ) {
							$question_mapper = new WpProQuiz_Model_QuestionMapper();
							$question        = $question_mapper->fetchById( intval( $question_id ), null );
							if ( $question instanceof WpProQuiz_Model_Question ) {

								$submitted_essay_data = learndash_get_submitted_essay_data( $quiz_id, $question_id, $essay );

								echo '<div class="ld-approval-points">';
								$max_points = $question->getPoints();

								$current_points = 0;
								if ( isset( $submitted_essay_data['points_awarded'] ) ) {
									$current_points = intval( $submitted_essay_data['points_awarded'] );
								}

								if ( 'not_graded' === $essay->post_status ) {
									$points_label = '<label class="learndash-listing-row-field-label" for="essay_points_' . absint( $post_id ) . '">' . esc_html__( 'Points', 'ld-dashboard' ) . '</label>';

									$points_input = '<input id="essay_points_' . absint( $post_id ) . '" class="small-text learndash-award-points" type="number" value="' . absint( $current_points ) . '" max="' . absint( $max_points ) . '" min="0" step="1" name="essay_points[' . absint( $post_id ) . ']" data-id="' . absint( $post_id ) . '" />';

									echo sprintf(
										// translators: placeholders: Points label, points input, maximum points.
										esc_html_x( '%1$s: %2$s / %3$d', 'placeholders: Points label, points input, maximum points', 'ld-dashboard' ),
										$points_label, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										$points_input, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										absint( $max_points )
									);
								} else {
									$points_field = '<span class="learndash-listing-row-field-label">' . esc_html__( 'Points', 'ld-dashboard' ) . '</span>';
									echo sprintf(
										// translators: placeholders: Points label, current points, maximum points.
										esc_html_x( '%1$s: %2$d / %3$d', 'placeholders: Points label, points input, maximum points', 'ld-dashboard' ),
										$points_field, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										absint( $current_points ),
										absint( $max_points )
									);
								}
								echo '</div>';
							}
						}

						if ( 'not_graded' === $essay->post_status ) {
							?>
								<div class="ld-approval-action">
								<button id="essay_approve_<?php echo absint( $post_id ); ?>" class="small essay_approve_single" data-id="<?php echo absint( $post_id ); ?>"><?php esc_html_e( 'approve', 'ld-dashboard' ); ?></button>
								</div>
								<?php
						}
					}
					?>
					</td>
					<td>
					<?php

					if ( ! empty( $post_id ) ) {
						$quiz_post_id = get_post_meta( $post_id, 'quiz_post_id', true );
						$quiz_post_id = absint( $quiz_post_id );
						if ( empty( $quiz_post_id ) ) {
							$user_quiz = learndash_get_user_quiz_entry_for_essay( $post_id );
							if ( ( isset( $user_quiz['quiz'] ) ) && ( ! empty( $user_quiz['quiz'] ) ) ) {
								$quiz_post_id = absint( $user_quiz['quiz'] );
								update_post_meta( $post_id, 'quiz_post_id', $quiz_post_id );
							}
						}

						if ( ! empty( $quiz_post_id ) ) {
							$quiz_post = get_post( $quiz_post_id );
							if ( ( $quiz_post ) && ( is_a( $quiz_post, 'WP_Post' ) ) ) {
								$quiz_title = learndash_format_step_post_title_with_status_label( $quiz_post );

								$filter_url = get_permalink( $quiz_post_id );

								echo '<a href="' . esc_url( $filter_url ) . '" target="_blank">' . wp_kses_post( $quiz_title ) . '</a>';
							}
						}
					}
					?>
					</td>
				</tr>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
			</tbody>
			<?php
		} else {
			?>
		
			<tbody>
				<tr>
					<td colspan=""> 
						<?php esc_html_e( 'Submitted Essay Not found', 'ld-dashboard' ); ?>
					</td>
				</tr>
			</tbody>		
			<?php
		}
		?>
		
			<tfoot>
				<tr>
					<th><?php esc_html_e( 'Essay Question title', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Submited By', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Submited By', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Assigned Quiz', 'ld-dashboard' ); ?></th>
				</tr>
			</tfoot>
		</table>

		<?php if ( count( $essays_query->posts ) > 0 && $essays_query->max_num_pages > 1 ) : ?>
				<nav class="custom-learndash-pagination-nav">
					<ul class="custom-learndash-pagination course-pagination-wrapper">
						<?php if ( $essays_query->query_vars['paged'] > 1 ) : ?>
						<li class="custom-learndash-pagination-first"><a href="<?php echo esc_url( $dashboard_page_url . '/?tab=my-courses' ); ?>" title="<?php esc_html_e( 'First', 'ld-dashboard' ); ?>">&#8606;</a></li> 
						<?php endif; ?>
						<li class="custom-learndash-pagination-prev"><?php previous_posts_link( '&larr;', $essays_query->max_num_pages ); ?></li>
						<li class="custom-learndash-pagination-pagedisplay">
							<span>
								<?php esc_html_e( 'Page', 'ld-dashboard' ); ?>
								<span class="pagedisplay">
									<span class="current_page"><?php echo esc_html( $essays_query->query_vars['paged'] ); ?></span> / 
									<span class="total_pages"><?php echo esc_html( $essays_query->max_num_pages ); ?></span>
									(<span class="total_items"><?php echo esc_html( $essays_query->found_posts ); ?></span>)
								</span>
							</span>
						</li>
						<li class="custom-learndash-pagination-next"><?php next_posts_link( '&rarr;', $essays_query->max_num_pages ); ?></li>
						<?php if ( $essays_query->query_vars['paged'] != $essays_query->max_num_pages ) : ?>
						<li class="custom-learndash-pagination-last"><a href="<?php echo esc_url( $dashboard_page_url . '/page/' . $essays_query->max_num_pages . '/?tab=my-courses' ); ?>" title="<?php esc_html_e( 'Last', 'ld-dashboard' ); ?>">&#8608;</a></li>
						<?php endif; ?> 
					</ul>
				</nav>
		<?php endif; ?>
	</div>
	<?php do_action( 'ld_dashboard_after_submitted-essays_content' ); ?>
</div>
