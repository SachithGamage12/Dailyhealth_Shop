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
    $weekStart = $_POST['week_start'];
    $weekEnd = $_POST['week_end'];
    $question = $_POST['question'];

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO weekly_questions (calendar_type, week_start_date, week_end_date, question) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $calendarType, $weekStart, $weekEnd, $question);
    
    if ($stmt->execute()) {
        $msg = "Question added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all questions for display
$sql = "SELECT * FROM weekly_questions ORDER BY week_start_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question of the Week</title>
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
        <h2>Question of the Week</h2>
        <?php if ($msg): ?>
            <p class="message"><?php echo $msg; ?></p>
        <?php endif; ?>
        <form method="post">
            <label>Calendar Type:</label>
            <select name="calendar_type" required>
                <option value="">Select Calendar</option>
                <?php
                for ($i = 1; $i <= 65; $i++) {
                    echo "<option value='calendar$i'>Calendar $i</option>";
                }
                ?>
            </select>
            
            <label>Week Start Date:</label>
            <input type="date" name="week_start" required>
            
            <label>Week End Date:</label>
            <input type="date" name="week_end" required>
            
            <label>Question:</label>
            <textarea name="question" rows="4" required></textarea>
            
            <button type="submit">Submit</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <h3>Existing Questions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Calendar</th>
                        <th>Week Start</th>
                        <th>Week End</th>
                        <th>Question</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['calendar_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['week_start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['week_end_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['question']); ?></td>
                           
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; margin-top: 20px;">No questions added yet.</p>
        <?php endif; ?>
        
        <br><br>
        <center>
            <a href="questiom_table.php" class="btn" style="
                background-color: black;
                color: white;
                padding: 10px 15px;
                text-decoration: none;
            ">View All Weekly Questions</a>
        </center>
    </div>
</body>
</html>