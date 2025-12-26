<?php
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete Admin
if (isset($_GET['delete_admin'])) {
    $admin_id = $_GET['delete_admin'];
    $delete_admin_query = "DELETE FROM admins WHERE id = ?";
    $stmt = $conn->prepare($delete_admin_query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete Admin Login History
if (isset($_GET['delete_history'])) {
    $history_id = $_GET['delete_history'];
    $delete_history_query = "DELETE FROM admin_login_history WHERE id = ?";
    $stmt = $conn->prepare($delete_history_query);
    $stmt->bind_param("i", $history_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query_admins = "SELECT id, username, email, created_at FROM admins";
$result_admins = $conn->query($query_admins);

$query_history = "SELECT id, admin_id, login_time, logout_time FROM admin_login_history";
$result_history = $conn->query($query_history);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Table</title>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .table-container {
            margin-bottom: 40px;
        }
        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">All Registered Admins</h2>
    
    <!-- Admin Table -->
    <div class="table-container">
        <center><h3>Admin Details</h3></center>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registration Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_admins->num_rows > 0) {
                    while ($row = $result_admins->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["username"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . $row["created_at"] . "</td>";
                        echo "<td><a href='?delete_admin=" . $row["id"] . "' class='delete-btn'>Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No admins found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Admin Login History Table -->
    <div class="table-container">
        <center><h3>Admin Login History</h3></center>
        
        <table>
            <thead>
                <tr>
                    <th>Admin ID</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_history->num_rows > 0) {
                    while ($row = $result_history->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["admin_id"] . "</td>";
                        echo "<td>" . $row["login_time"] . "</td>";
                        echo "<td>" . $row["logout_time"] . "</td>";
                        echo "<td><a href='?delete_history=" . $row["id"] . "' class='delete-btn'>Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No login history found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <a href="userdashboard.php" class="btn" style="
    position: absolute;
    top: 15px;
    right: 35px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
</body>
</html>

<?php
$conn->close();
?>
