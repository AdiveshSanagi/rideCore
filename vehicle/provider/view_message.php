<?php
session_start();

// Check if user is logged in and is a provider
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "provider"){
    header("location: ../login.php");
    exit;
}

include_once '../config/database.php';
include_once '../models/Message.php';
include_once '../models/User.php';
include_once '../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$message = new Message($db);
$user = new User($db);
$vehicle = new Vehicle($db);

$provider_id = $_SESSION['user_id'];
$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($message_id <= 0){
    header("location: provider_inbox.php");
    exit;
}

// Get message details
$msg = $message->getMessageById($message_id, $provider_id);

if(!$msg){
    header("location: provider_inbox.php");
    exit;
}

// Handle reply
$reply_sent = false;
if(isset($_POST['reply'])){
    $reply_text = trim($_POST['reply_text']);
    if(!empty($reply_text)){
        $message->sender_id = $provider_id;
        $message->receiver_id = $msg['sender_id']; // reply to customer
        $message->message_text = $reply_text;
        $message->vehicle_id = $msg['vehicle_id']; // optional: link to same vehicle
        $message->send();
        $reply_sent = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - rideCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <a href="provider_inbox.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Inbox</a>

    <div class="card">
        <div class="card-header">
            From: <?php echo htmlspecialchars($msg['sender_name']); ?>
            <?php if(!empty($msg['vehicle_name'])): ?>
                | Vehicle: <?php echo htmlspecialchars($msg['vehicle_name']); ?>
            <?php endif; ?>
            <span class="float-end"><?php echo date("d M Y, H:i", strtotime($msg['created_at'])); ?></span>
        </div>
        <div class="card-body">
            <p><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Reply</div>
        <div class="card-body">
            <?php if($reply_sent): ?>
                <div class="alert alert-success">Reply sent successfully!</div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <textarea name="reply_text" class="form-control" rows="4" placeholder="Type your reply..." required></textarea>
                </div>
                <button type="submit" name="reply" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
