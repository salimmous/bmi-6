<?php
if (!defined('ABSPATH')) exit;

// Check if the function is already defined
if (!function_exists('bmi_pro_register_dashboard_widget')) {
    // Register a dashboard widget
    function bmi_pro_register_dashboard_widget() {
        wp_add_dashboard_widget(
            'bmi_pro_dashboard_widget',
            'BMI Pro Analytics',
            'bmi_pro_dashboard_widget_content'
        );
    }
    add_action('wp_dashboard_setup', 'bmi_pro_register_dashboard_widget');
}

// Widget content
if (!function_exists('bmi_pro_dashboard_widget_content')) {
    function bmi_pro_dashboard_widget_content() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bmi_pro_data';

        // Fetch stats
        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $average_bmi = $wpdb->get_var("SELECT AVG(bmi) FROM $table_name");
        $underweight_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE bmi < 18.5");
        $normal_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE bmi BETWEEN 18.5 AND 24.9");
        $overweight_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE bmi BETWEEN 25 AND 29.9");
        $obese_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE bmi >= 30");

        // Display stats
        echo '<p><strong>Total Records:</strong> ' . intval($total_records) . '</p>';
        echo '<p><strong>Average BMI:</strong> ' . round(floatval($average_bmi), 2) . '</p>';
        echo '<p><strong>Underweight:</strong> ' . intval($underweight_count) . '</p>';
        echo '<p><strong>Normal:</strong> ' . intval($normal_count) . '</p>';
        echo '<p><strong>Overweight:</strong> ' . intval($overweight_count) . '</p>';
        echo '<p><strong>Obese:</strong> ' . intval($obese_count) . '</p>';
    }
}
