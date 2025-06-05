<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'vehicle_assistance');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get mechanic ID from URL
$mechanicId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch mechanic details
$mechanic = null;
if ($mechanicId > 0) {
    $stmt = $conn->prepare("SELECT * FROM mechanics WHERE id = ?");
    $stmt->bind_param("i", $mechanicId);
    $stmt->execute();
    $result = $stmt->get_result();
    $mechanic = $result->fetch_assoc();
    $stmt->close();
}

if (!$mechanic) {
    die("Mechanic not found.");
}

// Fetch feedback for the mechanic
$feedbacks = [];
$stmt = $conn->prepare("SELECT * FROM feedback WHERE mechanic_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $mechanicId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}
$stmt->close();

// Fetch request status for the mechanic
$requestStatus = null;
if ($mechanicId > 0) {
    $stmt = $conn->prepare("SELECT status FROM requests WHERE mechanic_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $mechanicId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $requestStatus = $row['status'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <style>
        body {
            background-color: #e9ecef; /* Light gray background */
            font-family: 'Arial', sans-serif; /* Modern font */
        }

        .header {
            background: linear-gradient(to right, #ff6a00, #ee0979); /* Vibrant gradient */
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0; /* Rounded top corners */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4); /* Darker shadow for header */
        }

        .container {
            background-color: #ffffff; /* White background for the container */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            padding: 30px;
            margin-top: 20px; /* Space above the container */
        }

        h1 {
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 2.5rem; /* Larger font size for the title */
        }

        .profile-pic {
            width: 400px; /* Fixed width for 20:20 ratio */
            height: 400px; /* Fixed height for 20:20 ratio */
            border-radius: 10px; /* Rounded corners */
            object-fit: cover; /* Cover the area while maintaining aspect ratio */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Normal shadow for image */
        }

        .card {
            margin-bottom: 20px; /* Space between cards */
            border: none; /* Remove default border */
        }

        .feedback-section {
            margin-top: 30px; /* Space above feedback section */
            padding: 20px;
            border: 1px solid #dee2e6; /* Light border */
            border-radius: 8px; /* Rounded corners */
            background-color: #f8f9fa; /* Light background for feedback */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Normal shadow for feedback section */
            height: auto; /* Allow feedback section to adjust height */
            margin-top: 20px; /* Add margin to create space between details and feedback */
        }

        .feedback-box {
            max-height: 200px; /* Increased max height for scroll */
            overflow-y: auto; /* Enable vertical scroll */
        }

        .feedback-card {
            border-bottom: 1px solid #dee2e6; /* Divider between feedbacks */
            padding: 10px 0; /* Padding for feedback items */
        }

        .feedback-card:last-child {
            border-bottom: none; /* Remove border for last item */
        }

        .rating {
            color: gold;
        }

        .star {
            cursor: pointer;
            font-size: 24px;
            color: gray;
            transition: color 0.3s; /* Smooth color transition */
        }

        .star:hover {
            color: gold; /* Highlight on hover */
        }

        .info-label {
            background-color: #ffffff;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            z-index: 1000;
            margin-top: 10px; /* Space above the label */
        }

        .btn {
            transition: background-color 0.3s, transform 0.3s; /* Smooth transition */
        }

        .btn:hover {
            transform: translateY(-2px); /* Lift effect on hover */
        }

        #map {
            height: 400px; /* Set height for the Leaflet map */
            width: 100%; /* Full width */
            border-radius: 8px; /* Rounded corners */
            margin-top: 20px; /* Space above the map */
        }

        .contact-buttons .btn {
            margin: 5px; /* Space between buttons */
        }

        .btn-sms {
            background-color: #1a73e8; /* Blue for SMS */
            color: white;
        }

        .btn-whatsapp {
            background-color: #25d366; /* Green for WhatsApp */
            color: white;
        }

        .flex-container {
            display: flex; /* Use flexbox for layout */
            align-items: stretch; /* Make items stretch to equal height */
        }

        .flex-item {
            flex: 1; /* Allow items to grow equally */
            margin-right: 15px; /* Space between items */
        }

        .flex-item:last-child {
            margin-right: 0; /* Remove margin for last item */
        }

        .btn:disabled {
            opacity: 0.5; /* Make the button look disabled */
            cursor: not-allowed; /* Change cursor to indicate disabled state */
        }
    </style>
</head>
<body>
<div class="header">
    <h1><?php echo strtoupper($mechanic['name']); ?></h1>
</div>

<div class="container">
    <div class="flex-container">
        <div class="flex-item">
            <div class="card">
                <img src="<?php echo !empty($mechanic['profile_picture']) ? $mechanic['profile_picture'] : 'uploads/default_image.jpg'; ?>" alt="Mechanic Image" class="profile-pic">
                <div class="card-body">
                    <h5 class="card-title">Details</h5>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($mechanic['location']); ?></p>
                    <p><strong>Languages:</strong> <?php echo htmlspecialchars($mechanic['languages']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($mechanic['phone']); ?></p>
                    <p><strong>Rating:</strong> 
                        <span class="rating">
                            <?php 
                            $rating = 4.5; // Example rating, replace with actual rating from database if available
                            for ($i = 0; $i < floor($rating); $i++) {
                                echo '<i class="fas fa-star"></i>';
                            }
                            if ($rating - floor($rating) >= 0.5) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            }
                            for ($i = 0; $i < (5 - ceil($rating)); $i++) {
                                echo '<i class="far fa-star"></i>';
                            }
                            ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex-item">
            <div class="feedback-section">
                <h5>User Feedback</h5>
                <div class="feedback-box">
                    <div id="feedbackList">
                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="feedback-card">
                                <strong>You</strong> (<?php echo htmlspecialchars($feedback['rating']); ?> stars): 
                                <p><?php echo htmlspecialchars($feedback['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div id="map"></div>
            <div class="info-label" id="infoLabel" style="display: none;">
                <span id="distanceLabel">Distance: 0 meters</span> | 
                <span id="timeLabel">Estimated Time: 0 minutes</span>
            </div>
        </div>
    </div>

    <!-- Track Mechanic Button and Leave Feedback Button -->
    <div class="row mt-3">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" id="trackMechanicButton" <?php echo ($requestStatus === 'accepted') ? '' : 'disabled'; ?>>Track Mechanic</button>
            <button class="btn btn-secondary" id="leaveFeedbackButton" data-bs-toggle="modal" data-bs-target="#feedbackModal" <?php echo ($requestStatus === 'accepted') ? '' : 'disabled'; ?>>Leave Feedback</button>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="rating">
                        <span class="star" data-value="1">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="5">&#9733;</span>
                    </div>
                    <textarea id="feedbackComment" class="form-control mt-2" placeholder="Enter your feedback"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="submitFeedback" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Buttons -->
    <div class="row mt-3">
        <div class="col-12 text-center contact-buttons">
            <a href="tel:<?php echo htmlspecialchars($mechanic['phone']); ?>" class="btn btn-primary me-2"><i class="fas fa-phone"></i> Call</a>
            <button class="btn btn-sms" onclick='showVehicleModal("<?php echo htmlspecialchars($mechanic['phone']); ?>", "sms")'><i class="fas fa-sms"></i> SMS</button>
            <button class="btn btn-whatsapp" onclick='showVehicleModal("<?php echo htmlspecialchars($mechanic['phone']); ?>", "whatsapp")'><i class="fab fa-whatsapp"></i> WhatsApp</button>
        </div>
    </div>
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
                <input type="text" id="customIssue" class="form-control mb-3" placeholder="Please specify the issue" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="sendMessageBtn" class="btn btn-primary" disabled>Send</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
<script>
    // Initialize Leaflet map
    const lat = parseFloat(<?php echo json_encode($mechanic['latitude']); ?>);
    const lng = parseFloat(<?php echo json_encode($mechanic['longitude']); ?>);
    const map = L.map('map').setView([lat, lng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Markers for the mechanic and user
    let mechanicMarker;
    let userMarker;
    let routeControl;
    let path; // Variable to hold the path line
    let distanceLabel = document.getElementById('distanceLabel'); // Distance label element
    let timeLabel = document.getElementById('timeLabel'); // Time label element
    let infoLabel = document.getElementById('infoLabel'); // Info label element

    // Get user location
    let userLat, userLng;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            userLat = position.coords.latitude;
            userLng = position.coords.longitude;

            // Set user marker
            userMarker = L.marker([userLat, userLng]).addTo(map)
                .bindPopup('Your Location')
                .openPopup();
        }, () => {
            alert("Unable to retrieve your location.");
        });
    } else {
        alert("Geolocation is not supported by this browser.");
    }

    let tracking = false;
    let trackingInterval;

    document.getElementById('trackMechanicButton').addEventListener('click', function() {
        if (!tracking) {
            tracking = true;
            this.textContent = "Stop Tracking";
            startTracking();
        } else {
            tracking = false;
            this.textContent = "Track Mechanic";
            clearInterval(trackingInterval);
            if (mechanicMarker) {
                map.removeLayer(mechanicMarker);
            }
            if (routeControl) {
                map.removeControl(routeControl);
            }
            if (path) {
                map.removeLayer(path); // Remove the path line
            }
            infoLabel.style.display = 'none'; // Hide info label
        }
    });

    function startTracking() {
        // Initialize mechanic marker
        mechanicMarker = L.marker([lat, lng]).addTo(map)
            .bindPopup('<?php echo htmlspecialchars($mechanic['name']); ?>')
            .openPopup();

        // Draw the path between mechanic and user
        path = L.polyline([[lat, lng], [userLat, userLng]], { color: 'blue' }).addTo(map);
        map.fitBounds(path.getBounds()); // Adjust the map view to fit the path

        // Calculate and display the distance and estimated time
        updateDistanceAndTime(lat, lng, userLat, userLng);

        trackingInterval = setInterval(() => {
            // Simulate fetching new mechanic location
            const newLat = lat + (Math.random() - 0.5) * 0.0001; // Simulate movement
            const newLng = lng + (Math.random() - 0.5) * 0.0001; // Simulate movement

            // Update mechanic marker position
            mechanicMarker.setLatLng([newLat, newLng]);

            // Update path
            path.setLatLngs([[newLat, newLng], [userLat, userLng]]); // Update the path line

            // Update distance and time
            updateDistanceAndTime(newLat, newLng, userLat, userLng);

            lat = newLat; // Update the latitude
            lng = newLng; // Update the longitude
        }, 3000); // Update every 3 seconds
    }

    function updateDistanceAndTime(mechanicLat, mechanicLng, userLat, userLng) {
        const distance = map.distance([mechanicLat, mechanicLng], [userLat, userLng]); // Calculate distance in meters
        distanceLabel.innerHTML = `Distance: ${Math.round(distance)} meters`;

        // Assuming an average speed of 30 km/h (which is 500 meters per minute)
        const averageSpeed = 500; // meters per minute
        const estimatedTime = Math.round(distance / averageSpeed); // Time in minutes
        timeLabel.innerHTML = `Estimated Time: ${estimatedTime} minutes`;

        infoLabel.style.display = 'block'; // Show info label
    }

    function showVehicleModal(phone, mode) {
        selectedPhone = phone;
        selectedMode = mode;
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

    document.getElementById('sendMessageBtn').addEventListener('click', () => {
        const vehicle = document.getElementById('vehicleSelect').value;
        let issue = document.getElementById('issueSelect').value;
        if (issue === "Other") {
            issue = document.getElementById('customIssue').value;
        }
        if (vehicle && issue) {
            const message = `Vehicle: ${vehicle}, Issue: ${issue}. I need assistance.`;
            if (selectedMode === 'sms') {
                window.open(`sms:${selectedPhone}?body=${encodeURIComponent(message)}`);
            } else if (selectedMode === 'whatsapp') {
                const whatsappURL = `https://wa.me/${selectedPhone}?text=${encodeURIComponent(message)}`;
                window.open(whatsappURL, '_blank');
            }
            new bootstrap.Modal(document.getElementById('vehicleModal')).hide();
        }
    });

    // Feedback functionality
    let selectedRating = 0;

    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = this.getAttribute('data-value');
            document.querySelectorAll('.star').forEach(s => {
                s.style.color = s.getAttribute('data-value') <= selectedRating ? 'gold' : 'gray';
            });
        });
    });

    document.getElementById('submitFeedback').addEventListener('click', function() {
        const comment = document.getElementById('feedbackComment').value;
        if (selectedRating > 0 && comment) {
            // AJAX request to submit feedback
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "submit_feedback.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Append feedback to feedback list
                    const feedbackList = document.getElementById('feedbackList');
                    feedbackList.innerHTML += `<div class="feedback-card"><strong>You</strong> (${selectedRating} stars): <p>${comment}</p></div>`;
                    // Close modal
                    $('#feedbackModal').modal('hide');
                    // Reset rating and comment
                    resetFeedbackForm();
                }
            };
            xhr.send(`mechanic_id=<?php echo $mechanicId; ?>&rating=${selectedRating}&comment=${encodeURIComponent(comment)}`);
        } else {
            alert("Please provide a rating and comment.");
        }
    });

    // Reset feedback form
    function resetFeedbackForm() {
        selectedRating = 0;
        document.getElementById('feedbackComment').value = '';
        document.querySelectorAll('.star').forEach(s => {
            s.style.color = 'gray'; // Reset star colors
        });
    }

    // Reset feedback form when modal is opened
    $('#feedbackModal').on('show.bs.modal', function () {
        resetFeedbackForm();
    });
</script>
</body>
</html>