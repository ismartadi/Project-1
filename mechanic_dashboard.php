
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
        }
        .status {
            margin-bottom: 20px;
        }
        .user-request {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Mechanic Dashboard</h1>

        <!-- Status Update -->
        <div class="row">
            <div class="col-md-6 offset-md-3 text-center">
                <h3>Update Your Status</h3>
                <form method="POST" action="mechanic_dashboard.php">
                    <div class="btn-group" role="group" aria-label="Status">
                        <button type="submit" name="status" value="free" class="btn btn-success">Free</button>
                        <button type="submit" name="status" value="busy" class="btn btn-danger">Busy</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Incoming User Requests -->
        <div class="row">
            <div class="col-md-12">
                <h3>Incoming User Requests</h3>
                <ul class="list-group">
                    <!-- Replace this section with PHP to fetch user requests -->
                    <li class="list-group-item user-request">
                        <strong>User:</strong> Alex Johnson<br>
                        <strong>Location:</strong> Central Park<br>
                        <a href="callto:+123456789" class="btn btn-primary">Call User</a>
                        <a href="#" class="btn btn-warning">View Location</a>
                    </li>
                    <li class="list-group-item user-request">
                        <strong>User:</strong> Emma Watson<br>
                        <strong>Location:</strong> Times Square<br>
                        <a href="callto:+987654321" class="btn btn-primary">Call User</a>
                        <a href="#" class="btn btn-warning">View Location</a>
                    </li>
                    <!-- End of User Requests -->
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
