<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Check if user is a service provider
if($_SESSION["user_type"] !== "service_provider") {
    header("location: ../index.php");
    exit;
}

// Include database connection
include_once '../config/database.php';
include_once '../models/User.php';
include_once '../models/Vehicle.php';
include_once '../models/Booking.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$user = new User($db);
$vehicle = new Vehicle($db);
$booking = new Booking($db);

// Set user ID
$user->user_id = $_SESSION["user_id"];
$user->getById();

// Get bookings for provider's vehicles
$booking->provider_id = $_SESSION["user_id"];
$bookings = $booking->readByProvider();

// Filter by status if provided
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - rideCore Vehicle Booking</title>
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
                        <a class="nav-link" href="vehicles.php">My Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">Bookings</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="provider_inbox.php">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
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

    <!-- Bookings Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Booking Requests</h2>
                <p>Manage all booking requests for your vehicles.</p>
            </div>
        </div>

        <!-- Filter Options -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filter Bookings</h5>
                        <div class="d-flex">
                            <a href="bookings.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">All</a>
                            <a href="bookings.php?status=pending" class="btn <?php echo $status_filter == 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">Pending</a>
                            <a href="bookings.php?status=accepted" class="btn <?php echo $status_filter == 'accepted' ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">Accepted</a>
                            <a href="bookings.php?status=rejected" class="btn <?php echo $status_filter == 'rejected' ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">Rejected</a>
                            <a href="bookings.php?status=completed" class="btn <?php echo $status_filter == 'completed' ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">Completed</a>
                            <a href="bookings.php?status=cancelled" class="btn <?php echo $status_filter == 'cancelled' ? 'btn-primary' : 'btn-outline-primary'; ?>">Cancelled</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 0;
                                    // Replace the while loop with a foreach loop
                                    if (!empty($bookings)) {
                                        foreach ($bookings as $row) {
                                            $count++;
                                            // Status badge styling
                                            $status_badge = '';
                                            switch($row['status']) {
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
                                            
                                            // Apply status filter if set
                                            if(!empty($status_filter) && $row['status'] != $status_filter) {
                                                continue;
                                            }
                                    ?>
                                    <tr>
                                        <td><?php echo $row['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['vehicle_name']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($row['start_time'])); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($row['end_time'])); ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td> ₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td>
                                            <a href="view_booking.php?id=<?php echo $row['booking_id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($row['status'] == 'pending'): ?>
                                            <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>&action=accept" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>&action=reject" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if($row['status'] == 'accepted'): ?>
                                            <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>&action=complete" class="btn btn-sm btn-primary">
                                                <i class="fas fa-flag-checkered"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } 
                                    ?>
                                    
                                    <?php if($count == 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No booking requests found.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
                    <h5>rideCore Vehicle Booking</h5>
                    <p>Making vehicle booking simple and convenient.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Contact</h5>
                    <p>Email: support@ridecore.com<br>Phone: (123) 456-7890</p>
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
</body>
</html>