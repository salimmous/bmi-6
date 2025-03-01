<?php

if (!defined('ABSPATH')) exit;

// Calculate BMI
function calculate_bmi($weight, $height) {
    return $weight / (($height / 100) ** 2);
}

// Calculate BFP
function calculate_bfp($bmi, $age) {
    return ($bmi * 1.2) + (0.23 * $age) - 5.4;
}

// Calculate BMR
function calculate_bmr($weight, $height, $age) {
    return 10 * $weight + 6.25 * $height - 5 * $age + 5;
}

// Ideal weight range
function calculate_ideal_weight($height) {
    $min = 18.5 * (($height / 100) ** 2);
    $max = 24.9 * (($height / 100) ** 2);
    return [$min, $max];
}
