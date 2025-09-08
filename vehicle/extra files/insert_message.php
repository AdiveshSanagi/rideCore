<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Include database connection
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get logged in user ID
$sender_id = $_SESSION["user_id"]; // logged in customer

// Check if POST request
if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate POST data
    if(!isset($_POST['receiver_id'], $_POST['vehicle_id'], $_POST['message'])) {
        $_SESSION["error"] = "Incomplete message data.";
        header("Location: search.php");
        exit;
    }

    $receiver_id = intval($_POST['receiver_id']); // provider id
    $vehicle_id = intval($_POST['vehicle_id']);   // vehicle id
    $message = trim($_POST['message']);           // message content

    if(empty($message)) {
        $_SESSION["error"] = "Message cannot be empty.";
        header("Location: search.php");
        exit;
    }

    try {
        // Insert message into database
        $sql = "INSERT INTO messages (sender_id, receiver_id, vehicle_id, message) 
                VALUES (:sender_id, :receiver_id, :vehicle_id, :message)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":sender_id", $sender_id, PDO::PARAM_INT);
        $stmt->bindParam(":receiver_id", $receiver_id, PDO::PARAM_INT);
        $stmt->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message, PDO::PARAM_STR);

        if($stmt->execute()) {
            $_SESSION["success"] = "Message sent successfully!";
        } else {
            $_SESSION["error"] = "Failed to send message.";
        }

    } catch (PDOException $e) {
        $_SESSION["error"] = "Database error: " . $e->getMessage();
    }

    // Redirect back to search page
    header("Location: search.php");
    exit;
}
?>
