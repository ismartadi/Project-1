<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$mysqli = new mysqli("localhost", "root", "", "vehicle_assistance");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get the user ID from the request (e.g., passed via GET)
$userId = $_GET['id']; 

// Validate user ID
if (!isset($userId) || empty($userId)) {
    echo "No user ID provided.";
    exit;
}

// Start a transaction
$mysqli->begin_transaction();

try {
    // First, delete related requests from user_requests table
    $deleteRequestsSql = "DELETE FROM user_requests WHERE user_id = ?";
    $stmt = $mysqli->prepare($deleteRequestsSql);
    if (!$stmt) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit;
    }
    $stmt->bind_param("i", $userId);
    if ($stmt->execute() === FALSE) {
        echo "Failed to delete requests: (" . $stmt->errno . ") " . $stmt->error;
        $stmt->close();
        $mysqli->rollback();
        exit;
    }
    $stmt->close();

    // Then, delete the user from users table
    $deleteUserSql = "DELETE FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($deleteUserSql);
    if (!$stmt) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit;
    }
    $stmt->bind_param("i", $userId);
    if ($stmt->execute() === FALSE) {
        echo "Failed to delete user: (" . $stmt->errno . ") " . $stmt->error;
        $stmt->close();
        $mysqli->rollback();
        exit;
    }

    // Commit the transaction
    $mysqli->commit();
    echo "User deleted successfully.";

} catch (Exception $e) {
    // Rollback the transaction if something failed
    $mysqli->rollback();
    echo "Failed to delete user: " . $e->getMessage();
}

// Close the statement and connection
$stmt->close();
$mysqli->close();
?>
