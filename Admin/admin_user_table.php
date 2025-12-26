<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all users and their additional details
$sql = "SELECT u.*, uad.* 
        FROM users u 
        LEFT JOIN user_additional_details uad ON u.id = uad.user_id";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .back-button {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            z-index: 1000;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .table-container {
            margin-top: 70px;
        }
    </style>
</head>
<body>
<a href="userdashboard.php" class="btn" style="
    position: absolute;
    top: 35px;
    right: 35px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back </a>

<div class="container table-container">
    <h2 class="text-center">User Details</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Marital Status</th>
                    <th>Area</th>
                    <th>Occupation</th>
                    <th>WhatsApp</th>
                    <th>Email</th>
                    <th>Permanent Address</th>
                    <th>Postal Address</th>
                    <th>Nationality</th>
                    <th>Race</th>
                    <th>Religion</th>
                    <th>Earning/Dependant</th>
                    <th>Monthly Income</th>
                    <th>Highest Education</th>
                    <th>Currently Living With</th>
                    <th>Performing Activities</th>
                    <th>Hobbies</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['dob']); ?></td>
                        <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        <td><?php echo htmlspecialchars($user['marital_status']); ?></td>
                        <td><?php echo htmlspecialchars($user['area']); ?></td>
                        <td><?php echo htmlspecialchars($user['occupation']); ?></td>
                        <td><?php echo htmlspecialchars($user['whatsapp']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['permanent_address'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['postal_address'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['nationality'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['race'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['religion'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['earning_or_dependant'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['monthly_income'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['highest_education'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['currently_living_with'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['performing_activities'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($user['hobbies'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>