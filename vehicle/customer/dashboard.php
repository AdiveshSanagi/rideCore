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

// Include database connection
include_once '../config/database.php';
include_once '../models/Booking.php';
include_once '../models/Vehicle.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize booking object
$booking = new Booking($db);
$vehicle = new Vehicle($db);

// Set customer ID
$booking->customer_id = $_SESSION["user_id"];

// Get active bookings
$active_bookings = $booking->getActiveBookingsByCustomer();

// Get past bookings
$past_bookings = $booking->getPastBookingsByCustomer();

// Get available vehicles
$available_vehicles = $vehicle->getAvailableVehicles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - rideCore Vehicle Booking</title>
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">My Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                            <a class="nav-link" href="customer_inbox.php">
                                <i class="fas fa-envelope"></i> Messages
                            </a>
                        </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION["username"]); ?>
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

    <!-- Dashboard Content -->
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Find and book vehicles for your needs. Track your bookings and manage your account.
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-car fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Book a Vehicle</h5>
                        <p class="card-text">Search and book from our wide range of vehicles.</p>
                        <a href="../vehicles.php" class="btn btn-primary">Find Vehicles</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">My Bookings</h5>
                        <p class="card-text">View and manage your current and past bookings.</p>
                        <a href="bookings.php" class="btn btn-primary">View Bookings</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-user fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">My Profile</h5>
                        <p class="card-text">Update your profile information and preferences.</p>
                        <a href="profile.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Bookings -->
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Active Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($active_bookings)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($active_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($booking['start_time'])); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($booking['end_time'])); ?></td>
                                                <td>
                                                    <?php if($booking['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif($booking['status'] == 'accepted'): ?>
                                                        <span class="badge bg-success">Accepted</span>
                                                    <?php elseif($booking['status'] == 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                <td>
                                                    <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-info">View</a>
                                                    <?php if($booking['status'] == 'pending'): ?>
                                                        <a href="cancel_booking.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center mb-0">You have no active bookings.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Available Vehicles -->
        <div class="row">
            <div class="col-md-12">
                <h3 class="mb-4">Available Vehicles</h3>
                <div class="row">
                    <?php if(!empty($available_vehicles)): ?>
                        <?php foreach($available_vehicles as $vehicle): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <?php if(!empty($vehicle['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($vehicle['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>">
                                    <?php else: ?>
                                        <img src="../assets/images/vehicle-placeholder.jpg" class="card-img-top" alt="Vehicle Placeholder">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($vehicle['vehicle_name']); ?></h5>
                                        <p class="card-text">
                                            <strong>Type:</strong> <?php echo htmlspecialchars($vehicle['vehicle_type']); ?><br>
                                            <strong>Model:</strong> <?php echo htmlspecialchars($vehicle['model']); ?> (<?php echo $vehicle['year']; ?>)<br>
                                            <strong>Capacity:</strong> <?php echo $vehicle['capacity']; ?> persons<br>
                                            <strong>Rate:</strong>  ₹<?php echo number_format($vehicle['rate_per_hour'], 2); ?> per hour
                                        </p>
                                    </div>
                                    <div class="card-footer">
                                        <a href="../book_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-primary w-100">Book Now</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center">No vehicles available at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>rideCore Vehicle Booking</h5>
                    <p>Making vehicle booking simple and convenient.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="../index.php" class="text-white">Home</a></li>
                        <li><a href="../about.php" class="text-white">About</a></li>
                        <li><a href="../services.php" class="text-white">Services</a></li>
                        <li><a href="../contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2025 rideCore Vehicle Booking. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>