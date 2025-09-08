<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "service_provider"){
    header("location: ../login.php");
    exit;
}

if(!isset($_GET["id"]) || !isset($_GET["action"])){
    header("location: bookings.php?error=invalid_request");
    exit;
}

include_once '../config/database.php';
include_once '../models/Booking.php';
include_once '../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$booking->booking_id = $_GET["id"];
$booking_details = $booking->getById();

if(!$booking_details){
    header("location: bookings.php?error=not_found");
    exit;
}

// Set booking object properties after fetching from DB
$booking->status = $booking_details['status'];
$booking->vehicle_id = $booking_details['vehicle_id'];  // Make sure this is set correctly

$vehicle = new Vehicle($db);
$vehicle->vehicle_id = $booking->vehicle_id;
$vehicle->getById();

if($vehicle->provider_id != $_SESSION["user_id"]){
    header("location: bookings.php?error=unauthorized");
    exit;
}

$action = $_GET["action"];
$success = false;

switch($action){
    case 'accept':
        if(strtolower($booking->status) == 'pending'){
            $success = $booking->updateStatus('accepted');
        }
        break;
    case 'reject':
        if(strtolower($booking->status) == 'pending'){
            $success = $booking->updateStatus('rejected');
        }
        break;
    case 'complete':
        if(strtolower($booking->status) == 'accepted'){
            $success = $booking->updateStatus('completed');
        }
        break;
    default:
        header("location: bookings.php?error=invalid_action");
        exit;
}

if($success){
    header("location: dashboard.php?success=status_updated");
}else{
    header("location: dashboard.php?error=update_failed");
}
exit;
?>
