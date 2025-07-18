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

$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
$currency                   = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
$monetization_setting       = $ld_dashboard_settings_data['monetization_settings'];
$dashboard_page             = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
$withdrawal_data            = get_user_meta( get_current_user_id(), 'ld_withdrawals_data', true );
$selected_method            = ( isset( $withdrawal_data['ldd_withdraw_method'] ) ) ? $withdrawal_data['ldd_withdraw_method'] : '';
$methods                    = array(
	'bank_transfer' => esc_html__( 'Bank Transfer', 'ld-dashboard' ),
	'e_check'       => esc_html__( 'E-Check', 'ld-dashboard' ),
	'paypal'        => esc_html__( 'Paypal', 'ld-dashboard' ),
);
$prefererred_method         = '';
$has_pending_requests       = false;
if ( isset( $methods[ $selected_method ] ) ) {
	$prefererred_method = $methods[ $selected_method ];
}

$instructor_id           = get_current_user_id();
$query                   = $wpdb->prepare( 'SELECT sum(commission) as total_earning  FROM ' . $wpdb->prefix . 'ld_dashboard_instructor_commission_logs WHERE user_id = %d', $instructor_id );
$course_purchase_data    = $wpdb->get_results( $query, ARRAY_A );
$total_earning           = ( ! empty( $course_purchase_data ) ) ? $course_purchase_data[0]['total_earning'] : 0;
$instructor_paid_earning = ld_dashboard_instrictor_paid_earnings( $instructor_id, '' );
$total_earning           = $total_earning - $instructor_paid_earning;

$min_amount         = isset( $monetization_setting['minimum-withdrawal-amount'] ) ? $monetization_setting['minimum-withdrawal-amount'] : 0;
$statement_per_page = isset( $monetization_setting['statement-per-page'] ) ? $monetization_setting['statement-per-page'] : 0;
$page_num           = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args               = array(
	'post_type'      => 'withdrawals',
	'post_status'    => 'publish',
	'author'         => get_current_user_id(),
	'paged'          => $page_num,
	'posts_per_page' => $statement_per_page,
);
$withdrawals_query  = new WP_Query( $args );
$currency           = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
		<div class="ld-dashboard-course-content ld-dashboard-assignment-content instructor-courses-list">
			<div class="ld-dashboard-section-head-title">
				<h3 class="ld-dashboard-nav-title"><?php esc_html_e( 'Withdrawal', 'ld-dashboard' ); ?></h3>
			</div>
			<div class="ld-dashboard-content-inner">
				<?php do_action( 'ld_dashboard_before_withdrawal_content' ); ?>
				<div class="ld-dashboard-withdrawal-tab-content-wrapper">
					<div class="ld-dashboard-withdrawal-content">
						<div class="ld-dashboard-current-balance">
						<div class="ld-dashboard-withdraw-img-wrapper">
							<span class="ld-dashboard-withdraw-wallet-img ld-icons ld-icon-wallet-2-line"></span>
						</div>
						<div class="current-balance">
							<span><?php echo esc_html__( 'Current Balance', 'ld-dashboard' ); ?></span>
							<p>
							<?php
								/* translators: %d: */
								printf( esc_html__( 'You currently have %1$s %2$s ready to withdraw.', 'ld-dashboard' ), wp_kses_post( $currency ), esc_html( $total_earning ) );
							?>
							</p>
						</div>
						</div>
						<?php if ( $total_earning > 0 && '' !== $selected_method ) : ?>
							<button class="ld-dashboard-withdraw-modal-btn"><?php esc_html_e( 'Withdraw Request', 'ld-dashboard' ); ?></button>
						<?php endif; ?>
					</div>
					<div class="ld-dashboard-withdrawal-preference-content">
						<span class="dashicons dashicons-info-outline"></span>
						<p>
							<?php
							if ( '' !== $selected_method ) {
								/* translators: %d: */
								printf( esc_html__( 'The preferred payment method is selected as %s. ', 'ld-dashboard' ), esc_html( $prefererred_method ) );
							} else {
								printf( esc_html__( 'The preferred payment method is not selected. ', 'ld-dashboard' ), esc_html( $prefererred_method ) );
							}
							echo esc_html__( 'You can change your ', 'ld-dashboard' );
							echo wp_kses_post( '<a href="' . esc_url( $dashboard_page ) . '?tab=settings&action=withdraw">' . esc_html__( 'Withdraw Preference', 'ld-dashboard' ) . '</a>' );
							?>
						</p>
					</div>
					<div class="ld-dashboard-fee-deduction-msg-container">
						<?php if ( isset( $monetization_setting['fee-description'] ) && '' !== $monetization_setting['fee-description'] ) : ?>
							<p class="ld-dashboard-fee-deduction-msg-content">
								<?php echo esc_html( $monetization_setting['fee-description'] ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
				<?php do_action( 'ld_dashboard_after_withdrawal_content' ); ?>
			</div>
		</div>
	</div>
</div>
<?php if ( count( $withdrawals_query->posts ) > 0 ) : ?>
<div class="ld-dashboard-withdrawal-history-wrapper">
	<div class="ld-dashboard-withdrawal-history-head">
		<h4><?php echo esc_html__( 'Withdrawal History', 'ld-dashboard' ); ?></h4>
	</div>
	<div class="ld-dashboard-withdrawal-history-content">
		<div class="ld-dashboard-withdrawal-history-content-table">
			<table>
				<tr>
					<th><?php echo esc_html__( 'Withdrawal Method', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Requested On', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Amount', 'ld-dashboard' ); ?></th>
					<th><?php echo esc_html__( 'Status', 'ld-dashboard' ); ?></th>
				</tr>
				<?php
				foreach ( $withdrawals_query->posts as $request ) :
					$method     = get_post_meta( $request->ID, 'withdrawal_method', true );
					$amount     = wp_kses_post( $currency ) . ' ' . get_post_meta( $request->ID, 'withdrawal_amount', true );
					$req_status = get_post_meta( $request->ID, 'withdrawal_status', true );
					if ( 0 == $req_status ) {
						$has_pending_requests = true;
					}
					$requested_on = gmdate( 'd F Y h:i A', strtotime( $request->post_date ) );
					$status_class = 'ld-dashboard-withdrawal-pending';
					$status_label = 'Pending';
					if ( 1 == $req_status ) {
						$status_class = 'ld-dashboard-withdrawal-approved';
						$status_label = 'Approved';
					} elseif ( 2 == $req_status ) {
						$status_class = 'ld-dashboard-withdrawal-rejected';
						$status_label = 'Rejected';
					}
					$status_content = '<div class="ld-dashboard-withdrawal-status-single ' . $status_class . '">' . $status_label . '</div>';
					$req_method     = '';
					if ( isset( $methods[ $selected_method ] ) ) {
						$req_method = $methods[ $method ];
					}
					?>
					<tr class="ld-dashboard-withdrawal-list">
						<td><span class="ld-dashboard-withdrawal-method-label"><?php echo esc_html( $req_method ); ?></span></td>
						<td><?php echo esc_html( $requested_on ); ?></td>
						<td><?php echo esc_html( $amount ); ?></td>
						<td><?php echo wp_kses_post( $status_content ); ?></td>
					</tr>
					<?php
				endforeach;
				?>
			</table>
		</div>
		<?php if ( count( $withdrawals_query->posts ) > 0 && $withdrawals_query->max_num_pages > 1 ) : ?>
			<nav class="custom-learndash-pagination-nav">
				<ul class="custom-learndash-pagination course-pagination-wrapper">
					<li class="custom-learndash-pagination-prev"><?php previous_posts_link( '&laquo; PREV', $withdrawals_query->max_num_pages ); ?></li> 
					<li class="custom-learndash-pagination-next"><?php next_posts_link( 'NEXT &raquo;', $withdrawals_query->max_num_pages ); ?></li>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</div>
<?php else : ?>
	<div class="ld-dashboard-no-withdrawal-history-wrapper">
		<div class="ld-dashboard-no-withdrawal-history-img"><img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/no-data-available.png'; ?>"></div>
		<div class="ld-dashboard-no-withdrawal-history-message"><?php echo esc_html__( 'No Data Available in this Section', 'ld-dashboard' ); ?></div>
	</div>
<?php endif; ?>
<!-- withdrawal Popup -->
<div class="ld-dashboard-withdrawal-pop-up-wrapper">
	<div class="ld-dashboard-withdrawal-pop-up-section">
		<span class="ld-dashboard-close-withdrawal-popup ld-dashboard-cancel-withdrawal-request">x</span>
		<div class="ld-dashboard-withdrawal-pop-up-content">
			<div class="ld-dashboard-pop-up-details">
				<div class="ld-dashboard-withdraw-img-wrapper">
						<span class="ld-dashboard-withdraw-wallet-img ld-icons ld-icon-wallet-2-line"></span>
				</div>
					<h4><?php echo esc_html__( 'Withdrawal Request', 'ld-dashboard' ); ?></h4>
				<p>
					<?php
						/* translators: %d: */
						echo esc_html__( 'Please enter withdrawal amount and click the submit request button', 'ld-dashboard' );
					?>
				</p>
				<div class="ld-dashboard-withdrawal-request-balance">
					<div class="ld-dashboard-request-balance">
						<span><?php esc_html_e( 'Current Balance', 'ld-dashboard' ); ?></span>
						<strong><?php echo wp_kses_post( $currency ) . ' ' . esc_html( $total_earning ); ?></strong>
					</div>
					<div class="ld-dashboard-request-balance">
						<span><?php esc_html_e( 'Selected Payment Method', 'ld-dashboard' ); ?></span>
						<strong><?php echo esc_html( $prefererred_method ); ?></b></strong>
					</div>
				</div>
			</div>
			<div class="ld-dashboard-withdrawal-form-content">
				<?php if ( ! $has_pending_requests ) : ?>
				<div class="ld-dashboard-withdrawal-form-field">
					<label><?php echo esc_html__( 'Amount', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-withdrawal-form-field-amount">
						<input type="number" class="ld-dashboard-withdrawal-amount" name="withdraw_amount" min="<?php echo esc_attr( $min_amount ); ?>">
					</div>
					<div class="ld-dashboard-withdrawal-preference-content">
						<span class="dashicons dashicons-info-outline"></span>
						<p>
							<?php
								/* translators: %d: */
								printf( esc_html__( 'Minimum withdraw amount is %1$s %2$d. ', 'ld-dashboard' ), wp_kses_post( $currency ), esc_html( $min_amount ) );
							?>
						</p>
					</div>
				</div>
				<div class="ld-dashboard-withdrawal-form-submit-field">
					<button class="ld-dashboard-submit-withdrawal-request" <?php echo ( $has_pending_requests ) ? 'disabled="disabled"' : ''; ?> data-min="<?php echo esc_attr( $min_amount ); ?>" data-earning="<?php echo esc_attr( $total_earning ); ?>"><?php echo esc_html__( 'Submit Request', 'ld-dashboard' ); ?></button>
					<button class="ld-dashboard-cancel-withdrawal-request"><?php echo esc_html__( 'Cancel', 'ld-dashboard' ); ?></button>
				</div>
				<?php else : ?>
					<div class="ld-dashboard-withdrawal-pending-msg"><?php echo esc_html__( 'You have a pending withdrawal request.', 'ld-dashboard' ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="ld-dashboard-withdrawal-popup-overlap"></div>
</div>
