<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="logo.jpeg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <style>
        .body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            font-family: 'Arial', sans-serif;
        }
        .container {
            width: 90%;
            max-width: 400px;
            padding: 15px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-top: 80px; /* Added to prevent overlap with fixed elements */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h3 {
            color: #333;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 8px;
            padding: 8px;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }

        .btn-success {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 10px;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-link {
            text-align: center;
            display: block;
            font-size: 14px;
        }

        .btn-link:hover {
            color: #6a11cb;
        }

        .form-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .alert {
            margin-top: 10px;
            display: none;
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
        
 .whatsapp-support-inline {
            margin-top: 15px;
            text-align: center;
        }
        
        .whatsapp-toggle-inline {
            color: #0066cc;
            text-decoration: underline;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .whatsapp-content-inline {
            display: none;
            animation: fadeIn 0.3s;
            margin-top: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .whatsapp-icon-inline {
            color: #25D366;
            font-size: 30px;
            margin-bottom: 5px;
            text-align: center;
        }
        
        .whatsapp-btn-inline {
            display: inline-block;
            background-color: #25D366;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12px;
            width: 100%;
            text-align: center;
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

        <div class="d-flex d-lg-none" style="gap: 10px;">
            <a class="nav-link" href="../shop/product.php">
                <p style="
                    border: 2px solid white;
                    display: inline-block;
                    padding: 6px;
                    border-radius: 20px;
                    margin: -10px;
            color: #0E47B4 !important; <!-- Force black -->
                    margin-right: 10px;
                ">Shop</p>
            </a>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"
    >
    <span class="navbar-toggler-icon" style="
        background-image: url('data:image/svg+xml;charset=utf8,<svg viewBox=\'0 0 30 30\' xmlns=\'http://www.w3.org/2000/svg\'><path stroke=\'%230E47B4\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' d=\'M4 7h22M4 15h22M4 23h22\'/></svg>');
        width: 1.5em;
        height: 1.5em;
    "></span>
</button>
        
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">Login</li>
            </ol>
        </div>
    </div>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link active" href="#">
                    Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php/#Daily_Messages">Day's Thought</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php/#winners">Health Champs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php/#videos">Health Talks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php/#downloads">Downloads</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php/#events">Events</a>
            </li>
        </ul>
    </div>
</nav>

<!-- WhatsApp Support Button - Fixed at top left -->


<div class="container" style="margin-top:2px">
    <h3 class="text-center">Login</h3>
    <form id="loginForm">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="username" class="form-control" name="username" id="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <button type="submit" class="btn btn-success mt-3"         style="background-color: #FF7C2A; border: none; color: white; font-weight: bold;">Login</button>
    </form>
    <br>
   <div class="d-flex justify-content-between mt-3">
    <a href="forgot_password.php" class="btn-link">Forgot Password?</a>
    <a href="register.php" class="btn-link">Not registered? Register here</a>
</div>
     <!-- WhatsApp Support Section - Placed after Forgot Password -->
    <div class="whatsapp-support-inline">
        <div class="whatsapp-toggle-inline" onclick="toggleWhatsAppSupportInline()">
             Do you forgot your username ?
        </div>
        
        <div id="whatsappContentInline" class="whatsapp-content-inline">
            <div class="whatsapp-icon-inline">
                <i class="fab fa-whatsapp"></i>
            </div>
            <p style="color: #666; font-size: 12px; margin-bottom: 10px; text-align: center;">Contact our support team via WhatsApp for immediate assistance.</p>
            <a href="https://wa.me/94777867942" class="whatsapp-btn-inline">
                WhatsApp Support
            </a>
        </div>
    </div>

    <!-- Error alert -->
    <div id="alertBox" class="alert alert-danger" role="alert">
        Invalid credentials or something went wrong. Please try again.
    </div>
</div>

<script>
$(document).ready(function () {
    $("#loginForm").submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        let username = $("#username").val().trim();
        let password = $("#password").val().trim();

        if (username === "" || password === "") {
            alert("Please fill in all fields.");
            return;
        }

        $.ajax({
            type: "POST",
            url: "login_process.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    $("#alertBox").show();
                }
            },
            error: function () {
                $("#alertBox").show();
            }
        });
    });
});

function toggleWhatsAppSupportInline() {
    const content = document.getElementById('whatsappContentInline');
    if (content.style.display === 'block') {
        content.style.display = 'none';
    } else {
        content.style.display = 'block';
    }
}
</script>


<!-- Font Awesome for WhatsApp icon -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
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
    background-color: #FF7C2A;
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
    <a href="#" class="get-started-button">Get Started</a>
    
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