<?php
session_start();
$servername = "localhost";
$username = "u627928174_root";
$password = "Daily@365";
$dbname = "u627928174_daily_routine";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure admin is logged in
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $logout_time = date('Y-m-d H:i:s');

    // Update logout time in admin_login_history table
    $stmt = $conn->prepare("UPDATE admin_login_history SET logout_time = ? WHERE admin_id = ? AND logout_time IS NULL");
    $stmt->bind_param("si", $logout_time, $admin_id);
    $stmt->execute();
    $stmt->close();

    // Destroy session and logout
    session_unset();
    session_destroy();

    // Redirect to login page after logout
    header("Location: ./login.php");
    exit();
} else {
    // No admin is logged in
    header("Location: ./login.php");
    exit();
}

$conn->close();
?>
