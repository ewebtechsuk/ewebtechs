<?php
/*
Plugin Name: FluentCRM Twilio Buttons
Description: Adds SMS and Call buttons next to contact phone fields using Twilio.
Version: 0.1
*/

if (!defined('ABSPATH')) {
    exit;
}

function fctb_enqueue_scripts() {
    wp_enqueue_script(
        'fctb-js',
        plugins_url('js/twilio-buttons.js', __FILE__),
        array('jquery'),
        '0.1',
        true
    );
    wp_localize_script('fctb-js', 'FCTB', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('fctb_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'fctb_enqueue_scripts');

function fctb_send_sms() {
    check_ajax_referer('fctb_nonce', 'nonce');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    if (!$phone) {
        wp_send_json_error('no phone');
    }
    $settings = get_option('fctb_twilio_settings', array(
        'sid'   => '',
        'token' => '',
        'from'  => ''
    ));
    if (!$settings['sid'] || !$settings['token'] || !$settings['from']) {
        wp_send_json_error('twilio not configured');
    }
    $body = array(
        'To'   => $phone,
        'From' => $settings['from'],
        'Body' => 'Test message from FluentCRM'
    );
    $response = wp_remote_post("https://api.twilio.com/2010-04-01/Accounts/{$settings['sid']}/Messages.json", array(
        'body'    => $body,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($settings['sid'] . ':' . $settings['token'])
        )
    ));
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }
    wp_send_json_success();
}
add_action('wp_ajax_fctb_send_sms', 'fctb_send_sms');
