<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";
$message_id = null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $target_dir = "uploads/";
    
    // Handle image upload
    $target_file = '';
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target_file = $target_dir . time() . '_' . basename($image);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    } elseif (!empty($_POST['current_image'])) {
        $target_file = $_POST['current_image'];
    }

    if (isset($_POST['message_id'])) {
        // Update existing message
        $message_id = $_POST['message_id'];
        $stmt = $conn->prepare("UPDATE daily_messages SET year=?, month=?, date=?, title=?, description=?, image_path=? WHERE id=?");
        $stmt->bind_param("iiisssi", $year, $month, $date, $title, $description, $target_file, $message_id);
        if ($stmt->execute()) {
            $msg = "Message updated successfully!";
        } else {
            $msg = "Error: " . $stmt->error;
        }
    } else {
        // Insert new message
        $stmt = $conn->prepare("INSERT INTO daily_messages (year, month, date, title, description, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $year, $month, $date, $title, $description, $target_file);
        if ($stmt->execute()) {
            $msg = "Message added successfully!";
        } else {
            $msg = "Error: " . $stmt->error;
        }
    }
}

// Handle message deletion
if (isset($_GET['delete'])) {
    $message_id = $_GET['delete'];
    $conn->begin_transaction();
    
    try {
        // Delete comments first
        $delete_comments = $conn->prepare("DELETE FROM `mesg-comments` WHERE message_id=?");
        $delete_comments->bind_param("i", $message_id);
        $delete_comments->execute();
        
        // Delete likes
        $delete_likes = $conn->prepare("DELETE FROM likes WHERE post_id=?");
        $delete_likes->bind_param("i", $message_id);
        $delete_likes->execute();
        
        // Delete message
        $delete_message = $conn->prepare("DELETE FROM daily_messages WHERE id=?");
        $delete_message->bind_param("i", $message_id);
        $delete_message->execute();
        
        $conn->commit();
        $msg = "Message deleted successfully.";
        header("Location: daily_messages_table.php?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "Error deleting message: " . $e->getMessage();
    }
}

// Fetch all messages
$messages = $conn->query("SELECT * FROM daily_messages ORDER BY year DESC, month DESC, date DESC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Message Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
        }
        .back-btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: #2980b9;
        }
        .message-alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 600;
            color: #2c3e50;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            margin-right: 5px;
            transition: all 0.2s;
        }
        .edit-btn {
            background-color: #f39c12;
            color: white;
        }
        .edit-btn:hover {
            background-color: #e67e22;
        }
        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .message-img {
            max-width: 80px;
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #eee;
        }
        .no-image {
            color: #7f8c8d;
            font-style: italic;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 25px;
            border-radius: 6px;
            width: 80%;
            max-width: 700px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        .submit-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background-color: #27ae60;
        }
        .image-preview {
            margin-top: 10px;
        }
        .image-preview img {
            max-width: 150px;
            max-height: 100px;
            border-radius: 4px;
            margin-top: 5px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            table {
                font-size: 13px;
            }
            th, td {
                padding: 8px 10px;
            }
            .modal-content {
                width: 90%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="daily_message_form.php" class="back-btn">Back</a>
        <h2>Daily Message Admin Panel</h2>

        <?php if (!empty($msg)): ?>
            <div class="message-alert <?php echo strpos($msg, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Year</th>
                    <th>Month</th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $messages->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td><?php echo $row['month']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 50) . (strlen($row['description']) > 50 ? '...' : ''))); ?></td>
                        <td>
                            <?php if (!empty($row['image_path'])): ?>
                                <img src="<?php echo $row['image_path']; ?>" alt="Message Image" class="message-img">
                            <?php else: ?>
                                <span class="no-image">No image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                <?php echo $row['id']; ?>, 
                                '<?php echo addslashes($row['title']); ?>', 
                                '<?php echo addslashes($row['description']); ?>', 
                                '<?php echo !empty($row['image_path']) ? $row['image_path'] : ''; ?>',
                                <?php echo $row['year']; ?>,
                                <?php echo $row['month']; ?>,
                                <?php echo $row['date']; ?>
                            )">Edit</button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Message</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="message_id" id="message_id">
                <input type="hidden" name="current_image" id="current_image">

                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="number" class="form-control" name="year" id="year" min="2000" max="2099" required>
                </div>

                <div class="form-group">
                    <label for="month">Month</label>
                    <select class="form-control" name="month" id="month" required>
                        <option value="">Select Month</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo date('F', mktime(0, 0, 0, $i, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="number" class="form-control" name="date" id="date" min="1" max="31" required>
                </div>

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" id="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" class="form-control" name="image" id="image">
                    <div class="image-preview">
                        <img id="existing_image" style="display: none;">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Update Message</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, title, description, image, year, month, date) {
            document.getElementById("editModal").style.display = "block";
            document.getElementById("message_id").value = id;
            document.getElementById("year").value = year;
            document.getElementById("month").value = month;
            document.getElementById("date").value = date;
            document.getElementById("title").value = title;
            document.getElementById("description").value = description;
            document.getElementById("current_image").value = image;
            
            const imgElement = document.getElementById("existing_image");
            if (image) {
                imgElement.src = image;
                imgElement.style.display = "block";
            } else {
                imgElement.style.display = "none";
            }
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById("editModal")) {
                closeModal();
            }
        }
    </script>
</body>
</html>