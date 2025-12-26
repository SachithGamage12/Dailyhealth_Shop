<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);

    if (isset($_FILES["video"]) && $_FILES["video"]["error"] == 0) {
        $video_name = time() . "_" . $_FILES["video"]["name"]; // Unique file name
        $upload_directory = "uploads/"; // Folder where videos are stored
        $video_path = $upload_directory . $video_name;

        // Move uploaded file
        move_uploaded_file($_FILES["video"]["tmp_name"], $video_path);

        // Save only the file name (not "uploads/") in the database
        $conn->query("INSERT INTO videos (title, description, video_path) VALUES ('$title', '$description', '$video_name')");

        echo "<script>alert('Video uploaded successfully!'); window.location.href='vid_form.php';</script>";
    } else {
        echo "<script>alert('Error uploading video.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Upload Video</title>
    <a href="admin_panel.html" class="btn" style="
    position: absolute;
    top: 35px;
    right: 25px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 600px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    color: #333;
}

.form-label {
    font-weight: bold;
    color: #333;
}

.form-control {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #0056b3;
}

a.btn {
    display: inline-block;
    padding: 10px 15px;
    background-color: black;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    text-align: center;
}

a.btn:hover {
    background-color: #333;
}

</style>
<body class="container mt-5">
    <h2 class="mb-4">Upload Video</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Select Video</label>
            <input type="file" name="video" class="form-control" accept="video/*" required>
        </div>
        <center>
        <button type="submit" class="btn btn-primary">Upload</button></center>
    </form>
    <br>
 <center>
        <a href="vid_form_table.php" class="btn" style="
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
">View upload videoes</a></center>

        <br>
     
</body>
</html>
