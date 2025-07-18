<?php

class Ld_Dashboard_Meeting_Embed {

	/**
	 * Contain the instance of the plugin
	 *
	 * @since    5.9.9
	 * @access   private
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * plugin name
	 *
	 * @since    5.9.9
	 * @access   private
	 *
	 * @var string
	 */
	private $plugin_name = 'ld-dashboard';


	public function __construct() {
		add_shortcode( 'ld_dashboard_meeting_embed', array( $this, 'ld_dashboard_meeting_embed_callback' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'ld_dashboard_enueue_embed_shortcode_script' ) );
	}

	public function ld_dashboard_enueue_embed_shortcode_script() {
		wp_register_script( $this->plugin_name . '-moment', LD_DASHBOARD_PLUGIN_URL . '/public/js/moment/moment-min.js', array( 'jquery' ), LD_DASHBOARD_VERSION, false );
		wp_register_script( $this->plugin_name . '-moment-timezone', LD_DASHBOARD_PLUGIN_URL . '/public/js/moment/moment-timezone-with-data.min.js', array( 'jquery' ), LD_DASHBOARD_VERSION, false );
		// wp_register_script( $this->plugin_name . '-moment-locals', LD_DASHBOARD_PLUGIN_URL . '/public/js/moment/moment-with-locales-min.js', array( 'jquery', $this->plugin_name . '-moment' ), LD_DASHBOARD_VERSION, false );
		wp_register_script( $this->plugin_name . '-embed-meeting', LD_DASHBOARD_PLUGIN_URL . '/public/js/ld-dashboard-shortcode-embed.js', array( $this->plugin_name . '-moment', $this->plugin_name . '-moment-timezone' ), LD_DASHBOARD_VERSION, false );

	}


	public static function getInstance(): Ld_Dashboard_Meeting_Embed {
		$cls = static::class;
		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new static();
		}

		return self::$instances[ $cls ];
	}

	public function ld_dashboard_meeting_embed_callback( $atts ) {
		$atts   = shortcode_atts(
			array(
				'meeting_id'        => '',
				'height'            => '500px',
				'iframe'            => 'yes',
				'disable_countdown' => 'no',
			),
			$atts,
			'ld_dashboard_meeting_embed'
		);
		$output = '';
		if ( empty( $atts['meeting_id'] ) ) {
			$output = __( 'Meeting id require. Please enter meeting id.', 'ld-dashboard' );
		}

		$zoom          = new Zoom_Api();
		$meeting_id    = get_post_meta( $atts['meeting_id'], 'zoom_meeting_id', true );
		$meeting       = '';
		$settings      = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
		$zoom_settings = $settings['zoom_meeting_settings'];

		if ( isset( $zoom_settings['use-admin-account'] ) && 0 === $zoom_settings['use-admin-account'] ) {
			$instructor = get_post_field( 'post_author', $atts['meeting_id'] );
			$meeting    = $zoom->get_instructor_meeting( $instructor, $meeting_id );
		} else {
			$meeting = $zoom->get_meeting_info( $meeting_id );
		}

		if ( isset( $meeting->code ) && ( 124 === $meeting->code || 3001 === $meeting->code ) ) {
			$output = $meeting->message;
			return $output;
		}

		wp_enqueue_script( $this->plugin_name . '-moment' );
		wp_enqueue_script( $this->plugin_name . '-moment-timezone' );
		wp_enqueue_script( $this->plugin_name . '-embed-meeting' );
		$meeting->mobile_zoom_url       = 'https://zoom.us/j/' . $meeting_id;
		$start_time                     = ! empty( $meeting->start_time ) ? $meeting->start_time : 'now';
		$meeting_time                   = date( 'Y-m-d h:i a', strtotime( $start_time ) );
		$meeting->meeting_timezone_time = ld_dashboard_date_converter( 'now', $meeting->timezone, false );
		$meeting->meeting_time_check    = ld_dashboard_date_converter( $meeting_time, $meeting->timezone, false );
		$meeting->shortcode_attributes  = $atts;

		$GLOBALS['ldd_meeting'] = $meeting;

		ob_start();
		if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-embed-meeting-content.php' ) ) {
			include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-embed-meeting-content.php' );
		} else {
			include LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-embed-meeting-content.php';
		}

		$output = ob_get_contents();
		ob_clean();
		return $output;
	}





}


Ld_Dashboard_Meeting_Embed::getInstance();
