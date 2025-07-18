<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Expiring card reminder email
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCStripe_Expiring_Card_Email' ) ) {

	/**
	 * Expiring card reminder email
	 *
	 * @since 1.0.0
	 */
	class YITH_WCStripe_Expiring_Card_Email extends YITH_Stripe_Email {

		/**
		 * Receiver id
		 *
		 * @var $user_id int
		 */
		public $user_id = false;

		/**
		 * Expiring card
		 *
		 * @var \WC_Payment_Token_CC
		 */
		public $token = null;


		/**
		 * Days before expiration
		 *
		 * @var $days_before_expiration int
		 */
		public $days_before_expiration;

		/**
		 * Subscribed only
		 *
		 * @var $subscribed_only bool
		 */
		public $subscribed_only;

		/**
		 * Exclusions
		 *
		 * @var $exclusions array
		 */
		public $exclusions;

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.8.1
		 */
		public function __construct() {
			$this->id             = 'expiring_card';
			$this->title          = 'YITH WooCommerce Stripe - ' . __( 'Customer\'s expiring card reminder', 'yith-woocommerce-stripe' );
			$this->description    = __( 'This email is sent to customers that have at least one expiring card in the related period.', 'yith-woocommerce-stripe' );
			$this->customer_email = true;

			$this->heading = __( 'Update your card information', 'yith-woocommerce-stripe' );
			$this->subject = __( 'Update your card information', 'yith-woocommerce-stripe' );

			$this->days_before_expiration = $this->get_option( 'days_before_expiration' );
			$this->subscribed_only        = defined( 'YITH_YWSBS_VERSION' ) ? 'yes' === $this->get_option( 'days_before_expiration' ) : false;
			$this->exclusions             = explode( ',', $this->get_option( 'exclusions' ) );

			$this->template_html  = 'emails/expiring-card-email.php';
			$this->template_plain = 'emails/plain/expiring-card-email.php';

			// Triggers for this email.
			add_action( 'yith_wcstripe_expiring_card_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger email sending
		 *
		 * @param int                 $user_id        Id of the owner of expiring card.
		 * @param WC_Payment_Token_CC $expiring_token Expiring card.
		 *
		 * @return void
		 */
		public function trigger( $user_id, $expiring_token ) {
			$this->user_id = $user_id;
			$this->token   = $expiring_token;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			/**
			 * DO_ACTION: yith_wcstripe_before_send_expiring_card_notification
			 *
			 * Triggered before sending expiring card notification.
			 *
			 * @param YITH_WCStripe_Expiring_Card_Email $this YITH_WCStripe_Expiring_Card_Email class.
			 */
			do_action( 'yith_wcstripe_before_send_expiring_card_notification', $this );

			$this->setup_locale();

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			$this->restore_locale();

			/**
			 * DO_ACTION: yith_wcstripe_after_send_expiring_card_notification
			 *
			 * Triggered after sending expiring card notification.
			 *
			 * @param YITH_WCStripe_Expiring_Card_Email $this YITH_WCStripe_Expiring_Card_Email class.
			 */
			do_action( 'yith_wcstripe_after_send_expiring_card_notification', $this );
		}

		/**
		 * Get valid recipients.
		 *
		 * @return string
		 */
		public function get_recipient() {
			$user = get_user_by( 'id', $this->user_id );

			if ( ! $user ) {
				return false;
			}

			$this->recipient = $user->user_email;

			return parent::get_recipient();
		}

		/**
		 * Get the email content in HTML format.
		 *
		 * @return string
		 */
		public function get_content_html() {
			$expiration_date      = gmdate( 'Y-m-t', strtotime( "{$this->get_expiry_year( $this->token )}-{$this->get_expiry_month( $this->token )}-01" ) );
			$expiration_timestamp = strtotime( $expiration_date );
			$userdata             = get_userdata( $this->user_id );
			$bg_color             = get_option( 'woocommerce_email_base_color' );

			return $this->format_string(
				wc_get_template_html(
					$this->template_html,
					array(
						'email'                => $this,
						'username'             => $this->get_username( $userdata ),
						'card_type'            => ucfirst( $this->get_card_type( $this->token ) ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_update_card_url
						 *
						 * Filters the URL to get to update the card.
						 *
						 * @param string The URL. Default value is the payment methods endpoint in my account page.
						 *
						 * @return string
						 */
						'update_card_url'      => apply_filters( 'yith_wcstripe_update_card_url', wc_get_endpoint_url( 'payment-methods', '', wc_get_page_permalink( 'myaccount' ) ) ),
						'update_card_bg'       => $bg_color,
						'update_card_fg'       => wc_light_or_dark( $bg_color, '#202020', '#ffffff' ),
						'last4'                => $this->get_last4( $this->token ),
						'already_expired'      => $expiration_timestamp < time(),
						'expiration_timestamp' => $expiration_timestamp,
						'expiration_date'      => date_i18n( wc_date_format(), $expiration_timestamp ),
						'site_title'           => get_option( 'blogname' ),
						'email_heading'        => $this->get_heading(),
						'sent_to_admin'        => false,
						'plain_text'           => false,
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
			$expiration_date      = gmdate( 'Y-m-t', strtotime( "{$this->get_expiry_year( $this->token )}-{$this->get_expiry_month( $this->token )}-01" ) );
			$expiration_timestamp = strtotime( $expiration_date );
			$userdata             = get_userdata( $this->user_id );

			return $this->format_string(
				wc_get_template_html(
					$this->template_plain,
					array(
						'email'                => $this,
						'username'             => $this->get_username( $userdata ),
						'card_type'            => ucfirst( $this->get_card_type( $this->token ) ),
						/**
						 * APPLY_FILTERS: yith_wcstripe_update_card_url
						 *
						 * Filters the URL to get to update the card.
						 *
						 * @param string The URL. Default value is the payment methods endpoint in my account page.
						 *
						 * @return string
						 */
						'update_card_url'      => apply_filters( 'yith_wcstripe_update_card_url', wc_get_endpoint_url( 'payment-methods', '', wc_get_page_permalink( 'myaccount' ) ) ),
						'last4'                => $this->get_last4( $this->token ),
						'already_expired'      => $expiration_timestamp < time(),
						'expiration_timestamp' => $expiration_timestamp,
						'expiration_date'      => date_i18n( wc_date_format(), strtotime( $expiration_date ) ),
						'site_title'           => get_option( 'blogname' ),
						'email_heading'        => $this->get_heading(),
						'sent_to_admin'        => false,
						'plain_text'           => true,
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
					'enabled'                  => array(
						'title'   => __( 'Enable/Disable', 'woocommerce' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable this email notification', 'woocommerce' ),
						'default' => 'no',
					),
					'subject'                  => array(
						'title'       => __( 'Subject', 'woocommerce' ),
						'type'        => 'text',
						// translators: 1. Default subject for the email.
						'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
						'placeholder' => '',
						'default'     => '',
					),
					'heading'                  => array(
						'title'       => __( 'Email heading', 'woocommerce' ),
						'type'        => 'text',
						// translators: 1. Default heading for the email.
						'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
						'placeholder' => '',
						'default'     => '',
					),
					'months_before_expiration' => array(
						'title'       => __( 'Months before expiration', 'yith-woocommerce-stripe' ),
						'type'        => 'number',
						'description' => __( 'This controls how many months before the card\'s expiration date the reminder should be sent.', 'yith-woocommerce-stripe' ),
						'placeholder' => '',
						'default'     => 1,
					),
				),
				defined( 'YITH_YWSBS_VERSION' ) ? array(
					'subscribed_only' => array(
						'title'   => __( 'Subscribed users only', 'yith-woocommerce-stripe' ),
						'type'    => 'checkbox',
						'label'   => __( 'Send this notification only to customers that have at least one active subscription', 'yith-woocommerce-stripe' ),
						'default' => 'no',
					),
				) : array(),
				array(
					'exclusions' => array(
						'title'       => __( 'Exclusions', 'woocommerce' ),
						'type'        => 'textarea',
						'description' => __( 'Enter a list of email addresses, separated by a comma, that should not receive this notification.', 'yith-woocommerce-stripe' ),
						'placeholder' => '',
						'default'     => '',
					),
					'email_type' => array(
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

return new YITH_WCStripe_Expiring_Card_Email();
