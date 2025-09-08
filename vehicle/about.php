<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - rideCore Vehicle Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Add dark theme styles -->
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }
        .card {
            background-color: #1e1e1e;
            border-color: #333;
        }
        .card-body {
            background-color: #1e1e1e;
            color: #e0e0e0;
        }
        .team-member img {
            border: 3px solid #333;
        }
        .timeline-item {
            background-color: #1e1e1e;
            border-left: 3px solid #0d6efd;
        }
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
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                <?php if($_SESSION["user_type"] === "customer"): ?>
                                    <li><a class="dropdown-item" href="customer/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="customer/bookings.php">My Bookings</a></li>
                                <?php elseif($_SESSION["user_type"] === "provider"): ?>
                                    <li><a class="dropdown-item" href="provider/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="provider/vehicles.php">My Vehicles</a></li>
                                <?php elseif($_SESSION["user_type"] === "admin"): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>About rideCore Vehicle Booking</h2>
                <p class="lead">Your trusted partner for vehicle rentals in India.</p>
            </div>
        </div>

        <!-- Company Overview -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-building"></i> Our Company</h5>
                    </div>
                    <div class="card-body">
                        <p>rideCore Vehicle Booking was founded in 2023 with a simple mission: to make vehicle rentals accessible, affordable, and hassle-free for everyone in India.</p>
                        <p>We connect vehicle owners with people who need transportation, creating a win-win situation for both parties. Our platform ensures safety, reliability, and transparency in every transaction.</p>
                        <p>With operations in over 20 cities across India, we're rapidly expanding to serve more customers and provide more opportunities for vehicle owners to earn additional income.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Our Growth</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent">Over 10,000 registered vehicles</li>
                            <li class="list-group-item bg-transparent">50,000+ satisfied customers</li>
                            <li class="list-group-item bg-transparent">200,000+ successful bookings</li>
                            <li class="list-group-item bg-transparent">4.8/5 average customer rating</li>
                            <li class="list-group-item bg-transparent">Operations in 20+ cities across India</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Values -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-heart"></i> Our Values</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                    <h5>Safety First</h5>
                                    <p>We prioritize the safety of our customers and ensure all vehicles meet strict quality standards.</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-rupee-sign fa-3x text-primary mb-3"></i>
                                    <h5>Affordability</h5>
                                    <p>We believe in providing value for money with transparent pricing and no hidden charges.</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                                    <h5>Trust & Reliability</h5>
                                    <p>We build trust through reliable service and maintaining the highest standards of integrity.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Team -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users"></i> Our Leadership Team</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="text-center team-member">
                                    <img src="assets/images/vehicles/my-about.jpeg" class="rounded-circle mb-3" width="150" height="150" alt="CEO">
                                    <h5>Adivesh Sanagi</h5>
                                    <p class="text-primary">CEO & Founder</p>
                                    <p>With 15+ years in the transportation industry, Adivesh leads our vision and strategy.</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="text-center team-member">
                                    <img src="assets/images/vehicles/mallikarjun-about.jpeg" class="rounded-circle mb-3" width="150" height="150" alt="CTO">
                                    <h5>Mallikarjun</h5>
                                    <p class="text-primary">CTO</p>
                                    <p>Mallikarjun oversees our technology platform, ensuring a seamless experience for all users.</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="text-center team-member">
                                    <img src="assets/images/vehicles/manjula-about.jpeg" class="rounded-circle mb-3" width="150" height="150" alt="COO">
                                    <h5>Manjula</h5>
                                    <p class="text-primary">COO</p>
                                    <p>Manjula manages our day-to-day operations and expansion into new markets across India.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Journey -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-history"></i> Our Journey</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item p-3 mb-3">
                                <h5>2023: The Beginning</h5>
                                <p>rideCore was founded in Mumbai with just 50 vehicles and a small team of 5 people.</p>
                            </div>
                            <div class="timeline-item p-3 mb-3">
                                <h5>2024: Expansion Phase</h5>
                                <p>Expanded to 5 major cities with over 1,000 vehicles and secured our first round of funding.</p>
                            </div>
                            <div class="timeline-item p-3 mb-3">
                                <h5>2025: Technology Upgrade</h5>
                                <p>Launched our web app and implemented real-time tracking and booking features.</p>
                            </div>
                            <div class="timeline-item p-3 mb-3">
                                <h5>2026: Nationwide Presence</h5>
                                <p>Reached 20+ cities across India with 10,000+ vehicles and 100,000+ registered users.</p>
                            </div>
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
</body>
</html>