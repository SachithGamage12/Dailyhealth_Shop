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

// Handle Form Submission for Adding/Editing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_winner'])) {
        // Adding or Updating Winner
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

        // Default image name (if file is not provided during edit)
        $file_name = '';
        if (isset($_FILES["fb_post_image"]) && $_FILES["fb_post_image"]["error"] == 0) {
            $file_name = basename($_FILES["fb_post_image"]["name"]);
            $target_file = $upload_dir . $file_name;
            move_uploaded_file($_FILES["fb_post_image"]["tmp_name"], $target_file);
        } else {
            // Use the old file name if updating and no new file is uploaded
            if (isset($_POST['winner_id']) && !empty($_POST['winner_id'])) {
                $winner_id = $_POST['winner_id'];
                $result = $conn->query("SELECT fb_post_image FROM winners WHERE id = $winner_id");
                $winner_data = $result->fetch_assoc();
                $file_name = $winner_data['fb_post_image'];
            }
        }

        if (isset($_POST['winner_id']) && !empty($_POST['winner_id'])) {
            // Edit Operation
            $winner_id = $_POST['winner_id'];
            $stmt = $conn->prepare("UPDATE winners SET winner_name = ?, fb_post_image = ?, winner_point = ?, winner_role = ?, winner_date = ?, calendar_type = ?, week_title = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $winner_name, $file_name, $winner_point, $winner_role, $winner_date, $calendar_type, $week_title, $winner_id);
            if ($stmt->execute()) {
                $message = "<p class='success'>Winner updated successfully!</p>";
            } else {
                $message = "<p class='error'>Error: " . $stmt->error . "</p>";
            }
        } else {
            // Add Operation
            $stmt = $conn->prepare("INSERT INTO winners (winner_name, fb_post_image, winner_point, winner_role, winner_date, calendar_type, week_title) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $winner_name, $file_name, $winner_point, $winner_role, $winner_date, $calendar_type, $week_title);
            if ($stmt->execute()) {
                $message = "<p class='success'>Winner added successfully!</p>";
            } else {
                $message = "<p class='error'>Error: " . $stmt->error . "</p>";
            }
        }

        $stmt->close();
    }
}

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $winner_id = $_GET['delete'];
    $result = $conn->query("SELECT fb_post_image FROM winners WHERE id = $winner_id");
    $winner_data = $result->fetch_assoc();
    // Delete the image file from the server if exists
    if (file_exists("uploads/" . $winner_data['fb_post_image'])) {
        unlink("uploads/" . $winner_data['fb_post_image']);
    }
    // Delete the winner from the database
    $conn->query("DELETE FROM winners WHERE id = $winner_id");
    header("Location: winner_table.php");  // Redirect after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manage Winners</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
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
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .btn {
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .edit-btn {
            background-color: #ff9800;
        }
        .edit-btn:hover {
            background-color: #e68900;
        }
        .delete-btn {
            background-color: #f44336;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        .view-btn {
            background-color: #2196F3;
        }
        .view-btn:hover {
            background-color: #0b7dda;
        }

        /* Popup Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
        }

        .modal-header {
            text-align: center;
            font-size: 24px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            float: right;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        .img-preview {
            max-width: 100px;
            max-height: 100px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Panel - Manage Winners</h2>
        <a href="winner_form.php" class="btn" style="
            position: absolute;
            top: 85px;
            right: 155px;
            background-color: black;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        ">Back</a>
        <?php if (isset($message)) echo $message; ?>

        <!-- Displaying Winners Table -->
        <h3>Current Winners:</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Calendar</th>
                    <th>Week Title</th>
                    <th>Role</th>
                    <th>Point</th>
                    <th>Date</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $result = $conn->query("SELECT * FROM winners ORDER BY winner_date DESC");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td style='text-align:center'>" . $row['id'] . "</td>
                                <td>" . $row['winner_name'] . "</td>
                                <td>" . $row['calendar_type'] . "</td>
                                <td>" . $row['week_title'] . "</td>
                                <td>" . $row['winner_role'] . "</td>
                                <td>" . $row['winner_point'] . "</td>
                                <td>" . $row['winner_date'] . "</td>
                                <td style='text-align:center'><img src='uploads/" . $row['fb_post_image'] . "' width='80' alt='Image'></td>
                                <td style='text-align:center'>
                                    <button class='btn edit-btn' onclick='openEditModal(" . json_encode($row) . ")'>Edit</button>
                                    <a href='winner_table.php?delete=" . $row['id'] . "' class='btn delete-btn' onclick='return confirm(\"Are you sure you want to delete?\");'>Delete</a>
                                </td>
                              </tr>";
                    }
                ?>
            </tbody>
        </table>

        <!-- Popup Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 class="modal-header">Edit Winner</h2>
                <form action="winner_table.php" method="post" enctype="multipart/form-data">
                    <label>Winner Name:</label>
                    <input type="text" id="winner_name" name="winner_name" required>

                    <label>Select Calendar:</label>
                    <select name="calendar_type" id="calendar_type" required>
                        <option value="">Select Calendar</option>
                        <?php
                        for ($i = 1; $i <= 65; $i++) {
                            echo "<option value='calendar$i'>Calendar $i</option>";
                        }
                        ?>
                    </select>

                    <label>Week Title:</label>
                    <select name="week_title" id="week_title" required>
                        <option value="">Select Week Title</option>
                        <?php
                        foreach ($titles as $title) {
                            echo "<option value='" . htmlspecialchars($title) . "'>" . htmlspecialchars($title) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Upload FB Post Image (600x315 recommended):</label>
                    <input type="file" name="fb_post_image">
                    <div id="imagePreview" class="img-preview"></div>
                    
                    <label>Winner Point:</label>
                    <input type="text" id="winner_point" name="winner_point" required>

                    <label>Winner Role:</label>
                    <select id="winner_role" name="winner_role" required>
                        <option value="Prize Winner">Prize Winner</option>
                        <option value="Nominate Winner">Nominate Winner</option>
                    </select>

                    <label>Date:</label>
                    <input type="date" id="winner_date" name="winner_date" required>

                    <input type="hidden" id="winner_id" name="winner_id">
                    <input type="hidden" id="current_image" name="current_image">

                    <button type="submit" name="add_winner">Update Winner</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Open Modal for Editing with all data
        function openEditModal(rowData) {
            document.getElementById("winner_id").value = rowData.id;
            document.getElementById("winner_name").value = rowData.winner_name;
            document.getElementById("calendar_type").value = rowData.calendar_type || '';
            document.getElementById("week_title").value = rowData.week_title || '';
            document.getElementById("winner_point").value = rowData.winner_point;
            document.getElementById("winner_role").value = rowData.winner_role;
            document.getElementById("winner_date").value = rowData.winner_date;
            document.getElementById("current_image").value = rowData.fb_post_image;
            
            // Show current image preview
            const imagePreview = document.getElementById("imagePreview");
            if (rowData.fb_post_image) {
                imagePreview.innerHTML = `<p>Current Image:</p><img src="uploads/${rowData.fb_post_image}" width="100" alt="Current Image">`;
            } else {
                imagePreview.innerHTML = '';
            }
            
            document.getElementById("myModal").style.display = "block";
        }

        // Close Modal
        var modal = document.getElementById("myModal");
        var span = document.getElementsByClassName("close")[0];

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        // Preview image before upload
        document.querySelector("input[name='fb_post_image']").addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imagePreview = document.getElementById("imagePreview");
                    imagePreview.innerHTML = `<p>New Image Preview:</p><img src="${e.target.result}" width="100" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>