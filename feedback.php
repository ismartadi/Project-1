//feedback.php
<?php
include 'db_connection.php';
session_start();

$mechanic_id = isset($_GET['mechanic_id']) ? $_GET['mechanic_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $feedback_text = $_POST['feedback'];

    // Insert feedback into database
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, mechanic_id, feedback) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $mechanic_id, $feedback_text);
    $stmt->execute();
}

// Fetch feedback for a specific mechanic
$sql = "SELECT feedback.feedback, users.username FROM feedback JOIN users ON feedback.user_id = users.id WHERE feedback.mechanic_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mechanic_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feedback</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Feedback for Mechanic ID: <?php echo htmlspecialchars($mechanic_id); ?></h2>

        <form method="POST">
            <textarea name="feedback" required></textarea><br>
            <input type="submit" value="Submit Feedback">
        </form>

        <h3>Existing Feedback:</h3>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div><?php echo htmlspecialchars($row['username']) . ': ' . htmlspecialchars($row['feedback']); ?></div>
        <?php } ?>
    </div>
</body>
</html>
