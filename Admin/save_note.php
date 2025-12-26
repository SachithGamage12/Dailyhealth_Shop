<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "User not authenticated."]));
}

$user_id = $_SESSION['user_id'];
$host = "localhost";
$user = "u627928174_root";
$pass = "Daily@365";
$dbname = "u627928174_daily_routine";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

$data = json_decode(file_get_contents("php://input"), true);
$year = (int)$data['year'];
$month = (int)$data['month'];
$date = (int)$data['date'];
$note = $conn->real_escape_string($data['note']);

$sql = "INSERT INTO daily_notes (year, month, date, note, user_id) 
        VALUES ($year, $month, $date, '$note', '$user_id')
        ON DUPLICATE KEY UPDATE note = VALUES(note)";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "note" => $note]);
} else {
    echo json_encode(["status" => "error", "message" => "Error saving note: " . $conn->error]);
}

$conn->close();
?>
