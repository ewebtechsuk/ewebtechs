<?php
/**
 * Faqs support template file.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


global $wpdb;
$table_name   = $wpdb->prefix . 'ld_dashboard_emails  ';
$where_search = '';
$user_id      = '';
if ( isset( $_GET['user_id'] ) && $_GET['user_id'] != '' ) {
	$where_search = ' WHERE user_id = ' . $_GET['user_id'];
	$user_id      = $_GET['user_id'];
}
$customPagHTML  = '';
$total_query    = 'SELECT count(*) FROM ' . $table_name . " {$where_search} ORDER BY ID DESC";
$total          = $wpdb->get_var( $total_query );
$items_per_page = 20;
$page           = ( isset( $_GET['cpage'] ) ) ? abs( (int) $_GET['cpage'] ) : 1;
$offset         = ( $page * $items_per_page ) - $items_per_page;
$query          = 'SELECT * FROM ' . $table_name . " {$where_search} ORDER BY ID DESC LIMIT {$offset}, {$items_per_page}";
$result         = $wpdb->get_results( $query );
$total_page     = ceil( $total / $items_per_page );

$total_user_query  = 'SELECT DISTINCT(user_id)  FROM ' . $table_name . ' ORDER BY user_id DESC';
$total_user_result = $wpdb->get_results( $total_user_query );

$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );
?>
<div class="wbcom-tab-content">
	<div class="ld-dashboard-wrapper-admin">
	<div class="wbcom-admin-title-section  title-tutorial-section-wrap">
		<h3><?php esc_html_e( 'Email Logs', 'ld-dashboard' ); ?></h3>
		<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/email-log/email-logs/" class="ld-tutorial-btn" target="_blank"><?php esc_html_e( 'View Tutorial', 'ld-dashboard' ); ?></a>
	</div>
	<div class="wbcom-admin-option-wrap ">
	<form id="search_email_logs" action="" method="get">
		<input type="hidden" name="page" value="ld-dashboard-settings" />
		<input type="hidden" name="tab" value="email_logs" />		
		<select id="search_by_user_id" name="user_id">
			<option value=""><?php esc_html_e( 'Search By User', 'ld-dashboard' ); ?></option>
			<?php
			foreach ( $total_user_result as $user ) :
				$user_info = get_userdata( $user->user_id );
				?>
				<option value="<?php echo esc_attr( $user->user_id ); ?>" <?php selected( $user_id, $user->user_id ); ?>><?php echo esc_html( $user_info->display_name ); ?></option>
			<?php endforeach; ?>
		</select>
	</form>
	<div class="ld-instructor-email-logs-table-wrap">
		<table class="table wp-list-table widefat fixed striped table-view-list ld-instructor-email-logs">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Email Subject', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Scheduled By', 'ld-dashboard' ); ?></th>
					<th><?php printf( esc_html__( '%s Name', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'course' ) ) ); ?></th>
					<!--th><?php // esc_html_e( 'Sending To', 'ld-dashboard' ); ?></th-->
					<th><?php esc_html_e( 'Total Student', 'ld-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Schedule Run Date', 'ld-dashboard' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $result ) :
					foreach ( $result as $res ) :
						$user_info   = get_userdata( $res->user_id );
						$course_ids  = json_decode( $res->course_ids, true );
						$student_ids = json_decode( $res->student_ids, true );
						?>
					<tr>
						<td><?php echo $res->email_subject; ?></td>
						<td><?php echo $user_info->display_name; ?></td>
						<td>
							<?php
							$course_name = '';
							foreach ( $course_ids as $cid ) {
								$course_name .= "<a href='" . get_the_permalink( $cid ) . "' target='_blank'>" . get_the_title( $cid ) . '</a>, ';

							}
							echo substr( trim( $course_name ), 0, -1 );
							?>
						</td>
						<!--td>
							<?php
							/*
							$student_name = '';
							foreach($student_ids as $uid){
								$student_info = get_userdata($uid);
								$student_name .= "<a href='" . get_author_posts_url($uid) . "' target='_blank'>" . $student_info->display_name . "</a>, ";

							}*/
							echo '-'; // substr(trim($student_name), 0, -1);
							?>

						</td-->
						<td><?php echo count( $student_ids ); ?></td>
						<td>
							<?php echo date_i18n( $date_format . ' ' . $time_format, strtotime( $res->created ) ); ?>
						</td>
					</tr>
						<?php
					endforeach;
				else :
					?>
					<tr >
						<td colspan="5"><?php esc_html_e( 'You have no email logs.', 'ld-dashboard' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	</div>
	
	<?php if ( $total_page > 1 ) { ?>
		<div class="ld-instructor-email-logs-pagination">		
			<?php
			$big = 999999999; // need an unlikely integer.
			echo paginate_links(
				array(
					'base'    => add_query_arg( 'cpage', '%#%' ),
					'format'  => '',
					'current' => $page,
					'total'   => $total_page,
				)
			);
			?>
		</div>
	<?php } ?>
</div>
</div>
