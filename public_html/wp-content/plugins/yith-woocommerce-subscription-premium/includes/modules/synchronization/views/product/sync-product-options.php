<?php
/**
 * Single product template options for sync module
 *
 * @since   3.0.0
 * @author  YITH
 * @package YITH\Subscription
 * @var WC_Product $product           Current product instance.
 * @var array      $sync_info         Current sync data.
 */

defined( 'YITH_YWSBS_INIT' ) || exit; // Exit if accessed directly.

?>
<div class="ywsbs-product-metabox-field ywsbs-synchronize-info" data-deps-on="_ywsbs_price_time_option" data-deps-val="weeks|months|years">
	<label for="_ywsbs_synchronize_info"><?php esc_html_e( 'Synchronize recurring payments on', 'yith-woocommerce-subscription' ); ?></label>
	<div class="ywsbs-product-metabox-field-container">

		<!-- Weeks sync options -->
		<div class="ywsbs-product-metabox-field-content" data-deps-on="_ywsbs_price_time_option" data-deps-val="weeks" data-deps-effect="plain">
			<select id="_ywsbs_synchronize_info_weeks" name="_ywsbs_synchronize_info[weeks]">
				<?php
				$val = isset( $sync_info['weeks'] ) ? $sync_info['weeks'] : get_option( 'start_of_week' );
				foreach ( ywsbs_get_period_options( 'day_weeks' ) as $day => $day_label ) :
					?>
					<option value="<?php echo esc_attr( $day ); ?>" <?php selected( $day, $val ); ?>><?php echo esc_attr( $day_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<!-- Months sync options -->
		<div class="ywsbs-product-metabox-field-content" data-deps-on="_ywsbs_price_time_option" data-deps-val="months" data-deps-effect="plain">
			<select id="_ywsbs_synchronize_info_months" name="_ywsbs_synchronize_info[months]">
				<?php
				$val = isset( $sync_info['months'] ) ? $sync_info['months'] : 1;
				for ( $day_index = 1; $day_index <= 28; $day_index++ ) :
					?>
					<option value="<?php echo esc_attr( $day_index ); ?>" <?php selected( $day_index, $val ); ?>><?php echo esc_html__( 'Day', 'yith-woocommerce-subscription' ) . ' ' . absint( $day_index ); ?></option>
				<?php endfor; ?>
				<option value="end" <?php selected( $val, 'end' ); ?>><?php echo esc_html_x( 'End of month', 'Admin product select option', 'yith-woocommerce-subscription' ); ?></option>
			</select>
			<span><?php esc_html_e( 'of each month', 'yith-woocommerce-subscription' ); ?></span>
		</div>

		<!-- Years sync options -->
		<div class="ywsbs-product-metabox-field-content" data-deps-on="_ywsbs_price_time_option" data-deps-val="years" data-deps-effect="plain">
			<?php
			$val = isset( $sync_info['years'] ) ? $sync_info['years'] : array(
				'month' => 1,
				'day'   => 1,
			);
			?>
			<select id="_ywsbs_synchronize_info_years_month" name="_ywsbs_synchronize_info[years][month]">
				<?php foreach ( ywsbs_get_period_options( 'months' ) as $month => $month_label ) : ?>
					<option value="<?php echo esc_attr( $month ); ?>" <?php selected( $month, $val['month'] ); ?>><?php echo esc_attr( $month_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<select id="_ywsbs_synchronize_info_years_day" name="_ywsbs_synchronize_info[years][day]">
				<?php for ( $day_index = 1; $day_index <= 28; $day_index++ ) : ?>
					<option value="<?php echo esc_attr( $day_index ); ?>" <?php selected( $day_index, $val['day'] ); ?>><?php echo esc_html__( 'Day', 'yith-woocommerce-subscription' ) . ' ' . absint( $day_index ); ?></option>
				<?php endfor; ?>
				<option value="end" <?php selected( $val['day'], 'end' ); ?>><?php echo esc_html_x( 'End of month', 'Admin product select option', 'yith-woocommerce-subscription' ); ?></option>
			</select>
		</div>

		<div class="ywsbs-product-metabox-field-description">
			<?php esc_html_e( 'Set a specific payment date for all users who purchase this subscription.', 'yith-woocommerce-subscription' ); ?>
		</div>
	</div>
</div>
<?php
