<?php

class LD_Dashboard_Zoom_Auth {

	/**
	 * User Id who's app credentials will save
	 *
	 * @var string
	 */
	private $_user_id;

	/**
	 * Zoom app account ID
	 *
	 * @var string
	 */
	private $_account_id;

	/**
	 * Zoom app client Id
	 *
	 * @var string
	 */
	private $_client_id;

	/**
	 * Zoom app client aecret
	 *
	 * @var string
	 */
	private $_client_secret;


	/**
	 * The constructor will call on class Instence
	 *
	 * @return array
	 */
	public function __construct( array $credentials ) {
		$this->_user_id       = $credentials['user_id'];
		$this->_account_id    = $credentials['account_id'];
		$this->_client_id     = $credentials['client_id'];
		$this->_client_secret = $credentials['client_secret'];

		$this->saveAccessToken();

	}

	private function generateAccessToken() {

		if ( empty( $this->_account_id ) ) {
			return new \WP_Error( 'Account ID', 'Account ID is missing' );
		} elseif ( empty( $this->_client_id ) ) {
			return new \WP_Error( 'Client ID', 'Client ID is missing' );
		} elseif ( empty( $this->_client_secret ) ) {
			return new \WP_Error( 'Client Secret', 'Client Secret is missing' );
		}

		$request_url = 'https://zoom.us/oauth/token';
		$encoded     = base64_encode( $this->_client_id . ':' . $this->_client_secret );
		$result      = new \WP_Error( 0, 'Something went wrong' );

		$args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization' => "Basic $encoded",
			),
			'body'    => array(
				'grant_type' => 'account_credentials',
				'account_id' => $this->_account_id,
			),
		);

		$response         = wp_remote_post( $request_url, $args );
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		if ( $response_code == 200 && strtolower( $response_message ) == 'ok' ) {
			$response_body         = wp_remote_retrieve_body( $response );
			$decoded_response_body = json_decode( $response_body );
			if ( isset( $decoded_response_body->access_token ) && ! empty( $decoded_response_body->access_token ) ) {
				$result = $decoded_response_body;
			} elseif ( isset( $decoded_response_body->errorCode ) && ! empty( $decoded_response_body->errorCode ) ) {
				$result = new \WP_Error( $decoded_response_body->errorCode, $decoded_response_body->errorMessage );
			}
		} else {
			$result = new \WP_Error( $response_code, $response_message );
		}

		return $result;

	}

	public function getAccessToken() {
		$user_access_token = get_user_meta( $this->_user_id, 'ldd_zoom_app_token', true );
		if ( ! empty( $user_access_token ) && is_wp_error( $user_access_token ) ) {
			return $this->generateAccessToken();
		} else {
			return $user_access_token;
		}
	}

	private function saveAccessToken() {
		$token = $this->generateAccessToken();
		if ( isset( $token ) && ! empty( $token ) && ! empty( $this->_user_id ) ) {
			update_user_meta( $this->_user_id, 'ldd_zoom_app_token', $token );
		}
	}

}
