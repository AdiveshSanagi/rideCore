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

// Check if user details exist and set default values
if(!$user_details) {
    $user_details = [];
}

$user_details['username'] = $user_details['username'] ?? $_SESSION["username"];
$user_details['email'] = $user_details['email'] ?? '';
$user_details['phone'] = $user_details['phone'] ?? '';
$user_details['address'] = $user_details['address'] ?? '';
$user_details['created_at'] = $user_details['created_at'] ?? date('Y-m-d H:i:s');
$user_details['updated_at'] = $user_details['updated_at'] ?? date('Y-m-d H:i:s');

$success_msg = "";
$error_msg = "";

// Handle form submissions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update profile
    if(isset($_POST["update_profile"])) {
        $user->username = $_POST["username"];
        $user->email = $_POST["email"];
        $user->phone = $_POST["phone"];
        $user->address = $_POST["address"];

        if($user->updateProfile()) {
            $success_msg = "Profile updated successfully.";
            $_SESSION["username"] = $user->username;
        } else {
            $error_msg = "Unable to update profile. Please try again.";
        }
    }

    // Change password
    if(isset($_POST["change_password"])) {
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        if(!$user->verifyPassword($current_password)) {
            $error_msg = "Current password is incorrect.";
        } elseif($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match.";
        } elseif(strlen($new_password) < 6) {
            $error_msg = "Password must be at least 6 characters long.";
        } else {
            if($user->updatePassword($new_password)) {
                $success_msg = "Password changed successfully.";
            } else {
                $error_msg = "Unable to change password. Please try again.";
            }
        }
    }

    $user_details = $user->getById();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - rideCore Service Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <span class="text-primary">rideCore</span> Service Provider
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="vehicles.php">My Vehicles</a></li>
                    <li class="nav-item"><a class="nav-link" href="bookings.php">Bookings</a></li>
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
        <h2>My Profile</h2>
        <p>View and update your provider profile information.</p>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-user-edit"></i> Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user_details['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user_details['phone']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user_details['address']); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
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
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required>
                                <small class="text-muted">Password must be at least 6 characters long.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
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
                                <p><strong>Account Type:</strong> Service Provider</p>
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

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="text-center">&copy; <?php echo date('Y'); ?> rideCore Service Provider. All rights reserved.</div>
        </div>
    </footer>
</body>
</html>
