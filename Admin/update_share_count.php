<?php
// Manual DB connection
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

if (isset($_POST['postId'])) {
    $postId = (int)$_POST['postId'];

    // Increment the share count
    $sql = "UPDATE posts SET shares = shares + 1 WHERE id = $postId";

    if ($conn->query($sql) === TRUE) {
        // Get the new share count
        $result = $conn->query("SELECT shares FROM posts WHERE id = $postId");
        $row = $result->fetch_assoc();
        echo $row['shares']; // Return the updated share count
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
