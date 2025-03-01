<?php
if (!defined('ABSPATH')) exit;

class BMI_Logger {
    private static $instance = null;
    private $log_file;
    private $log_enabled;

    private function __construct() {
        $this->log_file = plugin_dir_path(__FILE__) . 'logs/bmi-pro.log';
        $this->log_enabled = get_option('bmi_enable_logging', true);
        $this->init_log_file();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_log_file() {
        if (!file_exists(dirname($this->log_file))) {
            wp_mkdir_p(dirname($this->log_file));
        }
        if (!file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }

    public function log($message, $level = 'info', $context = array()) {
        if (!$this->log_enabled) return;

        $timestamp = current_time('Y-m-d H:i:s');
        $level = strtoupper($level);
        $context_str = !empty($context) ? json_encode($context) : '';
        
        $log_entry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            $level,
            $message,
            $context_str
        );

        error_log($log_entry, 3, $this->log_file);
    }

    public function log_api_request($endpoint, $payload, $response) {
        $context = array(
            'endpoint' => $endpoint,
            'payload' => $payload,
            'response' => $response
        );
        $this->log('API Request/Response', 'debug', $context);
    }

    public function log_bmi_calculation($user_data, $results) {
        $context = array(
            'user_data' => $user_data,
            'results' => $results
        );
        $this->log('BMI Calculation', 'info', $context);
    }

    public function log_error($message, $error_data = array()) {
        $this->log($message, 'error', $error_data);
    }

    public function clear_logs() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
            $this->init_log_file();
        }
    }

    public function get_logs($lines = 100) {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $logs = file($this->log_file);
        if (!$logs) return array();

        return array_slice(array_reverse($logs), 0, $lines);
    }
}