<?php
// provider_inbox.php

session_start();

// Check if logged in and is a provider
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "service_provider") {
    header("location: ../login.php");
    exit;
}

// Include database and Message model
include_once '../config/database.php';
include_once '../models/Message.php';

// Initialize DB and Message object
$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

// ✅ No need to handle reply form here anymore
// ✅ Just fetch provider inbox messages
$messages = $message->getMessagesForProvider($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Inbox - rideCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Inbox</h2>

    <?php if(empty($messages)): ?>
        <div class="alert alert-info">No messages received yet.</div>
    <?php else: ?>
        <?php foreach($messages as $msg): ?>
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong> 
                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($msg['vehicle_name'] ?? 'no Vehicle'); ?></span>
                    <span class="float-end"><?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></span>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($msg['subject']); ?></h5>
                    <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>

                    <!-- ✅ Instead of inline replies, link to chat.php -->
                    <a href="../chat/chat.php?with=<?php echo $msg['sender_id']; ?>&vehicle_id=<?php echo $msg['vehicle_id']; ?>" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-comments"></i> Open Chat
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<button onclick="location.href='dashboard.php'" class="btn btn-secondary m-3">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</button>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
