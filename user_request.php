<?php
// user_requests.php

// Include database connection
include 'db_connection.php';

// Fetch requests assigned to this mechanic
$mechanic_id = $_SESSION['mechanic_id'];
$query = "SELECT * FROM user_requests WHERE mechanic_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mechanic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];  // 'accept' or 'reject'

    $status = ($action === 'accept') ? 'Accepted' : 'Rejected';
    $stmt = $conn->prepare("UPDATE user_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $request_id);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Requests</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Incoming User Requests</h2>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div>
                <p>Request from User ID: <?php echo $row['user_id']; ?> - Status: <?php echo $row['status']; ?></p>
                <form method="post">
                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="action" value="accept">Accept</button>
                    <button type="submit" name="action" value="reject">Reject</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>
