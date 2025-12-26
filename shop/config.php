<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u627928174_root');
define('DB_PASS', 'Daily@365');
define('DB_NAME', 'u627928174_daily_routine');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}
?>