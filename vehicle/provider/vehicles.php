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
include_once '../models/Vehicle.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize vehicle object
$vehicle = new Vehicle($db);

// Set provider ID
$vehicle->provider_id = $_SESSION["user_id"];

// Get provider's vehicles
$vehicles = $vehicle->getByProviderId();

// Process success and error messages
$success_msg = "";
$error_msg = "";

if(isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'added':
            $success_msg = "Vehicle added successfully.";
            break;
        case 'updated':
            $success_msg = "Vehicle updated successfully.";
            break;
        case 'deleted':
            $success_msg = "Vehicle deleted successfully.";
            break;
    }
}

if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'add_failed':
            $error_msg = "Failed to add vehicle.";
            break;
        case 'update_failed':
            $error_msg = "Failed to update vehicle.";
            break;
        case 'delete_failed':
            $error_msg = "Failed to delete vehicle.";
            break;
        case 'not_found':
            $error_msg = "Vehicle not found.";
            break;
        case 'unauthorized':
            $error_msg = "You are not authorized to perform this action.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vehicles - rideCore Vehicle Booking</title>
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
                        <a class="nav-link active" href="vehicles.php">My Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">Bookings</a>
                    </li>
                  <!--  <li class="nav-item">
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

    <!-- Vehicles Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Vehicles</h2>
                    <a href="add_vehicle.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Vehicle
                    </a>
                </div>
                
                <?php if(!empty($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Vehicles Cards -->
        <div class="row">
            <?php
            $count = 0;
            if (!empty($vehicles)) {
                foreach ($vehicles as $row) {
                    $count++;
                    $availability_badge = $row['availability'] ? 
                        '<span class="badge bg-success">Available</span>' : 
                        '<span class="badge bg-danger">Not Available</span>';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if(!empty($row['image']) && file_exists("../" . $row['image'])): ?>
                        <img src="../<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['vehicle_name']); ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="../assets/images/vehicle-placeholder.jpg" class="card-img-top" alt="Vehicle Placeholder" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['vehicle_name']); ?> <?php echo $availability_badge; ?></h5>
                        <p class="card-text">
                            <strong>Type:</strong> <?php echo htmlspecialchars($row['vehicle_type']); ?><br>
                            <strong>License Plate:</strong> <?php echo htmlspecialchars($row['license_plate']); ?><br>
                            <strong>Model:</strong> <?php echo htmlspecialchars($row['model']); ?> (<?php echo $row['year']; ?>)<br>
                            <strong>Capacity:</strong> <?php echo $row['capacity']; ?> passengers<br>
                            <strong>Rate:</strong>  ₹<?php echo number_format($row['rate_per_hour'], 2); ?> per hour
                        </p>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="edit_vehicle.php?id=<?php echo $row['vehicle_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="toggle_availability.php?id=<?php echo $row['vehicle_id']; ?>" class="btn <?php echo $row['availability'] ? 'btn-warning' : 'btn-success'; ?>">
                                <i class="fas <?php echo $row['availability'] ? 'fa-ban' : 'fa-check'; ?>"></i> 
                                <?php echo $row['availability'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                            </a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['vehicle_id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal<?php echo $row['vehicle_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete the vehicle "<?php echo htmlspecialchars($row['vehicle_name']); ?>" (<?php echo htmlspecialchars($row['license_plate']); ?>)?
                            <p class="text-danger mt-2">This action cannot be undone and will also delete all associated bookings.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <a href="delete_vehicle.php?id=<?php echo $row['vehicle_id']; ?>" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                }
            } 
            ?>
            
            <?php if($count == 0): ?>
            <div class="col-md-12">
                <div class="alert alert-info">
                    <p>You haven't added any vehicles yet. <a href="add_vehicle.php">Add your first vehicle</a> to start receiving booking requests.</p>
                </div>
            </div>
            <?php endif; ?>
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