<?php
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Initialize message variable
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calendarType = $_POST['calendar_type'];
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Handle image upload
    $imagePath = "";
    if (isset($_FILES['image'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageName = basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            $msg = "Error uploading image.";
        }
    }

    // Insert data into the database
    if (!empty($imagePath)) {
        $stmt = $conn->prepare("INSERT INTO daily_messages (calendar_type, year, month, date, title, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiisss", $calendarType, $year, $month, $date, $title, $description, $imagePath);
        if ($stmt->execute()) {
            $msg = "Message added successfully!";
        } else {
            $msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all messages for display
$sql = "SELECT * FROM daily_messages ORDER BY year DESC, month DESC, date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Message Form</title>
    <a href="admin_panel.html" class="btn" style="
    position: absolute;
    top: 15px;
    right: 155px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 50%; margin: auto; padding: 20px; background: white; box-shadow: 0px 0px 10px gray; border-radius: 8px; }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; }
        label { margin-top: 10px; font-weight: bold; }
        select, input, textarea { padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 15px; padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #218838; }
        .message { color: green; text-align: center; margin-bottom: 20px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .btn:hover { background-color: #0056b3; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daily Message Form</h2>
        <?php if ($msg): ?>
            <p class="message"><?php echo $msg; ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label>Calendar Type:</label>
            <select name="calendar_type" required>
                <option value="">Select Calendar</option>
                <?php
                for ($i = 1; $i <= 65; $i++) {
                    echo "<option value='calendar$i'>Calendar $i</option>";
                }
                ?>
            </select>
            
            <label>Year:</label>
            <input type="number" name="year" required>
            
            <label>Month:</label>
            <select name="month" required>
                <option value="">Select Month</option>
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            
            <label>Date:</label>
            <input type="number" name="date" required>
            
            <label>Title:</label>
            <input type="text" name="title" required>
            
            <label>Description:</label>
            <textarea name="description" required></textarea>
            
            <label>Image:</label>
            <input type="file" name="image" required>
            
            <button type="submit">Submit</button>
        </form>

       

            <br><br>
            <center>
        <a href="daily_messages_table.php" class="btn" style="
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
">View Daily Messages</a></center>
        </form>


        
    </div>
</body>
</html>
