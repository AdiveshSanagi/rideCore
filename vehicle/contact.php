<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Include your database connection
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) 
            VALUES (:name, :email, :phone, :subject, :message)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Thank you for your message! We will get back to you soon.</div>';
    } else {
        echo '<div class="alert alert-danger">Something went wrong. Please try again later.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - rideCore Vehicle Booking</title>
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
        .form-control, .form-select {
            background-color: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            background-color: #2d2d2d;
            color: #e0e0e0;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .contact-info-item {
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
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
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

    <!-- Contact Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Contact Us</h2>
                <p class="lead">We'd love to hear from you. Get in touch with our team.</p>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Contact Form -->
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-envelope"></i> Send Us a Message</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Process form submission
                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            // In a real application, you would validate and process the form data here
                            echo '<div class="alert alert-success">Thank you for your message! We will get back to you soon.</div>';
                        }
                        ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="booking">Booking Inquiry</option>
                                    <option value="support">Customer Support</option>
                                    <option value="partnership">Partnership Opportunity</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-md-5">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="contact-info-item p-3 mb-3">
                            <h5><i class="fas fa-map-marker-alt text-primary me-2"></i> Our Office</h5>
                            <p class="mb-0">
                                VRIF Headquarters<br>
                                VRIF VTU<br>
                                Belagavi, Karnataka 590001<br>
                                India
                            </p>
                        </div>
                        <div class="contact-info-item p-3 mb-3">
                            <h5><i class="fas fa-phone text-primary me-2"></i> Phone</h5>
                            <p class="mb-0">
                                Customer Support: +91 9148 020 911<br>
                                Booking Inquiries: +91 7406 614 912<br>
                                Business Hours: 9:00 AM - 6:00 PM (IST), Monday to Saturday
                            </p>
                        </div>
                        <div class="contact-info-item p-3 mb-3">
                            <h5><i class="fas fa-envelope text-primary me-2"></i> Email</h5>
                            <p class="mb-0">
                                General Inquiries: info@ridecore.com<br>
                                Customer Support: support@ridecore.com<br>
                                Business Partnerships: partners@ridecore.com
                            </p>
                        </div>
                        <div class="contact-info-item p-3">
                            <h5><i class="fas fa-share-alt text-primary me-2"></i> Connect With Us</h5>
                            <div class="social-icons mt-2">
                                <a href="#" class="text-primary me-3"><i class="fab fa-facebook-f fa-2x"></i></a>
                                <a href="#" class="text-primary me-3"><i class="fab fa-twitter fa-2x"></i></a>
                                <a href="#" class="text-primary me-3"><i class="fab fa-instagram fa-2x"></i></a>
                                <a href="#" class="text-primary me-3"><i class="fab fa-linkedin-in fa-2x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-map"></i> Find Us</h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Replace with your actual Google Maps embed code -->
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3910.1234567890123!2d74.59344337910156!3d15.862565200000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bbf669f5095362f%3A0xabcdef1234567890!2sVY%20V.R.I.F.!5e0!3m2!1sen!2sin!4v1693599999999!5m2!1sen!2sin"  width="600" height="450"  style="border:0;"  allowfullscreen=""  loading="lazy"></iframe>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-question-circle"></i> Frequently Asked Questions</h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="contactFAQ">
                            <div class="accordion-item bg-dark text-white border-secondary">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        How do I book a vehicle?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#contactFAQ">
                                    <div class="accordion-body">
                                        To book a vehicle, simply browse our available vehicles, select the one you want, choose your dates, and complete the booking process. You'll need to be registered and logged in to make a booking.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item bg-dark text-white border-secondary">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        What payment methods do you accept?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#contactFAQ">
                                    <div class="accordion-body">
                                        We accept all major credit and debit cards, UPI payments, net banking, and digital wallets like PayTM and Google Pay.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item bg-dark text-white border-secondary">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        How can I cancel my booking?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#contactFAQ">
                                    <div class="accordion-body">
                                        You can cancel your booking through your account dashboard. Please note that cancellation policies vary depending on how far in advance you cancel. Check our terms and conditions for more details.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item bg-dark text-white border-secondary">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        How do I list my vehicle for rent?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#contactFAQ">
                                    <div class="accordion-body">
                                        To list your vehicle, register as a provider, complete your profile verification, and then add your vehicle details through your provider dashboard. Our team will review and approve your listing.
                                    </div>
                                </div>
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
                    <p>Email: support@ridecore.com<br>Phone:+91 9148020911</p>
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