<?php

if (!defined('ABSPATH')) exit; // Prevent direct access

// Add BMI Pro menu and submenus
function bmi_pro_register_menu() {
    add_menu_page(
        'BMI Pro Dashboard',
        'BMI Pro',
        'manage_options',
        'bmi-pro-dashboard',
        'bmi_pro_dashboard_page',
        'dashicons-chart-area',
        2
    );

    add_submenu_page(
        'bmi-pro-dashboard',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'bmi-pro-dashboard',
        'bmi_pro_dashboard_page'
    );

    add_submenu_page(
        'bmi-pro-dashboard',
        'Settings',
        'Settings',
        'manage_options',
        'bmi-pro-settings',
        'bmi_pro_settings_page'
    );

    add_submenu_page(
        'bmi-pro-dashboard',
        'Logs',
        'User Logs',
        'manage_options',
        'bmi-pro-logs',
        'bmi_pro_logs_page'
    );
}
add_action('admin_menu', 'bmi_pro_register_menu');

// Dashboard Page Callback
function bmi_pro_dashboard_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bmi_pro_data';
    $user_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 20");

    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-chart-area"></span> BMI Pro Dashboard</h1>
        <p>Welcome to the BMI Pro dashboard. From here, you can monitor analytics and user data.</p>

        <h2>Analytics</h2>
        <div style="display: flex; gap: 20px; margin-top: 20px;">
            <div style="flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                <h3>Total Users</h3>
                <p><strong><?php echo bmi_pro_get_total_users(); ?></strong></p>
            </div>
            <div style="flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                <h3>Recent Calculations</h3>
                <p><strong><?php echo bmi_pro_get_recent_calculations(); ?></strong></p>
            </div>
            <div style="flex: 1; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                <h3>Average BMI</h3>
                <p><strong><?php echo bmi_pro_get_average_bmi(); ?></strong></p>
            </div>
        </div>

        <h2>User Data</h2>
        <div style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Height</th>
                        <th>Weight</th>
                        <th>BMI</th>
                        <th>BFP</th>
                        <th>BMR</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($user_data)) : ?>
                        <?php foreach ($user_data as $user) : ?>
                            <tr>
                                <td><?php echo esc_html($user->name ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->email ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->phone ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->age ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->gender ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->height ?? 'N/A'); ?> cm</td>
                                <td><?php echo esc_html($user->weight ?? 'N/A'); ?> kg</td>
                                <td><?php echo esc_html($user->bmi ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->bfp ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->bmr ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($user->created_at ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="11" style="text-align: center;">No data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Settings Page Callback
function bmi_pro_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('bmi_ai_api_key', sanitize_text_field($_POST['bmi_ai_api_key']));
        update_option('bmi_ai_service', sanitize_text_field($_POST['bmi_ai_service']));
        update_option('bmi_smtp_host', sanitize_text_field($_POST['bmi_smtp_host']));
        update_option('bmi_smtp_port', sanitize_text_field($_POST['bmi_smtp_port']));
        update_option('bmi_smtp_username', sanitize_text_field($_POST['bmi_smtp_username']));
        update_option('bmi_smtp_password', sanitize_text_field($_POST['bmi_smtp_password']));
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    $api_key = get_option('bmi_ai_api_key', '');
    $ai_service = get_option('bmi_ai_service', 'chatgpt');
    $smtp_host = get_option('bmi_smtp_host', '');
    $smtp_port = get_option('bmi_smtp_port', '587');
    $smtp_username = get_option('bmi_smtp_username', '');
    $smtp_password = get_option('bmi_smtp_password', '');

    ?>

    <div class="wrap">
        <h1>BMI Pro Settings</h1>

        <!-- Status Dashboard -->
        <div class="status-dashboard" style="margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
            <h2>System Status</h2>
            <?php
            require_once plugin_dir_path(__FILE__) . 'class-bmi-status-checker.php';
            $status_checker = BMI_Status_Checker::get_instance();
            
            $ai_status = $status_checker->check_ai_api_status();
            $smtp_status = $status_checker->check_smtp_status();
            ?>
            
            <div class="status-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- AI API Status -->
                <div class="status-card" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                    <h3>AI API Status</h3>
                    <div class="status-indicator <?php echo $ai_status['status']; ?>">
                        <span class="dashicons <?php echo $ai_status['status'] === 'success' ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                        <?php echo esc_html($ai_status['message']); ?>
                    </div>
                </div>

                <!-- SMTP Status -->
                <div class="status-card" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                    <h3>SMTP Status</h3>
                    <div class="status-indicator <?php echo $smtp_status['status']; ?>">
                        <span class="dashicons <?php echo $smtp_status['status'] === 'success' ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                        <?php echo esc_html($smtp_status['message']); ?>
                    </div>
                </div>
            </div>
        </div>

        <form method="post">
            <h2>AI Service Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">AI Service</th>
                    <td>
                        <select name="bmi_ai_service" id="bmi_ai_service">
                            <option value="chatgpt" <?php selected($ai_service, 'chatgpt'); ?>>ChatGPT</option>
                            <option value="gemini" <?php selected($ai_service, 'gemini'); ?>>Gemini</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Key</th>
                    <td>
                        <input type="password" name="bmi_ai_api_key" id="bmi_ai_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <button type="button" class="button button-secondary" id="test_ai_connection">Test AI Connection</button>
                        <span id="ai_connection_status"></span>
                    </td>
                </tr>
            </table>

            <h2>SMTP Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">SMTP Host</th>
                    <td>
                        <input type="text" name="bmi_smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text" placeholder="e.g., smtp.gmail.com">
                    </td>
                </tr>
                <tr>
                    <th scope="row">SMTP Port</th>
                    <td>
                        <input type="text" name="bmi_smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="regular-text" placeholder="587">
                    </td>
                </tr>
                <tr>
                    <th scope="row">SMTP Username</th>
                    <td>
                        <input type="text" name="bmi_smtp_username" value="<?php echo esc_attr($smtp_username); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">SMTP Password</th>
                    <td>
                        <input type="password" name="bmi_smtp_password" value="<?php echo esc_attr($smtp_password); ?>" class="regular-text">
                        <button type="button" class="button button-secondary" id="test_smtp_connection">Test SMTP Connection</button>
                        <span id="smtp_connection_status"></span>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <style>
            .status-indicator { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 4px; }
            .status-indicator.success { background: #e7f5ea; color: #0a6b1d; }
            .status-indicator.error { background: #fde8e8; color: #9b1c1c; }
            .dashicons { font-size: 20px; }
        </style>

        <!-- Enhanced Log Viewer -->
        <div class="log-viewer" style="margin-top: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px;">
            <h2>System Logs</h2>
            <div class="log-filters" style="margin-bottom: 15px;">
                <select id="log-level-filter">
                    <option value="all">All Levels</option>
                    <option value="INFO">Info</option>
                    <option value="ERROR">Error</option>
                    <option value="DEBUG">Debug</option>
                </select>
                <button type="button" class="button" id="refresh-logs">Refresh Logs</button>
                <button type="button" class="button" id="clear-logs">Clear Logs</button>
            </div>
            <div class="log-entries" style="background: #f5f5f5; padding: 15px; max-height: 500px; overflow-y: auto;">
                <?php
                $logger = BMI_Logger::get_instance();
                $logs = $logger->get_logs(100);
                foreach ($logs as $log) {
                    echo '<pre class="log-entry" style="margin: 5px 0; padding: 5px; border-bottom: 1px solid #ddd;">' . esc_html($log) . '</pre>';
                }
                ?>
            </div>
        </div>

        <div class="bmi-admin-section">
            <h2>API Connection Test</h2>
            <p>Use this section to test your AI API connection. Select a service and click the test button to verify the connection.</p>
            <!-- The test interface will be dynamically inserted here by api-test.js -->
        </div>
    </div>
    <?php
}

// Logs Page Callback
function bmi_pro_logs_page() {
    $logger = BMI_Logger::get_instance();
    $logs = $logger->get_logs(1000); // Get last 1000 log entries
    
    ?>
    <div class="wrap">
        <h1>BMI Pro Logs</h1>
        <p>View the logs for all user interactions and calculations here.</p>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="post">
                    <?php wp_nonce_field('bmi_clear_logs', 'bmi_logs_nonce'); ?>
                    <input type="submit" name="clear_logs" class="button" value="Clear Logs">
                </form>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <div class="log-filters" style="margin-bottom: 20px;">
                <select id="log-level-filter" style="margin-right: 10px;">
                    <option value="all">All Levels</option>
                    <option value="INFO">Info</option>
                    <option value="DEBUG">Debug</option>
                    <option value="ERROR">Error</option>
                </select>
                <input type="text" id="log-search" placeholder="Search logs..." style="width: 200px;">
            </div>
            
            <div class="log-container" style="max-height: 600px; overflow-y: auto;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log) :
                            if (preg_match('/\[(.*?)\]\s*(\w+):\s*(.*)/', $log, $matches)) :
                                $timestamp = $matches[1];
                                $level = $matches[2];
                                $message_and_context = $matches[3];
                                
                                // Split message and context if exists
                                $context = '';
                                if (strpos($message_and_context, '{') !== false) {
                                    $parts = explode('{', $message_and_context, 2);
                                    $message = trim($parts[0]);
                                    $context = '{' . $parts[1];
                                } else {
                                    $message = trim($message_and_context);
                                }
                                ?>
                                <tr class="log-entry" data-level="<?php echo esc_attr($level); ?>">
                                    <td><?php echo esc_html($timestamp); ?></td>
                                    <td><span class="log-level <?php echo strtolower(esc_attr($level)); ?>"><?php echo esc_html($level); ?></span></td>
                                    <td><?php echo esc_html($message); ?></td>
                                    <td>
                                        <?php if ($context) : ?>
                                            <pre style="margin: 0; white-space: pre-wrap;"><?php echo esc_html(json_encode(json_decode($context), JSON_PRETTY_PRINT)); ?></pre>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif;
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            .log-level {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            .log-level.info { background: #e8f5e9; color: #2e7d32; }
            .log-level.debug { background: #e3f2fd; color: #1565c0; }
            .log-level.error { background: #ffebee; color: #c62828; }
            .log-container table { border-collapse: collapse; width: 100%; }
            .log-container th, .log-container td { padding: 12px; text-align: left; }
            .log-container th { background: #f5f5f5; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Level filter
            $('#log-level-filter').on('change', function() {
                var level = $(this).val();
                if (level === 'all') {
                    $('.log-entry').show();
                } else {
                    $('.log-entry').hide();
                    $('.log-entry[data-level="' + level + '"]').show();
                }
            });

            // Search filter
            $('#log-search').on('keyup', function() {
                var search = $(this).val().toLowerCase();
                $('.log-entry').each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(search) > -1);
                });
            });
        });
        </script>
    </div>
    <?php

    // Handle log clearing
    if (isset($_POST['clear_logs']) && check_admin_referer('bmi_clear_logs', 'bmi_logs_nonce')) {
        $logger->clear_logs();
        echo '<div class="notice notice-success"><p>Logs cleared successfully!</p></div>';
    }
}

// Helper Functions
function bmi_pro_get_total_users() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bmi_pro_data';
    return intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
}

function bmi_pro_get_recent_calculations() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bmi_pro_data';
    return intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"));
}

function bmi_pro_get_average_bmi() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bmi_pro_data';
    return round($wpdb->get_var("SELECT AVG(bmi) FROM $table_name"), 2);
}



// Register settings for customization and AI API
function bmi_pro_register_customization_settings() {
    register_setting('bmi_pro_settings', 'bmi_header_color');
    register_setting('bmi_pro_settings', 'bmi_button_color');
    register_setting('bmi_pro_settings', 'bmi_text_color');
    register_setting('bmi_pro_settings', 'bmi_ai_service');
    register_setting('bmi_pro_settings', 'bmi_ai_api_key');
    add_settings_field('bmi_font_family', 'Font Family', 'bmi_pro_field_font_family', 'bmi-pro-settings', 'bmi_pro_customization');
    add_settings_field('bmi_font_size', 'Font Size', 'bmi_pro_field_font_size', 'bmi-pro-settings', 'bmi_pro_customization');


    add_settings_section('bmi_pro_customization', 'Customization Settings', null, 'bmi-pro-settings');
    add_settings_section('bmi_pro_ai', 'AI Recommendations Settings', null, 'bmi-pro-settings');

    add_settings_field('bmi_header_color', 'Header Color', 'bmi_pro_field_header_color', 'bmi-pro-settings', 'bmi_pro_customization');
    add_settings_field('bmi_button_color', 'Button Color', 'bmi_pro_field_button_color', 'bmi-pro-settings', 'bmi_pro_customization');
    add_settings_field('bmi_text_color', 'Text Color', 'bmi_pro_field_text_color', 'bmi-pro-settings', 'bmi_pro_customization');
    add_settings_field('bmi_ai_service', 'AI Service', 'bmi_pro_field_ai_service', 'bmi-pro-settings', 'bmi_pro_ai');
    add_settings_field('bmi_ai_api_key', 'AI API Key', 'bmi_pro_field_ai_api_key', 'bmi-pro-settings', 'bmi_pro_ai');
    
    add_settings_field(
    'bmi_font_family',
    __('Font Family', 'bmi-pro'),
    'bmi_pro_field_font_family',
    'bmi-pro-settings',
    'bmi_pro_customization'
);

}

// Render Customization Fields
function bmi_pro_field_header_color() {
    $value = get_option('bmi_header_color', '#000000');
    echo '<input type="color" name="bmi_header_color" value="' . esc_attr($value) . '">';
}

function bmi_pro_field_button_color() {
    $value = get_option('bmi_button_color', '#007bff');
    echo '<input type="color" name="bmi_button_color" value="' . esc_attr($value) . '">';
}

function bmi_pro_field_text_color() {
    $value = get_option('bmi_text_color', '#333333');
    echo '<input type="color" name="bmi_text_color" value="' . esc_attr($value) . '">';
}

function bmi_pro_field_ai_service() {
    $value = get_option('bmi_ai_service', 'chatgpt');
    echo '<select name="bmi_ai_service">
        <option value="chatgpt" ' . selected($value, 'chatgpt', false) . '>ChatGPT</option>
        <option value="gemini" ' . selected($value, 'gemini', false) . '>Gemini</option>
        <option value="aimlapi" ' . selected($value, 'aimlapi', false) . '>AIMLAPI</option>
    </select>';
}

function bmi_pro_field_ai_api_key() {
    $value = get_option('bmi_ai_api_key', '');
    echo '<input type="text" name="bmi_ai_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

// AJAX handler for API testing
function bmi_pro_test_api_handler() {
    $api_key = sanitize_text_field($_POST['api_key']);
    $service = sanitize_text_field($_POST['service']);

    if (empty($api_key)) {
        wp_send_json_error(['message' => 'API key is missing.']);
    }

    // Test the API connection based on the service
    $test_result = false;
    $error_message = '';

    switch($service) {
        case 'chatgpt':
            $test_result = bmi_pro_test_openai_connection($api_key);
            break;
        case 'gemini':
            $test_result = bmi_pro_test_gemini_connection($api_key);
            break;
    }

    if ($test_result) {
        wp_send_json_success(['message' => "Successfully connected to {$service} API"]);
    } else {
        wp_send_json_error(['message' => "Failed to connect to {$service} API. {$error_message}"]);
    }
}

function bmi_pro_test_smtp_handler() {
    $host = sanitize_text_field($_POST['host']);
    $port = sanitize_text_field($_POST['port']);
    $username = sanitize_text_field($_POST['username']);
    $password = sanitize_text_field($_POST['password']);

    if (empty($host) || empty($port) || empty($username) || empty($password)) {
        wp_send_json_error(['message' => 'All SMTP fields are required.']);
    }

    // Test SMTP connection
    $test_result = bmi_pro_test_smtp_connection($host, $port, $username, $password);

    if ($test_result === true) {
        wp_send_json_success(['message' => 'SMTP connection successful']);
    } else {
        wp_send_json_error(['message' => 'SMTP connection failed: ' . $test_result]);
    }
}

function bmi_pro_test_openai_connection($api_key) {
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => 'Test']],
            'max_tokens' => 5
        ])
    ]);

    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

function bmi_pro_test_gemini_connection($api_key) {
    $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode([
            'contents' => [['parts' => [['text' => 'Test']]]]
        ])
    ]);

    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

function bmi_pro_test_smtp_connection($host, $port, $username, $password) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        // Set debug level to 0 to prevent raw error messages
        $mail->SMTPDebug = 0;

        ob_start();
        $mail->SmtpConnect();
        ob_end_clean();

        return true;
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        
        // Provide more user-friendly error messages
        if (strpos($error_message, 'Could not authenticate') !== false) {
            return 'Authentication failed. Please check your username and password.';
        } elseif (strpos($error_message, 'connect()') !== false) {
            return 'Connection failed. Please check your host and port settings.';
        } else {
            return 'SMTP Error: ' . $error_message;
        }
    }
}

add_action('wp_ajax_bmi_test_api', 'bmi_pro_test_api_handler');
add_action('wp_ajax_bmi_test_smtp', 'bmi_pro_test_smtp_handler');

// Initialize settings
add_action('admin_init', 'bmi_pro_register_customization_settings');

// Output CSS for Customization
function bmi_pro_output_custom_styles() {
    $header_color = get_option('bmi_header_color', '#000000');
    $button_color = get_option('bmi_button_color', '#007bff');
    $text_color = get_option('bmi_text_color', '#333333');
    echo "<style>
        .calculator-container .section-title { color: $header_color; }
        .calculator-container .submit-btn { background-color: $button_color; color: #fff; }
        .calculator-container { color: $text_color; }
    </style>";
}
add_action('wp_head', 'bmi_pro_output_custom_styles');
function bmi_pro_field_font_family() {
    $value = get_option('bmi_font_family', 'Roboto');
    echo '<input type="text" name="bmi_font_family" value="' . esc_attr($value) . '" placeholder="e.g., Arial, Roboto">';
}

function bmi_pro_field_font_size() {
    $value = get_option('bmi_font_size', '16px');
    echo '<input type="text" name="bmi_font_size" value="' . esc_attr($value) . '" placeholder="e.g., 16px, 14px">';
}

function bmi_pro_output_custom_fonts() {
    $font_family = get_option('bmi_font_family', 'Roboto');
    $font_size = get_option('bmi_font_size', '16px');

    echo "<style>
        .calculator-container {
            font-family: {$font_family}, sans-serif;
            font-size: {$font_size};
        }
    </style>";
}
add_action('wp_head', 'bmi_pro_output_custom_fonts');
