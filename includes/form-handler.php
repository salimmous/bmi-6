<?php

function bmi_pro_ajax_handler() {
    // Check if data and nonce are present
    if (empty($_POST['data']) || empty($_POST['nonce'])) {
        wp_send_json_error(['message' => 'Missing data or nonce.']);
    }

    // Verify the nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'bmi_pro_action')) {
        wp_send_json_error(['message' => 'Invalid nonce.']);
    }

    // Parse the form data
    parse_str($_POST['data'], $form_data);

    // Sanitize and validate input fields
    $name = sanitize_text_field($form_data['name'] ?? '');
    $email = sanitize_email($form_data['email'] ?? '');
    $phone = sanitize_text_field($form_data['phone'] ?? '');
    $age = intval($form_data['age'] ?? 0);
    $gender = sanitize_text_field($form_data['gender'] ?? '');
    
    // Check if height_cm and weight_kg exist and sanitize them
    $height_in_cm = isset($form_data['height_cm']) ? floatval($form_data['height_cm']) : 0;
    $weight_in_kg = isset($form_data['weight_kg']) ? floatval($form_data['weight_kg']) : 0;

    $activity_level = sanitize_text_field($form_data['activity_level'] ?? '');
    
    // Additional form fields
    $fitness_goal = sanitize_text_field($form_data['fitness_goal'] ?? '');
    $calories = intval($form_data['calories'] ?? 0);
    $diet_preference = sanitize_text_field($form_data['diet_preference'] ?? '');
    $gym_sessions = intval($form_data['gym_sessions'] ?? 0);
    $time_in_gym = floatval($form_data['time_in_gym'] ?? 0);
    $sleep_hours = intval($form_data['sleep_hours'] ?? 0);

    // Validate numerical values for height, weight, and age
    if ($height_in_cm <= 0 || $height_in_cm > 250 || $weight_in_kg <= 0 || $weight_in_kg > 300 || $age <= 0 || $age > 120) {
        wp_send_json_error(['message' => 'Please enter realistic values for height, weight, and age.']);
    }

    // Perform calculations
    $height_in_meters = $height_in_cm / 100;
    $bmi = $weight_in_kg / ($height_in_meters ** 2);
    $bfp = ($bmi * 1.2) + (0.23 * $age) - ($gender === 'male' ? 16.2 : 5.4);

    // BMR calculation (Mifflin-St Jeor Equation)
    $bmr = ($gender === 'male')
        ? (10 * $weight_in_kg + 6.25 * $height_in_cm - 5 * $age + 5)
        : (10 * $weight_in_kg + 6.25 * $height_in_cm - 5 * $age - 161);

    // Ideal weight range
    $min_ideal_weight = 18.5 * ($height_in_meters ** 2);
    $max_ideal_weight = 24.9 * ($height_in_meters ** 2);
    $ideal_weight = round($min_ideal_weight, 2) . " - " . round($max_ideal_weight, 2) . " kg";

    // Generate recommendations
    $recommendations = generate_recommendations($bmi, $bfp, $bmr, $gender, $age, $activity_level);

    // Insert the data into the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'bmi_pro_data';
    $user_id = get_current_user_id();

    $data = [
        'user_id' => $user_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'age' => $age,
        'gender' => $gender,
        'height' => $height_in_cm,
        'weight' => $weight_in_kg,
        'bmi' => round($bmi, 2),
        'bfp' => round($bfp, 2),
        'bmr' => round($bmr, 2),
        'activity_level' => $activity_level,
        'fitness_goal' => $fitness_goal,
        'calories' => $calories,
        'diet_preference' => $diet_preference,
        'gym_sessions' => $gym_sessions,
        'time_in_gym' => $time_in_gym,
        'sleep_hours' => $sleep_hours,
        'created_at' => current_time('mysql'),
    ];

    $result = $wpdb->insert($table_name, $data);

    if ($result === false) {
        BMI_Logger::get_instance()->log('Database insertion failed', 'error', [
            'error' => $wpdb->last_error,
            'data' => $data
        ]);
        wp_send_json_error(['message' => 'Failed to save data']);
    }

    // Send an email report
    bmi_pro_send_email($name, $email, $bmi, $bfp, $bmr, $ideal_weight, $recommendations);

    // Return the results to the frontend
    wp_send_json_success([
        'bmi' => round($bmi, 2),
        'bfp' => round($bfp, 2),
        'bmr' => round($bmr, 2),
        'ideal_weight' => $ideal_weight,
        'recommendations' => $recommendations,
    ]);
}
add_action('wp_ajax_bmi_pro_calculate', 'bmi_pro_ajax_handler');
add_action('wp_ajax_nopriv_bmi_pro_calculate', 'bmi_pro_ajax_handler');



function calculate_bmi( $weight, $height ) {
    return $weight / (($height / 100) ** 2); // BMI = weight(kg) / height(m)^2
}

function calculate_bfp( $age, $gender, $bmi ) {
    // Simple formula for BFP based on gender and BMI
    if ( $gender == 'male' ) {
        return (1.20 * $bmi) + (0.23 * $age) - 16.2;
    } else {
        return (1.20 * $bmi) + (0.23 * $age) - 5.4;
    }
}

function calculate_bmr( $age, $gender, $weight, $height ) {
    // BMR Calculation using Harris-Benedict equation
    if ( $gender == 'male' ) {
        return 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
    } else {
        return 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
    }
}

function calculate_ideal_weight( $height ) {
    // A simple formula for Ideal Weight
    return (height - 100) - ((height - 100) * 0.1);
}

function generate_recommendations( $bmi, $bfp, $bmr, $gender, $age, $activity_level, $emotional_state = '' ) {
    $recommendation = '';
    
    // Base BMI recommendations with specific actionable steps
    if ($bmi < 18.5) {
        $recommendation = "Your BMI indicates you're underweight. Here's your personalized plan:\n\n";
        $recommendation .= "1. Nutrition Goals:\n";
        $recommendation .= "   - Increase daily caloric intake by 300-500 calories\n";
        $recommendation .= "   - Eat 5-6 smaller meals throughout the day\n";
        $recommendation .= "   - Focus on protein intake (1.6-2.2g per kg of body weight)\n\n";
        $recommendation .= "2. Exercise Plan:\n";
        $recommendation .= "   - Strength training 3-4 times per week\n";
        $recommendation .= "   - Focus on compound exercises (squats, deadlifts, bench press)\n";
        $recommendation .= "   - Limit cardio to 20-30 minutes, 2-3 times per week\n";
    } elseif ($bmi >= 18.5 && $bmi < 24.9) {
        $recommendation = "Your BMI is in the healthy range. Here's how to maintain and optimize your fitness:\n\n";
        $recommendation .= "1. Fitness Goals:\n";
        $recommendation .= "   - Mix of strength training and cardio\n";
        $recommendation .= "   - Aim for 150 minutes of moderate exercise per week\n";
        $recommendation .= "   - Include flexibility and mobility work\n\n";
        $recommendation .= "2. Nutrition Strategy:\n";
        $recommendation .= "   - Maintain current caloric intake\n";
        $recommendation .= "   - Focus on whole foods and lean proteins\n";
        $recommendation .= "   - Stay hydrated (2-3 liters of water daily)\n";
    } elseif ($bmi >= 25 && $bmi < 29.9) {
        $recommendation = "Your BMI indicates you're overweight. Here's your transformation plan:\n\n";
        $recommendation .= "1. Weight Management Strategy:\n";
        $recommendation .= "   - Create a 500 calorie daily deficit\n";
        $recommendation .= "   - Track your meals using a food diary\n";
        $recommendation .= "   - Aim for 1-2 pounds of weight loss per week\n\n";
        $recommendation .= "2. Exercise Routine:\n";
        $recommendation .= "   - 30-45 minutes of cardio, 5 times per week\n";
        $recommendation .= "   - Strength training 3 times per week\n";
        $recommendation .= "   - Consider HIIT workouts for fat burning\n";
    } else {
        $recommendation = "Your BMI indicates obesity. Here's your health improvement plan:\n\n";
        $recommendation .= "1. Initial Steps:\n";
        $recommendation .= "   - Consult with a healthcare provider\n";
        $recommendation .= "   - Start with walking 15-20 minutes daily\n";
        $recommendation .= "   - Focus on portion control\n\n";
        $recommendation .= "2. Progressive Plan:\n";
        $recommendation .= "   - Gradually increase activity duration\n";
        $recommendation .= "   - Consider working with a personal trainer\n";
        $recommendation .= "   - Join a supportive fitness community\n";
    }

    // Activity level specific recommendations
    if ($activity_level == 'sedentary') {
        $recommendation .= "\n\nActivity Level Recommendations:\n";
        $recommendation .= "1. Start Small:\n";
        $recommendation .= "   - Take 5-minute breaks every hour to walk\n";
        $recommendation .= "   - Use a standing desk if possible\n";
        $recommendation .= "   - Park further from entrances\n";
        $recommendation .= "2. Weekly Goals:\n";
        $recommendation .= "   - Aim for 4,000 steps daily, increasing by 1,000 each week\n";
        $recommendation .= "   - Try beginner-friendly yoga or stretching\n";
    } elseif ($activity_level == 'very_active') {
        $recommendation .= "\n\nRecovery and Performance Tips:\n";
        $recommendation .= "1. Recovery Protocol:\n";
        $recommendation .= "   - Ensure 7-9 hours of quality sleep\n";
        $recommendation .= "   - Consider foam rolling and stretching\n";
        $recommendation .= "   - Plan deload weeks every 6-8 weeks\n";
        $recommendation .= "2. Performance Optimization:\n";
        $recommendation .= "   - Track your heart rate variability\n";
        $recommendation .= "   - Focus on post-workout nutrition\n";
        $recommendation .= "   - Consider supplementation (consult a professional)\n";
    }

    // Add BMR-based nutrition advice
    $recommendation .= "\n\nDaily Energy Requirements:\n";
    $recommendation .= "- Your Basal Metabolic Rate (BMR): " . round($bmr) . " calories\n";
    $recommendation .= "- Recommended protein intake: " . round($weight_in_kg * 1.8) . "g per day\n";
    $recommendation .= "- Stay hydrated: Drink " . round($weight_in_kg * 0.033) . " liters of water daily\n";
    
    return $recommendation;
}


/**
 * Sends an email report with BMI results
 */
function bmi_pro_send_email($name, $email, $bmi, $bfp, $bmr, $ideal_weight, $recommendations) {
    $subject = "Your Personalized BMI Report";
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $message = "
    <html>
    <head>
        <title>Your Personalized BMI Report</title>
    </head>
    <body>
        <h1>BMI Report</h1>
        <p><strong>Name:</strong> $name</p>
        <p><strong>BMI:</strong> $bmi</p>
        <p><strong>BFP:</strong> $bfp%</p>
        <p><strong>BMR:</strong> $bmr kcal/day</p>
        <p><strong>Ideal Weight Range:</strong> $ideal_weight</p>
        <p><strong>Recommendations:</strong><br> $recommendations</p>
        <p>Thank you for using our BMI Calculator!<br>If you have any further questions, feel free to contact us.</p>
    </body>
    </html>
    ";

    wp_mail($email, $subject, $message, $headers);
}
