<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get selected calendar from GET parameter or use default
$selectedCalendar = $_GET['calendar'] ?? 'calendar1';

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$userId = $loggedIn ? $_SESSION['user_id'] : null;

// Get user details if logged in
$username = '';
$name = '';
$profilePicture = '';

if ($loggedIn) {
    $stmt = $conn->prepare("SELECT username, name, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'] ?? '';
        $name = $user['name'] ?? '';
        $profilePicture = $user['profile_picture'] ?? '';
    }
    $stmt->close();
}

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loggedIn) {
    $questionId = $_POST['question_id'];
    $answer = $_POST['answer'];
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['answer_image']) && $_FILES['answer_image']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "answer_uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileExt = pathinfo($_FILES['answer_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $targetFile = $targetDir . $fileName;
        
        if (move_uploaded_file($_FILES['answer_image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }
    
    // Insert answer into database
    $stmt = $conn->prepare("INSERT INTO weekly_answers (question_id, user_id, answer, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $questionId, $userId, $answer, $imagePath);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Your answer has been submitted!";
    header("Location: weekly_questions_display.php?calendar=" . urlencode($selectedCalendar));
    exit();
}

// Get current week's question for selected calendar
$currentDate = date('Y-m-d');
$sql = "SELECT * FROM weekly_questions WHERE calendar_type = ? AND week_start_date <= ? AND week_end_date >= ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $selectedCalendar, $currentDate, $currentDate);
$stmt->execute();
$currentQuestion = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get all answers for the current question
$answers = [];
if ($currentQuestion) {
    $sql = "SELECT wa.*, u.username, u.name, u.profile_picture 
            FROM weekly_answers wa 
            JOIN users u ON wa.user_id = u.id 
            WHERE wa.question_id = ? 
            ORDER BY wa.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentQuestion['id']);
    $stmt->execute();
    $answers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question of the Week</title>
            <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .calendar-selector {
            margin-bottom: 20px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        .question-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }
        .answer-form {
            background: #e9f7ef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .answer-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
            border-left: 3px solid #ddd;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4CAF50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            overflow: hidden;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name {
            font-weight: bold;
        }
        .username {
            color: #666;
            font-size: 0.9em;
        }
        .answer-image {
            max-width: 100%;
            max-height: 300px;
            margin-top: 10px;
            border-radius: 4px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            padding: 10px;
            background: #dff0d8;
            color: #3c763d;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .login-prompt {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        select.form-control {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
        }
 .navbar {
    background-color: lightblue;
    border-bottom: 2px solid #ddd;
    padding: 10px 15px;
    position: sticky;
    top: 0;
    z-index: 1000;
    width: 100%;
}

    .navbar-brand {
        font-weight: bold;
        font-size: 24px;
        color: black;
        display: flex;
        align-items: center;
        margin-right: auto;
    }
    .navbar-brand .health {
        color: blue;
    }
    .navbar-brand img {
        width: 40px;
        height: 40px;
        margin-right: 10px;
    }
    /* Nav Links Base Styles */
    .navbar-nav .nav-link {
        color: black !important;
        font-size: 16px;
        padding: 10px 15px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link:focus {
        color: blue !important;
        transform: scale(1.05);
    }
    .navbar-nav .nav-link i {
        margin-right: 6px;
        font-size: 18px;
    }
    /* Mobile Navigation Icons */
    .mobile-icons {
        display: flex;
        align-items: center;
        margin-right: 10px;
    }
    .mobile-icons .nav-link {
        padding: 8px;
        margin-left: 5px;
        color: #333 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mobile-icons .nav-link i {
        font-size: 20px;
    }
    /* Toggler Button */
    .navbar-toggler {
        border: none;
        padding: 8px;
        outline: none !important;
        box-shadow: none !important;
    }
    .navbar-toggler:focus {
        outline: none;
        box-shadow: none;
    }
    /* Back Button */
    .back-button {
        display: inline-block;
        margin: 15px;
        padding: 8px 16px;
        background-color: #f8f9fa;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
    }
    .back-button:hover {
        background-color: #e9ecef;
        color: #000;
    }
    .filter-buttons button.active {
        opacity: 1;
        transform: scale(1.15);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    /* Responsive Styles */
    @media (max-width: 991.98px) {
        /* This targets screens up to Bootstrap's large breakpoint */
        .navbar-collapse {
            position: fixed;
            top: 62px; /* Adjust to match your navbar height */
            left: 0;
            width: 100%;
            height: auto;
            background-color: white;
            border-bottom: 2px solid #ddd;
            padding: 10px 0;
            z-index: 999;
            max-height: 80vh;
            overflow-y: auto;
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }
        .navbar-collapse.show {
            transform: translateY(0);
        }
        .navbar-nav {
            padding: 10px 0;
        }
        .navbar-nav .nav-item {
            width: 100%;
        }
        .navbar-nav .nav-link {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            justify-content: center;
        }
        .navbar-nav .nav-link i {
            margin-right: 10px;
        }
    }
    @media (max-width: 768px) {
        .navbar {
            padding: 8px 10px;
        }
        .navbar-brand {
            font-size: 20px;
        }
        .navbar-brand img {
            width: 32px;
            height: 32px;
        }
        .mobile-icons .nav-link {
            padding: 6px;
        }
        .mobile-icons .nav-link i {
            font-size: 18px;
        }
        .back-button {
            margin: 10px;
            padding: 6px 12px;
            font-size: 14px;
        }
    }
    @media (max-width: 576px) {
        .navbar-brand {
            font-size: 18px;
        }
        .navbar-brand img {
            width: 28px;
            height: 28px;
            margin-right: 6px;
        }
        .mobile-icons .nav-link i {
            font-size: 16px;
        }
    }
    /* Fixed positioning for breadcrumb */
        .breadcrumb-container {
        background-color: lightblue;
        padding: 8px 15px;
        border-bottom: 1px solid lightblue;
        width: 100%;
        margin: 0;
    }
    .breadcrumb {
        margin-bottom: 0;
        padding: 0;
        list-style: none;
        background-color: transparent;
        font-size: 14px;
    }
    /* Fix for navbar structure */
    .navbar .container-fluid {
        padding: 0 15px;
    }
    /* Mobile response fixes */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            position: fixed;
            top: 82px;
            left: 0;
            width: 100%;
            height: auto;
            background-color: white;
            border-bottom: 2px solid #ddd;
            padding: 10px 0;
            z-index: 999;
            max-height: 80vh;
            overflow-y: auto;
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }
        .navbar-collapse.show {
            transform: translateY(0);
        }
    }
    /* Comment Section Styles */
    .comment-list {
        max-height: 200px;
        overflow-y: auto;
        margin-bottom: 10px;
        border: 1px solid #eee;
        padding: 10px;
        border-radius: 4px;
        background-color: #f9f9f9;
    }
    .comment-item {
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }
    .comment-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .comment-form {
        margin-top: 15px;
    }
    .comment-input-group {
        display: flex;
        gap: 10px;
    }
    .comment-input {
        flex-grow: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .comment-submit-btn {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 15px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .comment-submit-btn:hover {
        background-color: #0056b3;
    }
    .comment-author {
        font-weight: bold;
        color: #333;
    }
    .comment-text {
        color: #555;
        margin: 5px 0;
    }
    .comment-time {
        font-size: 0.8em;
        color: #777;
    }
    .comments-toggle-btn {
        background: none;
        border: none;
        color: #007bff;
        cursor: pointer;
        padding: 5px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .comments-toggle-btn i {
        transition: transform 0.3s;
    }
    .comments-toggle-btn.active i {
        transform: rotate(180deg);
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="#">
            <img src="img/log.png" alt="Logo" style="width:100%;">
        </a>

<div class="d-flex d-lg-none" style="gap: 10px;">
    <a class="nav-link" href="../user_details.php">
        <p style="
            border: 2px solid #0066cc; /* Blue border, 2px width */
            display: inline-block; /* Make the box fit the content */
            padding: 6px; /* Add some padding inside the box */
            border-radius: 20px; /* Increased rounded corners */
             margin: -10px; /* Add margin -->
             color: blue;
             text-decoration: bold;
            font-weight: 500;


        ">Profile</p> <!-- FontAwesome user icon -->
    </a>
    <a class="nav-link" href="shop_index.php">
        <p style="
            border: 2px solid #0066cc; /* Blue border, 2px width */
            display: inline-block; /* Make the box fit the content */
            padding: 6px; /* Add some padding inside the box */
            border-radius: 20px; /* Increased rounded corners */
            margin: -10px; /* Add margin -->
            color: black;
            text-decoration: bold;
            font-weight: 500;


        ">Shop</p> <!-- FontAwesome shop icon -->
    </a>
</div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../user.php">Home</a></li>
                <li class="breadcrumb-item active">Day's Thought</li>
            </ol>
        </div>
    </div>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link active" href="../user.php">
                     Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="display_messages.php">Day's Thought</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="winner_list.php">Health Champs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="vid_display.php">Health Talks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="download_list.php">Downloads</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="event_display.php">Events</a>
            </li>
           
            <li class="nav-item ">
                <a class="nav-link" href="shop_index.php">
                     Shop
                </a>
            </li>
        </ul>
    </div>
</nav>

    <div class="container">
        <!-- Calendar Selector -->
        <div class="calendar-selector">
            <form method="GET">
                <select name="calendar" class="form-control" onchange="this.form.submit()">
                    <?php
                    for ($i = 1; $i <= 65; $i++) {
                        $calendarValue = 'calendar' . $i;
                        $selected = ($selectedCalendar === $calendarValue) ? 'selected' : '';
                        echo "<option value='$calendarValue' $selected>Calendar $i</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <h2>Question of the Week</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if ($currentQuestion): ?>
            <div class="question-card">
              
                <p><strong>Week of:</strong> <?php echo date('M j', strtotime($currentQuestion['week_start_date'])) . ' - ' . date('M j, Y', strtotime($currentQuestion['week_end_date'])); ?></p>
                <h2><?php echo nl2br(htmlspecialchars($currentQuestion['question'])); ?></h2>
            </div>
            
            <?php if ($loggedIn): ?>
                <div class="answer-form">
                    <h3>Your Answer</h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="question_id" value="<?php echo $currentQuestion['id']; ?>">
                        <textarea name="answer" placeholder="Write your answer here..." required></textarea>
                        <div style="margin-bottom: 10px;">
                            <label for="answer_image">Upload Image (optional):</label>
                            <input type="file" name="answer_image" id="answer_image" accept="image/*">
                        </div>
                        <button type="submit">Submit Answer</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <p>Please <a href="login.php">login</a> to submit your answer to this week's question.</p>
                </div>
            <?php endif; ?>
            
            <h2>Community Answers</h2>
            
            <?php if (!empty($answers)): ?>
                <?php foreach ($answers as $answer): ?>
                    <div class="answer-card">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php if (!empty($answer['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($answer['profile_picture']); ?>" alt="Profile picture">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($answer['name'] ?? 'U', 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($answer['name'] ?? 'Unknown'); ?></div>
                                <div class="username">@<?php echo htmlspecialchars($answer['username'] ?? 'user'); ?></div>
                            </div>
                        </div>
                        <div class="answer-text">
                            <?php echo nl2br(htmlspecialchars($answer['answer'])); ?>
                        </div>
                        <?php if (!empty($answer['image_path'])): ?>
                            <div class="answer-image-container">
                                <img src="<?php echo htmlspecialchars($answer['image_path']); ?>" alt="Answer image" class="answer-image">
                            </div>
                        <?php endif; ?>
                        <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                            Answered on <?php echo date('M j, Y g:i a', strtotime($answer['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No answers yet. Be the first to respond!</p>
            <?php endif; ?>
        <?php else: ?>
            <p>No question has been set for this week in <?php echo htmlspecialchars($selectedCalendar); ?>. Please check back later.</p>
        <?php endif; ?>
    </div>
</body>
<style>
/* Footer Styles */
.footer {
    background-color: lightblue;
    padding-top: 60px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    padding: 0 20px 40px 20px;
}

.footer-column {
    display: flex;
    flex-direction: column;
}

/* Logo and Description */
.footer-logo {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    max-width: 100%;
}

.footer-logo img {
    width: 90%;
    max-width: 250px;
    height: auto;
}

.heart-icon {
    stroke: #0d9488;
    fill: none;
    margin-right: 8px;
}

.logo-text {
    font-size: 22px;
    font-weight: bold;
    color: black;
}

.footer-description {
    color: black;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* Social Media */
.social-media {
    display: flex;
    gap: 15px;
    margin-top: 5px;
}

.social-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: black;
    border: 1px solid #0d9488;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background-color: #0d9488;
    color: blue;
}

/* Column Titles */
.footer-title {
    font-size: 18px;
    font-weight: 600;
    color: black;
    margin-bottom: 20px;
}

/* Lists */
.footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.footer-icon {
    color: black;
    margin-right: 10px;
}

.footer-link {
    color: black;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #0d9488;
}

/* Get Started Button */
.get-started-button {
    display: inline-block;
    background-color: #D34DEE;
    color: black;
    font-weight: 600;
    text-decoration: none;
    padding: 10px 24px;
    border-radius: 15px;
    margin-bottom: 20px;
    text-align: center;
    transition: background-color 0.3s ease;
}

.get-started-button:hover {
    background-color: #D34DEE;
}

/* Responsive styling for the button */
@media (max-width: 768px) {
    .get-started-button {
        display: block;
        width: 100%;
        margin: 0 auto 20px auto;
    }
}
/* Newsletter */
.newsletter-form {
    display: flex;
    margin-top: 10px;
    margin-bottom: 20px;
}

.newsletter-input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 4px 0 0 4px;
    outline: none;
    font-size: 14px;
}

.newsletter-button {
    background-color: #0d9488;
    color: white;
    border: none;
    padding: 0 15px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.newsletter-button:hover {
    background-color: #0f766e;
}

/* Features */
.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    color: black;
    font-size: 14px;
}

.feature-icon {
    color: black;
    margin-right: 10px;
}

/* Copyright Section */
.copyright-section {
    background-color: darkblue;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
}

.copyright-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.copyright-text {
    color: white;
    font-size: 14px;
}

.footer-links {
    display: flex;
    gap: 20px;
}

.copyright-link {
    color: white;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.copyright-link:hover {
    color: white;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    /* Create a services-links wrapper for mobile */
    .services-links-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .copyright-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-input {
        width: 100%;
        border-radius: 4px;
        border-right: 1px solid #e2e8f0;
        margin-bottom: 10px;
    }
    
    .newsletter-button {
        width: 100%;
        border-radius: 4px;
        padding: 12px 15px;
    }
    
    /* Center logo in mobile view */
    .footer-logo {
        justify-content: center;
        margin: 0 auto 15px auto;
    }
    
    .footer-logo img {
        max-width: 200px;
    }
    
    /* Center social media icons */
    .social-media {
        justify-content: center;
        margin: 10px auto;
    }
    
    /* Adjust font sizes for mobile */
    .footer-title {
        font-size: 16px;
    }
    
    .footer-description {
        text-align: center;
    }
}
</style>



<!-- Footer Section -->
<footer class="footer">
    <div class="footer-container">
        <!-- Logo and Description Section -->
        <div class="footer-column">
            <div class="footer-logo">
            <img src="img/log.png" alt="Logo" style="width:90%;"> <!-- Add your logo image here -->
           </div>
            <p class="footer-description">Your trusted partner in health and wellness. Providing quality healthcare services across Sri Lanka.</p>
            
            <!-- Social Media Icons -->
            <center>
            <div class="social-media" style="margin-left:10px;">
                
    <a href="https://www.facebook.com/share/1BUFn5hKYY/" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
        </svg>
    </a>
    <a href="https://wa.me/94777867942" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
    </a>
    <a href="https://www.instagram.com/dailyhealthlk?igsh=MTBzYXljeHI5N3Rtdw==" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
        </svg>
    </a>
    <a href="https://youtube.com/@dailyhealthlk?si=HusrGcS-FTdcg1eZ" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
        </svg>
    </a>
</div>
</center>
        </div>
        
        
        <!-- Quick Links Column -->
        <div class="footer-column">
            <h3 class="footer-title">Quick Links</h3>
            <ul class="footer-list">
                <li class="footer-item">
                    <a href="display_messages.php" class="footer-link">Day's Thoughts</a>
                </li>
                <li class="footer-item">
                    <a href="winner_list.php" class="footer-link">Health Champs</a>
                </li>
                <li class="footer-item">
                    <a href="vid_display.php" class="footer-link">Health Talks</a>
                </li>
                <li class="footer-item">
                    <a href="download_list.php" class="footer-link">Downloads </a>
                </li>
                <li class="footer-item">
                    <a href="event_display.php" class="footer-link">Event</a>
                </li>
            </ul>
        </div>
        
     
       
        <!-- Health Updates Column -->
        <!-- Health Updates Column -->
<div class="footer-column">
    <h3 class="footer-title">Health Updates</h3>
    <p class="footer-description">Subscribe to receive health tips and updates.</p>
    
    <!-- Get Started Button -->
     <a href="https://wa.me/94777867942" class="get-started-button" target="_blank" rel="noopener noreferrer">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
        Send a meesage </a>    
    <!-- Features -->
    <ul class="features-list">
        <li class="feature-item">
            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Licensed Medical Professionals</span>
        </li>
        <li class="feature-item">
            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>24/7 Online Support</span>
        </li>
        <li class="feature-item">
            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Verified Health Information</span>
        </li>
    </ul>
</div>
    </div>
    
    <!-- Copyright Section -->
    <div class="copyright-section">
        <div class="copyright-container">
            <div class="copyright-text">© 2025 DailyHealth.lk. All rights reserved.</div>
            <div class="footer-links">
                <a href="#" class="copyright-link">Privacy Policy</a>
                <a href="#" class="copyright-link">Terms of Service</a>
                <a href="#" class="copyright-link">Sitemap</a>
            </div>
        </div>
    </div>
</footer>
</html>
<?php $conn->close(); ?>