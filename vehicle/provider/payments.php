<?php
session_start();

// Check login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Check service provider
if($_SESSION["user_type"] !== "service_provider"){
    header("location: ../index.php");
    exit;
}

// Include DB & Models
include_once '../config/database.php';
include_once '../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$booking->provider_id = $_SESSION["user_id"];

// Get status filter from URL (?status=pending/completed)
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'pending';

$query = "SELECT b.booking_id, b.total_amount, b.status, b.payment_done, b.start_time, b.end_time,
                 u.username as customer_name, v.vehicle_name
          FROM bookings b
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id
          JOIN users u ON b.customer_id = u.user_id
          WHERE v.provider_id = :provider_id";

if($statusFilter === "pending"){
    $query .= " AND b.payment_done = 0 AND b.status = 'completed'";
} elseif($statusFilter === "completed"){
    $query .= " AND b.payment_done = 1 AND b.status = 'completed'";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':provider_id', $booking->provider_id);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2><?php echo ucfirst($statusFilter); ?> Payments</h2>
    <table class="table table-bordered table-striped mt-3">
        <thead class="thead-dark">
            <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Amount</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if(count($payments) > 0): ?>
            <?php foreach($payments as $pay): ?>
            <tr>
                <td><?php echo $pay['booking_id']; ?></td>
                <td><?php echo htmlspecialchars($pay['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($pay['vehicle_name']); ?></td>
                <td><?php echo $pay['start_time']; ?></td>
                <td><?php echo $pay['end_time']; ?></td>
                <td>₹<?php echo number_format($pay['total_amount'],2); ?></td>
                <td>
                    <?php echo $pay['payment_done'] ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-warning">Pending</span>'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No <?php echo $statusFilter; ?> payments found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html>
