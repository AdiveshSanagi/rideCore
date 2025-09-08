<?php
class Vehicle {
    // Database connection and table name
    private $conn;
    private $table_name = "vehicles";
    
    // Object properties
    public $vehicle_id;
    public $provider_id;
    public $vehicle_name;
    public $vehicle_type;
    public $license_plate;
    public $model;
    public $year;
    public $capacity;
    public $rate_per_hour;
    public $availability;
    public $latitude;
    public $longitude;
    public $image;
    public $created_at;
    public $updated_at;

    // Extra properties for searches
    public $location;
    public $start_date;
    public $end_date;
    public $min_price;
    public $max_price;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Create a new vehicle
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
            (provider_id, vehicle_name, vehicle_type, license_plate, model, year, 
             capacity, rate_per_hour, availability, latitude, longitude, image, created_at, updated_at) 
            VALUES 
            (:provider_id, :vehicle_name, :vehicle_type, :license_plate, :model, :year, 
             :capacity, :rate_per_hour, :availability, :latitude, :longitude, :image, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->vehicle_name   = htmlspecialchars(strip_tags($this->vehicle_name));
        $this->vehicle_type   = htmlspecialchars(strip_tags($this->vehicle_type));
        $this->license_plate  = htmlspecialchars(strip_tags($this->license_plate));
        $this->model          = htmlspecialchars(strip_tags($this->model));
        $this->year           = htmlspecialchars(strip_tags($this->year));
        $this->capacity       = htmlspecialchars(strip_tags($this->capacity));
        $this->rate_per_hour  = htmlspecialchars(strip_tags($this->rate_per_hour));
        $this->availability   = htmlspecialchars(strip_tags($this->availability));
        $this->latitude       = htmlspecialchars(strip_tags($this->latitude));
        $this->longitude      = htmlspecialchars(strip_tags($this->longitude));
        $this->image          = htmlspecialchars(strip_tags($this->image));

        // bind values
        $stmt->bindParam(":provider_id",   $this->provider_id);
        $stmt->bindParam(":vehicle_name",  $this->vehicle_name);
        $stmt->bindParam(":vehicle_type",  $this->vehicle_type);
        $stmt->bindParam(":license_plate", $this->license_plate);
        $stmt->bindParam(":model",         $this->model);
        $stmt->bindParam(":year",          $this->year);
        $stmt->bindParam(":capacity",      $this->capacity);
        $stmt->bindParam(":rate_per_hour", $this->rate_per_hour);
        $stmt->bindParam(":availability",  $this->availability);
        $stmt->bindParam(":latitude",      $this->latitude);
        $stmt->bindParam(":longitude",     $this->longitude);
        $stmt->bindParam(":image",         $this->image);

        if ($stmt->execute()) {
            $this->vehicle_id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Get vehicles by provider ID
    public function getByProviderId() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE provider_id = :provider_id
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':provider_id', $this->provider_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get available vehicles
    public function getAvailableVehicles() {
        $query = "SELECT v.*, u.username as provider_name 
                  FROM " . $this->table_name . " v
                  JOIN users u ON v.provider_id = u.user_id
                  WHERE v.availability = 1
                  ORDER BY v.rate_per_hour ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get vehicle by ID
    public function getById() {
        $query = "SELECT v.*, u.username as provider_name 
                  FROM " . $this->table_name . " v
                  JOIN users u ON v.provider_id = u.user_id
                  WHERE v.vehicle_id = :vehicle_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vehicle_id', $this->vehicle_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            foreach ($row as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return $row;
        }
        return false;
    }

    // Search vehicles by criteria
    public function searchVehicles($type = null, $capacity = null, $max_rate = null) {
        $query = "SELECT v.*, u.username as provider_name 
                  FROM " . $this->table_name . " v
                  JOIN users u ON v.provider_id = u.user_id
                  WHERE v.availability = 1";
        if($type) {
            $query .= " AND v.vehicle_type = :type";
        }
        if($capacity) {
            $query .= " AND v.capacity >= :capacity";
        }
        if($max_rate) {
            $query .= " AND v.rate_per_hour <= :max_rate";
        }
        $query .= " ORDER BY v.rate_per_hour ASC";
        $stmt = $this->conn->prepare($query);
        if($type) {
            $stmt->bindParam(':type', $type);
        }
        if($capacity) {
            $stmt->bindParam(':capacity', $capacity);
        }
        if($max_rate) {
            $stmt->bindParam(':max_rate', $max_rate);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if vehicle is available for booking
    public function isAvailableForBooking($start_time, $end_time) {
        $query = "SELECT COUNT(*) as booking_count
                  FROM bookings
                  WHERE vehicle_id = :vehicle_id
                  AND status IN ('pending', 'accepted')
                  AND (
                      (start_time <= :start_time AND end_time >= :start_time)
                      OR (start_time <= :end_time AND end_time >= :end_time)
                      OR (start_time >= :start_time AND end_time <= :end_time)
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vehicle_id', $this->vehicle_id);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['booking_count'] == 0);
    }

    // Get all unique vehicle types
    public function getAllVehicleTypes() {
        $query = "SELECT DISTINCT vehicle_type FROM " . $this->table_name . " ORDER BY vehicle_type";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $types = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $row['vehicle_type'];
        }
        return $types;
    }

    // Search for available vehicles
    public function searchAvailable() {
        $query = "SELECT v.*, u.username as provider_name 
                  FROM " . $this->table_name . " v
                  JOIN users u ON v.provider_id = u.user_id
                  WHERE v.availability = 1";
        if(!empty($this->vehicle_type)) {
            $query .= " AND v.vehicle_type = :vehicle_type";
        }
        if(!empty($this->min_price)) {
            $query .= " AND v.rate_per_hour >= :min_price";
        }
        if(!empty($this->max_price)) {
            $query .= " AND v.rate_per_hour <= :max_price";
        }
        if(!empty($this->start_date) && !empty($this->end_date)) {
            $query .= " AND v.vehicle_id NOT IN (
                        SELECT b.vehicle_id FROM bookings b 
                        WHERE b.status IN ('pending', 'accepted') 
                        AND ((b.start_time <= :end_date AND b.end_time >= :start_date))
                    )";
        }
        $query .= " ORDER BY v.rate_per_hour ASC";
        $stmt = $this->conn->prepare($query);
        if(!empty($this->vehicle_type)) {
            $stmt->bindParam(':vehicle_type', $this->vehicle_type);
        }
        if(!empty($this->min_price)) {
            $stmt->bindParam(':min_price', $this->min_price);
        }
        if(!empty($this->max_price)) {
            $stmt->bindParam(':max_price', $this->max_price);
        }
        if(!empty($this->start_date) && !empty($this->end_date)) {
            $stmt->bindParam(':start_date', $this->start_date);
            $stmt->bindParam(':end_date', $this->end_date);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Read one vehicle
    public function readOne() {
        $query = "SELECT v.*, u.username as provider_name 
                  FROM " . $this->table_name . " v
                  LEFT JOIN users u ON v.provider_id = u.user_id
                  WHERE v.vehicle_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->vehicle_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            foreach ($row as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return $row;
        }
        return false;
    }

    // Get average rating for a vehicle
    public function getAverageRating() {
        $query = "SELECT AVG(r.rating) as average_rating, COUNT(r.rating_id) as rating_count
                  FROM ratings r
                  JOIN bookings b ON r.booking_id = b.booking_id
                  WHERE b.vehicle_id = ? AND r.rated_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->vehicle_id);
        $stmt->bindParam(2, $this->provider_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->average_rating = $row['average_rating'] ? round($row['average_rating'], 1) : 0;
        $this->rating_count   = $row['rating_count'] ? $row['rating_count'] : 0;
        return $this->average_rating;
    }

    // Get reviews for a vehicle
    public function getReviews() {
        $query = "SELECT r.*, u.username
                  FROM ratings r
                  JOIN bookings b ON r.booking_id = b.booking_id
                  JOIN users u ON r.user_id = u.user_id
                  WHERE b.vehicle_id = ? AND r.rated_id = ?
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->vehicle_id);
        $stmt->bindParam(2, $this->provider_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Inside Vehicle.php
public function update() {
    $query = "UPDATE " . $this->table_name . " 
              SET 
                vehicle_name = :vehicle_name,
                vehicle_type = :vehicle_type,
                license_plate = :license_plate,
                model = :model,
                year = :year,
                capacity = :capacity,
                rate_per_hour = :rate_per_hour,
                availability = :availability,
                latitude = :latitude,
                longitude = :longitude,
                image = :image,
                updated_at = NOW()
              WHERE vehicle_id = :vehicle_id";

    $stmt = $this->conn->prepare($query);

    // Sanitize
    $this->vehicle_name   = htmlspecialchars(strip_tags($this->vehicle_name));
    $this->vehicle_type   = htmlspecialchars(strip_tags($this->vehicle_type));
    $this->license_plate  = htmlspecialchars(strip_tags($this->license_plate));
    $this->model          = htmlspecialchars(strip_tags($this->model));
    $this->year           = htmlspecialchars(strip_tags($this->year));
    $this->capacity       = htmlspecialchars(strip_tags($this->capacity));
    $this->rate_per_hour  = htmlspecialchars(strip_tags($this->rate_per_hour));
    $this->availability   = htmlspecialchars(strip_tags($this->availability));
    $this->latitude       = htmlspecialchars(strip_tags($this->latitude));
    $this->longitude      = htmlspecialchars(strip_tags($this->longitude));
    $this->image          = htmlspecialchars(strip_tags($this->image));
    $this->vehicle_id     = htmlspecialchars(strip_tags($this->vehicle_id));

    // Bind values
    $stmt->bindParam(":vehicle_name",  $this->vehicle_name);
    $stmt->bindParam(":vehicle_type",  $this->vehicle_type);
    $stmt->bindParam(":license_plate", $this->license_plate);
    $stmt->bindParam(":model",         $this->model);
    $stmt->bindParam(":year",          $this->year);
    $stmt->bindParam(":capacity",      $this->capacity);
    $stmt->bindParam(":rate_per_hour", $this->rate_per_hour);
    $stmt->bindParam(":availability",  $this->availability);
    $stmt->bindParam(":latitude",      $this->latitude);
    $stmt->bindParam(":longitude",     $this->longitude);
    $stmt->bindParam(":image",         $this->image);
    $stmt->bindParam(":vehicle_id",    $this->vehicle_id);

    return $stmt->execute();
}    

// Delete a vehicle
   public function delete() {
    $query = "DELETE FROM " . $this->table_name . " 
              WHERE vehicle_id = :vehicle_id AND provider_id = :provider_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":vehicle_id", $this->vehicle_id);
    $stmt->bindParam(":provider_id", $this->provider_id);
    return $stmt->execute();
}

    // ✅ Update availability of a vehicle
public function updateAvailability() {
    $query = "UPDATE " . $this->table_name . " 
              SET availability = :availability 
              WHERE vehicle_id = :vehicle_id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":availability", $this->availability, PDO::PARAM_INT);
    $stmt->bindParam(":vehicle_id", $this->vehicle_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return true;
    }
    return false;
}
// ✅ Toggle availability (flip 1 ↔ 0)
public function toggleAvailability() {
    $query = "UPDATE " . $this->table_name . " 
              SET availability = CASE WHEN availability = 1 THEN 0 ELSE 1 END 
              WHERE vehicle_id = :vehicle_id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":vehicle_id", $this->vehicle_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return true;
    }
    return false;
}

};
