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
    echo json_encode(['exists' => false]);
    exit();
}

$phone = $conn->real_escape_string($data['phone']);

// Check for existing requests
$sql = "SELECT * FROM requests WHERE phone = '$phone' AND status = 'pending'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
}

// Close the connection
$conn->close();
?>