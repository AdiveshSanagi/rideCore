<?php
session_start();
require('../razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

include_once '../config/database.php';

$keyId = "YOUR_KEY_ID";
$keySecret = "YOUR_KEY_SECRET";

$api = new Api($keyId, $keySecret);

$payment_id = $_POST['razorpay_payment_id'];
$booking_id = $_POST['booking_id'];

try {
    $payment = $api->payment->fetch($payment_id);

    if ($payment->status == 'captured') {
        // Update booking table
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare("UPDATE bookings SET payment_done = 1 WHERE booking_id = ?");
        $stmt->execute([$booking_id]);

        echo "<h2>Payment Successful for Booking #$booking_id</h2>";
        echo "<a href='bookings.php'>Go Back</a>";
    } else {
        echo "Payment not captured. Please try again.";
    }
} catch (Exception $e) {
    echo "Payment Failed: " . $e->getMessage();
}
