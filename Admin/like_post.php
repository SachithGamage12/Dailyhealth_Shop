<?php
session_start();  // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You need to log in to like a post."]);
    exit();
}

$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

$data = json_decode(file_get_contents("php://input"), true);
$post_id = (int)$data['id'];
$user_id = $_SESSION['user_id'];  // The logged-in user ID from the session

// Check if the user already liked this post
$sql_check_like = "SELECT * FROM likes WHERE user_id = $user_id AND post_id = $post_id";
$result = $conn->query($sql_check_like);

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "You have already liked this post."]);
    exit();
}

// Proceed with liking the post: Insert into `likes` table
$sql = "INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)";
if ($conn->query($sql) === TRUE) {
    // Increment the likes count in the `daily_messages` table
    $sql_increment_like = "UPDATE daily_messages SET likes = likes + 1 WHERE id = $post_id";
    if ($conn->query($sql_increment_like) === TRUE) {
        // Get the updated likes count
        $sql_likes = "SELECT likes FROM daily_messages WHERE id = $post_id";
        $like_result = $conn->query($sql_likes);
        $row = $like_result->fetch_assoc();
        echo json_encode(["status" => "success", "likes" => $row['likes']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update likes count"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to record your like"]);
}

$conn->close();
?>
