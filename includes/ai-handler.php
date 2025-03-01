<?php
if (!defined('ABSPATH')) exit;

/**
 * Handle API test AJAX requests
 */
function bmi_pro_test_api_handler() {
    $ai_service = get_option('bmi_ai_service', 'chatgpt'); // Default to ChatGPT
    $api_key = get_option('bmi_ai_api_key', '');

    if (empty($api_key)) {
        wp_send_json_error(['message' => 'API key is missing. Please configure it in the settings.']);
    }

    // Define API endpoints and payloads
    $endpoint = '';
    $payload = [];

    switch ($ai_service) {
        case 'chatgpt':
            $endpoint = 'https://api.openai.com/v1/completions';
            $payload = json_encode([
                'model' => 'text-davinci-003',
                'prompt' => 'This is a test message for API connection.',
                'max_tokens' => 10,
                'temperature' => 0.7,
            ]);
            break;

        case 'gemini':
            $endpoint = 'https://api.gemini.com/v1/test';
            $payload = json_encode(['test' => 'This is a test message for Gemini.']);
            break;

        case 'aimlapi':
            $endpoint = 'https://api.aimlapi.com/v1/test';
            $payload = json_encode(['test' => 'This is a test message for AIMLAPI.']);
            break;

        default:
            wp_send_json_error(['message' => 'Invalid AI service selected.']);
    }

    // Make API request
    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => $payload,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Failed to connect to the API. Error: ' . $response->get_error_message()]);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Log API response for debugging
    error_log('API Response: ' . print_r($data, true));

    // Parse and handle the response based on the AI service
    if ($ai_service === 'chatgpt' && isset($data['choices'][0]['text'])) {
        wp_send_json_success(['message' => 'ChatGPT API connected successfully. Response: ' . $data['choices'][0]['text']]);
    } elseif ($ai_service === 'gemini' && isset($data['status']) && $data['status'] === 'success') {
        wp_send_json_success(['message' => 'Gemini API connected successfully.']);
    } elseif ($ai_service === 'aimlapi' && isset($data['status']) && $data['status'] === 'success') {
        wp_send_json_success(['message' => 'AIMLAPI connected successfully.']);
    } else {
        wp_send_json_error(['message' => 'Unexpected response from ' . $ai_service . '.']);
    }
}

add_action('wp_ajax_bmi_pro_test_api', 'bmi_pro_test_api_handler');
