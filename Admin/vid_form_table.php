<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

// Fetch videos from the database
$result = $conn->query("SELECT * FROM videos");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle edit or delete
    if (isset($_POST['edit'])) {
        $video_id = $_POST['edit'];
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);

        $conn->query("UPDATE videos SET title = '$title', description = '$description' WHERE id = $video_id");
        echo "<script>alert('Video updated successfully!'); window.location.href='vid_form_table.php';</script>";
    }

    if (isset($_POST['delete'])) {
        $video_id = $_POST['delete'];
        $result = $conn->query("SELECT video_path FROM videos WHERE id = $video_id");
        $video = $result->fetch_assoc();

        // Delete the video file from server
        if (file_exists($video['video_path'])) {
            unlink($video['video_path']);
        }

        // Delete the video from the database
        $conn->query("DELETE FROM videos WHERE id = $video_id");
        echo "<script>alert('Video deleted successfully!'); window.location.href='vid_form_table.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Video Upload Table</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">Uploaded Videos</h2>
    <a href="vid_form.php" class="btn" style="
    position: absolute;
    top: 35px;
    right: 25px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back </a>
    <!-- Video Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Video</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row['id'] . "</td>
                            <td>" . $row['title'] . "</td>
                            <td>" . $row['description'] . "</td>
                            <td><video width='200' controls><source src='" . $row['video_path'] . "' type='video/mp4'></video></td>
                            <td>
                                <button class='btn btn-warning' onclick='openEditModal(" . $row['id'] . ", \"" . $row['title'] . "\", \"" . $row['description'] . "\")'>Edit</button>
                                <form action='vid_form_table.php' method='POST' style='display:inline;'>
                                    <button type='submit' name='delete' value='" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this video?\")'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
            ?>
        </tbody>
    </table>

    <!-- Edit Video Modal -->
    <div id="editModal" class="modal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" id="video_id" name="edit">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <center>
                        <button type="submit" class="btn btn-primary">Save Changes</button></center>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open Edit Modal and Pre-fill Form
        function openEditModal(id, title, description) {
            document.getElementById('video_id').value = id;
            document.getElementById('title').value = title;
            document.getElementById('description').value = description;
            var myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }
    </script>


</body>
</html>
