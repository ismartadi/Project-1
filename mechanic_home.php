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

// Fetch mechanic details
$username = $_SESSION['username'];
$sql = "SELECT * FROM mechanics WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$mechanic = $result->fetch_assoc();

// Safely access mechanic details
$location = $mechanic['location'] ?? '';
$languages = $mechanic['languages'] ?? '';
$latitude = $mechanic['latitude'] ?? '';
$longitude = $mechanic['longitude'] ?? '';
$name = $mechanic['name'] ?? ''; // Fetch the name
$profile_picture = $mechanic['profile_picture'] ?? 'default.png'; // Default profile picture

// Update profile picture and details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_picture'])) {
        // Handle profile picture update
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/"; // Directory to save the uploaded images
            $targetFile = $targetDir . basename($_FILES['profile_picture']['name']);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Check if the file is a valid image type
            $validImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $validImageTypes)) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    // Update the profile picture in the database
                    $stmt = $conn->prepare("UPDATE mechanics SET profile_picture = ? WHERE id = ?");
                    $stmt->bind_param("si", $targetFile, $mechanic['id']);
                    $stmt->execute();
                    $profile_picture = $targetFile; // Update the local variable to reflect the new picture
                } else {
                    echo "<script>alert('Error uploading profile picture.');</script>";
                }
            } else {
                echo "<script>alert('Only JPG, JPEG, PNG, and GIF images are allowed.');</script>";
            }
        }
    } elseif (isset($_POST['update_details'])) {
        // Handle details update
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $languages = $_POST['languages'];
        $location = $_POST['location'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $status = $_POST['status']; // Get the selected status

        // Update the mechanic's details in the database
        $stmt = $conn->prepare("UPDATE mechanics SET name = ?, phone = ?, languages = ?, location = ?, latitude = ?, longitude = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $name, $phone, $languages, $location, $latitude, $longitude, $status, $mechanic['id']);
        $stmt->execute();
    }
    header("Location: mechanic_home.php"); // Redirect to the same page to see updated profile
    exit();
}

// Fetch user requests for the logged-in mechanic
$sql_requests = "SELECT * FROM requests WHERE mechanic_id = ? AND status = 'pending'";
$stmt_requests = $conn->prepare($sql_requests);
$stmt_requests->bind_param("i", $mechanic['id']);
$stmt_requests->execute();
$result_requests = $stmt_requests->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            background-color: #f7f9fc;
            padding: 20px;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 1200px; /* Limit the width of the container */
            margin: auto; /* Center the container */
        }

        .window {
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px; /* Add space between windows */
        }

        .profile-pic-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #007bff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            object-fit: cover;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s; /* Smooth transition */
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        #map {
            height: 300px;
            margin-top: 20px;
        }

        .table {
            width: 100%; /* Full width for the table */
            border-collapse: collapse; /* Remove space between borders */
        }

        .table th, .table td {
            border: 1px solid #dee2e6; /* Add border to table cells */
            padding: 10px; /* Add padding for better spacing */
            text-align: left; /* Align text to the left */
        }

        .table tr:nth-child(even) {
            background-color: #f2f2f2; /* Alternate row color */
        }
    </style>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
        let marker;

        function initMap() {
            const initialLocation = [<?php echo $latitude; ?>, <?php echo $longitude; ?>]; // Use mechanic's current location
            map = L.map('map').setView(initialLocation, 15); // Set the view to the mechanic's location

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            marker = L.marker(initialLocation, { draggable: true }).addTo(map); // Create a draggable marker

            // Update latitude and longitude on marker drag
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat;
                document.getElementById('longitude').value = position.lng;
            });

            // Set marker position on map click
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
            });
        }
    </script>
</head>
<body onload="initMap()">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="window">
                    <h4>Profile Picture</h4>
                    <div class="profile-pic-container">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
                    </div>
                    <form method="POST" action="mechanic_home.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="profile_picture">Choose Profile Picture:</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" required>
                        </div>
                        <button type="submit" name="update_picture" class="btn btn-primary btn-block mt-3">Update Picture</button>
                    </form>
                </div>

                <div class="window">
                    <h4>Update Details</h4>
                    <form method="POST" action="mechanic_home.php">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">Name:</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phone">Phone Number:</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($mechanic['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="languages">Languages:</label>
                                <input type="text" class="form-control" id="languages" name="languages" value="<?php echo htmlspecialchars($languages); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Select Availability:</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="free" <?php if ($mechanic['status'] === 'free') echo 'selected'; ?>>Available</option>
                                    <option value="busy" <?php if ($mechanic['status'] === 'busy') echo 'selected'; ?>>Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="location">Selected Location:</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" readonly>
                            <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($latitude); ?>" required>
                            <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($longitude); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="map">Select Location:</label>
                            <div id="map"></div>
                        </div>
                        <button type="submit" name="update_details" class="btn btn-primary btn-block mt-3">Update Details</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="window">
                    <h4>User Requests</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle Type</th>
                                <th>Issue</th>
                                <th>Phone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_requests->num_rows > 0): ?>
                                <?php while ($request = $result_requests->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['vehicle_type']); ?></td>
                                    <td><?php echo htmlspecialchars($request['issue_description']); ?></td>
                                    <td><?php echo htmlspecialchars($request['phone']); ?></td>
                                    <td>
                                        <form method="POST" action="handle_request.php">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" name="action" value="accept" class="btn btn-success">Accept</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No pending requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>