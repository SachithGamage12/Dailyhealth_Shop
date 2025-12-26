<?php
// Database connection details
$servername = "localhost";
$username = "u627928174_root";
$password = "Daily@365";
$dbname = "u627928174_daily_routine";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $target = "images/" . basename($image);

    // Upload image
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Insert product into database using prepared statements
        $sql = "INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssds", $name, $description, $price, $image);
            if ($stmt->execute()) {
                echo "Product added successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Failed to upload image.";
    }
}

// Fetch products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Display</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        h1 {
            color: #2d3436;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
            margin-top: 20px;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, #6c5ce7, #a29bfe);
        }

        .products-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin: 0 auto;
            padding: 0 15px;
            flex: 1;
        }

        .product {
            width: calc(33.333% - 10px);
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: left;
            cursor: pointer;
            position: relative;
            margin-bottom: 20px;
        }

        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .product img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #f1f1f1;
        }

        .product-content {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 180px);
        }

        .product h2 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: #2d3436;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product p.description {
            color: #636e72;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            height: 40px;
            overflow: hidden;
            font-size: 0.8rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-footer {
            margin-top: auto;
            text-align: center;
        }

        .price {
            font-weight: 700;
            font-size: 1.1rem;
            color: #6c5ce7;
            margin-bottom: 0.5rem;
        }

        button.cartButton {
            background: linear-gradient(to right, rgb(76, 30, 228), #a29bfe);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            max-width: 150px;
            z-index: 10;
            position: relative;
        }

        button.cartButton:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 19, 19, 0.5);
        }

        .no-products {
            display: inline-block;
            text-align: center;
            font-size: 1.2rem;
            color: #636e72;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .product-link {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 5;
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
        
        .navbar .container-fluid {
            padding: 0 15px;
        }
        
        /* Footer Styles */
        .footer {
            background-color: lightblue;
            padding-top: 60px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-top: auto;
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

        .footer-description {
            color: black;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

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

        .footer-title {
            font-size: 18px;
            font-weight: 600;
            color: black;
            margin-bottom: 20px;
        }

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
        @media (max-width: 1199px) {
            .product {
                width: calc(25% - 15px);
            }
        }

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
            
            .footer-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .services-links-wrapper {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
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
        }

        @media (max-width: 768px) {
            .product {
                width: calc(33.333% - 10px);
            }
            
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
            
            .get-started-button {
                display: block;
                width: 100%;
                margin: 0 auto 20px auto;
            }
        }

        @media (max-width: 576px) {
            .product {
                width: calc(50% - 10px);
            }
            
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
            
            .product img {
                height: 150px;
            }
            
            .product-content {
                padding: 0.8rem;
                height: calc(100% - 150px);
            }
            
            h1 {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }
            
            button.cartButton {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .copyright-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="#">
            <img src="img/log.png" alt="Logo" style="width:100%;"> <!-- Add your logo image here -->
        </a>

        <div class="d-flex d-lg-none">
            <a class="nav-link" href=" ">
                <center> <i class="fa-solid fa-cart-shopping"></i><p>Cart</p></center>  <!-- FontAwesome shop icon -->
            </a>
            <a class="nav-link" href="../index.php">
                <center><i class="fa-solid fa-right-to-bracket" style="color:red;"></i><p style="color:red;">Log Out</p></center>
            </a>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../user.php">Home </a></li>
                <li class="breadcrumb-item active">Shop </li>
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
                <a class="nav-link" href="shop_index.php">
                     Shop
                </a>
            </li>
        </ul>
    </div>
</nav>

<h1>Products</h1>

<div class="products-container">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="product">';
            echo '<a href="product_details.php?id=' . $row['id'] . '" class="product-link"></a>';
            echo '<img src="images/' . $row['image'] . '" alt="' . $row['name'] . '">';
            echo '<div class="product-content">';
            echo '<h2>' . $row['name'] . '</h2>';
            echo '<p class="description">' . $row['description'] . '</p>';
            echo '<div class="product-footer">';
            echo '<p class="price">Rs.' . $row['price'] . '</p>';
            echo '<button class="cartButton" onclick="window.location.href=\'../login.php\'"><i class="fas fa-cart-plus"></i> Buy Now</button>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-products">No products found.</div>';
    }
    $conn->close();
    ?>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
<script>
    function addToCart(productId, event) {
        event.preventDefault();
        event.stopPropagation();
        // Add your cart functionality here
        alert('Product ' + productId + ' added to cart!');
    }
</script>
</body>
</html>