<?php
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");;
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $item_type = $_POST['item_type'];
    
    // File upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["item_file"]["name"]);
    move_uploaded_file($_FILES["item_file"]["tmp_name"], $target_file);

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO downloads (item_name, item_type, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $item_name, $item_type, $target_file);

    if ($stmt->execute()) {
        $message = "Download item added successfully!";
    } else {
        $message = "Error adding item: " . $conn->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Download Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        input, select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .message {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Download Item</h2>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="item_name" placeholder="Enter Item Name" required>
            <select name="item_type" required>
                <option value="">Select Item Type</option>
                <option value="PDFs">PDFs</option>
                <option value="Images">Images</option>
                <option value="Posts">Posts</option>
                <option value="Stickers">Stickers</option>
                <option value="Wishing">Wishing</option>
                <option value="Emojis">Emojis</option>
                <option value="Videos">Videos</option>
            </select>
            <input type="file" name="item_file" required>
            <center>
            <button type="submit">Add Item</button>
            </center>
            <br><br>
    <center>
        <a href="download_table.php" class="btn" style="
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
">View downloads</a></center>
        </form>
    </div>

    <a href="admin_panel.html" class="btn" style="
    position: absolute;
    top: 75px;
    right: 405px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
</body>
</html>
