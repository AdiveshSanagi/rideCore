<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and required models
include_once 'config/database.php';
include_once 'models/Vehicle.php';
include_once 'helpers/format_helper.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize vehicle object
$vehicle = new Vehicle($db);

// Get all available vehicles
$vehicles = $vehicle->getAvailableVehicles();

// Get all vehicle types for filter
$vehicle_types = $vehicle->getAllVehicleTypes();

// Check if filter is applied
$filtered = false;
if (isset($_GET['filter']) && $_GET['filter'] == 'apply') {
    $filtered = true;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $capacity = isset($_GET['capacity']) ? $_GET['capacity'] : null;
    $max_rate = isset($_GET['max_rate']) ? $_GET['max_rate'] : null;

    // Search vehicles with filters
    $vehicles = $vehicle->searchVehicles($type, $capacity, $max_rate);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Vehicles - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Dark theme styles -->
    <style>
        body { background-color: #121212; color: #e0e0e0; }
        .card { background-color: #1e1e1e; border-color: #333; }
        .card-body, .card-footer { background-color: #1e1e1e; color: #e0e0e0; }
        .form-control, .form-select { background-color: #2d2d2d; border-color: #444; color: #e0e0e0; }
        .form-control:focus, .form-select:focus { background-color: #2d2d2d; color: #e0e0e0; }
        .input-group-text { background-color: #333; border-color: #444; color: #e0e0e0; }
        .btn-outline-secondary { color: #e0e0e0; border-color: #666; }
        .btn-outline-secondary:hover { background-color: #444; color: #fff; }
        .alert-info { background-color: #1a3a4a; color: #9ee; border-color: #2a4a5a; }
        .alert-warning { background-color: #4a3a1a; color: #ee9; border-color: #5a4a2a; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="text-primary">rideCore</span> Vehicle Booking
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="vehicles.php">Vehicles</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                <?php if ($_SESSION["user_type"] === "customer"): ?>
                                    <li><a class="dropdown-item" href="customer/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="customer/bookings.php">My Bookings</a></li>
                                <?php elseif ($_SESSION["user_type"] === "provider"): ?>
                                    <li><a class="dropdown-item" href="provider/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="provider/vehicles.php">My Vehicles</a></li>
                                <?php elseif ($_SESSION["user_type"] === "admin"): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Vehicles Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Available Vehicles</h2>
                <p>Browse our selection of available vehicles for rent.</p>
            </div>
        </div>

        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filter Vehicles</h5>
                    </div>
                    <div class="card-body">
                        <form action="vehicles.php" method="get">
                            <input type="hidden" name="filter" value="apply">

                            <div class="mb-3">
                                <label for="type" class="form-label">Vehicle Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    <?php foreach ($vehicle_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo (isset($_GET['type']) && $_GET['type'] == $type) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="capacity" class="form-label">Minimum Capacity</label>
                                <select class="form-select" id="capacity" name="capacity">
                                    <option value="">Any Capacity</option>
                                    <?php for ($i = 2; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($_GET['capacity']) && $_GET['capacity'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> or more
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="max_rate" class="form-label">Maximum Rate (per hour)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="max_rate" name="max_rate" min="0" step="100" value="<?php echo isset($_GET['max_rate']) ? htmlspecialchars($_GET['max_rate']) : ''; ?>">
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Apply Filters</button>
                                <a href="vehicles.php" class="btn btn-outline-secondary"><i class="fas fa-undo"></i> Reset Filters</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Vehicle Listings -->
            <div class="col-md-9">
                <?php if ($filtered): ?>
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> Showing filtered results. <a href="vehicles.php" class="alert-link">Clear all filters</a>
                    </div>
                <?php endif; ?>

                <?php if (empty($vehicles)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No vehicles found matching your criteria. Please try different filters.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($vehicles as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <?php if (!empty($item['image'])): ?>
                                        <?php 
                                            // Fix: handle both filename and full Windows path
                                            $imageFile = basename($item['image']);
                                            $imagePath = "assets/images/vehicles/" . $imageFile;
                                        ?>
                                        <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['vehicle_name']); ?>" style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="assets/images/vehicle-placeholder.jpg" class="card-img-top" alt="Vehicle" style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>

                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($item['vehicle_name']); ?></h5>
                                        <p class="card-text">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($item['vehicle_type']); ?></span>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($item['model']) . ' ' . htmlspecialchars($item['year']); ?></span>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-users"></i> Capacity: <?php echo htmlspecialchars($item['capacity']); ?> people<br>
                                            <i class="fas fa-id-card"></i> License: <?php echo htmlspecialchars($item['license_plate']); ?><br>
                                            <i class="fas fa-user"></i> Provider: <?php echo htmlspecialchars($item['provider_name']); ?>
                                        </p>
                                        <h6 class="card-subtitle mb-2 text-primary fw-bold">
                                            <?php echo formatIndianRupee($item['rate_per_hour']); ?> per hour
                                        </h6>
                                    </div>
                                    <div class="card-footer">
                                        <a href="vehicle_details.php?id=<?php echo $item['vehicle_id']; ?>" class="btn btn-primary"><i class="fas fa-info-circle"></i> View Details</a>
                                        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["user_type"] === "customer"): ?>
                                            <a href="book_vehicle.php?id=<?php echo $item['vehicle_id']; ?>" class="btn btn-success"><i class="fas fa-calendar-plus"></i> Book Now</a>
                                        <?php elseif (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                                            <a href="login.php?redirect=vehicles.php" class="btn btn-outline-success"><i class="fas fa-sign-in-alt"></i> Login to Book</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
