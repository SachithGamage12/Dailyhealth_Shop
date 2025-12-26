<?php
// Database connection
$host = "localhost";
$user = "u627928174_root";
$pass = "Daily@365";
$dbname = "u627928174_daily_routine";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

$year = (int)$_GET['year'];
$month = (int)$_GET['month'];
$date = (int)$_GET['date'];

$sql = "SELECT note FROM daily_notes WHERE year = $year AND month = $month AND date = $date";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "success", "note" => $row['note']]);
} else {
    echo json_encode(["status" => "error", "message" => "No note found."]);
}

$conn->close();
?>
