<?php
// Start session
session_start();

// Include database connection
include_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include_once 'includes/navbar.php'; ?>

    <!-- Services Header -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h1 class="display-4">Our Services</h1>
                    <p class="lead">Discover the wide range of vehicle booking services we offer to meet your transportation needs.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Services Section -->
    <div class="container my-5">
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-car fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Car Rentals</h3>
                        <p class="card-text">Choose from a wide selection of cars for your daily commute or special occasions. We offer sedans, SUVs, and luxury vehicles to suit your needs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Commercial Vehicles</h3>
                        <p class="card-text">Need to move goods or equipment? Our commercial vehicle fleet includes trucks and vans of various sizes for your business needs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-motorcycle fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Two-Wheelers</h3>
                        <p class="card-text">For quick city travel, our two-wheeler options provide convenience and affordability. Perfect for short trips and navigating traffic.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Flexible Booking</h3>
                        <p class="card-text">Book vehicles for hours, days, or weeks. Our flexible booking system allows you to choose the exact duration you need.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">GPS Tracking</h3>
                        <p class="card-text">All our vehicles are equipped with GPS tracking for your safety and convenience. Know exactly where your vehicle is at all times.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">24/7 Support</h3>
                        <p class="card-text">Our customer support team is available round the clock to assist you with any queries or issues during your rental period.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">How It Works</h2>
            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-search fa-2x text-primary"></i>
                    </div>
                    <h4 class="mt-3">Search</h4>
                    <p>Browse our extensive collection of vehicles and find the perfect match for your needs.</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-calendar-check fa-2x text-primary"></i>
                    </div>
                    <h4 class="mt-3">Book</h4>
                    <p>Select your pickup and return dates, and complete the booking process online.</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-key fa-2x text-primary"></i>
                    </div>
                    <h4 class="mt-3">Pickup</h4>
                    <p>Collect your vehicle from the designated location at your scheduled time.</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="bg-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-undo fa-2x text-primary"></i>
                    </div>
                    <h4 class="mt-3">Return</h4>
                    <p>Return the vehicle at the end of your booking period and share your experience.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Pricing Plans</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Economy</h3>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="card-title pricing-card-title">2000<small class="text-muted">/hour</small></h2>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li>Compact Cars</li>
                            <li>Basic Insurance</li>
                            <li>Standard Mileage</li>
                            <li>24/7 Support</li>
                        </ul>
                        <a href="customer/search.php?category=economy" class="btn btn-outline-primary btn-lg">View Vehicles</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow border-primary">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Standard</h3>
                        <span class="badge bg-warning text-dark">Most Popular</span>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="card-title pricing-card-title">3500<small class="text-muted">/hour</small></h2>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li>Sedans & SUVs</li>
                            <li>Comprehensive Insurance</li>
                            <li>Unlimited Mileage</li>
                            <li>24/7 Premium Support</li>
                        </ul>
                        <a href="customer/search.php?category=standard" class="btn btn-primary btn-lg">View Vehicles</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Premium</h3>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="card-title pricing-card-title">4000<small class="text-muted">/hour</small></h2>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li>Luxury Vehicles</li>
                            <li>Full Coverage Insurance</li>
                            <li>Unlimited Mileage</li>
                            <li>Dedicated Concierge</li>
                        </ul>
                        <a href="customer/search.php?category=premium" class="btn btn-outline-primary btn-lg">View Vehicles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">What Our Customers Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"The booking process was incredibly smooth, and the vehicle was in excellent condition. Will definitely use this service again!"</p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex align-items-center">
                                <img src="assets/images/vehicles/adi.jpg" alt="Customer" class="rounded-circle me-3" width="50">
                                <div>
                                    <h5 class="mb-0">Adisan-ms</h5>
                                    <small class="text-muted">Business Traveler</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            </div>
                            <p class="card-text">"I needed a vehicle for a family trip, and the SUV we rented was perfect. Clean, spacious, and fuel-efficient. Great service!"</p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex align-items-center">
                                <img src="assets\images\vehicles\somu 2.jpg" alt="Customer" class="rounded-circle me-3" width="50">
                                <div>
                                    <h5 class="mb-0">Somu AC</h5>
                                    <small class="text-muted">Family Traveler</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"As a frequent traveler, I've used many vehicle rental services, but this one stands out for its reliability and customer service."</p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex align-items-center">
                                <img src="assets\images\vehicles\PIC123.jpeg" alt="Customer" class="rounded-circle me-3" width="50">
                                <div>
                                    <h5 class="mb-0">Somesh Jangly</h5>
                                    <small class="text-muted">Frequent Traveler</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-10 mx-auto text-center">
                <h2>Ready to Book Your Vehicle?</h2>
                <p class="lead mb-4">Browse our selection of vehicles and find the perfect one for your needs.</p>
                <a href="customer/search.php" class="btn btn-primary btn-lg px-5">Search Vehicles</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>