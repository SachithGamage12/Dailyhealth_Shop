<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle Edit
    if (isset($_POST['edit'])) {
        $item_id = $_POST['edit'];
        $item_name = $_POST['item_name'];
        $item_type = $_POST['item_type'];

        // File upload handling
        if (isset($_FILES["item_file"]) && $_FILES["item_file"]["error"] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["item_file"]["name"]);
            move_uploaded_file($_FILES["item_file"]["tmp_name"], $target_file);

            // Update query
            $stmt = $conn->prepare("UPDATE downloads SET item_name = ?, item_type = ?, file_path = ? WHERE id = ?");
            $stmt->bind_param("sssi", $item_name, $item_type, $target_file, $item_id);
        } else {
            // Update query without file update
            $stmt = $conn->prepare("UPDATE downloads SET item_name = ?, item_type = ? WHERE id = ?");
            $stmt->bind_param("ssi", $item_name, $item_type, $item_id);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Item updated successfully!'); window.location.href='download_table.php';</script>";
        } else {
            echo "<script>alert('Error updating item: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }

    // Handle Delete
    if (isset($_POST['delete'])) {
        $item_id = $_POST['delete'];
        $result = $conn->query("SELECT file_path FROM downloads WHERE id = $item_id");
        $row = $result->fetch_assoc();

        // Delete file from the server
        if (file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }

        // Delete from the database
        $conn->query("DELETE FROM downloads WHERE id = $item_id");
        echo "<script>alert('Item deleted successfully!'); window.location.href='download_table.php';</script>";
    }
}

// Fetch all items from the database
$result = $conn->query("SELECT * FROM downloads");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Download Item Table</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            text-align: center;
        }
        .modal-header, .modal-body, .modal-footer {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Download Items</h2>
        <a href="add_download.php" class="btn" style="
    position: absolute;
    top: 65px;
    right: 185px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back </a>
        <!-- Download Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Item Type</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['item_name']; ?></td>
                        <td><?php echo $row['item_type']; ?></td>
                        <td><a href="<?php echo $row['file_path']; ?>" target="_blank">View File</a></td>
                        <td>
                            <button class="btn btn-warning" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo $row['item_name']; ?>', '<?php echo $row['item_type']; ?>', '<?php echo $row['file_path']; ?>')">Edit</button>
                            <form action="download_table.php" method="POST" style="display:inline;">
                                <button type="submit" name="delete" value="<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Download Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="item_id" name="edit">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <select class="form-control" id="item_type" name="item_type" required>
                                <option value="PDFs">PDFs</option>
                                <option value="Images">Images</option>
                                <option value="Posts">Posts</option>
                                <option value="Stickers">Stickers</option>
                                <option value="Wishing">Wishing</option>
                                <option value="Emojis">Emojis</option>
                                <option value="Videos">Videos</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="item_file">File</label>
                            <input type="file" class="form-control" id="item_file" name="item_file">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(id, name, type, filePath) {
            document.getElementById('item_id').value = id;
            document.getElementById('item_name').value = name;
            document.getElementById('item_type').value = type;
            var myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }
    </script>

</body>
</html>
