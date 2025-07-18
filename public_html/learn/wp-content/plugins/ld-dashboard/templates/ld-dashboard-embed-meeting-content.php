<?php
$meeting_id   = ! empty( $meeting ) && ! empty( $meeting->id ) ? $meeting->id : false;
$meeting_link = ld_dashboard_get_meeting_embed_url();

if ( isset( $meeting->zoom_states[ $meeting_id ]['state'] ) && $meeting->zoom_states[ $meeting_id ]['state'] == 'ended' ) {
	?>
		<h3><?php esc_html__( 'This meeting has been ended by host.', 'ld-dashboard' ); ?> </h3>
	<?php
} elseif ( $meeting->meeting_time_check > $meeting->meeting_timezone_time && ! empty( $meeting->shortcode_attributes['disable_countdown'] ) && 'no' === $meeting->shortcode_attributes['disable_countdown'] ) {
	?>
	<div class="ld-dashboard-meeting-countdown-wrapper ldd-meeting-countdown">
		<h2 class="ldd-meeting-countdown-title"><?php _e( 'Meeting Starts In', 'ld-dashboard' ); ?></h2>
		<div id="ldd-meeting-timer" class="ldd-meeting-countdoen-timer" data-start-time="<?php echo esc_attr( $meeting->start_time ); ?>" data-timezone="<?php echo esc_attr( $meeting->timezone ); ?>">
			<div class="ldd-timer-cell">
				<div class="ldd-timer-cell-inner">
				<div class="ldd-timer-cell-number">
					<div id="ldd-timer-days">00</div>
				</div>
				<div class="ldd-timer-cell-string"><?php _e( 'Days', 'ld-dashboard' ); ?></div>
				</div>
			</div>
			<div class="ldd-timer-cell ldd-timer-hours">
				<div class="ldd-timer-cell-inner">
				<div class="ldd-timer-cell-number">
					<div id="ldd-timer-hours">00</div>
				</div>
				<div class="ldd-timer-cell-string"><?php _e( 'Hours', 'ld-dashboard' ); ?></div>
				</div>
			</div>
			<div class="ldd-timer-cell ldd-timer-minutes">
				<div class="ldd-timer-cell-inner">
				<div class="ldd-timer-cell-number">
					<div id="ldd-timer-minutes">00</div>
				</div>
				<div class="ldd-timer-cell-string"><?php _e( 'Minutes', 'ld-dashboard' ); ?></div>
				</div>
			</div>
			<div class="ldd-timer-cell ldd-timer-seconds">
				<div class="ldd-timer-cell-inner">
				<div class="ldd-timer-cell-number">
					<div id="ldd-timer-seconds">00</div>
				</div>
				<div class="ldd-timer-cell-string"><?php _e( 'Seconds', 'ld-dashboard' ); ?></div>
				</div>
			</div>
		</div>
	</div>
	<?php
} else {

	if ( isset( $meeting->shortcode_attributes['iframe'] ) && 'yes' === $meeting->shortcode_attributes['iframe'] ) {
		?>
		<div class="ld-dashboard-meeting-iframe-wrapper zoom-window-wrap">
			<div id="<?php echo ! empty( $meeting->shortcode_attributes['id'] ) ? esc_attr( $meeting->shortcode_attributes['id'] ) : 'video-conferncing-embed-iframe'; ?>" class="ldd-zoom-iframe-container">
				<iframe style="width:100%; <?php echo ! empty( $meeting->shortcode_attributes['height'] ) ? 'height: ' . $meeting->shortcode_attributes['height'] : 'height: 500px;'; ?>" sandbox="allow-forms allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-top-navigation" allow="cross-origin-isolated; encrypted-media; autoplay; microphone; camera" src="<?php echo esc_url( $meeting_link ); ?>"></iframe>
			</div>
		</div>
		<?php

	}
}

