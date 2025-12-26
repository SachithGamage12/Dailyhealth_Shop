    <?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch events with their primary images
$role_filter = $_GET['role'] ?? 'all';
$sql = "SELECT e.*, 
        (SELECT image_path FROM event_images WHERE event_id = e.id LIMIT 1) as primary_image
        FROM events e";
if ($role_filter !== 'all') {
    $sql .= " WHERE e.role = '$role_filter'";
}
$sql .= " LIMIT 10";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// AJAX endpoint to fetch all images for an event
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $query = "SELECT e.*, 
              GROUP_CONCAT(ei.image_path) as all_images
              FROM events e
              LEFT JOIN event_images ei ON e.id = ei.event_id
              WHERE e.id = ?
              GROUP BY e.id";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    
    // Convert comma-separated images to array
    if ($event['all_images']) {
        $event['all_images'] = explode(',', $event['all_images']);
    } else {
        $event['all_images'] = [];
    }
    
    echo json_encode($event);
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-hover: #1B5E20;
            --secondary-color: #f8f9fa;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }
        
        /* Big Clear Filter Buttons */
        .filter-container {
            text-align: center;
            margin: 30px 0;
            padding: 0 15px;
        }
        
        .filter-btn-group {
            display: inline-flex;
            background: var(--secondary-color);
            padding: 10px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .filter-btn {
            padding: 12px 25px;
            margin: 0 5px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: transparent;
            color: #555;
        }
        
        .filter-btn:hover {
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary-color);
        }
        
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 8px rgba(46, 125, 50, 0.3);
        }
        
        /* Large Event Cards */
        .event-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .event-card {
            background: white;
            width: 350px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        
        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .event-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        
        .event-content {
            padding: 25px;
        }
        
        .event-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .event-description {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }
        
        .event-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .event-role {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
            background: rgba(46, 125, 50, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
        }
        
        .event-date {
            font-size: 15px;
            color: #777;
            display: flex;
            align-items: center;
        }
        
        .event-date i {
            margin-right: 5px;
            color: var(--primary-color);
        }
        
        .event-created {
            margin-top: 10px;
            font-size: 14px;
            color: #888;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .event-created i {
            color: var(--primary-color);
        }
        
        .no-events {
            text-align: center;
            padding: 60px 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .no-events-icon {
            font-size: 60px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .no-events-text {
            font-size: 20px;
            color: #666;
        }
        
        /* Event Modal Styles */
        .modal-event-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-event-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .modal-event-description {
            font-size: 18px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
        }
        
        .modal-event-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .modal-event-detail {
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #555;
        }
        
        .modal-event-detail i {
            margin-right: 8px;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .modal-event-content {
            padding: 30px;
        }
        
        .modal-event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            border-top: 1px solid #eee;
            background-color: #f9f9f9;
        }
        
        .modal-event-created {
            font-size: 14px;
            color: #888;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filter-btn-group {
                flex-direction: column;
                border-radius: 15px;
                padding: 15px;
            }
            
            .filter-btn {
                margin: 5px 0;
                width: 100%;
            }
            
            .event-card {
                width: 100%;
                max-width: 400px;
            }
            
            .modal-event-image {
                height: 200px;
            }
            
            .modal-event-title {
                font-size: 24px;
            }
            
            .modal-event-description {
                font-size: 16px;
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
             margin: -10px; /* Add margin -->
            color: #0E47B4 !important; <!-- Force black -->
             text-decoration: bold;
            font-weight: 500;


        ">Profile</p> <!-- FontAwesome user icon -->
    </a>
    <a class="nav-link" href="../shop/products.php">
        <p style="
            display: inline-block; /* Make the box fit the content */
            padding: 6px; /* Add some padding inside the box */
            border-radius: 20px; /* Increased rounded corners */
            margin: -10px; /* Add margin -->
            color: #0E47B4 !important; <!-- Force black -->
            text-decoration: bold;
            font-weight: 500;


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
                <li class="breadcrumb-item active">Next to come</li>
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

   <style>
        /* Modal Styles */
        .modal-event-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .modal-event-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .modal-event-description {
            font-size: 18px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
        }
        
        .modal-event-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .modal-event-detail {
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #555;
        }
        
        .modal-event-detail i {
            margin-right: 8px;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .modal-image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .modal-image-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }
        
        .modal-image-thumbnail:hover, 
        .modal-image-thumbnail.active {
            transform: scale(1.05);
            border-color: #2E7D32;
        }
        
        .image-counter {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .carousel-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .carousel-btn {
            background-color: #2E7D32;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .carousel-btn:hover {
            background-color: #1B5E20;
        }
        
        .carousel-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .filter-btn-group {
                flex-direction: column;
                border-radius: 15px;
                padding: 15px;
            }
            
            .filter-btn {
                margin: 5px 0;
                width: 100%;
            }
            
            .event-card {
                width: 100%;
                max-width: 400px;
            }
            
            .modal-event-image {
                height: 200px;
            }
            
            .modal-event-title {
                font-size: 24px;
            }
            
            .modal-event-description {
                font-size: 16px;
            }
        }
                /* Big Clear Filter Buttons */
        .filter-container {
            text-align: center;
            margin: 30px 0;
            padding: 0 15px;
        }
        
        .filter-btn-group {
            display: inline-flex;
            background: var(--secondary-color);
            padding: 10px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Table Styles */
        .event-table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .event-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        .event-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .event-table tr:last-child td {
            border-bottom: none;
        }
        
        .event-table tr:hover {
            background-color: rgba(46, 125, 50, 0.05);
        }
        
        .event-row {
            cursor: pointer;
        }
        
        .event-title-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .event-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .event-title-text {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .event-date-cell {
            color: #555;
        }
        
        .event-role-cell {
            font-weight: 600;
            color: var(--primary-color);
            background: rgba(46, 125, 50, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .dropdown-icon {
            color: var(--primary-color);
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .dropdown-icon:hover {
            transform: scale(1.2);
        }
        
        /* No Events Styles */
        .no-events {
            text-align: center;
            padding: 60px 20px;
            max-width: 600px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .no-events-icon {
            font-size: 60px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .no-events-text {
            font-size: 20px;
            color: #666;
        }
        
        /* Modal Styles */
        .modal-event-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .modal-event-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .modal-event-description {
            font-size: 18px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
        }
        
        .modal-event-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .modal-event-detail {
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #555;
        }
        
        .modal-event-detail i {
            margin-right: 8px;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .modal-image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .modal-image-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }
        
        .modal-image-thumbnail:hover, 
        .modal-image-thumbnail.active {
            transform: scale(1.05);
            border-color: #2E7D32;
        }
        
        .image-counter {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .carousel-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .carousel-btn {
            background-color: #2E7D32;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .carousel-btn:hover {
            background-color: #1B5E20;
        }
        
        .carousel-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
         /* Responsive Styles */
        @media (max-width: 768px) {
            .event-table {
                width: 95%;
            }
            
            .event-table th, 
            .event-table td {
                padding: 10px;
            }
            
            .event-thumbnail {
                width: 40px;
                height: 40px;
            }
            
            .event-title-text {
                font-size: 14px;
            }
            
            .event-date-cell,
            .event-role-cell {
                font-size: 12px;
            }
            
            .modal-event-image {
                height: 200px;
            }
            
            .modal-event-title {
                font-size: 24px;
            }
            
            .modal-event-description {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<center><h2 style="margin-top: 10px;">Next to come</h2></center>
  
<div class="button-container" style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-top:15px;">
    <a href="?role=upcoming"><button class="<?php echo $role_filter === 'upcoming' ? 'active' : ''; ?>" style="padding: 12px 24px; font-size: 16px; border-radius: 6px; border: none; cursor: pointer; background-color: <?php echo $role_filter === 'upcoming' ? '#2E7D32' : '#4CAF50'; ?>; color: white; transition: all 0.3s ease;">Upcoming Events</button></a>
    <a href="?role=completed"><button class="<?php echo $role_filter === 'completed' ? 'active' : ''; ?>" style="padding: 12px 24px; font-size: 16px; border-radius: 6px; border: none; cursor: pointer; background-color: <?php echo $role_filter === 'completed' ? '#2E7D32' : '#4CAF50'; ?>; color: white; transition: all 0.3s ease;">Completed Events</button></a>
</div>

<?php if (empty($events)): ?>
    <div class="no-events">
        <div class="no-events-icon">
            <i class="fas fa-calendar-times"></i>
        </div>
        <p class="no-events-text">No events found for this category</p>
    </div>
<?php else: ?>
    <table class="event-table" style="margin-top:10px;">
        <thead>
            <tr>
                <th>Event</th>
                <th>Date</th>
           
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr class="event-row">
                    <td>
                        <div class="event-title-cell">
                            <?php if ($event['primary_image']): ?>
                                <img src="<?= $event['primary_image'] ?>" alt="<?= $event['title'] ?>" class="event-thumbnail">
                            <?php else: ?>
                                <div class="event-thumbnail" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-calendar-alt" style="color: #999;"></i>
                                </div>
                            <?php endif; ?>
                            <span class="event-title-text"><?= $event['title'] ?></span>
                        </div>
                    </td>
                    <td class="event-date-cell">
                        <?= !empty($event['date']) ? date('M d, Y', strtotime($event['date'])) : 'Not specified' ?>
                    </td>
                    
                    <td style="text-align: right;">
                        <i class="fas fa-chevron-down dropdown-icon" onclick="openEventModal(<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>)"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalEventTitle" class="modal-event-title"></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalEventImage" src="" alt="Event Image" class="modal-event-image">
                
                <!-- Image Gallery Section -->
                <div id="imageGalleryContainer">
                    <div class="modal-image-gallery" id="modalImageGallery"></div>
                    <div class="image-counter" id="imageCounter"></div>
                    <div class="carousel-nav">
                        <button class="carousel-btn" id="prevBtn" onclick="navigateGallery(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-btn" id="nextBtn" onclick="navigateGallery(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <p id="modalEventDescription" class="modal-event-description"></p>
                
                <div class="modal-event-details">
                    <div class="modal-event-detail">
                        <i class="fas fa-user-tag"></i>
                        <span id="modalEventRole"></span>
                    </div>
                    <div class="modal-event-detail">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="modalEventDate"></span>
                    </div>
                    <div class="modal-event-detail">
                        <i class="fas fa-clock"></i>
                        <span id="modalEventTime"></span>
                    </div>
                    <div class="modal-event-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span id="modalEventVenue"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentEvent = null;
let currentImageIndex = 0;

function openEventModal(event) {
    currentEvent = event;
    currentImageIndex = 0;
    
    // Set basic info immediately
    document.getElementById('modalEventTitle').textContent = event.title;
    document.getElementById('modalEventDescription').textContent = event.description;
    document.getElementById('modalEventRole').textContent = event.role.charAt(0).toUpperCase() + event.role.slice(1);
    document.getElementById('modalEventDate').textContent = event.date || 'Not specified';
    document.getElementById('modalEventTime').textContent = event.time || 'Not specified';
    document.getElementById('modalEventVenue').textContent = event.venue || 'Not specified';
    
    // Show primary image immediately
    if (event.primary_image) {
        document.getElementById('modalEventImage').src = event.primary_image;
    }
    
    // Initialize and show the modal
    var modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
    
    // Then fetch additional data if we have an ID
    if (event.id) {
        fetch(`event_display.php?id=${event.id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(fullEvent => {
                currentEvent = fullEvent;
                updateModalContent();
            })
            .catch(error => {
                console.error('Error fetching event details:', error);
                // Hide gallery since we couldn't load additional images
                document.getElementById('imageGalleryContainer').style.display = 'none';
            });
    } else {
        // If no ID, just hide the gallery
        document.getElementById('imageGalleryContainer').style.display = 'none';
    }
}

function updateModalContent() {
    if (!currentEvent) return;
    
    const event = currentEvent;
    const imageGallery = document.getElementById('modalImageGallery');
    const imageCounter = document.getElementById('imageCounter');
    
    if (event.all_images && event.all_images.length > 0) {
        imageGallery.innerHTML = '';
        
        event.all_images.forEach((image, index) => {
            const img = document.createElement('img');
            img.src = image;
            img.className = `modal-image-thumbnail ${index === currentImageIndex ? 'active' : ''}`;
            img.alt = `Event Image ${index + 1}`;
            img.onclick = () => {
                currentImageIndex = index;
                updateMainImage();
                updateThumbnailSelection();
            };
            imageGallery.appendChild(img);
        });
        
        updateMainImage();
        imageCounter.textContent = `${currentImageIndex + 1} of ${event.all_images.length} images`;
        document.getElementById('imageGalleryContainer').style.display = 'block';
    } else {
        document.getElementById('imageGalleryContainer').style.display = 'none';
    }
}

function updateMainImage() {
    if (currentEvent.all_images && currentEvent.all_images.length > 0) {
        document.getElementById('modalEventImage').src = currentEvent.all_images[currentImageIndex];
        document.getElementById('imageCounter').textContent = 
            `${currentImageIndex + 1} of ${currentEvent.all_images.length} images`;
    }
}

function updateThumbnailSelection() {
    const thumbnails = document.querySelectorAll('.modal-image-thumbnail');
    thumbnails.forEach((thumb, index) => {
        if (index === currentImageIndex) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

function navigateGallery(direction) {
    if (!currentEvent.all_images || currentEvent.all_images.length === 0) return;
    
    currentImageIndex += direction;
    
    if (currentImageIndex < 0) {
        currentImageIndex = currentEvent.all_images.length - 1;
    } else if (currentImageIndex >= currentEvent.all_images.length) {
        currentImageIndex = 0;
    }
    
    updateMainImage();
    updateThumbnailSelection();
}
</script>

    
    
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