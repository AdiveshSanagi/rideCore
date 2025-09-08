<?php
session_start();

// Check login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "customer") {
    header("location: ../login.php");
    exit;
}

include_once '../config/database.php';
include_once '../models/Message.php';

// DB connection
$database = new Database();
$db = $database->getConnection();

$messageObj = new Message($db);

// Get provider ID from URL
$provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : 0;

// Form submission
$success = '';
$error = '';

if(isset($_POST['send'])) {
    $messageObj->sender_id = $_SESSION['user_id']; // customer ID
    $messageObj->receiver_id = $provider_id;
    $messageObj->subject = trim($_POST['subject']);
    $messageObj->message = trim($_POST['message']);

    if($messageObj->send()) {
        $success = "Message sent successfully!";
    } else {
        $error = "Failed to send message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Message - rideCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Send Message to Provider</h2>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" required>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
        </div>
        <button type="submit" name="send" class="btn btn-primary">Send Message</button>
        <a href="search.php" class="btn btn-secondary">Back to Search</a>
    </form>
</div>
</body>
</html>
