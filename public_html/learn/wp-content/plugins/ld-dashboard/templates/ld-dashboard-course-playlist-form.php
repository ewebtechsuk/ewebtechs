<?php
$playlist      = new Ld_Dashboard_Course_Playlist();
$return_url    = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' ) . '?action=add-course-playlist&tab=my-courses&_lddnonce=' . $course_nonce;
$license_key   = get_option( 'nss_plugin_license_sfwd_lms' );
$license_email = get_option( 'nss_plugin_license_email_sfwd_lms' );
$process_data  = array(
	'current_step'      => 'ld_cw_process',
	'can_create_course' => false,
	'error_message'     => null,
	'playlist_data'     => null,
	'playlist_url'      => null,
);
if ( isset( $_GET['u'] ) ) {
	$playlist_url   = isset( $_GET['u'] ) ? esc_url_raw( wp_unslash( $_GET['u'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$transient_data = get_transient( $playlist_url );
	
	if ( isset($transient_data['playlist_data']) && !is_object($transient_data['playlist_data'])) {
		$transient_data = array_map('trim', $transient_data);
	}
	
	if ( false === $transient_data || ( isset($transient_data['playlist_data']) && empty($transient_data['playlist_data'])) ) {
		$encoded_playlist_url = rawurlencode( $playlist_url );
		$request              = wp_remote_get(
			add_query_arg(
				array(
					'playlist_url'  => $encoded_playlist_url,
					'license_email' => $license_email,
					'license_key'   => $license_key,
					'return_url'    => rawurlencode( $return_url . '&test=/wp-admin/admin.php&u=' . rawurlencode( $encoded_playlist_url ) ),
				),
				'https://licensing.learndash.com/services/wp-json/learndash-playlist-parser/v1/url_data'
			),
			array(
				'sslverify' => true,
			)
		);
		$body                 = json_decode( wp_remote_retrieve_body( $request ) );
		$transient_data       = array(
			'playlist_data' => $body->playlist_data,
		);
		set_transient( $playlist_url, $transient_data, DAY_IN_SECONDS );
		$process_data = array(
			'current_step'      => 'ld_cw_config',
			'can_create_course' => true,
			'error_message'     => null,
			'playlist_data'     => $body->playlist_data,
			'playlist_url'      => $playlist_url,
		);

	} else {		
		$process_data = array(
			'current_step'      => 'ld_cw_config',
			'can_create_course' => true,
			'error_message'     => null,
			'playlist_data'     => $transient_data['playlist_data'],
			'playlist_url'      => $playlist_url,
		);
	}
}
$user_id        = get_current_user_id();
$transient_name = 'ld_playlist_error_msg_' . $user_id;
$transient_data = get_transient( $transient_name );
if ( false !== $transient_data ) {
	$process_data['can_create_course'] = false;
}
$playlist->render( $process_data );


