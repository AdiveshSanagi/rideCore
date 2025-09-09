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

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize booking object
$booking = new Booking($db);
$booking->customer_id = $_SESSION["user_id"];

// Get customer's bookings
$bookings = $booking->getByCustomerId();

// Process success and error messages
$success_msg = "";
$error_msg = "";

if(isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'booked':
            $success_msg = "Vehicle booked successfully.";
            break;
        case 'cancelled':
            $success_msg = "Booking cancelled successfully.";
            break;
    }
}

if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'booking_failed':
            $error_msg = "Failed to book vehicle.";
            break;
        case 'cancel_failed':
            $error_msg = "Failed to cancel booking.";
            break;
        case 'not_found':
            $error_msg = "Booking not found.";
            break;
        case 'unauthorized':
            $error_msg = "You are not authorized to perform this action.";
            break;
    }
}

// Status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - rideCore Vehicle Booking</title>
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

    <!-- Bookings Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>My Bookings</h2>
                <p>View and manage all your vehicle bookings.</p>
                
                <?php if(!empty($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>
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
                                        <th>Vehicle</th>
                                        <th>Provider</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 0;
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
                                        <td><?php echo htmlspecialchars($row['vehicle_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['provider_name']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($row['start_time'])); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($row['end_time'])); ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td> ₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td>
                                                        <?php if($row['status'] == 'completed' && $row['payment_done'] == 0): ?>
                                                            <form action="payment.php" method="POST">
                                                                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                                                <input type="hidden" name="amount" value="<?php echo $row['total_amount']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-primary">
                                                                    Pay ₹<?php echo number_format($row['total_amount'], 2); ?>
                                                                </button>
                                                            </form>
                                                        <?php elseif($row['payment_done'] == 1): ?>
                                                            <span class="badge bg-success">Paid</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Not Available</span>
                                                        <?php endif; ?>
                                                    </td>


                                        <td>
                                            <a href="view_booking.php?id=<?php echo $row['booking_id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                          <?php if($row['status'] == 'pending'): ?>
                                                            <a href="cancel_booking.php?id=<?php echo $row['booking_id']; ?>" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        <?php elseif($row['status'] == 'completed'): ?>
                                                            <span class="badge bg-success">Booking already completed</span>
                                                        <?php elseif($row['status'] == 'cancelled'): ?>
                                                            <span class="badge bg-danger">Booking already cancelled</span>
                                                        <?php elseif($row['status'] == 'rejected'): ?>
                                                            <span class="badge bg-dark">Booking rejected</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Not cancellable</span>
                                                        <?php endif; ?>


                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } 
                                    ?>
                                    
                                    <?php if($count == 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No bookings found.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Vehicles Button -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="search.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Search for Vehicles to Book
                </a>
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
</body>
</html>