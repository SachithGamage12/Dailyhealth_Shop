<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection (Update credentials)
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total admins
$result = $conn->query("SELECT COUNT(*) AS total_admins FROM admins");
$total_admins = ($result) ? $result->fetch_assoc()['total_admins'] : 0;

// Fetch total users
$result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = ($result) ? $result->fetch_assoc()['total_users'] : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
 <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #b3cce6, #E9EDF4);
        }
        
        .container {
            width: 250px;
            background-color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            padding: 10px;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }
        
        .sidebar-menu a {
            display: block;
            margin: 15px 0;
            padding: 10px;
            font-size: 16px;
            color: #666;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .sidebar-menu a:hover {
            background-color: #c6d9ec;
            color: #204060;
            font-weight: bold;
        }
        
        main {
            margin-left: 270px;
            width: calc(100% - 250px);
            display: flex;
            flex-direction: column;
            background-color: white;
        }
        
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background-color: white;
            position: fixed;
            width: 100%;
            z-index: 1001;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .logo-image {
            width: 50px;
            /* Adjust the size as needed */
            height: auto;
            margin-right: 10px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #204060;
            text-transform: uppercase;
        }
        
        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }
        
        .content-intro {
            padding: 20px;
            text-align: center;
            background: linear-gradient(135deg, #b3cce6, #E9EDF4);
        }
        
        .content-intro h2 {
            font-size: 2rem;
            color: #204060;
        }
        
        .grid {
            padding: 30px;
            background: linear-gradient(135deg, #b3cce6, #E9EDF4);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            /* Make sure the grid occupies full viewport height */
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* Two cards per row */
            gap: 20px;
            width: 80%;
            /* Optional: Adjust to control grid width */
        }
        
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .card img {
            width: 100px;
            margin-bottom: 10px;
        }
        
        .button a {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #E9EDF4, #b3cce6);
            color: #204060;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .button a:hover {
            background-color: #204060;
            color: white;
        }
        
        @media screen and (max-width: 768px) {
            .container {
                width: 250px;
                height: 100vh;
                position: fixed;
                left: -250px;
                /* Start hidden */
                top: 0;
                transition: left 0.3s ease-in-out;
            }
            .container.show {
                left: 0;
                /* Show the sidebar when toggled */
            }
            main {
                margin-left: 0;
                width: 100%;
            }
            .menu-toggle {
                display: block;
                font-size: 30px;
                cursor: pointer;
                margin-left: 15px;
            }
            .sidebar-menu {
                display: flex;
                flex-direction: column;
                padding: 10px;
            }
            .sidebar-menu a {
                font-size: 18px;
                margin: 10px 0;
            }
        }
        
        @media screen and (max-width: 480px) {
            .navbar {
                padding: 10px;
            }
            .logo-image {
                width: 40px;
            }
            .logo {
                font-size: 18px;
            }
            .grid-container {
                grid-template-columns: 1fr;
            }
            .card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <section class="grid">
        <div class="grid-container">

            <!-- Total Admins -->
            <div class="card">
                <p id="adminCount">Total Admins: <?php echo $total_admins; ?></p>
            </div>

            <!-- Total Users -->
            <div class="card">
                <p id="userCount">Total Users: <?php echo $total_users; ?></p>
            </div>

            <!-- Admin Details -->
            <div class="card">
                <img src="../Admin/img/Video.jpg" alt="Video Management">
                <div class="button">
                    <a href="adminuser_table.php">View Admin Details</a>
                </div>
            </div>

            <!-- User Details -->
            <div class="card">
                <img src="../Admin/img/Daily Msg.jpg" alt="Daily Message">
                <div class="button">
                    <a href="admin_user_table.php">View User Details</a>
                </div>
            </div>

            <!-- Add Admin -->
            <div class="card">
                <img src="../Admin/img/Download.jpg" alt="Download Management">
                <div class="button">
                    <a href="admin_register.php">Add an Admin</a>
                </div>
            </div>

        </div>
    </section>

    <!-- Back to Admin Panel -->
    <a href="admin_panel.html" class="btn" style="
        position: absolute;
        top: 35px;
        right: 35px;
        background-color: black;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 4px;">
        Back to Admin
    </a>

    <script>
        function highlightText(id) {
            let textElement = document.getElementById(id);
            setInterval(() => {
                textElement.style.color = textElement.style.color === 'red' ? 'black' : 'red';
            }, 3000);
        }
        highlightText("adminCount");
        highlightText("userCount");
    </script>

</body>
</html>

