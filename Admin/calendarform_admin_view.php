<?php
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Get selected calendar, year, and month from user input or default to current
$selectedCalendar = isset($_GET['calendar']) ? $_GET['calendar'] : 'calendar1';
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date("m");

// Fetch the year title (independent of month)
$yearSql = "SELECT year_title FROM calendars WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear LIMIT 1";
$yearResult = $conn->query($yearSql);
if ($yearResult && $yearResult->num_rows > 0) {
    $yearRow = $yearResult->fetch_assoc();
    $yearTitle = $yearRow['year_title'];
} else {
    $yearTitle = "No Topic for This Year";
}

// Fetch the correct month titles and week titles from the database
$sql = "SELECT * FROM calendars WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth";
$result = $conn->query($sql);

// If data exists, fetch it; otherwise, set default messages for month and weeks
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $monthTitle = $row['month_title'];
    $week1Title = $row['week1_title'];
    $week1Color = $row['week1_color'];
    $week2Title = $row['week2_title'];
    $week2Color = $row['week2_color'];
    $week1Start = isset($row['week1_start']) ? (int)$row['week1_start'] : 1;
    $week1End = isset($row['week1_end']) ? (int)$row['week1_end'] : 14;
    $week2Start = isset($row['week2_start']) ? (int)$row['week2_start'] : 15;
    $week2End = isset($row['week2_end']) ? (int)$row['week2_end'] : 31;
} else {
    $monthTitle = "No Topic for This Month";
    $week1Title = "No Topic for Week 1";
    $week2Title = "No Topic for Week 2";
    $week1Color = "";  // Default color for Week 1
    $week2Color = "";  // Default color for Week 2
    $week1Start = 1;
    $week1End = 14;
    $week2Start = 15;
    $week2End = 31;
}

// Fetch holidays for the selected calendar, year, and month
$holidaySql = "SELECT date, holiday_title, holiday_color FROM holidays WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth";
$holidayResult = $conn->query($holidaySql);
$holidays = [];
if ($holidayResult && $holidayResult->num_rows > 0) {
    while ($holidayRow = $holidayResult->fetch_assoc()) {
        $holidays[$holidayRow['date']] = [
            'title' => $holidayRow['holiday_title'],
            'color' => $holidayRow['holiday_color']
        ];
    }
}

// Get number of days in the selected month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$firstDay = date("w", strtotime("$selectedYear-$selectedMonth-01"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Calendar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .calendar-container {
            max-width: 100%;
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background: linear-gradient(135deg, #E9EDF4, #b3cce6);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #204060;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            table-layout: fixed; /* Ensures equal width columns */
        }

        th, td {
            text-align: center;
            padding: 10px;
            border: 2px solid #b3cce6;
            word-wrap: break-word;
        }

        th {
            background: linear-gradient(135deg, #b3cce6, #E9EDF4);
            text-transform: uppercase;
        }

        /* Highlight today */
        .today {
            background-color: #b3cce6;
            font-weight: bold;
        }

        /* Week topic styling */
        .week-topic {
            padding: 5px;
            color: white;
            font-weight: bold;
        }

        /* Holiday styling */
        .holiday {
            background-color: #FFCCCB; /* Default holiday color */
            font-weight: bold;
        }

        h1 {
            font-size: 1.5rem;
        }

        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            text-align: center;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }

        /* Responsive Fix */
        @media screen and (max-width: 768px) {
            .calendar-container {
                padding: 10px;
            }

            table {
                width: 100%;
            }

            th, td {
                padding: 6px;
                font-size: 0.9rem;
            }
        }

        @media screen and (max-width: 480px) {
            h1 {
                font-size: 1.2rem;
            }

            th, td {
                padding: 4px;
                font-size: 0.8rem;
            }
        }

        /* Custom styles for Saturday and Sunday dates */
        .saturday-date {
            color: #9370DB; /* Light purple for Saturday dates */
        }

        .sunday-date {
            color: #FF0000; /* Red for Sunday dates */
        }
        /* Custom styles for Saturday and Sunday dates */
        .saturday-date {
            color: #9370DB; /* Light purple for Saturday dates */
        }

        .sunday-date {
            color: #FF0000; /* Red for Sunday dates */
        }

        /* Custom styles for Saturday and Sunday headers */
        th.saturday-header {
            color: #9370DB;
        }

        th.sunday-header {
            color: #FF0000;
        }
    </style>
</head>
<body class="container mt-5">
<div class="calendar-container" >
        <!-- Dropdown to select calendar -->
        <form method="GET" class="mb-3">
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

        <h1><?= htmlspecialchars($yearTitle) ?></h1>
        <h2><?= htmlspecialchars($monthTitle) ?></h2>

    
        <!-- Calendar Table -->
        <table>
            <thead>
                <tr>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th class="saturday-header">Sat</th>
                    <th class="sunday-header">Sun</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $date = 1;
                for ($i = 0; $i < 6; $i++) {
                    echo "<tr>";
                    for ($j = 0; $j < 7; $j++) {
                        if ($i === 0 && $j < ($firstDay - 1 + 7) % 7) {
                            echo "<td></td>";
                        } elseif ($date > $daysInMonth) {
                            break;
                        } else {
                            // Initialize styles for week and holiday
                            $weekStyle = "";
                            $holidayStyle = "";

                            // Check if the date falls within Week 1 or Week 2
                            if ($date >= $week1Start && $date <= $week1End) {
                                $weekStyle = "background-color: $week1Color;";
                            } elseif ($date >= $week2Start && $date <= $week2End) {
                                $weekStyle = "background-color: $week2Color;";
                            }

                            // Check if the date is a holiday
                            $holidayTitle = "";
                            if (isset($holidays[$date])) {
                                $holidayStyle = "background-color: {$holidays[$date]['color']};";
                                $holidayTitle = "<div class='holiday-title'>{$holidays[$date]['title']}</div>";
                            }

                            // Combine week and holiday styles
                            $combinedStyle = $weekStyle . $holidayStyle;

                            // Fetch daily message for the date
                            $dailySql = "SELECT title, description FROM daily_messages WHERE calendar_type = '$selectedCalendar' AND year = $selectedYear AND month = $selectedMonth AND date = $date LIMIT 1";
                            $dailyResult = $conn->query($dailySql);
                            $dailyData = $dailyResult->fetch_assoc();

                            // Display the date, holiday title, and daily message title
                            $dailyMessage = $dailyData ? "<div class='daily-message'>{$dailyData['title']}</div>" : "";

                            // Add onclick event to open popup
                            $onclick = $dailyData ? "onclick=\"openPopup('{$dailyData['title']}', '{$dailyData['description']}')\"" : "";

                            // Determine if the date is Saturday or Sunday
                            $dayOfWeek = date("N", strtotime("$selectedYear-$selectedMonth-$date"));
                            $dateClass = "";
                            if ($dayOfWeek == 6) {
                                $dateClass = "saturday-date"; // Saturday
                            } elseif ($dayOfWeek == 7) {
                                $dateClass = "sunday-date"; // Sunday
                            }

                            // Output the table cell with combined styles
                            echo "<td style='$combinedStyle' $onclick><span class='$dateClass'>$date</span> $holidayTitle $dailyMessage</td>";
                            $date++;
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
        

            <!-- Week Topics -->
            <div class="row mb-3">
            <div class="col-6">
                <div class="week-topic" style="background-color: <?= htmlspecialchars($week1Color) ?>;">
                    <?= htmlspecialchars($week1Title) ?>
                </div>
            </div>
            <div class="col-6">
                <div class="week-topic" style="background-color: <?= htmlspecialchars($week2Color) ?>;">
                    <?= htmlspecialchars($week2Title) ?>
                </div>
            </div>
            </div>

    
    <br>
    <a href="calenderform.php" class="btn btn-warning">Edit Calendar Titles</a>
    <br>
    <a href="calenderform.php" class="btn btn-dark text-white position-absolute top-0 end-0 m-3">Back</a>

</body>
</html>
