<?php

$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");;
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $holiday_title = $_POST['holiday_title'];
    $holiday_color = $_POST['holiday_color'];

    // Loop through calendars 1 to 65 and insert the holiday
    for ($i = 1; $i <= 65; $i++) {
        $calendar_type = "calendar" . $i;

        // Check if the holiday already exists for this calendar
        $checkSql = "SELECT * FROM holidays WHERE calendar_type = '$calendar_type' AND year = $year AND month = $month AND date = $date";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows == 0) {
            // Insert the holiday into the database
            $insertSql = "INSERT INTO holidays (calendar_type, year, month, date, holiday_title, holiday_color) 
                          VALUES ('$calendar_type', $year, $month, $date, '$holiday_title', '$holiday_color')";
            if (!$conn->query($insertSql)) {
                echo "Error: " . $insertSql . "<br>" . $conn->error;
            }
        } else {
            echo "Holiday already exists for $calendar_type on $year-$month-$date.<br>";
        }
    }

    echo "Holidays marked successfully for all calendars!";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Holidays</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container label {
            font-weight: bold;
        }
        .form-container input[type="color"] {
            width: 100%;
            height: 40px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Mark Holidays for Calendars 1 to 65</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="year" class="form-label">Year:</label>
                <input type="number" id="year" name="year" class="form-control" required min="2000" max="2100">
            </div>
            <div class="mb-3">
                <label for="month" class="form-label">Month:</label>
                <select id="month" name="month" class="form-control" required>
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
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date:</label>
                <input type="number" id="date" name="date" class="form-control" required min="1" max="31">
            </div>
            <div class="mb-3">
                <label for="holiday_title" class="form-label">Holiday Title:</label>
                <input type="text" id="holiday_title" name="holiday_title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="holiday_color" class="form-label">Holiday Mark Color:</label>
                <input type="color" id="holiday_color" name="holiday_color" class="form-control" value="#FF0000">
            </div>
            <button type="submit" class="btn btn-primary">Mark Holidays</button>
        </form>
        <br><button onclick="window.location.href='holidaytable.php'" class="btn btn-primary">
    Go to Holiday Table
</button>
    </div>
      <a href="admin_panel.html" class="btn" style="
    position: absolute;
    top: 115px;
    right: 495px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
    
</body>
</html>
