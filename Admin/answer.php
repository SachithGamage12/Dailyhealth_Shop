<?php
// admin_answers_table.php


$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all answers with calendar type and user information
$sql = "SELECT wa.*, wq.calendar_type, wq.week_start_date, wq.week_end_date, 
               u.username, u.name, u.profile_picture
        FROM weekly_answers wa
        JOIN weekly_questions wq ON wa.question_id = wq.id
        JOIN users u ON wa.user_id = u.id
        ORDER BY wa.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Answers Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            overflow: hidden;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .answer-image {
            max-width: 150px;
            max-height: 150px;
        }
        .actions {
            white-space: nowrap;
        }
        .btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin: 2px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-view {
            background-color: #2196F3;
            color: white;
        }
        .week-info {
            white-space: nowrap;
        }
        .calendar-type {
            font-weight: bold;
            color: #2c3e50;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .search-container input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filter-container {
            margin-bottom: 20px;
        }
        .filter-container select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Answers Management</h1>
        
        <div class="search-container">
            <div class="filter-container">
                <select id="calendar-filter">
                    <option value="">All Calendars</option>
                    <?php
                    // Fetch distinct calendar types for filter
                    $calendarQuery = $conn->query("SELECT DISTINCT calendar_type FROM weekly_questions ORDER BY calendar_type");
                    while ($calendar = $calendarQuery->fetch_assoc()) {
                        echo '<option value="'.htmlspecialchars($calendar['calendar_type']).'">'.htmlspecialchars($calendar['calendar_type']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <input type="text" id="search-input" placeholder="Search answers...">
        </div>
        
        <table id="answers-table">
            <thead>
                <tr>
                    <th>Calendar</th>
                    <th>Week</th>
                    <th>User</th>
                    <th>Answer</th>
                    <th>Image</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-calendar="<?php echo htmlspecialchars($row['calendar_type']); ?>">
                            <td class="calendar-type"><?php echo htmlspecialchars($row['calendar_type']); ?></td>
                            <td class="week-info">
                                <?php echo date('M j', strtotime($row['week_start_date'])).' - '.date('M j, Y', strtotime($row['week_end_date'])); ?>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php if (!empty($row['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($row['name'] ?? 'U', 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div><?php echo htmlspecialchars($row['name']); ?></div>
                                        <small>@<?php echo htmlspecialchars($row['username']); ?></small>
                                    </div>
                                </div>
                            </td>
<td><?php echo nl2br(htmlspecialchars(substr($row['answer'], 0, 100) . (strlen($row['answer']) > 100 ? '...' : ''))); ?></td>                            <td>
                                <?php if (!empty($row['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="answer-image" alt="Answer Image">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y g:i a', strtotime($row['created_at'])); ?></td>
                            <td class="actions">
                                <a href="view_answer.php?id=<?php echo $row['id']; ?>" class="btn btn-view">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No answers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="pagination">
            <a href="#">&laquo;</a>
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">&raquo;</a>
        </div>
    </div>

    <script>
        // Simple client-side filtering
        document.getElementById('search-input').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#answers-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const calendarFilter = document.getElementById('calendar-filter').value;
                const calendarMatch = calendarFilter === '' || row.dataset.calendar === calendarFilter;
                
                if ((text.includes(filter) || filter === '') && calendarMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Calendar filter
        document.getElementById('calendar-filter').addEventListener('change', function() {
            const filter = document.getElementById('search-input').value.toLowerCase();
            const calendarFilter = this.value;
            const rows = document.querySelectorAll('#answers-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const calendarMatch = calendarFilter === '' || row.dataset.calendar === calendarFilter;
                
                if ((text.includes(filter) || filter === '') && calendarMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
        <a href="admin_panel.html" class="btn" style="
    position: absolute;
    top: 65px;
    right: 155px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Admin</a>
</body>
</html>
<?php $conn->close(); ?>