<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate cart count
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyHealth Navigation</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

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
            <img src="../Admin/img/log.png" alt="Logo" style="width:100%;"> <!-- Add your logo image here -->
        </a>

        <div class="d-flex d-lg-none">
            <a class="nav-link" href=" ">
                <center> <i class="fa-solid fa-cart-shopping"></i><p>Cart</p></center>  <!-- FontAwesome shop icon -->
            </a>
            <a class="nav-link" href="../login.php">
                <center><i class="fa-solid fa-right-to-bracket" style="color:red;"></i><p style="color:red;">Log in</p></center>
            </a>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home </a></li>
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
           
           
        </ul>
    </div>
</nav>

</bogy>
</html>