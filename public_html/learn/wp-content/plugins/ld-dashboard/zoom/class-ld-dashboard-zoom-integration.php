<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Zoom_Api {

	/**
	 * Zoom_api_key.
	 *
	 * @var string
	 */
	private $zoom_api_key = '';

	/**
	 * Zoom_api_secret.
	 *
	 * @var string
	 */
	private $zoom_api_secret = '';

	/**
	 * Zoom_email.
	 *
	 * @var string
	 */
	private $zoom_email = '';

	/**
	 * User Id who create a meeting
	 *
	 * @var string
	 */
	private $user_email = '';

	/**
	 * Zoom API Acoount Id.
	 *
	 * @var string
	 */
	private $zoom_account_id = '';

	/**
	 * Zoom_email.
	 *
	 * @var string
	 */
	private $using_admin_credentials = 'no';

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->include();
		$this->set_zoom_credentials();
		$this->hooks();
		$this->init_cron();
	}


	public function include() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'zoom/class-ld-dashboard-zoom-post-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'zoom/class-ld-dashboard-zoom-authentication.php';
	}

	/**
	 * Zoom Integration Hooks
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'save_post_zoom_meet', array( $this, 'update_zoom_meeting_data_on_save' ), 10, 2 );

		add_action( 'wp_ajax_ld_dashboard_create_meeting', array( $this, 'ld_dashboard_create_meeting_callback' ) );
		add_action( 'wp_ajax_ld_dashboard_load_meeting_form', array( $this, 'ld_dashboard_load_meeting_form_callback' ) );
		add_action( 'wp_ajax_ld_dashboard_get_meeting_recordings', array( $this, 'ld_dashboard_get_meeting_recordings_callback' ) );
		add_action( 'wp_ajax_ld_dashboard_delete_meeting', array( $this, 'ld_dashboard_delete_meeting_callback' ) );
		add_action( 'ld_dashboard_fetch_zoom_recordings', array( $this, 'ld_dashboard_fetch_zoom_recordings_callback' ) );
		add_filter( 'archive_template', array( $this, 'ld_dashboard_cpt_archive' ), 20 );
		add_action( 'ldd_before_meeting_content', array( $this, 'ld_dashboard_before_meeting_callback' ) );
		add_action( 'ldd_after_meeting_content', array( $this, 'ld_dashboard_after_meeting_callback' ) );
		add_action( 'wp_ajax_ldd_get_auth', array( $this, 'ld_dasboard_get_sdk_signature' ) );
		add_action( 'wp_ajax_nopriv_ldd_get_auth', array( $this, 'ld_dasboard_get_sdk_signature' ) );

	}

	private function init_cron() {
		if ( ! wp_next_scheduled( 'ld_dashboard_fetch_zoom_recordings' ) ) {
			wp_schedule_event( time(), 'every_six_hours', 'ld_dashboard_fetch_zoom_recordings' );
		}
	}

	private function set_zoom_credentials() {
		$current_user          = wp_get_current_user();
		$this->zoom_api_key    = get_user_meta( $current_user->ID, 'zoom_api_key', true );
		$this->zoom_api_secret = get_user_meta( $current_user->ID, 'zoom_api_secret', true );
		$this->zoom_account_id = get_user_meta( $current_user->ID, 'zoom_account_id', true );
		$this->zoom_account_id = get_user_meta( $current_user->ID, 'zoom_account_id', true );
		$this->zoom_email      = get_user_meta( $current_user->ID, 'zoom_email', true );

		if ( '' === $this->zoom_api_key ) {
			$obj                        = Ld_Dashboard_Functions::instance();
			$ld_dashboard_settings_data = $obj->ld_dashboard_settings_data();
			$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];
			if ( learndash_is_admin_user() || ( isset( $settings['use-admin-account'] ) && 1 == $settings['use-admin-account'] ) ) {
				$this->zoom_api_key            = isset( $settings['zoom-api-key'] ) ? $settings['zoom-api-key'] : '';
				$this->zoom_api_secret         = isset( $settings['zoom-api-secret'] ) ? $settings['zoom-api-secret'] : '';
				$this->zoom_account_id         = isset( $settings['zoom-account-id'] ) ? $settings['zoom-account-id'] : '';
				$this->zoom_email              = isset( $settings['zoom-user-email'] ) ? $settings['zoom-user-email'] : '';
				$this->using_admin_credentials = 'yes';
			}
		}
	}

	public function ld_dashboard_fetch_zoom_recordings_callback() {
		$meetings = get_posts(
			array(
				'post_type'   => 'zoom_meet',
				'post_status' => 'publish',
			)
		);
		if ( is_array( $meetings ) && ! empty( $meetings ) ) {
			foreach ( $meetings as $meeting ) {
				$author_id       = $meeting->post_author;
				$zoom_meeting_id = get_post_meta( $meeting->ID, 'zoom_meeting_id', true );
				$author_data     = get_user_by( 'id', $author_id );
				if ( in_array( 'administrator', $author_data->roles ) ) {
					$obj                        = Ld_Dashboard_Functions::instance();
					$ld_dashboard_settings_data = $obj->ld_dashboard_settings_data();
					$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];
					$this->zoom_api_key         = isset( $settings['zoom-api-key'] ) ? $settings['zoom-api-key'] : '';
					$this->zoom_api_secret      = isset( $settings['zoom-api-secret'] ) ? $settings['zoom-api-secret'] : '';
					$this->zoom_account_id      = isset( $settings['zoom-account-id'] ) ? $settings['zoom-account-id'] : '';
					$this->zoom_email           = isset( $settings['zoom-user-email'] ) ? $settings['zoom-user-email'] : '';
				} elseif ( in_array( 'ld_instructor', $author_data->roles ) ) {
					$this->zoom_api_key    = get_user_meta( $author_data->ID, 'zoom_api_key', true );
					$this->zoom_api_secret = get_user_meta( $author_data->ID, 'zoom_api_secret', true );
					$this->zoom_account_id = get_user_meta( $author_data->ID, 'zoom_account_id', true );
					$this->zoom_email      = get_user_meta( $author_data->ID, 'zoom_email', true );
				}
				$recordings = $this->get_meeting_recordings( $zoom_meeting_id );
				if ( property_exists( $recordings, 'recording_files' ) ) {
					update_post_meta( $meeting->ID, 'ldd_meeting_has_recordings', 'yes' );
					update_post_meta( $meeting->ID, 'ldd_meeting_recordings', $recordings->recording_files );
				} else {
					update_post_meta( $meeting->ID, 'ldd_meeting_has_recordings', 'no' );
					update_post_meta( $meeting->ID, 'ldd_meeting_recordings', '' );
				}
			}
			$this->set_zoom_credentials();
		}
	}

	/**
	 * Update zoom meeting data on save.
	 *
	 * @param  mixed $post_id Meeting Id.
	 * @return void
	 */
	public function update_zoom_meeting_data_on_save( $post_id, $post ) {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			$meeting_id = get_post_meta( $post_id, 'zoom_meeting_id', true );

			if ( isset( $_POST['zoom_details'] ) ) {
				$meeting_data = wp_unslash( $_POST['zoom_details'] );
				update_post_meta( $post_id, 'zoom_details', $meeting_data );
				$data = array();
				if ( is_array( $meeting_data ) ) {
					foreach ( $meeting_data as $key => $value ) {
						if ( 'duration' === $key ) {
							$hr    = isset( $value['hr'] ) ? $value['hr'] * 60 : 0;
							$min   = isset( $value['min'] ) ? $value['min'] : 0;
							$value = ( (int) $value['hr'] * 60 ) + (int) $value['min'];
						}
						if ( 'settings' === $key ) {
							$value = array_map(
								function( $tmp ) {
									if ( is_int( $tmp ) && $tmp == 1 ) {
										$tmp = 'true';
									}
									return $tmp;
								},
								$value
							);
						}
						if ( 'participant_video' === $key && is_int( $value ) && 1 == $value ) {
							$value = true;
						}
						if ( 'mute_upon_entry' === $key && is_int( $value ) && 1 == $value ) {
							$value = true;
						}
						$data[ $key ] = $value;
					}
				}

				$data['topic']  = $post->post_title;
				$data['agenda'] = wp_strip_all_tags( $post->post_content );

				if ( '' !== $meeting_id ) {
					$data['meeting_id'] = $meeting_id;
					$response           = $this->update_meeting( $data );

					if ( empty( $response ) ) {
						$meeting  = $this->get_meeting_info( $data['meeting_id'] );
						$response = $meeting;
					}
				} else {
					$response = $this->create_meeting( $data );
				}

				if ( ! is_wp_error( $response ) ) {
					if ( is_object( $response ) ) {
						if ( property_exists( $response, 'id' ) ) {
							update_post_meta( $post->ID, 'zoom_meeting_id', $response->id );
							update_post_meta( $post->ID, 'zoom_meeting_response', $response );
							update_post_meta( $post->ID, 'using_admin_credentials', $this->using_admin_credentials );
						}
						if ( property_exists( $response, 'start_url' ) ) {
							update_post_meta( $post->ID, 'zoom_meeting_start_url', $response->start_url );
						}
						if ( property_exists( $response, 'join_url' ) ) {
							update_post_meta( $post->ID, 'zoom_meeting_join_url', $response->join_url );
						}
					}
				}
				do_action( 'ld_dashboard_after_save_meeting', $meeting_data );
			}
		}
	}

	public function ld_dashboard_create_meeting_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$data      = array();
		$form_data = wp_unslash( $_POST['formData'] );
		foreach ( $form_data as $key => $value ) {
			if ( '_wp_http_referer' === $key ) {
				continue;
			}
			$new_key          = str_replace( 'zoom_details[', '', $key );
			$data[ $new_key ] = $value;
		}
		$_POST['zoom_details'] = $data;
		unset( $_POST['formData'] );
		if ( isset( $data['post_id'] ) ) {
			$update_meeting = array(
				'ID'           => sanitize_text_field( wp_unslash( $data['post_id'] ) ),
				'post_title'   => $data['topic'],
				'post_content' => $data['agenda'],
			);

			// Insert the post into the database.
			wp_update_post( $update_meeting );
		} else {
			$new_meeting = array(
				'post_title'   => $data['topic'],
				'post_content' => $data['agenda'],
				'post_status'  => 'publish',
				'post_type'    => 'zoom_meet',
				'post_author'  => get_current_user_id(),
			);

			// Insert the post into the database.
			wp_insert_post( $new_meeting );
		}
		exit;
	}

	public function ld_dashboard_load_meeting_form_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;
		if ( $post_id > 0 ) {
			$meeting_data = get_post_meta( $post_id, 'zoom_details', true );
			if ( ! empty( $meeting_data ) ) {
				$meeting_data['topic']  = get_the_title( $post_id );
				$meeting_data['agenda'] = get_post_field( 'post_content', $post_id );
			}
		}
		ob_start();
		include LD_DASHBOARD_PLUGIN_DIR . 'zoom/ld-dashboard-meeting-form.php';
		$response = ob_get_clean();
		echo $response;
		exit;
	}

	public function ld_dashboard_delete_meeting_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$meeting_id = isset( $_POST['meeting'] ) ? sanitize_text_field( wp_unslash( $_POST['meeting'] ) ) : '';
		$post_id    = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;
		if ( '' !== $meeting_id ) {

			wp_delete_post( $post_id );
			$data = array(
				'meeting_id' => $meeting_id,
			);
			$this->delete_meeting( $data );
		}
		exit();
	}

	public function ld_dashboard_get_meeting_recordings_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$meeting_id = isset( $_POST['meeting_id'] ) ? sanitize_text_field( wp_unslash( $_POST['meeting_id'] ) ) : '';
		if ( '' !== $meeting_id ) {
			$recordings = $this->get_meeting_recordings( $meeting_id );
			if ( property_exists( $recordings, 'recording_files' ) ) {
				ob_start();
				echo '<div class="ld-dashboard-meeting-recording-content">';
				foreach ( $recordings->recording_files as $file ) {
					?>
					<div class="ld-dashboard-meeting-recording-single">
						<span><?php echo esc_html( $file->file_extension ); ?></span>
						<a href="<?php echo esc_html( $file->play_url ); ?>" target="_blank"><span class="dashicons dashicons-controls-play"></span></a>
						<a href="<?php echo esc_html( $file->download_url ); ?>"><span class="dashicons dashicons-download"></span></a>
					</div>
					<?php
				}
				echo '</div>';
				$recordings_html = ob_get_clean();
				echo $recordings_html;
			}
		}
		exit();
	}

	/**
	 *  Function to create meeting.
	 *
	 * @param  mixed $data data.
	 */
	public function create_meeting( $data = array() ) {
		// Enter_Your_Email - Zoom Email.
		$request    = array(
			'url'    => 'https://api.zoom.us/v2/users/' . $this->zoom_email . '/meetings',
			'method' => 'POST',
		);
		$post_time  = $data['start_time'];
		$start_time = gmdate( 'Y-m-d\TH:i:s', strtotime( $post_time ) );

		$request_data = array();
		if ( ! empty( $data['alternative_host_ids'] ) ) {
			if ( count( $data['alternative_host_ids'] ) > 1 ) {
				$alternative_host_ids = implode( ',', $data['alternative_host_ids'] );
			} else {
				$alternative_host_ids = $data['alternative_host_ids'][0];
			}
		}

		$request_data['topic']      = $data['topic'];
		$request_data['agenda']     = ! empty( $data['agenda'] ) ? $data['agenda'] : '';
		$request_data['type']       = ! empty( $data['type'] ) ? $data['type'] : 2; // Scheduled.
		$request_data['start_time'] = $start_time;
		$request_data['timezone']   = $data['timezone'];
		$request_data['password']   = ! empty( $data['password'] ) ? $data['password'] : '';
		$request_data['duration']   = ! empty( $data['duration'] ) ? $data['duration'] : 60;

		$request_data['settings'] = array(
			'join_before_host'       => ! empty( $data['settings']['join_before_host'] ) ? true : false,
			'waiting_room'           => ! empty( $data['settings']['waiting_room'] ) ? false : true,
			'host_video'             => ! empty( $data['settings']['host_video'] ) ? true : false,
			'participant_video'      => ! empty( $data['participant_video'] ) ? true : false,
			'mute_upon_entry'        => ! empty( $data['mute_upon_entry'] ) ? true : false,
			'meeting_authentication' => ! empty( $data['settings']['meeting_authentication'] ) ? true : false,
			'auto_recording'         => ! empty( $data['settings']['auto_recording'] ) ? $data['settings']['auto_recording'] : 'none',
			'alternative_hosts'      => isset( $alternative_host_ids ) ? $alternative_host_ids : '',
		);

		return $this->sendRequest( $request_data, $request );
	}

	/**
	 *  Function to create meeting.
	 *
	 * @param  mixed $data data.
	 */
	public function update_meeting( $data = array() ) {

		$request      = array(
			'url'    => 'https://api.zoom.us/v2/meetings/' . $data['meeting_id'],
			'method' => 'PATCH',
		);
		$post_time    = $data['start_time'];
		$start_time   = gmdate( 'Y-m-d\TH:i:s', strtotime( $post_time ) );
		$request_data = array();
		if ( ! empty( $data['alternative_host_ids'] ) ) {
			if ( count( $data['alternative_host_ids'] ) > 1 ) {
				$alternative_host_ids = implode( ',', $data['alternative_host_ids'] );
			} else {
				$alternative_host_ids = $data['alternative_host_ids'][0];
			}
		}

		$request_data['topic']      = $data['topic'];
		$request_data['agenda']     = ! empty( $data['agenda'] ) ? $data['agenda'] : '';
		$request_data['type']       = ! empty( $data['type'] ) ? $data['type'] : 2; // Scheduled.
		$request_data['start_time'] = $start_time;
		$request_data['timezone']   = $data['timezone'];
		$request_data['password']   = ! empty( $data['password'] ) ? $data['password'] : '';
		$request_data['duration']   = ! empty( $data['duration'] ) ? $data['duration'] : 60;

		$request_data['settings'] = array(
			'join_before_host'       => ! empty( $data['settings']['join_before_host'] ) ? true : false,
			'waiting_room'           => ! empty( $data['settings']['waiting_room'] ) ? false : true,
			'host_video'             => ! empty( $data['settings']['host_video'] ) ? true : false,
			'participant_video'      => ! empty( $data['participant_video'] ) ? true : false,
			'mute_upon_entry'        => ! empty( $data['mute_upon_entry'] ) ? true : false,
			'meeting_authentication' => ! empty( $data['settings']['meeting_authentication'] ) ? true : false,
			'auto_recording'         => ! empty( $data['settings']['auto_recording'] ) ? $data['settings']['auto_recording'] : 'none',
			'alternative_hosts'      => isset( $alternative_host_ids ) ? $alternative_host_ids : '',
		);

		return $this->sendRequest( $request_data, $request );
	}

	/**
	 *  Function to delete meeting.
	 *
	 * @param  mixed $data data.
	 */
	public function delete_meeting( $data = array() ) {
		$request = array(
			'url'    => 'https://api.zoom.us/v2/meetings/' . $data['meeting_id'],
			'method' => 'DELETE',
		);
		return $this->sendRequest( array(), $request );
	}

	public function get_all_users( $data = '' ) {
		$request = array(
			'url'    => 'https://api.zoom.us/v2/users',
			'method' => 'GET',
		);
		return $this->sendRequest( $data, $request );
	}

	public function create_user( $user_id ) {
		$user    = get_user_by( 'id', $user_id );
		$data    = array(
			'action'    => 'custCreate',
			'user_info' => array(
				'email'      => $user->data->user_email,
				'first_name' => get_user_meta( $user->ID, 'first_name', true ),
				'last_name'  => get_user_meta( $user->ID, 'last_name', true ),
				'type'       => 1,
			),
		);
		$request = array(
			'url'    => 'https://api.zoom.us/v2/users',
			'method' => 'POST',
		);
		return $this->sendRequest( $data, $request );
	}

	public function delete_user( $user_id ) {
		$user    = get_user_by( 'id', $user_id );
		$request = array(
			'url'    => 'https://api.zoom.us/v2/users/' . $user->data->user_email . '?action=delete',
			'method' => 'DELETE',
		);
		return $this->sendRequest( '', $request );
	}

	public function get_all_meetings( $data = '' ) {
		$request = array(
			'url'    => 'https://api.zoom.us/v2/users/' . $this->zoom_email . '/meetings' . $data,
			'method' => 'GET',
		);
		return $this->sendRequest( $data, $request );
	}

	public function get_meeting( $data ) {
		$request = array(
			'url'    => 'https://api.zoom.us/v2/meetings/' . $data,
			'method' => 'GET',
		);
		return $this->sendRequest( $data, $request );
	}

	public function get_meeting_info( $id ) {
		$request = array(
			'url'    => 'https://api.zoom.us/v2/meetings/' . $id,
			'method' => 'GET',
		);
		return $this->sendRequest( $id, $request );
	}

	public function get_instructor_meeting( $instructor, $meeting_id ) {
		if ( ! empty( $instructor ) ) {
			$instructor_zoom_api_key    = get_user_meta( $instructor, 'zoom_api_key', true );
			$instructor_zoom_api_secret = get_user_meta( $instructor, 'zoom_api_secret', true );
			$instructor_zoom_account_id = get_user_meta( $instructor, 'zoom_account_id', true );
			$instructor_zoom_email      = get_user_meta( $instructor, 'zoom_email', true );
			$credentials                = array(
				'user_id'    => $instructor,
				'api_key'    => $instructor_zoom_api_key,
				'api_secret' => $instructor_zoom_api_secret,
				'account_id' => $instructor_zoom_account_id,
				'zoom_email' => $instructor_zoom_email,
			);

			$request = array(
				'url'    => 'https://api.zoom.us/v2/meetings/' . $meeting_id,
				'method' => 'GET',
			);

			return $this->sendRequest( $meeting_id, $request, $credentials );
		}

	}

	public function get_meeting_recordings( $meeting_id ) {
		$request_data = array();
		$request      = array(
			'url'    => 'https://api.zoom.us/v2/meetings/' . $meeting_id . '/recordings',
			'method' => 'GET',
		);
		return $this->sendRequest( $request_data, $request );
	}

	// Function to send request.
	protected function sendRequest( $data, $request, $credentials = '' ) {

		if ( is_array( $credentials ) && isset( $credentials ) && ! empty( $credentials ) ) {
			$auth = new LD_Dashboard_Zoom_Auth(
				array(
					'user_id'       => $credentials['user_id'],
					'account_id'    => $credentials['account_id'],
					'client_id'     => $credentials['api_key'],
					'client_secret' => $credentials['api_secret'],
				)
			);

		} else {
			$auth = new LD_Dashboard_Zoom_Auth(
				array(
					'user_id'       => get_current_user_id(),
					'account_id'    => $this->zoom_account_id,
					'client_id'     => $this->zoom_api_key,
					'client_secret' => $this->zoom_api_secret,
				)
			);
		}

		if ( is_wp_error( $auth->getAccessToken() ) ) {
			return;
		}

		$headers = array(
			'authorization: Bearer ' . $auth->getAccessToken()->access_token,
			'content-type: application/json',
			'Accept: application/json',
		);

		$post_fields = wp_json_encode( $data );

		$ch = curl_init();
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL            => $request['url'],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => $request['method'],
				CURLOPT_POSTFIELDS     => $post_fields,
				CURLOPT_HTTPHEADER     => $headers,
			)
		);

		$response = curl_exec( $ch );
		$err      = curl_error( $ch );
		curl_close( $ch );
		if ( ! $response ) {
			return $err;
		}
		return json_decode( $response );
	}

	/**
	 * Archive page template
	 *
	 * @param $template
	 *
	 * @return bool|string
	 * @return bool|string|void
	 * @since  6.1.0
	 */
	public function ld_dashboard_cpt_archive( $template ) {
		if ( ! is_post_type_archive( 'zoom_meet' ) ) {
			return $template;
		}

		if ( isset( $_GET['type'] ) && $_GET['type'] === 'meeting' && isset( $_GET['join'] ) ) {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-zoom-meeting-archive-content.php' ) ) {
				$template = Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-zoom-meeting-archive-content.php' );
			} else {
				$template = LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-zoom-meeting-archive-content.php';
			}
		} else {
			if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-zoom-meeting-archive-content.php' ) ) {
				$template = Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-zoom-meeting-archive-content.php' );
			} else {
				$template = LD_DASHBOARD_PLUGIN_DIR . 'templates/ld-dashboard-zoom-meeting-archive-content.php';
			}
		}
		return $template;

	}

	public function ld_dashboard_before_meeting_callback( $ldd_meeting ) {
		ob_start();
		?>
			<!DOCTYPE html><html>
			<head>
				<meta charset="UTF-8">
				<meta name="format-detection" content="telephone=no">
				<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
				<meta name="robots" content="noindex, nofollow">
				<title><?php echo ! empty( $ldd_meeting['api']->topic ) ? $ldd_meeting['api']->topic : 'Join Meeting'; ?></title>
				<link rel='stylesheet' type="text/css"
					href="<?php echo LD_DASHBOARD_PLUGIN_URL . '/public/js/zoom/bootstrap.css?ver=' . LD_DASHBOARD_VERSION; ?>"
					media='all'>
				<link rel='stylesheet' type="text/css"
					href="<?php echo LD_DASHBOARD_PLUGIN_URL . '/public/js/zoom/react-select.css?ver=' . LD_DASHBOARD_VERSION; ?>"
					media='all'>
				<link rel='stylesheet' type="text/css"
					href="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/css/ld-dashboard-public-zoom.css?ver=' . LD_DASHBOARD_VERSION; ?>"
					media='all'>
				<link rel='stylesheet' type="text/css" href="<?php echo get_stylesheet_uri(); ?>" media='all'>
			</head><body class="ld-dashboard-join-meeting">
		<?php
		ob_end_flush();
	}


	public function ld_dashboard_after_meeting_callback() {
		do_action( 'ld_dashboard_join_meeting_footer' );

		ob_start();
		global $post;
		if ( isset( $_GET['redirect'] ) && ! empty( $_GET['redirect'] ) ) {
			$post_link = esc_url( $_GET['redirect'] );
		} elseif ( ! empty( $post ) && ! empty( $post->ID ) ) {
			$post_link = get_permalink( $post->ID );
		} else {
			$post_link = home_url( '/' );
		}

		$localize = array(
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'security'      => wp_create_nonce( '_nonce_ldd_security' ),
			'redirect_page' => apply_filters( 'ld_dashboard_api_redirect_join_browser', esc_url( $post_link ) ),
			'meeting_id'    => base64_encode( ld_dashboard_encrypt_decrypt( 'decrypt', $_GET['join'] ) ),
			'meeting_pwd'   => ! empty( $_GET['pak'] ) ? base64_encode( ld_dashboard_encrypt_decrypt( 'decrypt', $_GET['pak'] ) ) : false,
			'disableInvite' => ( get_option( 'ld_dashboard_disable_invite' ) == 'yes' ) ? true : false,
		);

		/**
		 * Additional Data
		 */
		$additional_data = apply_filters(
			'ld_dashboard_api_join_meeting_params',
			array(
				'meetingInfo'       => array(
					'topic',
					'host',
				),
				'disableRecord'     => false,
				'disableJoinAudio'  => false,
				'isSupportChat'     => true,
				'isSupportQA'       => true,
				'isSupportBreakout' => true,
				'isSupportCC'       => true,
				'screenShare'       => true,
			)
		);

		// localize strings
		$translations = array(
			'browser_info'    => __( 'Browser Info', 'ld-dashboard' ),
			'browser_name'    => __( 'Browser Name', 'ld-dashboard' ),
			'browser_version' => __( 'Browser Version', 'ld-dashboard' ),
			'error_name'      => __( 'Error: Name is Required!', 'ld-dashboard' ),
			'error_email'     => __( 'Error: Email is Required!', 'ld-dashboard' ),
			'error_password'  => __( 'Error: Password is Required!', 'ld-dashboard' ),
		);
		$localize     = array_merge( $localize, $additional_data, $translations );
		?>
			<script id='ld_dashboard-zoom-api-js-extra'>
				var lddzm_ajx = <?php echo wp_json_encode( $localize ); ?>;
			</script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/jquery.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/react.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/react-dom.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/redux.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/redux-thunk.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/lodash.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/zoom/zoom-meeting.min.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<script src="<?php echo LD_DASHBOARD_PLUGIN_URL . 'public/js/ld-dashboard-zoom-meeting.js?ver=' . LD_DASHBOARD_VERSION; ?>"></script>
			<?php do_action( 'vczapi_join_via_browser_after_script_load' ); ?>
			</body>
			</html>
		<?php

		ob_end_flush();
	}


	public function ld_dasboard_get_sdk_signature() {
		check_ajax_referer( '_nonce_ldd_security', 'noncce' );
		$meeting_id = null !== filter_input( INPUT_POST, 'meeting_id' ) ? wp_unslash( filter_input( INPUT_POST, 'meeting_id' ) ) : 0;

		if ( $meeting_id > 0 && ld_dashboard_is_sdk_enabled() ) {
			$settings         = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
			$meeting_settigns = $settings['zoom_meeting_settings'];
			$sdk_key          = $meeting_settigns['sdk-client-id'];
			$secret_key       = $meeting_settigns['sdk-client-secret'];
			$signature        = $this->ld_dashboard_generate_sdk_signature( $sdk_key, $secret_key, $meeting_id, 0 );
			wp_send_json_success(
				array(
					'sig'  => $signature,
					'key'  => $sdk_key,
					'type' => 'sdk',
				)
			);
		} else {
			wp_send_json_error( __( 'Error occured!', 'ld-dashboard' ) );
		}
	}

	private function ld_dashboard_generate_sdk_signature( $sdk_key, $secret_key, $meeting_number, $role ) {
		require_once LD_DASHBOARD_PLUGIN_DIR . 'vendor/php-jwt/src/JWT.php';

		$iat     = round( ( time() * 1000 - 30000 ) / 1000 );
		$exp     = $iat + 86400;
		$payload = array(
			'sdkKey'   => $sdk_key,
			'mn'       => $meeting_number,
			'role'     => $role,
			'iat'      => $iat,
			'exp'      => $exp,
			'appKey'   => $sdk_key,
			'tokenExp' => $exp,
		);

		if ( empty( $secret_key ) ) {
			return false;
		}

		return \Firebase\JWT\JWT::encode( $payload, $secret_key, 'HS256' );
	}
}
