<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$user = "u627928174_root";
$pass = "Daily@365";
$dbname = "u627928174_daily_routine";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

if (!isset($_GET['message_id'])) {
    die(json_encode(['error' => 'Message ID not provided']));
}

$message_id = (int)$_GET['message_id'];

$comments = $conn->query("SELECT message_comments.*, users.name 
                         FROM message_comments 
                         JOIN users ON message_comments.user_id = users.id 
                         WHERE message_id = $message_id 
                         ORDER BY created_at DESC");

$commentsArray = [];
if ($comments && $comments->num_rows > 0) {
    while ($comment = $comments->fetch_assoc()) {
        $commentsArray[] = $comment;
    }
}

echo json_encode($commentsArray);
$conn->close();
?>

