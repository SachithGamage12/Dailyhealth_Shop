<?php
session_start();
$servername = "localhost";
$username = "u627928174_root"; // Database username
$password = "Daily@365"; // Database password
$dbname = "u627928174_daily_routine"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $newPassword = trim($_POST['new_password']);

    if (empty($username) || empty($newPassword)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password."]);
    }

    $stmt->close();
    $conn->close();
}
?>