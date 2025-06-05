<?php
session_start(); // Start a session

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize selected_option
$selected_option = 'manage_users'; // Default to manage users

// Handling menu selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_option = isset($_POST['option']) ? $_POST['option'] : 'manage_users';
}

// Prepare SQL statements
$user_stmt = $conn->prepare("SELECT * FROM users");
$mechanic_stmt = $conn->prepare("SELECT * FROM mechanics");
$feedback_stmt = $conn->prepare("SELECT * FROM feedback"); // Prepare feedback statement

// Deletion logic for users and mechanics
if (isset($_POST['delete_user_id'])) {
    $delete_user_id = intval($_POST['delete_user_id']);
    $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_user_stmt->bind_param("i", $delete_user_id);
    $delete_user_stmt->execute();
    echo "<script>alert('User  deleted successfully');</script>";
}

if (isset($_POST['delete_mechanic_id'])) {
    $delete_mechanic_id = intval($_POST['delete_mechanic_id']);
    $delete_mechanic_stmt = $conn->prepare("DELETE FROM mechanics WHERE id = ?");
    $delete_mechanic_stmt->bind_param("i", $delete_mechanic_id);
    $delete_mechanic_stmt->execute();
    echo "<script>alert('Mechanic deleted successfully');</script>";
}

// Function to validate phone number
function is_valid_phone($phone) {
    // Define a regex pattern for valid phone numbers (10 digits)
    return preg_match('/^\d{10}$/', $phone);
}

// Update logic for users and mechanics
if (isset($_POST['edit_user_id'])) {
    $edit_user_id = intval($_POST['edit_user_id']);
    $username = $conn->real_escape_string($_POST['user_username']);
    $phone = $conn->real_escape_string($_POST['user_phone']);

    // Validate phone number
    if (!is_valid_phone($phone)) {
        echo "<script>alert('Invalid phone number. Please enter a valid 10-digit phone number.');</script>";
    } else {
        // Check for existing username or phone in users and mechanics
        $check_user_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR phone = ?) AND id != ?");
        $check_user_stmt->bind_param("ssi", $username, $phone, $edit_user_id);
        $check_user_stmt->execute();
        $check_user_result = $check_user_stmt->get_result();

        $check_mechanic_stmt = $conn->prepare("SELECT id FROM mechanics WHERE phone = ?");
        $check_mechanic_stmt->bind_param("s", $phone);
        $check_mechanic_stmt->execute();
        $check_mechanic_result = $check_mechanic_stmt->get_result();

        if ($check_user_result->num_rows > 0) {
            echo "<script>alert('Username or phone number already exists in users. Please choose a different one.');</script>";
        } elseif ($check_mechanic_result->num_rows > 0) {
            echo "<script>alert('Phone number already exists in mechanics. Please choose a different one.');</script>";
        } else {
            $update_user_stmt = $conn->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
            $update_user_stmt->bind_param("ssi", $username, $phone, $edit_user_id);
            $update_user_stmt->execute();
            echo "<script>alert('User  updated successfully');</script>";
        }
    }
}

if (isset($_POST['edit_mechanic_id'])) {
    $edit_mechanic_id = intval($_POST['edit_mechanic_id']);
    $username = $conn->real_escape_string($_POST['mechanic_username']);
    $phone = $conn->real_escape_string($_POST['mechanic_phone']);
    $languages = $conn->real_escape_string($_POST['mechanic_languages']);
    $location = $conn->real_escape_string($_POST['mechanic_location']);

    // Validate phone number
    if (!is_valid_phone($phone)) {
        echo "<script>alert('Invalid phone number. Please enter a valid 10-digit phone number.');</script>";
    } else {
        // Check for existing username or phone in mechanics and users
        $check_mechanic_stmt = $conn->prepare("SELECT id FROM mechanics WHERE (username = ? OR phone = ?) AND id != ?");
        $check_mechanic_stmt->bind_param("ssi", $username, $phone, $edit_mechanic_id);
        $check_mechanic_stmt->execute();
        $check_mechanic_result = $check_mechanic_stmt->get_result();

        $check_user_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $check_user_stmt->bind_param("s", $phone);
        $check_user_stmt->execute();
        $check_user_result = $check_user_stmt->get_result();

        if ($check_mechanic_result->num_rows > 0) {
            echo "<script>alert('Username or phone number already exists in mechanics. Please choose a different one.');</script>";
        } elseif ($check_user_result->num_rows > 0) {
            echo "<script>alert('Phone number already exists in users. Please choose a different one.');</script>";
        } else {
            $update_mechanic_stmt = $conn->prepare("UPDATE mechanics SET username = ?, phone = ?, languages = ?, location = ? WHERE id = ?");
            $update_mechanic_stmt->bind_param("ssssi", $username, $phone, $languages, $location, $edit_mechanic_id);
            $update_mechanic_stmt->execute();
            echo "<script>alert('Mechanic updated successfully');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f9fc; /* Light gray background */
            font-family: 'Arial', sans-serif; /* Modern font */
        }

        .sidebar {
            display: flex;
            flex-direction: column; /* Stack items vertically */
            justify-content: space-between; /* Space between items */
            width: 250px; /* Fixed width for sidebar */
            background-color: #343a40; /* Dark background for sidebar */
            padding: 20px; /* Padding for sidebar */
            color: white; /* White text color */
            position: fixed; /* Fixed position */
            height: 100%; /* Full height */
        }

        .sidebar h2 {
            text-align: center; /* Centered heading */
            color: #ffffff; /* White color for heading */
        }

        .sidebar a {
            display: block; /* Block display for links */
            color: #ffffff; /* White text color */
            padding: 10px; /* Padding for links */
            text-decoration: none; /* No underline */
            border-radius: 5px; /* Rounded corners */
            margin-bottom: 10px; /* Spacing between links */
            transition: background-color 0.3s; /* Smooth transition for background color */
        }

        .sidebar a:hover {
            background-color: #495057; /* Darker background on hover */
        }

        .sidebar img {
            width: 100%; /* Make logo responsive */
            height: auto; /* Maintain aspect ratio */
            margin-top: auto; /* Push logo to the bottom */
        }

        .content {
            margin-left: 270px; /* Space for sidebar */
            padding: 20px; /* Padding for content */
        }

        .tab-content {
            background-color: #ffffff; /* White background for content */
            border-radius: 15px; /* Rounded corners */
            padding: 20px; /* Padding for content */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Shadow for content */
        }

        h4 {
            margin-top: 0; /* Remove top margin for h4 */
        }

        .btn {
            border-radius: 5px; /* Rounded buttons */
            transition: background-color 0.3s, transform 0.3s; /* Smooth transition for buttons */
        }

        .btn-success {
            background-color: #28a745; /* Green background */
            border: none; /* No border */
        }

        .btn-success:hover {
            background-color: #218838; /* Darker green on hover */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        .btn-danger {
            background-color: #dc3545; /* Red background */
            border: none; /* No border */
        }

        .btn-danger:hover {
            background-color: #c82333; /* Darker red on hover */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        table {
            width: 100%; /* Full width for tables */
            margin-top: 20px; /* Spacing above tables */
            border-collapse: collapse; /* Collapse borders */
        }

        th, td {
            padding: 12px; /* Padding for table cells */
            text-align: left; /* Left align text */
            border-bottom: 1px solid #dee2e6; /* Light border for rows */
        }

        th {
            background-color: #f8f9fa; /* Light gray background for headers */
            color: #495057; /* Darker text color for headers */
        }

        tr:hover {
            background-color: #f1f1f1; /* Light gray background on row hover */
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative; /* Position relative for small screens */
                width: 100%; /* Full width for sidebar on small screens */
                height: auto; /* Auto height for small screens */
            }

            .content {
                margin-left: 0; /* No margin for content on small screens */
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="#" onclick="document.getElementById('manage_users').submit();">Manage Users</a>
        <a href="#" onclick="document.getElementById('manage_mechanics').submit();">Manage Mechanics</a>
        <a href="#" onclick="document.getElementById('view_feedback').submit();">View Feedback</a>
        <img src="car.png" alt="Logo"> <!-- Logo added here at the bottom -->
    </div>

    <div class="content">
        <form id="manage_users" method="POST" action="admin_dashboard.php">
            <input type="hidden" name="option" value="manage_users">
        </form>
        <form id="manage_mechanics" method="POST" action="admin_dashboard.php">
            <input type="hidden" name="option" value="manage_mechanics">
        </form>
        <form id="view_feedback" method="POST" action="admin_dashboard.php">
            <input type="hidden" name="option" value="view_feedback">
        </form>

        <div class="tab-content">
            <?php if ($selected_option): ?>
                <h4><?php echo ucfirst(str_replace('_', ' ', $selected_option)); ?></h4>
                <div class="mt-4">
                    <?php
                    // Manage Users
                    if ($selected_option == 'manage_users') {
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();
                        if ($user_result->num_rows > 0) {
                            echo "<table class='table'>";
                            echo "<thead><tr><th>ID</th><th>Username</th><th>Phone</th><th>Actions</th></tr></thead><tbody>";
                            while ($user = $user_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($user['id']) . "</td>
                                        <td>
                                            <form method='POST'>
                                                <input type='hidden' name='edit_user_id' value='" . htmlspecialchars($user['id']) . "'>
                                                <input type='text' name='user_username' value='" . htmlspecialchars($user['username']) . "' class='form-control' placeholder='Username'>
                                        </td>
                                        <td>
                                                <input type='text' name='user_phone' value='" . htmlspecialchars($user['phone']) . "' class='form-control' placeholder='Phone'>
                                        </td>
                                        <td>
                                                <button type='submit' class='btn btn-success btn-sm'>Update</button>
                                                <button type='submit' name='delete_user_id' value='" . htmlspecialchars($user['id']) . "' class='btn btn-danger btn-sm ml-2'>Delete</button>
                                            </form>
                                        </td>
                                    </tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<p>No users found.</p>";
                        }
                    }
                    // Manage Mechanics
                    elseif ($selected_option == 'manage_mechanics') {
                        $mechanic_stmt->execute();
                        $mechanic_result = $mechanic_stmt->get_result();
                        if ($mechanic_result->num_rows > 0) {
                            echo "<table class='table'>";
                            echo "<thead><tr><th>ID</th><th>Username</th><th>Phone</th><th>Languages</th><th>Location</th><th>Actions</th></tr></thead><tbody>";
                            while ($mechanic = $mechanic_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($mechanic['id']) . "</td>
                                        <td>
                                            <form method='POST'>
                                                <input type='hidden' name='edit_mechanic_id' value='" . htmlspecialchars($mechanic['id']) . "'>
                                                <input type='text' name='mechanic_username' value='" . htmlspecialchars($mechanic['username']) . "' class='form-control' placeholder='Username'>
                                        </td>
                                        <td>
                                                <input type='text' name='mechanic_phone' value='" . htmlspecialchars($mechanic['phone']) . "' class='form-control' placeholder='Phone'>
                                        </td>
                                        <td>
                                                <input type='text' name='mechanic_languages' value='" . htmlspecialchars($mechanic['languages']) . "' class='form-control' placeholder='Languages'>
                                        </td>
                                        <td>
                                                <input type='text' name='mechanic_location' value='" . htmlspecialchars($mechanic['location']) . "' class='form-control' placeholder='Location'>
                                        </td>
                                        <td>
                                                <button type='submit' class='btn btn-success btn-sm'>Update</button>
                                                <button type='submit' name='delete_mechanic_id' value='" . htmlspecialchars($mechanic['id']) . "' class='btn btn-danger btn-sm ml-2'>Delete</button>
                                            </form>
                                        </td>
                                    </tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<p>No mechanics found.</p>";
                        }
                    }
                    // View Feedback
                    elseif ($selected_option == 'view_feedback') {
                        $feedback_stmt->execute();
                        $feedback_result = $feedback_stmt->get_result();
                        if ($feedback_result->num_rows > 0) {
                            echo "<table class='table'>";
                            echo "<thead><tr><th>ID</th><th>Mechanic ID</th><th>Comment</th><th>Rating</th><th>Created At</th></tr></thead><tbody>";
                            while ($feedback = $feedback_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($feedback['id']) . "</td>
                                        <td>" . htmlspecialchars($feedback['mechanic_id']) . "</td>
                                        <td>" . htmlspecialchars($feedback['comment']) . "</td>
                                        <td>" . htmlspecialchars($feedback['rating']) . "</td>
                                        <td>" . htmlspecialchars($feedback['created_at']) . "</td>
                                    </tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<p>No feedback found.</p>";
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>