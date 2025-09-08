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

// Include database connection and user model
include_once '../config/database.php';
include_once '../models/User.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);
$user->user_id = $_SESSION["user_id"];

// Get user details
$user_details = $user->getById();

// Check if user details exist and set default values for missing fields
if(!$user_details) {
    $user_details = [];
}

// Set default values for missing fields
$user_details['username'] = $user_details['username'] ?? $_SESSION["username"];
$user_details['email'] = $user_details['email'] ?? '';
$user_details['phone'] = $user_details['phone'] ?? '';
$user_details['address'] = $user_details['address'] ?? '';
$user_details['created_at'] = $user_details['created_at'] ?? date('Y-m-d H:i:s');
$user_details['updated_at'] = $user_details['updated_at'] ?? date('Y-m-d H:i:s');

// Process form submission
$success_msg = "";
$error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a profile update
    if(isset($_POST["update_profile"])) {
        // Set user properties
        $user->username = $_POST["username"];
        $user->email = $_POST["email"];
        $user->phone = $_POST["phone"];
        $user->address = $_POST["address"];
        
        // Update user profile
        if($user->updateProfile()) {
            $success_msg = "Profile updated successfully.";
            // Update session variables
            $_SESSION["username"] = $user->username;
        } else {
            $error_msg = "Unable to update profile. Please try again.";
        }
    }
    
    // Check if it's a password change
    if(isset($_POST["change_password"])) {
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];
        
        // Verify current password
        if(!$user->verifyPassword($current_password)) {
            $error_msg = "Current password is incorrect.";
        } 
        // Check if new passwords match
        else if($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match.";
        } 
        // Check password length
        else if(strlen($new_password) < 6) {
            $error_msg = "Password must be at least 6 characters long.";
        } 
        // Update password
        else {
            if($user->updatePassword($new_password)) {
                $success_msg = "Password changed successfully.";
            } else {
                $error_msg = "Unable to change password. Please try again.";
            }
        }
    }
    
    // Refresh user details after update
    $user_details = $user->getById();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - rideCore Vehicle Booking</title>
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
                        <a class="nav-link" href="search.php">Search Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">My Bookings</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>My Profile</h2>
                <p>View and update your profile information.</p>
                
                <?php if(!empty($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-user-edit"></i> Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_details['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_details['phone']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user_details['address']); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-key"></i> Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Password must be at least 6 characters long.</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Account Information -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Account Type:</strong> Customer</p>
                                <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user_details['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Last Updated:</strong> <?php echo date('F d, Y', strtotime($user_details['updated_at'])); ?></p>
                                <p><strong>Account Status:</strong> <span class="badge bg-success">Active</span></p>
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