
<?php
include 'db_connect.php';
session_start(); // Start the session

// Initialize error messages
$usernameError = $passwordError = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE BINARY username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password using password_verify
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            // Redirect to the user home page
            header("Location: user_home.php");
            exit(); // Always call exit after a header redirect
        } else {
            $passwordError = "Invalid password.";
        }
    } else {
        $usernameError = "Username not found.";
    }
}
?>

<!-- HTML Form for Login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            width: 400px; /* Set a fixed width for the form */
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            font-size: 14px; /* Smaller font size */
            height: 45px; /* Smaller height */
        }
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2 class="text-center">User Login</h2><br>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" required>
            <?php if ($usernameError): ?>
                <div class="error-message"><?= $usernameError ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" name="password" required>
            <?php if ($passwordError): ?>
                <div class="error-message"><?= $passwordError ?></div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
</div>
</body>
</html>
