<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "u627928174_root"; // Change this to your database username
$password = "Daily@365"; // Change this to your database password
$dbname = "u627928174_daily_routine"; // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get form data
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? ''); // Now manually entered
$dob = $_POST['dob'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? '';
$marital_status = $_POST['marital_status'] ?? '';
$area = trim($_POST['area'] ?? '');
$occupation = trim($_POST['occupation'] ?? '');
$language = $_POST['language'] ?? '';
$whatsapp = trim($_POST['whatsapp'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$repassword = $_POST['repassword'] ?? '';

// Validate required fields
if (empty($name) || empty($username) || empty($dob) || empty($phone) || empty($gender) || 
    empty($marital_status) || empty($area) || empty($occupation) || empty($language) || 
    empty($whatsapp) || empty($email) || empty($password) || empty($repassword)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Validate username format (alphanumeric, underscore, hyphen, 3-20 chars)
if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be 3-20 characters and can only contain letters, numbers, underscores and hyphens']);
    exit;
}

// Validate password match
if ($password !== $repassword) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Validate phone numbers (basic validation)
if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number']);
    exit;
}

if (!preg_match('/^[0-9]{10,15}$/', $whatsapp)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid WhatsApp number']);
    exit;
}

// Check if username or email already exists
$check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username or email already exists']);
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$insert_query = "INSERT INTO users (username, name, dob, phone, gender, marital_status, 
                  area, occupation, language, whatsapp, email, password) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ssssssssssss", 
    $username, 
    $name, 
    $dob, 
    $phone, 
    $gender, 
    $marital_status, 
    $area, 
    $occupation, 
    $language, 
    $whatsapp, 
    $email, 
    $hashed_password
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>