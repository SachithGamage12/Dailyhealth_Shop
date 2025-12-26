<?php
session_start();
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_question'])) {
        // Add new question
        $calendarType = $_POST['calendar_type'];
        $questionText = $_POST['question'];
        $weekStart = $_POST['week_start'];
        $weekEnd = $_POST['week_end'];
        
        $stmt = $conn->prepare("INSERT INTO weekly_questions (calendar_type, question, week_start_date, week_end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $calendarType, $questionText, $weekStart, $weekEnd);
        $stmt->execute();
        
        $_SESSION['message'] = "Question added successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } 
    elseif (isset($_POST['update_question'])) {
        // Update question
        $questionId = $_POST['question_id'];
        $calendarType = $_POST['calendar_type'];
        $questionText = $_POST['question'];
        $weekStart = $_POST['week_start'];
        $weekEnd = $_POST['week_end'];
        
        $stmt = $conn->prepare("UPDATE weekly_questions SET calendar_type = ?, question = ?, week_start_date = ?, week_end_date = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $calendarType, $questionText, $weekStart, $weekEnd, $questionId);
        $stmt->execute();
        
        $_SESSION['message'] = "Question updated successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $questionId = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM weekly_questions WHERE id = ?");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    
    $_SESSION['message'] = "Question deleted successfully!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Get question to edit
$editQuestion = null;
if (isset($_GET['edit'])) {
    $questionId = (int)$_GET['edit'];
    
    $stmt = $conn->prepare("SELECT * FROM weekly_questions WHERE id = ?");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editQuestion = $result->fetch_assoc();
    $stmt->close();
}

// Get all questions
$questions = [];
$stmt = $conn->prepare("SELECT * FROM weekly_questions ORDER BY week_start_date DESC");
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f1f1f1;
            position: sticky;
            top: 0;
        }
        .action-btns .btn {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 14px;
        }
        .question-preview {
            white-space: pre-wrap;
            background: #f9f9f9;
            padding: 8px 12px;
            border-radius: 5px;
            border-left: 3px solid #0d6efd;
            max-height: 100px;
            overflow: hidden;
        }
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .action-btns .btn {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <?php if (isset($_GET['add']) || isset($editQuestion)): ?>
            <div class="form-section">
                <h2><?php echo isset($editQuestion) ? 'Edit Question' : 'Add New Question'; ?></h2>
                <form method="post">
                    <?php if (isset($editQuestion)): ?>
                        <input type="hidden" name="question_id" value="<?php echo $editQuestion['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Calendar Type</label>
                            <select class="form-select" name="calendar_type" required>
                                <?php for ($i = 1; $i <= 65; $i++): ?>
                                    <option value="calendar<?php echo $i; ?>"
                                        <?php if (isset($editQuestion) && $editQuestion['calendar_type'] == 'calendar'.$i) echo 'selected'; ?>>
                                        Calendar <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Week Start</label>
                            <input type="date" class="form-control" name="week_start" 
                                   value="<?php echo isset($editQuestion) ? $editQuestion['week_start_date'] : ''; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Week End</label>
                            <input type="date" class="form-control" name="week_end" 
                                   value="<?php echo isset($editQuestion) ? $editQuestion['week_end_date'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="question" rows="5" required><?php 
                            echo isset($editQuestion) ? htmlspecialchars($editQuestion['question']) : ''; 
                        ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="<?php echo isset($editQuestion) ? 'update_question' : 'add_question'; ?>" 
                                class="btn btn-primary">
                            <?php echo isset($editQuestion) ? 'Update Question' : 'Add Question'; ?>
                        </button>
                        <a href="?" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Questions Table -->
        <div class="card">
                    <a href="question_form.php" class="btn" style="
    position: absolute;
    top: 15px;
    right: 75px;
    background-color: black;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
">Back to Question Form</a>
            <div class="card-body">
                <h2 class="card-title">All Questions</h2>
                <div class="table-responsive">
                    <?php if (empty($questions)): ?>
                        <div class="alert alert-info">No questions found.</div>
                    <?php else: ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Calendar</th>
                                    <th>Week Range</th>
                                    <th>Question Preview</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $question): ?>
                                    <tr>
                                        <td><?php echo $question['id']; ?></td>
                                        <td><?php echo htmlspecialchars($question['calendar_type']); ?></td>
                                        <td>
                                            <?php echo date('M j', strtotime($question['week_start_date'])) . ' - ' . 
                                                 date('M j, Y', strtotime($question['week_end_date'])); ?>
                                        </td>
                                        <td>
                                            <div class="question-preview" title="<?php echo htmlspecialchars($question['question']); ?>">
                                                <?php echo substr(htmlspecialchars($question['question']), 0, 100); ?>
                                                <?php if (strlen($question['question']) > 100): ?>
                                                    <span class="text-muted">...</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($question['created_at'])); ?></td>
                                        <td class="action-btns">
                                            <a href="?edit=<?php echo $question['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete=<?php echo $question['id']; ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this question?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default dates for new questions
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.search.includes('add=new')) {
                const today = new Date();
                const startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay()); // Sunday
                
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6); // Saturday
                
                document.querySelector('input[name="week_start"]').valueAsDate = startDate;
                document.querySelector('input[name="week_end"]').valueAsDate = endDate;
            }
            
            // Confirm before delete
            document.querySelectorAll('.btn-danger').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this question?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>