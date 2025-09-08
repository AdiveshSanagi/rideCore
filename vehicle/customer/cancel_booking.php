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

// Check if booking ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: bookings.php?error=invalid_request");
    exit;
}

// Include database connection
include_once '../config/database.php';
include_once '../models/Booking.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize booking object
$booking = new Booking($db);
$booking->booking_id = $_GET["id"];

// Get booking details
$booking_details = $booking->getById();

// Check if booking exists
if(!$booking_details) {
    header("location: bookings.php?error=not_found");
    exit;
}

// Check if the booking belongs to the current customer
if($booking->customer_id != $_SESSION["user_id"]) {
    header("location: bookings.php?error=unauthorized");
    exit;
}

// Check if booking is in a status that can be cancelled (only pending bookings can be cancelled)
if($booking->status !== "pending") {
    header("location: bookings.php?error=cannot_cancel");
    exit;
}

// Update booking status to cancelled
$booking->status = "cancelled";
$booking->updated_at = date('Y-m-d H:i:s');

// Cancel the booking
if($booking->updateStatus("cancelled")) {
    header("location: bookings.php?success=cancelled");
    exit;
} else {
    header("location: bookings.php?error=cancel_failed");
    exit;
}

?>