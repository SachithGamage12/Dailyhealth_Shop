<?php
$servername = "localhost";
$username = "u627928174_root"; // Database username
$password = "Daily@365"; // Database password
$dbname = "u627928174_daily_routine"; // Database name


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter type from URL (default to all)
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';

// SQL Query
$sql = "SELECT * FROM downloads";
if ($filter_type && $filter_type != 'All') {
    $sql .= " WHERE item_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter_type);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Items</title>
        <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            background: white;
            padding: 20px;
        }
        select {
            padding: 10px;
            width: 220px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
            width: 45%;
            margin: 10px;
            padding: 15px;
            display: inline-block;
            text-align: left;
            vertical-align: top;
        }
        .card img, .card video, .card iframe {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .download-btn {
            padding: 10px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .card {
                width: 100%;
                margin: 10px 0;
            }
            select {
                width: 100%;
            }
        }
      .back-button {
    position: absolute;
    top: 15px;
    left: 15px;
    background-color: #333;
    color: white;
    padding: 10px 18px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    font-size: 16px;
    transition: background 0.3s ease, transform 0.2s ease;
    display: inline-block;
}

.back-button:hover {
    background-color: #555;
    transform: scale(1.05);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .back-button {
        top: 10px;
        left: 10px;
        padding: 8px 15px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .back-button {
        top: 8px;
        left: 8px;
        padding: 6px 12px;
        font-size: 12px;
    }
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
                <li class="breadcrumb-item active">Have It For You </li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <div class="container" style="margin-top: -15px;">
        <h2>Download Items</h2>
<!-- Add this at the top of your PHP file -->
<?php
// Get filter type from URL or default to 'All'
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'All';
?>

<!-- Dropdown Filter -->
<select onchange="filterItems(this.value)" class="filter-dropdown">
    <option value="All" <?php echo $filter_type == 'All' ? 'selected' : ''; ?>>Show All</option>
    <option value="PDFs" <?php echo $filter_type == 'PDFs' ? 'selected' : ''; ?>>PDFs</option>
    <option value="Images" <?php echo $filter_type == 'Images' ? 'selected' : ''; ?>>Images</option>
    <option value="Posts" <?php echo $filter_type == 'Posts' ? 'selected' : ''; ?>>Posts</option>
    <option value="Stickers" <?php echo $filter_type == 'Stickers' ? 'selected' : ''; ?>>Stickers</option>
    <option value="Wishing" <?php echo $filter_type == 'Wishing' ? 'selected' : ''; ?>>Wishing</option>
    <option value="Emojis" <?php echo $filter_type == 'Emojis' ? 'selected' : ''; ?>>Emojis</option>
    <option value="Videos" <?php echo $filter_type == 'Videos' ? 'selected' : ''; ?>>Videos</option>
</select>

<!-- Add this JavaScript -->
<script>
function filterItems(type) {
    // Construct new URL with filter parameter
    const url = new URL(window.location.href);
    url.searchParams.set('type', type);
    window.location.href = url.toString();
}
</script>

<!-- Add some basic styling -->
<style>
.filter-dropdown {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    font-size: 16px;
}
</style>
        <div class="row">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="card" 
             data-name="<?php echo htmlspecialchars($row['item_name']); ?>"
             data-type="<?php echo htmlspecialchars($row['item_type']); ?>"
             data-path="<?php echo htmlspecialchars($row['file_path']); ?>">
            <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
            
            <div class="card-preview">
                <?php
                if ($row['item_type'] == 'Videos') {
                    echo '<video><source src="' . htmlspecialchars($row['file_path']) . '" type="video/mp4"></video>';
                } elseif ($row['item_type'] == 'PDFs') {
                    echo '<iframe src="' . htmlspecialchars($row['file_path']) . '"></iframe>';
                } elseif ($row['item_type'] == 'Images' || $row['item_type'] == 'Posts' || $row['item_type'] == 'Stickers' || $row['item_type'] == 'Emojis') {
                    echo '<img src="' . htmlspecialchars($row['file_path']) . '" alt="Preview">';
                } else {
                    echo '<p>Preview not available</p>';
                }
                ?>
            </div>
            <div class="card-footer">
    <span class="item-type"><?php echo htmlspecialchars($row['item_type']); ?></span>
    <a href="<?php echo htmlspecialchars($row['file_path']); ?>" download class="download-btn">Download</a>
</div>

<style>
.card-footer {
    display: flex;
    justify-content: space-between; /* This pushes items to opposite sides */
    align-items: center; /* Vertically centers the items */
    padding: 10px 15px;
    border-top: 1px solid #eee;
    background: #f9f9f9;
}

.item-type {
    font-size: 14px;
    color: #666;
    /* Optional: if you want some spacing */
    margin-right: 10px;
}

.download-btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #FF7C2A;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
    /* Optional: if you want the button to stay right-aligned */
    margin-left: auto;
}

.download-btn:hover {
    background-color: #45a049;
}
</style>
        </div>
    <?php } ?>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalItemTitle"></h2>
        <div id="modalItemPreview"></div>
        <div class="modal-details">
            <p><strong>Type:</strong> <span id="modalItemType"></span></p>
            <a id="modalDownloadLink" href="#" download class="download-btn">Download Now</a>
        </div>
    </div>
</div>

<style>

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 25px;
    width: 80%;
    max-width: 700px;
    border-radius: 8px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
}
.close {
    position: absolute;
    top: 10px;
    right: 15px;
    color: red;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #333;
}

#modalItemPreview {
    height: 400px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border-radius: 4px;
    overflow: hidden;
}

#modalItemPreview video,
#modalItemPreview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

#modalItemPreview iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.modal-details {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

/* Download Button */
.download-btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.download-btn:hover {
    background-color: #45a049;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('detailModal');
    const closeBtn = document.querySelector('.close');
    
    // Make entire card clickable
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't open modal if clicking on download button
            if (e.target.classList.contains('download-btn')) {
                return;
            }
            
            const name = this.dataset.name;
            const type = this.dataset.type;
            const path = this.dataset.path;
            
            document.getElementById('modalItemTitle').textContent = name;
            document.getElementById('modalItemType').textContent = type;
            document.getElementById('modalDownloadLink').href = path;
            
            const preview = document.getElementById('modalItemPreview');
            
            if (type === 'Videos') {
                preview.innerHTML = `<video controls autoplay><source src="${path}" type="video/mp4"></video>`;
            } else if (type === 'PDFs') {
                preview.innerHTML = `<iframe src="${path}"></iframe>`;
            } else if (['Images', 'Posts', 'Stickers', 'Emojis'].includes(type)) {
                preview.innerHTML = `<img src="${path}" alt="${name}">`;
            } else {
                preview.innerHTML = '<p>Preview not available</p>';
            }
            
            modal.style.display = "block";
        });
    });
    
    // Close modal
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    
    // Filter function
    function filterItems(type) {
        window.location.href = "download_list.php?type=" + encodeURIComponent(type);
    }
    
    // Back/forward navigation handling
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        window.location.replace("../user.php");
    }
});
</script>
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
