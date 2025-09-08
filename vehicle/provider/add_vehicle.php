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

// Define variables and initialize with empty values
$vehicle_name = $vehicle_type = $license_plate = $model = "";
$year = $capacity = 0;
$rate_per_hour = 0.00;
$latitude = $longitude = "";
$image = "";

$vehicle_name_err = $vehicle_type_err = $license_plate_err = $model_err = "";
$year_err = $capacity_err = $rate_per_hour_err = $image_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate vehicle name
    if(empty(trim($_POST["vehicle_name"]))) {
        $vehicle_name_err = "Please enter vehicle name.";
    } else {
        $vehicle_name = trim($_POST["vehicle_name"]);
    }
    
    // Validate vehicle type
    if(empty(trim($_POST["vehicle_type"]))) {
        $vehicle_type_err = "Please enter vehicle type.";
    } else {
        $vehicle_type = trim($_POST["vehicle_type"]);
    }
    
    // Validate license plate
    if(empty(trim($_POST["license_plate"]))) {
        $license_plate_err = "Please enter license plate.";
    } else {
        $license_plate = trim($_POST["license_plate"]);
    }
    
    // Validate model
    if(empty(trim($_POST["model"]))) {
        $model_err = "Please enter model.";
    } else {
        $model = trim($_POST["model"]);
    }
    
    // Validate year
    if(empty(trim($_POST["year"]))) {
        $year_err = "Please enter year.";
    } elseif(!is_numeric($_POST["year"]) || $_POST["year"] < 1900 || $_POST["year"] > date("Y") + 1) {
        $year_err = "Please enter a valid year.";
    } else {
        $year = (int)trim($_POST["year"]);
    }
    
    // Validate capacity
    if(empty(trim($_POST["capacity"]))) {
        $capacity_err = "Please enter capacity.";
    } elseif(!is_numeric($_POST["capacity"]) || $_POST["capacity"] < 1) {
        $capacity_err = "Please enter a valid capacity.";
    } else {
        $capacity = (int)trim($_POST["capacity"]);
    }
    
    // Validate rate per hour
    if(empty(trim($_POST["rate_per_hour"]))) {
        $rate_per_hour_err = "Please enter rate per hour.";
    } elseif(!is_numeric($_POST["rate_per_hour"]) || $_POST["rate_per_hour"] <= 0) {
        $rate_per_hour_err = "Please enter a valid rate per hour.";
    } else {
        $rate_per_hour = (float)trim($_POST["rate_per_hour"]);
    }
    
    // Get latitude and longitude
    $latitude = !empty($_POST["latitude"]) ? trim($_POST["latitude"]) : null;
    $longitude = !empty($_POST["longitude"]) ? trim($_POST["longitude"]) : null;
    
    // Handle image upload
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) {
            $image_err = "Please select a valid file format (JPG, JPEG, PNG, GIF).";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) {
            $image_err = "File size is larger than the allowed limit (5MB).";
        }
        
        // Verify MIME type of the file
        if(in_array($filetype, $allowed)) {
            // Check if file already exists
            $image_dir = "../assets/images/vehicles/";
            
            // Create directory if it doesn't exist
            if(!file_exists($image_dir)) {
                mkdir($image_dir, 0777, true);
            }
            
            // Create unique filename
            $new_filename = uniqid() . "." . $ext;
            $image_path = $image_dir . $new_filename;
            
            // Upload file
            if(empty($image_err) && move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                $image = "assets/images/vehicles/" . $new_filename;
            } else {
                $image_err = "Failed to upload file.";
            }
        } else {
            $image_err = "There was a problem with the uploaded file.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($vehicle_name_err) && empty($vehicle_type_err) && empty($license_plate_err) && 
       empty($model_err) && empty($year_err) && empty($capacity_err) && 
       empty($rate_per_hour_err) && empty($image_err)) {
        
        // Set vehicle properties
        $vehicle->provider_id = $_SESSION["user_id"];
        $vehicle->vehicle_name = $vehicle_name;
        $vehicle->vehicle_type = $vehicle_type;
        $vehicle->license_plate = $license_plate;
        $vehicle->model = $model;
        $vehicle->year = $year;
        $vehicle->capacity = $capacity;
        $vehicle->rate_per_hour = $rate_per_hour;
        $vehicle->availability = true;
        $vehicle->latitude = $latitude;
        $vehicle->longitude = $longitude;
        $vehicle->image = $image;
        
        // Create the vehicle
        if($vehicle->create()) {
            // Redirect to vehicles page with success message
            header("location: vehicles.php?success=added");
            exit;
        } else {
            // Redirect to vehicles page with error message
            header("location: vehicles.php?error=add_failed");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle - rideCore Vehicle Booking</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
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

    <!-- Add Vehicle Form -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Add New Vehicle</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vehicle_name" class="form-label">Vehicle Name</label>
                                        <input type="text" name="vehicle_name" id="vehicle_name" class="form-control <?php echo (!empty($vehicle_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $vehicle_name; ?>">
                                        <div class="invalid-feedback"><?php echo $vehicle_name_err; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                                        <select name="vehicle_type" id="vehicle_type" class="form-select <?php echo (!empty($vehicle_type_err)) ? 'is-invalid' : ''; ?>">
                                            <option value="">Select Type</option>
                                            <option value="Sedan" <?php echo ($vehicle_type == "Sedan") ? 'selected' : ''; ?>>Sedan</option>
                                            <option value="SUV" <?php echo ($vehicle_type == "SUV") ? 'selected' : ''; ?>>SUV</option>
                                            <option value="Truck" <?php echo ($vehicle_type == "Truck") ? 'selected' : ''; ?>>Truck</option>
                                            <option value="Van" <?php echo ($vehicle_type == "Van") ? 'selected' : ''; ?>>Van</option>
                                            <option value="Luxury" <?php echo ($vehicle_type == "Luxury") ? 'selected' : ''; ?>>Luxury</option>
                                            <option value="Sports" <?php echo ($vehicle_type == "Sports") ? 'selected' : ''; ?>>Sports</option>
                                            <option value="Other" <?php echo ($vehicle_type == "Other") ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <div class="invalid-feedback"><?php echo $vehicle_type_err; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="license_plate" class="form-label">License Plate</label>
                                        <input type="text" name="license_plate" id="license_plate" class="form-control <?php echo (!empty($license_plate_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $license_plate; ?>">
                                        <div class="invalid-feedback"><?php echo $license_plate_err; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" name="model" id="model" class="form-control <?php echo (!empty($model_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $model; ?>">
                                        <div class="invalid-feedback"><?php echo $model_err; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Year</label>
                                        <input type="number" name="year" id="year" class="form-control <?php echo (!empty($year_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $year; ?>" min="1900" max="<?php echo date("Y") + 1; ?>">
                                        <div class="invalid-feedback"><?php echo $year_err; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Capacity (Passengers)</label>
                                        <input type="number" name="capacity" id="capacity" class="form-control <?php echo (!empty($capacity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $capacity; ?>" min="1">
                                        <div class="invalid-feedback"><?php echo $capacity_err; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rate_per_hour" class="form-label">Rate Per Hour (₹)</label>
                                        <input type="number" name="rate_per_hour" id="rate_per_hour" class="form-control <?php echo (!empty($rate_per_hour_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $rate_per_hour; ?>" min="0.01" step="0.01">
                                        <div class="invalid-feedback"><?php echo $rate_per_hour_err; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Vehicle Image</label>
                                        <input type="file" name="image" id="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                                        <div class="invalid-feedback"><?php echo $image_err; ?></div>
                                        <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, GIF. Max size: 5MB.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Vehicle Location (Optional)</label>
                                        <div id="map" style="height: 300px; width: 100%; margin-bottom: 10px;"></div>
                                        <p class="text-muted small">Click on the map to set the vehicle's location or enter coordinates manually below.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" class="form-control" value="<?php echo $latitude; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="longitude" class="form-label">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" class="form-control" value="<?php echo $longitude; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="vehicles.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Vehicle</button>
                            </div>
                        </form>
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
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
    <script>
        // Initialize Google Map
        function initMap() {
            // Default map center (New York)
            var center = {lat: 40.7128, lng: -74.0060};
            
            // Create map
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: center
            });
            
            // Create marker
            var marker = null;
            
            // Add click event listener to map
            map.addListener('click', function(event) {
                // Remove existing marker if any
                if (marker) {
                    marker.setMap(null);
                }
                
                // Create new marker at clicked location
                marker = new google.maps.Marker({
                    position: event.latLng,
                    map: map
                });
                
                // Update latitude and longitude inputs
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();
            });
            
            // If latitude and longitude are already set, show marker
            var lat = document.getElementById('latitude').value;
            var lng = document.getElementById('longitude').value;
            
            if (lat && lng) {
                var position = {lat: parseFloat(lat), lng: parseFloat(lng)};
                
                // Center map on existing position
                map.setCenter(position);
                
                // Create marker at existing position
                marker = new google.maps.Marker({
                    position: position,
                    map: map
                });
            }
        }
    </script>
</body>
</html>