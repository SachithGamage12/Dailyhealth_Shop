<?php
// Start session for user authentication
session_start();

// Database Connection
$host = "localhost";
$user = "u627928174_root";
$pass = "Daily@365";
$dbname = "u627928174_daily_routine";
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle like and share actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'like_specialday' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            exit;
        }
        
        // Update like count
        $sql = "UPDATE specialdays SET likes = likes + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Get the updated like count
            $result = $conn->query("SELECT likes FROM specialdays WHERE id = $id");
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'likes' => $row['likes']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update like count']);
        }
        $stmt->close();
        exit;
    }
    elseif ($_POST['action'] === 'share_specialday' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            exit;
        }
        
        // Update share count
        $sql = "UPDATE specialdays SET shares = shares + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Get the updated share count
            $result = $conn->query("SELECT shares FROM specialdays WHERE id = $id");
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'shares' => $row['shares']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update share count']);
        }
        $stmt->close();
        exit;
    }
}

// Rest of your existing code for displaying special messages...
// [Previous code continues here...]

// Get selected calendar from dropdown
$selectedCalendar = isset($_GET['calendar']) ? $_GET['calendar'] : 'calendar1';

// Initialize date search variables
$searchDate = isset($_GET['search_date']) ? $_GET['search_date'] : '';
$searchMonth = isset($_GET['search_month']) ? $_GET['search_month'] : '';
$searchYear = isset($_GET['search_year']) ? $_GET['search_year'] : '';

// Fetch the latest special message for the selected calendar
$latestSql = "SELECT * FROM specialdays WHERE calendar_type = '$selectedCalendar' ORDER BY year DESC, month DESC, date DESC LIMIT 1";
$latestResult = $conn->query($latestSql);
$latestRow = null;
if ($latestResult && $latestResult->num_rows > 0) {
    $latestRow = $latestResult->fetch_assoc();
}

// Function to get comments for a special day
function getSpecialDayComments($conn, $specialday_id) {
    $specialday_id = (int)$specialday_id;
    $comments = array();
    $result = $conn->query("SELECT c.*, u.name FROM `specialday_comments` c JOIN users u ON c.user_id = u.id WHERE c.specialday_id = $specialday_id ORDER BY c.created_at DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
    }
    return $comments;
}

// Function to get comment count for special day
function getSpecialDayCommentCount($conn, $specialday_id) {
    $specialday_id = (int)$specialday_id;
    $result = $conn->query("SELECT COUNT(*) as count FROM `specialday_comments` WHERE specialday_id = $specialday_id");
    return $result ? $result->fetch_assoc()['count'] : 0;
}

// Handle comment submission via AJAX
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add_specialday_comment' && isset($_POST['specialday_id']) && isset($_POST['comment_text'])) {
        $specialday_id = (int)$_POST['specialday_id'];
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $comment_text = $conn->real_escape_string($_POST['comment_text']);
        
        if ($user_id > 0 && !empty($comment_text)) {
            $conn->query("INSERT INTO `specialday_comments` (user_id, specialday_id, comment_text) VALUES ($user_id, $specialday_id, '$comment_text')");
            echo json_encode(['status' => 'success', 'comments' => getSpecialDayComments($conn, $specialday_id)]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        }
        exit();
    }
    elseif ($_POST['action'] == 'get_specialday_comments' && isset($_POST['specialday_id'])) {
        $specialday_id = (int)$_POST['specialday_id'];
        echo json_encode(getSpecialDayComments($conn, $specialday_id));
        exit();
    }
}

// Fetch previous special messages for the selected calendar
$searchCondition = "";
if (!empty($searchDate) || !empty($searchMonth) || !empty($searchYear)) {
    $conditions = [];
    if (!empty($searchDate)) {
        $conditions[] = "date = '$searchDate'";
    }
    if (!empty($searchMonth)) {
        $conditions[] = "month = '$searchMonth'";
    }
    if (!empty($searchYear)) {
        $conditions[] = "year = '$searchYear'";
    }
    $searchCondition = " AND " . implode(" AND ", $conditions);
}

// Query for other special messages (excluding the latest one)
$otherMessagesSql = "SELECT * FROM specialdays WHERE calendar_type = '$selectedCalendar' $searchCondition ORDER BY year DESC, month DESC, date DESC";
if ($latestRow) {
    $otherMessagesSql = "SELECT * FROM specialdays WHERE calendar_type = '$selectedCalendar' AND id != '{$latestRow['id']}' $searchCondition ORDER BY year DESC, month DESC, date DESC";
}
$otherMessagesResult = $conn->query($otherMessagesSql);

// Get distinct years, months, and dates for search filters
$yearsQuery = "SELECT DISTINCT year FROM specialdays WHERE calendar_type = '$selectedCalendar' ORDER BY year DESC";
$yearsResult = $conn->query($yearsQuery);

$monthsQuery = "SELECT DISTINCT month FROM specialdays WHERE calendar_type = '$selectedCalendar' ORDER BY month ASC";
$monthsResult = $conn->query($monthsQuery);

$datesQuery = "SELECT DISTINCT date FROM specialdays WHERE calendar_type = '$selectedCalendar' ORDER BY date ASC";
$datesResult = $conn->query($datesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
    body { 
        font-family: Arial, sans-serif; 
        background-color: white; 
        margin: -10px; 
        padding: 0; 
    }
    .container { 
        width: 95%; 
        margin: auto; 
        padding: 20px; 
    }
    h2 { 
        text-align: center; 
    }
    .card { 
        background: white; 
        box-shadow: 0px 0px 8px rgba(0,0,0,0.1); 
        border-radius: 8px; 
        margin-bottom: 20px;
        height: 100%;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .card img { 
        width: 100%; 
        height: 200px; 
        object-fit: cover; 
        border-radius: 4px 4px 0 0; 
    }
    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-top: -25px; 
        margin-bottom: 20px;
        box-shadow: 0px 0px 8px rgba(0,0,0,0.1);
    }
    .latest-message {
        margin-bottom: 30px;
    }
    .read-more { 
        color: blue; 
        cursor: pointer; 
    }
    .other-messages {
        margin-top: 30px;
    }
    .responsive-heading {
        font-size: 1.5rem; 
        font-weight: 700; 
        color: #333; 
        padding: 5px 20px;
        border-top: 15px solid rgb(255, 255, 255); 
        background-color:white; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        width: auto; 
        margin: 0 5% 20px 5%; 
        border-bottom: none; 
        text-align: left;
    }
    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    .search-form .form-group {
        flex: 1;
        min-width: 150px;
    }
    .section-title {
        border-left: 4px solid #007bff;
        padding-left: 10px;
        margin-bottom: 20px;
    }
    .form-label {
        font-weight: 600;
        color: #555;
    }
    .form-select {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
        font-size: 14px;
        color: #333;
        background-color: #f9f9f9;
        transition: border-color 0.3s ease;
    }
    .form-select:focus {
        border-color: #007bff;
        outline: none;
    }
    .btn {
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }
    .btn-primary:hover {
        background-color: #0056b3;
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    .button-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .buttons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    .like-share-btn {
        background: white; 
        color: blue;
        border: none;
        border-radius: 5px;
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease;
    }
    .like-share-btn:hover {
        background: #f9f1f0;
    }
    .icon-text {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .icon-text i {
        font-size: 18px;
    }
    .icon-text span {
        font-size: 14px;
    }
    /* Modal image styling */
    #modalImage {
        max-height: 400px;
        object-fit: contain;
        width: 100%;
    }
    /* Modal description styling */
    #modalDescription {
        white-space: pre-line;
        line-height: 1.6;
    }
    /* Horizontal scroll for previous messages */
    .previous-messages-container {
        display: flex;
        overflow-x: auto;
        padding-bottom: 15px;
        scrollbar-width: thin;
        scrollbar-color: #007bff #f1f1f1;
    }
    .previous-messages-container::-webkit-scrollbar {
        height: 8px;
    }
    .previous-messages-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .previous-messages-container::-webkit-scrollbar-thumb {
        background-color: #007bff;
        border-radius: 10px;
    }
    .message-card {
        flex: 0 0 300px;
        margin-right: 15px;
    }
    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
        }
        .search-form .form-group {
            width: 100%;
        }
        .button-group {
            width: 100%;
            justify-content: flex-start;
        }
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
                <li class="breadcrumb-item active">Special Messages</li>
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
                <a class="nav-link" href="special_messages.php">Special Messages</a>
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

<div class="container" style="margin-top:-15px;">
    <a href="" style="text-decoration: none; color: inherit;">
        <h2 class="responsive-heading mb-4" id="Special_Messages">
            Special Messages
        </h2>
    </a>
    
    <!-- Filter Section -->
    <div class="filter-section">
       
           <div class="col-md-6">
    <!-- Date Search Options -->
    <form method="get" class="search-form">
        <input type="hidden" name="calendar" value="<?php echo $selectedCalendar; ?>">
        
        <div class="form-group d-flex align-items-end gap-3">
            <div class="flex-grow-1">
                <label for="search_date" class="form-label">Date:</label>
                <select name="search_date" id="search_date" class="form-select">
                    <option value="">Any</option>
                    <?php
                    if ($datesResult && $datesResult->num_rows > 0) {
                        while ($dateRow = $datesResult->fetch_assoc()) {
                            $selected = ($searchDate == $dateRow['date']) ? 'selected' : '';
                            echo "<option value='{$dateRow['date']}' $selected>{$dateRow['date']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="flex-grow-1">
                <label for="search_month" class="form-label">Month:</label>
                <select name="search_month" id="search_month" class="form-select">
                    <option value="">Any</option>
                    <?php
                    if ($monthsResult && $monthsResult->num_rows > 0) {
                        while ($monthRow = $monthsResult->fetch_assoc()) {
                            $selected = ($searchMonth == $monthRow['month']) ? 'selected' : '';
                            echo "<option value='{$monthRow['month']}' $selected>{$monthRow['month']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="flex-grow-1">
                <label for="search_year" class="form-label">Year:</label>
                <select name="search_year" id="search_year" class="form-select">
                    <option value="">Any</option>
                    <?php
                    if ($yearsResult && $yearsResult->num_rows > 0) {
                        while ($yearRow = $yearsResult->fetch_assoc()) {
                            $selected = ($searchYear == $yearRow['year']) ? 'selected' : '';
                            echo "<option value='{$yearRow['year']}' $selected>{$yearRow['year']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    
                    <!-- Button Group for Search and Reset -->
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="?calendar=<?php echo $selectedCalendar; ?>" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Latest Special Message Section -->
   <div class="latest-message">
    <h3 class="section-title">Latest Special Message</h3>
    <div class="row">
        <div class="col-md-12">
            <?php
            if ($latestRow) {
                $description = isset($latestRow['description']) ? htmlspecialchars($latestRow['description']) : '';
                $special_message = isset($latestRow['special_message']) ? htmlspecialchars($latestRow['special_message']) : '';
                $shortDescription = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                $imagePath = isset($latestRow['image_path']) ? '../Admin/uploads/specialdays/' . basename(htmlspecialchars($latestRow['image_path'])) : '';
                ?>
                <div class="card">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="<?php echo $imagePath; ?>" class="img-fluid rounded-start h-100" alt="Image" style="object-fit: cover;">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo isset($latestRow['specialday_title']) ? htmlspecialchars($latestRow['specialday_title']) : ''; ?></h5>
                                <p class="card-text"><small class="text-muted">
                                    <?php 
                                    echo isset($latestRow['date']) ? htmlspecialchars($latestRow['date']) : ''; 
                                    echo '-';
                                    echo isset($latestRow['month']) ? htmlspecialchars($latestRow['month']) : '';
                                    echo '-';
                                    echo isset($latestRow['year']) ? htmlspecialchars($latestRow['year']) : '';
                                    ?>
                                </small></p>
                                <p class="card-text" id="description-<?php echo $latestRow['id']; ?>" 
                                   data-full-description="<?php echo nl2br($description); ?>" 
                                   data-short-description="<?php echo nl2br($shortDescription); ?>">
                                    <?php echo nl2br($shortDescription); ?>
                                    <?php if (strlen($description) > 100) { ?>
                                        <span class="read-more" onclick="toggleDescription(<?php echo $latestRow['id']; ?>)">Read More</span>
                                    <?php } ?>
                                </p>
                                <?php if (!empty($special_message)) { ?>
                                    <div class="alert alert-info mt-3">
                                        <strong>Special Message:</strong> <?php echo nl2br($special_message); ?>
                                    </div>
                                <?php } ?>
                                <!-- Like, Share & Comment Buttons -->
                                <div class="buttons">
                                    <button class="like-share-btn" onclick="likeSpecialDay(<?php echo $latestRow['id']; ?>)">
                                        <div class="icon-text">
                                            <span id="like-count-<?php echo $latestRow['id']; ?>">
                                                <?php echo isset($latestRow['likes']) ? $latestRow['likes'] : 0; ?>
                                            </span>
                                            <i class="fa fa-thumbs-up"></i>
                                            <span>Like</span>
                                        </div>
                                    </button>
                                    <button class="like-share-btn" onclick="shareSpecialMessage(<?php echo $latestRow['id']; ?>)">
                                        <div class="icon-text">
                                            <span id="share-count-<?php echo $latestRow['id']; ?>">
                                                <?php echo isset($latestRow['shares']) ? $latestRow['shares'] : 0; ?>
                                            </span>
                                            <i class="fa fa-share-alt"></i>
                                            <span>Share</span>
                                        </div>
                                    </button>
                                    <button class="like-share-btn">
                                        <div class="icon-text">
                                            <span id="comment-count-<?php echo $latestRow['id']; ?>">
                                                <?php echo getSpecialDayCommentCount($conn, $latestRow['id']); ?>
                                            </span>
                                            <i class="fa fa-comment"></i>
                                            <span>Comment</span>
                                        </div>
                                    </button>
                                </div>
                                
                                <!-- Comments Container - Always visible -->
                                <div id="comments-container-<?php echo $latestRow['id']; ?>" style="margin-top: 15px;">
                                    <div class="comment-list" id="comment-list-<?php echo $latestRow['id']; ?>">
                                        <?php 
                                        $comments = getSpecialDayComments($conn, $latestRow['id']);
                                        foreach ($comments as $comment): 
                                        ?>
                                            <div class="comment-item">
                                                <div class="comment-author"><?php echo htmlspecialchars($comment['name']); ?></div>
                                                <div class="comment-text"><?php echo htmlspecialchars($comment['comment_text']); ?></div>
                                                <div class="comment-time">
                                                    <?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Comment Form - Always visible -->
                                    <form class="comment-form" onsubmit="return addSpecialDayComment(event, <?php echo $latestRow['id']; ?>)">
                                        <div class="comment-input-group">
                                            <input type="text" class="comment-input" id="comment-input-<?php echo $latestRow['id']; ?>" 
                                                   placeholder="Write a comment..." required>
                                            <button class="comment-submit-btn" type="submit">Post</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                echo "<div class='alert alert-info'>No special messages found for this calendar.</div>";
            }
            ?>
        </div>
    </div>
</div>

    <!-- Other Special Messages Section -->
    <div class="other-messages">
        <h3 class="section-title">Previous Special Messages</h3>
        <div class="previous-messages-container">
            <?php
            if ($otherMessagesResult && $otherMessagesResult->num_rows > 0) {
                while ($row = $otherMessagesResult->fetch_assoc()) {
                    if (!$row) continue; // Skip if row is null
                    
                    $description = isset($row['description']) ? htmlspecialchars($row['description']) : '';
                    $special_message = isset($row['special_message']) ? htmlspecialchars($row['special_message']) : '';
                    $shortDescription = strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description;
                    $imagePath = isset($row['image_path']) ? '../Admin/uploads/specialdays/' . basename(htmlspecialchars($row['image_path'])) : '';
                    ?>
                    <div class="message-card">
                        <div class="card h-100" data-id="<?php echo isset($row['id']) ? $row['id'] : 0; ?>">
                            <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="Image" onclick="showSpecialMessageInModal(<?php echo isset($row['id']) ? $row['id'] : 0; ?>)">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo isset($row['specialday_title']) ? htmlspecialchars($row['specialday_title']) : ''; ?></h5>
                                <p class="card-text"><small class="text-muted">
                                    <?php 
                                    echo isset($row['date']) ? htmlspecialchars($row['date']) : ''; 
                                    echo '-';
                                    echo isset($row['month']) ? htmlspecialchars($row['month']) : '';
                                    echo '-';
                                    echo isset($row['year']) ? htmlspecialchars($row['year']) : '';
                                    ?>
                                </small></p>
<p class="card-text" id="description-<?php echo isset($row['id']) ? $row['id'] : 0; ?>" 
   data-full-description="<?php echo htmlspecialchars($description); ?>">
    <span class="description-text">
        <?php 
        $shortDesc = strlen($description) > 30 ? substr($description, 0, 30) . '...' : $description;
        echo htmlspecialchars($shortDesc); 
        ?>
    </span>
    <?php if (strlen($description) > 30): ?>
        <span class="read-more" onclick="event.stopPropagation(); toggleDescription(<?php echo isset($row['id']) ? $row['id'] : 0; ?>)">
            Read More
        </span>
    <?php endif; ?>
</p>
                                <?php if (!empty($special_message)) { ?>
                                    <div class="alert alert-info mt-3">
                                        <strong>Special Message:</strong> <?php echo nl2br($special_message); ?>
                                    </div>
                                <?php } ?>
                                <!-- Like, Share & Comment Buttons -->
                                <div class="buttons">
                                    <button class="like-share-btn" onclick="event.stopPropagation(); likeSpecialDay(<?php echo $row['id']; ?>)">
                                        <div class="icon-text">
                                            <span id="like-count-<?php echo $row['id']; ?>">
                                                <?php echo isset($row['likes']) ? $row['likes'] : 0; ?>
                                            </span>
                                            <i class="fa fa-thumbs-up"></i>
                                            <span>Like</span>
                                        </div>
                                    </button>
                                    <button class="like-share-btn" onclick="event.stopPropagation(); shareSpecialMessage(<?php echo $row['id']; ?>)">
                                        <div class="icon-text">
                                            <span id="share-count-<?php echo $row['id']; ?>">
                                                <?php echo isset($row['shares']) ? $row['shares'] : 0; ?>
                                            </span>
                                            <i class="fa fa-share-alt"></i>
                                            <span>Share</span>
                                        </div>
                                    </button>
                                    <button class="like-share-btn" onclick="event.stopPropagation(); toggleComments(<?php echo $row['id']; ?>)">
                                        <div class="icon-text">
                                            <span id="comment-count-<?php echo $row['id']; ?>">
                                                <?php echo getSpecialDayCommentCount($conn, $row['id']); ?>
                                            </span>
                                            <i class="fa fa-comment"></i>
                                            <span>Comment</span>
                                        </div>
                                    </button>
                                </div>
                                
                                <!-- Comments Container -->
                               
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12'><div class='alert alert-info'>No previous special messages found.</div></div>";
            }
            ?>
        </div>
    </div>
<!-- Modal for Full Special Message Display -->
<div class="modal fade" id="specialMessageModal" tabindex="-1" aria-labelledby="specialMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="specialMessageModalLabel">Special Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="modalSpecialImage" src="" class="img-fluid rounded" alt="Special Message Image" style="max-height: 400px; object-fit: contain;">
                    </div>
                    <div class="col-md-6">
                        <h4 id="modalSpecialTitle"></h4>
                        <p class="text-muted" id="modalSpecialDate"></p>
                        <p id="modalSpecialDescription" class="mt-3"></p>
                        <div id="modalSpecialMessage" class="alert alert-info mt-3"></div>
                        <div class="buttons mt-4 d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="likeSpecialDay(modalCurrentSpecialId)">
                                <span id="modalSpecialLikeCount">0</span>
                                <i class="fa fa-thumbs-up ms-1"></i>
                                <span class="ms-1">Like</span>
                            </button>
                            <button class="btn btn-outline-primary" onclick="shareSpecialMessage(modalCurrentSpecialId)">
                                <span id="modalSpecialShareCount">0</span>
                                <i class="fa fa-share-alt ms-1"></i>
                                <span class="ms-1">Share</span>
                            </button>
                            <button class="btn btn-outline-primary" onclick="toggleModalSpecialComments(modalCurrentSpecialId)">
                                <span id="modalSpecialCommentCount">0</span>
                                <i class="fa fa-comment ms-1"></i>
                                <span class="ms-1">Comment</span>
                            </button>
                        </div>
                        
                        <!-- Comments Container for Modal -->
                        <div id="modal-special-comments-container" style="display: none; margin-top: 15px;">
                            <div class="comment-list" id="modal-special-comment-list">
                                <!-- Comments will be loaded here -->
                            </div>
                            
                            <!-- Comment Form for Modal -->
                            <form class="comment-form mt-3" onsubmit="event.preventDefault(); addModalSpecialComment(modalCurrentSpecialId)">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="modal-special-comment-input" 
                                           placeholder="Write a comment..." required>
                                    <button class="btn btn-primary" type="submit">Post</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>
<script>
// Store the current modal ID
let modalCurrentSpecialId = 0;
let specialMessageModalInstance = null;

// Initialize the modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal
    const modalElement = document.getElementById('specialMessageModal');
    if (modalElement) {
        specialMessageModalInstance = new bootstrap.Modal(modalElement);
    }
    
    // Add click event to all cards that should open the modal
    document.querySelectorAll('.card[data-id]').forEach(card => {
        card.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (id) {
                showSpecialMessageInModal(id);
            }
        });
    });
});

/**
 * Shows a special message in the modal
 * @param {number} id - The ID of the special message to show
 */
function showSpecialMessageInModal(id) {
    if (!id) return;
    
    modalCurrentSpecialId = id;
    const card = document.querySelector(`.card[data-id="${id}"]`);
    
    if (card) {
        // Get all required elements and data from the card
        const title = card.querySelector('.card-title')?.innerText || 'No title';
        const date = card.querySelector('.card-text small')?.innerText || '';
        const description = card.querySelector('.card-text[data-full-description]')?.getAttribute('data-full-description') || '';
        const specialMessage = card.querySelector('.alert-info')?.innerText || '';
        const imageElement = card.querySelector('img');
        const imageSrc = imageElement?.src || '';
        const likeCount = card.querySelector('.icon-text span:nth-child(1)')?.innerText || '0';
        const shareCount = card.querySelector('.icon-text span:nth-child(1)')?.innerText || '0';
        const commentCount = card.querySelector('.icon-text span:nth-child(1)')?.innerText || '0';
        
        // Safely set modal content
        const modalTitle = document.getElementById('modalSpecialTitle');
        const modalDate = document.getElementById('modalSpecialDate');
        const modalDescription = document.getElementById('modalSpecialDescription');
        const modalSpecialMessage = document.getElementById('modalSpecialMessage');
        const modalImage = document.getElementById('modalSpecialImage');
        const modalLikeCount = document.getElementById('modalSpecialLikeCount');
        const modalShareCount = document.getElementById('modalSpecialShareCount');
        const modalCommentCount = document.getElementById('modalSpecialCommentCount');
        
        if (modalTitle) modalTitle.innerText = title;
        if (modalDate) modalDate.innerText = date;
        if (modalDescription) modalDescription.innerHTML = nl2br(description);
        if (modalSpecialMessage) modalSpecialMessage.innerHTML = specialMessage;
        if (modalImage) modalImage.src = imageSrc;
        if (modalLikeCount) modalLikeCount.innerText = likeCount;
        if (modalShareCount) modalShareCount.innerText = shareCount;
        if (modalCommentCount) modalCommentCount.innerText = commentCount;
        
        // Hide comments container initially
        const commentsContainer = document.getElementById('modal-special-comments-container');
        if (commentsContainer) {
            commentsContainer.style.display = 'none';
        }
        
        // Show modal if instance exists
        if (specialMessageModalInstance) {
            specialMessageModalInstance.show();
        }
    }
}

/**
 * Converts newlines to <br> tags
 * @param {string} str - The string to convert
 * @returns {string} The converted string
 */
function nl2br(str) {
    if (typeof str !== 'string') return '';
    return str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');
}

/**
 * Toggles the comments section in the modal
 * @param {number} messageId - The ID of the special message
 */
function toggleModalSpecialComments(messageId) {
    const container = document.getElementById('modal-special-comments-container');
    if (!container) return;
    
    if (container.style.display === 'none') {
        // Load comments when showing
        loadCommentsForSpecialModal(messageId);
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

/**
 * Loads comments for the modal view
 * @param {number} messageId - The ID of the special message
 */
function loadCommentsForSpecialModal(messageId) {
    const commentList = document.getElementById('modal-special-comment-list');
    if (!commentList) return;
    
    // Show loading state
    commentList.innerHTML = '<div class="text-muted">Loading comments...</div>';
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_specialday_comments&specialday_id=${messageId}`
    })
    .then(response => response.json())
    .then(data => {
        commentList.innerHTML = '';
        
        if (data?.length > 0) {
            data.forEach(comment => {
                const commentItem = document.createElement('div');
                commentItem.className = 'comment-item mb-3 p-2 border-bottom';
                commentItem.innerHTML = `
                    <div class="comment-author fw-bold">${sanitizeHTML(comment.name || 'Anonymous')}</div>
                    <div class="comment-text">${sanitizeHTML(comment.comment_text || '')}</div>
                    <div class="comment-time small text-muted">
                        ${comment.created_at ? new Date(comment.created_at).toLocaleString() : ''}
                    </div>
                `;
                commentList.appendChild(commentItem);
            });
        } else {
            commentList.innerHTML = '<div class="text-muted">No comments yet</div>';
        }
    })
    .catch(error => {
        console.error('Error loading comments:', error);
        commentList.innerHTML = '<div class="text-danger">Error loading comments</div>';
    });
}

/**
 * Sanitizes HTML to prevent XSS
 * @param {string} str - String to sanitize
 * @returns {string} Sanitized string
 */
function sanitizeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Adds a comment via the modal
 * @param {number} messageId - The ID of the special message
 */
function addModalSpecialComment(messageId) {
    const commentInput = document.getElementById('modal-special-comment-input');
    if (!commentInput) return false;
    
    const commentText = commentInput.value.trim();
    
    if (commentText === '') {
        alert('Please enter a comment');
        return false;
    }
    
    const form = document.querySelector('#modal-special-comments-container form');
    const submitButton = form.querySelector('button[type="submit"]');
    if (!submitButton) return false;
    
    // Show loading state
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Posting...';
    submitButton.disabled = true;
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_specialday_comment&specialday_id=${messageId}&comment_text=${encodeURIComponent(commentText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data?.status === 'success') {
            // Clear input
            commentInput.value = '';
            
            // Update modal comment list
            loadCommentsForSpecialModal(messageId);
            
            // Update comment count in modal
            const modalCommentCount = document.getElementById('modalSpecialCommentCount');
            if (modalCommentCount && data.comments?.length !== undefined) {
                modalCommentCount.textContent = data.comments.length;
            }
            
            // Also update comment count in the card if it exists
            const cardCommentCount = document.querySelector(`.card[data-id="${messageId}"] .icon-text span:nth-child(1)`);
            if (cardCommentCount && data.comments?.length !== undefined) {
                cardCommentCount.textContent = data.comments.length;
            }
        } else {
            throw new Error(data?.message || 'Failed to add comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add comment: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
    });
    
    return false;
}

/**
 * Function to add a new comment for special day
 */
function addSpecialDayComment(event, specialdayId) {
    event.preventDefault();
    const commentInput = document.getElementById(`comment-input-${specialdayId}`);
    const commentText = commentInput.value.trim();
    
    if (commentText === '') return false;
    
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_specialday_comment&specialday_id=${specialdayId}&comment_text=${encodeURIComponent(commentText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Clear input
            commentInput.value = '';
            
            // Update comment list
            const commentList = document.getElementById(`comment-list-${specialdayId}`);
            commentList.innerHTML = '';
            
            data.comments.forEach(comment => {
                const commentItem = document.createElement('div');
                commentItem.className = 'comment-item';
                commentItem.innerHTML = `
                    <div class="comment-author">${comment.name}</div>
                    <div class="comment-text">${comment.comment_text}</div>
                    <div class="comment-time">
                        ${new Date(comment.created_at).toLocaleString()}
                    </div>
                `;
                commentList.appendChild(commentItem);
            });
            
            // Update comment count
            const commentCount = document.getElementById(`comment-count-${specialdayId}`);
            if (commentCount) {
                commentCount.textContent = data.comments.length;
            }
        }
    })
    .catch(error => console.error('Error:', error));
    
    return false;
}

/**
 * Function to toggle description (read more/less)
 */
function toggleDescription(id) {
    const descriptionElement = document.getElementById(`description-${id}`);
    if (!descriptionElement) return;
    
    const fullDescription = descriptionElement.getAttribute('data-full-description');
    const shortDescription = descriptionElement.getAttribute('data-short-description');

    if (descriptionElement.innerHTML.includes('...')) {
        descriptionElement.innerHTML = fullDescription + ' <span class="read-more" onclick="event.stopPropagation(); toggleDescription(' + id + ')">Read Less</span>';
    } else {
        descriptionElement.innerHTML = shortDescription + ' <span class="read-more" onclick="event.stopPropagation(); toggleDescription(' + id + ')">Read More</span>';
    }
}

/**
 * Function to handle special day liking
 */
function likeSpecialDay(id) {
    fetch('like_specialday.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById(`like-count-${id}`).innerText = data.likes;
            if (modalCurrentSpecialId === id) {
                document.getElementById('modalSpecialLikeCount').innerText = data.likes;
            }
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

/**
 * Function to handle special message sharing
 */
function shareSpecialMessage(postId) {
    const shareUrl = window.location.href.split('?')[0] + '?id=' + postId;
    const shareText = 'Check out this special message!';

    if (navigator.share) {
        navigator.share({
            title: 'Share this special message',
            text: shareText,
            url: shareUrl
        }).then(() => {
            console.log('Thanks for sharing!');
            // Update share count
            updateSpecialShareCount(postId);
        }).catch((error) => {
            console.error('Error sharing:', error);
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        const shareWindow = window.open('', '_blank', 'width=600,height=400');
        shareWindow.document.write(`
            <div>
                <h3>Share this special message</h3>
                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}" target="_blank">Facebook</a><br>
                <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}" target="_blank">Twitter</a><br>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(shareText)}" target="_blank">LinkedIn</a><br>
            </div>
        `);
        
        updateSpecialShareCount(postId);
    }
}

/**
 * Helper function to update special share count
 */
function updateSpecialShareCount(postId) {
    fetch('share_specialday.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById(`share-count-${postId}`).innerText = data.shares;
            if (modalCurrentSpecialId === postId) {
                document.getElementById('modalSpecialShareCount').innerText = data.shares;
            }
        }
    });
}

/**
 * Function to toggle comments section
 */
function toggleComments(specialdayId) {
    const container = document.getElementById(`comments-container-${specialdayId}`);
    if (!container) return;
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

/**
 * Function to handle special day liking
 */
function likeSpecialDay(id) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like_specialday&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById(`like-count-${id}`).innerText = data.likes;
            if (modalCurrentSpecialId === id) {
                document.getElementById('modalSpecialLikeCount').innerText = data.likes;
            }
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

/**
 * Function to handle special message sharing
 */
function shareSpecialMessage(postId) {
    // First update the share count
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=share_specialday&id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById(`share-count-${postId}`).innerText = data.shares;
            if (modalCurrentSpecialId === postId) {
                document.getElementById('modalSpecialShareCount').innerText = data.shares;
            }
            
            // Then proceed with sharing
            const shareUrl = window.location.href.split('?')[0] + '?id=' + postId;
            const shareText = 'Check out this special message!';

            if (navigator.share) {
                navigator.share({
                    title: 'Share this special message',
                    text: shareText,
                    url: shareUrl
                }).catch((error) => {
                    console.error('Error sharing:', error);
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareWindow = window.open('', '_blank', 'width=600,height=400');
                shareWindow.document.write(`
                    <div>
                        <h3>Share this special message</h3>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}" target="_blank">Facebook</a><br>
                        <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}" target="_blank">Twitter</a><br>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(shareText)}" target="_blank">LinkedIn</a><br>
                    </div>
                `);
            }
        }
    })
    .catch(error => console.error("Error:", error));
}
</script>
<style>
.read-more {
    color: #007bff;
    cursor: pointer;
    margin-left: 5px;
    font-weight: bold;
}
.read-more:hover {
    text-decoration: underline;
}
</style>
</div>
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
                    <a href="special_messages.php" class="footer-link">Special Messages</a>
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
</body>
</html>