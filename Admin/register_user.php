<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input from the form (email, name, and password)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $sql_check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql_check_email);

    if ($result->num_rows > 0) {
        echo "Email already registered. Please use a different email.";
    } else {
        // Insert new user data into the database
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
        if ($conn->query($sql) === TRUE) {
            echo "User registered successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

$conn->close();
?>
