<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$instructor_stat_data = $this->ld_get_overview_instructor_states();
?>
<div class="ld-dashboard-instructor-stats">
	<?php do_action( 'ld_dashboard_instructor_stats_before' ); ?>
	<div class="ld-dashboard-instructor-stats-block">
		<table id="ld-dashboard-instructor-stats-tbl">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ); ?></th>
					<th><?php echo esc_html( LearnDash_Custom_Label::get_label( 'lessons' ) ); ?></th>
					<th><?php echo esc_html( LearnDash_Custom_Label::get_label( 'topics' ) ); ?></th>
					<th><?php echo esc_html( LearnDash_Custom_Label::get_label( 'quizzes' ) ); ?></th>
					<th><?php esc_html_e( 'Assignments', 'ld-dashboard' ); ?></th>
					<?php
					$dynamic_ths = apply_filters( 'ld_dashboard_instructor_stats_new_column_title', $new_col_title = array() );
					if ( ! empty( $dynamic_ths ) ) {
						foreach ( $dynamic_ths as $key => $th_title ) {
							echo '<th>' . $th_title . '</th>';
						}
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $instructor_stat_data ) ) : ?>
					<?php foreach ( $instructor_stat_data as $instructor_stat ) : ?>
						<tr>
							<td><?php echo $instructor_stat['display_name']; ?></td>
							<td><?php echo $instructor_stat['course_count']; ?></td>
							<td><?php echo $instructor_stat['lesson_count']; ?></td>
							<td><?php echo $instructor_stat['topic_count']; ?></td>
							<td><?php echo $instructor_stat['quiz_count']; ?></td>
							<td><?php echo $instructor_stat['assignment_count']; ?></td>
							<?php
							$dynamic_tds = apply_filters( 'ld_dashboard_instructor_stats_new_column_content', $new_col_content = array() );
							if ( ! empty( $dynamic_tds ) ) {
								foreach ( $dynamic_tds as $key => $td_content ) {
									echo '<td>' . $td_content . '</td>';
								}
							}
							?>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td><?php echo apply_filters( 'ld_dashboard_overview_no_instructor_message', __( 'There is no instructors yet!', 'ld-dashboard' ) ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php do_action( 'ld_dashboard_instructor_stats_after' ); ?>
</div>
