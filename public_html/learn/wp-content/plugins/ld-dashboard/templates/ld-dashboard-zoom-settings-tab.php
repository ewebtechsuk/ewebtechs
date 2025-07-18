<?php $dashboard_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' ); ?>

<div class="ld-dashboard-content-inner ld-dashboard-zoom-setting-fields">
<?php if ( ! is_user_logged_in() ) : ?>
<p class="warning ld-dashboard-warning">
	<?php esc_html_e( 'You must be logged in to edit your profile.', 'ld-dashboard' ); ?>
</p><!-- .warning -->
<?php else : ?>
	<?php do_action( 'ld_dashboard_before_zoom_setting_form' ); ?>
	<form method="post" class="ld-dashboard-profile-form " id="adduser" action="<?php echo esc_url( $dashboard_url ) . '?tab=settings&action=zoom'; ?>">
		<?php
		do_action( 'ld_dashboard_before_zoom_setting_fields' );

		$status_icon = '';
		if ( class_exists( 'Zoom_Api' ) && '' !== get_user_meta( get_current_user_id(), 'zoom_api_key', true ) ) {
			$zoom_meeting = new Zoom_Api();
			$response     = $zoom_meeting->get_all_meetings( '?page_size=2&page_number=1' );

			if ( ! empty( $response ) && property_exists( $response, 'meetings' ) ) {
				$status_icon = '<span class="dashicons dashicons-yes-alt ld-dashboard-zoom-api-status zoom-api-active"></span>';
			} else {
				$status_icon = '<span class="dashicons dashicons-dismiss ld-dashboard-zoom-api-status zoom-api-inactive"></span>';
			}
		}

			$zoom_fields = array(
				'zoom_account_id' => array(
					'title' => esc_html__( 'Account ID ( S2S Outh )', 'ld-dashboard' ),
					'tag'   => 'input',
					'type'  => 'password',
					'name'  => 'zoom_account_id',
					'value' => get_user_meta( get_current_user_id(), 'zoom_account_id', true ),
					'class' => 'form-url',
					'icon'  => ( '' !== $status_icon ) ? $status_icon : '',
				),
				'zoom_api_key'    => array(
					'title' => esc_html__( 'Client ID ( S2S Outh )', 'ld-dashboard' ),
					'tag'   => 'input',
					'type'  => 'password',
					'name'  => 'zoom_api_key',
					'value' => get_user_meta( get_current_user_id(), 'zoom_api_key', true ),
					'class' => 'form-url',
					'icon'  => ( '' !== $status_icon ) ? $status_icon : '',
				),
				'zoom_api_secret' => array(
					'title' => esc_html__( 'Client secret ( S2S Outh )', 'ld-dashboard' ),
					'tag'   => 'input',
					'type'  => 'password',
					'name'  => 'zoom_api_secret',
					'value' => get_user_meta( get_current_user_id(), 'zoom_api_secret', true ),
					'class' => 'form-url',
					'icon'  => ( '' !== $status_icon ) ? $status_icon : '',
				),
				'zoom_email'      => array(
					'title' => esc_html__( 'Zoom Account Email', 'ld-dashboard' ),
					'tag'   => 'input',
					'type'  => 'email',
					'name'  => 'zoom_email',
					'value' => get_user_meta( get_current_user_id(), 'zoom_email', true ),
					'class' => 'form-url',
					'icon'  => ( '' !== $status_icon ) ? $status_icon : '',
				),
			);

			$zoom_fields = apply_filters( 'ld_dashboard_zoom_fields', $zoom_fields );
			?>
			<div class="ld-dashboard-profile-form-field-list">
				<div class="ld-dashboard-notice" style="color:red;">
					<?php
					echo sprintf(
						__( 'Zoom will no longer support JWT app types as of June 1, 2023. As a result, we are migrating from JWT to the S2S app type. Please generate your Server-to-Server OAuth credentials and paste them into the respective field. %s to learn how to setup S2S OAuth.', 'ld-dashboard' ),
						'<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/zoom-integration/setup-of-server-to-server-oauth-app/" target="_blank">' . __( 'Click here', 'ld-dashboard' ) . '</a>'
					);
					?>
				</div>
				<?php
				do_action( 'ld_dashboard_before_zoom_setting_fields' );
				foreach ( $zoom_fields as $slug => $field ) {
					?>
					<div class="ld-dashboard-profile-form-field <?php echo esc_attr( $field['class'] ); ?>">
						<label for="first-name"><?php echo esc_html( $field['title'] ); ?> <?php echo ( isset( $field['icon'] ) && '' !== $field['icon'] ) ? wp_kses_post( $field['icon'] ) : ''; ?></label>
						<div class="ld-dashboard-zoom-setting-fields"><?php ld_dashboard_get_field_html( $field ); ?></div>
					</div>
					<?php
				}
				do_action( 'ld_dashboard_after_zoom_setting_fields' );
				?>
				<div class="ld-dashboard-profile-form-field field-full-width">
					<input name="updateuser" type="submit" id="updateuser" class="submit button ld-dashboard-btn-bg" value="<?php esc_html_e( 'Save', 'ld-dashboard' ); ?>" />
					<?php wp_nonce_field( 'update-zoom-settings' ); ?>
					<input name="action" type="hidden" id="action" value="update-zoom-settings" />
				</div>
			</div>
	</form>
	<?php do_action( 'ld_dashboard_after_zoom_setting_form' ); ?>
	<?php endif; ?>
</div>
