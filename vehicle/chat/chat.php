<?php
session_start();
require_once '../config/database.php';
require_once '../models/Message.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$messageObj = new Message($db);

$current_user = $_SESSION['user_id'];  // logged in user
$chat_with   = $_GET['with'];         // user we are chatting with (provider/customer)
$vehicle_id  = isset($_GET['vehicle_id']) ? $_GET['vehicle_id'] : null;

// ✅ Handle sending message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])) {
    $messageObj->sender_id   = $current_user;
    $messageObj->receiver_id = $chat_with;
    $messageObj->vehicle_id  = $vehicle_id;
    $messageObj->subject     = "No Subject"; // fallback to avoid NULL
    $messageObj->message     = $_POST['message'];
    $messageObj->parent_id   = null; // since conversation is threaded
    $messageObj->send();
    header("Location: chat.php?with=$chat_with&vehicle_id=$vehicle_id");
    exit;
}

// ✅ Get full conversation
$conversation = $messageObj->getConversation($current_user, $chat_with, $vehicle_id);

// ✅ Decide correct inbox link
$inbox_link = "#";
if ($_SESSION["user_type"] === "customer") {
    $inbox_link = "../customer/customer_inbox.php";
} elseif ($_SESSION["user_type"] === "service_provider") {
    $inbox_link = "../provider/provider_inbox.php";
} elseif ($_SESSION["user_type"] === "admin") {
    $inbox_link = "../admin/dashboard.php"; // adjust if you have admin_inbox.php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .chat-box {
        height: 500px;
        overflow-y: auto;
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    .message {
        margin: 10px 0;
        padding: 10px 15px;
        border-radius: 15px;
        max-width: 70%;
        display: inline-block;
    }
    .sent {
        background: #007bff;
        color: white;
        margin-left: auto;
        display: block;
        text-align: right;
    }
    .received {
        background: #e9ecef;
        color: black;
        margin-right: auto;
        display: block;
        text-align: left;
    }
  </style>
</head>
<body class="container mt-4">

  <h3 class="mb-3">Chat with User #<?= htmlspecialchars($chat_with) ?></h3>

  <div class="chat-box mb-3">
    <?php foreach ($conversation as $msg): ?>
      <div class="message <?= $msg['sender_id'] == $current_user ? 'sent' : 'received' ?>">
        <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong><br>
        <?= nl2br(htmlspecialchars($msg['message'])) ?><br>
        <small class="text-muted"><?= $msg['created_at'] ?></small>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Send message form -->
  <form method="POST" class="d-flex">
    <input type="text" name="message" class="form-control me-2" placeholder="Type your message..." required>
    <button type="submit" class="btn btn-primary">Send</button>
  </form>

  <!-- ✅ Fixed Back Button -->
  <a href="<?= $inbox_link ?>" class="btn btn-secondary mt-3">Back to Inbox</a>

</body>
</html>
