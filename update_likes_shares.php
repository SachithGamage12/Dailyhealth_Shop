<?php
session_start(); // Start the session to check if the user is logged in

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['redirect' => true]); // If not logged in, redirect to login page
    exit;
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID
$servername = "localhost";
$username = "u627928174_root"; // Database username
$password = "Daily@365"; // Database password
$dbname = "u627928174_daily_routine"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_GET['action'];
$postId = (int) $_GET['id'];

if ($action == 'like') {
    // Check if the user has already liked this post
    $sql = "SELECT * FROM likes WHERE user_id = $userId AND post_id = $postId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // User has already liked this post, so don't allow another like
        echo json_encode(['success' => false, 'message' => 'You have already liked this post.']);
        exit;
    }

    // User has not liked the post, so update the like count
    $sql = "UPDATE daily_messages SET likes = likes + 1 WHERE id = $postId";
    if ($conn->query($sql) === TRUE) {
        // Insert the like record into the likes table
        $sql = "INSERT INTO likes (user_id, post_id) VALUES ($userId, $postId)";
        $conn->query($sql);

        // Fetch updated likes count
        $result = $conn->query("SELECT likes FROM daily_messages WHERE id = $postId");
        $row = $result->fetch_assoc();

        echo json_encode(['success' => true, 'likes' => $row['likes']]);
    } else {
        echo json_encode(['success' => false]);
    }
} elseif ($action == 'share') {
    $sql = "UPDATE daily_messages SET shares = shares + 1 WHERE id = $postId";
    if ($conn->query($sql) === TRUE) {
        $result = $conn->query("SELECT shares FROM daily_messages WHERE id = $postId");
        $row = $result->fetch_assoc();

        echo json_encode(['success' => true, 'shares' => $row['shares']]);
    } else {
        echo json_encode(['success' => false]);
    }
}

$conn->close();
?>
