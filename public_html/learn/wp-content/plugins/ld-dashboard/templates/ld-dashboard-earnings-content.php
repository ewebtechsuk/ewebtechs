<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Custom_Learndash
 * @subpackage Custom_Learndash/public/partials
 */

global $wpdb;
$instructor_id              = get_current_user_id();
$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$currency                   = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
$monetization_setting       = $ld_dashboard_settings_data['monetization_settings'];
$dashboard_page             = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );

$statement_per_page = isset( $monetization_setting['statement-per-page'] ) ? $monetization_setting['statement-per-page'] : 0;

$instructor_commission_table_name = $wpdb->prefix . 'ld_dashboard_instructor_commission_logs ';
$where_search                     = 'WHERE user_id = %d ';

$start_date   = ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' ) ? $_GET['start-date'] : '';
$end_date     = ( isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) ? $_GET['end-date'] : '';
$payment_mode = ( isset( $_GET['payment-mode'] ) && $_GET['payment-mode'] != '' ) ? $_GET['payment-mode'] : '';
if ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' && isset( $_GET['end-date'] ) && $_GET['end-date'] == '' ) {
	$where_search .= " AND Date(created) = '" . $_GET['start-date'] . "'";
} elseif ( isset( $_GET['start-date'] ) && $_GET['start-date'] == '' && isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) {
	$where_search .= " AND Date(created) = '" . $_GET['end-date'] . "'";
} elseif ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' && isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) {
	$where_search .= " AND (Date(created) between '" . $_GET['start-date'] . "' AND '" . $_GET['end-date'] . "') ";
}

if ( isset( $_GET['payment-mode'] ) && $_GET['payment-mode'] != '' ) {
	if ( $where_search != '' ) {
		$where_search .= ' AND ';
	}
	$where_search .= "source_type= '{$_GET['payment-mode']}'";
}


$total_query = $wpdb->prepare( 'SELECT * FROM ' . $instructor_commission_table_name . " {$where_search} order by ID DESC", $instructor_id );
$total       = $wpdb->get_var( $total_query );

$page                     = ( isset( $_GET['epage'] ) ) ? abs( (int) $_GET['epage'] ) : 1;
$offset                   = ( $page * $statement_per_page ) - $statement_per_page;
$query                    = $wpdb->prepare( 'SELECT * FROM ' . $instructor_commission_table_name . " {$where_search} order by ID DESC LIMIT {$offset}, {$statement_per_page}", $instructor_id );
$instrcutor_earnings_data = $wpdb->get_results( $query, ARRAY_A );
$total_page               = ceil( $total / $statement_per_page );


$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );
?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
		<div class="ld-dashboard-course-content ld-dashboard-assignment-content instructor-courses-list">
			<div class="ld-dashboard-section-head-title">
				<h3 class="ld-dashboard-nav-title"><?php esc_html_e( 'Earnings History', 'ld-dashboard' ); ?></h3>
			</div>
		</div>
	</div>
</div>

<div class="ld-dashboard-earning-logs-wrapper">
	<div class="ld-dashboard-earning-logs-head">
		<form action="" method="get">
			<input type="hidden" name="tab" value="earnings" />
			<div class="ld-dashboard-search-field">
				<input type="text" class="ld-dashboard-date-picker" id="ld-dashboard-start-date" name="start-date" value="<?php echo esc_attr( $start_date ); ?>" placeholder="<?php echo esc_html__( 'Select start date ', 'ld-dashboard' ); ?>"/>
				<input type="text" class="ld-dashboard-date-picker" id="ld-dashboard-end-date" name="end-date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="<?php echo esc_html__( 'Select end date ', 'ld-dashboard' ); ?>"/>

				<?php if ( class_exists( 'WooCommerce' ) && class_exists( 'Learndash_WooCommerce' ) ) : ?>
				<div class="ld-dashboard-payment-mode">
					<select name="payment-mode" >
						<option value=""><?php esc_html_e( 'Select payment mode', 'ld-dashboard' ); ?></option>
						<option value="learndash" <?php echo selected( 'learndash', $payment_mode ); ?>><?php esc_html_e( 'LearnDash', 'ld-dashboard' ); ?></option>
						<option value="WC" <?php echo selected( 'WC', $payment_mode ); ?>><?php esc_html_e( 'WoCommerce', 'ld-dashboard' ); ?></option>
					</select>
				</div>
				<?php endif; ?>

			</div>

			<div class="ld-dashboard-search-field ld-dashboard-export-btn">
				<input type="submit" value="<?php esc_html_e( 'Submit', 'ld-dashboard' ); ?>" />
				<a href="<?php echo esc_url( $dashboard_page ) . '?tab=earnings&export-format=csv&ld-export=instructor-commission'; ?>" target="_blank" class="button ld-dashboard-export-csv"><?php esc_html_e( 'Export CSV', 'ld-dashboard' ); ?></a>
			</div>

		</form>
	</div>
	<?php if ( ! empty( $instrcutor_earnings_data ) ) : ?>
	<div class="ld-dashboard-instrcutor-earnings-data ld-dashboard-withdrawal-history-content">
		<div class="ld-dashboard-withdrawal-history-content-table">
			<table>
				<thead>
				<tr>
					<th><?php echo esc_html__( 'ID', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Date', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html( LearnDash_Custom_Label::get_label( 'course' ) ); ?></th>
					<th><?php echo esc_html__( 'Price', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Earning', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( '% Share', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Fee', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Mode', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Reference', 'ld-dashboard' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $instrcutor_earnings_data as $earning_data ) :
					$course_pricing = learndash_get_course_price( $earning_data['course_id'] );
					$fees_type      = $earning_data['fees_type'];
					$fee_typpe      = '';
					if ( $fees_type == 'percent' ) {
						$fee_typpe = '%';
					}
					$source_type = isset( $earning_data['source_type'] ) && 'learndash' === $earning_data['source_type'] ? 'VC' : '';
					?>
					<tr>
						<td><?php echo esc_html( $earning_data['id'] ); ?></td>
						<td><?php echo esc_html( date_i18n( $date_format . ' ' . $time_format, strtotime( $earning_data['created'] ) ) ); ?></td>
						<td><a href="<?php echo esc_url( get_the_permalink( $earning_data['course_id'] ) ); ?>"><?php echo get_the_title( $earning_data['course_id'] ); ?></a></td>
						<td><?php echo $course_pricing['price']; ?></td>
						<td><?php echo $earning_data['commission']; ?></td>
						<td><?php echo $earning_data['commission_rate']; ?></td>
						<td><?php echo $earning_data['fees_amount'] . $fee_typpe; ?></td>
						<td><?php echo $source_type; ?></td>
						<td><?php echo $earning_data['reference']; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			</table>
		</div>
		<?php if ( $total_page > 1 ) { ?>
			<nav class="custom-learndash-pagination-nav">
				<?php
				$big = 999999999; // need an unlikely integer.
				echo paginate_links(
					array(
						'base'    => add_query_arg( 'epage', '%#%' ),
						'format'  => '',
						'current' => $page,
						'total'   => $total_page,
					)
				);
				?>
			</nav>
		<?php } ?>

	</div>
	<?php else : ?>
	<div class="ld-dashboard-no-withdrawal-history-wrapper">
		<div class="ld-dashboard-no-withdrawal-history-img"><img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/no-data-available.png'; ?>"></div>
		<div class="ld-dashboard-no-withdrawal-history-message"><?php echo esc_html__( 'No Data Available in this Section', 'ld-dashboard' ); ?></div>
	</div>
<?php endif; ?>
</div>
