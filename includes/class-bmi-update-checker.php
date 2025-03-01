<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BMI_Update_Checker {
    private static $instance = null;
    private $current_version;
    private $plugin_slug;
    private $plugin_basename;
    private $github_repo;
    
    private function __construct() {
        $this->current_version = '1.0';
        $this->plugin_slug = 'bmi-calculate-pro';
        $this->plugin_basename = 'bmi-calculate-pro/bmi-calculate-pro.php';
        $this->github_repo = 'salimmous/bmi-6'; // GitHub repository for updates
        
        // Add filters for the update checker
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get the latest release from GitHub
        $response = wp_remote_get(
            sprintf('https://api.github.com/repos/%s/releases/latest', $this->github_repo),
            array('headers' => array('Accept' => 'application/vnd.github.v3+json'))
        );
        
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $release_info = json_decode(wp_remote_retrieve_body($response));
            
            if (version_compare($this->current_version, ltrim($release_info->tag_name, 'v'), '<')) {
                $transient->response[$this->plugin_basename] = (object) array(
                    'slug' => $this->plugin_slug,
                    'new_version' => ltrim($release_info->tag_name, 'v'),
                    'package' => $release_info->zipball_url,
                    'tested' => get_bloginfo('version'),
                    'requires' => '5.0',
                    'url' => sprintf('https://github.com/%s', $this->github_repo)
                );
            }
        }
        
        return $transient;
    }
    
    public function plugin_info($result, $action, $args) {
        // Check if this request is for our plugin
        if ('plugin_information' !== $action || $this->plugin_slug !== $args->slug) {
            return $result;
        }
        
        $response = wp_remote_get(
            sprintf('https://api.github.com/repos/%s/releases/latest', $this->github_repo),
            array('headers' => array('Accept' => 'application/vnd.github.v3+json'))
        );
        
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $release_info = json_decode(wp_remote_retrieve_body($response));
            return (object) array(
                'name' => 'BMI Calculate Pro',
                'slug' => $this->plugin_slug,
                'version' => ltrim($release_info->tag_name, 'v'),
                'author' => sprintf('<a href="https://github.com/%s">%s</a>', $this->github_repo, explode('/', $this->github_repo)[0]),
                'requires' => '5.0',
                'tested' => get_bloginfo('version'),
                'last_updated' => $release_info->published_at,
                'sections' => array(
                    'description' => $release_info->body,
                    'changelog' => $release_info->body
                ),
                'download_link' => $release_info->zipball_url
            );
        }
        
        return $result;
    }
    
    public function after_update($upgrader_object, $options) {
        if ('update' === $options['action'] && 'plugin' === $options['type']) {
            // Clear any caches
            delete_site_transient('update_plugins');
            wp_cache_delete('plugins', 'plugins');
            
            // Log the update
            BMI_Logger::get_instance()->log('Plugin updated successfully', 'info', array(
                'version' => $this->current_version
            ));
        }
    }
}

// Initialize the update checker
add_action('init', array('BMI_Update_Checker', 'get_instance'));