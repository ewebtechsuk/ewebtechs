<?php
/**
 * List of options for Subscriptions tab.
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Options
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
}

$subscription_options = array(
	'subscriptions' => array(
		'subscription-options'            => array(
			'title' => __( 'Subscriptions', 'yith-woocommerce-stripe' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith_wcstripe_subscription_options',
		),
		'subscription-options-renew-mode' => array(
			'id'        => 'woocommerce_yith-stripe_settings[renew_mode]',
			'name'      => __( 'Subscriptions\' renewal mode', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => array(
				'stripe' => __( 'Stripe Classic', 'yith-woocommerce-stripe' ),
				'ywsbs'  => __( 'YWSBS Renews', 'yith-woocommerce-stripe' ),
			),
			'desc'      => __( 'Select how you want to process Subscriptions\' renewals.<br> Stripe Classic will create subscriptions on Stripe\'s side and let Stripe manage renewals automatically.<br> YWSBS Renews will charge renewals when triggered by YITH WooCommerce Subscription, this grants more flexibility.', 'yith-woocommerce-stripe' ),
			'default'   => 'stripe',
			'class'     => 'wc-enhanced-select',
		),
		'subscription-options-try-again'  => array(
			'id'        => 'woocommerce_yith-stripe_settings[retry_with_other_cards]',
			'name'      => __( 'If renewal fails, try again with other cards', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'If renewal fails, and the customer has additional cards registered, try to process payment with other cards, before giving up.', 'yith-woocommerce-stripe' ),
			'default'   => 'yes',
		),
		'subscription-options-end'        => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcstripe_subscription_options',
		),
	),
);

return apply_filters( 'yith_wcstripe_subscription_settings', $subscription_options );
