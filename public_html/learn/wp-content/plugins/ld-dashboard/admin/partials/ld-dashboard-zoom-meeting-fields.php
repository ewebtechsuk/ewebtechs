<?php
/**
 * Zoom meetings fields template
 */
global $post;
$meeting_data = get_post_meta( $post->ID, 'zoom_details', true );
?>
<div class="ld-dashboard-zoom-meeting-fields-container">
	<div class="ld-dashboard-zoom-meeting-fields-content">
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Start Date/Time', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="datetime-local" name="zoom_details[start_time]" value="<?php echo ( isset( $meeting_data['start_time'] ) ) ? esc_attr( $meeting_data['start_time'] ) : 0; ?>">
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Timezone', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<?php
				$zones_array = array();
				$timestamp   = time();
				?>
				<select name="zoom_details[timezone]">
					<option value=""><?php esc_html_e( 'Select', 'ld-dashboard' ); ?></option>
					<?php
					foreach ( timezone_identifiers_list() as $key => $zone ) {
						date_default_timezone_set( $zone );
						?>
						<option value="<?php echo esc_attr( $zone ); ?>" <?php ( isset( $meeting_data['timezone'] ) ) ? selected( $meeting_data['timezone'], $zone ) : ''; ?>><?php printf( esc_html__( '%1$s (GMT %2$s)', 'ld-dashboard' ), $zone, date( 'P', $timestamp ) ); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Duration', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-dashboard-meeting-time ld-grid-content">
				<div class="meeting-time">
				<span><?php echo esc_html__( 'Hour(s)', 'ld-dashboard' ); ?></span>	
				<input type="number" max="24" name="zoom_details[duration][hr]" value="<?php echo ( isset( $meeting_data['duration']['hr'] ) ) ? esc_attr( $meeting_data['duration']['hr'] ) : 0; ?>" >
				</div>
				<div class="meeting-time">
				<span><?php echo esc_html__( 'Minute(s)', 'ld-dashboard' ); ?></span>
				<input type="number" max="60" name="zoom_details[duration][min]" value="<?php echo ( isset( $meeting_data['duration']['min'] ) ) ? esc_attr( $meeting_data['duration']['min'] ) : 0; ?>">
				</div>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Password', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="text" name="zoom_details[password]" value="<?php echo ( isset( $meeting_data['password'] ) ) ? esc_attr( $meeting_data['password'] ) : ''; ?>">
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Disable Waiting Room', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="checkbox" name="zoom_details[settings][waiting_room]" value="1" <?php ( isset( $meeting_data['settings']['waiting_room'] ) ) ? checked( $meeting_data['settings']['waiting_room'], 1 ) : ''; ?>>
				<span class="ld-decription"><?php esc_html_e( 'Waiting Room is enabled by default - if you want users to skip the waiting room and join the meeting directly - enable this option. Please keep in mind anyone with the meeting link will be able to join without you allowing them into the meeting.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Meeting Authentication', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="checkbox" name="zoom_details[settings][meeting_authentication]" value="1" <?php ( isset( $meeting_data['settings']['meeting_authentication'] ) ) ? checked( $meeting_data['settings']['meeting_authentication'], 1 ) : ''; ?>>
				<span class="ld-decription"><?php esc_html_e( 'Only loggedin users in Zoom App can join this Meeting.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Join Before Host', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="checkbox" name="zoom_details[settings][join_before_host]" value="1" <?php ( isset( $meeting_data['settings']['join_before_host'] ) ) ? checked( $meeting_data['settings']['join_before_host'], 1 ) : ''; ?>>
				<span class="ld-decription"><?php esc_html_e( 'Allow users to join meetin before host start/joins the meeting. Only for scheduled or recurring meetings. If the waiting room is enabled, this setting will not work.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Start When Host Joins', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="checkbox" name="zoom_details[settings][host_video]" value="1" <?php ( isset( $meeting_data['settings']['host_video'] ) ) ? checked( $meeting_data['settings']['host_video'], 1 ) : ''; ?>>
				<span class="ld-decription"><?php esc_html_e( 'Start video when host join meeting.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Participants Video', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="checkbox" name="zoom_details[participant_video]" value="1" <?php ( isset( $meeting_data['participant_video'] ) ) ? checked( $meeting_data['participant_video'], 1 ) : ''; ?>>
				<span class="ld-decription"><?php esc_html_e( 'Start video when participants join meeting.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Mute Participants upon entry', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<input type="checkbox" name="zoom_details[mute_upon_entry]" value="1" <?php ( isset( $meeting_data['mute_upon_entry'] ) ) ? checked( $meeting_data['mute_upon_entry'], 1 ) : ''; ?>>
				<span class="ld-decription"><?php esc_html_e( 'Mutes Participants when entering the meeting.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<div class="ld-single-grid">
			<div class="ld-dashboard-zoom-meeting-field-label ld-grid-label">
				<label><?php echo esc_html__( 'Auto Recording', 'ld-dashboard' ); ?></label>
			</div>
			<div class="ld-dashboard-zoom-meeting-field-content ld-grid-content">
				<select name="zoom_details[settings][auto_recording]">
					<option value="none" <?php echo ( isset( $meeting_data['duration']['min'] ) ) ? selected( $meeting_data['settings']['auto_recording'], 'none' ) : ''; ?>><?php echo esc_html__( 'No Recordings', 'ld-dashboard' ); ?></option>
					<option value="local" <?php echo ( isset( $meeting_data['duration']['min'] ) ) ? selected( $meeting_data['settings']['auto_recording'], 'local' ) : ''; ?>><?php echo esc_html__( 'Local', 'ld-dashboard' ); ?></option>
					<option value="cloud" <?php echo ( isset( $meeting_data['duration']['min'] ) ) ? selected( $meeting_data['settings']['auto_recording'], 'cloud' ) : ''; ?>><?php echo esc_html__( 'Cloud', 'ld-dashboard' ); ?></option>
				</select>
				<span class="ld-decription"><?php esc_html_e( 'Set what type of auto recording feature you want to add. Default is none.', 'ld-dashboard' ); ?></span>
			</div>
		</div>
		<?php wp_nonce_field( 'ajax-nonce', 'nonce' ); ?>
	</div>
</div>
