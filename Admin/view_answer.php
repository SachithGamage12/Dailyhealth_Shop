<?php


$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? 0;

$sql = "SELECT wa.*, wq.calendar_type, wq.question, wq.week_start_date, wq.week_end_date,
               u.username, u.name, u.profile_picture
        FROM weekly_answers wa
        JOIN weekly_questions wq ON wa.question_id = wq.id
        JOIN users u ON wa.user_id = u.id
        WHERE wa.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$answer = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Answer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .answer-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            overflow: hidden;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .answer-image {
            max-width: 100%;
            max-height: 400px;
            margin-top: 15px;
        }
        .calendar-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($answer): ?>
            <div class="answer-header">
                <h1>Answer Details</h1>
                <div class="calendar-info">
                    <h3><?php echo htmlspecialchars($answer['calendar_type']); ?></h3>
                    <p>Week of <?php echo date('M j', strtotime($answer['week_start_date'])).' to '.date('M j, Y', strtotime($answer['week_end_date'])); ?></p>
                    <p><strong>Question:</strong> <?php echo htmlspecialchars($answer['question']); ?></p>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php if (!empty($answer['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($answer['profile_picture']); ?>" alt="Profile">
                        <?php else: ?>
                            <?php echo strtoupper(substr($answer['name'] ?? 'U', 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($answer['name']); ?></h3>
                        <p>@<?php echo htmlspecialchars($answer['username']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="answer-content">
                <h3>Answer:</h3>
                <p><?php echo nl2br(htmlspecialchars($answer['answer'])); ?></p>
                
                <?php if (!empty($answer['image_path'])): ?>
                    <h3>Attached Image:</h3>
                    <img src="<?php echo htmlspecialchars($answer['image_path']); ?>" class="answer-image" alt="Answer Image">
                <?php endif; ?>
                
                <p><em>Submitted on <?php echo date('M j, Y g:i a', strtotime($answer['created_at'])); ?></em></p>
            </div>
            
            <a href="answer.php" class="btn">Back to Answers</a>
        <?php else: ?>
            <p>Answer not found.</p>
            <a href="answer.php" class="btn">Back to Answer table</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>