<?php

function ld_dashboard_get_field_html( $field ) {
	switch ( $field['tag'] ) {
		case 'input':
			echo ld_dashboard_get_input_field_html( $field );
			break;
		case 'select':
			echo ld_dashboard_get_select_field_html( $field );
			break;
		case 'textarea':
			echo ld_dashboard_get_textarea_field_html( $field );
			break;
		default:
			break;
	}
}


function ld_dashboard_get_input_field_html( $field ) {

	$html = '<input type="' . $field['type'] . '" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '">';

	if ( 'password' === $field['type'] ) {
		$html .= '<span class="ld-dashboard-password-toggle dashicons dashicons-visibility"></span>';
	}
	return $html;
}

function ld_dashboard_get_select_field_html( $field ) {

	$html = '<select name="' . $field['name'] . '">';
	if ( is_array( $field['options'] ) && ! empty( $field['options'] ) ) {
		foreach ( $field['options'] as $option => $option_text ) {
			$selected = ( $field['value'] === $option ) ? 'selected' : '';
			$html    .= '<option value="' . esc_attr( $option ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $option_text ) . '</option>';
		}
	}
	$html .= '</select>';
	return $html;
}

function ld_dashboard_get_textarea_field_html( $field ) {
	$html = '<textarea name="' . esc_attr( $field['name'] ) . '">' . esc_html( $field['value'] ) . '</textarea>';
	return $html;
}


add_action( 'init', 'ld_dashboard_submit_user_zoom_credentials' );
function ld_dashboard_submit_user_zoom_credentials() {

	if ( isset( $_POST['action'] ) && 'update-zoom-settings' === $_POST['action'] ) {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-zoom-settings' ) ) {

			if ( isset( $_POST['zoom_api_key'] ) && '' != $_POST['zoom_api_key'] ) {
				update_user_meta( get_current_user_id(), 'zoom_api_key', sanitize_text_field( wp_unslash( $_POST['zoom_api_key'] ) ) );
			} else {
				update_user_meta( get_current_user_id(), 'zoom_api_key', '' );
			}
			if ( isset( $_POST['zoom_api_secret'] ) && '' != $_POST['zoom_api_secret'] ) {
				update_user_meta( get_current_user_id(), 'zoom_api_secret', sanitize_text_field( wp_unslash( $_POST['zoom_api_secret'] ) ) );
			} else {
				update_user_meta( get_current_user_id(), 'zoom_api_secret', '' );
			}
			if ( isset( $_POST['zoom_account_id'] ) && '' != $_POST['zoom_account_id'] ) {
				update_user_meta( get_current_user_id(), 'zoom_account_id', sanitize_text_field( wp_unslash( $_POST['zoom_account_id'] ) ) );
			} else {
				update_user_meta( get_current_user_id(), 'zoom_account_id', '' );
			}
			if ( isset( $_POST['zoom_email'] ) && '' != $_POST['zoom_email'] ) {
				update_user_meta( get_current_user_id(), 'zoom_email', sanitize_text_field( wp_unslash( $_POST['zoom_email'] ) ) );
			} else {
				update_user_meta( get_current_user_id(), 'zoom_email', '' );
			}
			do_action( 'ld_dashboard_save_zoom_fields' );

		}
	}
}



