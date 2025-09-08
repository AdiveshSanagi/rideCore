<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if user is a customer
if($_SESSION["user_type"] !== "customer") {
    header("location: index.php");
    exit;
}

// Include database connection
include_once 'config/database.php';
include_once 'models/Vehicle.php';
include_once 'models/Booking.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize vehicle object
$vehicle = new Vehicle($db);
$booking = new Booking($db);

// Define variables and initialize with empty values
$start_time = $end_time = $pickup_location = $dropoff_location = "";
$start_time_err = $end_time_err = $pickup_location_err = $dropoff_location_err = "";
$booking_error = $success_message = "";
$total_hours = $total_amount = 0;

// Check if vehicle ID is provided
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Set vehicle ID
    $vehicle->vehicle_id = $_GET['id'];
    
    // Get vehicle details
    $vehicle_data = $vehicle->getById();
    
    if(!$vehicle_data) {
        // Redirect to vehicles page if vehicle not found
        header("location: vehicles.php");
        exit;
    }
} else {
    // Redirect to vehicles page if no ID provided
    header("location: vehicles.php");
    exit;
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate start time
    if(empty(trim($_POST["start_time"]))) {
        $start_time_err = "Please select start time.";
    } else {
        $start_time = trim($_POST["start_time"]);
        
        // Check if start time is in the future
        if(strtotime($start_time) < time()) {
            $start_time_err = "Start time must be in the future.";
        }
    }
    
    // Validate end time
    if(empty(trim($_POST["end_time"]))) {
        $end_time_err = "Please select end time.";
    } else {
        $end_time = trim($_POST["end_time"]);
        
        // Check if end time is after start time
        if(empty($start_time_err) && strtotime($end_time) <= strtotime($start_time)) {
            $end_time_err = "End time must be after start time.";
        }
    }
    
    // Validate pickup location
    if(empty(trim($_POST["pickup_location"]))) {
        $pickup_location_err = "Please enter pickup location.";
    } else {
        $pickup_location = trim($_POST["pickup_location"]);
    }
    
    // Validate dropoff location
    if(empty(trim($_POST["dropoff_location"]))) {
        $dropoff_location_err = "Please enter dropoff location.";
    } else {
        $dropoff_location = trim($_POST["dropoff_location"]);
    }
    
    // Calculate total hours and amount
    if(empty($start_time_err) && empty($end_time_err)) {
        $total_hours = (strtotime($end_time) - strtotime($start_time)) / 3600;
        $total_amount = $total_hours * $vehicle->rate_per_hour;
    }
    
    // Check if vehicle is available for the selected time period
    if(empty($start_time_err) && empty($end_time_err) && empty($pickup_location_err) && empty($dropoff_location_err)) {
        if($vehicle->isAvailableForBooking($start_time, $end_time)) {
            // Set booking properties
            $booking->customer_id = $_SESSION["user_id"];
            $booking->vehicle_id = $vehicle->vehicle_id;
            $booking->start_time = $start_time;
            $booking->end_time = $end_time;
            $booking->pickup_location = $pickup_location;
            $booking->dropoff_location = $dropoff_location;
            $booking->total_amount = $total_amount;
            
            // Create booking
            if($booking->create()) {
                $success_message = "Booking created successfully. Your booking is pending approval from the service provider.";
                
                // Clear form data
                $start_time = $end_time = $pickup_location = $dropoff_location = "";
                $total_hours = $total_amount = 0;
            } else {
                $booking_error = "Something went wrong. Please try again later.";
            }
        } else {
            $booking_error = "Vehicle is not available for the selected time period. Please choose a different time.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Vehicle - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Flatpickr for datetime picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="customer/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="customer/bookings.php">My Bookings</a></li>
                                <li><a class="dropdown-item" href="customer/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary ms-2 text-white" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-primary ms-2" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Book Vehicle Form -->
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">Vehicle Details</h2>
                        <div class="vehicle-details">
                            <?php if(!empty($vehicle->image)): ?>
                                <img src="<?php echo htmlspecialchars($vehicle->image); ?>" class="img-fluid mb-3 rounded" alt="<?php echo htmlspecialchars($vehicle->vehicle_name); ?>">
                            <?php else: ?>
                                <img src="assets/images/vehicle-placeholder.jpg" class="img-fluid mb-3 rounded" alt="Vehicle Placeholder">
                            <?php endif; ?>
                            
                            <h3><?php echo htmlspecialchars($vehicle->vehicle_name); ?></h3>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($vehicle->vehicle_type); ?></p>
                            <p><strong>Model:</strong> <?php echo htmlspecialchars($vehicle->model); ?> (<?php echo $vehicle->year; ?>)</p>
                            <p><strong>License Plate:</strong> <?php echo htmlspecialchars($vehicle->license_plate); ?></p>
                            <p><strong>Capacity:</strong> <?php echo $vehicle->capacity; ?> persons</p>
                            <p><strong>Rate:</strong>  ₹<?php echo number_format($vehicle->rate_per_hour, 2); ?> per hour</p>
                            <p><strong>Provider:</strong> <?php echo htmlspecialchars($vehicle_data['provider_name']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Book This Vehicle</h2>
                        
                        <?php if(!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <p class="text-center mt-3">
                                <a href="customer/bookings.php" class="btn btn-primary">View My Bookings</a>
                                <a href="vehicles.php" class="btn btn-outline-primary ms-2">Browse More Vehicles</a>
                            </p>
                        <?php else: ?>
                            <?php if(!empty($booking_error)): ?>
                                <div class="alert alert-danger"><?php echo $booking_error; ?></div>
                            <?php endif; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $vehicle->vehicle_id; ?>" method="post" class="needs-validation">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="text" name="start_time" id="start_time" class="form-control flatpickr-datetime <?php echo (!empty($start_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $start_time; ?>" placeholder="Select start date and time">
                                    <div class="invalid-feedback"><?php echo $start_time_err; ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="text" name="end_time" id="end_time" class="form-control flatpickr-datetime <?php echo (!empty($end_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $end_time; ?>" placeholder="Select end date and time">
                                    <div class="invalid-feedback"><?php echo $end_time_err; ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pickup_location" class="form-label">Pickup Location</label>
                                    <input type="text" name="pickup_location" id="pickup_location" class="form-control <?php echo (!empty($pickup_location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $pickup_location; ?>" placeholder="Enter pickup address">
                                    <div class="invalid-feedback"><?php echo $pickup_location_err; ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="dropoff_location" class="form-label">Dropoff Location</label>
                                    <input type="text" name="dropoff_location" id="dropoff_location" class="form-control <?php echo (!empty($dropoff_location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $dropoff_location; ?>" placeholder="Enter dropoff address">
                                    <div class="invalid-feedback"><?php echo $dropoff_location_err; ?></div>
                                </div>
                                
                                <?php if($total_hours > 0): ?>
                                    <div class="alert alert-info">
                                        <h5>Booking Summary</h5>
                                        <p><strong>Duration:</strong> <?php echo number_format($total_hours, 2); ?> hours</p>
                                        <p><strong>Total Amount:</strong>  ₹<?php echo number_format($total_amount, 2); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Book Now</button>
                                    <a href="vehicles.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
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
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="about.php" class="text-white">About</a></li>
                        <li><a href="services.php" class="text-white">Services</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize datetime pickers
        flatpickr(".flatpickr-datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true
        });
        
        // Calculate total amount when dates change
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            if(startTimeInput && endTimeInput) {
                const calculateTotal = function() {
                    const startTime = startTimeInput.value;
                    const endTime = endTimeInput.value;
                    
                    if(startTime && endTime) {
                        const startTimestamp = new Date(startTime).getTime();
                        const endTimestamp = new Date(endTime).getTime();
                        
                        if(endTimestamp > startTimestamp) {
                            const totalHours = (endTimestamp - startTimestamp) / (1000 * 60 * 60);
                            const ratePerHour = <?php echo $vehicle->rate_per_hour; ?>;
                            const totalAmount = totalHours * ratePerHour;
                            
                            // Submit form to update the summary
                            document.querySelector('form').submit();
                        }
                    }
                };
                
                startTimeInput.addEventListener('change', calculateTotal);
                endTimeInput.addEventListener('change', calculateTotal);
            }
        });
    </script>
</body>
</html>