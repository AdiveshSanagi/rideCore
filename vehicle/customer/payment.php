<?php
session_start();
include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    $amount = $_POST['amount'] ?? null;

    if (!$booking_id || !$amount) {
        echo "<script>alert('Invalid request.'); window.location.href='bookings.php';</script>";
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // ✅ Check if booking exists and belongs to this customer
    $query = "SELECT * FROM bookings WHERE booking_id = :booking_id AND customer_id = :customer_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':booking_id', $booking_id);
    $stmt->bindParam(':customer_id', $_SESSION['user_id']);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo "<script>alert('Booking not found or not authorized.'); window.location.href='bookings.php';</script>";
        exit;
    }

    // ✅ Only allow payment if booking is completed & not already paid
    if ($booking['status'] === 'completed' && $booking['payment_done'] == 0) {
        
        // 🔹 Here you would integrate a real payment gateway (Razorpay, Stripe, PayPal)
        // For now, we just mark it as paid
        $update = "UPDATE bookings 
                   SET payment_done = 1, updated_at = NOW() 
                   WHERE booking_id = :booking_id";
        $stmt2 = $db->prepare($update);
        $stmt2->bindParam(':booking_id', $booking_id);
        if ($stmt2->execute()) {
            echo "<script>alert('Payment successful!'); window.location.href='bookings.php';</script>";
            exit;
        } else {
            echo "<script>alert('Payment update failed.'); window.location.href='bookings.php';</script>";
            exit;
        }
    } elseif ($booking['status'] !== 'completed') {
        // ❌ Show alert if provider has not completed
        echo "<script>alert('Provider has not completed this booking yet. Please wait until it is marked as completed.'); window.location.href='bookings.php';</script>";
        exit;
    } else {
        echo "<script>alert('This booking is already paid.'); window.location.href='bookings.php';</script>";
        exit;
    }
}
?>
