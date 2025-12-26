<?php
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct titles from daily_messages for the dropdown
$titles_query = $conn->query("SELECT DISTINCT title FROM daily_messages");
$titles = [];
while ($row = $titles_query->fetch_assoc()) {
    $titles[] = $row['title'];
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $winner_name = $_POST['winner_name'];
    $winner_point = $_POST['winner_point'];
    $winner_role = $_POST['winner_role'];
    $winner_date = $_POST['winner_date'];
    $calendar_type = $_POST['calendar_type'];
    $week_title = $_POST['week_title'];

    // Handle File Upload
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES["fb_post_image"]["name"]);
    $target_file = $upload_dir . $file_name;
    move_uploaded_file($_FILES["fb_post_image"]["tmp_name"], $target_file);

    // Insert into Database
    $stmt = $conn->prepare("INSERT INTO winners (calendar_type, winner_name, fb_post_image, winner_point, winner_role, winner_date, week_title) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $calendar_type, $winner_name, $file_name, $winner_point, $winner_role, $winner_date, $week_title);    
    
    if ($stmt->execute()) {
        $message = "<p class='success'>Winner added successfully!</p>";
    } else {
        $message = "<p class='error'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winner Details Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            border: none;
            padding: 10px;
        }
        button:hover {
            background-color: #218838;
        }
        .success {
            color: green;
            text-align: center;
            font-weight: bold;
        }
        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Winner Details Form</h2>
        <?php if (isset($message)) echo $message; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Select Calendar:</label>
            <select name="calendar_type" required>
                <option value="">Select Calendar</option>
                <?php
                for ($i = 1; $i <= 65; $i++) {
                    echo "<option value='calendar$i'>Calendar $i</option>";
                }
                ?>
            </select>

            <label>Week Title:</label>
            <select name="week_title" required>
                <option value="">Select Week Title</option>
                <?php
                foreach ($titles as $title) {
                    echo "<option value='" . htmlspecialchars($title) . "'>" . htmlspecialchars($title) . "</option>";
                }
                ?>
            </select>

            <label>Winner Name:</label>
            <input type="text" name="winner_name" required>

            <label>Upload FB Post Image (600x315 recommended):</label>
            <input type="file" name="fb_post_image" required>
            
            <label>Winner Point:</label>
            <input type="text" name="winner_point" required>

            <label>Winner Role:</label>
            <select name="winner_role" required>
                <option value="Prize Winner">Prize Winner</option>
                <option value="Nominate Winner">Nominate Winner</option>
            </select>

            <label>Date:</label>
            <input type="date" name="winner_date" required>

            <button type="submit">Submit</button>
        </form>
        <br><br>
        <center>
            <a href="winner_table.php" class="btn" style="
                background-color: black;
                color: white;
                padding: 10px 15px;
                text-decoration: none;
            ">View Winner's</a>
        </center>

        <br>
        <a href="admin_panel.html" class="btn" style="
            position: absolute;
            top: 85px;
            right: 385px;
            background-color: black;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        ">Back to Admin</a>
    </div>
</body>
</html>