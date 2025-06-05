<?php
// register.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $role = $_POST['role'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $phone = $_POST['phone'];
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $languages = isset($_POST['languages']) ? $_POST['languages'] : null;

    // Location parts
    $location = isset($_POST['location']) ? $_POST['location'] : null; // New field for location
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null; // New field for latitude
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null; // New field for longitude

    $profilePicture = null; // Default profile picture value

    // Handle profile picture upload for mechanic
    if ($role === 'mechanic' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/"; // Directory to save the uploaded images
        $targetFile = $targetDir . basename($_FILES['profile_picture']['name']);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is a valid image type
        $validImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $validImageTypes)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicture = $targetFile; // Store the file path in the database
            } else {
                echo "<script>alert('Error uploading profile picture.');</script>";
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, PNG, and GIF images are allowed.');</script>";
        }
    }

    // Phone number validation (example: basic validation for 10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        echo "<script>alert('Invalid phone number. Please enter a valid 10-digit phone number.');</script>";
    } else {
        // Check if the username already exists
        $table = ($role === 'user') ? 'users' : ($role === 'mechanic' ? 'mechanics' : 'admins');
        $checkUsernameSql = "SELECT * FROM $table WHERE username = '$username'";
        $usernameResult = $conn->query($checkUsernameSql);

        if ($usernameResult->num_rows > 0) {
            echo "<script>alert('Username already taken. Please choose a different one.');</script>";
        } else {
            // Check if the phone number already exists
            $checkPhoneSql = "
                SELECT 1 FROM users WHERE phone = '$phone'
                UNION
                SELECT 1 FROM mechanics WHERE phone = '$phone'
                UNION
                SELECT 1 FROM admins WHERE phone = '$phone'";
            $phoneResult = $conn->query($checkPhoneSql);

            if ($phoneResult->num_rows > 0) {
                echo "<script>alert('Phone number already registered. Please use a different phone number.');</script>";
            } else {
                // Insert into database based on role
                if ($role === 'user') {
                    $sql = "INSERT INTO users (username, password, phone) 
                            VALUES ('$username', '$password', '$phone')";
                } elseif ($role === 'mechanic') {
                    $sql = "INSERT INTO mechanics (username, password, phone, languages, name, location, latitude, longitude, profile_picture) 
                            VALUES ('$username', '$password', '$phone', '$languages', '$name', '$location', '$latitude', '$longitude', '$profilePicture')";
                } elseif ($role === 'admin') {
                    $sql = "INSERT INTO admins (username, password, phone) VALUES ('$username', '$password', '$phone')";
                }

                if ($conn->query($sql) === TRUE) {
                    if ($role === 'mechanic') {
                        header("Location: mechanic_login.php"); // Redirect to mechanic login
                    } elseif ($role === 'admin') {
                        header("Location: admin_login.php"); // Redirect to admin login
                    } else {
                        header("Location: user_login.php"); // Redirect to user login
                    }
                    exit(); // Ensure no further code is executed
                } else {
                    echo "<script>alert('Error: " . $conn->error . "');</script>";
                }
            }
        }
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
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        body {
            background-color: #f7f9fc;
        }
        .form-container {
            margin-top: 50px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .form-control {
            border-radius: 5px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-title {
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
            color: #333;
        }
        #map {
            height: 300px; /* Set the height of the map */
            margin-bottom: 20px; /* Space below the map */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 class="form-title">Register</h2>
                    <form method="POST" action="register.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="role">Register as:</label>
                            <select class="form-control" id="role" name="role" required onchange="toggleFields()">
                                <option value="user">Customer</option>
                                <option value="mechanic">Mechanic</option>
                                <option value="admin">Admin</option> <!-- Added Admin option back -->
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                        </div>

                        <div class="form-group" id="fullNameGroup" style="display: none;">
                            <label for="name">Full Name:</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name">
                        </div>

                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
                        </div>

                        <div class="form-group" id="languagesGroup" style="display: none;">
                            <label for="languages">Languages:</label>
                            <input type="text" class="form-control" id="languages" name="languages" placeholder="Enter languages you speak (e.g., Telugu, Tamil)">
                        </div>

                        <div class="form-group" id="locationGroup" style="display: none;">
                            <label for="location">Selected Location:</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="Selected location will appear here" readonly>
                        </div>

                        <div class="form-group" id="mapGroup" style="display: none;">
                            <label for="map">Select Location:</label>
                            <div id="map"></div>
                            <input type="hidden" id="latitude" name="latitude" required>
                            <input type="hidden" id="longitude" name="longitude" required>
                        </div>

                        <div class="form-group" id="profilePictureGroup" style="display: none;">
                            <label for="profile_picture">Profile Picture:</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <!-- JavaScript to handle field visibility and map functionality -->
    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            const fullNameGroup = document.getElementById('fullNameGroup');
            const languagesGroup = document.getElementById('languagesGroup');
            const locationGroup = document.getElementById('locationGroup');
            const mapGroup = document.getElementById('mapGroup');
            const profilePictureGroup = document.getElementById('profilePictureGroup');

            // Show or hide fields based on the role selected
            if (role === 'mechanic') {
                fullNameGroup.style.display = 'block';
                languagesGroup.style.display = 'block';
                locationGroup.style.display = 'block'; // Show location fields for mechanics
                mapGroup.style.display = 'block'; // Show map for mechanics
                profilePictureGroup.style.display = 'block'; // Show profile picture field for mechanics
                document.getElementById('name').required = true; // Required for mechanics
                document.getElementById('languages').required = true; // Required for mechanics
            } else if (role === 'user') {
                fullNameGroup.style.display = 'none';
                languagesGroup.style.display = 'none';
                locationGroup.style.display = 'none';
                mapGroup.style.display = 'none';
                profilePictureGroup.style.display = 'none';
            } else if (role === 'admin') {
                fullNameGroup.style.display = 'none'; // Admins do not need full name
                languagesGroup.style.display = 'none'; // Admins do not need languages
                locationGroup.style.display = 'none'; // Admins do not need location
                mapGroup.style.display = 'none'; // Admins do not need map
                profilePictureGroup.style.display = 'none'; // Admins do not need profile picture
            }
        }

        // Initialize the map
        function initMap() {
            const map = L.map('map').setView([20.5937, 78.9629], 5); // Default view over India

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const marker = L.marker([20.5937, 78.9629]).addTo(map); // Default marker position

            // Update latitude and longitude on marker drag
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat;
                document.getElementById('longitude').value = position.lng;
                document.getElementById('location').value = position.lat + ', ' + position.lng; // Update location text box
            });

            // Set marker position on map click
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
                document.getElementById('location').value = e.latlng.lat + ', ' + e.latlng.lng; // Update location text box
            });

            // Add geocoder control
            const geocoder = L.Control.geocoder({
                defaultMarkGeocode: true
            }).addTo(map);

            // Handle geocode result
            geocoder.on('markgeocode', function(e) {
                const result = e.geocode;
                marker.setLatLng(result.center);
                map.setView(result.center, 13);
                document.getElementById('latitude').value = result.center.lat;
                document.getElementById('longitude').value = result.center.lng;
                document.getElementById('location').value = result.name; // Update location text box with the name
            });
        }

        // Run the function once when the page loads to set the initial state
        window.onload = function() {
            toggleFields();
            initMap(); // Initialize the map
        };
    </script>
</body>
</html>