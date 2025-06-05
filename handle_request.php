<?php
session_start();

// Check if the user is logged in and has the role 'mechanic'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mechanic') {
    header("Location: mechanic_login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Update the request status to accepted
        $stmt = $conn->prepare("UPDATE requests SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    } elseif ($action === 'reject') {
        // Update the request status to rejected
        $stmt = $conn->prepare("UPDATE requests SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }

    // Redirect back to the mechanic home page
    header("Location: mechanic_home.php");
    exit();
}

// Close the connection
$conn->close();
?>