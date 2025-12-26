<?php
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "u627928174_root";
$pass = "Daily@365";
$dbname = "u627928174_daily_routine";
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Get the year, month, and date from the request
$data = json_decode(file_get_contents('php://input'), true);
$year = $data['year'];
$month = $data['month'];
$date = $data['date'];

// Delete the note from the database
$sql = "DELETE FROM daily_notes WHERE year = $year AND month = $month AND date = $date";
if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error deleting note: " . $conn->error]);
}

$conn->close();
?>