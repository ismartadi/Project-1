
<?php
// edit_mechanic.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $id = $_POST['id'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $name = $_POST['name'];

    // Update mechanic in the database
    $updateSql = "UPDATE mechanics SET username = ?, phone = ?, name = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssi", $username, $phone, $name, $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php"); // Redirect to admin dashboard after edit
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Fetch the mechanic for editing
    $id = $_GET['id'];
    $conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');
    $mechanicSql = "SELECT * FROM mechanics WHERE id = ?";
    $stmt = $conn->prepare($mechanicSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mechanic = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Mechanic</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f9fc;
        }
        .container {
            margin-top: 50px;
        }
        h1 {
            margin-bottom: 30px;
        }
        .btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Mechanic</h1>
        <form method="POST" action="edit_mechanic.php">
            <input type="hidden" name="id" value="<?php echo $mechanic['id']; ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" value="<?php echo $mechanic['username']; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" class="form-control" name="phone" value="<?php echo $mechanic['phone']; ?>">
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" name="name" value="<?php echo $mechanic['name']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Mechanic</button>
        </form>
        <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
    </div>
</body>
</html>
