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

// Handle Add/Edit/Delete Note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $calendarType = $_POST['calendar_type'];
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';

    if ($action === 'add' || $action === 'edit') {
        if (!empty($note)) {
            $stmt = $conn->prepare("INSERT INTO notes (calendar_type, year, month, date, note) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE note = ?");
            $stmt->bind_param("siiiss", $calendarType, $year, $month, $date, $note, $note);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM notes WHERE calendar_type = ? AND year = ? AND month = ? AND date = ?");
        $stmt->bind_param("siii", $calendarType, $year, $month, $date);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect to avoid form resubmission
    header("Location: ".$_SERVER['PHP_SELF']."?calendar=".$calendarType."&year=".$year."&month=".$month);
    exit();
}

// Get selected calendar, year, and month
$selectedCalendar = isset($_GET['calendar']) ? $_GET['calendar'] : 'calendar1';
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date("m");

// Fetch notes for the selected month
$notes = [];
$noteSql = "SELECT date, note FROM notes WHERE calendar_type = ? AND year = ? AND month = ?";
$noteStmt = $conn->prepare($noteSql);
$noteStmt->bind_param("sii", $selectedCalendar, $selectedYear, $selectedMonth);
$noteStmt->execute();
$noteResult = $noteStmt->get_result();
while ($noteRow = $noteResult->fetch_assoc()) {
    $notes[$noteRow['date']] = $noteRow['note'];
}
$noteStmt->close();

// Fetch year title
$yearSql = "SELECT year_title FROM calendars WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear LIMIT 1";
$yearResult = $conn->query($yearSql);
$yearTitle = $yearResult && $yearResult->num_rows > 0 ? $yearResult->fetch_assoc()['year_title'] : "No Topic for This Year";

// Fetch month and week data
$sql = "SELECT * FROM calendars WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $monthTitle = $row['month_title'];
    $week1Title = $row['week1_title'];
    $week1Color = $row['week1_color'];
    $week2Title = $row['week2_title'];
    $week2Color = $row['week2_color'];
    $week1Start = $row['week1_start'] ?? 1;
    $week1End = $row['week1_end'] ?? 14;
    $week2Start = $row['week2_start'] ?? 15;
    $week2End = $row['week2_end'] ?? 31;
} else {
    $monthTitle = "No Topic for This Month";
    $week1Title = "No Topic for Week 1";
    $week1Color = "#FF9999";
    $week2Title = "No Topic for Week 2";
    $week2Color = "#99CCFF";
    $week1Start = 1;
    $week1End = 14;
    $week2Start = 15;
    $week2End = 31;
}

// Fetch holidays
$holidaySql = "SELECT date, holiday_title, holiday_color FROM holidays WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth";
$holidayResult = $conn->query($holidaySql);
$holidays = [];
if ($holidayResult && $holidayResult->num_rows > 0) {
    while ($holidayRow = $holidayResult->fetch_assoc()) {
        $holidays[$holidayRow['date']] = [
            'title' => $holidayRow['holiday_title'],
            'color' => $holidayRow['holiday_color']
        ];
    }
}

// Fetch special days
$specialDaySql = "SELECT date, specialday_title, special_message FROM specialdays WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth";
$specialDayResult = $conn->query($specialDaySql);
$specialDays = [];
if ($specialDayResult && $specialDayResult->num_rows > 0) {
    while ($specialDayRow = $specialDayResult->fetch_assoc()) {
        $specialDays[$specialDayRow['date']] = [
            'title' => $specialDayRow['specialday_title'],
            'message' => $specialDayRow['special_message']
        ];
    }
}

// Calendar setup
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$firstDay = date("w", strtotime("$selectedYear-$selectedMonth-01"));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Health</title>
    <link rel="icon" type="image/png" href="log.png">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">


    <style>
 body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Prevent horizontal scrolling */
            width: 100%;
            max-width: 100%;
        }

        .calendar-container {
            width: 100%;
            overflow-x: auto; /* Allow horizontal scrolling for the table */
        }

        table {
            width: 100%;
            table-layout: fixed; /* Ensures equal width columns */
            border-collapse: collapse;
        }

        img, video, iframe {
            max-width: 100%;
            height: auto;
        }

    
       body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: white;
}


.navbar {
    background-color: white;
    border-bottom: 2px solid #ddd;
    padding: 10px 15px;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-brand {
    font-weight: bold;
    font-size: 24px;
    color: black;
    display: flex;
    align-items: center;
}

.navbar-brand .health {
    color: blue;
}

.navbar-brand img {
    width: 40px;
    height: 40px;
    margin-right: 10px;
}

.navbar-nav .nav-link {
    color: black !important;
    font-size: 16px;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link:focus {
    color: blue !important;
    transform: scale(1.1);
}

.main-content {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 60px); /* Adjust based on navbar height */
    flex-direction: column;
    padding: 20px;
}

.card-container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    max-width: 1500px;
    width: 100%;
    margin-top: -5px;
}

.card {
    overflow: hidden;
    width: 300px;
    background-color: white;
}

.card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-body {
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-title {
    text-align: center;
    color: #007bff;
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.card-text {
    color: #555;
    font-size: 1rem;
    line-height: 1.5;
    text-align: justify;
}

.button-group {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.like-share-btn {
    padding: 10px;
    font-size: 1rem;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
}

.like-share-btn:hover {
    background-color: #0056b3;
}

.like-share-btn i {
    margin-right: 5px;
}
.see-more-btn {
    padding: 8px 16px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.see-more-btn {
    display: inline-block;
    padding: 12px 30px;
    font-size: 1.2rem;
    background-color: #FF7C2A;
    color: white;
    border-radius: 20px;
    text-decoration: none;
    margin-top: 20px;
     border-radius: 40px;
    /* Add these to remove focus outline */
    outline: none;
    border: none;
}

.see-more-btn:hover, 
.see-more-btn:focus { /* Include focus state for consistency */
    background-color: #FF7C2A;
    /* Optional: Add a custom focus style (e.g., a subtle shadow) */
    box-shadow: 0 0 0 2px rgba(255, 124, 42, 0.5);
}

/* Responsive Navbar */
@media (max-width: 768px) {
    .navbar-nav {
        text-align: center;
    }

    .navbar-nav .nav-link {
        display: block;
        width: 100%;
    }

    .main-content {
        padding: 10px;
    }
}

/* Main Container */
.container {
    width: 80%;
    margin: auto;
    padding: 20px;
}

@media (max-width: 768px) {
    .container {
        width: 95%;
    }
}

/* Cards */
/* Centered title */
h2.text-center {
    font-size: 2rem;
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 767px) {
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}



/*calendar*/
.calendar-container {
    width: 100%;
    margin: 20px auto;
    padding: 20px;
    border: 1px solid #ddd;
    background: #FCF7DC ;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    color: #204060;
    text-align: center;
      margin-top: 2px;
}


table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    table-layout: fixed; /* Ensures equal width columns */
}

th, td {
    text-align: center;
    border: 2px solid #b3cce6;
    word-wrap: break-word;
    position: relative;
}

th {
    background: #FCF7DC ;
    text-transform: uppercase;
    padding: 15px 10px;
    font-weight: bold;
}

td {
    height: 120px; /* Larger date boxes */
    padding: 10px;
    vertical-align: center; /* Align content to top */
}

/* Date number styling */
td span {
    position: absolute;
    top: 5px;
    left: 5px;
    font-size: 18px;
    font-weight: bold;
}

/* Content container inside date cells */
td .date-content {
    margin-top: 20px; /* Space for the date number */
    height: calc(100% - 25px);
    overflow-y: auto;
    text-align: left;
    font-weight: 600;
}

/* Highlight today */
.today {
    background-color: rgba(179, 204, 230, 0.3);
}

/* Week topic styling */
.week-topic {
    padding: 5px;
    color: white;
    font-weight: bold;
    border-radius: 20px;
}

/* Holiday styling */
.holiday-title {
    font-weight: bold;
    color: black;
    margin-top: 5px;
    font-size: 14px;
}

/* Daily message styling */
.daily-message {
    margin-top: 5px;
    font-size: 15px;
    color: #555;
}

/* Note preview styling */
.note-preview {
    margin-top: 5px;
    font-style: italic;
    font-size: 11px;
    color: #666;
}

/* Calendar header */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.calendar-title {
    font-size: 1.5rem;
    font-weight: bold;
}

.calendar-controls button {
    padding: 8px 12px;
    margin: 0 5px;
    background: #b3cce6;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

/* Custom styles for Saturday and Sunday dates */
.saturday-date {
    color: #9370DB; /* Light purple for Saturday dates */
}

.sunday-date {
    color: #FF0000; /* Red for Sunday dates */
}

/* Custom styles for Saturday and Sunday headers */
th.saturday-header {
    color: #9370DB;
    font-size: 16px;
}

th.sunday-header {
    color: #FF0000;
    font-size: 16px;
}

/* Popup Styles */
.popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1000;
}

.popup-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    max-height: 80vh;
    overflow-y: auto;
    text-align: left;
}

.popup-title {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.popup-date {
    font-weight: bold;
    color: #204060;
    margin-bottom: 15px;
}

.popup-message, .popup-note {
    margin-bottom: 15px;
}

.popup-note-title, .popup-message-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.close {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    font-size: 20px;
}

/* Responsive styles */
@media screen and (max-width: 992px) {
    td {
        height: 100px;
    }
}

@media screen and (max-width: 768px) {
    .calendar-container {
        padding: 10px;
    }
    
    td {
        height: 80px;
        padding: 5px;
    }
    
    td span {
        font-size: 12px;
    }
    
    .holiday-title, .daily-message, .note-preview {
        font-size: 10px;
    }
}

@media screen and (max-width: 576px) {
    th {
        padding: 8px 5px;
        font-size: 12px;
    }
    
    td {
        height: 70px;
        padding: 3px;
    }
    
    .calendar-title {
        font-size: 1.2rem;
    }
    
    /* Make the table scroll horizontally */
    .calendar-table-container {
        overflow-x: auto;
    }
    
    /* Alternative mobile view - stacked dates */
    .mobile-calendar-view {
        display: none; /* Hidden by default, toggle with JS */
    }
    
    .mobile-date-card {
        border: 1px solid #b3cce6;
        border-radius: 5px;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #fff;
    }
    
    .mobile-date-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .mobile-date-content {
        text-align: left;
    }
}

/* Alternative layout for very small screens */
@media screen and (max-width: 400px) {
     th {
        padding: 8px 5px;
        font-size: 12px;
    }
    
    td {
        height: 70px;
        padding: 3px;
    }
    
    .calendar-title {
        font-size: 1.2rem;
    }
    
    /* Make the table scroll horizontally */
    .calendar-table-container {
        overflow-x: auto;
    }
    
    /* Alternative mobile view - stacked dates */
    .mobile-calendar-view {
        display: none; /* Hidden by default, toggle with JS */
    }
    
    .mobile-date-card {
        border: 1px solid #b3cce6;
        border-radius: 5px;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #fff;
    }
    
    .mobile-date-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .mobile-date-content {
        text-align: left;
    }
}
/* Mobile View - Larger Date Boxes */
@media (max-width: 768px) {
    td {
        height: 100px; /* Bigger boxes for mobile */
        padding: 3px;
    }
    
    /* Smaller text in mobile view */
    .date-content {
        font-size: 0.8em;
    }
    
    td span {
        font-size: 14px; /* Date number size */
    }
    
    .content-preview {
        font-size: 10px !important; /* Force small text */
        line-height: 1.2;
        margin: 1px 0;
    }
    
    /* Header adjustments */
    th {
        padding: 8px 2px;
        font-size: 11px;
    }
}

/* Extra Small Mobile Devices */
@media (max-width: 480px) {
    td {
        height: 90px; /* Slightly smaller for very small devices */
    }
    
    /* Even more compact text */
    .content-preview {
        font-size: 9px !important;
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Limit to 2 lines */
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    /* Hide some elements if needed */
    .holiday-title, .special-day-title {
        font-size: 8px;
    }
}
.logi-btn {
    display: inline-block;
    padding: 10px 20px;
    margin-left: 5px;
    margin-top: 0px;
    font-size: 16px;
    font-weight: bold;
    color: black;
    background-color: #FF7C2A; /* solid orange */
    border: none;
    border-radius: 30px;
    text-transform: uppercase;
    text-decoration: none;
    transition: 0.3s ease-in-out;
}


.logi-btn:hover {
    transform: scale(1.05);
}

.p{
    
}
.winner-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* 2 items per row (2x2 grid) */
    justify-content: center;
    max-width: 1200px; /* Adjust width as needed */
    margin: auto;
}

.winner-item {
    background-color: white; /* Background for each item */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Light shadow */
    padding: 15px;
    border: 1px solid black;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%; /* Ensure cards are equal height */
    justify-content: space-between; /* Space out content */
}

.winner-item img {
    width: 100%; /* Make image responsive */
    height: 200px; /* Fixed height for the image */
    object-fit: cover; /* Ensure the image covers the space */
    border-radius: 8px;
}

.winner-item .winner-info {
    padding-top: 10px;
    flex-grow: 1; /* This ensures the text section takes the remaining space */
}

.winner-item .winner-name {
    font-size: 1.1rem;
    font-weight: bold;
}

.winner-item .winner-role {
    font-size: 0.9rem;
    color: #555;
}
.winner-name {
    color:#0083ff  ;
    align-items: center;
    text-align: center;
}
/*btn in winner card*/
.winner-item.last-item {
    position: relative;
}

.see-more-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10;
}

/* Adjust for smaller devices but still keep 2x2 grid */
@media (max-width: 1024px) {
    .winner-grid {
        grid-template-columns: repeat(2, 1fr); /* Keep 2 items per row on tablets */
    }
}

@media (max-width: 480px) {
    .winner-grid {
        grid-template-columns: repeat(2, 1fr); /* Keep 2 items per row even on mobile */
    }

    .winner-item {
        padding: 10px; /* Reduce padding on mobile */
    }
}


/* Video Card Styles */
/* Container for the video cards */
.video-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    margin-top: -20px;
}

/* Video Card Styles */
.video-card {
    width: 100%;
    max-width: 350px; /* Max width to maintain card size */
    background-color: White;
    overflow: hidden;
    padding: 10px;
}

.video-card:hover {

}

/* Video Thumbnail Styling */
.video-thumbnail {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
}

.video-thumbnail video {
    width: 100%;
    height: auto;
    border-radius: 10px;
}

/* Video Title Styling */
.video-title {
    text-align: center;
    font-size: 1.4rem;
    font-weight: 600;
    margin: 10px 0;
     color:#0083ff  ;
}

/* Video Description Styling */
.video-description {
    text-align: left;
    font-size: 0.9rem;
    color: #555;
    font-style: italic;
    font-weight: bold;
    margin-bottom: 15px;
}

/* Upload Date Styling */
.upload-date {
    text-align: center;
    color: #888;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .video-card {
        max-width: 100%;
        margin: 10px 0;
    }

    .video-title {
        font-size: 1.2rem;
        
    }

    .video-description {
        font-size: 0.8rem;
    }

    .upload-date {
        font-size: 0.75rem;
    }
}

@media (min-width: 769px) {
    .video-card.large {
        max-width: 500px; /* Larger size for the first video */
    }

    .video-card.medium {
        max-width: 150px;
    }
}

/* Container for the event cards */
/* Event Container Styles */
.event-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

/* Big Event Image */
.big-event-image-container {
    width: 100%;
    height: 400px;
    overflow: hidden;
    border-radius: 10px;
}

.big-event-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensure image covers the container without distorting */
    border-radius: 10px;
}

/* Event Card Styles (For Small Thumbnails) */
.event-card {
    width: 100%;
    max-width: 300px; /* Smaller width for event cards */
    border-radius: 10px;
    background-color: white;
    overflow: hidden;
    padding: 10px;
    cursor: pointer;
}



/* Event Thumbnail Styling */
.event-thumbnail img {
    width: 100%;
    height: auto;
}

/* Event Title Styling */
.event-title {
    text-align: center;
    font-size: 1.4rem;
    font-weight: 600;
    margin: 10px 0;
     color:#0083ff  ;
}

/* Event Description Styling */
.event-description {
    text-align: center;
    font-size: 0.9rem;
    color: #555;
    font-style: italic;
    font-weight: bold;
    margin-bottom: 15px;
}

/* Event Role Styling */
.event-role {
    text-align: center;
    font-size: 0.9rem;
    font-weight: bold;
    color: #007bff; /* Blue color for role */
    margin-bottom: 5px;
}

/* Created At Styling */
.event-date {
    text-align: center;
    color: #888;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

/* On smaller screens, make the event thumbnails stack vertically */
@media (max-width: 767px) {
    .event-container {
        flex-direction: column;
        align-items: center;
    }

    .event-card {
        width: 90%; /* Full width for smaller screens */
        max-width: none; /* Remove the max-width restriction */
        margin-bottom: 20px;
    }

    /* For the big image on small screens */
    .big-event-image-container {
        height: 250px; /* Adjust the height for mobile */
    }
}

/* On larger screens, display thumbnails in a row */
@media (min-width: 768px) {
    .col-md-3, .col-lg-3 {
        width: 30%; /* Ensure the small boxes are 30% of the screen width */
    }

    .big-event-image-container {
        height: 400px; /* Height of the big image on larger screens */
    }
}

/* Neon Divider */
.neon-divider {
    width: 100%;
    height: 10px; /* Increase thickness of the line */
    background: white; /* White color for the divider */
    margin: 20px 0;
    position: relative;
    box-shadow: 0 0 10px 2px rgba(255, 255, 255, 0.3), 0 0 20px 4px rgba(255, 255, 255, 0.3), 0 0 30px 6px rgba(255, 255, 255, 0.2);
    animation: neon-flicker 1.5s infinite alternate;
}

/* Neon Flicker Effect */
@keyframes neon-flicker {
    0% {
        box-shadow: 0 0 10px 2px rgba(255, 255, 255, 0.3), 0 0 20px 4px rgba(255, 255, 255, 0.3), 0 0 30px 6px rgba(255, 255, 255, 0.2);
    }
    50% {
        box-shadow: 0 0 15px 3px rgba(255, 255, 255, 0.5), 0 0 30px 5px rgba(255, 255, 255, 0.4), 0 0 40px 8px rgba(255, 255, 255, 0.3);
    }
    100% {
        box-shadow: 0 0 10px 2px rgba(255, 255, 255, 0.3), 0 0 20px 4px rgba(255, 255, 255, 0.3), 0 0 30px 6px rgba(255, 255, 255, 0.2);
    }
}



.health {
    font-family: 'Pacifico', cursive; /* Example cursive font */
    font-style: italic; /* Optional for extra style */
}
.today {
    font-weight: bold;
    font-size: 1.4em; /* Slightly larger text */
}
.links-container {
    display: flex;
    justify-content: space-between;
    width: 100%;
    border-radius: 20px;
    padding: 10px 30px; /* Adjust spacing from edges */
}

.link {
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
    color: #007bff;
}
.message-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    background-color: red; /* Choose any color you like */
    border-radius: 50%;
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}
 @media (max-width: 768px) {
        #Daily_Messages {
            font-size: 1.8rem; /* Adjust font size for smaller screens */
            padding: 5px 10px; /* Reduce padding */
        }
    }
      .read-more {
        color: blue;
        cursor: pointer;
        text-decoration: underline;
    }

    .read-more:hover {
        color: darkblue;
    }
    /* Base Navbar Styles */
    /* Base Navbar Styles */
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
    </style>
</head>

</head>
<body>
       <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="Admin/img/log.png" alt="Logo" style="width:100%;"> <!-- Add your logo image here -->
            
        </a>
<div class="d-flex d-lg-none" style="gap: 10px;">

    <a class="nav-link" href="../shop/products.php">
        <p style="
            display: inline-block;
            padding: 6px;
            border-radius: 20px;
            margin: -10px;
            color: #0E47B4 !important; <!-- Force black -->
            font-weight: bold; 


        ">Shop</p> <!-- FontAwesome shop icon -->
    </a>
    
        <a class="nav-link" href="user_details.php">
     <i style="
  
            display: inline-block;
            font-weight: bold; 

            margin: -10px;
            color:  #0E47B4 !important; <!-- Force black -->

        "  class="fas fa-user"></i><!-- FontAwesome user icon -->
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
                    <a class="nav-link" href="Admin/vid_display.php">Health Talk</a>
                </li>
                <li class="nav-item">
    
                    <a class="nav-link" href="Admin/download_list.php">Downloads</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Admin/event_display.php">Events</a>
                </li>
                <li class="nav-item d-none d-lg-block">
                    <a class="nav-link" href="../shop/products.php">
                         Shop
                    </a>
                </li>
                 <li class="nav-item">
    <a class="nav-link" href="user_details.php">
        <i class="fas fa-user"></i>Profile <!-- FontAwesome user icon -->
    </a>
                </li
            </ul>
        </div>
    </div>
</nav>





  <!-- Main Content Section -->
 
  <!-- Main Content Section -->
  <div class="links-container" style="margin-top:8px;">
            <button style="
        
            display: inline-block;
            padding: 6px;
            border-radius: 20px;
            margin: -10px;
            color: white !important;" class="logi-btn" onclick="openPopup('join-popup')">How to Join</button>
            
            <button style="

            display: inline-block;
            padding: 6px;
            border-radius: 20px;
            margin: -10px;
            color: white !important;" class="logi-btn" onclick="openPopup('works-popup')">How it Works</button>
        </div>
    </div>
    
    
       <!-- How to Join Popup -->
    <div id="join-popup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); z-index: 100; max-width: 90%; width: 450px;">
        <div>
            <h2 style="color: #2c3e50; margin-bottom: 20px; text-align: center; font-size: 24px;">How to Join</h2>
            <div style="background-color: #f1f9f1; border-left: 4px solid #25D366; padding: 15px; margin: 15px 0; border-radius: 4px;"><p style="color: #606060; line-height: 1.8; margin-bottom: 10px; font-size: 16px; text-align: left;"><strong>Send a WhatsApp message to:</strong> 0777867942</p><p style="color: #606060; line-height: 1.8; margin-bottom: 10px; font-size: 16px; text-align: left;">Include the calendar code in your message. Example: <strong>"HLS0125"</strong></p><center><a href="https://wa.me/94777867942" style="display: inline-block; padding: 10px 20px; background-color: #25D366; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;"><img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="width: 20px; vertical-align: middle; margin-right: 8px;"> Send a Message</a></center></div>
            <p style="color: #606060; line-height: 1.8; margin-bottom: 10px; font-size: 16px; text-align: left;">Send WhatsApp message to 0777867942 by mentioning the code no. of the calendar. Eg. "HLS0125"

Then further instructions will be sent.</p>
        </div>
        <button style="padding: 10px 20px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: none; border-radius: 50px; cursor: pointer; font-weight: 600; transition: all 0.3s ease; display: block; margin: 0 auto; box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3);" onclick="closePopup('join-popup')">Close</button>
    </div>
    
    <!-- How it Works Popup -->
    <div id="works-popup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); z-index: 100; max-width: 90%; width: 450px;">
        <div>
    <ol style="padding-left: 0; list-style-type: none;">
        <li style="margin-bottom: 10px;"><span style="display: inline-block; width: 28px; height: 28px; background-color: #3498db; color: white; border-radius: 50%; text-align: center; line-height: 28px; margin-right: 10px; font-weight: bold;">1</span> You have to start following biweekly messages within that weeks and continuously.</li>
        <li style="margin-bottom: 10px;"><span style="display: inline-block; width: 28px; height: 28px; background-color: #3498db; color: white; border-radius: 50%; text-align: center; line-height: 28px; margin-right: 10px; font-weight: bold;">2</span> We are sending motivational and activity requesting messages related to the topic of that two weeks.</li>
        <li style="margin-bottom: 10px;"><span style="display: inline-block; width: 28px; height: 28px; background-color: #3498db; color: white; border-radius: 50%; text-align: center; line-height: 28px; margin-right: 10px; font-weight: bold;">3</span> Out of the successful respondants to the given activity, we will select few winners and award an encouraging gift.</li>

    </ol>
</div>
        <button style="padding: 10px 20px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: none; border-radius: 50px; cursor: pointer; font-weight: 600; transition: all 0.3s ease; display: block; margin: 0 auto; box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3);" onclick="closePopup('works-popup')">Close</button>
    </div>
    
    <div id="overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); z-index: 50; backdrop-filter: blur(3px);"></div>
    
    <script>
        function openPopup(popupId) {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById(popupId).style.display = 'block';
            
            // Add animation class
            setTimeout(() => {
                document.getElementById(popupId).style.opacity = '1';
                document.getElementById(popupId).style.transform = 'translate(-50%, -50%) scale(1)';
            }, 50);
        }
        
        function closePopup(popupId) {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById(popupId).style.display = 'none';
        }
        
        // Close popup when clicking on overlay
        document.getElementById('overlay').addEventListener('click', function() {
            document.querySelectorAll('[id$="-popup"]').forEach(function(popup) {
                popup.style.display = 'none';
            });
            this.style.display = 'none';
        });
    </script>


<style>
    .large-select {
        font-size: 20px;
    }
    .large-select option {
        font-size: 16px;
    }
</style>

<form method="GET" class="mb-3" style="margin-top:5px;">
    <!-- Calendar Dropdown (Full Width) -->
    <div class="row mb-3" style="margin-left: 20px; margin-right: 20px; margin-bottom:10px;">
        <div class="col-12">
            <select name="calendar" id="calendar" class="form-control large-select" onchange="this.form.submit()" 
    style="margin-top:4px; font-weight: 600; border: 2px solid black; border-radius:10px; text-align-last: center; text-align: center;">
    <?php
    // Generate options for calendar_type from calendar1 to calendar65
    for ($i = 1; $i <= 65; $i++) {
        $calendarValue = 'calendar' . $i;
        $selected = ($selectedCalendar === $calendarValue) ? 'selected' : '';
        echo "<option value='$calendarValue' $selected>Select Calendar $i</option>";
    }
    ?>
</select>
        </div>
    </div>
</form>

<div class="calendar-container">
    <!-- Dropdown to select calendar -->
<h2 style="color: #0E47B4;"><?= htmlspecialchars($yearTitle) ?></h2>
<h3 style="color: #0E47B4;"><?= htmlspecialchars($monthTitle) ?></h3>



    <div class="calendar-wrapper">
        
        <table class="fixed-calendar">
                <form method="GET" class="mb-3">
        <div class="row g-0">
            <div class="col-6">
                <select name="year" id="year" class="form-control" onchange="this.form.submit()" style="font-size: 18px; font-weight: 600; border: 2px solid #0E47B4; border-radius:10px; gap:5px;">
                    <?php for ($y = date("Y") - 10; $y <= date("Y") + 10; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-6">
                <select name="month" id="month" class="form-control" onchange="this.form.submit()" style="font-size: 18px; font-weight: 600; border: 2px solid #0E47B4; border-radius:10px; gap:5px;">
                    <?php
                    $months = [
                        1 => "January", 2 => "February", 3 => "March", 4 => "April",
                        5 => "May", 6 => "June", 7 => "July", 8 => "August",
                        9 => "September", 10 => "October", 11 => "November", 12 => "December"
                    ];
                    foreach ($months as $num => $name):
                    ?>
                        <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
            <thead>
                <tr>
                    <th style="font-size: 16px; font-weight: 600;">Mon</th>
                    <th style="font-size: 16px; font-weight: 600;">Tue</th>
                    <th style="font-size: 16px; font-weight: 600;">Wed</th>
                    <th style="font-size: 16px; font-weight: 600;">Thu</th>
                    <th style="font-size: 16px; font-weight: 600;">Fri</th>
                    <th class="saturday-header" style="font-weight: 600;">Sat</th>
                    <th class="sunday-header" style="font-weight: 600;">Sun</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $date = 1;
                $rowsNeeded = ceil(($daysInMonth + (($firstDay - 1 + 7) % 7)) / 7);
                for ($i = 0; $i < $rowsNeeded; $i++) {
                    echo "<tr>";
                    for ($j = 0; $j < 7; $j++) {
                        if ($i === 0 && $j < ($firstDay - 1 + 7) % 7) {
                            echo "<td class='empty-date'></td>";
                        } elseif ($date > $daysInMonth) {
                            echo "<td class='empty-date'></td>";
                        } else {
                            $weekStyle = "";
                            $holidayStyle = "";
                            $holidayTitle = "";
                            $specialDayTitle = "";
                            $specialDayMessage = "";

                            if ($date >= $week1Start && $date <= $week1End) {
                                $weekStyle = "background-color: $week1Color;";
                            } elseif ($date >= $week2Start && $date <= $week2End) {
                                $weekStyle = "background-color: $week2Color;";
                            }

                            if (isset($holidays[$date])) {
                                $holidayStyle = "background-color: {$holidays[$date]['color']};";
                                $holidayTitle = $holidays[$date]['title'];
                            }

                            if (isset($specialDays[$date])) {
                                $specialDayTitle = $specialDays[$date]['title'];
                                $specialDayMessage = $specialDays[$date]['message'];
                            }

                            $combinedStyle = $weekStyle . $holidayStyle;

                            $dailySql = "SELECT title, description FROM daily_messages WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth AND date = $date LIMIT 1";
                            $dailyResult = $conn->query($dailySql);
                            $dailyData = $dailyResult->fetch_assoc();

                            $noteSql = "SELECT note FROM notes WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth AND date = $date LIMIT 1";
                            $noteResult = $conn->query($noteSql);
                            $noteData = $noteResult->fetch_assoc();
                            $note = $noteData ? $noteData['note'] : "";

                            // Process all content to show only first two words
                            $holidayPreview = $holidayTitle ? implode(" ", array_slice(explode(" ", $holidayTitle), 0, 2)) : "";
                            $specialDayPreview = $specialDayTitle ? implode(" ", array_slice(explode(" ", $specialDayTitle), 0, 2)) : "";
                            $dailyMessagePreview = $dailyData ? implode(" ", array_slice(explode(" ", $dailyData['title']), 0, 2)) : "";
                            $notePreview = $note ? implode(" ", array_slice(explode(" ", $note), 0, 2)) : "";

                            $dayOfWeek = date("N", strtotime("$selectedYear-$selectedMonth-$date"));
                            $dateClass = "";
                            if ($dayOfWeek == 6) {
                                $dateClass = "saturday-date";
                            } elseif ($dayOfWeek == 7) {
                                $dateClass = "sunday-date";
                            }

                            $onclick = "onclick=\"openModal('$selectedYear-$selectedMonth-$date', '" . 
                                      ($dailyData ? addslashes($dailyData['title']) : 'No daily message for this date.') . 
                                      "', '" . addslashes($note) . 
                                      "', '" . addslashes($holidayTitle) . 
                                      "', '" . addslashes($specialDayTitle) . 
                                      "', '" . addslashes($specialDayMessage) . "')\"";

                            echo "<td style='$combinedStyle' $onclick>
                                    <div class='date-content'>
                                        <span class='$dateClass'>$date</span>
                                        <div class='content-preview'>$holidayPreview</div>
                                        <div class='content-preview'>$specialDayPreview</div>
                                        <div class='content-preview'>$dailyMessagePreview</div>
                                        <div class='content-preview'>$notePreview</div>
                                    </div>
                                  </td>";
                            $date++;
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
   <div class="row week-topics-row g-2"> <!-- Added g-2 for gutter spacing -->
    <div class="col-md-6 col-6 week-topic-col"> <!-- Changed col-12 to col-6 for side-by-side on mobile -->
        <div class="week-topic-wrapper" style="background-color: <?= htmlspecialchars($week1Color) ?>;">
            <div class="week-topic-content">
                <span class="topic-preview"><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $week1Title), 0, 5))) ?></span>
                <?php if (str_word_count($week1Title) > 5): ?>
                    <span class="read-more" data-fulltext="<?= htmlspecialchars($week1Title) ?>">... Read more</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-6 week-topic-col"> <!-- Changed col-12 to col-6 for side-by-side on mobile -->
        <div class="week-topic-wrapper" style="background-color: <?= htmlspecialchars($week2Color) ?>;">
            <div class="week-topic-content">
                <span class="topic-preview"><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $week2Title), 0, 5))) ?></span>
                <?php if (str_word_count($week2Title) > 5): ?>
                    <span class="read-more" data-fulltext="<?= htmlspecialchars($week2Title) ?>">... Read more</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .week-topics-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    
    .week-topic-col {
        padding: 5px !important;
        display: flex;
    }
    
    .week-topic-wrapper {
        flex: 1;
        padding: 10px;
        color: black;
        font-weight: bold;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 60px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        word-break: break-word; /* Added for better text handling */
    }
    
    .week-topic-content {
        width: 100%;
        position: relative;
    }
    
    .topic-preview {
        display: inline;
    }
    
    .read-more {
        font-weight: normal;
        font-size: 0.85em;
        text-decoration: underline;
        white-space: nowrap;
        cursor: pointer;
        color: darkblue;
    }
    
    .close-fulltext {
        float: right;
        cursor: pointer;
        font-size: 1.2em;
        margin-left: 8px;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .week-topic-col {
            flex: 0 0 50%; /* Changed to 50% for side-by-side */
            max-width: 50%;
            margin-bottom: 0; /* Removed bottom margin */
        }
        
        .week-topic-wrapper {
            min-height: 55px;
            padding: 8px 10px;
            font-size: 0.9em; /* Slightly smaller text on mobile */
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store original content to restore later
    const originalContents = {};
    
    document.querySelectorAll('.week-topic-col').forEach((col, index) => {
        const wrapper = col.querySelector('.week-topic-wrapper');
        originalContents[`box${index}`] = wrapper.innerHTML;
    });

    document.querySelectorAll('.read-more').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const wrapper = this.closest('.week-topic-wrapper');
            const fullText = this.getAttribute('data-fulltext');
            const parentCol = wrapper.closest('.week-topic-col');
            const colIndex = Array.from(document.querySelectorAll('.week-topic-col')).indexOf(parentCol);
            
            // Store original content if not already stored
            if (!originalContents[`box${colIndex}`]) {
                originalContents[`box${colIndex}`] = wrapper.innerHTML;
            }
            
            wrapper.innerHTML = `
                <div class="week-topic-content">
                    ${fullText}
                    <span class="close-fulltext">×</span>
                </div>`;
            
            // Add close event
            wrapper.querySelector('.close-fulltext').addEventListener('click', function(ev) {
                ev.stopPropagation();
                wrapper.innerHTML = originalContents[`box${colIndex}`];
                // Reattach event listeners after restoring
                attachReadMoreEvents();
            });
        });
    });
    
    function attachReadMoreEvents() {
        document.querySelectorAll('.read-more').forEach(element => {
            element.addEventListener('click', function(e) {
                e.stopPropagation();
                const wrapper = this.closest('.week-topic-wrapper');
                const fullText = this.getAttribute('data-fulltext');
                const parentCol = wrapper.closest('.week-topic-col');
                const colIndex = Array.from(document.querySelectorAll('.week-topic-col')).indexOf(parentCol);
                
                wrapper.innerHTML = `
                    <div class="week-topic-content">
                        ${fullText}
                        <span class="close-fulltext">×</span>
                    </div>`;
                
                // Add close event
                wrapper.querySelector('.close-fulltext').addEventListener('click', function(ev) {
                    ev.stopPropagation();
                    wrapper.innerHTML = originalContents[`box${colIndex}`];
                    attachReadMoreEvents();
                });
            });
        });
    }
});
</script>
    
    <div class="row">
        <div class="col-12" style="margin-top:-5px;">
            <a href="Admin/weekly_questions_display.php?calendar=<?php echo urlencode($selectedCalendar ?? 'calendar1'); ?>" 
               class="btn btn-primary d-block" 
                              style="font-size: 0.9rem; font-weight: bold; color: black; padding: 10px 12px; line-height: 1.2; background-color: white; font-size: 16px; border: none;     border-radius: 20px; /* Makes it a perfect circle */
">
                View & Answer Weekly Question
            </a>
        </div>
    </div>
</div>
    <style>
/* Mobile responsiveness with bigger squares */
@media (max-width: 768px) {
    .calendar-table {
        min-width: 100%; /* Full width on mobile */
    }
    
    .calendar-table th, 
    .calendar-table td {
        width: 14.28%; /* Equal width for each day */
        min-width: 50px; /* Minimum width */
        height: 100px; /* Tall mobile cells - increased height */
        padding: 2px; /* Reduce padding to maximize space */
    }
    
    .date-number {
        font-size: 1.2em; /* Smaller date numbers */
        font-weight: bold;
        display: block;
        margin-bottom: 3px;
    }
    
    .date-content {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .holiday-title, 
    .special-day, 
    .daily-message, 
    .note-preview {
        font-size: 0.8em; /* Smaller text for other elements */
        -webkit-line-clamp: 2;
        line-height: 1.1;
        margin: 1px 0;
        flex-grow: 1;
        overflow: hidden;
    }
}

/* Phones in landscape */
@media (max-width: 768px) and (orientation: landscape) {
    .calendar-table th, 
    .calendar-table td {
        height: 95px; /* Slightly shorter in landscape */
    }
}

/* Smaller phones */
@media (max-width: 576px) {
    .calendar-table th, 
    .calendar-table td {
        min-width: 45px;
        height: 95px; /* Maintain tall cells */
    }
    
    .date-number {
        font-size: 0.6em; /* Keep date numbers small */
    }
    
    .holiday-title, 
    .special-day, 
    .daily-message, 
    .note-preview {
        font-size: 0.75em; /* Smaller text */
    }
}

/* Very small phones */
@media (max-width: 400px) {
    .calendar-table th, 
    .calendar-table td {
        min-width: 40px;
        height: 90px;
    }
    
    .date-number {
        font-size: 1em; /* Smallest date numbers */
    }
}
/* Very small phones */
@media (max-width: 400px) {
    .calendar-table th, 
    .calendar-table td {
        min-width: 40px;
        height: 80px;
    }
    
    .date-number {
        font-size: 1.3em;
    }
}
   
   
        .holiday-title {
            font-size: 0.8em;
            color: #333;
            margin-top: 3px;
        }
        
        .daily-message {
            font-size: 0.9em;
            color: #555;
            margin-top: 5px;
        }
        
        .note-preview {
            font-size: 0.7em;
            color: #888;
            margin-top: 5px;
            font-style: italic;
        }
        
        .week-topic {
            padding: 10px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #b3cce6, #E9EDF4);
            border-bottom: 2px solid #b3cce6;
            color: #204060;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .modal-body {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 10px 10px;
        }
        
        .holiday-display {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }
        
        .holiday-display h6 {
            color: #856404;
            margin-bottom: 5px;
        }
        
        .holiday-display p {
            color: #856404;
            margin-bottom: 0;
            font-weight: bold;
        }
        
         .special-message-display {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }
        
        .special-message-display h6 {
            color: #856404;
            margin-bottom: 5px;
        }
        
        .special-message-display p {
            color: #856404;
            margin-bottom: 0;
            font-weight: bold;
        }
        
        #dailyMessageButton {
            background-color: #007bff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        #dailyMessageButton:hover {
            background-color: #0056b3;
        }
        
        #noteForm textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
            margin-bottom: 10px;
        }
        
        .btn {
            margin-right: 10px;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
    </style>
<!-- Modal -->
<div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateModalLabel">Day Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="holidayDisplay" class="holiday-display" style="display: none;">
                    <center>
                        <h6>Holiday</h6>
                        <p id="holidayTitle"></p>
                    </center>
                </div>
                <div id="specialMessageDisplay" class="special-message-display" style="display: none;">
                    <center>
                        <h6>Special Message</h6>
                        <p id="specialMessageTitle"></p>
                        <button type="button" class="btn btn-info btn-sm" id="viewSpecialMessageButton">View Special Message</button>
                    </center>
                </div>
                <center>
                    <h7>Day's Thought</h7>
                    <p style="font-size: 1.25rem;" id="dailyMessage"></p>
                    <button type="button" class="btn btn-primary" id="dailyMessageButton">View Full Message</button>
                    <p></p>
                </center>
                <center>
                    <h6>Day Note</h6>
                    <div id="noteMessage" style="white-space: pre-line;"></div>
                </center>
                <form id="noteForm" method="POST">
                    <input type="hidden" name="calendar_type" id="calendarType">
                    <input type="hidden" name="year" id="modalYear">
                    <input type="hidden" name="month" id="modalMonth">
                    <input type="hidden" name="date" id="modalDate">
                    <input type="hidden" name="action" id="action" value="add">
                    
                    <textarea name="note" id="noteInput" class="form-control mb-2" placeholder="Add a note..." rows="4"></textarea>
                    <button type="submit" class="btn btn-success" id="saveNoteButton">Save Note</button>
                    <button type="button" class="btn btn-warning" id="editNoteButton" style="display: none;">Edit Note</button>
                    <button type="button" class="btn btn-danger" id="deleteNoteButton" style="display: none;">Delete Note</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
    function openModal(date, dailyMessage, noteMessage, holidayTitle, specialMessageTitle) {
        // Set the date in modal title
        const [year, month, day] = date.split('-');
        const dateObj = new Date(year, month-1, day);
        document.getElementById('dateModalLabel').textContent = 
            dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        // Display holiday if exists
        const holidayDisplay = document.getElementById('holidayDisplay');
        if (holidayTitle && holidayTitle.trim() !== '') {
            document.getElementById('holidayTitle').textContent = holidayTitle;
            holidayDisplay.style.display = 'block';
        } else {
            holidayDisplay.style.display = 'none';
        }
        
        // Display special message if exists
        const specialMessageDisplay = document.getElementById('specialMessageDisplay');
        const viewSpecialMessageButton = document.getElementById('viewSpecialMessageButton');
        if (specialMessageTitle && specialMessageTitle.trim() !== '') {
            document.getElementById('specialMessageTitle').textContent = specialMessageTitle;
            specialMessageDisplay.style.display = 'block';
            viewSpecialMessageButton.style.display = 'inline-block';
        } else {
            specialMessageDisplay.style.display = 'none';
            viewSpecialMessageButton.style.display = 'none';
        }
        
        // Set other fields
        document.getElementById('dailyMessage').textContent = dailyMessage || "No daily message for this date.";
        
        // Handle note message with line breaks
        const noteMessageElement = document.getElementById('noteMessage');
        if (noteMessage && noteMessage.trim() !== '') {
            noteMessageElement.textContent = noteMessage;
        } else {
            noteMessageElement.textContent = "No note for this date.";
        }
        
        document.getElementById('noteInput').value = noteMessage || "";
        
        // Set hidden inputs
        document.getElementById('calendarType').value = "<?= $selectedCalendar ?>";
        document.getElementById('modalYear').value = year;
        document.getElementById('modalMonth').value = month;
        document.getElementById('modalDate').value = day;
        
        // Toggle buttons based on note existence
        if (noteMessage && noteMessage.trim() !== '' && noteMessage !== "No note for this date.") {
            document.getElementById('editNoteButton').style.display = 'inline-block';
            document.getElementById('deleteNoteButton').style.display = 'inline-block';
            document.getElementById('saveNoteButton').style.display = 'none';
            document.getElementById('noteInput').style.display = 'none';
        } else {
            document.getElementById('editNoteButton').style.display = 'none';
            document.getElementById('deleteNoteButton').style.display = 'none';
            document.getElementById('saveNoteButton').style.display = 'inline-block';
            document.getElementById('noteInput').style.display = 'block';
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('dateModal'));
        modal.show();
    }

    document.getElementById('noteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const noteInput = document.getElementById('noteInput');
        noteInput.value = noteInput.value.trim();
        
        if (noteInput.value === '') {
            alert('Note cannot be empty');
            return;
        }
        
        const action = document.getElementById('saveNoteButton').style.display === 'none' ? 'edit' : 'add';
        document.getElementById('action').value = action;
        this.submit();
    });

    document.getElementById('deleteNoteButton').addEventListener('click', function() {
        if (confirm("Are you sure you want to delete this note?")) {
            document.getElementById('action').value = 'delete';
            document.getElementById('noteForm').submit();
        }
    });

    document.getElementById('editNoteButton').addEventListener('click', function() {
        document.getElementById('saveNoteButton').style.display = 'inline-block';
        document.getElementById('editNoteButton').style.display = 'none';
        document.getElementById('deleteNoteButton').style.display = 'none';
        document.getElementById('noteInput').style.display = 'block';
    });

    document.getElementById('dailyMessageButton').addEventListener('click', function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('dateModal'));
        modal.hide();
        const dailyMessageSection = document.getElementById('Daily_Messages');
        if (dailyMessageSection) {
            dailyMessageSection.scrollIntoView({ behavior: 'smooth' });
        }
    });

    document.getElementById('viewSpecialMessageButton').addEventListener('click', function() {
        window.location.href = './Admin/special_message.php';
    });
</script>
</div>

<?php
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

// Get selected calendar from dropdown
$selectedCalendar = isset($_GET['calendar']) ? $_GET['calendar'] : 'calendar1';

// Fetch the latest message for the selected calendar
$sql = "SELECT * FROM daily_messages WHERE calendar_type = '$selectedCalendar' ORDER BY year DESC, month DESC, date DESC LIMIT 1";
$result = $conn->query($sql);
?>

    <style>
/* Desktop Styles (default) */
        .container { 
            width: 80%; 
            margin: auto; 
            padding: 20px; 
            background: white; 
            box-shadow: 0px 0px 10px gray; 
            border-radius: 8px; 
        }
        
        h2 { 
            text-align: center; 
        }
        
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }
        
        .card { 
            width: 300px; 
            background: white; 
            box-shadow: 0px 0px 8px gray; 
            padding: 10px; 
            border-radius: 8px; 
            text-align: center; 
            margin-bottom: 20px;
        }
        
        .card img { 
            width: 100%; 
            height: 200px; 
            object-fit: cover; 
            border-radius: 4px; 
        }
        
        .card h3 { 
            margin-top: 10px; 
            font-size: 18px; 
        }
        
        .card p { 
            font-size: 14px; 
            color: #555; 
        }
        
        .dropdown { 
            margin-bottom: 20px; 
        }
        
        .dropdown select { 
            padding: 8px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            width: 200px;
        }
        
        .read-more { 
            color: blue; 
            cursor: pointer; 
            display: inline-block;
            margin-top: 10px;
            text-decoration: underline;
        }
          /* Mobile Styles */
        @media only screen and (max-width: 768px) {
            .container {
                width: 100%;
                margin: 0;
                padding: 15px;
                border-radius: 0;
                box-sizing: border-box;
            }
            
            .card {
                width: 100%;
                box-sizing: border-box;
            }
            
            .dropdown select {
                width: 100%;
                padding: 12px;
                font-size: 16px;
            }
            
            .cards-container {
                flex-direction: column;
                gap: 15px;
            }
        }
        /* Like Count Styling */
.like-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    padding: 6px 12px;
    background-color: #f8f9fa;
    border-radius: 20px;
    font-size: 14px;
    color: #555;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.like-icon {
    color: #e74c3c; /* Heart color */
    font-size: 16px;
    transition: transform 0.3s ease;
}

.like-text {
    font-weight: 500;
    color: #555;
}

.like-number {
    font-weight: bold;
    color: #e74c3c;
    min-width: 20px;
    text-align: center;
}

/* Hover Effects */
.like-count:hover {
    background-color: #f0f0f0;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.like-count:hover .like-icon {
    transform: scale(1.2);
}

/* Animation when liked */
@keyframes heartBeat {
    0% { transform: scale(1); }
    25% { transform: scale(1.3); }
    50% { transform: scale(1); }
    75% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.liked .like-icon {
    animation: heartBeat 0.5s;
    color: #e74c3c;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .like-count {
        padding: 4px 8px;
        font-size: 13px;
    }
    
    .like-icon {
        font-size: 14px;
    }
}
    </style>
<script>
    function toggleDescription(id) {
        const descriptionElement = document.getElementById(`description-${id}`);
        const fullDescription = descriptionElement.getAttribute('data-full-description');
        const shortDescription = descriptionElement.getAttribute('data-short-description');

        if (descriptionElement.innerHTML.includes('...')) {
            descriptionElement.innerHTML = fullDescription + ' <span class="read-more" onclick="toggleDescription(' + id + ')">Read Less</span>';
        } else {
            descriptionElement.innerHTML = shortDescription + ' <span class="read-more" onclick="toggleDescription(' + id + ')">Read More</span>';
        }
    }
</script>
<div class="#Daily_Messages" style="margin-top:-15px;">
  
<a href="Admin/display_messages.php" style="text-decoration: none; color: inherit; display: block;">
  <h2 id="Daily_Messages" 
        style="font-family: 'Dancing Script', cursive;
               font-size: 2.8rem;
               font-weight: 600;
               color: #333;
               padding: 10px 20px;
               background-color: white;
               letter-spacing: 2px;
               width: 100%;
               text-align: center;
               text-transform: none;
               margin: 0;
               box-sizing: border-box;">
    DAY'S THOUGHT
  </h2>
</a>


</div>
    <!-- Display the latest message in a card -->
    <div class="card-container">
        <?php
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $description = htmlspecialchars($row['description']);
            
            // Create short description (first 10 words)
            $words = explode(' ', $description);
            $shortDescription = implode(' ', array_slice($words, 0, 10));
            if (count($words) > 10) {
                $shortDescription .= '...';
            }
            
            $imagePath = '../Admin/uploads/' . basename(htmlspecialchars($row['image_path']));
            
            // Format the created_at date
            $uploadDate = date('F j, Y', strtotime($row['created_at']));
            ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted" style="font-size: 1.8rem; margin-top: -20px;">
                     <?php echo $uploadDate; ?>
                    </p>
                    <img style="margin-top:-10px;" src="<?php echo $imagePath; ?>" class="card-img-top" alt="Image">
                    <h5 style="margin-top:10px; color:#4A79E2;"  class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                    
                    
                    
                    <p class="card-text" id="description-<?php echo $row['id']; ?>" 
                       data-full-description="<?php echo nl2br($description); ?>" 
                       data-short-description="<?php echo nl2br($shortDescription); ?>">
                        <?php echo nl2br($shortDescription); ?>
                        <?php if (count($words) > 10) { ?>
<a href="Admin/display_messages.php?id=<?php echo $row['id']; ?>" class="read-more">Read More</a>
<?php } ?>
                    </p>
                    
                    <!-- Upload date -->

                    
                    <center>
                        <p class="like-count">
                            <i class="fas fa-heart like-icon"></i>
                            <span class="like-text">Likes:</span>
                            <span id="like-count-<?php echo $row['id']; ?>" class="like-number">
                                <?php echo isset($row['likes']) ? $row['likes'] : 0; ?>
                            </span>
                        </p>
                    </center>
                    <div class="text-center mt-4">
<a 
   href="Admin/display_messages.php" 
   class="btn btn-primary see-more-btn" 
   style="margin-top: -10px; width: 80%; @media (max-width: 767px) { width: 100% !important; }border-radius: 20px;"
>
   View More Day's Thoughts
</a>                    </div>
                    
            </div>
            
            <?php
        } else {
            echo "<p>No messages found for this calendar.</p>";
        }
        ?>
    </div>
    
</div>

                </div>
    <!-- Centered See More button -->
<!-- Centered See More button -->
<div class="#Daily_Messages" style="margin-top:-15px;">
     <hr>
<a href="Admin/winner_list.php" style="text-decoration: none; color: inherit; display: block;">
  <h2 id="Daily_Messages" 
        style="font-family: 'Dancing Script', cursive;
               font-size: 3.0rem;
               font-weight: 600;
               color: #333;
               padding: 10px 20px;
               background-color: white;
               letter-spacing: 2px;
               width: 100%;
               text-align: center;
               text-transform: none;
               margin: 0;
               box-sizing: border-box;">
   Health Champs
  </h2>
</a>
 <div class="card-container" id="winners" style="margin-top: 0px;">
        <?php
        // Get selected calendar or default to calendar1
        $selectedCalendar = $_GET['calendar'] ?? 'calendar1';
        
        $query = "SELECT winner_name, fb_post_image, winner_role, winner_date, winner_point 
                  FROM winners 
                  WHERE calendar_type = ?
                  ORDER BY winner_date DESC 
                  LIMIT 3";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $selectedCalendar);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $totalRows = count($rows);
        ?>
            <div class="winner-grid">
                <?php foreach ($rows as $row) { ?>
                    <div class="winner-item shadow-sm">

                        <?php $imagePath = './Admin/uploads/' . htmlspecialchars($row['fb_post_image']); ?>
                        <img src="<?php echo $imagePath; ?>" alt="Winner Image">
                        
                        <h5 style="margin-top: 10px;" class="winner-name"><?php echo htmlspecialchars($row['winner_name']); ?></h5>

                        <div class="winner-info" style="margin-top:-20px;">
                            <p class="winner-role">
                                <center>
                                    <strong>Points:</strong> <?php echo htmlspecialchars($row['winner_point']); ?>
                                </center>
                            </p>
                        </div>
                    </div>
                <?php } ?>
                
                <!-- Hardcoded "See More" Card -->
<div class="winner-item shadow-sm last-item position-relative" style=" color: white; border-radius: 8px;">
    
    <!-- Transparent overlay section at the top -->
    <div class="see-more-overlay d-flex justify-content-center align-items-center" 
         style="position: absolute; 
                top: 0; 
                left: 0; 
                width: 100%; 
                height:525px; /* Adjust height as needed */
                background-color: rgba(14, 71, 180, 0.7); 
                border-radius: 8px 8px 0 0; 
                z-index: 2;">
        <div class="winner-info" style="margin-top:-20px; padding-bottom: 15px; position: relative; z-index: 1;">
        <p class="winner-role">
            <center>
                <a href="../Admin/winner_list.php?calendar=<?php echo urlencode($selectedCalendar); ?>" 
                   class="btn btn-primary see-more-btn" 
                   style="width: 90%; 
                          padding: 8px; 
                          font-size: 14px;
                          background: #FF7C2A;
                          border: none;
                          border-radius: 20px;
                          color: white;
                          font-weight: bold;
                          transition: all 0.3s ease;">
                    See more Champs
                </a>
            </center>
        </p>
    </div>
    </div>

    <img src="./Admin/img/profile.jpeg" alt="See More Winners" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; position: relative; z-index: 1;">
    
    
</div>
</div>
            </div>
        <?php 
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
        ?>
    </div>
</div>



<div class="#Daily_Messages" style="margin-top:0px;">
     <hr style="
        height: 10px;
        background: linear-gradient(90deg, #3498db, #9b59b6);
   
        border: none;
        border-radius: 3px;
    ">
<a href="Admin/vid_display.php" style="text-decoration: none; color: inherit; display: block;">
  <h2 id="Daily_Messages" 
        style="font-family: 'Dancing Script', cursive;
               font-size: 3.0rem;
               font-weight: 600;
               color: #333;
               padding: 10px 20px;
               background-color: white;
               letter-spacing: 2px;
               width: 100%;
               text-align: center;
               text-transform: none;
               margin: 0;
               box-sizing: border-box;">
    Health Talk
  </h2>
</a>
<div class="video-container d-flex flex-wrap justify-content-center" id="videos">
    <?php
    // Use your existing connection ($conn)
    $query = "SELECT id, title, description, video_path, uploaded_at FROM videos ORDER BY uploaded_at DESC LIMIT 1";  
    $result = $conn->query($query);

    if ($result) {
        $videoCount = 0;
        while ($row = $result->fetch_assoc()) {
            $videoCount++;
            $videoSize = "medium";
            if ($videoCount == 1) {
                $videoSize = "large";
            }
    ?>
        <div class="video-card mb-4 <?php echo $videoSize; ?>" style="margin: 10px;">
            <!-- Video Title First -->
            <h5 class="video-title mt-2" style="color: #4A79E2;">
  <?php echo htmlspecialchars($row['title']); ?>
</h5>

            <!-- Video Container with Hover-to-Play -->
            <div class="video-hover-container" 
                 style="position: relative; overflow: hidden;"
                 onmouseover="playVideo(this)" 
                 onmouseout="pauseVideo(this)">
                
                <!-- Actual Video Element -->
                <video width="100%" height="auto" 
                       poster="<?php echo './Admin/uploads/' . pathinfo(htmlspecialchars($row['video_path']), PATHINFO_FILENAME) . '.jpg'; ?>"
                       preload="metadata"
                       muted
                       loop
                       style="width: 100%; height: auto; display: block;">
                    <source src="<?php echo './Admin/uploads/' . htmlspecialchars($row['video_path']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <!-- Play button overlay (hidden when video is playing) -->
                <div class="play-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                      display: flex; align-items: center; justify-content: center;
                      background: rgba(0,0,0,0.3); transition: opacity 0.3s;">
                    <div style="background: rgba(0,0,0,0.5); border-radius: 50%; 
                                width: 60px; height: 60px; display: flex; 
                                align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 30px;">▶</span>
                    </div>
                </div>
            </div>

            <!-- Video Description -->
            <p style="margin-top:10px;"class="video-description"><?php echo htmlspecialchars($row['description']); ?></p>

            <!-- Engagement Metrics -->
            <div style="display: flex; justify-content: space-around; margin: 10px 0; color: #666;">
                    <center>
                        <p class="like-count">
                            <i class="fas fa-heart like-icon"></i>
                            <span class="like-text">Likes:</span>
                            <span id="like-count-<?php echo $row['id']; ?>" class="like-number">
                                <?php echo isset($row['likes']) ? $row['likes'] : 0; ?>
                            </span>
                        </p>
                    </center>

            </div>

            <!-- Upload Date -->
            <center>
                <a href="Admin/vid_display.php" class="btn btn-primary see-more-btn" style="width:80%; margin-top:-5px; border-radius: 20px;">Watch More Health Talk</a>
            </center>
        </div>
    <?php 
        }
    } else {
        echo "Error: " . $conn->error;
    }
    ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoContainers = document.querySelectorAll('.video-hover-container');
    
    videoContainers.forEach(container => {
        const overlay = container.querySelector('.play-overlay');
        
        // Add click event to the play overlay
        overlay.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent the container's click event from firing
            window.location.href = './Admin/vid_display.php';
        });
        
        // Keep the existing video play/pause functionality for the container
        container.addEventListener('click', function() {
            const video = this.querySelector('video');
            if (video.paused) {
                video.play();
                overlay.style.opacity = '0';
            } else {
                video.pause();
                overlay.style.opacity = '1';
            }
        });
    });
});
</script>

<!-- Centered See More button -->
<style>
    .scrolling-wrapper {
    -webkit-overflow-scrolling: touch; /* For smooth scrolling on iOS */
    scrollbar-width: thin; /* For Firefox */
}

.scrolling-wrapper::-webkit-scrollbar {
    height: 8px;
}

.scrolling-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.scrolling-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.scrolling-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
}

</style>
<div class="#Daily_Messages" style="margin-top:-25px;">
            <hr >
<a href="../Admin/download_list.php" style="text-decoration: none; color: inherit; display: block;">
  <h2 id="Daily_Messages" 
        style="font-family: 'Dancing Script', cursive;
               font-size: 3.0rem;
               font-weight: 600;
               color: #333;
               padding: 10px 20px;
               background-color: white;
               letter-spacing: 2px;
               width: 100%;
               text-align: center;
               text-transform: none;
               margin: 0;
               box-sizing: border-box;">
    Have it for you
  </h2>
</a>
<div class="scrolling-wrapper" style="
    margin: 0 auto;                  /* centers the wrapper */
    overflow-x: auto; 
    white-space: nowrap; 
    -ms-overflow-style: none; 
    scrollbar-width: none;
">
    <div class="card-container" id="downloads" style="display: inline-block; white-space: nowrap;">
        
        <?php
        $query = "SELECT item_name, item_type, file_path, uploaded_at FROM downloads ORDER BY uploaded_at DESC LIMIT 5";
        $result = $conn->query($query);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $filePath = 'Admin/' . htmlspecialchars($row['file_path']); 
        ?>
                <div class="card shadow-sm" style="display: inline-block; width: 300px; margin-right: 15px; white-space: normal;">
                    <div class="card-body">
                        <?php if (in_array($row['item_type'], ['Images', 'Posts', 'Stickers'])) { ?>
                            <div style="height: 200px; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                                <img src="<?= $filePath ?>" class="card-img-top" alt="Download Image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        <?php } elseif ($row['item_type'] == 'PDFs') { ?>
                            <iframe src="<?= $filePath ?>#toolbar=0" width="100%" height="300px" style="border: none;"></iframe>
                        <?php } elseif ($row['item_type'] == 'Videos') { ?>
                            <video width="100%" height="200" controls>
                                <source src="<?= $filePath ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php } ?>
<h5 style="margin-top:10px; color: #4A79E2;" class="card-title">
    <?= htmlspecialchars($row['item_name']) ?>
</h5>
<a href="<?= $filePath ?>" download class="btn" style="background-color: #4A79E2; border: none; color: white;">
    Download
</a>
                    </div>
                </div>
        <?php 
            }
        } else {
            echo "Error fetching data: " . $conn->error;
        }
        ?>
    </div>
</div>


</div>
<div class="text-center" style="margin: 5px 5% 25px 10%;">
    <a href="Admin/download_list.php" class="btn btn-primary see-more-btn" style="margin-top:-40px; width:100%; border-radius: 20px;">View More Downloads</a>
</div>
</div>


<div class="#Daily_Messages" style="margin-top:-25px;">
     <hr >
<a href="Admin/display_messages.php" style="text-decoration: none; color: inherit; display: block;">
  <h2 id="Daily_Messages" 
        style="font-family: 'Dancing Script', cursive;
               font-size: 3.0rem;
               font-weight: 600;
               color: #333;
               padding: 10px 20px;
               background-color: white;
               letter-spacing: 2px;
               width: 100%;
               text-align: center;
               text-transform: none;
               margin: 0;
               box-sizing: border-box;">
    Next to come
  </h2>
</a>
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the correct base paths (assuming DB stores just filenames)
$baseUrl = 'Admin/';               // Base URL path
$baseDir = __DIR__ . '/Admin/';    // Base filesystem path
$uploadsFolder = 'uploads/';       // The uploads folder name

// Full paths for use in code
$uploadsUrl = $baseUrl . $uploadsFolder;   // For <img src=""> (e.g. "Admin/uploads/")
$uploadsDir = $baseDir . $uploadsFolder;  // For file_exists() (e.g. "/path/to/Admin/uploads/")

// Verify the uploads directory exists
if (!is_dir($uploadsDir)) {
    die("Error: Uploads directory does not exist at: " . $uploadsDir);
}

// Fetch events with their first associated image
$query = "SELECT e.id, e.title, e.description, e.created_at, e.role, e.date, e.time, e.venue,
                 (SELECT ei.image_path FROM event_images ei WHERE ei.event_id = e.id LIMIT 1) as image_path
          FROM events e
          ORDER BY CASE WHEN e.role = 'upcoming' THEN 1 ELSE 2 END, e.created_at DESC 
          LIMIT 3";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

$events = [];
while ($row = $result->fetch_assoc()) {
    // Clean the image path by removing any duplicate "uploads/" or slashes
    if (!empty($row['image_path'])) {
        $row['image_path'] = preg_replace('/(^uploads\/|^\/)/', '', $row['image_path']);
    }
    $events[] = $row;
}

if (empty($events)) {
    echo "<div class='alert alert-info'>No events found.</div>";
    exit;
}
?>

<div class="row" style="margin-top:-20px;">
    <!-- Big Upcoming Event -->
    <?php 
    $firstEvent = array_shift($events);
    $firstEventImage = !empty($firstEvent['image_path']) ? $firstEvent['image_path'] : '';
    $firstEventImageURL = $firstEventImage ? $uploadsUrl . $firstEventImage : '';
    $firstEventImagePath = $firstEventImage ? $uploadsDir . $firstEventImage : '';
    $firstEventHasImage = $firstEventImage && file_exists($firstEventImagePath);
    
    // Debug output (remove in production)
    echo "<!-- DEBUG INFO:
        Image path from DB: " . htmlspecialchars($firstEvent['image_path']) . "
        Constructed URL: $firstEventImageURL
        Constructed path: $firstEventImagePath
        File exists: " . ($firstEventHasImage ? 'Yes' : 'No') . "
    -->";
    ?>
    
    <div class="col-12 col-md-6 col-lg-12 d-flex justify-content-center mb-4">
        <div class="event-card p-3 text-center w-100">
            <h4 id="bigEventTitle" class="event-title mt-3"><?php echo htmlspecialchars($firstEvent['title']); ?></h4>
            
            <div class="event-thumbnail" id="bigEventThumbnail" style="width: 100%; height: auto; overflow: hidden; background-color: #f5f5f5;">
                <?php if ($firstEventHasImage): ?>
                    <img id="bigEventImage" src="<?php echo $firstEventImageURL; ?>" 
                         alt="<?php echo htmlspecialchars($firstEvent['title']); ?>" 
                         class="img-fluid w-100 h-auto" 
                         style="object-fit: cover; border-radius: 10px;"
                         onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'d-flex align-items-center justify-content-center w-100 h-100\'><span class=\'text-muted\'>Image failed to load</span></div>';">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center" style="height: 200px;">
                        <span class="text-muted">
                            <?php echo $firstEventImage ? 'Image not found: ' . htmlspecialchars($firstEventImagePath) : 'No image available'; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

<p id="bigEventDescription" style="margin-top:10px; text-align: center;" class="event-description">
    <?php echo htmlspecialchars($firstEvent['description']); ?>
</p>
<p id="bigEventRole" style="margin-top:-5px; text-align: center;" class="event-role text-muted">
    <?php echo ucfirst(htmlspecialchars($firstEvent['role'])); ?>
</p>
<div id="bigEventDetails" style="margin-top:5px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 20px; text-align: center;" class="text-muted">
    <span style="display: inline-flex; align-items: center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar" viewBox="0 0 16 16" style="margin-right: 8px;">
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
        </svg>
        <?php 
            $date = new DateTime($firstEvent['date']);
            echo htmlspecialchars($date->format('F j, Y')); 
        ?>
    </span>
    
    <span style="display: inline-flex; align-items: center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16" style="margin-right: 8px;">
            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
        </svg>
        <?php echo htmlspecialchars($firstEvent['time']); ?>
    </span>
    
    <span style="display: inline-flex; align-items: center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16" style="margin-right: 8px;">
            <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
            <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
        </svg>
        <?php echo htmlspecialchars($firstEvent['venue']); ?>
    </span>
</div>
        </div>
    </div>

    <!-- Small Event Thumbnails -->
    <div class="col-12 d-flex flex-wrap justify-content-center" style="margin-top: -40px;">
        <?php 
        array_unshift($events, $firstEvent); // Add first event back for thumbnails
        $totalEvents = count($events);

        foreach ($events as $index => $event) {
            $isLastItem = ($index === $totalEvents - 1);
            $eventImage = !empty($event['image_path']) ? $event['image_path'] : '';
            $eventImageURL = $eventImage ? $uploadsUrl . $eventImage : '';
            $eventImagePath = $eventImage ? $uploadsDir . $eventImage : '';
            $hasImage = $eventImage && file_exists($eventImagePath);
        ?>
            <div class="col-4 col-md-2 p-2 d-flex justify-content-center">
                <div class="event-thumbnail position-relative" 
                    style="width: 100px; height: 100px; overflow: hidden; border-radius: 10px; cursor: pointer; background-color: #f5f5f5;" 
                    onclick="<?php echo $isLastItem ? "window.location.href='./Admin/event_display.php'" : "changeBigImage('".($hasImage ? $eventImageURL : '')."', '" . addslashes(htmlspecialchars($event['title'])) . "', '" . addslashes(htmlspecialchars($event['description'])) . "', '" . addslashes(htmlspecialchars($event['role'])) . "', '" . addslashes(htmlspecialchars($event['date'])) . "', '" . addslashes(htmlspecialchars($event['time'])) . "', '" . addslashes(htmlspecialchars($event['venue'])) . "')"; ?>">
                    
                    <?php if ($isLastItem) { ?>
                        <div class="see-more-overlay d-flex justify-content-center align-items-center" 
                             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(14, 71, 180, 0.5); border-radius: 10px; z-index: 2;">
                            <span class="text-white font-weight-bold">See More</span>
                        </div>
                    <?php } ?>

                    <?php if ($hasImage): ?>
                        <div style="width: 100%; height: 100%; background-color: #f5f5f5; overflow: hidden;">
                            <img src="<?php echo $eventImageURL; ?>" 
                                      alt="<?php echo htmlspecialchars($event['title']); ?>" 
 
                                 class="img-fluid w-100 h-100" 
                                 style="object-fit: cover; border-radius: 10px;"
                                 onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'d-flex align-items-center justify-content-center w-100 h-100\'><span class=\'text-muted\'>Thumbnail failed</span></div>';" />
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                            <span class="text-muted"><?php echo $eventImage ? 'No image' : 'No image'; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function changeBigImage(imageUrl, title, description, role, date, time, venue) {
    if (imageUrl) {
        document.getElementById('bigEventImage').src = imageUrl;
    }
    document.getElementById('bigEventTitle').textContent = title;
    document.getElementById('bigEventDescription').textContent = description;
    document.getElementById('bigEventRole').textContent = role;
    
    // Format the date properly
    const eventDate = new Date(date);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = eventDate.toLocaleDateString(undefined, options);
    
    document.getElementById('bigEventDate').textContent = formattedDate + ' at ' + time;
    document.getElementById('bigEventVenue').textContent = venue;
}
</script>

<br>
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

/* Contact Info */
.contact-info {
    margin-bottom: 20px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    color: black;
}

.contact-icon {
    margin-right: 10px;
    color: #0d9488;
    min-width: 20px;
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
    color: white;
}

/* Column Titles */
.footer-title {
    font-size: 18px;
    font-weight: 600;
    color: black;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-title:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background-color: #0d9488;
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
    font-size: 14px;
}

.footer-link:hover {
    color: #0d9488;
}

/* Get Started Button */
.get-started-button {
    display: inline-block;
    background-color: #FF7C2A;
    color: white;
    font-weight: 600;
    text-decoration: none;
    padding: 10px 24px;
    border-radius: 20px;
    margin-bottom: 20px;
    text-align: center;
    transition: background-color 0.3s ease;
}
.get-started-button:hover {
    background-color: #D34DEE;
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
    flex-wrap: wrap;
}

.copyright-text {
    color: white;
    font-size: 14px;
    margin: 5px 0;
}

.footer-links {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.copyright-link {
    color: white;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
    white-space: nowrap;
}

.copyright-link:hover {
    color: #FF7C2A;
}

/* Partner Logos */
.partner-logos {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    margin-top: 15px;
}

.partner-logo {
    height: 30px;
    width: auto;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.partner-logo:hover {
    opacity: 1;
}

/* Legal Links */
.legal-links {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 20px;
}

.legal-link {
    color: black;
    text-decoration: none;
    font-size: 13px;
    transition: color 0.3s ease;
}

.legal-link:hover {
    color: #0d9488;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .services-links-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .copyright-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
        justify-content: center;
    }
    
    .footer-links {
        justify-content: center;
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
    
    .footer-logo {
        justify-content: center;
        margin: 0 auto 15px auto;
    }
    
    .footer-logo img {
        max-width: 200px;
    }
    
    .social-media {
        justify-content: center;
        margin: 10px auto;
    }
    
    .footer-title {
        font-size: 16px;
    }
    
    .footer-description {
        text-align: center;
    }
    
    .partner-logos {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .services-links-wrapper {
        grid-template-columns: 1fr;
    }
    
    .footer-links {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
}
</style>

<!-- Footer Section -->
<footer class="footer">
    <div class="footer-container">
        <!-- Logo and Contact Section -->
        <div class="footer-column">
            <div class="footer-logo">
                <img src="Admin/img/log.png" alt="DailyHealth Logo">
            </div>
            <p class="footer-description">Your trusted partner in health and wellness. Providing quality healthcare services across Sri Lanka.</p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>+94 777 867 942</span>
                </div>
                <div class="contact-item">
                    <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span>info@dailyhealth.lk</span>
                </div>
                <div class="contact-item">
                    <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>123 Health Street, Colombo, Sri Lanka</span>
                </div>
            </div>
            
            <div class="social-media">
                <a href="https://www.facebook.com/share/1BUFn5hKYY/" class="social-icon" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                </a>
                <a href="https://wa.me/94777867942" class="social-icon" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
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
                    <a href="Admin/download_list.php" class="footer-link">Downloads</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/event_display.php" class="footer-link">Events</a>
                </li>
                <li class="footer-item">
                    <a href="about.php" class="footer-link">About Us</a>
                </li>
                <li class="footer-item">
                    <a href="faq.php" class="footer-link">FAQs</a>
                </li>
                <li class="footer-item">
                    <a href="contact.php" class="footer-link">Contact Us</a>
                </li>
            </ul>
        </div>
        
        <!-- Services Column -->
        <div class="footer-column">
            <h3 class="footer-title">Our Services</h3>
            <ul class="footer-list">
                <li class="footer-item">
                    <a href="services.php#consultation" class="footer-link">Online Consultations</a>
                </li>
                <li class="footer-item">
                    <a href="services.php#pharmacy" class="footer-link">Pharmacy Services</a>
                </li>
                <li class="footer-item">
                    <a href="services.php#wellness" class="footer-link">Wellness Programs</a>
                </li>
                <li class="footer-item">
                    <a href="services.php#diagnostics" class="footer-link">Diagnostic Services</a>
                </li>
                <li class="footer-item">
                    <a href="services.php#emergency" class="footer-link">Emergency Care</a>
                </li>
                <li class="footer-item">
                    <a href="opportunities.php" class="footer-link">Career Opportunities</a>
                </li>
                <li class="footer-item">
                    <a href="partners.php" class="footer-link">Partnerships</a>
                </li>
            </ul>
            
            <div class="legal-links">
                <a href="privacy.php" class="legal-link">Privacy Policy</a>
                <a href="terms.php" class="legal-link">Terms of Use</a>
                <a href="disclaimer.php" class="legal-link">Disclaimer</a>
                <a href="cookies.php" class="legal-link">Cookies Policy</a>
                <a href="refund.php" class="legal-link">Refund Policy</a>
            </div>
        </div>
        
        <!-- Health Updates Column -->
        <div class="footer-column">
            <h3 class="footer-title">Health Updates</h3>
            <p class="footer-description">Subscribe to receive health tips and updates.</p>
            
            <a href="https://wa.me/94777867942" class="get-started-button" target="_blank" rel="noopener noreferrer">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                Send a message
            </a>
            
            <div class="newsletter-form">
                <input type="email" class="newsletter-input" placeholder="Your email address">
                <button type="submit" class="newsletter-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
            
            <h3 class="footer-title">Our Partners</h3>
            <div class="partner-logos">
                <img src="images/partner1.png" alt="Partner 1" class="partner-logo">
                <img src="images/partner2.png" alt="Partner 2" class="partner-logo">
                <img src="images/partner3.png" alt="Partner 3" class="partner-logo">
            </div>
            
            <div class="important-notices">
                <h3 class="footer-title">Important Notices</h3>
                <p style="font-size: 12px; color: black; line-height: 1.4;">
                    DailyHealth.lk provides health information for educational purposes only. 
                    Consult a healthcare professional for medical advice. 
                    Emergency? Call 1990.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Copyright Section -->
    <div class="copyright-section">
        <div class="copyright-container">
            <div class="copyright-text">© 2025 DailyHealth.lk. All rights reserved. Developed by [Your Team Name]</div>
            <div class="footer-links">
                <a href="sitemap.php" class="copyright-link">Sitemap</a>
                <a href="accessibility.php" class="copyright-link">Accessibility</a>
                <a href="feedback.php" class="copyright-link">Feedback</a>
                <a href="contact.php" class="copyright-link">Contact Developers</a>
            </div>
        </div>
    </div>
</footer>

    <script>
function changeBigImage(image, title, description, role, date) {
    document.getElementById("bigEventImage").src = image;
    document.getElementById("bigEventTitle").innerText = title;
    document.getElementById("bigEventDescription").innerText = description;
    document.getElementById("bigEventRole").innerText = role;
    document.getElementById("bigEventDate").innerText = date;
}
// Toggle the full description visibility
    function toggleDescription(id) {
        var descriptionElement = document.getElementById('description-' + id);
        
        var fullDescription = '<?php echo addslashes($description); ?>';
        
        if (descriptionElement.innerHTML.includes('Read More')) {
            descriptionElement.innerHTML = fullDescription + ' <span class="read-more" onclick="toggleDescription(' + id + ')">Read Less</span>';
        } else {
            var shortDescription = fullDescription.length > 50 ? fullDescription.substring(0, 50) + '...' : fullDescription;
            descriptionElement.innerHTML = shortDescription + ' <span class="read-more" onclick="toggleDescription(' + id + ')">Read More</span>';
        }
    }
    
   document.querySelector('.navbar a[href="#events"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('events').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
         document.querySelector('.navbar a[href="#downloads"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('downloads').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
        document.querySelector('.navbar a[href="#videos"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('videos').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
               document.querySelector('.navbar a[href="#calendar"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('calendar').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
        document.querySelector('.navbar a[href="#Daily_Messages"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('Daily_Messages').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});

document.querySelector('.navbar a[href="#winners"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('winners').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
        document.addEventListener('DOMContentLoaded', function () {
            const cells = document.querySelectorAll('td');
            const dateModal = new bootstrap.Modal(document.getElementById('dateModal')); // Initialize the modal

            cells.forEach(cell => {
                cell.addEventListener('click', function () {
                    const date = this.getAttribute('data-date');  // Get the clicked date
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');

                    // Set the modal title and daily message (if any)
                    document.getElementById('dateModalLabel').innerText = 'Details for ' + date;
                    document.getElementById('dailyMessage').innerText = title || "No daily message for this day.";
                    document.getElementById('noteMessage').innerText = description || "No description available.";

                    // Fetch existing note for this date
                    fetchNote(date);

                    // Show the modal
                    dateModal.show();
                });
            });

            function fetchNote(date) {
                const year = <?php echo $selectedYear; ?>;
                const month = <?php echo $selectedMonth; ?>;

                // Get the selected cell's existing note
                const selectedCell = document.querySelector(`td[data-date='${date}']`);
                if (selectedCell && selectedCell.getAttribute('data-description')) {
                    const existingNote = selectedCell.getAttribute('data-description');
                    document.getElementById('noteMessage').innerText = existingNote;
                    
                    // Show Edit and Delete buttons
                    document.getElementById('editNoteButton').style.display = 'inline-block';
                    document.getElementById('deleteNoteButton').style.display = 'inline-block';
                    document.getElementById('addNoteButton').style.display = 'none';
                    return; // Avoid unnecessary AJAX request
                }

              // If no existing note in frontend, fetch from backend
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `Admin/fetch_note.php?year=${year}&month=${month}&date=${date}`, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                document.getElementById('noteMessage').innerText = response.note || "No note available.";
                if (selectedCell) selectedCell.setAttribute('data-description', response.note); // Store it in the cell
                
                document.getElementById('editNoteButton').style.display = 'inline-block';
                document.getElementById('deleteNoteButton').style.display = 'inline-block';
                document.getElementById('addNoteButton').style.display = 'none';
            } else {
                document.getElementById('noteMessage').innerText = "No note available.";
                document.getElementById('editNoteButton').style.display = 'none';
                document.getElementById('deleteNoteButton').style.display = 'none';
                document.getElementById('addNoteButton').style.display = 'inline-block';
            }
        }
    };
    xhr.send();
}



    // Handle "Add Note" button click
    document.getElementById('addNoteButton').addEventListener('click', function() {
        const date = document.getElementById('dateModalLabel').innerText.split(' ')[2]; // Extract date from modal title
        const note = prompt("Enter your note for this day:");
        if (note) {
            saveNote(date, note);
        }
    });

    // Handle "Edit Note" button click
    document.getElementById('editNoteButton').addEventListener('click', function() {
        const date = document.getElementById('dateModalLabel').innerText.split(' ')[2]; // Extract date from modal title
        const currentNote = document.getElementById('noteMessage').innerText;
        const updatedNote = prompt("Edit your note for this day:", currentNote);
        if (updatedNote !== null) {
            saveNote(date, updatedNote);
        }
    });

    // Handle "Delete Note" button click
    document.getElementById('deleteNoteButton').addEventListener('click', function() {
        const date = document.getElementById('dateModalLabel').innerText.split(' ')[2]; // Extract date from modal title
        if (confirm("Are you sure you want to delete this note?")) {
            deleteNote(date);
        }
    });

    function saveNote(date, note) {
    const year = <?php echo $selectedYear; ?>;
    const month = <?php echo $selectedMonth; ?>;

    const noteData = {
        year: year,
        month: month,
        date: date,
        note: note
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Admin/save_note.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                alert("Note saved successfully!");
                
                // Update the modal content
                document.getElementById('noteMessage').innerText = response.note;

                // Update the clicked date cell to store the new note
                const selectedCell = document.querySelector(`td[data-date='${date}']`);
                if (selectedCell) {
                    selectedCell.setAttribute('data-description', response.note);
                }
                
                fetchNote(date); // Refresh the note display
            } else {
                alert("Error saving note: " + response.message);
            }
        }
    };
    xhr.send(JSON.stringify(noteData));
}



    // Function to delete a note
function deleteNote(date) {
    const year = <?php echo json_encode($selectedYear); ?>;
    const month = <?php echo json_encode($selectedMonth); ?>;

    const noteData = { year, month, date };

    // Create an AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Admin/delete_note.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        alert("Note deleted successfully!");
                        document.getElementById('noteMessage').innerText = "No note available.";
                        fetchNote(date); // Refresh the note display
                        location.reload(); // Refresh the page after successful deletion
                    } else {
                        alert("Error deleting note: " + response.message);
                    }
                } catch (error) {
                    console.error("JSON Parse Error:", error);
                    alert("An unexpected error occurred.");
                }
            } else {
                console.error("AJAX Error: ", xhr.statusText);
                alert("Failed to communicate with the server.");
            }
        }
    };

    xhr.send(JSON.stringify(noteData));
}

});

//like button
 function likePost(id) {
            fetch('Admin/like_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById("like-count-" + id).innerText = data.likes;
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }


function shareMessage(postId) {
    const shareUrl = 'https://www.dailyhealth.lk/Admin/display_messages.php?id=' + postId;
    const shareText = 'Check out this post!';

    if (navigator.share) {
        navigator.share({
            title: 'Share this message',
            text: shareText,
            url: shareUrl
        }).then(() => {
            console.log('Thanks for sharing!');
        }).catch((error) => {
            console.error('Error sharing:', error);
        });
    } else {
        // Fallback for browsers that do not support the Web Share API
        const shareWindow = window.open('', '_blank', 'width=600,height=400');
        shareWindow.document.write(`
            <div>
                <h3>Share this message</h3>
                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}" target="_blank">Facebook</a><br>
                <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}" target="_blank">Twitter</a><br>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(shareText)}" target="_blank">LinkedIn</a><br>
            </div>
        `);
    }
}

</script>
    

   
</body>

</html>

<?php $conn->close(); ?>
