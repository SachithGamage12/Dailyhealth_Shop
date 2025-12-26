<?php
// Database connection using PDO
$dsn = 'mysql:host=localhost;dbname=u627928174_daily_routine';
$username = 'u627928174_root';
$password = 'Daily@365';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exceptions for errors
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$selectedCalendar = $_GET['calendar'] ?? 'calendar1';
$selectedYear = $_GET['year'] ?? date("Y");
$selectedMonth = $_GET['month'] ?? date("n");

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM calendars WHERE calendar_type = ? AND year = ? AND month = ?");
$stmt->execute([$selectedCalendar, $selectedYear, $selectedMonth]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$yearTitle = $data['year_title'] ?? '';
$monthTitle = $data['month_title'] ?? '';
$week1Title = $data['week1_title'] ?? '';
$week1Start = $data['week1_start'] ?? 1;
$week1End = $data['week1_end'] ?? 7;
$week1Color = $data['week1_color'] ?? '#ffffff';
$week2Title = $data['week2_title'] ?? '';
$week2Start = $data['week2_start'] ?? 8;
$week2End = $data['week2_end'] ?? 14;
$week2Color = $data['week2_color'] ?? '#ffffff';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $yearTitle = $_POST['year_title'];
    $monthTitle = $_POST['month_title'];
    $week1Title = $_POST['week1_title'];
    $week1Start = $_POST['week1_start'];
    $week1End = $_POST['week1_end'];
    $week1Color = $_POST['week1_color'];
    $week2Title = $_POST['week2_title'];
    $week2Start = $_POST['week2_start'];
    $week2End = $_POST['week2_end'];
    $week2Color = $_POST['week2_color'];

    // Update or insert data
    $stmt = $pdo->prepare("REPLACE INTO calendars (calendar_type, year_title, month_title, week1_title, week1_start, week1_end, week1_color, week2_title, week2_start, week2_end, week2_color, year, month) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$selectedCalendar, $yearTitle, $monthTitle, $week1Title, $week1Start, $week1End, $week1Color, $week2Title, $week2Start, $week2End, $week2Color, $selectedYear, $selectedMonth]);

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?calendar=$selectedCalendar&year=$selectedYear&month=$selectedMonth");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Update Calendar Titles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">Admin Panel - Update Calendar Titles</h2>

    <!-- Dropdown for Calendar Selection -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="calendar">Select Calendar:</label>
                <select name="calendar" id="calendar" class="form-control" onchange="this.form.submit()">
                    <?php
                    // Generate options for calendar_type from calendar1 to calendar65
                    for ($i = 1; $i <= 65; $i++) {
                        $calendarValue = 'calendar' . $i;
                        $selected = ($selectedCalendar === $calendarValue) ? 'selected' : '';
                        echo "<option value='$calendarValue' $selected>Calendar $i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year">Select Year:</label>
                <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                    <?php for ($y = date("Y") - 10; $y <= date("Y") + 10; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="month">Select Month:</label>
                <select name="month" id="month" class="form-control" onchange="this.form.submit()">
                    <?php
                    $months = [
                        1 => "January", 2 => "February", 3 => "March", 4 => "April",
                        5 => "May", 6 => "June", 7 => "July", 8 => "August",
                        9 => "September", 10 => "October", 11 => "November", 12 => "December"
                    ];
                    foreach ($months as $num => $name):
                    ?>
                        <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <!-- Form for Editing Titles -->
    <form method="POST">
        <div class="mb-3">
            <label>Year Title:</label>
            <input type="text" class="form-control" name="year_title" value="<?= htmlspecialchars($yearTitle) ?>" required>
        </div>
        <div class="mb-3">
            <label>Month Title:</label>
            <input type="text" class="form-control" name="month_title" value="<?= htmlspecialchars($monthTitle) ?>" required>
        </div>

        <!-- Week 1 -->
        <h4>Week 1 Topic</h4>
        <div class="mb-3">
            <input type="text" class="form-control" name="week1_title" value="<?= htmlspecialchars($week1Title) ?>" placeholder="Week 1 Title" required>
            <input type="number" class="form-control mt-2" name="week1_start" value="<?= $week1Start ?>" min="1" max="31" required>
            <input type="number" class="form-control mt-2" name="week1_end" value="<?= $week1End ?>" min="1" max="31" required>
            <input type="color" class="form-control mt-2" name="week1_color" value="<?= $week1Color ?>">
        </div>

        <!-- Week 2 -->
        <h4>Week 2 Topic</h4>
        <div class="mb-3">
            <input type="text" class="form-control" name="week2_title" value="<?= htmlspecialchars($week2Title) ?>" placeholder="Week 2 Title" required>
            <input type="number" class="form-control mt-2" name="week2_start" value="<?= $week2Start ?>" min="1" max="31" required>
            <input type="number" class="form-control mt-2" name="week2_end" value="<?= $week2End ?>" min="1" max="31" required>
            <input type="color" class="form-control mt-2" name="week2_color" value="<?= $week2Color ?>">
        </div>
        <center>
            <button type="submit" class="btn btn-primary">Update Calendar</button>
        </center>
    </form>
    
    <a href="calendarform_admin_view.php" class="btn btn-warning">Edit Calendar Titles</a>
    
    <a href="admin_panel.html" class="btn btn-dark text-white position-absolute top-0 end-0 m-3">Back</a>


</body>
</html>