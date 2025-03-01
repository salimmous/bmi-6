<?php
if (!defined('ABSPATH')) exit;

// Fitbit API settings
define('FITBIT_CLIENT_ID', 'your-client-id');
define('FITBIT_CLIENT_SECRET', 'your-client-secret');
define('FITBIT_REDIRECT_URI', site_url('/fitbit-callback'));

// Add Fitbit Connect Button
function bmi_pro_fitbit_connect_button() {
    $auth_url = "https://www.fitbit.com/oauth2/authorize?response_type=code&client_id=" . FITBIT_CLIENT_ID . "&redirect_uri=" . urlencode(FITBIT_REDIRECT_URI) . "&scope=profile weight activity";
    echo '<a href="' . esc_url($auth_url) . '" class="button button-primary">Connect to Fitbit</a>';
}
add_shortcode('bmi_pro_fitbit_connect', 'bmi_pro_fitbit_connect_button');

// Handle Fitbit Callback
function bmi_pro_fitbit_callback() {
    if (isset($_GET['code'])) {
        $code = sanitize_text_field($_GET['code']);

        $response = wp_remote_post('https://api.fitbit.com/oauth2/token', [
            'body' => [
                'client_id' => FITBIT_CLIENT_ID,
                'grant_type' => 'authorization_code',
                'redirect_uri' => FITBIT_REDIRECT_URI,
                'code' => $code,
            ],
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(FITBIT_CLIENT_ID . ':' . FITBIT_CLIENT_SECRET),
            ],
        ]);

        if (is_wp_error($response)) {
            wp_die('Fitbit connection failed. Please try again.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        update_user_meta(get_current_user_id(), 'fitbit_access_token', $body['access_token']);
        update_user_meta(get_current_user_id(), 'fitbit_refresh_token', $body['refresh_token']);

        wp_redirect(home_url());
        exit;
    }
}
add_action('template_redirect', 'bmi_pro_fitbit_callback');

// Fetch Fitbit Data
function bmi_pro_fetch_fitbit_data() {
    $access_token = get_user_meta(get_current_user_id(), 'fitbit_access_token', true);
    if (!$access_token) {
        return 'Not connected to Fitbit.';
    }

    $response = wp_remote_get('https://api.fitbit.com/1/user/-/profile.json', [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);

    if (is_wp_error($response)) {
        return 'Failed to fetch data from Fitbit.';
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}
