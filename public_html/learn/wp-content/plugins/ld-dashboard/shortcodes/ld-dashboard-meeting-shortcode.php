<?php

function ld_dashboard_meeting_callback( $atts ) {
	ob_start();
	if ( false !== Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-meeting-shortcode.php' ) ) {
		include Ld_Dashboard_Public::template_override_exists( 'ld-dashboard-meeting-shortcode.php' );
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
			$start_time = gmdate( 'M d Y h:i A', ld_get_local_time_difference( $meeting->start_time ) );
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
				<div class="ld-dashboard-single-meeting-shortcode-content-right-area">
					<div class="ld-dashboard-single-meeting-shortcode-content">
						<div class="ld-dashboard-meeting-sidebar-tile">
							<h3><?php echo esc_html( $title ); ?></h3>
						</div>
						<div class="ld-dashboard-meeting-hosted-by-list-wrap">
							<div class="ld-dashboard-meeting-hosted-details">
								<div class="ld-dashboard-meeting-hosted-by-list-item">
									<span><strong><?php echo esc_html__( 'Topic:', 'ld-dashboard' ); ?></strong></span>
									<span><?php echo esc_html( $meeting->topic ); ?></span>
								</div>
								<div class="ld-dashboard-meeting-hosted-by-list-item">
									<span><strong><?php echo esc_html__( 'Start:', 'ld-dashboard' ); ?></strong></span>
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

add_shortcode( 'ld_dashboard_meeting', 'ld_dashboard_meeting_callback' );
