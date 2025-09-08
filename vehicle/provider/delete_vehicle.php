<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Check service provider
if($_SESSION["user_type"] !== "service_provider"){
    header("location: ../index.php");
    exit;
}

// Include database & Vehicle model
include_once '../config/database.php';
include_once '../models/Vehicle.php';

// Check if vehicle ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: vehicles.php?error=not_found");
    exit;
}

$vehicle_id = intval($_GET['id']);

// Database connection
$database = new Database();
$db = $database->getConnection();

// Initialize vehicle
$vehicle = new Vehicle($db);
$vehicle->vehicle_id = $vehicle_id;
$vehicle->provider_id = $_SESSION["user_id"]; // Security: only delete own vehicles

// Attempt delete
if($vehicle->delete()){
    header("location: vehicles.php?success=deleted");
    exit;
} else {
    header("location: vehicles.php?error=delete_failed");
    exit;
}
?>
