<?php
if (!defined('ABSPATH')) exit;

class BMI_AI_Recommendations {
    private $logger;
    private $api_key;
    private $ai_service;

    public function __construct() {
        $this->logger = BMI_Logger::get_instance();
        $this->api_key = get_option('bmi_ai_api_key', '');
        $this->ai_service = get_option('bmi_ai_service', 'chatgpt');
    }

    public function get_recommendations($bmi_data) {
        if (empty($this->api_key)) {
            $this->logger->log_error('AI API key is not configured');
            return array('error' => 'AI service is not properly configured');
        }

        $prompt = $this->build_prompt($bmi_data);
        
        try {
            $response = $this->make_ai_request($prompt);
            $this->logger->log_api_request('ai_recommendations', $prompt, $response);
            return $this->parse_ai_response($response);
        } catch (Exception $e) {
            $this->logger->log_error('AI recommendation error', array('error' => $e->getMessage()));
            return array('error' => 'Failed to generate recommendations');
        }
    }

    private function build_prompt($bmi_data) {
        return sprintf(
            "Based on the following health metrics, provide personalized health recommendations:\n" .
            "- BMI: %.1f\n" .
            "- Age: %d\n" .
            "- Gender: %s\n" .
            "- Activity Level: %s\n" .
            "Please provide specific, actionable recommendations for diet, exercise, and lifestyle changes.",
            $bmi_data['bmi'],
            $bmi_data['age'],
            $bmi_data['gender'],
            $bmi_data['activity_level']
        );
    }

    private function make_ai_request($prompt) {
        $endpoint = $this->get_ai_endpoint();
        $payload = $this->prepare_ai_payload($prompt);

        $headers = array('Content-Type' => 'application/json');
        if ($this->ai_service === 'chatgpt') {
            $headers['Authorization'] = 'Bearer ' . $this->api_key;
        } elseif ($this->ai_service === 'gemini') {
            $endpoint .= '?key=' . $this->api_key;
        }

        $response = wp_remote_post($endpoint, array(
            'headers' => $headers,
            'body' => json_encode($payload),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_ai_endpoint() {
        switch ($this->ai_service) {
            case 'chatgpt':
                return 'https://api.openai.com/v1/completions';
            case 'gemini':
                return 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent';
            default:
                throw new Exception('Unsupported AI service');
        }
    }

    private function prepare_ai_payload($prompt) {
        switch ($this->ai_service) {
            case 'chatgpt':
                return array(
                    'model' => 'text-davinci-003',
                    'prompt' => $prompt,
                    'max_tokens' => 500,
                    'temperature' => 0.7
                );
            case 'gemini':
                return array(
                    'contents' => array(
                        array(
                            'parts' => array(
                                array('text' => $prompt)
                            )
                        )
                    ),
                    'generationConfig' => array(
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500
                    )
                );
            default:
                throw new Exception('Unsupported AI service');
        }
    }

    private function parse_ai_response($response) {
        switch ($this->ai_service) {
            case 'chatgpt':
                return array(
                    'recommendations' => $response['choices'][0]['text'] ?? ''
                );
            case 'gemini':
                return array(
                    'recommendations' => $response['candidates'][0]['content']['text'] ?? ''
                );
            default:
                throw new Exception('Unsupported AI service');
        }
    }
}