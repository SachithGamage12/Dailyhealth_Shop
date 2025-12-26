<?php
// event_add.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    $conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine"); // Replace with your actual DB credentials

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $title = $_POST['title'];
    $description = $_POST['description'];
    $role = $_POST['role'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = $_POST['venue'];

    // First insert the event data (without images)
    $sql = "INSERT INTO events (title, description, role, date, time, venue) 
            VALUES ('$title', '$description', '$role', '$date', '$time', '$venue')";

    if ($conn->query($sql) === TRUE) {
        $event_id = $conn->insert_id;
        $upload_success = true;
        $uploaded_files = array();
        
        // Handle multiple image uploads
        if (!empty($_FILES['images']['name'][0])) {
            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $image_name = $_FILES['images']['name'][$key];
                $target = "uploads/" . basename($image_name);
                
                // Check if file already exists, if so, rename it
                $counter = 1;
                $file_info = pathinfo($target);
                while (file_exists($target)) {
                    $target = "uploads/" . $file_info['filename'] . "_" . $counter . "." . $file_info['extension'];
                    $counter++;
                }
                
                if (move_uploaded_file($tmp_name, $target)) {
                    // Insert image info into database (assuming you have an event_images table)
                    $image_sql = "INSERT INTO event_images (event_id, image_path) VALUES ('$event_id', '$target')";
                    if (!$conn->query($image_sql)) {
                        $upload_success = false;
                        error_log("Failed to insert image record: " . $conn->error);
                    } else {
                        $uploaded_files[] = $target;
                    }
                } else {
                    $upload_success = false;
                    error_log("Failed to upload image: " . $image_name);
                }
            }
        }
        
        if ($upload_success) {
            echo "<div class='success-message'>Event added successfully with " . count($uploaded_files) . " images!</div>";
        } else {
            echo "<div class='error-message'>Event added but some images failed to upload.</div>";
        }
    } else {
        echo "<div class='error-message'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-container {
            background-color: #fff;
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .form-container h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
        }
        input[type="text"],
        textarea,
        input[type="file"],
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 150px;
            resize: vertical;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview-item {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            cursor: pointer;
            font-size: 12px;
        }
        .image-preview-container {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add Event</h2>
        <a href="admin_panel.html" class="btn" style="
            position: absolute;
            top: 35px;
            right: 155px;
            background-color: black;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        ">Back to Admin</a>
       
        <form action="event_add.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="role">Event Role</label>
                <select id="role" name="role" required>
                    <option value="completed">Completed Project</option>
                    <option value="upcoming">Upcoming Project</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Event Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="time">Event Time</label>
                <input type="time" id="time" name="time" required>
            </div>
            <div class="form-group">
                <label for="venue">Event Venue</label>
                <input type="text" id="venue" name="venue" required>
            </div>
            <div class="form-group">
                <label for="images">Event Images (Multiple allowed)</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple required>
                <div class="image-preview" id="imagePreview"></div>
            </div>
            <button type="submit" class="btn-submit">Add Event</button>
            <br><br><br>
            <center>
                <a href="event_table.php" class="btn" style="
                    background-color: black;
                    color: white;
                    padding: 10px 15px;
                    text-decoration: none;
                ">View event</a>
            </center><br>
        </form>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(event) {
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            
            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) continue;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainerItem = document.createElement('div');
                    previewContainerItem.className = 'image-preview-container';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview-item';
                    
                    const removeBtn = document.createElement('span');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = function() {
                        previewContainerItem.remove();
                        // Remove the file from the input (this requires more complex handling)
                    };
                    
                    previewContainerItem.appendChild(img);
                    previewContainerItem.appendChild(removeBtn);
                    previewContainer.appendChild(previewContainerItem);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>