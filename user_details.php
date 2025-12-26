<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$success = false;

// Fetch user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>alert('User not found!'); window.location.href = 'login.php';</script>";
    exit();
}

// Fetch additional details for the user
$additional_result = $conn->query("SELECT * FROM user_additional_details WHERE user_id = $user_id");
$additional_details = [];
if ($additional_result && $additional_result->num_rows > 0) {
    $additional_details = $additional_result->fetch_assoc();
}

// Fetch activity data
$likes_result = $conn->query("SELECT * FROM likes WHERE user_id = $user_id");
$likes = $likes_result->fetch_all(MYSQLI_ASSOC);

$comments_result = $conn->query("SELECT * FROM `mesg-comments` WHERE user_id = $user_id");
$mesg_comments = $comments_result->fetch_all(MYSQLI_ASSOC);

$ratings_result = $conn->query("SELECT * FROM ratings WHERE user_id = $user_id");
$ratings = $ratings_result->fetch_all(MYSQLI_ASSOC);

// Fetch weekly answers count
$answers_result = $conn->query("SELECT COUNT(*) as answer_count FROM weekly_answers WHERE user_id = $user_id");
$answers_count = $answers_result->fetch_assoc()['answer_count'];

// Fetch weekly answers for timeline
$weekly_answers = [];
$answers_timeline_result = $conn->query("SELECT wa.*, wq.question 
                                       FROM weekly_answers wa
                                       JOIN weekly_questions wq ON wa.question_id = wq.id
                                       WHERE wa.user_id = $user_id
                                       ORDER BY wa.created_at DESC");
if ($answers_timeline_result) {
    $weekly_answers = $answers_timeline_result->fetch_all(MYSQLI_ASSOC);
}

// Fetch all data for raw display
$users_data = [];
$users_result = $conn->query("SELECT * FROM users");
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users_data[] = $row;
    }
}

$additional_data = [];
$additional_result_all = $conn->query("SELECT * FROM user_additional_details");
if ($additional_result_all) {
    while ($row = $additional_result_all->fetch_assoc()) {
        $additional_data[] = $row;
    }
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['profile_picture'])) {
        // Handle photo upload
        $target_dir = "uploads/profile_pictures/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        
        if ($check !== false) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $update_sql = "UPDATE users SET profile_picture='$target_file' WHERE id=$user_id";
                if ($conn->query($update_sql)) {
                    $success = true;
                    $user['profile_picture'] = $target_file;
                }
            }
        }
    } else {
        // Handle other form submissions
        $area = $conn->real_escape_string($_POST['area'] ?? '');
        $occupation = $conn->real_escape_string($_POST['occupation'] ?? '');
        $whatsapp = $conn->real_escape_string($_POST['whatsapp'] ?? '');
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        
        $update_sql = "UPDATE users SET 
                       area='$area', 
                       occupation='$occupation', 
                       whatsapp='$whatsapp', 
                       email='$email' 
                       WHERE id=$user_id";
        $conn->query($update_sql);
        
        $permanent_address = $conn->real_escape_string($_POST['permanent_address'] ?? '');
        $postal_address = $conn->real_escape_string($_POST['postal_address'] ?? '');
        $nationality = $conn->real_escape_string($_POST['nationality'] ?? '');
        $race = $conn->real_escape_string($_POST['race'] ?? '');
        $religion = $conn->real_escape_string($_POST['religion'] ?? '');
        $earning_or_dependant = $conn->real_escape_string($_POST['earning_or_dependant'] ?? '');
        $monthly_income = $conn->real_escape_string($_POST['monthly_income'] ?? '');
        $highest_education = $conn->real_escape_string($_POST['highest_education'] ?? '');
        $currently_living_with = $conn->real_escape_string($_POST['currently_living_with'] ?? '');
        $performing_activities = $conn->real_escape_string($_POST['performing_activities'] ?? '');
        $hobbies = $conn->real_escape_string($_POST['hobbies'] ?? '');
        
        if (!empty($additional_details)) {
            $update_additional_sql = "UPDATE user_additional_details SET 
                                     permanent_address='$permanent_address', 
                                     postal_address='$postal_address', 
                                     nationality='$nationality', 
                                     race='$race', 
                                     religion='$religion', 
                                     earning_or_dependant='$earning_or_dependant', 
                                     monthly_income='$monthly_income', 
                                     highest_education='$highest_education', 
                                     currently_living_with='$currently_living_with', 
                                     performing_activities='$performing_activities', 
                                     hobbies='$hobbies' 
                                     WHERE user_id=$user_id";
        } else {
            $update_additional_sql = "INSERT INTO user_additional_details 
                                     (user_id, permanent_address, postal_address, nationality, race, religion, 
                                     earning_or_dependant, monthly_income, highest_education, 
                                     currently_living_with, performing_activities, hobbies) 
                                     VALUES 
                                     ('$user_id', '$permanent_address', '$postal_address', '$nationality', 
                                     '$race', '$religion', '$earning_or_dependant', '$monthly_income', 
                                     '$highest_education', '$currently_living_with', '$performing_activities', '$hobbies')";
        }
        $conn->query($update_additional_sql);
        
        $success = true;
    }
}

// Close connection after all database operations are complete
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Main container styling */
        .container.mt-5 {
            margin-top: 30px;
            max-width: 1200px;
        }
        
        /* Card styling */
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }
        
        .card.p-4 {
            padding: 30px !important;
        }
        
        /* Profile picture styling */
        .img-fluid.rounded-circle {
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        
        .img-fluid.rounded-circle:hover {
            transform: scale(1.05);
        }
        
        /* Details display styling */
        .details-display p {
            font-size: 16px;
            margin-bottom: 10px;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .details-display p:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        
        .details-display strong {
            color: #2c3e50;
            min-width: 150px;
            display: inline-block;
        }
        
        /* Form styling */
        .edit-form, .additional-details-form, .photo-form {
            display: none;
            animation: fadeIn 0.5s ease;
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.25rem rgba(74, 144, 226, 0.25);
        }
        
        /* Button styling */
        .btn-toggle-edit {
            background: linear-gradient(135deg, #4a90e2, #6a5acd);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
            margin: 10px auto;
            display: block;
        }
        
        .btn-toggle-additional {
            background: linear-gradient(135deg, #6a5acd, #4a90e2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 90, 205, 0.3);
            margin: 10px auto;
            display: block;
        }
        
        .btn-toggle-photo {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
            margin: 10px auto;
            display: block;
        }
        
        .btn-toggle-edit:hover, 
        .btn-toggle-additional:hover,
        .btn-toggle-photo:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.4);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a5acd, #4a90e2);
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
            width: 100%;
            margin-top: 15px;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.4);
        }
        
        /* Section headers */
        h2.text-center {
            color: #2c3e50;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
            font-weight: 700;
        }
        
        h2.text-center:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4a90e2, #6a5acd);
        }
        
        /* Close button for forms */
        .close-form {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff6b6b;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .close-form:hover {
            background: #ff5252;
            transform: rotate(90deg);
        }
        
        /* Form container positioning */
        .form-container {
            position: relative;
        }
        
        /* Button container */
        .button-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        /* Preview image styling */
        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
            margin: 15px auto;
            display: block;
        }
        
        /* Raw data display styles */
        .raw-data-container {
            margin-top: 40px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .raw-data-section {
            margin-bottom: 30px;
        }
        
        .raw-data-title {
            color: #2c3e50;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        pre {
            background-color: #2c3e50;
            color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        
        .toggle-raw-data {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px auto;
            display: block;
            cursor: pointer;
        }
        
        /* Timeline styling */
        .timeline {
            position: relative;
            padding-left: 50px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }
        
        .timeline-badge {
            position: absolute;
            left: -50px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .timeline-content {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 3px solid #4a90e2;
        }
        
        /* Activity cards */
        .activity-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .activity-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .activity-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
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
/* Responsive Heading Styles */
.responsive-heading {
    transition: all 0.3s ease;
}

/* Large screens */
@media (min-width: 1200px) {
    .responsive-heading {
        margin: 0 0 0 10px !important;
        font-size: 1.5rem !important;
    }
}

/* Medium screens */
@media (min-width: 768px) and (max-width: 1199px) {
    .responsive-heading {
        margin: 0 0 0 10px !important;
        font-size: 1.4rem !important;
    }
}

/* Small screens */
@media (min-width: 576px) and (max-width: 767px) {
    .responsive-heading {
        margin: 0 0 0 10px !important;
        font-size: 1.3rem !important;
        padding: 5px 15px !important;
    }
}

/* Extra small screens */
@media (max-width: 575px) {
    .responsive-heading {
        margin: 0 5px !important;
        font-size: 1.2rem !important;
        padding: 5px 10px !important;
        letter-spacing: 0.5px !important;
        border-top-width: 10px !important;
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
    </style>

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="#">
            <img src="Admin/img/log.png" alt="Logo" style="width:100%;">
        </a>
        
                <div class="d-flex d-lg-none" style="">
            <a class="nav-link" href="../shop/product.php">
                <p style="
                   display: inline-block;
            padding: 6px;
            border-radius: 20px;
            margin: -10px;
            color: #0E47B4 !important; <!-- Force black -->
                  
                ">Shop</p>
            </a>
             <a class="nav-link" href="login.php">
                <p style="
                   display: inline-block;
            padding: 6px;
            border-radius: 20px;
            margin: -10px;
            color: red !important; <!-- Force black -->
                ">Log Out</p>
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
                <li class="breadcrumb-item active">Profile Details </li>
            </ol>
        </div>
    </div>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link active" href="user.php">
                    Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Admin/display_messages.php">Day's Thought</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Admin/winner_list.php">Health Champs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Admin/vid_display.php">Health Talks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Admin/download_list.php">Downloads</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Admin/event_display.php">Events</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="login.php" style="color: red;">Log Out</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4" style="margin-top:-60px;">
                <center>
                    <h2 class="text-center">My Profile</h2>
                    <div id="profileImageContainer">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-fluid rounded-circle mb-3">
                        <?php else: ?>
                            <div class="image-preview bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-toggle-edit" onclick="togglePhotoForm()">Update Photo</button>
                </center>
                
               <!-- Photo Upload Form -->
                <div class="photo-form" id="photoFormContainer">
                    <div class="form-container">
                        <button class="close-form" onclick="togglePhotoForm()">&times;</button>
                        <form method="POST" id="photoForm" enctype="multipart/form-data">
                            <div class="mb-3 text-center">
                                <div id="imagePreview" class="image-preview mb-3">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" class="img-fluid rounded-circle" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <label class="form-label">Choose New Profile Picture:</label>
                                <input type="file" name="profile_picture" id="profilePictureInput" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Photo</button>
                        </form>
                    </div>
                </div>
                
                <script>
                    function togglePhotoForm() {
    const form = document.getElementById('photoFormContainer');
    if (form.style.display === 'block') {
        form.style.display = 'none';
    } else {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    }
}
                </script>
                
              <!-- Replace the existing details-display div with this code -->
<div class="details-container mt-4">
    <button class="btn btn-primary w-100 d-flex justify-content-between align-items-center" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#userDetailsCollapse">
        <span>My Details</span>
        <i class="fas fa-chevron-down"></i>
    </button>
    
    <div class="collapse" id="userDetailsCollapse">
        <div class="card card-body mt-2">
            <div class="details-display">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name'] ?? ''); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['dob'] ?? ''); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender'] ?? ''); ?></p>
                <p><strong>Marital Status:</strong> <?php echo htmlspecialchars($user['marital_status'] ?? ''); ?></p>
                <p><strong>Area:</strong> <?php echo htmlspecialchars($user['area'] ?? ''); ?></p>
                <p><strong>Occupation:</strong> <?php echo htmlspecialchars($user['occupation'] ?? ''); ?></p>
                <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($user['whatsapp'] ?? ''); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.details-container .btn {
    background: linear-gradient(135deg, #4a90e2, #6a5acd);
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.details-container .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
}

.details-container .btn i {
    transition: transform 0.3s ease;
}

.details-container .btn[aria-expanded="true"] i {
    transform: rotate(180deg);
}

.details-display p {
    margin-bottom: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    background-color: #f8f9fa;
}

.details-display strong {
    color: #2c3e50;
    min-width: 120px;
    display: inline-block;
}
</style>
<!-- User Details Section with Collapsible Button -->


<!-- Edit Button -->
<div class="button-container mt-3">
    <button style="margin-top:5px;" class="btn btn-primary w-100 d-flex justify-content-between align-items-center" 
            type="button" 
            onclick="toggleAllForms()">
        <span><i class="fas fa-edit me-2"></i>Edit & Add All Details</span>
        <i class="fas fa-chevron-down"></i>
    </button>
</div>

<!-- Combined Form (Hidden by Default) -->
<div class="mt-2" id="combinedFormContainer" style="display: none;">
    <div class="card p-4">
        <form method="POST" id="combinedForm" enctype="multipart/form-data">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Edit Your Details</h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllForms()">
                    <i style="color:red;" class="fas fa-times"></i> 
                </button>
            </div>
            
            <!-- Basic Details Section -->
            <div class="mb-4">
                <h4 class="border-bottom pb-2">Basic Details</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth:</label>
                        <input type="text" name="dob" class="form-control" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gender:</label>
                        <input type="text" name="gender" class="form-control" value="<?php echo htmlspecialchars($user['gender'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Marital Status:</label>
                        <input type="text" name="marital_status" class="form-control" value="<?php echo htmlspecialchars($user['marital_status'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Area:</label>
                        <input type="text" name="area" class="form-control" value="<?php echo htmlspecialchars($user['area'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Occupation:</label>
                        <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">WhatsApp:</label>
                        <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($user['whatsapp'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Additional Details Section -->
            <div class="mb-4">
                <h4 class="border-bottom pb-2">Additional Details</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Permanent Address:</label>
                        <input type="text" name="permanent_address" class="form-control" value="<?php echo htmlspecialchars($additional_details['permanent_address'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Postal Address:</label>
                        <input type="text" name="postal_address" class="form-control" value="<?php echo htmlspecialchars($additional_details['postal_address'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nationality:</label>
                        <input type="text" name="nationality" class="form-control" value="<?php echo htmlspecialchars($additional_details['nationality'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Race:</label>
                        <input type="text" name="race" class="form-control" value="<?php echo htmlspecialchars($additional_details['race'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Religion:</label>
                        <input type="text" name="religion" class="form-control" value="<?php echo htmlspecialchars($additional_details['religion'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employment Status:</label>
                        <select name="earning_or_dependant" class="form-control">
                            <option value="Earning" <?= (isset($additional_details['earning_or_dependant']) && $additional_details['earning_or_dependant'] == 'Earning') ? 'selected' : '' ?>>Earning</option>
                            <option value="Depending" <?= (isset($additional_details['earning_or_dependant']) && $additional_details['earning_or_dependant'] == 'Depending') ? 'selected' : '' ?>>Depending</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Monthly Income:</label>
                        <input type="number" name="monthly_income" class="form-control" value="<?php echo htmlspecialchars($additional_details['monthly_income'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
    <label class="form-label">Highest Education:</label>
    <select name="highest_education" class="form-control">
        <option value="">-- Select Education Level --</option>
        <option value="OL" <?= (isset($additional_details['highest_education']) && $additional_details['highest_education'] == 'OL') ? 'selected' : '' ?>>OL</option>
        <option value="AL" <?= (isset($additional_details['highest_education']) && $additional_details['highest_education'] == 'AL') ? 'selected' : '' ?>>AL</option>
        <option value="Bsc" <?= (isset($additional_details['highest_education']) && $additional_details['highest_education'] == 'Bsc') ? 'selected' : '' ?>>Bsc</option>
        <option value="Master Degree" <?= (isset($additional_details['highest_education']) && $additional_details['highest_education'] == 'Master Degree') ? 'selected' : '' ?>>Master Degree</option>
        <option value="Phd" <?= (isset($additional_details['highest_education']) && $additional_details['highest_education'] == 'Phd') ? 'selected' : '' ?>>Phd</option>
    </select>
</div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Living With:</label>
                        <input type="text" name="currently_living_with" class="form-control" value="<?php echo htmlspecialchars($additional_details['currently_living_with'] ?? ''); ?>">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Activities:</label>
                        <textarea name="performing_activities" class="form-control" rows="3"><?php echo htmlspecialchars($additional_details['performing_activities'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Hobbies:</label>
                        <textarea name="hobbies" class="form-control" rows="3"><?php echo htmlspecialchars($additional_details['hobbies'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Save All Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Function to toggle the combined form
function toggleAllForms() {
    const form = document.getElementById('combinedFormContainer');
    const btn = document.querySelector('.button-container button');
    const icon = btn.querySelector('.fa-chevron-down');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        btn.querySelector('span').innerHTML = '<i class="fas fa-edit me-2"></i>Hide Edit Form';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        btn.querySelector('span').innerHTML = '<i class="fas fa-edit me-2"></i>Edit & Add All Details';
    }
}

// Close form when clicking outside
document.addEventListener('click', function(e) {
    const formContainer = document.getElementById('combinedFormContainer');
    const btn = document.querySelector('.button-container button');
    
    if (!e.target.closest('#combinedFormContainer') && !e.target.closest('.button-container')) {
        formContainer.style.display = 'none';
        const icon = btn.querySelector('.fa-chevron-up');
        if (icon) {
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            btn.querySelector('span').innerHTML = '<i class="fas fa-edit me-2"></i>Edit & Add All Details';
        }
    }
});
</script>


<div class="mt-4">
    <!-- Toggle Button for Activity Summary -->
    <button style="margin-top:-25px;"class="btn btn-primary w-100 d-flex justify-content-between align-items-center" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#activitySummaryCollapse"
            aria-expanded="false" 
            aria-controls="activitySummaryCollapse">
        <span>My Activity Summary</span>
        <i class="fas fa-chevron-down"></i>
    </button>
    
    <!-- Activity Summary Section (collapsible) -->
    <div class="collapse" id="activitySummaryCollapse">
        <div class="card p-4 mt-2">
            <div class="row">
                <!-- Daily Messages Card -->
                <div class="col-md-3 mb-4 activity-col">
                    <div class="card h-100 activity-card">
                        <div class="card-body text-center">
                            <div class="activity-icon text-primary">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <h4>Daily Messages</h4>
                            <p class="display-5"><?php echo count($likes); ?></p>
                            <p class="text-muted">Messages you've liked</p>
                        </div>
                    </div>
                </div>
                
                <!-- Comments Card -->
                <div class="col-md-3 mb-4 activity-col">
                    <div class="card h-100 activity-card">
                        <div class="card-body text-center">
                            <div class="activity-icon text-success">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h4>Comments</h4>
                            <p class="display-5"><?php echo count($mesg_comments); ?></p>
                            <p class="text-muted">Comments you've posted</p>
                        </div>
                    </div>
                </div>
                
                <!-- Video Interactions Card -->
                <div class="col-md-3 mb-4 activity-col">
                    <div class="card h-100 activity-card">
                        <div class="card-body text-center">
                            <div class="activity-icon text-info">
                                <i class="fas fa-video"></i>
                            </div>
                            <h4>Video Views</h4>
                            <p class="display-5"><?php echo count($ratings); ?></p>
                            <p class="text-muted">Videos you've interacted with</p>
                        </div>
                    </div>
                </div>
                
                <!-- Question Answers Card -->
                <div class="col-md-3 mb-4 activity-col">
                    <div class="card h-100 activity-card">
                        <div class="card-body text-center">
                            <div class="activity-icon text-warning">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h4>Q&A</h4>
                            <p class="display-5"><?php echo count($weekly_answers); ?></p>
                            <p class="text-muted">Questions you've answered</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Rotate chevron icon when collapsed
document.getElementById('activitySummaryCollapse').addEventListener('show.bs.collapse', function() {
    this.previousElementSibling.querySelector('.fa-chevron-down').classList.replace('fa-chevron-down', 'fa-chevron-up');
});

document.getElementById('activitySummaryCollapse').addEventListener('hide.bs.collapse', function() {
    this.previousElementSibling.querySelector('.fa-chevron-up').classList.replace('fa-chevron-up', 'fa-chevron-down');
});
</script>
        
<!-- Recent Activity Section -->
<div class="mt-4">
    <!-- Toggle Button - Now matches your preferred full-width style -->
    <button style="margin-top:-5px;" class="btn btn-primary w-100 d-flex justify-content-between align-items-center" 
            type="button" 
            id="toggleActivityTimelineBtn">
        <span><i class="fas fa-history me-2"></i>Recent Activity</span>
        <i class="fas fa-chevron-down"></i>
    </button>
    
    <!-- Recent Activity Timeline (initially hidden) -->
    <div class="mt-2" id="activityTimeline" style="display: none;">
        <div class="card p-3">
            <h4 class="mb-3">Recent Activity</h4>
            <div class="timeline">
        <?php
        // Combine different activity types into one timeline
        $activities = [];
        
        // Add comments to timeline
        foreach ($mesg_comments as $comment) {
            $activities[] = [
                'date' => $comment['created_at'],
                'type' => 'comment',
                'content' => 'Commented: "'.htmlspecialchars(substr($comment['comment_text'], 0, 50)).(strlen($comment['comment_text']) > 50 ? '...' : '').'"',
                'icon' => 'comment',
                'link' => 'Admin/display_messages.php'
            ];
        }
        
        // Add likes to timeline
        foreach ($likes as $like) {
            $activities[] = [
                'date' => $like['created_at'] ?? date('Y-m-d H:i:s'),
                'type' => 'like',
                'content' => 'Liked a daily message',
                'icon' => 'thumbs-up',
                'link' => 'Admin/display_messages.php'
            ];
        }
        
        // Add video ratings to timeline
        foreach ($ratings as $rating) {
            $rating_value = $rating['rating_value'] ?? 0;
            $activities[] = [
                'date' => $rating['created_at'] ?? date('Y-m-d H:i:s'),
                'type' => 'rating',
                'content' => 'Rated a health video ('.$rating_value.' stars)',
                'icon' => 'star',
                'link' => 'Admin/vid_display.php'
            ];
        }
        
        // Add weekly answers to timeline
        foreach ($weekly_answers as $answer) {
            $activities[] = [
                'date' => $answer['created_at'],
                'type' => 'answer',
                'content' => 'Answered: "'.htmlspecialchars(substr($answer['question'], 0, 50)).(strlen($answer['question']) > 50 ? '...' : '').'"',
                'icon' => 'question-circle',
                'link' => 'Admin/weekly_questions_display.php'
            ];
        }
        
        // Sort activities by date (newest first)
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        if (empty($activities)) {
            echo '<p class="text-muted">No recent activity found.</p>';
        } else {
            foreach ($activities as $activity) {
                echo '
                <div class="timeline-item mb-3">
                    <div class="timeline-badge bg-primary">
                        <i class="fas fa-'.$activity['icon'].'"></i>
                    </div>
                    <div class="timeline-content p-3">
                        <p class="mb-1">'.$activity['content'].'</p>
                        <small class="text-muted">'.date('M j, Y g:i a', strtotime($activity['date'])).'</small>
                        <div class="mt-2">
                            <a href="'.$activity['link'].'" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                </div>';
            }
        }
        ?>
    </div>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleActivityTimelineBtn').addEventListener('click', function() {
    const activityTimeline = document.getElementById('activityTimeline');
    const icon = this.querySelector('.fa-chevron-down');
    
    if (activityTimeline.style.display === 'none') {
        activityTimeline.style.display = 'block';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        this.querySelector('span').innerHTML = '<i class="fas fa-history me-2"></i>Hide Recent Activity';
    } else {
        activityTimeline.style.display = 'none';
        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        this.querySelector('span').innerHTML = '<i class="fas fa-history me-2"></i>Recent Activity';
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</div>
</div>
</div></div>
</div>
<style>


/* Footer Styles */
.footer {
    background-color: white;
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
            <img src="Admin/img/log.png" alt="Logo" style="width:90%;"> <!-- Add your logo image here -->
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
                    <a href="Admin/display_messages.php" class="footer-link">Day's Thoughts</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/winner_list.php" class="footer-link">Health Champs</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/vid_display.php" class="footer-link">Health Talks</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/download_list.php" class="footer-link">Downloads </a>
                </li>
                <li class="footer-item">
                    <a href="Admin/event_display.php" class="footer-link">Event</a>
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
</body>
</html>