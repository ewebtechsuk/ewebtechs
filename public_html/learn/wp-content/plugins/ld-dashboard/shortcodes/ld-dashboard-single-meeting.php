<?php

function ld_dashboard_meeting_single_callback( $atts ) {
	ob_start();
	if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-single-meeting.php' ) ) {
		include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-single-meeting.php' );
		return ob_get_clean();
	}
	if ( isset( $atts['id'] ) ) {
		$current_user               = wp_get_current_user();
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];
		$meeting_id                 = $atts['id'];
		$title                      = ( isset( $atts['title'] ) && '' !== $atts['title'] ) ? $atts['title'] : esc_html__( 'Details', 'ld-dashboard' );
		$zoom_meeting_id            = get_post_meta( $meeting_id, 'zoom_meeting_id', true );
		$meeting                    = get_post_meta( $meeting_id, 'zoom_meeting_response', true );
		$start_url                  = get_post_meta( $meeting_id, 'zoom_meeting_start_url', true );
		$meeting_client_url         = ld_dashboard_get_meeting_embed_url();

		if ( is_object( $meeting ) && property_exists( $meeting, 'id' ) ) {
			$start_time = ld_dashboard_date_converter( $meeting->start_time, $meeting->timezone, 'M d Y h:i A', true );
			if ( $meeting->duration > 59 ) {
				$min = $meeting->duration % 60;
				$hr  = $meeting->duration - $min;
				$hr  = $meeting->duration / 60;
			} else {
				$hr  = 0;
				$min = $meeting->duration;
			}
			?>
			<div class="ld-dashboard-single-meeting-shortcode-wrapper">
				<?php if ( ! empty( $meeting->agenda ) ) : ?>
				<div class="ld-dashboard-single-meeting-shortcode-content-left-area">
					<div class="ld-dashboard-meeting-sidebar-tile">
						<?php echo isset( $meeting->agenda ) && ! empty( $meeting->agenda ) ? esc_html( $meeting->agenda ) : ''; ?>
					</div>
				</div>
				<?php endif; ?>
				<div class="ld-dashboard-single-meeting-shortcode-content-right-area">
					<div class="ld-dashboard-single-meeting-shortcode-content">
						<div class="ld-dashboard-meeting-countdown-wrapper ldd-meeting-countdown">
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
						<div class="ld-dashboard-meeting-hosted-by-list-wrap">
							<div class="ld-dashboard-meeting-hosted-details">
								<div class="ld-dashboard-meeting-hosted-by-list-item">
									<span><strong><?php echo esc_html__( 'Topic:', 'ld-dashboard' ); ?></strong></span>
									<span><?php echo esc_html( $meeting->topic ); ?></span>
								</div>
								<div class="ld-dashboard-meeting-hosted-by-list-item">
									<span><strong><?php echo esc_html__( 'Start:', 'ld-dashboard' ); ?></strong></span>
									<!-- <input type="hidden" class="ld-dashboard-single-meeting-end-time" value="<?php echo esc_attr( $date_input_val ); ?>"> -->
									<span><?php echo esc_html( $start_time ); ?></span>
								</div>
								<div class="ld-dashboard-meeting-hosted-by-list-item">
									<span><strong><?php echo esc_html__( 'Duration:', 'ld-dashboard' ); ?></strong></span>
									<span><?php printf( '%d hrs %d min', esc_html( $hr ), esc_html( $min ) ); ?></span>
								</div>
								<div class="ld-dashboard-meeting-hosted-by-list-item">
									<span><strong><?php echo esc_html__( 'Timezone:', 'ld-dashboard' ); ?></strong></span>
									<span><?php echo esc_html( $meeting->timezone ); ?></span>
								</div>
								<div class="ld-dashboard-meeting-actions-button">
								<div class="ld-dashboard-meeting-actions">
								<?php if ( property_exists( $meeting, 'start_url' ) && ( in_array( 'administrator', $current_user->roles ) || in_array( 'ld_instructor', $current_user->roles ) ) && ld_dashboard_can_user_start_meeting( $meeting_id ) ) : ?>
									<div class="ld-dashboard-meeting-action">
										<a href="<?php echo esc_url( $start_url ); ?>"><?php echo esc_html__( 'Start', 'ld-dashboard' ); ?></a>
									</div>
									<?php endif; ?>
								<?php if ( property_exists( $meeting, 'join_url' ) ) : ?>
									<div class="ld-dashboard-meeting-action">
										<a href="<?php echo esc_url( $meeting->join_url ); ?>"><?php echo esc_html__( 'Join', 'ld-dashboard' ); ?></a>
									</div>
									<?php endif; ?>									
								</div>
								<?php if ( ld_dashboard_is_sdk_enabled() ) : ?>
									<div class="ld-dashboard-meeting-action browser-join">
										<a href="<?php echo esc_url( $meeting_client_url ); ?>"><?php echo esc_html__( 'Browser Join', 'ld-dashboard' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></a>
									</div>
									<?php endif; ?>
								</div>	
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		} else {
			?>
				<p class="ld-dashboard-warning"><?php echo esc_html__( 'Invalid meeting Id.', 'ld-dashboard' ); ?></p>
			<?php
		}
	} else {
		?>
			<p class="ld-dashboard-warning"><?php echo esc_html__( 'Invalid meeting Id.', 'ld-dashboard' ); ?></p>
		<?php
	}
	return ob_get_clean();
}

add_shortcode( 'ld_dashboard_meeting_single', 'ld_dashboard_meeting_single_callback' );
