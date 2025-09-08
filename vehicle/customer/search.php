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

// Include database connection and models
include_once '../config/database.php';
include_once '../models/Vehicle.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize vehicle object
$vehicle = new Vehicle($db);

// Set default search parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$vehicle_type = isset($_GET['vehicle_type']) ? $_GET['vehicle_type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Get all vehicle types for filter dropdown
$vehicle_types = $vehicle->getAllVehicleTypes();

// Search for vehicles if form is submitted
$search_results = [];
$search_performed = false;

if(isset($_GET['search'])) {
    $search_performed = true;
    
    // Set search parameters
    $vehicle->location = $location;
    $vehicle->vehicle_type = $vehicle_type;
    $vehicle->min_price = $min_price;
    $vehicle->max_price = $max_price;
    $vehicle->start_date = $start_date;
    $vehicle->end_date = $end_date;
    
    // Get search results
    $search_results = $vehicle->searchAvailable();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Vehicles - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Flatpickr for date/time picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                        <a class="nav-link active" href="search.php">Search Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">My Bookings</a>
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

    <!-- Search Form Section -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Search Available Vehicles</h2>
                <p>Find the perfect vehicle for your needs.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="search.php" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="Enter city or area" value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <select class="form-select" id="vehicle_type" name="vehicle_type">
                            <option value="">All Types</option>
                            <?php foreach($vehicle_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($vehicle_type == $type) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date & Time</label>
                        <input type="text" class="form-control flatpickr-datetime" id="start_date" name="start_date" placeholder="Select start date and time" value="<?php echo htmlspecialchars($start_date); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date & Time</label>
                        <input type="text" class="form-control flatpickr-datetime" id="end_date" name="end_date" placeholder="Select end date and time" value="<?php echo htmlspecialchars($end_date); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="min_price" class="form-label">Min Price (per hour)</label>
                        <div class="input-group">
                            <span class="input-group-text"> ₹</span>
                            <input type="number" class="form-control" id="min_price" name="min_price" placeholder="Min" min="0" value="<?php echo htmlspecialchars($min_price); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="max_price" class="form-label">Max Price (per hour)</label>
                        <div class="input-group">
                            <span class="input-group-text"> ₹</span>
                            <input type="number" class="form-control" id="max_price" name="max_price" placeholder="Max" min="0" value="<?php echo htmlspecialchars($max_price); ?>">
                        </div>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" name="search" value="1" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Search Vehicles
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if($search_performed): ?>
            <div class="row">
                <div class="col-md-12">
                    <h3>Search Results</h3>
                    <?php if(empty($search_results)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No vehicles found matching your criteria. Please try different search parameters.
                        </div>
                    <?php else: ?>
                        <p>Found <?php echo count($search_results); ?> vehicle(s) matching your criteria.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                <?php foreach($search_results as $vehicle): ?>
                    <div class="col">
                        <div class="card h-100">
                            <?php if(!empty($vehicle['image']) && file_exists("../" . $vehicle['image'])): ?>
                                <img src="../<?php echo $vehicle['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="../assets/images/vehicle-placeholder.jpg" class="card-img-top" alt="Vehicle Placeholder" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($vehicle['vehicle_name']); ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($vehicle['vehicle_type']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($vehicle['model']); ?> (<?php echo $vehicle['year']; ?>)</span>
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($location = $_GET['location'] ?? ''); ?>Location<br>
                                    <i class="fas fa-users text-info"></i> <?php echo $vehicle['capacity']; ?> passengers<br>
                                    <i class="fas fa-dollar-sign text-success"></i> ₹<?php echo number_format($vehicle['rate_per_hour'], 2); ?> per hour
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Provided by: <?php echo htmlspecialchars($vehicle['provider_name']); ?>
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer d-flex flex-column gap-2">
                                <!-- Book Now Button -->
                                <a href="../book_vehicle.php?id=<?= $vehicle['vehicle_id']; ?>&start=<?= urlencode($start_date); ?>&end=<?= urlencode($end_date); ?>" 
                                        class="btn btn-primary w-100">
                                        <i class="fas fa-calendar-check"></i> Book Now
                                        </a>


                                <!-- Send Message Button -->
                                <a href="send_message.php?provider_id=<?php echo $vehicle['provider_id']; ?>" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-envelope"></i> Send Message to Provider
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize datetime pickers
            flatpickr(".flatpickr-datetime", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today",
                time_24hr: false
            });
            
            // Ensure end date is after start date
            const startDatePicker = flatpickr("#start_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today",
                time_24hr: false,
                onChange: function(selectedDates, dateStr) {
                    endDatePicker.set('minDate', dateStr);
                }
            });
            
            const endDatePicker = flatpickr("#end_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today",
                time_24hr: false
            });
        });
    </script>
</body>
</html>
