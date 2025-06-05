<?php
session_start();

// Initialize error messages
$usernameError = $passwordError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch mechanic data from the database
    $sql = "SELECT * FROM mechanics WHERE BINARY username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mechanic = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $mechanic['password'])) {
            // Store session data
            $_SESSION['user_id'] = $mechanic['id'];
            $_SESSION['username'] = $mechanic['username'];
            $_SESSION['role'] = 'mechanic'; // Set role

            // Redirect to mechanic home
            header("Location: mechanic_home.php");
            exit();
        } else {
            $passwordError = "Invalid password.";
        }
    } else {
        $usernameError = "Username not found.";
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            width: 400px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .form-control {
            font-size: 18px;
            height: 50px;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center">Mechanic Login</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
                <?php if ($usernameError): ?>
                    <div class="error-message"><?= $usernameError ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <?php if ($passwordError): ?>
                    <div class="error-message"><?= $passwordError ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</body>
</html>
