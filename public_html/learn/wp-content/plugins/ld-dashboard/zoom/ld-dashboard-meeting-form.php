<?php
if ( 'create-form' === $type ) {
	$header_label = esc_html__( 'Create Meeting', 'ld-dashboard' );
	$header_slug  = 'create-meeting';
} else {
	$header_label = esc_html__( 'Update Meeting', 'ld-dashboard' );
	$header_slug  = 'edit-meeting';
}
$current_date = gmdate( 'Y-m-d' );
$current_time = gmdate( 'h:i' );
$min_date     = $current_date . 'T' . $current_time;
?>
<div class="ld-dashboard-create-meeting-inner">
	<div class="ld-dashboard-create-meeting-form-head">
		<h3><?php echo esc_html( $header_label ); ?></h3>
		<div class="ld-dashboard-create-meeting-close-btn"> &#10006; </div>
	</div>
	<form id="ld-dashboard-meeting-form" method="POST">
		<?php
		if ( 'edit-meeting' === $header_slug ) {
			echo '<input type="hidden" name="post_id" value="' . esc_attr( $post_id ) . '">';
		}
		do_action( 'ld_dashboard_before_meeting_form_fields' );
		?>
		<div class="ld-dashboard-create-meeting-form-row ld-dashboard-create-meeting-title">
			<div class="ld-dashboard-form-group-row">
				<label><?php echo esc_html__( 'Meeting Title *', 'ld-dashboard' ); ?></label>
				<input type="text" name="zoom_details[topic]" value="<?php echo ( isset( $meeting_data['topic'] ) ) ? esc_attr( $meeting_data['topic'] ) : ''; ?>" required>
			</div>
			<div class="ld-dashboard-form-group-row ld-dashboard-create-meeting-description">
				<label><?php echo esc_html__( 'Description', 'ld-dashboard' ); ?></label>
				<textarea name="zoom_details[agenda]"><?php echo ( isset( $meeting_data['agenda'] ) ) ? esc_attr( $meeting_data['agenda'] ) : ''; ?></textarea>
			</div>
			<div class="ld-dashboard-form-group-row">
				<label><?php echo esc_html__( 'Password', 'ld-dashboard' ); ?></label>
				<input type="password" name="zoom_details[password]" value="<?php echo ( isset( $meeting_data['password'] ) ) ? esc_attr( $meeting_data['password'] ) : ''; ?>">
			</div>
		</div>
		<div class="ld-dashboard-create-meeting-form-row ld-dashboard-create-meeting-start-date-time-wrap">
			<div class="ld-dashboard-form-group-row ld-dashboard-create-meeting-start-date-time">
				<label><?php echo esc_html__( 'Start Date/Time', 'ld-dashboard' ); ?></label>
				<div class="ld-dashboard-meeting-row-group">
					<input type="datetime-local" name="zoom_details[start_time]" min="<?php echo esc_attr( $min_date ); ?>" value="<?php echo ( isset( $meeting_data['start_time'] ) ) ? esc_attr( $meeting_data['start_time'] ) : ''; ?>">
				</div>
			</div>
			<div class="ld-dashboard-form-group-row ld-dashboard-create-meeting-duration">
				<label><?php echo esc_html__( 'Duration', 'ld-dashboard' ); ?></label>
				<div class="meeting-row-group">
					<div class="meeting-time">
						<input type="number" max="24" name="zoom_details[duration][hr]" placeholder="<?php echo esc_html__( 'Hour(s)', 'ld-dashboard' ); ?>" value="<?php echo ( isset( $meeting_data['duration']['hr'] ) ) ? esc_attr( $meeting_data['duration']['hr'] ) : 0; ?>">
						<small class="ld-dashboard-meeting-time-text"><?php echo esc_html__( 'Hour(s)', 'ld-dashboard' ); ?></small>
					</div>
					<div class="meeting-time">
						<input type="number" max="60" name="zoom_details[duration][min]" placeholder="<?php echo esc_html__( 'Minute(s)', 'ld-dashboard' ); ?>" value="<?php echo ( isset( $meeting_data['duration']['min'] ) ) ? esc_attr( $meeting_data['duration']['min'] ) : 0; ?>">
						<small class="ld-dashboard-meeting-time-text"><?php echo esc_html__( 'Min(s)', 'ld-dashboard' ); ?></small>
					</div>
				</div>
			</div>
			<div class="ld-dashboard-form-group-row ld-dashboard-create-meeting-timezone">
				<label><?php echo esc_html__( 'Timezone', 'ld-dashboard' ); ?></label>
				<?php
				$zones_array    = array();
				$timestamp      = time();
				$local_timezone = wp_timezone_string();
				?>
				<select name="zoom_details[timezone]" class="ld-dashboard-meeting-timezone">
					<option value=""><?php esc_html_e( 'Select', 'ld-dashboard' ); ?></option>
					<?php
					foreach ( timezone_identifiers_list() as $key => $zone ) {
						date_default_timezone_set( $zone );
						?>
						<option value="<?php echo esc_attr( $zone ); ?>" <?php ( isset( $meeting_data['timezone'] ) ) ? selected( $meeting_data['timezone'], $zone ) : ''; ?> <?php echo ( ! isset( $meeting_data['timezone'] ) ) ? selected( $local_timezone, $zone ) : ''; ?> <?php echo ( ! isset( $meeting_data['timezone'] ) ) ? selected( $local_timezone, date( 'P', $timestamp ) ) : ''; ?>><?php printf( esc_html__( '%1$s (GMT %2$s)', 'ld-dashboard' ), esc_html( $zone ), esc_html( date( 'P', $timestamp ) ) ); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="ld-dashboard-create-meeting-form-row ld-dashboard-create-meeting-options">
			<div class="ld-dashboard-form-group-row">
				<h5><?php echo esc_html__( 'Meeting Option', 'ld-dashboard' ); ?></h5>
				<div class="ld-dashboard-meeting-row-group">
					<input type="checkbox" name="zoom_details[settings][waiting_room]" value="1" <?php ( isset( $meeting_data['settings']['waiting_room'] ) ) ? checked( $meeting_data['settings']['waiting_room'], 1 ) : ''; ?>>
					<label><?php echo esc_html__( 'Disable Waiting Room', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-meeting-option-tooltip">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/question-tooltips.svg'; ?>">
						<div class="ld-dashboard-option-tooltip">
							<span><?php esc_html_e( 'Waiting Room is enabled by default - if you want users to skip the waiting room and join the meeting directly - enable this option. Please keep in mind anyone with the meeting link will be able to join without you allowing them into the meeting.', 'ld-dashboard' ); ?></span>
						</div>
					</div>
				</div>
				<div class="ld-dashboard-meeting-row-group">
					<input type="checkbox" name="zoom_details[settings][meeting_authentication]" value="1" <?php ( isset( $meeting_data['settings']['meeting_authentication'] ) ) ? checked( $meeting_data['settings']['meeting_authentication'], 1 ) : ''; ?>>
					<label><?php echo esc_html__( 'Meeting Authentication', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-meeting-option-tooltip">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/question-tooltips.svg'; ?>">
						<div class="ld-dashboard-option-tooltip">
							<span><?php esc_html_e( 'Only loggedin users in Zoom App can join this Meeting.', 'ld-dashboard' ); ?></span>
						</div>
					</div>
				</div>
				<div class="ld-dashboard-meeting-row-group">
					<input type="checkbox" name="zoom_details[settings][join_before_host]" value="1" <?php ( isset( $meeting_data['settings']['join_before_host'] ) ) ? checked( $meeting_data['settings']['join_before_host'], 1 ) : ''; ?>>
					<label><?php echo esc_html__( 'Join Before Host', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-meeting-option-tooltip">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/question-tooltips.svg'; ?>">
						<div class="ld-dashboard-option-tooltip">
							<span><?php esc_html_e( 'Allow users to join meetin before host start/joins the meeting. Only for scheduled or recurring meetings. If the waiting room is enabled, this setting will not work.', 'ld-dashboard' ); ?></span>
						</div>
					</div>
				</div>
				<div class="ld-dashboard-meeting-row-group">
					<input type="checkbox" name="zoom_details[settings][host_video]" value="1" <?php ( isset( $meeting_data['settings']['host_video'] ) ) ? checked( $meeting_data['settings']['host_video'], 1 ) : ''; ?>>
					<label><?php echo esc_html__( 'Start When Host Joins', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-meeting-option-tooltip">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/question-tooltips.svg'; ?>">
						<div class="ld-dashboard-option-tooltip">
							<span><?php esc_html_e( 'Start video when host join meeting.', 'ld-dashboard' ); ?></span>
						</div>
					</div>
				</div>
				<div class="ld-dashboard-meeting-row-group">
					<input type="checkbox" name="zoom_details[participant_video]" value="1" <?php ( isset( $meeting_data['participant_video'] ) ) ? checked( $meeting_data['participant_video'], 1 ) : ''; ?>>
					<label><?php echo esc_html__( 'Participants Video', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-meeting-option-tooltip">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/question-tooltips.svg'; ?>">
						<div class="ld-dashboard-option-tooltip">
							<span><?php esc_html_e( 'Start video when participants join meeting.', 'ld-dashboard' ); ?></span>
						</div>
					</div>
				</div>
				<div class="ld-dashboard-meeting-row-group">
					<input type="checkbox" name="zoom_details[mute_upon_entry]" value="1" <?php ( isset( $meeting_data['mute_upon_entry'] ) ) ? checked( $meeting_data['mute_upon_entry'], 1 ) : ''; ?>>
					<label><?php echo esc_html__( 'Mute Participants upon entry', 'ld-dashboard' ); ?></label>
					<div class="ld-dashboard-meeting-option-tooltip">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/question-tooltips.svg'; ?>">
						<div class="ld-dashboard-option-tooltip">
							<span><?php esc_html_e( 'Mutes Participants when entering the meeting.', 'ld-dashboard' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ld-dashboard-create-meeting-form-row ld-dashboard-create-meeting-auto-recording">
			<div class="ld-dashboard-form-group-row ld-auto-recording">
				<label><?php echo esc_html__( 'Auto Recording', 'ld-dashboard' ); ?></label>	
				<div class="ld-dashboard-meeting-row-group">
					<select name="zoom_details[settings][auto_recording]">
						<option value="none" <?php echo ( isset( $meeting_data['duration']['min'] ) ) ? selected( $meeting_data['settings']['auto_recording'], 'none' ) : ''; ?>><?php echo esc_html__( 'No Recordings', 'ld-dashboard' ); ?></option>
						<option value="local" <?php echo ( isset( $meeting_data['duration']['min'] ) ) ? selected( $meeting_data['settings']['auto_recording'], 'local' ) : ''; ?>><?php echo esc_html__( 'Local', 'ld-dashboard' ); ?></option>
						<option value="cloud" <?php echo ( isset( $meeting_data['duration']['min'] ) ) ? selected( $meeting_data['settings']['auto_recording'], 'cloud' ) : ''; ?>><?php echo esc_html__( 'Cloud', 'ld-dashboard' ); ?></option>
					</select>
					<div class="ld-dashboard-option-sub-option">
						<small><?php esc_html_e( 'Set what type of auto recording feature you want to add. Default is none.', 'ld-dashboard' ); ?></small>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'ld_dashboard_after_meeting_form_fields' ); ?>
		<div class="ld-dashboard-create-meeting-form-row ld-create-meeting-btn">
			<?php wp_nonce_field( 'ld-dashboard-create-meeting', 'ld-dashboard-create-meeting' ); ?>
			<input type="submit" name="create-meeting" value="<?php echo esc_attr( $header_label ); ?>">
			<div class="ld-dashboard-meeting-form-loader" style="display:none"><i class="fa fa-spinner fa-spin" ></i></div>
		</div>
	</form>
</div>
