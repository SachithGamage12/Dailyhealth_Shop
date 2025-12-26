<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete all request
if (isset($_GET['delete_all']) && $_GET['delete_all'] == '1') {
    // First get all image paths to delete the files
    $getImagesSql = "SELECT image_path FROM specialdays WHERE image_path IS NOT NULL AND image_path != ''";
    $result = $conn->query($getImagesSql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (file_exists($row['image_path'])) {
                unlink($row['image_path']);
            }
        }
    }
    
    // Then delete all records
    $deleteAllSql = "DELETE FROM specialdays";
    if ($conn->query($deleteAllSql)) {
        echo "<script>alert('All special days deleted successfully!'); window.location.href='specialdaytable.php';</script>";
    } else {
        echo "<script>alert('Error deleting all special days: " . $conn->error . "');</script>";
    }
    exit();
}

// Handle delete single request
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql = "DELETE FROM specialdays WHERE id = $id";
    if ($conn->query($sql)) {
        // Also delete the associated image file if it exists
        $getImageSql = "SELECT image_path FROM specialdays WHERE id = $id";
        $result = $conn->query($getImageSql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                unlink($row['image_path']);
            }
        }
        echo "<script>alert('Special day deleted successfully!'); window.location.href='specialdaytable.php';</script>";
    } else {
        echo "<script>alert('Error deleting special day: " . $conn->error . "');</script>";
    }
}

// Handle update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
    $id = $_POST['update_id'];
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $specialday_title = $_POST['specialday_title'];
    $description = $_POST['description'];
    $special_message = $_POST['special_message'];
    
    // Handle image update
    $image_path = $_POST['existing_image'];
    if (isset($_FILES['specialday_image']) && $_FILES['specialday_image']['error'] == 0) {
        // Delete old image if it exists
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Upload new image
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

    $updateSql = "UPDATE specialdays SET 
                  year = $year, 
                  month = $month, 
                  date = $date, 
                  specialday_title = '$specialday_title', 
                  description = '$description', 
                  special_message = '$special_message', 
                  image_path = '$image_path' 
                  WHERE id = $id";

    if ($conn->query($updateSql)) {
        echo "<script>alert('Special day updated successfully!'); window.location.href='specialdaytable.php';</script>";
    } else {
        echo "<script>alert('Error updating special day: " . $conn->error . "');</script>";
    }
}

// Get all special days
$sql = "SELECT * FROM specialdays ORDER BY year DESC, month DESC, date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Days Table</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .table-container {
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table-container h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .thumbnail-img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }
        .action-btns {
            white-space: nowrap;
        }
        .modal-img-preview {
            max-width: 100%;
            max-height: 200px;
            margin-bottom: 15px;
        }
        .btn-delete-all {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container table-container">
        <h2>Special Days Management</h2>
        
        <div class="mb-3">
            <a href="special_message_form.php" class="btn btn-secondary">Back to Form</a>
            <button type="button" class="btn btn-delete-all text-white" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                Delete All Special Days
            </button>
        </div>
        
        <table id="specialDaysTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Calendar</th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                 
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $dateObj = DateTime::createFromFormat('!m', $row['month']);
                        $monthName = $dateObj->format('F');
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['calendar_type']}</td>
                                <td>{$row['date']} {$monthName} {$row['year']}</td>
                                <td>{$row['specialday_title']}</td>
                                <td>" . substr($row['description'], 0, 50) . (strlen($row['description']) > 50 ? '...' : '') . "</td>
                                <td>";
                        if (!empty($row['image_path'])) {
                            echo "<img src='{$row['image_path']}' class='thumbnail-img' alt='Special Day Image'>";
                        } else {
                            echo "No Image";
                        }
                        echo "</td>
                               
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No special days found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Special Day</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="update_id" id="update_id">
                        <input type="hidden" name="existing_image" id="existing_image">
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_year" class="form-label">Year:</label>
                                <input type="number" id="edit_year" name="year" class="form-control" required min="2000" max="2100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_month" class="form-label">Month:</label>
                                <select id="edit_month" name="month" class="form-control" required>
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
                            <div class="col-md-4 mb-3">
                                <label for="edit_date" class="form-label">Date:</label>
                                <input type="number" id="edit_date" name="date" class="form-control" required min="1" max="31">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_specialday_title" class="form-label">Special Day Title:</label>
                            <input type="text" id="edit_specialday_title" name="specialday_title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description:</label>
                            <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_special_message" class="form-label">Special Message:</label>
                            <textarea id="edit_special_message" name="special_message" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_specialday_image" class="form-label">Special Day Image:</label>
                            <input type="file" id="edit_specialday_image" name="specialday_image" class="form-control" accept="image/*">
                            <div id="imagePreviewContainer" class="mt-2">
                                <img id="editImagePreview" class="modal-img-preview" src="#" alt="Image Preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this special day? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete All Confirmation Modal -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllModalLabel">Confirm Delete All</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete ALL special days?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone and will permanently delete all special days and their associated images.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="specialdaytable.php?delete_all=1" class="btn btn-danger">Delete All</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#specialDaysTable').DataTable({
                responsive: true,
                order: [[2, 'desc']] // Default sort by date descending
            });
            
            // Edit button click handler
            $('.edit-btn').click(function() {
                var id = $(this).data('id');
                
                // Fetch the special day data via AJAX
                $.ajax({
                    url: 'get_specialday.php?id=' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data) {
                            // Populate the edit form
                            $('#update_id').val(data.id);
                            $('#edit_year').val(data.year);
                            $('#edit_month').val(data.month);
                            $('#edit_date').val(data.date);
                            $('#edit_specialday_title').val(data.specialday_title);
                            $('#edit_description').val(data.description);
                            $('#edit_special_message').val(data.special_message);
                            $('#existing_image').val(data.image_path);
                            
                            // Handle image preview
                            if (data.image_path) {
                                $('#editImagePreview').attr('src', data.image_path).show();
                                $('#imagePreviewContainer').show();
                            } else {
                                $('#editImagePreview').hide();
                                $('#imagePreviewContainer').hide();
                            }
                            
                            // Show the modal
                            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                            editModal.show();
                        }
                    },
                    error: function() {
                        alert('Error fetching special day data');
                    }
                });
            });
            
            // Delete button click handler
            $('.delete-btn').click(function() {
                var id = $(this).data('id');
                $('#confirmDeleteBtn').attr('href', 'specialdaytable.php?delete_id=' + id);
                
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
            
            // Image preview for edit modal
            $('#edit_specialday_image').change(function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#editImagePreview').attr('src', e.target.result).show();
                        $('#imagePreviewContainer').show();
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>