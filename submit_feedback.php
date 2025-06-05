<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get feedback data
$mechanicId = isset($_POST['mechanic_id']) ? intval($_POST['mechanic_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validate input
if ($mechanicId > 0 && $rating > 0 && !empty($comment)) {
    // Insert feedback into the database
    $stmt = $conn->prepare("INSERT INTO feedback (mechanic_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $mechanicId, $rating, $comment);
    if ($stmt->execute()) {
        echo "Feedback submitted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid input.";
}

$conn->close();
?>