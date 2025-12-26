<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if (!isset($_GET['id'])) {
    die("Invalid video ID.");
}


$video_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get Comment Count for this video
$comment_count_result = $conn->query("SELECT COUNT(*) AS total FROM comments WHERE video_id=$video_id");
$comment_count = $comment_count_result->fetch_assoc()['total'];

// Handle Comment Submission
$comment_count = isset($comment_count) ? $comment_count : 0; // Default to 0 if not set

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment']) && !empty($_POST['comment'])) {
    $comment_text = $conn->real_escape_string($_POST['comment']);
    $conn->query("INSERT INTO comments (user_id, video_id, comment_text) VALUES ('$user_id', '$video_id', '$comment_text')");
    
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Handle Like Submission
if (isset($_POST['like'])) {
    $conn->query("INSERT IGNORE INTO ratings (user_id, video_id) VALUES ('$user_id', '$video_id')");
    
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Get Video Details
$video = $conn->query("SELECT * FROM videos WHERE id=$video_id")->fetch_assoc();

// Get Comments
$comments = $conn->query("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id = users.id WHERE video_id=$video_id ORDER BY created_at DESC");

// Get Like Count
$like_count = $conn->query("SELECT COUNT(*) AS total FROM ratings WHERE video_id=$video_id")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --bg-light: #f4f4f4;
            --text-dark: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Video Responsive Container */
        .video-wrapper {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .video-wrapper video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Interactive Buttons */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn-interactive {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border: none;
            border-radius: 25px;
            background-color: #f8f9fa;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .btn-interactive:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-interactive i {
            font-size: 1.2rem;
        }

        /* Comments Section */
        .comments-section {
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .comment-item {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .comment-form {
            margin-bottom: 20px;
        }

        /* Responsive Typography */
        @media screen and (max-width: 768px) {
            body {
                font-size: 14px;
            }

            .video-wrapper {
                padding-bottom: 75%; /* More square for mobile */
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-interactive {
                width: 100%;
                justify-content: center;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                font-size: 13px;
            }

            .video-wrapper {
                border-radius: 0;
            }
        }

        /* Accessibility Enhancements */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }
.navbar {
    background-color: white;
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
        background-color: white;
        padding: 8px 15px;
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
            display: inline-block; /* Make the box fit the content */
            padding: 6px; /* Add some padding inside the box */
            border-radius: 20px; /* Increased rounded corners */
            color: #0E47B4 !important; <!-- Force black -->
             text-decoration: bold;
            font-weight: 500;
            gap; 10px;

        ">Profile</p> <!-- FontAwesome user icon -->
    </a>
    <a class="nav-link" href="../shop/products.php">
        <p style="
            display: inline-block; /* Make the box fit the content */
            padding: 6px; /* Add some padding inside the box */
            border-radius: 20px; /* Increased rounded corners */
            color: #0E47B4 !important; <!-- Force black -->
            text-decoration: bold;
            font-weight: 500;
                        gap; 10px;



        ">Shop</p> <!-- FontAwesome shop icon -->
    </a>
</div>

        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
    style="background-color: white; padding: 0.4rem; border: none;">
    <span class="navbar-toggler-icon" style="
        background-image: url('data:image/svg+xml;charset=utf8,<svg viewBox=\'0 0 30 30\' xmlns=\'http://www.w3.org/2000/svg\'><path stroke=\'%230E47B4\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' d=\'M4 7h22M4 15h22M4 23h22\'/></svg>');
        width: 1.5em;
        height: 1.5em;
    "></span>
</button>
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../user.php">Home </a></li>
                <li class="breadcrumb-item"><a href="vid_display.php">Health Talk's Gallery</a></li>
                <li class="breadcrumb-item active">Health Talk</li>
            </ol>
        </div>
    </div>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link active" href="../user.php">
                    <i class="fas fa-home me-1"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="display_messages.php">Day's Thought</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="winner_list.php">health Champs</a>
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
                <a class="nav-link" href="../shop/products.php">
                     Shop
                </a>
            </li>
        </ul>
    </div>
</nav>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="my-3"><?= htmlspecialchars($video['title']) ?></h2>
                
                <div class="video-wrapper">
                    <video src="uploads/<?= $video['video_path'] ?>" controls playsinline></video>
                </div>
                
                <p class="mb-3"><?= htmlspecialchars($video['description']) ?></p>
                
                <div class="action-buttons">
    <form method="POST" class="w-100">
        <button name="like" class="btn-interactive w-100" style=" color: #FF7C2A; border: none; padding: 8px; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-thumbs-up"></i> Like (<?= $like_count ?>)
        </button>
    </form>
    
    <button class="btn-interactive w-100" onclick="navigator.share({url: window.location.href})" style=" color: #FF7C2A; border: none; padding: 8px; border-radius: 4px; cursor: pointer; margin-left: 10px;">
        <i class="fas fa-share-alt"></i> Share
    </button>
</div>

<style>
    .action-buttons {
        display: flex;
        flex-direction: row;
    }
    
    .w-100 {
        width: 100%;
    }
</style>


<div class="comment-form" style="padding: 10px; margin-top: 15px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
        <h4 style="margin: 0; font-size: 1.2rem; color:#FF7C2A">Comments</h4>
        <span style="
            background-color: #FF7C2A;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.9rem;
            font-weight: bold;
        "><?= $comment_count ?></span>
    </div>
    
    <form method="POST">
        <textarea name="comment" style="
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            min-height: 80px;
            font-family: inherit;
            font-size: 14px;
            box-sizing: border-box;
        " required placeholder="Write a comment..."></textarea>
        
        <button type="submit" style="
            width: 100%;
            padding: 10px;
            background-color: #FF7C2A;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        ">Post Comment</button>
    </form>
</div>

<style>
    @media (max-width: 768px) {
        .comment-form {
            padding: 8px !important;
        }
        
        textarea {
            font-size: 16px !important;
            min-height: 100px !important;
        }
        
        button[type="submit"] {
            padding: 12px !important;
        }
    }
</style>
                <div class="comments-section" style="margin-top:-20px;">
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <div class="comment-item">
                            <strong><?= htmlspecialchars($comment['name']) ?>:</strong> 
                            <?= htmlspecialchars($comment['comment_text']) ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
            </div>
        </div>
    </div>

    <script>
        // Check if the page is being accessed via back navigation
        if (window.performance.navigation.type == window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.replace("../user.php");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
<a href="https://wa.me/94777867942" class="get-started-button" style="background-color: #FF7C2A;" target="_blank" rel="noopener noreferrer">
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
</body>
</html>