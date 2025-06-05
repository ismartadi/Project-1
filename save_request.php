<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['user_id'];
$mechanicId = $data['mechanic_id'];
$vehicle = $data['vehicle'];
$issue = $data['issue'];
$userName = $data['user_name']; // Get user name
$userLocation = $data['location']; // Get user location

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO requests (user_id, mechanic_id, vehicle, issue, user_name, user_location) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissss", $userId, $mechanicId, $vehicle, $issue, $userName, $userLocation);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}

$stmt->close();
$conn->close();
?>