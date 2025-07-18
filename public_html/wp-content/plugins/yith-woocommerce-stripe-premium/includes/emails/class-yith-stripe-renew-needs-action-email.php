<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Renew needs action email
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Renew_Needs_Action_Email' ) ) {

	/**
	 * Renew needs action email
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Renew_Needs_Action_Email extends YITH_Stripe_Email {

		/**
		 * Failed renew order
		 *
		 * @var $order_id \WC_Order
		 */
		public $order;

		/**
		 * Token that will be used as default for renew
		 *
		 * @var $token \WC_Payment_Token_CC
		 */
		public $token;

		/**
		 * HTML heading
		 *
		 * @var $heading_html string
		 */
		public $heading_html;

		/**
		 * Plain heading
		 *
		 * @var $heading_plain string
		 */
		public $heading_plain;

		/**
		 * HTML footer
		 *
		 * @var $footer_html string
		 */
		public $footer_html;

		/**
		 * Plain footer
		 *
		 * @var $footer_plain string
		 */
		public $footer_plain;

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.10.0
		 */
		public function __construct() {
			$this->id             = 'renew_needs_action';
			$this->title          = 'YITH WooCommerce Stripe - ' . __( 'Payment pending confirmation email', 'yith-woocommerce-stripe' );
			$this->description    = __( 'This email is sent to customers that have a pending payment awaiting their confirmation.', 'yith-woocommerce-stripe' );
			$this->customer_email = true;

			$this->heading = __( 'Confirm your {order_total} payment', 'yith-woocommerce-stripe' );
			$this->subject = __( 'Confirm your {order_total} payment', 'yith-woocommerce-stripe' );

			$this->heading_html  = $this->get_option(
				'heading_html',
				"
<p>
	Please, confirm your payment to <a href='{site_url}'>{site_title}</a>. Your bank requires this security measure for your {card_type} card ending in {card_last4}
</p>"
			);
			$this->heading_plain = $this->get_option( 'heading_plain', 'Please, confirm your payment to {site_title}. Your bank requires this security measure for your {card_type} card ending in {card_last4}.' );
			$this->footer_html   = $this->get_option(
				'footer_html',
				"
<h3>Why do you need to confirm this payment?</h3>
<p>
	Your bank sometimes requires an additional step to make sure an online transaction was authorized. 
	Your bank uses 3D Secure to set a higher security standard and protect you from fraud.
</p>
<p>
Due to European regulations to protect consumers, many online payments now require two-factor authentication.
Your bank ultimately decides when authentication is required to confirm a payment, but you may notice this step when 
you start paying for a service or when the cost changes.
</p>
<p>
	<small>If you have any question, contact us at <a href='mailto:{contact_email}'>{contact_email}</a></small>
</p>"
			);
			$this->footer_plain  = $this->get_option(
				'footer_plain',
				"
=== Why to you need to confirm this payment? ===\n\n
Your bank sometimes requires an additional step to make sure an online transaction was authorized. 
Your bank uses 3D Secure to set a higher security standard and protect you from fraud.\n\n
Due to European regulations to protect consumers, many online payments now require two-factor authentication.
Your bank ultimately decides when authentication is required to confirm a payment, but you may notice this step 
when you start paying for a service or when the cost changes.\n\n
If you have any questions, please contact us at {contact_email}\n\n"
			);

			$this->template_html  = 'emails/renew-needs-action-email.php';
			$this->template_plain = 'emails/plain/renew-needs-action-email.php';

			// Triggers for this email.
			add_action( 'yith_wcstripe_renew_intent_requires_action_notification', array( $this, 'trigger' ), 10, 1 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger email sending
		 *
		 * @param int $order_id Id of the renew order.
		 *
		 * @return void
		 */
		public function trigger( $order_id ) {
			$this->order = wc_get_order( $order_id );
			$user_id     = $this->order ? $this->order->get_user_id() : false;

			if ( $this->order && $user_id ) {
				$found  = false;
				$tokens = WC_Payment_Tokens::get_tokens(
					array(
						'user_id'    => $user_id,
						'gateway_id' => YITH_WCStripe::$gateway_id,
					)
				);

				if ( ! empty( $tokens ) ) {
					foreach ( $tokens as $token ) {
						if ( $token->is_default() ) {
							$found = true;
							break;
						}
					}
				}

				if ( $found ) {
					$this->token = $token;
				}
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() || ! $this->order || ! $this->token ) {
				return;
			}

			$content = $this->get_content();

			$this->send( $this->get_recipient(), $this->get_subject(), $content, $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Get valid recipients.
		 *
		 * @return string
		 */
		public function get_recipient() {
			$this->recipient = $this->order ? $this->order->get_billing_email() : false;

			return parent::get_recipient();
		}

		/**
		 * Get the email content in HTML format.
		 *
		 * @return string
		 */
		public function get_content_html() {
			$order    = $this->get_order();
			$userdata = $order->get_user();
			$username = $this->get_username( $userdata );
			$bg_color = get_option( 'woocommerce_email_base_color' );
			$pay_url  = $order->needs_payment() ? $order->get_checkout_payment_url() : $order->get_view_order_url();

			/**
			 * APPLY_FILTERS: yith_wcstripe_pay_renew_url_enabled
			 *
			 * Filters if pay renew URL is enabled.
			 *
			 * @param bool Default value comming from if YITH Subscriptions is enabled and has a minimum version of 1.6.1.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcstripe_pay_renew_url_enabled', defined( 'YITH_YWSBS_VERSION' ) && version_compare( YITH_YWSBS_VERSION, '1.6.1', '>=' ) ) ) {
				/**
				 * APPLY_FILTERS: yith_wcstripe_pay_renew_url
				 *
				 * Filters subscription pay renew URL.
				 *
				 * @param string Default URL comming from order checkout payment URL.
				 *
				 * @return string
				 */
				$pay_url = apply_filters( 'yith_wcstripe_pay_renew_url', wp_nonce_url( $order->get_checkout_payment_url(), 'ywsbs_manual_renew', 'ywsbs_manual_renew' ) );
			}

			$this->placeholders = array_merge(
				$this->placeholders,
				array(
					'{site_url}'      => get_home_url(),
					'{username}'      => $username,
					'{card_type}'     => ucfirst( $this->get_card_type( $this->token ) ),
					'{pay_renew_url}' => $pay_url,
					'{card_last4}'    => $this->get_last4( $this->token ),
					'{order_id}'      => $order->get_id(),
					'{order_total}'   => wp_strip_all_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ),
					'{billing_email}' => $order->get_billing_email(),
					/**
					 * APPLY_FILTERS: yith_wcstripe_contact_email
					 *
					 * Filters 'renew needs action' email contact email placeholder.
					 *
					 * @param string The contact email. Default value: WooCommerce 'from' email address from settings.
					 *
					 * @return string
					 */
					'{contact_email}' => apply_filters( 'yith_wcstripe_contact_email', get_option( 'woocommerce_email_from_address', '' ) ),
				)
			);

			$this->placeholders = array_merge(
				$this->placeholders,
				array(
					'{opening_text}' => $this->format_string( $this->heading_html ),
					'{closing_text}' => $this->format_string( $this->footer_html ),
				)
			);

			return $this->format_string(
				wc_get_template_html(
					$this->template_html,
					array(
						'order'         => $order,
						'username'      => $username,
						'pay_renew_url' => $pay_url,
						'pay_renew_bg'  => $bg_color,
						'pay_renew_fg'  => wc_light_or_dark( $bg_color, '#202020', '#ffffff' ),
						'email_heading' => $this->get_heading(),
						'sent_to_admin' => false,
						'plain_text'    => false,
						'email'         => $this,
					),
					WC()->template_path() . 'yith-wcstripe/',
					YITH_WCSTRIPE_DIR . 'templates/'
				)
			);
		}

		/**
		 * Get the email content in plain text format.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			$order    = $this->get_order();
			$userdata = $order->get_user();
			$username = $this->get_username( $userdata );
			$pay_url  = $order->needs_payment() ? $order->get_checkout_payment_url() : $order->get_view_order_url();

			/**
			 * APPLY_FILTERS: yith_wcstripe_pay_renew_url_enabled
			 *
			 * Filters if pay renew URL is enabled.
			 *
			 * @param bool Default value comming from if YITH Subscriptions is enabled and has a minimum version of 1.6.1.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcstripe_pay_renew_url_enabled', defined( 'YITH_YWSBS_VERSION' ) && version_compare( YITH_YWSBS_VERSION, '1.6.1', '>=' ) ) ) {
				/**
				 * APPLY_FILTERS: yith_wcstripe_pay_renew_url
				 *
				 * Filters subscription pay renew URL.
				 *
				 * @param string Default URL comming from order checkout payment URL.
				 *
				 * @return string
				 */
				$pay_url = apply_filters( 'yith_wcstripe_pay_renew_url', wp_nonce_url( $order->get_checkout_payment_url(), 'ywsbs_manual_renew', 'ywsbs_manual_renew' ) );
			}

			$this->placeholders = array_merge(
				$this->placeholders,
				array(
					'{site_url}'      => get_home_url(),
					'{username}'      => $username,
					'{card_type}'     => ucfirst( $this->get_card_type( $this->token ) ),
					'{pay_renew_url}' => $pay_url,
					'{card_last4}'    => $this->get_last4( $this->token ),
					'{order_id}'      => $order->get_id(),
					'{order_total}'   => $order->get_total(),
					'{billing_email}' => $order->get_billing_email(),
					/**
					 * APPLY_FILTERS: yith_wcstripe_contact_email
					 *
					 * Filters 'renew needs action' email contact email placeholder.
					 *
					 * @param string The contact email. Default value: WooCommerce 'from' email address from settings.
					 *
					 * @return string
					 */
					'{contact_email}' => apply_filters( 'yith_wcstripe_contact_email', get_option( 'woocommerce_email_from_address', '' ) ),
				)
			);

			$this->placeholders = array_merge(
				$this->placeholders,
				array(
					'{opening_text}' => $this->format_string( $this->heading_plain ),
					'{closing_text}' => $this->format_string( $this->footer_plain ),
				)
			);

			return $this->format_string(
				wc_get_template_html(
					$this->template_plain,
					array(
						'order'         => $order,
						'username'      => $username,
						'pay_renew_url' => $pay_url,
						'email_heading' => $this->get_heading(),
						'sent_to_admin' => false,
						'plain_text'    => true,
						'email'         => $this,
					),
					WC()->template_path() . 'yith-wcstripe/',
					YITH_WCSTRIPE_DIR . 'templates/'
				)
			);
		}

		/**
		 * Init form fields to display in WC admin pages
		 *
		 * @return void
		 * @since 1.8.1
		 */
		public function init_form_fields() {
			$this->form_fields = array_merge(
				array(
					'enabled'       => array(
						'title'   => __( 'Enable/Disable', 'woocommerce' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable this email notification', 'woocommerce' ),
						'default' => 'no',
					),
					'subject'       => array(
						'title'       => __( 'Subject', 'woocommerce' ),
						'type'        => 'text',
						// translators: 1. Default subject for the email.
						'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
						'placeholder' => '',
						'default'     => '',
					),
					'heading'       => array(
						'title'       => __( 'Email heading', 'woocommerce' ),
						'type'        => 'text',
						// translators: 1. Default heading for the email.
						'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
						'placeholder' => '',
						'default'     => '',
					),
					'heading_html'  => array(
						'title'       => __( 'Opening text HTML', 'woocommerce' ),
						'type'        => 'textarea',
						'description' => __( 'Enter the text that you want to show before the CTA button. You can use the following placeholders: <code>{site_title}, {site_url}, {card_type}, {pay_renew_url}, {card_last4}, {order_id}, {order_total}, {billing_email}, {contact_email}, {opening_text}, {closing_text}</code>.', 'woocommerce' ),
						'placeholder' => '',
						'default'     => $this->heading_html,
					),
					'heading_plain' => array(
						'title'       => __( 'Opening text plain', 'woocommerce' ),
						'type'        => 'textarea',
						'description' => __( 'Enter the text that you want to show before the CTA button (plain text version). You can use the following placeholders: <code>{site_title}, {site_url}, {card_type}, {pay_renew_url}, {card_last4}, {order_id}, {order_total}, {billing_email}, {contact_email}, {opening_text}, {closing_text}</code>.', 'woocommerce' ),
						'placeholder' => '',
						'default'     => $this->heading_plain,
					),
					'footer_html'   => array(
						'title'       => __( 'Closing text HTML', 'woocommerce' ),
						'type'        => 'textarea',
						'description' => __( 'Enter the text that you want to show after the order summary table. You can use the following placeholders: <code>{site_title}, {site_url}, {card_type}, {pay_renew_url}, {card_last4}, {order_id}, {order_total}, {billing_email}, {contact_email}, {opening_text}, {closing_text}</code>.', 'woocommerce' ),
						'placeholder' => '',
						'default'     => $this->footer_html,
					),
					'footer_plain'  => array(
						'title'       => __( 'Closing text plain', 'woocommerce' ),
						'type'        => 'textarea',
						'description' => __( 'Enter the text that you want to show after the order summary table (plain text version). You can use the following placeholders: <code>{site_title}, {site_url}, {card_type}, {pay_renew_url}, {card_last4}, {order_id}, {order_total}, {billing_email}, {contact_email}, {opening_text}, {closing_text}</code>.', 'woocommerce' ),
						'placeholder' => '',
						'default'     => $this->footer_plain,
					),
					'email_type'    => array(
						'title'       => __( 'Email type', 'woocommerce' ),
						'type'        => 'select',
						'description' => __( 'Choose email format.', 'woocommerce' ),
						'default'     => 'html',
						'class'       => 'email_type wc-enhanced-select',
						'options'     => $this->get_email_type_options(),
					),
				)
			);
		}
	}
}

return new YITH_WCStripe_Renew_Needs_Action_Email();
