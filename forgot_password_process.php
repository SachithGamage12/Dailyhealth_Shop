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
    $dob = trim($_POST['dob']);

    if (empty($username) || empty($dob)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    // Check if the email and DOB match in the users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND dob = ?");
    $stmt->bind_param("ss", $username, $dob);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid username or date of birth."]);
    }

    $stmt->close();
    $conn->close();
}
?>