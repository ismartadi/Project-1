<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

$vehicle = $conn->real_escape_string($data['vehicle']);
$issue = $conn->real_escape_string($data['issue']);
$userPhone = $conn->real_escape_string($data['userPhone']);
$latitude = $conn->real_escape_string($data['latitude']);
$longitude = $conn->real_escape_string($data['longitude']);

// Insert the request into the database
$sql = "INSERT INTO requests (vehicle_type, issue_description, user_phone, latitude, longitude) VALUES ('$vehicle', '$issue', '$userPhone', '$latitude', '$longitude')";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
}

$conn->close();
?>