<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get mechanic ID and new status from POST request
$mechanicId = isset($_POST['mechanic_id']) ? intval($_POST['mechanic_id']) : 0;
$status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';

// Debugging: Log the received parameters
error_log("Received mechanic_id: $mechanicId, status: $status");

// Update mechanic status in the database
if ($mechanicId > 0 && ($status === 'free' || $status === 'busy')) {
    $stmt = $conn->prepare("UPDATE mechanics SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $mechanicId);
    
    // Debugging: Check if the statement was prepared successfully
    if ($stmt === false) {
        error_log("Prepare failed: " . htmlspecialchars($conn->error));
        echo json_encode(['success' => false, 'error' => 'Prepare failed']);
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        error_log("Execute failed: " . htmlspecialchars($stmt->error));
        echo json_encode(['success' => false, 'error' => 'Execute failed']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
}

$conn->close();
?>