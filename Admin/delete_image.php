<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$event_id = $_POST['event_id'] ?? null;
$image_path = $_POST['image_path'] ?? null;

if (!$event_id || !$image_path) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Delete the image file
if (file_exists($image_path)) {
    unlink($image_path);
}

// Delete the database record
$stmt = $conn->prepare("DELETE FROM event_images WHERE event_id = ? AND image_path = ?");
$stmt->bind_param("is", $event_id, $image_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>