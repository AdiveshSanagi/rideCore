<?php
// Start session
session_start();

// Include database connection
include_once 'config/database.php';
include_once 'models/Vehicle.php';
include_once 'models/User.php';
include_once 'models/Booking.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$vehicle = new Vehicle($db);
$user = new User($db);
$booking = new Booking($db);

// Check if ID is set in URL
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Set vehicle ID
$vehicle->vehicle_id = $_GET['id'];

// Get vehicle details
$vehicle_data = $vehicle->readOne();

// If vehicle not found, redirect to index
if(!$vehicle_data) {
    header("Location: index.php");
    exit;
}

// Get provider details
$user->user_id = $vehicle_data['provider_id'];
$provider = $user->getById();

// Check if vehicle is available for booking
$is_available = $vehicle_data['availability'] == 1;

// Get vehicle reviews/ratings
$vehicle->getAverageRating();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vehicle_data['vehicle_name']); ?> - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include_once 'includes/navbar.php'; ?>

    <!-- Vehicle Details -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo !empty($vehicle_data['image']) ? $vehicle_data['image'] : 'assets/images/vehicles/default.jpg'; ?>" 
                     class="img-fluid rounded" alt="<?php echo htmlspecialchars($vehicle_data['vehicle_name']); ?>">
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($vehicle_data['vehicle_name']); ?></h1>
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <?php
                        $rating = $vehicle->average_rating;
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $rating) {
                                echo '<i class="fas fa-star text-warning"></i>';
                            } elseif($i - 0.5 <= $rating) {
                                echo '<i class="fas fa-star-half-alt text-warning"></i>';
                            } else {
                                echo '<i class="far fa-star text-warning"></i>';
                            }
                        }
                        ?>
                        <span class="ms-1">(<?php echo $vehicle->rating_count; ?> reviews)</span>
                    </div>
                    <span class="badge <?php echo $is_available ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $is_available ? 'Available' : 'Not Available'; ?>
                    </span>
                </div>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($vehicle_data['vehicle_type']); ?></p>
                <p><strong>Model:</strong> <?php echo htmlspecialchars($vehicle_data['model']); ?> (<?php echo $vehicle_data['year']; ?>)</p>
                <p><strong>License Plate:</strong> <?php echo htmlspecialchars($vehicle_data['license_plate']); ?></p>
                <p><strong>Capacity:</strong> <?php echo $vehicle_data['capacity']; ?> passengers</p>
                <p><strong>Rate:</strong> ₹<?php echo number_format($vehicle_data['rate_per_hour'], 2); ?> per hour</p>
                <p><strong>Provider:</strong> <?php echo htmlspecialchars($user->username); ?></p>
                
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["user_type"] === "customer"): ?>
                    <?php if($is_available): ?>
                        <a href="book_vehicle.php?id=<?php echo $vehicle_data['vehicle_id']; ?>" class="btn btn-primary btn-lg">Book Now</a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>Currently Unavailable</button>
                    <?php endif; ?>
                <?php elseif(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                    <a href="login.php" class="btn btn-outline-primary btn-lg">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Vehicle Location Map -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Vehicle Location</h3>
                    </div>
                    <div class="card-body">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Customer Reviews</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $reviews = $vehicle->getReviews();
                        if(is_array($reviews) && count($reviews) > 0):
                        ?>
                            <?php foreach($reviews as $review): ?>
                                <div class="mb-4 border-bottom pb-3">
                                    <div class="d-flex justify-content-between">
                                        <h5><?php echo htmlspecialchars($review['username']); ?></h5>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <?php
                                        for($i = 1; $i <= 5; $i++) {
                                            if($i <= $review['rating']) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-warning"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p><?php echo htmlspecialchars($review['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center">No reviews yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Replace YOUR_GOOGLE_MAPS_API_KEY with an actual API key -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&callback=initMap" async defer></script>
    <script>
        // Initialize Google Map
        function initMap() {
            <?php if(!empty($vehicle_data['latitude']) && !empty($vehicle_data['longitude'])): ?>
                var vehicleLocation = {lat: <?php echo $vehicle_data['latitude']; ?>, lng: <?php echo $vehicle_data['longitude']; ?>};
                
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 14,
                    center: vehicleLocation
                });
                
                var marker = new google.maps.Marker({
                    position: vehicleLocation,
                    map: map,
                    title: '<?php echo htmlspecialchars($vehicle_data['vehicle_name']); ?>'
                });
            <?php else: ?>
                document.getElementById('map').innerHTML = '<div class="alert alert-info">Location information not available for this vehicle.</div>';
            <?php endif; ?>
        }
    </script>
</body>
</html>