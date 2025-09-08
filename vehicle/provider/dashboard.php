<?php
session_start();

// Check login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Check service provider
if($_SESSION["user_type"] !== "service_provider"){
    header("location: ../index.php");
    exit;
}

// Include DB and models
include_once '../config/database.php';
include_once '../models/User.php';
include_once '../models/Vehicle.php';
include_once '../models/Booking.php';

// DB connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$user = new User($db);
$vehicle = new Vehicle($db);
$booking = new Booking($db);

// Set current user
$user->user_id = $_SESSION["user_id"];
$user->getById();

// Provider's vehicles
$vehicle->provider_id = $_SESSION["user_id"];
$vehicles = $vehicle->getByProviderId();

// Provider bookings
$booking->provider_id = $_SESSION["user_id"];
$bookings = $booking->readByProvider();

// Provider stats
$stats = $booking->getProviderStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Provider Dashboard - rideCore Vehicle Booking</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php"><span class="text-primary">rideCore</span> Vehicle Booking</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="vehicles.php">My Vehicles</a></li>
                <li class="nav-item"><a class="nav-link" href="bookings.php">Bookings</a></li>
                <!--<li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>-->
                <li class="nav-item"><a class="nav-link" href="provider_inbox.php"><i class="fas fa-envelope"></i> Messages</a></li>
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

<!-- Dashboard Content -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <p>This is your service provider dashboard. Manage vehicles, bookings, and view reports.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mt-4">
        <?php
        $cards = [
            ['title'=>'Total Requests','value'=>$stats['total_requests']??0,'class'=>'bg-primary','link'=>'bookings.php'],
            ['title'=>'Accepted Requests','value'=>$stats['accepted_requests']??0,'class'=>'bg-success','link'=>'bookings.php?status=accepted'],
            ['title'=>'Completed Bookings','value'=>$stats['completed_bookings']??0,'class'=>'bg-warning','link'=>'bookings.php?status=completed'],
            ['title'=>'Total Earnings','value'=>"₹".number_format($stats['total_earnings']??0,2),'class'=>'bg-info','link'=>'reports.php']
        ];
        foreach($cards as $card): ?>
        <div class="col-md-3">
            <div class="card <?php echo $card['class']; ?> text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $card['title']; ?></h5>
                    <h2 class="display-4"><?php echo $card['value']; ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?php echo $card['link']; ?>">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Bookings Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-table me-1"></i>Recent Booking Requests</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                if(!empty($bookings)):
                                    foreach($bookings as $row):
                                        if($count>=5) break;
                                        $count++;
                                        $status_class = '';
                                        switch($row['status']){
                                            case 'pending': $status_class='bg-warning'; break;
                                            case 'accepted': $status_class='bg-success'; break;
                                            case 'rejected': $status_class='bg-danger'; break;
                                            case 'completed': $status_class='bg-info'; break;
                                            case 'cancelled': $status_class='bg-secondary'; break;
                                        }
                                        $license_plate = $row['license_plate'] ?? 'N/A';
                                ?>
                                <tr>
                                    <td><?php echo $row['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['vehicle_name'])." ($license_plate)"; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['start_time'])); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['end_time'])); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td>₹<?php echo number_format($row['total_amount'],2); ?></td>
                                    <td>
                                        <a href="view_booking.php?id=<?php echo $row['booking_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($row['status'] == 'pending'): ?>
                                            <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>&action=accept" class="btn btn-sm btn-success" onclick="return confirm('Accept this booking?');">
                                                <i class="fas fa-check"></i> Accept
                                            </a>
                                            <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>&action=reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this booking?');">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php elseif($row['status'] == 'accepted'): ?>
                                            <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>&action=complete" class="btn btn-sm btn-primary" onclick="return confirm('Mark this booking as completed?');">
                                                <i class="fas fa-flag-checkered"></i> Complete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No Actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                    endforeach;
                                else:
                                    echo '<tr><td colspan="8" class="text-center">No booking requests found.</td></tr>';
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3"><a href="bookings.php" class="btn btn-primary">View All Bookings</a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Map -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-map-marker-alt me-1"></i>Your Vehicle Locations</div>
                <div class="card-body">
                    <div id="map" style="height: 400px;">Loading map...</div>
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
        <div class="text-center"><p class="mb-0">&copy; 2025 rideCore Vehicle Booking. All rights reserved.</p></div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Google Maps API -->
<script src="http://maps.googleapis.com/maps/api/js?key=YOUR_REAL_API_KEY"></script>
<script>
    // Placeholder for Google Maps JS
</script>

</body>
</html>
