<?php
/**
 * List of options for Settings tab.
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

$doc_url            = 'https://stripe.com/docs/payments/capture-later';
$debug_url          = 'https://dashboard.stripe.com/logs';
$stripe_keys        = 'https://dashboard.stripe.com/account/apikeys';
$stripe_development = 'https://dashboard.stripe.com/account/webhooks';

$general_options = array(
	'settings' => array(
		'general-options'                             => array(
			'title' => __( 'Stripe', 'yith-woocommerce-stripe' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith_wcstripe_options',
		),
		'general-options-enable'                      => array(
			'id'        => 'woocommerce_yith-stripe_settings[enabled]',
			'name'      => __( 'Enable/Disable', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable to use the plugin features.', 'yith-woocommerce-stripe' ),
			'default'   => 'yes',
			'class'     => 'woocommerce_yith-stripe_enabled',
		),
		'api-test-secret-key'                         => array(
			'id'        => 'woocommerce_yith-stripe_settings[test_secrect_key]',
			'name'      => __( 'Test secret key', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			// translators: %s is the path to the stripe webhook configuration panel.
			'desc'      => sprintf( __( 'Set the secret API key for the test mode. You can find it in <a href="%s" target="_blank">your Stripe dashboard</a>.', 'yith-woocommerce-stripe' ), $stripe_keys ),
			'default'   => '',
			'class'     => 'woocommerce_yith-stripe_test_secrect_key',
			'deps'      => array(
				'target-id' => 'woocommerce_yith-stripe_settings\\[test_secrect_key\\]',
				'id'        => 'woocommerce_yith-stripe_settings\\[enabled\\]',
				'value'     => 'yes',
			),
		),
		'api-test-publishable-key'                    => array(
			'id'        => 'woocommerce_yith-stripe_settings[test_publishable_key]',
			'name'      => __( 'Test publishable key', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			// translators: %s is the path to the stripe webhook configuration panel.
			'desc'      => sprintf( __( 'Set the publishable API key for the test mode. You can find it in <a href="%s" target="_blank">your Stripe dashboard</a>.', 'yith-woocommerce-stripe' ), $stripe_keys ),
			'default'   => '',
			'class'     => 'woocommerce_yith-stripe_test_publishable_key',
			'deps'      => array(
				'target-id' => 'woocommerce_yith-stripe_settings\\[test_publishable_key\\]',
				'id'        => 'woocommerce_yith-stripe_settings\\[enabled\\]',
				'value'     => 'yes',
			),
		),
		'api-live-secret-key'                         => array(
			'id'        => 'woocommerce_yith-stripe_settings[live_secrect_key]',
			'name'      => __( 'Live secret key', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			// translators: %s is the path to the stripe webhook configuration panel.
			'desc'      => sprintf( __( 'Set the secret API key for the live mode. You can find it in <a href="%s" target="_blank">your Stripe dashboard</a>.', 'yith-woocommerce-stripe' ), $stripe_keys ),
			'default'   => '',
			'class'     => 'woocommerce_yith-stripe_live_secrect_key',
			'deps'      => array(
				'target-id' => 'woocommerce_yith-stripe_settings\\[live_secrect_key\\]',
				'id'        => 'woocommerce_yith-stripe_settings\\[enabled\\]',
				'value'     => 'yes',
			),
		),
		'api-live-publishable-key'                    => array(
			'id'        => 'woocommerce_yith-stripe_settings[live_publishable_key]',
			'name'      => __( 'Live publishable key', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			// translators: %s is the path to the stripe webhook configuration panel.
			'desc'      => sprintf( __( 'Set the publishable API key for the live mode. You can find it in <a href="%s" target="_blank">your Stripe dashboard</a>.', 'yith-woocommerce-stripe' ), $stripe_keys ),
			'default'   => '',
			'class'     => 'woocommerce_yith-stripe_live_publishable_key',
			'deps'      => array(
				'target-id' => 'woocommerce_yith-stripe_settings\\[live_publishable_key\\]',
				'id'        => 'woocommerce_yith-stripe_settings\\[enabled\\]',
				'value'     => 'yes',
			),
		),
		'general-options-capture'                     => array(
			'id'        => 'woocommerce_yith-stripe_settings[capture]',
			'name'      => __( 'Capture', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => array(
				'no'  => __( 'Authorize only & Capture later', 'yith-woocommerce-stripe' ),
				'yes' => __( 'Authorize & Capture immediately', 'yith-woocommerce-stripe' ),
			),
			// translators: %s is the url of the Stripe doc.
			'desc'      => sprintf( __( 'Decide whether to capture the charge immediately or not. When "Authorize only & Capture later" is selected, the charge issues an authorization (or pre-authorization) and it will be captured later. <br>Uncaptured charges expire in 7 days.<br>For further information, <a href="%s" target="_blank">see authorizing charges and settling later.</a>', 'yith-woocommerce-stripe' ), $doc_url ),
			'default'   => 'no',
			'class'     => 'woocommerce_yith-stripe_capture wc-enhanced-select',
			'deps'      => array(
				'target-id' => 'woocommerce_yith-stripe_settings\\[capture\\]',
				'id'        => 'woocommerce_yith-stripe_settings\\[enabled\\]',
				'value'     => 'yes',
			),
		),
		'general-options-payment'                     => array(
			'id'        => 'woocommerce_yith-stripe_settings[mode]',
			'name'      => __( 'Payment mode', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => array(
				'standard' => __( 'Standard', 'yith-woocommerce-stripe' ),
				'hosted'   => __( 'Stripe Checkout', 'yith-woocommerce-stripe' ),
				'elements' => __( 'Stripe Elements', 'yith-woocommerce-stripe' ),
			),
			'desc'      => __( '<strong>Standard</strong> will display credit card fields on your store (SSL required).<br><strong>Stripe Checkout</strong> will redirect the user to the checkout page hosted in Stripe.<br><strong>Elements</strong> will show an embedded form handled by Stripe.', 'yith-woocommerce-stripe' ),
			'default'   => 'elements',
			'class'     => 'woocommerce_yith-stripe_mode wc-enhanced-select',
		),
		'general-options-save-cards'                  => array(
			'id'        => 'woocommerce_yith-stripe_settings[save_cards]',
			'name'      => __( 'Save cards', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable to save users\' credit cards so they can use them for future payments.', 'yith-woocommerce-stripe' ),
			'default'   => 'yes',
			'class'     => 'woocommerce_yith-stripe_save_cards',
		),
		'general-options-card-mode'                   => array(
			'id'        => 'woocommerce_yith-stripe_settings[save_cards_mode]',
			'name'      => __( 'Card registration mode', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'options'   => array(
				'register' => __( 'Register automatically', 'yith-woocommerce-stripe' ),
				'prompt'   => __( 'Let user choose', 'yith-woocommerce-stripe' ),
			),
			'desc'      => __( 'If you choose to automatically register cards, every card used by the customer will be registered automatically.<br>Otherwise, the system will register cards only when the customer marks the "Save card" checkbox.<br>Please note that this option does not affect Stripe, which registers cards for internal processing anyway.', 'yith-woocommerce-stripe' ),
			'default'   => 'register',
			'class'     => 'woocommerce_yith-stripe_save_cards_mode wc-enhanced-select',
		),
		'general-options-custom-payment-method-style' => array(
			'id'        => 'woocommerce_yith-stripe_settings[custom_payment_method_style]',
			'name'      => __( 'Apply custom style to Payment Methods', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable to apply the plugin\'s custom style to the Payment Method\'s table in My Account.', 'yith-woocommerce-stripe' ),
			'default'   => 'no',
			'class'     => 'woocommerce_yith-stripe_custom_payment_method_style',
		),
		'general-options-chk-bill'                    => array(
			'id'        => 'woocommerce_yith-stripe_settings[add_billing_hosted_fields]',
			'name'      => __( 'Add billing fields for Stripe Checkout', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Option available only for "Stripe Checkout" payment mode.', 'yith-woocommerce-stripe' ),
			'default'   => 'no',
			'class'     => 'woocommerce_yith-stripe_add_billing_hosted_fields',
		),
		'general-options-chk-ship'                    => array(
			'id'        => 'woocommerce_yith-stripe_settings[add_shipping_hosted_fields]',
			'name'      => __( 'Add shipping fields for Stripe Checkout', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Option available only for "Stripe Checkout" payment mode.', 'yith-woocommerce-stripe' ),
			'default'   => 'no',
			'class'     => 'woocommerce_yith-stripe_add_shipping_hosted_fields',
		),
		'general-options-stand-bill'                  => array(
			'id'        => 'woocommerce_yith-stripe_settings[add_billing_fields]',
			'name'      => __( 'Add billing fields', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'If you have installed any WooCommerce extension to edit checkout fields, this option allows you to require some necessary information associated with the credit card, to further reduce the risk of fraudulent transactions.', 'yith-woocommerce-stripe' ),
			'default'   => 'no',
			'class'     => 'woocommerce_yith-stripe_add_billing_fields',
		),
		'general-options-show-name'                   => array(
			'id'        => 'woocommerce_yith-stripe_settings[show_name_on_card]',
			'name'      => __( 'Show \'Name on card\' field', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Show a \'Name on card\' field in Elements and Standard mode; the name will be sent within card data to let Stripe perform additional checks over the user and better evaluate risk.', 'yith-woocommerce-stripe' ),
			'default'   => 'yes',
			'class'     => 'woocommerce_yith-stripe_show_name_on_card',
		),
		'general-options-show-zip'                    => array(
			'id'        => 'woocommerce_yith-stripe_settings[elements_show_zip]',
			'name'      => __( 'Show \'ZIP\' field', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Show a \'ZIP\' field in Elements mode; the ZIP code will be sent within card data to let Stripe perform additional checks over the user and better evaluate risk.', 'yith-woocommerce-stripe' ),
			'default'   => 'yes',
			'class'     => 'woocommerce_yith-stripe_elements_show_zip',
		),
		'general-options-end'                         => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcstripe_options',
		),
		'customization-options'                       => array(
			'title' => __( 'Customization', 'yith-woocommerce-stripe' ),
			'type'  => 'title',
			'id'    => 'yith_wcstripe_customization',
		),
		'general-options-title'                       => array(
			'id'        => 'woocommerce_yith-stripe_settings[title]',
			'name'      => __( 'Title', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => __( 'Enter the title that the user sees during the checkout process.', 'yith-woocommerce-stripe' ),
			'default'   => __( 'Credit Card', 'yith-woocommerce-stripe' ),
		),
		'general-options-description'                 => array(
			'id'        => 'woocommerce_yith-stripe_settings[description]',
			'name'      => __( 'Description', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => __( 'Enter the description that the user sees during the checkout process.', 'yith-woocommerce-stripe' ),
			'default'   => __( 'Pay with a credit card.', 'yith-woocommerce-stripe' ),
		),
		'customization-label'                         => array(
			'id'        => 'woocommerce_yith-stripe_settings[button_label]',
			'name'      => __( 'Button label', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => __( 'Enter the label for the button on checkout.', 'yith-woocommerce-stripe' ),
			'default'   => __( 'Place order', 'yith-woocommerce-stripe' ),
		),
		'customization-options-end'                   => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcstripe_customization',
		),
		'security-options'                            => array(
			'title' => __( 'Security', 'yith-woocommerce-stripe' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith_wcstripe_security',
		),
		'security-blacklist'                          => array(
			'id'        => 'woocommerce_yith-stripe_settings[enable_blacklist]',
			'class'     => 'woocommerce_yith-stripe-enable-blacklist',
			'name'      => __( 'Enable Blacklist', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Hide payment gateway on frontend if the same user or the same IP address already has a failed payment. The blacklist table is available on <strong>YITH -> Stripe -> Blacklist</strong>.', 'yith-woocommerce-stripe' ),
			'default'   => 'no',
		),
		'security-options-end'                        => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcstripe_security',
		),
		'testing-options'                             => array(
			'title' => __( 'Testing & Debug', 'yith-woocommerce-stripe' ),
			'type'  => 'title',
			'desc'  => __( 'Enable here the testing mode to debug the payment system before going into production.', 'yith-woocommerce-stripe' ),
			'id'    => 'yith_wcstripe_security',
		),
		'testing-enable'                              => array(
			'id'        => 'woocommerce_yith-stripe_settings[enabled_test_mode]',
			'name'      => __( 'Enable test mode', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable this option if you want to test the gateway before going into production.', 'yith-woocommerce-stripe' ),
			'default'   => 'yes',
		),
		'testing-debug'                               => array(
			'id'        => 'woocommerce_yith-stripe_settings[debug]',
			'name'      => __( 'Debug Log', 'yith-woocommerce-stripe' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			// translators: %s is the path to the stripe log and the second one is the URL to the logs inside the Stripe web.
			'desc'      => sprintf( __( 'Log Stripe events inside: <div style="background: #f1f1f1;font-weight:500;color:black;">%1$s</div>You can also consult the logs in your <a href="%2$s" target="_blank">Logs Dashboard</a>, without checking this option.', 'yith-woocommerce-stripe' ), WC_Log_Handler_File::get_log_file_path( 'stripe' ), $debug_url ),
			'default'   => 'no',
		),
		'testing-options-end'                         => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcstripe_security',
		),
		'webhook-options'                             => array(
			'title' => __( 'Webhooks', 'yith-woocommerce-stripe' ),
			'type'  => 'title',
			// translators: %1$s is the webhook URL. %2$s is the path to the stripe webhook configuration panel.
			'desc'  => sprintf( __( 'You can configure the webhook URL %1$s in your <a href="%2$s" target="_blank">developers settings</a>. All the webhooks for your account will be sent to this endpoint.', 'yith-woocommerce-stripe' ), '<code>' . esc_url( add_query_arg( 'wc-api', 'stripe_webhook', site_url( '/' ) ) ) . '</code>', 'https://dashboard.stripe.com/account/webhooks' ),
			'id'    => 'yith_wcstripe_webhooks',
		),
		'webhoks-button'                              => array(
			'id'        => 'config_webhook',
			'type'      => 'yith-field',
			'yith-type' => 'custom',
			'action'    => 'yith_stripe_webhook_section',
		),
		'webhook-options-end'                         => array(
			'type' => 'sectionend',
			'id'   => 'yith_wcstripe_webhooks',
		),
	),
);

return apply_filters( 'yith_wcstripe_general_settings', $general_options );
