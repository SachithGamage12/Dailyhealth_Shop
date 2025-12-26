<?php
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $specialday_title = $_POST['specialday_title'];
    $description = $_POST['description'];
    $special_message = $_POST['special_message'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['specialday_image']) && $_FILES['specialday_image']['error'] == 0) {
        $target_dir = "uploads/specialdays/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["specialday_image"]["name"], PATHINFO_EXTENSION);
        $target_file = $target_dir . uniqid() . '.' . $file_extension;
        
        if (move_uploaded_file($_FILES["specialday_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Loop through calendars 1 to 65 and insert the special day
    for ($i = 1; $i <= 65; $i++) {
        $calendar_type = "calendar" . $i;

        // Check if the special day already exists for this calendar
        $checkSql = "SELECT * FROM specialdays WHERE calendar_type = '$calendar_type' AND year = $year AND month = $month AND date = $date";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows == 0) {
            // Insert the special day into the database
            $insertSql = "INSERT INTO specialdays (calendar_type, year, month, date, specialday_title, description, special_message, image_path) 
                          VALUES ('$calendar_type', $year, $month, $date, '$specialday_title', '$description', '$special_message', '$image_path')";
            if (!$conn->query($insertSql)) {
                echo "Error: " . $insertSql . "<br>" . $conn->error;
            }
        } else {
            echo "Special day already exists for $calendar_type on $year-$month-$date.<br>";
        }
    }

    echo "Special days marked successfully for all calendars!";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Special Days</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Mark Special Days for Calendars 1 to 65</h2>
        <form method="POST" enctype="multipart/form-data">
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
                <label for="specialday_title" class="form-label">Special Day Title:</label>
                <input type="text" id="specialday_title" name="specialday_title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="special_message" class="form-label">Special Message:</label>
                <textarea id="special_message" name="special_message" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="specialday_image" class="form-label">Special Day Image:</label>
                <input type="file" id="specialday_image" name="specialday_image" class="form-control" accept="image/*">
                <img id="imagePreview" class="preview-image" src="#" alt="Image Preview">
            </div>
            <button type="submit" class="btn btn-primary">Mark Special Days</button>
        </form>
        <br>
        <button onclick="window.location.href='specialdaytable.php'" class="btn btn-primary">
            Go to Special Days Table
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
    
    <script>
        // Image preview functionality
        document.getElementById('specialday_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>