<?php
// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Check if user is a provider
if($_SESSION["user_type"] !== "service_provider") {
    header("location: ../index.php");
    exit;
}

// Include database and class
include_once '../config/database.php';
include_once '../models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();

$vehicle = new Vehicle($db);

// ✅ Get vehicle_id from query string
if(!isset($_GET['id'])) {
    die("Vehicle ID missing.");
}
$vehicle->vehicle_id = $_GET['id'];

// Load existing vehicle data
$currentData = $vehicle->getById();
if(!$currentData) {
    die("Vehicle not found.");
}

// ✅ Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle->vehicle_id     = $_POST['vehicle_id'];
    $vehicle->vehicle_name   = $_POST['vehicle_name'];
    $vehicle->vehicle_type   = $_POST['vehicle_type'];
    $vehicle->license_plate  = $_POST['license_plate'];
    $vehicle->model          = $_POST['model'];
    $vehicle->year           = $_POST['year'];
    $vehicle->capacity       = $_POST['capacity'];
    $vehicle->rate_per_hour  = $_POST['rate_per_hour'];
    $vehicle->availability   = $_POST['availability'];
    $vehicle->latitude       = $_POST['latitude'];
    $vehicle->longitude      = $_POST['longitude'];

    // ✅ Handle image upload (optional)
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "../assets/images/vehicles/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if(move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $vehicle->image = "assets/images/vehicles/" . $fileName;
        } else {
            $vehicle->image = $currentData['image']; // keep old image if upload fails
        }
    } else {
        $vehicle->image = $currentData['image']; // keep old image
    }

    // Update in DB
    if($vehicle->update()) {
    header("Location: dashboard.php?msg=Vehicle+updated+successfully");
    exit;
}

    } else {
        $error = "Failed to update vehicle.";
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vehicle</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Edit Vehicle</h2>

    <?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

    <form action="edit_vehicle.php?id=<?php echo $vehicle->vehicle_id; ?>" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($currentData['vehicle_id']); ?>">

        <label>Vehicle Name:</label>
        <input type="text" name="vehicle_name" value="<?php echo htmlspecialchars($currentData['vehicle_name']); ?>" required><br>

        <label>Vehicle Type:</label>
        <input type="text" name="vehicle_type" value="<?php echo htmlspecialchars($currentData['vehicle_type']); ?>" required><br>

        <label>License Plate:</label>
        <input type="text" name="license_plate" value="<?php echo htmlspecialchars($currentData['license_plate']); ?>" required><br>

        <label>Model:</label>
        <input type="text" name="model" value="<?php echo htmlspecialchars($currentData['model']); ?>" required><br>

        <label>Year:</label>
        <input type="number" name="year" value="<?php echo htmlspecialchars($currentData['year']); ?>" required><br>

        <label>Capacity:</label>
        <input type="number" name="capacity" value="<?php echo htmlspecialchars($currentData['capacity']); ?>" required><br>

        <label>Rate per Hour (INR):</label>
        <input type="number" name="rate_per_hour" value="<?php echo htmlspecialchars($currentData['rate_per_hour']); ?>" required><br>

        <label>Availability:</label>
        <select name="availability">
            <option value="1" <?php if($currentData['availability']==1) echo "selected"; ?>>Available</option>
            <option value="0" <?php if($currentData['availability']==0) echo "selected"; ?>>Unavailable</option>
        </select><br>

        <label>Latitude:</label>
        <input type="text" name="latitude" value="<?php echo htmlspecialchars($currentData['latitude']); ?>"><br>

        <label>Longitude:</label>
        <input type="text" name="longitude" value="<?php echo htmlspecialchars($currentData['longitude']); ?>"><br>

        <label>Current Image:</label><br>
        <?php if(!empty($currentData['image'])): ?>
            <img src="../<?php echo htmlspecialchars($currentData['image']); ?>" width="150"><br>
        <?php endif; ?>
        <input type="file" name="image"><br><br>

        <button type="submit">Update Vehicle</button>
    </form>
</body>
</html>
