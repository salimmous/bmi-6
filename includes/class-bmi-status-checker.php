<?php
if (!defined('ABSPATH')) exit;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ABSPATH . 'wp-includes/PHPMailer/PHPMailer.php';
require_once ABSPATH . 'wp-includes/PHPMailer/SMTP.php';
require_once ABSPATH . 'wp-includes/PHPMailer/Exception.php';

class BMI_Status_Checker {
    private static $instance = null;
    private $logger;

    private function __construct() {
        $this->logger = BMI_Logger::get_instance();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function check_ai_api_status() {
        $api_key = get_option('bmi_ai_api_key', '');
        if (empty($api_key)) {
            return array(
                'status' => 'error',
                'message' => 'API key not configured'
            );
        }

        try {
            $endpoint = $this->get_ai_endpoint();
            $response = wp_remote_get($endpoint, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 5
            ));

            if (is_wp_error($response)) {
                $this->logger->log_error('AI API check failed', array('error' => $response->get_error_message()));
                return array(
                    'status' => 'error',
                    'message' => $response->get_error_message()
                );
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code === 200) {
                $this->logger->log('AI API check successful', 'info');
                return array(
                    'status' => 'success',
                    'message' => 'API connection successful'
                );
            } else {
                $this->logger->log_error('AI API check failed', array('code' => $response_code));
                return array(
                    'status' => 'error',
                    'message' => 'API returned status code: ' . $response_code
                );
            }
        } catch (Exception $e) {
            $this->logger->log_error('AI API check exception', array('error' => $e->getMessage()));
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    public function check_smtp_status() {
        $smtp_host = get_option('bmi_smtp_host', '');
        $smtp_port = get_option('bmi_smtp_port', '');
        $smtp_username = get_option('bmi_smtp_username', '');
        $smtp_password = get_option('bmi_smtp_password', '');

        if (empty($smtp_host) || empty($smtp_port) || empty($smtp_username) || empty($smtp_password)) {
            return array(
                'status' => 'error',
                'message' => 'SMTP settings not configured'
            );
        }

        try {
            $phpmailer = new PHPMailer(true);
            $phpmailer->isSMTP();
            $phpmailer->Host = $smtp_host;
            $phpmailer->Port = $smtp_port;
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_username;
            $phpmailer->Password = $smtp_password;
            $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // Disable debug output to prevent raw error messages
            $phpmailer->SMTPDebug = 0;

            if ($phpmailer->smtpConnect()) {
                $phpmailer->smtpClose();
                $this->logger->log('SMTP check successful', 'info');
                return array(
                    'status' => 'success',
                    'message' => 'SMTP connection successful'
                );
            } else {
                $this->logger->log_error('SMTP connection failed');
                return array(
                    'status' => 'error',
                    'message' => 'Failed to connect to SMTP server. Please check your credentials.'
                );
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $this->logger->log_error('SMTP check exception', array('error' => $error_message));
            
            // Provide a more user-friendly error message
            if (strpos($error_message, 'Could not authenticate') !== false) {
                $error_message = 'Authentication failed. Please check your username and password.';
            } elseif (strpos($error_message, 'connect()') !== false) {
                $error_message = 'Connection failed. Please check your host and port settings.';
            }
            
            return array(
                'status' => 'error',
                'message' => $error_message
            );
        }
    }

    private function get_ai_endpoint() {
        $ai_service = get_option('bmi_ai_service', 'chatgpt');
        switch ($ai_service) {
            case 'chatgpt':
                return 'https://api.openai.com/v1/chat/completions';
            default:
                return '';
        }
    }
}