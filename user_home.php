<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$userLatitude = null;
$userLongitude = null;
$searchLocation = '';
$searchLanguage = '';

// Check if the user has allowed location access
if (isset($_GET['latitude']) && isset($_GET['longitude'])) {
    $userLatitude = floatval($_GET['latitude']);
    $userLongitude = floatval($_GET['longitude']);
}

// Base query for fetching free mechanics
$sql = "SELECT * FROM mechanics WHERE status = 'free'";

// Check if a search location or language is provided
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $searchLocation = $conn->real_escape_string(trim($_GET['location']));
    $sql .= " AND location LIKE '%$searchLocation%'";
}

if (isset($_GET['languages']) && !empty($_GET['languages'])) {
    $searchLanguage = $conn->real_escape_string(trim($_GET['languages']));
    $sql .= " AND languages LIKE '%$searchLanguage%'";
}

// Execute the query
$result = $conn->query($sql);

// Array to hold nearby mechanics
$nearbyMechanics = [];

// Calculate distance and filter mechanics
if ($result->num_rows > 0 && $userLatitude !== null && $userLongitude !== null) {
    while ($mechanic = $result->fetch_assoc()) {
        $mechanicLatitude = floatval($mechanic['latitude']);
        $mechanicLongitude = floatval($mechanic['longitude']);

        // Calculate distance using Haversine formula
        $earthRadius = 6371; // Earth radius in kilometers
        $dLat = deg2rad($mechanicLatitude - $userLatitude);
        $dLon = deg2rad($mechanicLongitude - $userLongitude);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($userLatitude)) * cos(deg2rad($mechanicLatitude)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Distance in kilometers

        // Check if the mechanic is within 3 km
        if ($distance <= 3) {
            $nearbyMechanics[] = $mechanic;
        }
    }
}

// Fetch other mechanics from different locations
$otherMechanics = [];
if ($result->num_rows > 0) {
    // Reset the result pointer to the beginning
    $result->data_seek(0);
    while ($mechanic = $result->fetch_assoc()) {
        if (!in_array($mechanic, $nearbyMechanics)) {
            $otherMechanics[] = $mechanic; // Add to other mechanics if not in nearby
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Assistance - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 80px; /* Add padding to prevent content from being hidden behind the fixed footer */
            background-color: #f4f4f4;
        }
        .hero {
            background-image: url('ban.jpg'); /* Your banner image */
            background-size: cover;
            background-position: center;
            color: white;
            padding: 20px 0;
            text-align: center;
            position: relative;
        }
        .hero h1, .hero p {
            position: relative;
            z-index: 2; /* Bring text to the front */
        }
        .hero-image {
            width: 250px;
            height: auto;
            position: absolute;
            left: 350px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1; /* Send image to the back */
            opacity: 0.8; /* Optional: Add transparency for better readability */
        }
        .mechanic-card {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            transition: transform 0.2s;
        }
        .mechanic-card:hover {
            transform: scale(1.02);
        }
        .mechanic-details {
            flex: 1;
        }
        .mechanic-photo {
            width: 140px;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-left: 15px;
            border: 1px solid #ddd;
        }
        .btn-whatsapp {
            background-color: #25d366;
            color: white;
        }
        .btn-sms {
            background-color: #1a73e8;
            color: white;
        }
        .mt-4 {
            margin-top: 1.5rem; /* Add margin-top for spacing */
        }
    </style>
</head>
<body>
<div class="hero">
    <div class="container text-center">
        <img src="car.png" alt="Car" class="hero-image">
        <h1>Find a Mechanic Near You</h1>
        <p>Your trusted partner for vehicle assistance</p>
    </div>
</div>

<div class="container mt-4">
    <!-- Search form -->
    <div class="row mb-3">
        <div class="col-md-6 offset-md-3">
            <form method="GET" action="user_home.php">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search by location" name="location" value="<?php echo htmlspecialchars($searchLocation); ?>">
                    <input type="text" class="form-control" placeholder="Search by language" name="languages" value="<?php echo htmlspecialchars($searchLanguage); ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Location Access Prompt -->
    <div class="row">
        <div class="col-md-6 offset-md-3 text-center">
            <button class="btn btn-primary" id="getLocationBtn">Allow Location Access</button>
        </div>
    </div>

    <!-- Nearby Mechanic Results -->
    <div class="row mt-4">
        <h2>Nearby Mechanics</h2>
        <?php
        if (count($nearbyMechanics) > 0) {
            foreach ($nearbyMechanics as $mechanic) {
                echo "<div class='col-md-4'>";
                echo "<div class='mechanic-card d-flex'>";

                // Mechanic details
                echo "<div class='mechanic-details'>";
                echo "<h5><b>" . strtoupper($mechanic['name']) . "</b></h5>";
                echo "<p><b>Location:</b> " . htmlspecialchars($mechanic['location']) . "</p>";
                echo "<p><b>Language:</b> " . htmlspecialchars($mechanic['languages']) . "</p>";
                echo "<p><b>Contact:</b> " . htmlspecialchars($mechanic['phone']) . "</p>";

                // Buttons with icons
                echo "<a href='tel:" . htmlspecialchars($mechanic['phone']) . "' class='btn btn-primary btn-sm'><i class='fas fa-phone'></i> Call</a> ";
                echo "<button class='btn btn-sms btn-sm' onclick='showVehicleModal(\"" . htmlspecialchars($mechanic['id']) . "\", \"" . htmlspecialchars($mechanic['phone']) . "\", \"sms\")'><i class='fas fa-sms'></i> SMS</button> ";
                echo "<button class='btn btn-whatsapp btn-sm' onclick='showVehicleModal(\"" . htmlspecialchars($mechanic['id']) . "\", \"" . htmlspecialchars($mechanic['phone']) . "\", \"whatsapp\")'><i class='fab fa-whatsapp'></i> WhatsApp</button>";
                
                // Details button
                echo "<a href='mechanic_details.php?id=" . htmlspecialchars($mechanic['id']) . "' class='btn btn-info btn-sm mt-2'>Details</a>";
                
                echo "</div>";

                // Mechanic photo
                $profilePicture = !empty($mechanic['profile_picture']) ? $mechanic['profile_picture'] : 'uploads/default_image.jpg';
                echo "<img src='$profilePicture' alt='Mechanic Photo' class='mechanic-photo'>";

                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No nearby mechanics found.</p>";
        }
        ?>
    </div>

    <!-- Other Mechanics Results -->
    <div class="row mt-4">
        <h2>Other Mechanics</h2>
        <?php
        if (count($otherMechanics) > 0) {
            foreach ($otherMechanics as $mechanic) {
                echo "<div class='col-md-4'>";
                echo "<div class='mechanic-card d-flex'>";

                // Mechanic details
                echo "<div class='mechanic-details'>";
                echo "<h5><b>" . strtoupper($mechanic['name']) . "</b></h5>";
                echo "<p><b>Location:</b> " . htmlspecialchars($mechanic['location']) . "</p>";
                echo "<p><b>Language:</b> " . htmlspecialchars($mechanic['languages']) . "</p>";
                echo "<p><b>Contact:</b> " . htmlspecialchars($mechanic['phone']) . "</p>";

                // Buttons with icons
                echo "<a href='tel:" . htmlspecialchars($mechanic['phone']) . "' class='btn btn-primary btn-sm'><i class='fas fa-phone'></i> Call</a> ";
                echo "<button class='btn btn-sms btn-sm' onclick='showVehicleModal(\"" . htmlspecialchars($mechanic['id']) . "\", \"" . htmlspecialchars($mechanic['phone']) . "\", \"sms\")'><i class='fas fa-sms'></i> SMS</button> ";
                echo "<button class='btn btn-whatsapp btn-sm' onclick='showVehicleModal(\"" . htmlspecialchars($mechanic['id']) . "\", \"" . htmlspecialchars($mechanic['phone']) . "\", \"whatsapp\")'><i class='fab fa-whatsapp'></i> WhatsApp</button> ";
                
                // Details button
                echo "<a href='mechanic_details.php?id=" . htmlspecialchars($mechanic['id']) . "' class='btn btn-info btn-sm mt-2'>Details</a>";
                
                echo "</div>";

                // Mechanic photo
                $profilePicture = !empty($mechanic['profile_picture']) ? $mechanic['profile_picture'] : 'uploads/default_image.jpg';
                echo "<img src='$profilePicture' alt='Mechanic Photo' class='mechanic-photo'>";

                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No other mechanics found.</p>";
        }
        ?>
    </div>
</div>

<!-- Customer Service Section -->
<div class="container text-center mt-4">
    <h5>Customer Service: <b>XXXXXXXXXX</b></h5>
    <p>If you need assistance, please contact us.</p>
    <button class="btn btn-primary" onclick="alert('Contact functionality not implemented yet.');">Contact Now</button>
</div>

<!-- Modal for selecting vehicle and issue -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Vehicle and Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select id="vehicleSelect" class="form-select mb-3" onchange="loadIssues()">
                    <option value="">Select Vehicle</option>
                    <option value="Car">Car</option>
                    <option value="Bike">Bike</option>
                    <option value="Truck">Truck</option>
                </select>
                <select id="issueSelect" class="form-select mb-3" onchange="checkCustomIssue()" disabled>
                    <option value="">Select Issue</option>
                </select>
                <input type="text" id="customIssue" class="form-control mb-3" placeholder="Please specify the issue" style="display:none;" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="sendMessageBtn" class="btn btn-primary" disabled onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedPhone = '';
    let selectedMechanicId = '';
    let userLatitude = <?php echo json_encode($userLatitude); ?>; // Get latitude from PHP
    let userLongitude = <?php echo json_encode($userLongitude); ?>; // Get longitude from PHP

    document.getElementById('getLocationBtn').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userLatitude = position.coords.latitude;
                userLongitude = position.coords.longitude;

                // Redirect to the same page with latitude and longitude as query parameters
                window.location.href = `user_home.php?latitude=${userLatitude}&longitude=${userLongitude}`;
            }, function() {
                alert('Unable to retrieve your location. Please enable location services.');
            });
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    });

    function showVehicleModal(mechanicId, phone, mode) {
        selectedPhone = phone;
        selectedMechanicId = mechanicId; // Store the mechanic ID
        document.getElementById('vehicleSelect').value = '';
        document.getElementById('issueSelect').innerHTML = '<option value="">Select Issue</option>';
        document.getElementById('issueSelect').disabled = true;
        document.getElementById('customIssue').style.display = 'none';
        document.getElementById('sendMessageBtn').disabled = true;

        new bootstrap.Modal(document.getElementById('vehicleModal')).show();
    }

    function loadIssues() {
        const vehicle = document.getElementById('vehicleSelect').value;
        const issueSelect = document.getElementById('issueSelect');
        const issues = {
            Car: ['Engine Issue', 'Brake Problem', 'Flat Tire'],
            Bike: ['Chain Issue', 'Brake Problem', 'Engine Problem'],
            Truck: ['Overheating', 'Brake Failure', 'Flat Tire']
        };
        issueSelect.innerHTML = '<option value="">Select Issue</option>';
        if (vehicle && issues[vehicle]) {
            issues[vehicle].forEach(issue => {
                const option = document.createElement('option');
                option.value = issue;
                option.textContent = issue;
                issueSelect.appendChild(option);
            });
            issueSelect.appendChild(new Option('Other', 'Other')); // Add 'Other' option
            issueSelect.disabled = false;
        } else {
            issueSelect.disabled = true;
        }
        document.getElementById('sendMessageBtn').disabled = true;
    }

    function checkCustomIssue() {
        const issue = document.getElementById('issueSelect').value;
        if (issue === "Other") {
            document.getElementById('customIssue').style.display = 'block';
        } else {
            document.getElementById('customIssue').style.display = 'none';
        }
        document.getElementById('sendMessageBtn').disabled = !issue && !document.getElementById('customIssue').value;
    }

    function sendMessage() {
        const vehicle = document.getElementById('vehicleSelect').value;
        let issue = document.getElementById('issueSelect').value;
        if (issue === "Other") {
            issue = document.getElementById('customIssue').value;
        }

        if (vehicle && issue) {
            const requestData = {
                vehicle: vehicle,
                issue: issue,
                phone: selectedPhone,
                user_latitude: userLatitude,
                user_longitude: userLongitude,
                mechanic_id: selectedMechanicId // Use the mechanic ID
            };

            // Send the request to create_request.php
            fetch('create_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request sent successfully!');

                    // Construct the WhatsApp message
                    const message = `Vehicle: ${vehicle}, Issue: ${issue}. Location: https://maps.google.com/?q=${userLatitude},${userLongitude}. I need assistance.`;
                    
                    // Ensure the phone number is in the correct format
                    const formattedPhone = selectedPhone.replace(/\D/g, ''); // Remove non-numeric characters
                    const whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;
                    
                    // Open WhatsApp with the message
                    window.open(whatsappUrl, '_blank');

                    // Optionally, close the modal
                    bootstrap.Modal.getInstance(document.getElementById('vehicleModal')).hide();
                } else {
                    alert('Failed to send request. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        } else {
            alert("Please select a vehicle and issue before sending.");
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>