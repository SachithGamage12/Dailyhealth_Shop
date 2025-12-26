<?php
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = $_GET['delete'];
    
    // First delete associated images
    $delete_images = "DELETE FROM event_images WHERE event_id = ?";
    $stmt = $conn->prepare($delete_images);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
    
    // Then delete the event
    $delete_event = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($delete_event);
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        $success_message = "Event and all associated images deleted successfully!";
    } else {
        $error_message = "Error deleting event.";
    }
    $stmt->close();
}

// Handle event addition or update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $role = $_POST['role'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = $_POST['venue'];

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($event_id) {
            // Update existing event
            $update_query = "UPDATE events SET title = ?, description = ?, role = ?, date = ?, time = ?, venue = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssssi", $title, $description, $role, $date, $time, $venue, $event_id);
            $stmt->execute();
            $stmt->close();
            
            // Handle image uploads for existing event
            if (!empty($_FILES['images']['name'][0])) {
                // Create uploads directory if it doesn't exist
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                $img_stmt = $conn->prepare("INSERT INTO event_images (event_id, image_path) VALUES (?, ?)");
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $original_name = basename($_FILES['images']['name'][$key]);
                        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
                        $new_filename = uniqid('event_', true) . '.' . $file_extension;
                        $target_path = "uploads/" . $new_filename;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $img_stmt->bind_param("is", $event_id, $target_path);
                            $img_stmt->execute();
                        }
                    }
                }
                $img_stmt->close();
            }
            
            $success_message = "Event updated successfully!";
        } else {
            // Insert new event
            $insert_query = "INSERT INTO events (title, description, role, date, time, venue) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssssss", $title, $description, $role, $date, $time, $venue);
            $stmt->execute();
            $event_id = $conn->insert_id;
            $stmt->close();
            
            // Handle image uploads for new event
            if (!empty($_FILES['images']['name'][0])) {
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                $img_stmt = $conn->prepare("INSERT INTO event_images (event_id, image_path) VALUES (?, ?)");
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $original_name = basename($_FILES['images']['name'][$key]);
                        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
                        $new_filename = uniqid('event_', true) . '.' . $file_extension;
                        $target_path = "uploads/" . $new_filename;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $img_stmt->bind_param("is", $event_id, $target_path);
                            $img_stmt->execute();
                        }
                    }
                }
                $img_stmt->close();
            }
            
            $success_message = "Event added successfully!";
        }
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch events with their primary images for table display
$query = "SELECT e.*, 
          (SELECT image_path FROM event_images WHERE event_id = e.id LIMIT 1) as primary_image
          FROM events e
          ORDER BY e.date DESC, e.time DESC";
$result = $conn->query($query);

// Check for event fetch via AJAX
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $query = "SELECT e.*, 
              GROUP_CONCAT(ei.image_path) as all_images
              FROM events e
              LEFT JOIN event_images ei ON e.id = ei.event_id
              WHERE e.id = ?
              GROUP BY e.id";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    
    // Convert comma-separated images to array
    if ($event['all_images']) {
        $event['all_images'] = explode(',', $event['all_images']);
    } else {
        $event['all_images'] = [];
    }
    
    echo json_encode($event);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .action-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin: 2px;
            border-radius: 4px;
        }

        .action-btn:hover {
            background-color: #45a049;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background-color: #e53935;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            max-width: 700px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 0;
            right: 10px;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .message {
            font-weight: bold;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #4CAF50;
            color: white;
        }

        .error {
            background-color: #f44336;
            color: white;
        }

        .event-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
            object-fit: cover;
        }

        .image-preview-container {
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

        .image-preview-wrapper {
            position: relative;
            display: inline-block;
        }

        .remove-image-preview {
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

        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-gallery img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .view-images-btn {
            background-color: #2196F3;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 5px;
        }

        .view-images-btn:hover {
            background-color: #0b7dda;
        }

        .images-modal {
            display: none;
            position: fixed;
            z-index: 2;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            padding-top: 60px;
        }

        .images-modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
        }

        .images-modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .images-modal-close:hover {
            color: black;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Event Table</h2>
    <a href="event_add.php" class="btn" style="
        position: absolute;
        top: 35px;
        right: 155px;
        background-color: black;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 4px;
    ">Back to Admin</a>
    
    <?php if (isset($success_message)): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Primary Image</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['time']) ?></td>
                        <td><?= htmlspecialchars($row['venue']) ?></td>
                        <td>
                            <?php if (!empty($row['primary_image'])): ?>
                                <img src="<?= htmlspecialchars($row['primary_image']) ?>" class="event-image" alt="Event Image">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <button class="action-btn" onclick="openModal(<?= $row['id'] ?>)">Edit</button>
                            <a href="?delete=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this event and all its images?')">Delete</a>
                            <button class="view-images-btn" onclick="viewImages(<?= $row['id'] ?>)">View Images</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No events found.</p>
    <?php endif; ?>
</div>

<!-- Edit Event Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Event</h2>
        <form action="" method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" id="event_id" name="id">
            <div class="form-group">
                <label for="edit_title">Event Title</label>
                <input type="text" id="edit_title" name="title" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Event Description</label>
                <textarea id="edit_description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="edit_role">Event Role</label>
                <select id="edit_role" name="role" required>
                    <option value="completed">Completed Project</option>
                    <option value="upcoming">Upcoming Project</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_date">Event Date</label>
                <input type="date" id="edit_date" name="date" required>
            </div>
            <div class="form-group">
                <label for="edit_time">Event Time</label>
                <input type="time" id="edit_time" name="time" required>
            </div>
            <div class="form-group">
                <label for="edit_venue">Event Venue</label>
                <input type="text" id="edit_venue" name="venue" required>
            </div>
            <div class="form-group">
                <label for="edit_images">Add More Images</label>
                <input type="file" id="edit_images" name="images[]" accept="image/*" multiple>
                <small>Select multiple images to add to this event</small>
                <div class="image-preview-container" id="imagePreviewContainer"></div>
            </div>
            <div class="form-group">
                <label>Current Images</label>
                <div class="image-gallery" id="currentImagesGallery"></div>
            </div>
            <button type="submit" class="btn-submit">Save Changes</button>
        </form>
    </div>
</div>

<!-- View Images Modal -->
<div id="imagesModal" class="images-modal">
    <div class="images-modal-content">
        <span class="images-modal-close" onclick="closeImagesModal()">&times;</span>
        <h2>Event Images</h2>
        <div class="image-gallery" id="imagesModalGallery"></div>
    </div>
</div>

<script>
    // Edit Modal Functions
    function openModal(id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "?id=" + id, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                var event = JSON.parse(xhr.responseText);
                
                // Populate form fields
                document.getElementById('event_id').value = event.id;
                document.getElementById('edit_title').value = event.title;
                document.getElementById('edit_description').value = event.description;
                document.getElementById('edit_role').value = event.role;
                document.getElementById('edit_date').value = event.date;
                document.getElementById('edit_time').value = event.time;
                document.getElementById('edit_venue').value = event.venue;
                
                // Display current images
                var gallery = document.getElementById('currentImagesGallery');
                gallery.innerHTML = '';
                
                if (event.all_images && event.all_images.length > 0) {
                    event.all_images.forEach(function(image) {
                        var imgWrapper = document.createElement('div');
                        imgWrapper.className = 'image-preview-wrapper';
                        
                        var img = document.createElement('img');
                        img.src = image;
                        img.className = 'image-preview-item';
                        
                        var removeBtn = document.createElement('span');
                        removeBtn.className = 'remove-image-preview';
                        removeBtn.innerHTML = '×';
                        removeBtn.onclick = function() {
                            if (confirm('Are you sure you want to delete this image?')) {
                                deleteImage(event.id, image);
                            }
                        };
                        
                        imgWrapper.appendChild(img);
                        imgWrapper.appendChild(removeBtn);
                        gallery.appendChild(imgWrapper);
                    });
                } else {
                    gallery.innerHTML = '<p>No images for this event</p>';
                }
                
                document.getElementById("editModal").style.display = "block";
            } else {
                alert('Failed to load event details');
            }
        };
        xhr.send();
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    // Image Preview for New Uploads
    document.getElementById('edit_images').addEventListener('change', function(event) {
        var previewContainer = document.getElementById('imagePreviewContainer');
        previewContainer.innerHTML = '';
        
        var files = event.target.files;
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (!file.type.match('image.*')) continue;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                var previewWrapper = document.createElement('div');
                previewWrapper.className = 'image-preview-wrapper';
                
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview-item';
                
                var removeBtn = document.createElement('span');
                removeBtn.className = 'remove-image-preview';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    previewWrapper.remove();
                    // Remove the file from the input
                    var dataTransfer = new DataTransfer();
                    for (var j = 0; j < files.length; j++) {
                        if (j !== i) {
                            dataTransfer.items.add(files[j]);
                        }
                    }
                    event.target.files = dataTransfer.files;
                };
                
                previewWrapper.appendChild(img);
                previewWrapper.appendChild(removeBtn);
                previewContainer.appendChild(previewWrapper);
            }
            reader.readAsDataURL(file);
        }
    });

    // View Images Modal Functions
    function viewImages(eventId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "?id=" + eventId, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                var event = JSON.parse(xhr.responseText);
                var gallery = document.getElementById('imagesModalGallery');
                gallery.innerHTML = '';
                
                if (event.all_images && event.all_images.length > 0) {
                    event.all_images.forEach(function(image) {
                        var img = document.createElement('img');
                        img.src = image;
                        img.alt = 'Event Image';
                        gallery.appendChild(img);
                    });
                } else {
                    gallery.innerHTML = '<p>No images for this event</p>';
                }
                
                document.getElementById("imagesModal").style.display = "block";
            } else {
                alert('Failed to load event images');
            }
        };
        xhr.send();
    }

    function closeImagesModal() {
        document.getElementById("imagesModal").style.display = "none";
    }

    // Delete Image Function
    function deleteImage(eventId, imagePath) {
        if (confirm('Are you sure you want to delete this image?')) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_image.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status == 200) {
                    // Refresh the current images gallery
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Image deleted successfully');
                        openModal(eventId); // Refresh the modal
                    } else {
                        alert('Error deleting image: ' + response.message);
                    }
                } else {
                    alert('Request failed. Returned status of ' + xhr.status);
                }
            };
            xhr.send("event_id=" + eventId + "&image_path=" + encodeURIComponent(imagePath));
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById("editModal")) {
            closeModal();
        }
        if (event.target == document.getElementById("imagesModal")) {
            closeImagesModal();
        }
    }
</script>
</body>
</html>