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
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    // Check if the username exists in the admins table (both username and email fields)
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Admin login
        $stmt->bind_result($admin_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_logged_in'] = true;

            // Record the login time for admin
            $login_time = date('Y-m-d H:i:s');
            $history_stmt = $conn->prepare("INSERT INTO admin_login_history (admin_id, login_time) VALUES (?, ?)");
            $history_stmt->bind_param("is", $admin_id, $login_time);
            $history_stmt->execute();
            $history_stmt->close();

            echo json_encode(["success" => true, "redirect" => "./Admin/admin_panel.html"]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid credentials."]);
        }
    } else {
        // If not an admin, check the users table for a regular user
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User login
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_logged_in'] = true;
                echo json_encode(["success" => true, "redirect" => "user.php"]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid credentials."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "No user found with this username/email."]);
        }
    }

    $stmt->close();
    $conn->close();
}
?>