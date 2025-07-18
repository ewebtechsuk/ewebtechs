<?php
$dashboard_url              = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
$function_obj               = Ld_Dashboard_Functions::instance();
$ld_dashboard_settings_data = Ld_Dashboard_Functions::instance()->ld_dashboard_settings_data();
$settings                   = $ld_dashboard_settings_data['general_settings'];
$show_avatar_field          = true;
if ( class_exists( 'BuddyPress' ) && isset( $settings['default-avatar'] ) && 1 == $settings['default-avatar'] ) {
	$show_avatar_field = false;
}
if ( isset( $_POST['action'] ) && 'update-user' === $_POST['action'] ) {
	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user' ) ) {
		if ( isset( $_POST['first_name'] ) && '' != $_POST['first_name'] ) {
			wp_update_user(
				array(
					'ID'         => $current_user->ID,
					'first_name' => sanitize_text_field( wp_unslash( $_POST['first_name'] ) ),
				),
			);
		}
		if ( isset( $_POST['last_name'] ) && '' != $_POST['last_name'] ) {
			wp_update_user(
				array(
					'ID'        => $current_user->ID,
					'last_name' => sanitize_text_field( wp_unslash( $_POST['last_name'] ) ),
				)
			);
		}
		if ( isset( $_POST['user_email'] ) && '' != $_POST['user_email'] ) {
			wp_update_user(
				array(
					'ID'         => $current_user->ID,
					'user_email' => sanitize_text_field( wp_unslash( $_POST['user_email'] ) ),
				)
			);
		}

		if ( isset( $_POST['user_url'] ) && '' != $_POST['user_url'] ) {
			wp_update_user(
				array(
					'ID'       => $current_user->ID,
					'user_url' => sanitize_text_field( wp_unslash( $_POST['user_url'] ) ),
				)
			);
		}
		if ( isset( $_POST['display_name'] ) && '' != $_POST['display_name'] ) {
			wp_update_user(
				array(
					'ID'           => $current_user->ID,
					'display_name' => sanitize_text_field( wp_unslash( $_POST['display_name'] ) ),
				)
			);
		}
		if ( isset( $_POST['phone_number'] ) && '' != $_POST['phone_number'] ) {
			update_user_meta( $current_user->ID, 'phone_number', sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) );
		}
		if ( isset( $_POST['description'] ) && '' != $_POST['description'] ) {
			wp_update_user(
				array(
					'ID'          => $current_user->ID,
					'description' => sanitize_text_field( wp_unslash( $_POST['description'] ) ),
				)
			);
		}
		if ( isset( $_POST['avatar_id'] ) && '' != $_POST['avatar_id'] ) {
			update_user_meta( $current_user->ID, 'ld_dashboard_avatar_id', sanitize_text_field( wp_unslash( $_POST['avatar_id'] ) ) );
		} else {
			update_user_meta( $current_user->ID, 'ld_dashboard_avatar_id', '' );
		}

		if ( isset( $_POST['avatar_uploaded'] ) && '0' == $_POST['avatar_uploaded'] ) {
			update_user_meta( $current_user->ID, 'ld_dashboard_avatar_sizes', array() );
		}
		do_action( 'ld_dashboard_save_user_profile_fields' );
	}
}
?>

<div class="ld-dashboard-content-inner">
<?php if ( ! is_user_logged_in() ) : ?>
<p class="warning ld-dashboard-warning">
	<?php esc_html_e( 'You must be logged in to edit your profile.', 'ld-dashboard' ); ?>
</p>
<?php else : ?>
	<?php do_action( 'ld_dashboard_before_profile_setting_form' ); ?>
	<form method="post" class="ld-dashboard-profile-form " id="adduser" action="<?php echo esc_url( $dashboard_url ) . '?tab=settings'; ?>" enctype="multipart/form-data">
		<?php
		do_action( 'ld_dashboard_before_profile_setting_fields' );

		$profile_fields = array(
			'first_name'   => array(
				'title' => esc_html__( 'First Name', 'ld-dashboard' ),
				'tag'   => 'input',
				'type'  => 'text',
				'name'  => 'first_name',
				'value' => get_the_author_meta( 'first_name', $current_user->ID ),
				'class' => 'form-username',
			),
			'last_name'    => array(
				'title' => esc_html__( 'Last Name', 'ld-dashboard' ),
				'tag'   => 'input',
				'type'  => 'text',
				'name'  => 'last_name',
				'value' => get_the_author_meta( 'last_name', $current_user->ID ),
				'class' => 'form-username',
			),
			'display_name' => array(
				'title'   => esc_html__( 'Display Name', 'ld-dashboard' ),
				'tag'     => 'select',
				'name'    => 'display_name',
				'options' => array(
					get_the_author_meta( 'user_login', $current_user->ID ) => get_the_author_meta( 'user_login', $current_user->ID ),
					get_the_author_meta( 'first_name', $current_user->ID ) => get_the_author_meta( 'first_name', $current_user->ID ),
					get_the_author_meta( 'last_name', $current_user->ID ) => get_the_author_meta( 'last_name', $current_user->ID ),
					get_the_author_meta( 'first_name', $current_user->ID ) . ' ' . get_the_author_meta( 'last_name', $current_user->ID ) => get_the_author_meta( 'first_name', $current_user->ID ) . ' ' . get_the_author_meta( 'last_name', $current_user->ID ),
					get_the_author_meta( 'last_name', $current_user->ID ) . ' ' . get_the_author_meta( 'first_name', $current_user->ID ) => get_the_author_meta( 'last_name', $current_user->ID ) . ' ' . get_the_author_meta( 'first_name', $current_user->ID ),
					get_the_author_meta( 'nickname', $current_user->ID ) => get_the_author_meta( 'nickname', $current_user->ID ),
				),
				'value'   => get_the_author_meta( 'display_name', $current_user->ID ),
				'class'   => 'form-username',
			),
			'user_url'     => array(
				'title' => esc_html__( 'Website URL', 'ld-dashboard' ),
				'tag'   => 'input',
				'type'  => 'text',
				'name'  => 'user_url',
				'value' => get_the_author_meta( 'user_url', $current_user->ID ),
				'class' => 'form-url',
			),
			'phone_number' => array(
				'title' => esc_html__( 'Phone Number', 'ld-dashboard' ),
				'tag'   => 'input',
				'type'  => 'number',
				'name'  => 'phone_number',
				'value' => get_the_author_meta( 'phone_number', $current_user->ID ),
				'class' => 'form-url',
			),
			'description'  => array(
				'title' => esc_html__( 'Bio', 'ld-dashboard' ),
				'tag'   => 'textarea',
				'name'  => 'description',
				'value' => get_the_author_meta( 'description', $current_user->ID ),
				'class' => 'field-full-width',
			),
		);

		$profile_fields = apply_filters( 'ld_dashboard_user_profile_fields', $profile_fields );

		if ( $show_avatar_field ) :
			$avatar_id  = get_user_meta( $current_user->ID, 'ld_dashboard_avatar_id', true );
			$avatar_url = ( $avatar_id && '' !== $avatar_id ) ? get_user_meta( $current_user->ID, 'ld_dashboard_avatar_sizes', true ) : array();

			$url = ( isset( $avatar_url['ld-medium'] ) ) ? $avatar_url['ld-medium'] : '';

			if ( '' == $url ) {
				$url = ( isset( $avatar_url['medium'] ) ) ? $avatar_url['medium'] : LD_DASHBOARD_PLUGIN_URL . 'public/img/img_avatar.png';
			}
			?>
			<div class="ld-dashboard-profile-form-field ld-dashboard-avatar-field form-avatar" data-user="<?php echo esc_attr( get_current_user_id() ); ?>">
				<img class="ld-dashboard-user-avatar" src="<?php echo esc_url( $url ); ?>" width="200" height="200">
				<div class="upload-delete-avatar-button" data-user="<?php echo esc_attr( get_current_user_id() ); ?>">
					<button class="ld-dashboard-profile-settings change-avatar"><?php esc_html_e( 'Change Avatar', 'ld-dashboard' ); ?></button>
					<button class="ld-dashboard-profile-settings delete-avatar"><?php esc_html_e( 'Delete Avatar', 'ld-dashboard' ); ?></button>
					<input class="text-input ld-dashboard-profile-avatar-uploaded" name="avatar_uploaded" type="hidden" value="<?php echo ( '' != $url ) ? '1' : '0'; ?>" />
					<input class="text-input ld-dashboard-profile-avatar-id" name="avatar_id" type="hidden" value="<?php echo esc_attr( $avatar_id ); ?>" />
				</div>
			</div>
		<?php endif; ?>
		<div class="ld-dashboard-profile-form-field-list">
			<?php
			do_action( 'ld_dashboard_before_profile_setting_fields' );
			foreach ( $profile_fields as $slug => $field ) {
				?>
				<div class="ld-dashboard-profile-form-field <?php echo esc_attr( $field['class'] ); ?>">
					<label for="first-name"><?php echo esc_html( $field['title'] ); ?></label>
				<?php ld_dashboard_get_field_html( $field ); ?>
				</div>
				<?php
			}
			do_action( 'ld_dashboard_after_profile_setting_fields' );
			?>
			<div class="ld-dashboard-profile-form-field field-full-width">
				<input name="updateuser" type="submit" id="updateuser" class="submit button ld-dashboard-btn-bg" value="<?php esc_html_e( 'Update Profile', 'ld-dashboard' ); ?>" />
				<?php wp_nonce_field( 'update-user' ); ?>
				<input name="action" type="hidden" id="action" value="update-user" />
			</div>
		</div>
	</form>
	<?php do_action( 'ld_dashboard_after_profile_setting_form' ); ?>
	<?php endif; ?>
</div>
