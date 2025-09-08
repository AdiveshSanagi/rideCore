<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Check if user is a customer
if($_SESSION["user_type"] !== "customer") {
    header("location: ../index.php");
    exit;
}

// Check if booking ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: bookings.php?error=invalid_request");
    exit;
}

// Include database connection and models
include_once '../config/database.php';
include_once '../models/Booking.php';
include_once '../models/Vehicle.php';
include_once '../models/User.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize booking object
$booking = new Booking($db);
$booking->booking_id = $_GET["id"];

// Get booking details
$booking_details = $booking->getById();

// Check if booking exists
if(!$booking_details) {
    header("location: bookings.php?error=not_found");
    exit;
}

// Check if the booking belongs to the current customer
if($booking->customer_id != $_SESSION["user_id"]) {
    header("location: bookings.php?error=unauthorized");
    exit;
}

// Initialize vehicle object
$vehicle = new Vehicle($db);
$vehicle->vehicle_id = $booking->vehicle_id;
$vehicle->getById();

// Initialize provider object
$provider = new User($db);
$provider->user_id = $vehicle->provider_id;
$provider->getById();

// Format dates for display
$start_time = date('M d, Y h:i A', strtotime($booking->start_time));
$end_time = date('M d, Y h:i A', strtotime($booking->end_time));

// Calculate booking duration in hours
$start = new DateTime($booking->start_time);
$end = new DateTime($booking->end_time);
$interval = $start->diff($end);
$hours = $interval->h + ($interval->days * 24);
$minutes = $interval->i;
$duration = $hours . " hour" . ($hours != 1 ? "s" : "");
if($minutes > 0) {
    $duration .= " " . $minutes . " minute" . ($minutes != 1 ? "s" : "");
}

// Get status badge
$status_badge = '';
switch($booking->status) {
    case 'pending':
        $status_badge = '<span class="badge bg-warning">Pending</span>';
        break;
    case 'accepted':
        $status_badge = '<span class="badge bg-success">Accepted</span>';
        break;
    case 'rejected':
        $status_badge = '<span class="badge bg-danger">Rejected</span>';
        break;
    case 'completed':
        $status_badge = '<span class="badge bg-primary">Completed</span>';
        break;
    case 'cancelled':
        $status_badge = '<span class="badge bg-secondary">Cancelled</span>';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <span class="text-primary">rideCore</span> Vehicle Booking
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">Search Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">My Bookings</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Booking Details Content -->
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="bookings.php">My Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Booking #<?php echo $booking->booking_id; ?></li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Booking Details <?php echo $status_badge; ?></h2>
                    <div>
                        <a href="bookings.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a>
                        <?php if($booking->status == 'pending'): ?>
                        <a href="cancel_booking.php?id=<?php echo $booking->booking_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">
                            <i class="fas fa-times"></i> Cancel Booking
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Booking Information -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Booking Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Booking ID:</strong> #<?php echo $booking->booking_id; ?></p>
                                <p><strong>Status:</strong> <?php echo $status_badge; ?></p>
                                <p><strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($booking->created_at)); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking->total_amount, 2); ?></p>
                                <p><strong>Duration:</strong> <?php echo $duration; ?></p>
                                <p><strong>Last Updated:</strong> <?php echo date('M d, Y h:i A', strtotime($booking->updated_at)); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="fw-bold">Schedule</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-primary text-white me-3">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0"><strong>Start Time:</strong> <?php echo $start_time; ?></p>
                                        <p class="mb-0"><strong>End Time:</strong> <?php echo $end_time; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="fw-bold">Locations</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-success text-white me-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0"><strong>Pickup Location:</strong> <?php echo htmlspecialchars($booking->pickup_location); ?></p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-danger text-white me-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0"><strong>Dropoff Location:</strong> <?php echo htmlspecialchars($booking->dropoff_location); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider and Vehicle Information -->
            <div class="col-md-4">
                <!-- Provider Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Service Provider</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-info text-white me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                               <h6 class="mb-0"><?php echo htmlspecialchars($provider->username); ?></h6>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:<?php echo htmlspecialchars($provider->email); ?>">
                                            <?php echo htmlspecialchars($provider->email); ?>
                                        </a>
                                    </p>

                            </div>
                        </div>
                       <!-- <a href="message.php?user=<?php echo $provider->user_id; ?>&booking=<?php echo $booking->booking_id; ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-comment"></i> Message Provider
                        </a>-->
                    </div>
                </div>

                <!-- Vehicle Information -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($vehicle->image) && file_exists("../" . $vehicle->image)): ?>
                            <img src="../<?php echo $vehicle->image; ?>" class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($vehicle->vehicle_name); ?>">
                        <?php else: ?>
                            <img src="../assets/images/vehicle-placeholder.jpg" class="img-fluid rounded mb-3" alt="Vehicle Placeholder">
                        <?php endif; ?>
                        <h5><?php echo htmlspecialchars($vehicle->vehicle_name); ?></h5>
                        <p class="mb-1"><strong>Type:</strong> <?php echo htmlspecialchars($vehicle->vehicle_type); ?></p>
                        <p class="mb-1"><strong>Model:</strong> <?php echo htmlspecialchars($vehicle->model); ?> (<?php echo $vehicle->year; ?>)</p>
                        <p class="mb-1"><strong>License Plate:</strong> <?php echo htmlspecialchars($vehicle->license_plate); ?></p>
                        <p class="mb-1"><strong>Capacity:</strong> <?php echo $vehicle->capacity; ?> passengers</p>
                        <p><strong>Rate:</strong>  ₹<?php echo number_format($vehicle->rate_per_hour, 2); ?> per hour</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Savion Vehicle Booking</h5>
                    <p>Making vehicle booking simple and convenient.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Contact</h5>
                    <p>Email: support@rideCore.com<br>Phone: (123) 456-7890</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2025 rideCore Vehicle Booking. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>