<?php
/**
 * Admin view: Blacklist table
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Templates\Admin
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Template variblaes.
 *
 * @var YITH_Stripe_Blacklist_Table $blacklist_table
 */

if ( $blacklist_table->is_empty_table() ) {
	?>
	<div class="yith-wcstripe-empty-state-container">
		<div class="yith-wcstripe-cta-container">
			<?php
				yith_plugin_fw_get_component(
					array(
						'type'     => 'list-table-blank-state',
						'icon_url' => YITH_WCSTRIPE_URL . 'assets/images/black-list.svg',
						'message'  => __( 'You do not have any banned users yet', 'yith-woocommerce-stripe' ),
					)
				);
			?>
		</div>
	</div>
	<?php
} else {
	?>
	<div class="yith-plugin-fw-panel-custom-tab-container">
		<h2><?php esc_html_e( 'Stripe Blacklist', 'yith-woocommerce-stripe' ); ?></h2>

		<?php $blacklist_table->views(); ?>

		<form id="commissions-filter" class="yith-plugin-ui--classic-wp-list-style" method="get">
			<input type="hidden" name="page" value="yith_wcstripe_panel" />
			<input type="hidden" name="tab" value="blacklist" />
			<?php $blacklist_table->add_search_box( __( 'Search bans', 'yith-woocommerce-stripe' ), 's' ); ?>
			<?php $blacklist_table->display(); ?>
		</form>
	</div>
	<?php
}
