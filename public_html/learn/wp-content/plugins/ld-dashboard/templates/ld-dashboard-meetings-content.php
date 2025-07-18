<?php
if ( class_exists( 'Zoom_Api' ) ) {
	$zoom_meeting             = new Zoom_Api();
	$dashboard_page           = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
	$has_set_zoom_credentials = ld_dashboard_check_if_zoom_credentials_exists();
	$current_user             = wp_get_current_user();
	?>
<div class="zoom-meeting-wrapper">
	<div class="ld-dashboard-meeting-listing-wrapper">
		<div class="zoom-meeting-filter-link">
			<a href="<?php echo esc_url( $dashboard_page ) . '?tab=meetings'; ?>" class="ld-dashboard-meeting-filter <?php echo ( ! isset( $_GET['filter'] ) ) ? 'ld-dashboard-meeting-filter-active' : ''; ?>"><?php echo esc_html__( 'Upcoming Meetings', 'ld-dashboard' ); ?></a>
			<a href="<?php echo esc_url( $dashboard_page ) . '?tab=meetings&filter=prev'; ?>" class="ld-dashboard-meeting-filter <?php echo ( isset( $_GET['filter'] ) ) ? 'ld-dashboard-meeting-filter-active' : ''; ?>"><?php echo esc_html__( 'Past Meetings', 'ld-dashboard' ); ?></a>
			<a href="#" class="create-meeting ld-create-meeting-btn ld-create-meeting-action <?php echo ( ! $has_set_zoom_credentials ) ? 'has-no-credentials' : ''; ?>" data-type="create-form"><?php echo esc_html__( 'Create New Meeting', 'ld-dashboard' ); ?></a>
		</div>
		<div class="zoom-meeting-list-wrap">
			<?php
			try {
				$meeting_status = '';
				$param          = '';
				$show_edit_btn  = true;
				if ( isset( $_GET['filter'] ) && 'prev' === $_GET['filter'] ) {
					$meeting_status = 'prev-meeting';
					$param          = 'previous_meetings';
					$show_edit_btn  = false;
				} else {
					$meeting_status = 'upcoming-meeting';
					$param          = 'upcoming_meetings';
				}
				if ( in_array( 'administrator', $current_user->roles ) ) {
					$meeting_args = array(
						'post_type'   => 'zoom_meet',
						'post_status' => 'publish',
						'fields'      => 'ids',
						'meta_query'  => array(
							array(
								'key'     => 'using_admin_credentials',
								'value'   => 'yes',
								'compare' => '==',
							),
						),
					);
				} else {
					$meeting_args = array(
						'post_type'   => 'zoom_meet',
						'author'      => get_current_user_id(),
						'post_status' => 'publish',
						'fields'      => 'ids',
					);
				}

				$current_user_meetings = get_posts( $meeting_args );
				$response              = $zoom_meeting->get_all_meetings( '?page_size=10&page_number=1&type=' . $param );

				$no_meetings_found = false;
				if ( ! empty( $response ) && property_exists( $response, 'meetings' ) ) {
					$meetings = $response->meetings;

					if ( is_array( $meetings ) ) {
						$iterated = array();
						foreach ( $meetings as $meeting ) {

							$args            = array(
								'post_type'  => 'zoom_meet',
								'meta_query' => array(
									array(
										'key'     => 'zoom_meeting_id',
										'value'   => $meeting->id,
										'compare' => '==',
									),
								),
							);
							$meeting_post    = get_posts( $args );
							$meeting_post_id = 0;
							if ( ! empty( $meeting_post ) ) {
								$meeting_post_id = $meeting_post[0]->ID;
							}
							if ( 0 === $meeting_post_id || ! in_array( $meeting_post_id, $current_user_meetings ) ) {
								continue;
							}
							$start_url      = get_post_meta( $meeting_post_id, 'zoom_meeting_start_url', true );
							$start_text     = esc_html__( 'Start Meeting', 'ld-dashboard' );
							$iterated[]     = $meeting_post_id;
							$has_recordings = get_post_meta( $meeting_post_id, 'ldd_meeting_has_recordings', true );
							?>
							<div class="zoom-meeting-inner-list">
								<div class="zoom-meeting-left-inner">
									<h4><?php echo esc_html( $meeting->topic ); ?> <div class="ld-dashboard-meeting-shortcode-copy"><div class="ld-dashboard-meeting-shortcode">[ld_dashboard_meeting_embed meeting_id='<?php echo esc_html( $meeting_post_id ); ?>']</div> <div class="ld-dashboard-tooltip" title="<?php echo esc_html__( 'Short Code Copied!', 'ld-dashboard' ); ?>" flow="up" style="display:none;"></div></div></h4>
									<div class="zoom-meeting-meta">
										<span><?php echo esc_html__( 'ID:', 'ld-dashboard' ); ?> <?php echo esc_html( $meeting->id ); ?></span>
										<span>
											<?php echo esc_html__( 'Date:', 'ld-dashboard' ); ?> 
											<?php echo esc_html( ld_dashboard_date_converter( $meeting->start_time, $meeting->timezone, 'M d Y h:i A', true ) ); ?>
										</span>
										<span class="meeting-status <?php echo esc_attr( $meeting_status ); ?>"></span>
									</div>
									<div class="ld-dashboard-zoom-meeting-link-wrapper">
									<?php if ( '' !== $meeting->join_url ) : ?>
									<div class="zoom-meeting-join-link-wrapper">
										<div class="ld-join-link-url" style="display:none;"><?php echo esc_attr( $meeting->join_url ); ?></div>
										<button class="ld-dashboard-copy-join-link"><?php echo esc_html__( 'Meeting Join Link', 'ld-dashboard' ); ?></button>
										<span class="ld-dashboard-copy-join-link-message"><?php echo esc_html__( 'Link has been copied to clipboard', 'ld-dashboard' ); ?></span>
									</div>
									<?php endif; ?>
									<?php if ( ld_dashboard_is_sdk_enabled() ) : ?>
										<?php $meeting_client_url = ld_dashboard_get_meeting_embed_url(); ?>
										<div class="ld-dashboard-meeting-action browser-join">
											<a href="<?php echo esc_url( $meeting_client_url ); ?>"><?php echo esc_html__( 'Browser Join', 'ld-dashboard' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></a>
										</div>
									<?php endif; ?>
									</div>
									<?php if ( 'yes' === $has_recordings ) : ?>
										<div class="ld-dashboard-meeting-recordings-btn" data-id="<?php echo esc_attr( $meeting->id ); ?>"><?php echo esc_html__( 'Recordings', 'ld-dashboard' ); ?> <img src=" <?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/angle-down.svg'; ?>" /></div>
										<div class="ld-dashboard-meeting-recordings-wrapper"></div>
									<?php endif; ?>
								</div>
								<?php if ( $meeting_post_id > 0 ) : ?>
									<div class="edit-delete-meeting-zoom">
										<?php if ( $show_edit_btn ) : ?>
										<a href="#" class="ld-create-meeting-action" data-type="edit-form" data-post="<?php echo esc_attr( $meeting_post_id ); ?>"><?php echo esc_html__( 'Edit', 'ld-dashboard' ); ?></a>
										<?php endif; ?>
										<a href="#" class="ld-delete-meeting-action" data-post="<?php echo esc_attr( $meeting_post_id ); ?>" data-meeting="<?php echo esc_attr( $meeting->id ); ?>"><?php echo esc_html__( 'Delete', 'ld-dashboard' ); ?></a>
										<a href="<?php echo esc_url( get_permalink( $meeting_post_id ) ); ?>" class="ld-view-meeting-action" data-post="<?php echo esc_attr( $meeting_post_id ); ?>" data-meeting="<?php echo esc_attr( $meeting->id ); ?>"><?php echo esc_html__( 'View', 'ld-dashboard' ); ?></a>
									</div>
								<?php endif; ?>
								<?php if ( ld_dashboard_can_user_start_meeting( $meeting_post_id ) ) : ?>
								<div class="zoom-meeting-start-wrap">
									<div class="start-zoom-meeting-view">
										<a href="<?php echo esc_url( $start_url ); ?>" target="_blank"><img src=" <?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/icons/play-circle.svg'; ?>" /></a>
										<div class="zoom-meeting-start-toltip"><?php echo esc_html( $start_text ); ?></div>
									</div>
								</div>
								<?php endif; ?>
							</div>
							<?php
						}
						if ( empty( $iterated ) ) {
							$no_meetings_found = true;
						}
					} else {
						$no_meetings_found = true;
					}
				} else {
					$no_meetings_found = true;
				}
				if ( $no_meetings_found ) {
					?>
					<div class="ld-dashboard-meeting-error-msg"><?php echo esc_html__( 'No meetings found.', 'ld-dashboard' ); ?></div>
					<?php
				}
			} catch ( Exception $ex ) {
				echo $ex;
			}
			?>
		</div>
	</div>
	<div class="ld-dashboard-create-meeting-wrap"></div>
</div>

	<?php
}
