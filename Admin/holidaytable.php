<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete all action
if (isset($_GET['delete_all'])) {
    $deleteAllSql = "DELETE FROM holidays";
    if ($conn->query($deleteAllSql)) {
        echo "<script>alert('All holidays deleted successfully!'); window.location.href = 'holidaytable.php';</script>";
    } else {
        echo "<script>alert('Error deleting all holidays: " . $conn->error . "');</script>";
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $deleteSql = "DELETE FROM holidays WHERE id = $delete_id";
    if ($conn->query($deleteSql)) {
        echo "<script>alert('Holiday deleted successfully!'); window.location.href = 'holidaytable.php';</script>";
    } else {
        echo "<script>alert('Error deleting holiday: " . $conn->error . "');</script>";
    }
}

// Handle edit action (fetch data for the modal)
$editData = [];
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $editSql = "SELECT * FROM holidays WHERE id = $edit_id";
    $editResult = $conn->query($editSql);
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}

// Handle update action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_holiday'])) {
    $id = $_POST['id'];
    $year = $_POST['year'];
    $month = $_POST['month'];
    $date = $_POST['date'];
    $holiday_title = $_POST['holiday_title'];
    $holiday_color = $_POST['holiday_color'];

    $updateSql = "UPDATE holidays SET year = $year, month = $month, date = $date, holiday_title = '$holiday_title', holiday_color = '$holiday_color' WHERE id = $id";
    if ($conn->query($updateSql)) {
        echo "<script>alert('Holiday updated successfully!'); window.location.href = 'holidaytable.php';</script>";
    } else {
        echo "<script>alert('Error updating holiday: " . $conn->error . "');</script>";
    }
}

// Fetch all holidays
$sql = "SELECT * FROM holidays ORDER BY year DESC, month DESC, date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Holiday Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .table-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .table-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .delete-all-btn {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <h2>Admin Holiday Management</h2>
        
        <div class="btn-container">
            <a href="holiday_marker.php" class="btn btn-primary">Back to Holiday Form</a>
            <a href="?delete_all=1" class="btn delete-all-btn" onclick="return confirm('Are you sure you want to delete ALL holidays? This action cannot be undone.')">Delete All Holidays</a>
        </div>
        
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Calendar Type</th>
                    <th>Year</th>
                    <th>Month</th>
                    <th>Date</th>
                    <th>Holiday Title</th>
                    <th>Holiday Color</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['calendar_type']}</td>
                                <td>{$row['year']}</td>
                                <td>{$row['month']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['holiday_title']}</td>
                                <td><span style='background-color: {$row['holiday_color']}; padding: 5px; border-radius: 5px;'>{$row['holiday_color']}</span></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No holidays found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Holiday Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="edit_id" value="<?php echo $editData['id'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="year" class="form-label">Year:</label>
                            <input type="number" id="edit_year" name="year" class="form-control" required min="2000" max="2100" value="<?php echo $editData['year'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="month" class="form-label">Month:</label>
                            <select id="edit_month" name="month" class="form-control" required>
                                <?php
                                $months = [
                                    1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
                                    7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December"
                                ];
                                foreach ($months as $key => $value) {
                                    $selected = ($key == ($editData['month'] ?? '')) ? "selected" : "";
                                    echo "<option value='$key' $selected>$value</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date:</label>
                            <input type="number" id="edit_date" name="date" class="form-control" required min="1" max="31" value="<?php echo $editData['date'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="holiday_title" class="form-label">Holiday Title:</label>
                            <input type="text" id="edit_holiday_title" name="holiday_title" class="form-control" required value="<?php echo $editData['holiday_title'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="holiday_color" class="form-label">Holiday Color:</label>
                            <input type="color" id="edit_holiday_color" name="holiday_color" class="form-control" value="<?php echo $editData['holiday_color'] ?? '#FF0000'; ?>">
                        </div>
                        <button type="submit" name="update_holiday" class="btn btn-primary">Update Holiday</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>