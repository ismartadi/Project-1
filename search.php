<?php
// search.php

// Include database connection
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = $_POST['location'];
    $query = "SELECT * FROM mechanics WHERE location LIKE ?";
    $stmt = $conn->prepare($query);
    $searchParam = "%" . $location . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Mechanics</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Search for Mechanics</h2>
        <form method="post">
            <input type="text" name="location" placeholder="Enter your location">
            <button type="submit">Search</button>
        </form>

        <h3>Mechanics Available</h3>
        <?php if (isset($result)) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div><?php echo $row['name']; ?> - <?php echo $row['location']; ?> - <?php echo $row['status']; ?></div>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>
