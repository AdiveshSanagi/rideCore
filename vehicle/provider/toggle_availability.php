<?php
session_start();

// Check if provider is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "service_provider") {
    header("location: ../login.php");
    exit;
}

include_once '../config/database.php';
include_once '../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$vehicle = new Vehicle($db);

if(isset($_GET['id'])) {
    $vehicle->vehicle_id = $_GET['id'];

    if($vehicle->toggleAvailability()) {
        header("Location: vehicles.php?msg=Availability+updated");
        exit;
    } else {
        echo "❌ Failed to update availability.";
    }
} else {
    echo "❌ Invalid request. Vehicle ID missing.";
}
