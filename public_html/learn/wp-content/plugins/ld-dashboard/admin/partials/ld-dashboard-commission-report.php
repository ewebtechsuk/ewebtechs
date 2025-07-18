<?php
/**
 * Provide a admin area view for setting instructor commission.
 *
 * This file is used to markup the instructor commission aspects of the plugin.
 *
 * @link       https://wbcomdesigns.com/plugins
 * @since      1.0.0
 *
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/admin/partials
 */
global $wpdb;
$args        = array(
	'fields'         => array( 'ID', 'display_name' ),
	'role'           => 'ld_instructor',
	'posts_per_page' => -1,
);
$instructors = get_users( $args );

$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$currency                   = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() :
 learndash_get_currency_symbol();
$monetization_setting       = $ld_dashboard_settings_data['monetization_settings'];
$statement_per_page         = isset( $monetization_setting['statement-per-page'] ) ? $monetization_setting['statement-per-page'] : 10;

$instructor_commission_table_name = $wpdb->prefix . 'ld_dashboard_instructor_commission_logs ';
$start_date                       = ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' ) ? $_GET['start-date'] : '';
$end_date                         = ( isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) ? $_GET['end-date'] : '';
$instructor_id                    = ( isset( $_GET['instructor_id'] ) && $_GET['instructor_id'] != '' ) ? $_GET['instructor_id'] : '';
$instrcutor_earnings_data         = array();
$total_page                       = 1;
$where_search                     = '';
if ( isset( $_GET['is_search'] ) && $_GET['is_search'] == 1 ) {

	if ( isset( $_GET['instructor_id'] ) && $_GET['instructor_id'] != '' ) {
		$where_search .= " user_id = {$_GET['instructor_id']} ";
	}


	if ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' && isset( $_GET['end-date'] ) && $_GET['end-date'] == '' ) {
		if ( $where_search != '' ) {
			$where_search .= ' AND ';
		}
		$where_search .= " Date(created) = '" . $_GET['start-date'] . "'";
	} elseif ( isset( $_GET['start-date'] ) && $_GET['start-date'] == '' && isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) {
		if ( $where_search != '' ) {
			$where_search .= ' AND ';
		}
		$where_search .= " Date(created) = '" . $_GET['end-date'] . "'";
	} elseif ( isset( $_GET['start-date'] ) && $_GET['start-date'] != '' && isset( $_GET['end-date'] ) && $_GET['end-date'] != '' ) {
		if ( $where_search != '' ) {
			$where_search .= ' AND ';
		}
		$where_search .= "  (Date(created) between '" . $_GET['start-date'] . "' AND '" . $_GET['end-date'] . "') ";
	}
	if ( $where_search != '' ) {
		$where_search = "Where {$where_search}";
	}
}

$total                    = $wpdb->get_var( "SELECT * FROM {$instructor_commission_table_name} {$where_search} ORDER BY ID DESC" );
$page                     = ( isset( $_GET['epage'] ) ) ? abs( (int) $_GET['epage'] ) : 0;
$offset                   = abs( ( $page * (int) $statement_per_page ) - (int) $statement_per_page );
$query                    = $wpdb->prepare( "SELECT * FROM {$instructor_commission_table_name} {$where_search} ORDER BY ID DESC LIMIT %d", $offset );
$instrcutor_earnings_data = $wpdb->get_results( $query, ARRAY_A );


if ( $statement_per_page > 0 ) {
	$total_page = ceil( $total / $statement_per_page );
}

$total_commission = $wpdb->get_var( "SELECT sum(commission) as commission FROM {$instructor_commission_table_name} {$where_search} order by ID DESC" );

if ( ! empty( $_GET ) ) {
	$export_url = '';
	foreach ( $_GET as $key => $value ) {
		$export_url .= $key . '=' . $value . '&';
	}
	$export_url .= 'export-format=csv&ld-export=instructor-commission';
}
?>
<div class="wbcom-tab-content">
	<div class="ld-dashboard-settings">
		<div class="ld-dashboard-wrapper-admin">
			<div class="wbcom-admin-title-section title-tutorial-section-wrap">
				<h3><?php esc_html_e( 'Instructor Commission Report', 'ld-dashboard' ); ?></h3>
				<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/instructor-commission-report/the-easily-accessible-commission-report/" class="ld-tutorial-btn" target="_blank"><?php esc_html_e( 'View Tutorial', 'ld-dashboard' ); ?></a>
		</div>	
			</div>
			<div class="wbcom-admin-option-wrap ld-dashboard-content container ld-dashboard-commission-report-tab">
				<div class="">
					<div class="ld-dashboard-content container ld-dashboard-commission-report-tab">
						<div class="wbcom-settings-section-wrap ld-dashboard-fields">
							<?php if ( is_array( $instructors ) ) { ?>
								<form action="" method="get">
									<input type="hidden" name="page" value="ld-dashboard-settings" />
									<input type="hidden" name="tab" value="ld-dashboard-commission-report" />
									<input type="hidden" name="is_search" value="1" />
									<div class="ld-dashboard-search-field">
										<select name="instructor_id" id="ld-instructor-dropdown">
											<option value=''><?php esc_html_e( 'Select Instructor', 'ld-dashboard' ); ?></option>
											<?php
											foreach ( $instructors as $instructor ) {
												echo '<option value="' . esc_attr( $instructor->ID ) . '" ' . selected( $instructor_id, $instructor->ID, false ) . '>' . esc_html( $instructor->display_name ) . '</option>';
											}
											?>
										</select>
									</div>
									<div class="ld-dashboard-search-field">
										<input type="text" class="ld-dashboard-date-picker" id="ld-dashboard-start-date" name="start-date" value="<?php echo esc_attr( $start_date ); ?>" placeholder="<?php echo esc_html__( 'Select start date ', 'ld-dashboard' ); ?>"/>
							<input type="text" class="ld-dashboard-date-picker" id="ld-dashboard-end-date" name="end-date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="<?php echo esc_html__( 'Select end date ', 'ld-dashboard' ); ?>"/>
						</div>
						<div class="ld-dashboard-search-field ld-dashboard-export-btn">				
							<input type="submit" value="<?php esc_html_e( 'Submit', 'ld-dashboard' ); ?>" id="search-instructor" <?php if ( $instructor_id == '' ) : ?>
								disabled <?php endif; ?> class="button button-primary"/>
							<a href="<?php echo admin_url( 'admin.php?page=ld-dashboard-settings&tab=ld-dashboard-commission-report' ); ?>"  class="button button-primary"><?php esc_html_e( 'Reset', 'ld-dashboard' ); ?></a>
							<a href="<?php echo admin_url( 'admin.php?' . $export_url ); ?>"  class="button button-primary" target="_blank"><?php esc_html_e( 'Export CSV', 'ld-dashboard' ); ?></a>
						</div>
					</form>
					</div>
					
								<?php
								if ( isset( $_GET['is_search'] ) ) {
									$instructor_paid_earning   = ld_dashboard_instrictor_paid_earnings( $instructor_id );
									$instructor_unpaid_earning = $total_commission - $instructor_paid_earning;
									?>
						<div class="wbcom-settings-section-wrap ld-dashboard-fields">
						<div class="ld-instructor-unpaid-wrapper">
							<div class="paid-earning"><span class="dashicons dashicons-yes-alt"></span><small><?php echo esc_html( 'Paid Earning:', 'ld-dashboard' ); ?></small><strong><?php echo esc_html( $instructor_paid_earning ); ?></strong></div>
							<div class="unpaid-earning"><span class="dashicons dashicons-dismiss"></span><small><?php echo esc_html( 'Unpaid Earning:', 'ld-dashboard' ); ?></small><strong><?php echo esc_html( $instructor_unpaid_earning ); ?></strong></div>
						</div>
						</div>
								<?php } ?>
				
				<?php } ?>
				<form method="post" action="options.php" enctype="multipart/form-data">
					<?php
					settings_fields( 'ld_dashboard_comm_report_settings' );
					do_settings_sections( 'ld_dashboard_comm_report_settings' );
					?>
					<?php if ( ! empty( $instructors ) ) { ?>

						<div class="ld-instructor-commission-report-table-wrap">
							<table id="ld-instructor-commission-report" class="ld-instructor-commission-report">
								<thead>
									<tr>
										<th><?php esc_html_e( 'ID', 'ld-dashboard' ); ?></th>
										<th><?php esc_html_e( 'Date', 'ld-dashboard' ); ?></th>
										<th><?php echo esc_html( LearnDash_Custom_Label::get_label( 'course' ) ); ?></th>
										<th><?php esc_html_e( 'Price', 'ld-dashboard' ); ?></th>
										<th><?php esc_html_e( 'Earning', 'ld-dashboard' ); ?></th>
										<th><?php esc_html_e( '% Share', 'ld-dashboard' ); ?></th>
										<th><?php esc_html_e( 'Fee', 'ld-dashboard' ); ?></th>
										<th><?php esc_html_e( 'Mode', 'ld-dashboard' ); ?></th>
										<th><?php esc_html_e( 'Reference', 'ld-dashboard' ); ?></th>
									</tr>
								</thead>
								<?php
								$tr_html    = '';
								$tfoot_html = '';

								if ( ! empty( $instrcutor_earnings_data ) ) :
									?>
								<tbody>
									<?php
									$count                    = 0;
									$instructor_total_earning = 0;

									foreach ( $instrcutor_earnings_data as $key => $value ) {

										if ( false === get_post_status( $value['course_id'] ) ) {
											continue;
										}

										if ( $count % 2 == 0 ) {
											$class = 'even';
										} else {
											$class = 'odd';
										}
										$instructor_total_earning += $value['commission'];

										$fees_type = $value['fees_type'];
										$fee_typpe = '';
										if ( $fees_type == 'percent' ) {
											$fee_typpe = '%';
										}

										$tr_html .= '<tr class="' . $class . '" role="row">';
										$tr_html .= '<td>' . $value['id'] . '</td>';
										$tr_html .= '<td>' . $value['created'] . '</td>';
										$tr_html .= '<td><a href="' . get_the_permalink( $value['course_id'] ) . '">' . get_the_title( $value['course_id'] ) . '</a></td>';
										$tr_html .= '<td>' . $value['course_price'] . '</td>';
										$tr_html .= '<td>' . $value['commission'] . '</td>';
										$tr_html .= '<td>' . $value['commission_rate'] . '</td>';
										$tr_html .= '<td>' . $value['fees_amount'] . $fee_typpe . '</td>';
										$tr_html .= '<td>' . $value['source_type'] . '</td>';
										$tr_html .= '<td>#' . $value['reference'] . '</td>';
										$tr_html .= '</tr>';
										$count++;
									}
									echo $tr_html;

									?>

								</tbody>		
									<?php
								else :
									$tr_html .= '<tr>';
									$tr_html .= '<td colspan="9">' . esc_html__( 'No records found.', 'ld-dashboard' ) . '</td>';
									$tr_html .= '</tr>';
									echo $tr_html;

								endif;
								?>

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
					<?php } else { ?>
						<div class="no-instructors"><?php esc_html_e( 'No instructors registered in the site', 'ld-dashboard' ); ?></div>
					<?php } ?>	
				</form>
				<div class="ld-instructor-dialog">
					<div class="ld-instructor-dialog-container">
						<div class="ld-instructor-dialog-header">
							<?php esc_html_e( 'Instructor Data', 'ld-dashboard' ); ?><i class="fa fa-check"></i>
						</div>
						<div class="ld-instructor-dialog-msg">
							<div class="ld-instructor-dialog-desc">
								<div class="ld-instructor-dialog-div">
									<div class="ld-dialog-label move-left">
										<?php esc_html_e( 'Paid Earning', 'ld-dashboard' ); ?>
									</div>
									<div class="ld-dialog-paid-earning move-right"></div>
									<div class="ld-dialog-label move-left">
										<?php esc_html_e( 'Unpaid Earning', 'ld-dashboard' ); ?>
									</div>
									<div class="ld-dialog-unpaid-earning move-right"></div>
									<div class="ld-dialog-label move-left">
										<?php esc_html_e( 'Enter amount', 'ld-dashboard' ); ?>
									</div>
									<div class="ld-dialog-pay-amount move-right">
										<input type="number" name="ld-pay-amount" id="ld-pay-amount" min="0">
									</div>
								</div>
								<input type="hidden" id="ld-instructor-id" value="">
								<input type="hidden" id="ld-paid-earning" value="">
								<input type="hidden" id="ld-unpaid-earning" value="">
								<input type="hidden" id="ld-total-earning" value="">
								<div class="ld-pay-error"><?php esc_html_e( 'Please enter an amount less than unpaid amount.', 'ld-dashboard' ); ?></div>
							</div>
						</div>
						<ul class="ld-instructor-dialog-buttons">
							<li>
								<a class="ld-instructor-trigger-pay">
									<?php esc_html_e( 'Pay', 'ld-dashboard' ); ?>
								</a>
							</li>
							<li>
								<a class="ld-instructor-dialog-cancel">
									<?php esc_html_e( 'Cancel', 'ld-dashboard' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
