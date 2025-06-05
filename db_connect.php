<?php
$servername = "localhost";
$username = "root"; // Default MySQL username in XAMPP
$password = ""; // Leave it empty
$dbname = "vehicle_assistance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
