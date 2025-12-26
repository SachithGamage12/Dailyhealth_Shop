<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
$message = '';  // To store success or error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $message = "Username or Email already exists!";  // Error message
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $message = "Registration successful!";  // Success message
        } else {
            $message = "Error: " . $conn->error;  // Error message
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Register</title>
</head>
<style>
    /* Global Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fc;
    color: #333;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Container for the form */
form {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 300px;
    text-align: center;
}

/* Heading */
h2 {
    color: #4c6a92;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Input fields */
input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 16px;
}

/* Button */
button[type="submit"] {
    background-color: #4c6a92;
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
}

button[type="submit"]:hover {
    background-color: #3a5587;
}

/* Link Style */
a {
    color: #4c6a92;
    text-decoration: none;
    font-size: 14px;
}

a:hover {
    text-decoration: underline;
}

/* Error and Success messages */
.message {
    font-size: 14px;
    margin-top: 10px;
}

.success {
    color: green;
}

.error {
    color: red;
}

</style>
<body>
    <form method="post">
        <input type="text" name="username" id="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>

        <!-- Display message -->
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </form>
    
    <a href="userdashboard.php" class="btn" style="
    position: absolute;
    top: 15px;
    right: 35px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back</a>
</body>
</html>
