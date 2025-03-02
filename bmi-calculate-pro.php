<?php
/*
Plugin Name: BMI Calculate Pro
Description: A professional BMI calculator with AI-powered recommendations, customizable settings, and database integration.
Version: 1.1.1
Author: Your Name
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define plugin constants
define('BMI_PRO_VERSION', '1.1.1');
define('BMI_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BMI_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once BMI_PRO_PLUGIN_DIR . 'includes/class-bmi-logger.php';
require_once BMI_PRO_PLUGIN_DIR . 'includes/form-handler.php';
require_once BMI_PRO_PLUGIN_DIR . 'includes/admin-settings.php';
require_once BMI_PRO_PLUGIN_DIR . 'includes/dashboard-analytics.php';
require_once BMI_PRO_PLUGIN_DIR . 'includes/class-bmi-update-checker.php';

// Shortcode for the BMI Calculator
function bmi_calculate_pro_shortcode() {
    ob_start();
    ?>
    <div class="calculator-container">
<div class="form-section">
    <h2 class="section-title"><?php echo __('Personal Details', 'bmi-pro'); ?></h2>
    <form id="bmi-calculator-form" method="POST" action="">
        
        <!-- Row 1: Name, Email -->
        <div class="input-row">
            <div class="input-item">
                <label for="name"><?php echo __('Name', 'bmi-pro'); ?></label>
                <input type="text" id="name" name="name" placeholder="<?php echo __('Enter your name', 'bmi-pro'); ?>" required>
            </div>
            <div class="input-item">
                <label for="email"><?php echo __('Email', 'bmi-pro'); ?></label>
                <input type="email" id="email" name="email" placeholder="<?php echo __('Enter your email', 'bmi-pro'); ?>" required>
            </div>
        </div>

        <!-- Row 2: Phone, Age -->
        <div class="input-row">
            <div class="input-item">
                <label for="phone"><?php echo __('Phone', 'bmi-pro'); ?></label>
                <input type="text" id="phone" name="phone" placeholder="<?php echo __('Enter your phone number', 'bmi-pro'); ?>" required>
            </div>
            <div class="input-item">
                <label for="age"><?php echo __('Age', 'bmi-pro'); ?></label>
                <input type="number" id="age" name="age" placeholder="<?php echo __('Enter your age', 'bmi-pro'); ?>" required>
            </div>
        </div>

        <!-- Row 3: Gender, Height -->
        <div class="input-row">
            <div class="input-item">
                <label for="gender"><?php echo __('Gender', 'bmi-pro'); ?></label>
                <select id="gender" name="gender" required>
                    <option value="male"><?php echo __('Male', 'bmi-pro'); ?></option>
                    <option value="female"><?php echo __('Female', 'bmi-pro'); ?></option>
                </select>
            </div>
            <div class="input-item">
                <label for="height_cm"><?php echo __('Height (in cm)', 'bmi-pro'); ?></label>
                <input type="number" id="height_cm" name="height_cm" placeholder="<?php echo __('Enter your height', 'bmi-pro'); ?>" required>
            </div>
        </div>

        <!-- Row 4: Weight, Fitness Goal -->
        <div class="input-row">
            <div class="input-item">
                <label for="weight_kg"><?php echo __('Weight (in kg)', 'bmi-pro'); ?></label>
                <input type="number" id="weight_kg" name="weight_kg" placeholder="<?php echo __('Enter your weight', 'bmi-pro'); ?>" required>
            </div>
            <div class="input-item">
                <label for="fitness_goal"><?php echo __('Fitness Goal', 'bmi-pro'); ?></label>
                <select id="fitness_goal" name="fitness_goal" required>
                    <option value="weight_loss"><?php echo __('Weight Loss', 'bmi-pro'); ?></option>
                    <option value="muscle_gain"><?php echo __('Muscle Gain', 'bmi-pro'); ?></option>
                </select>
            </div>
        </div>

        <!-- Row 5: Calories per Day, Diet Preference -->
        <div class="input-row">
            <div class="input-item">
                <label for="calories"><?php echo __('Calories per day', 'bmi-pro'); ?></label>
                <input type="number" id="calories" name="calories" placeholder="<?php echo __('Enter your daily calorie intake', 'bmi-pro'); ?>" required>
            </div>
            <div class="input-item">
                <label for="diet_preference"><?php echo __('Diet Preference', 'bmi-pro'); ?></label>
                <select id="diet_preference" name="diet_preference" required>
                    <option value="vegetarian"><?php echo __('Vegetarian', 'bmi-pro'); ?></option>
                    <option value="keto"><?php echo __('Keto', 'bmi-pro'); ?></option>
                    <option value="standard"><?php echo __('Standard', 'bmi-pro'); ?></option>
                </select>
            </div>
        </div>

        <!-- Row 6: Activity Level, Gym Sessions per Week -->
        <div class="input-row">
            <div class="input-item">
                <label for="activity_level"><?php echo __('Activity Level', 'bmi-pro'); ?></label>
                <select id="activity_level" name="activity_level" required>
                    <option value="sedentary"><?php echo __('Sedentary', 'bmi-pro'); ?></option>
                    <option value="active"><?php echo __('Active', 'bmi-pro'); ?></option>
                    <option value="very_active"><?php echo __('Very Active', 'bmi-pro'); ?></option>
                </select>
            </div>
            <div class="input-item">
                <label for="gym_sessions"><?php echo __('Gym Sessions per Week', 'bmi-pro'); ?></label>
                <input type="number" id="gym_sessions" name="gym_sessions" placeholder="<?php echo __('Enter number of gym sessions per week', 'bmi-pro'); ?>" required>
            </div>
        </div>

        <!-- Row 7: Time in Gym (hours), Sleep Hours -->
        <div class="input-row">
            <div class="input-item">
                <label for="time_in_gym"><?php echo __('Time in Gym (hours per session)', 'bmi-pro'); ?></label>
                <input type="number" id="time_in_gym" name="time_in_gym" placeholder="<?php echo __('Enter the hours spent in gym per session', 'bmi-pro'); ?>" required>
            </div>
            <div class="input-item">
                <label for="sleep_hours"><?php echo __('Hours of Sleep', 'bmi-pro'); ?></label>
                <input type="number" id="sleep_hours" name="sleep_hours" placeholder="<?php echo __('Enter your sleep hours per day', 'bmi-pro'); ?>" required>
            </div>
        </div>

        <button type="submit" class="submit-btn"><?php echo __('Calculate Now', 'bmi-pro'); ?></button>
        <input type="hidden" id="bmi-nonce" name="bmi_nonce" value="<?php echo wp_create_nonce('bmi_pro_action'); ?>">
    </form>
</div>


        <!-- Results Section -->
        <div class="result-section">
            <h2><?php echo __('Results', 'bmi-pro'); ?></h2>
            <div id="bmi-gauge-container" style="position: relative; width: 300px; height: 300px; margin: 0 auto;">
                    <canvas id="bmi-gauge" width="300" height="300"></canvas>
                </div>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('Category', 'bmi-pro'); ?></th>
                        <th><?php echo __('Details', 'bmi-pro'); ?></th>
                    </tr>
                </thead>
                <tbody id="bmi-results">
                    <tr>
                        <td><?php echo __('BMI:', 'bmi-pro'); ?></td>
                        <td id="bmi-value">0</td>
                    </tr>
                    <tr>
                        <td><?php echo __('BFP:', 'bmi-pro'); ?></td>
                        <td id="bfp-value">0</td>
                    </tr>
                    <tr>
                        <td><?php echo __('BMR:', 'bmi-pro'); ?></td>
                        <td id="bmr-value">0</td>
                    </tr>
                    <tr>
                        <td><?php echo __('Ideal Weight:', 'bmi-pro'); ?></td>
                        <td id="ideal-weight">0</td>
                    </tr>
                    <tr>
                        <td><?php echo __('Recommendations:', 'bmi-pro'); ?></td>
                        <td id="recommendations"><?php echo __('N/A', 'bmi-pro'); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Progress Tracking Section -->
            <div class="progress-section">
                <h3><?php echo __('BMI Progress Tracking', 'bmi-pro'); ?></h3>
                <div id="bmi-progress-indicator" class="progress-indicator-container"></div>
                <div class="chart-container" style="position: relative; height: 300px; margin: 20px auto;">
                    <canvas id="bmi-progress-chart"></canvas>
                </div>
                <p class="progress-note"><?php echo __('Your BMI progress is tracked automatically. Check back after your next measurement to see your progress!', 'bmi-pro'); ?></p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bmi_calculate_pro', 'bmi_calculate_pro_shortcode');

// Enqueue CSS and JavaScript
if (!function_exists('bmi_enqueue_styles_scripts')) {
    function bmi_enqueue_styles_scripts() {
        wp_enqueue_style('bmi-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], time(), 'all');
        wp_enqueue_style('bmi-progress-tracker-style', plugin_dir_url(__FILE__) . 'assets/css/progress-tracker.css', [], time(), 'all');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
        wp_enqueue_script('bmi-gauge-script', plugin_dir_url(__FILE__) . 'assets/js/bmi-gauge.js', ['chart-js'], null, true);
        wp_enqueue_script('bmi-progress-tracker', plugin_dir_url(__FILE__) . 'assets/js/bmi-progress-tracker.js', ['chart-js'], null, true);
        wp_enqueue_script('bmi-ajax-script', plugin_dir_url(__FILE__) . 'assets/js/bmi-ajax.js', ['jquery', 'chart-js', 'bmi-gauge-script', 'bmi-progress-tracker'], null, true);

        wp_localize_script('bmi-ajax-script', 'bmi_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('bmi_pro_action'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'bmi_enqueue_styles_scripts');

function bmi_pro_create_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // BMI Data Table
    $bmi_table = $wpdb->prefix . 'bmi_pro_data';
    $sql = "CREATE TABLE $bmi_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) DEFAULT 0,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(15),
        age int NOT NULL,
        gender varchar(10) NOT NULL,
        height float NOT NULL,
        weight float NOT NULL,
        bmi float NOT NULL,
        bfp float NOT NULL,
        bmr float NOT NULL,
        activity_level varchar(20),
        emotional_state text,
        fitness_goal varchar(20),
        calories int,
        diet_preference varchar(20),
        gym_sessions int,
        time_in_gym float,
        sleep_hours int,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // SMTP Configuration Table
    $smtp_table = $wpdb->prefix . 'bmi_pro_smtp';
    $sql = "CREATE TABLE $smtp_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        host varchar(255) NOT NULL,
        port int NOT NULL,
        username varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        from_email varchar(255) NOT NULL,
        from_name varchar(255) NOT NULL,
        encryption varchar(10) DEFAULT 'tls',
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // API Configuration Table
    $api_table = $wpdb->prefix . 'bmi_pro_api';
    $sql = "CREATE TABLE $api_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        service_name varchar(100) NOT NULL,
        api_key varchar(255) NOT NULL,
        api_secret varchar(255),
        endpoint_url varchar(255),
        service_type varchar(50) NOT NULL,
        settings text,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'bmi_pro_create_table');


