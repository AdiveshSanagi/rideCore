<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION["user_id"];

// Fetch inbox (messages received)
$sql_inbox = "SELECT m.*, u.username AS sender_name, v.vehicle_name 
              FROM messages m
              JOIN users u ON m.sender_id = u.user_id
              JOIN vehicles v ON m.vehicle_id = v.vehicle_id
              WHERE m.receiver_id = :user_id
              ORDER BY m.created_at DESC";
$stmt_inbox = $db->prepare($sql_inbox);
$stmt_inbox->bindParam(":user_id", $user_id);
$stmt_inbox->execute();
$inbox = $stmt_inbox->fetchAll(PDO::FETCH_ASSOC);

// Fetch sent messages
$sql_sent = "SELECT m.*, u.username AS receiver_name, v.vehicle_name 
             FROM messages m
             JOIN users u ON m.receiver_id = u.id
             JOIN vehicles v ON m.vehicle_id = v.vehicle_id
             WHERE m.sender_id = :user_id
             ORDER BY m.created_at DESC";
$stmt_sent = $db->prepare($sql_sent);
$stmt_sent->bindParam(":user_id", $user_id);
$stmt_sent->execute();
$sent = $stmt_sent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Messages - rideCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2><i class="fas fa-envelope"></i> My Messages</h2>
        <ul class="nav nav-tabs" id="messageTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inbox">Inbox</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sent">Sent</button>
            </li>
        </ul>
        <div class="tab-content mt-3">
            <!-- Inbox -->
            <div class="tab-pane fade show active" id="inbox">
                <?php if (empty($inbox)): ?>
                    <div class="alert alert-info">No messages received.</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($inbox as $msg): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                (about <em><?php echo htmlspecialchars($msg['vehicle_name']); ?></em>):<br>
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                <div class="text-muted small"><?php echo $msg['created_at']; ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Sent -->
            <div class="tab-pane fade" id="sent">
                <?php if (empty($sent)): ?>
                    <div class="alert alert-info">No messages sent.</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($sent as $msg): ?>
                            <li class="list-group-item">
                                To <strong><?php echo htmlspecialchars($msg['receiver_name']); ?></strong>
                                (about <em><?php echo htmlspecialchars($msg['vehicle_name']); ?></em>):<br>
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                <div class="text-muted small"><?php echo $msg['created_at']; ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
