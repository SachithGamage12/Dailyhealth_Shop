<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters
$winner_role = isset($_GET['role']) ? $_GET['role'] : 'Prize Winner';
$search_week_title = isset($_GET['week_title']) ? $_GET['week_title'] : "";
$calendar_type = isset($_GET['calendar']) ? $_GET['calendar'] : 'calendar1';

// Get distinct week titles for dropdown
$weekTitles = [];
$titlesStmt = $conn->prepare("SELECT DISTINCT week_title FROM winners WHERE calendar_type = ? ORDER BY week_title ASC");
$titlesStmt->bind_param("s", $calendar_type);
$titlesStmt->execute();
$titlesResult = $titlesStmt->get_result();
while ($row = $titlesResult->fetch_assoc()) {
    if (!empty($row['week_title'])) {
        $weekTitles[] = $row['week_title'];
    }
}
$titlesStmt->close();

// Prepare main query
$sql = "SELECT * FROM winners WHERE winner_role = ? AND calendar_type = ?";
$params = [$winner_role, $calendar_type];
$types = "ss";

if (!empty($search_week_title)) {
    $sql .= " AND week_title LIKE ?";
    $params[] = "%" . $search_week_title . "%";
    $types .= "s";
}

$sql .= " ORDER BY winner_date DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in preparing statement: " . $conn->error);
}

// Bind parameters
$bindParams = [$types];
foreach ($params as &$param) {
    $bindParams[] = &$param;
}
call_user_func_array([$stmt, 'bind_param'], $bindParams);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Health Champs</title>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
/* Modern Color Palette */
:root {
  --primary: #4361ee;
  --primary-light: #4895ef;
  --secondary: #3f37c9;
  --accent: #f72585;
  --success: #4cc9f0;
  --warning: #f8961e;
  --danger: #f94144;
  --light: #f8f9fa;
  --dark: #212529;
  --gray-100: #f8f9fa;
  --gray-200: #e9ecef;
  --gray-300: #dee2e6;
  --gray-400: #ced4da;
  --gray-500: #adb5bd;
  --gray-600: #6c757d;
  --gray-700: #495057;
  --gray-800: #343a40;
  --gray-900: #212529;
}

/* Base Styles */
body {
  font-family: 'Nunito', 'Segoe UI', sans-serif;
  background-color: white;
  margin: 0;
  padding: 0;
  color: var(--gray-800);
  line-height: 1.6;
}

.container {
  width: 100%;
  max-width: 1200px;
  padding: 2rem;
  margin: 0 auto;
}

/* Typography */
h2 {
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1.5rem;
  font-size: 2.2rem;
  text-align: center;
  position: relative;
  padding-bottom: 15px;
}

h2:after {
  content: '';
  position: absolute;
  width: 60px;
  height: 4px;
  background: var(--accent);
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  border-radius: 2px;
}
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
        
        .filter-buttons button.active {
            opacity: 1;
            transform: scale(1.15);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                position: fixed;
                top: 62px;
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 50px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }
        
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
        }

/* Filter Container */
.filter-container {
  max-width: 950px;
  margin: 2rem auto 3rem;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.filter-container:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Filter Rows */
.filter-row {
  margin-bottom: 1.5rem;
}

.filter-combo {
  display: flex;
  gap: 1.5rem;
}

.filter-calendar, .filter-search {
  flex: 1;
}

.filter-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--gray-700);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Calendar Select */
.filter-calendar select {
  width: 100%;
  padding: 0.875rem 1rem;
  font-size: 0.95rem;
  border: 2px solid var(--gray-300);
  border-radius: 8px;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1rem;
  transition: all 0.3s ease;
}

.filter-calendar select:focus {
  border-color: var(--primary-light);
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
  outline: none;
}

/* Date Input */
.search-combo {
  display: flex;
  gap: 0.75rem;
}

.search-combo input[type="date"] {
  flex: 1;
  padding: 0.875rem 1rem;
  font-size: 0.95rem;
  border: 2px solid var(--gray-300);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.search-combo input[type="date"]:focus {
  border-color: var(--primary-light);
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
  outline: none;
}

/* Buttons */
.search-btn, .filter-btn {
  padding: 0.875rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.search-btn {
  background-color: var(--primary);
  color: white;
}

.search-btn:hover {
  background-color: var(--secondary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
}

.prize-winner {
  background-color: var(--warning);
  color: white;
}

.nominate-winner {
  background-color: var(--success);
  color: white;
}
.filter-container {
    max-width: 950px;
    margin: 2rem auto 3rem;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
}

.filter-row {
    margin-bottom: 1.5rem;
}

.filter-calendar select,
.filter-search input {
    width: 100%;
    padding: 0.875rem 1rem;
    font-size: 0.95rem;
    border: 2px solid #dee2e6;
    border-radius: 8px;
}

.filter-search .input-group {
    display: flex;
}

.filter-search .input-group input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
}

.filter-search .input-group button {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    padding: 0.875rem 1.5rem;
}

.button-row {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

.filter-buttons {
    display: flex;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .filter-row .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .filter-buttons {
        flex-direction: column;
        width: 100%;
    }
}

.button-row {
  display: flex;
  justify-content: center;
  margin-top: 0.5rem; /* Added space for the larger active button */
  padding-bottom: 0.5rem;
}

.filter-buttons {
  display: flex;
  gap: 1.5rem; /* Increased gap for larger active button */
}

/* Card Styles */
.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.card {
  background: white;
  border-radius: 12px;
  border: none;
  border: 1px solid black;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card:hover {
  transform: translateY(-12px) scale(1.02);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
}

.card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  transition: all 0.5s ease;
}

.card:hover img {
  transform: scale(1.05);
}

.card h3 {
  color: var(--primary);
  margin-top: 1rem;
  font-size: 1.25rem;
  font-weight: 700;
  padding: 0 1.25rem;
}

.card p {
  font-size: 0.95rem;
  color: var(--gray-700);
  margin: 0.5rem 0;
  padding: 0 1.25rem;
}

.card p:last-of-type {
  margin-bottom: 1.25rem;
}

.card p strong {
  color: var(--gray-800);
  font-weight: 600;
}

.card .date {
  color: var(--gray-600);
  font-size: 0.9rem;
  display: flex;
  align-items: center;
}

.card .date:before {
  content: '\f073';
  font-family: 'Font Awesome 5 Free';
  margin-right: 0.5rem;
  color: var(--accent);
}

/* Modal Styles */
.modal {
  background-color: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(5px);
}

.modal-content {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  max-width: 80%;
  max-height: 80vh;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  border: 3px solid white;
  object-fit: contain;
}
.close {
  color: white;
  font-size: 3rem;
  text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
  opacity: 0.8;
  transition: all 0.3s ease;
}

.close:hover {
  color: var(--accent);
  opacity: 1;
  transform: rotate(90deg);
}

/* Breadcrumb Styles */
    .breadcrumb-container {
        background-color: white;
        padding: 8px 15px;
        width: 100%;
        margin: 0;
    }

.breadcrumb {
  margin: 0;
  padding: 0;
}

.breadcrumb-item a {
  color: var(--primary);
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.breadcrumb-item a:hover {
  color: var(--secondary);
  text-decoration: underline;
}

.breadcrumb-item.active {
  color: var(--gray-600);
  font-weight: 600;
}

/* Responsive Styles */
@media (max-width: 992px) {
  .navbar-collapse {
    background-color: white;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }
  
  h2 {
    font-size: 1.8rem;
  }
  
  .filter-combo {
    flex-direction: column;
    gap: 1rem;
  }

  .filter-buttons {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .card-container {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
  }
  
  .card img {
    height: 180px;
  }
  
  /* Adjusted for mobile to ensure buttons don't overlap */
  .filter-btn.active {
    transform: translateY(-3px) scale(1.1);
  }
}

@media (max-width: 576px) {
  .search-combo {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .search-btn, .filter-btn {
    width: 100%;
    justify-content: center;
  }
  
  .filter-buttons {
    flex-direction: column;
    width: 100%;
    gap: 1.25rem; /* Increased for active button */
  }
  
  .card-container {
    grid-template-columns: 1fr;
  }
  
  /* Adjusted scale for vertical stacked buttons */
  .filter-btn.active {
    transform: translateY(-3px) scale(1.08);
  }
}

/* Animation Effects */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.card {
  animation: fadeIn 0.5s ease forwards;
  opacity: 0;
}

.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }
.card:nth-child(4) { animation-delay: 0.4s; }
.card:nth-child(5) { animation-delay: 0.5s; }
.card:nth-child(6) { animation-delay: 0.6s; }
.card:nth-child(7) { animation-delay: 0.7s; }
.card:nth-child(8) { animation-delay: 0.8s; }
.card:nth-child(9) { animation-delay: 0.9s; }
.card:nth-child(n+10) { animation-delay: 1s; }

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: var(--gray-100);
}

::-webkit-scrollbar-thumb {
  background: var(--gray-400);
  border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--gray-500);
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
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../user.php">Home</a></li>
                <li class="breadcrumb-item active">Health Champ</li>
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
            <li class="nav-item">
                <a class="nav-link" href="../shop/products.php">Shop</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top:-10px">
    <h2>Health Champs</h2>

    <!-- Filter Section -->
    <div class="filter-container" style="margin-top:-20px">
        <!-- Calendar Selection -->
        <div class="filter-row">
            <div class="row">
                <div class="col-md-6">
                    <div class="filter-calendar">
                        <select class="form-control" onchange="filterCalendar(this.value)">
                            <?php for ($i = 1; $i <= 65; $i++): ?>
                                <?php $calendarValue = 'calendar' . $i; ?>
                                <?php $selected = ($calendar_type === $calendarValue) ? 'selected' : ''; ?>
                                <option value="<?php echo $calendarValue; ?>" <?php echo $selected; ?>>Selecte Calendar <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-calendar">
                        <select class="form-control" onchange="filterWeekTitle(this.value)">
                            <option value="">All Week Titles</option>
                            <?php foreach ($weekTitles as $title): ?>
                                <?php $selected = ($search_week_title == $title) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($title); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    
    <!-- Second Row: Buttons -->
    <div class="button-row">
        <div class="filter-buttons">
            <button class="filter-btn prize-winner <?php echo ($winner_role == 'Prize Winner') ? 'active' : ''; ?>" onclick="filterWinners('Prize Winner')">
                <i class="fas fa-trophy"></i> Prize Winners
            </button>
            <button class="filter-btn nominate-winner <?php echo ($winner_role == 'Nominate Winner') ? 'active' : ''; ?>" onclick="filterWinners('Nominate Winner')">
                <i class="fas fa-star"></i> Nominate Winners
            </button>
        </div>
    </div>
</div>

    <div class="card-container" id="winnersList" style="margin-top:15px">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="card">
                <?php 
                $imagePath = "../Admin/uploads/" . htmlspecialchars($row['fb_post_image']);
                $imageId = "img_" . uniqid();
                if (file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="Winner Image" id="' . $imageId . '" onclick="openImageModal(this.src)">';
                } else {
                    echo '<p>Image not available.</p>';
                }
                ?>
                <center><h3><?php echo htmlspecialchars($row['winner_name']); ?></h3></center>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p style="margin: 0;"><strong>Points:</strong> <?php echo htmlspecialchars($row['winner_point']); ?></p>
                    <p class="date" style="margin: 0;"><?php echo date('M d, Y', strtotime($row['winner_date'])); ?></p>
                </div>
                <div style="margin-top: 10px;">
                    <center>
                    <p style="margin: 0; font-style: italic; color: #555;"><?php echo htmlspecialchars($row['week_title']); ?></p></center>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div id="imageModal" class="modal">
    <span class="close" onclick="closeImageModal()">&times;</span>
    <img class="modal-content" id="modalImg">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Close mobile menu when clicking a nav link
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        navLinks.forEach(function(navLink) {
            navLink.addEventListener('click', function() {
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            });
        });
    });

    // Image Modal Functions
    function openImageModal(imgSrc) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImg");
        modal.style.display = "block";
        modalImg.src = imgSrc;
        
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeImageModal();
            }
        });
    }
    
    function closeImageModal() {
        document.getElementById("imageModal").style.display = "none";
    }
    
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeImageModal();
        }
    });
    
    // Filter functions
    function filterCalendar(calendar) {
        let role = new URLSearchParams(window.location.search).get('role') || "Prize Winner";
        let week_title = new URLSearchParams(window.location.search).get('week_title') || "";
        let url = `winner_list.php?role=${encodeURIComponent(role)}&calendar=${encodeURIComponent(calendar)}`;
        if (week_title) url += `&week_title=${encodeURIComponent(week_title)}`;
        window.location.href = url;
    }

    function filterWeekTitle(week_title) {
        let role = new URLSearchParams(window.location.search).get('role') || "Prize Winner";
        let calendar = new URLSearchParams(window.location.search).get('calendar') || "calendar1";
        let url = `winner_list.php?role=${encodeURIComponent(role)}&calendar=${encodeURIComponent(calendar)}`;
        if (week_title) url += `&week_title=${encodeURIComponent(week_title)}`;
        window.location.href = url;
    }

    function filterWinners(role) {
        let calendar = new URLSearchParams(window.location.search).get('calendar') || "calendar1";
        let week_title = new URLSearchParams(window.location.search).get('week_title') || "";
        let url = `winner_list.php?role=${encodeURIComponent(role)}&calendar=${encodeURIComponent(calendar)}`;
        if (week_title) url += `&week_title=${encodeURIComponent(week_title)}`;
        window.location.href = url;
    }

    // Check if the page is being accessed via back navigation
    if (window.performance.navigation.type == window.performance.navigation.TYPE_BACK_FORWARD) {
        window.location.replace("../user.php");
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

<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>