<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the request data
$data = json_decode(file_get_contents("php://input"), true);

// Check if data is received
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit();
}

// Escape the input data
$vehicle = $conn->real_escape_string($data['vehicle']);
$issue = $conn->real_escape_string($data['issue']);
$phone = $conn->real_escape_string($data['phone']);
$user_latitude = $conn->real_escape_string($data['user_latitude']);
$user_longitude = $conn->real_escape_string($data['user_longitude']);
$mechanic_id = $conn->real_escape_string($data['mechanic_id']); // Get mechanic_id

// Check if there is already a pending request for this mechanic
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE mechanic_id = ? AND phone = ? AND status = 'pending'");
$checkStmt->bind_param("is", $mechanic_id, $phone);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    echo json_encode(['success' => false, 'error' => 'You cannot send a new request until the mechanic accepts or rejects your previous request.']);
    exit();
}

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO requests (mechanic_id, vehicle_type, issue_description, phone, user_latitude, user_longitude, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')");

if ($stmt) {
    // Bind parameters
    $stmt->bind_param("issddd", $mechanic_id, $vehicle, $issue, $phone, $user_latitude, $user_longitude);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare SQL statement']);
}

// Close the connection
$conn->close();
?>