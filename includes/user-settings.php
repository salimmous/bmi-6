<?php
if (!defined('ABSPATH')) exit;

// Add custom fields to user profile
function bmi_pro_user_profile_fields($user) {
    ?>
    <h3>BMI Pro Preferences</h3>
    <table class="form-table">
        <tr>
            <th><label for="bmi_chart_color">Preferred Chart Color</label></th>
            <td>
                <input type="color" name="bmi_chart_color" id="bmi_chart_color" value="<?php echo esc_attr(get_user_meta($user->ID, 'bmi_chart_color', true)); ?>" />
                <p class="description">Select your preferred color for the BMI chart.</p>
            </td>
        </tr>
        <tr>
            <th><label for="bmi_unit">Preferred BMI Unit</label></th>
            <td>
                <select name="bmi_unit" id="bmi_unit">
                    <option value="metric" <?php selected(get_user_meta($user->ID, 'bmi_unit', true), 'metric'); ?>>Metric</option>
                    <option value="imperial" <?php selected(get_user_meta($user->ID, 'bmi_unit', true), 'imperial'); ?>>Imperial</option>
                </select>
                <p class="description">Choose the BMI calculation unit.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'bmi_pro_user_profile_fields');
add_action('edit_user_profile', 'bmi_pro_user_profile_fields');

// Save custom user fields
function bmi_pro_save_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'bmi_chart_color', sanitize_hex_color($_POST['bmi_chart_color']));
    update_user_meta($user_id, 'bmi_unit', sanitize_text_field($_POST['bmi_unit']));
}
add_action('personal_options_update', 'bmi_pro_save_user_profile_fields');
add_action('edit_user_profile_update', 'bmi_pro_save_user_profile_fields');
