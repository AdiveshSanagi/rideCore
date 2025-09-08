<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public $booking_id;
    public $customer_id;
    public $vehicle_id;
    public $provider_id;
    public $start_time;
    public $end_time;
    public $pickup_location;
    public $dropoff_location;
    public $status;
    public $total_amount;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get booking by ID
    public function getById() {
        $query = "SELECT b.*, v.vehicle_name, v.vehicle_type, v.model, v.year, v.image,
                         u.username as provider_name
                  FROM " . $this->table_name . " b
                  JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                  JOIN users u ON v.provider_id = u.user_id
                  WHERE b.booking_id = :booking_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':booking_id', $this->booking_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->booking_id = $row['booking_id'];
            $this->customer_id = $row['customer_id'];
            $this->vehicle_id = $row['vehicle_id'];
            $this->start_time = $row['start_time'];
            $this->end_time = $row['end_time'];
            $this->pickup_location = $row['pickup_location'];
            $this->dropoff_location = $row['dropoff_location'];
            $this->status = $row['status'];
            $this->total_amount = $row['total_amount'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        return false;
    }

   public function create() {
    $query = "INSERT INTO bookings (customer_id, vehicle_id, vehicle_name, start_time, end_time, status, pickup_location, dropoff_location, total_amount, created_at)
              VALUES (:customer_id, :vehicle_id, :vehicle_name, :start_time, :end_time, :status, :pickup_location, :dropoff_location, :total_amount, NOW())";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':customer_id', $this->customer_id);
    $stmt->bindParam(':vehicle_id', $this->vehicle_id);
    $stmt->bindParam(':vehicle_name', $this->vehicle_name);
    $stmt->bindParam(':start_time', $this->start_time);
    $stmt->bindParam(':end_time', $this->end_time);
    $stmt->bindParam(':status', $this->status);
    $stmt->bindParam(':pickup_location', $this->pickup_location);
    $stmt->bindParam(':dropoff_location', $this->dropoff_location);
    $stmt->bindParam(':total_amount', $this->total_amount);

    return $stmt->execute();
}

    // Update booking status
    public function updateStatus($status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = NOW() 
                  WHERE booking_id = :booking_id";
        $stmt = $this->conn->prepare($query);
        $status = htmlspecialchars(strip_tags($status));
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':booking_id', $this->booking_id);
        return $stmt->execute();
    }

    // Get bookings for provider
    public function readByProvider() {
        $query = "SELECT b.*, v.vehicle_name, v.license_plate, u.username as customer_name 
                  FROM " . $this->table_name . " b
                  JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                  JOIN users u ON b.customer_id = u.user_id
                  WHERE v.provider_id = :provider_id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':provider_id', $this->provider_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function readByCustomer() {
    $query = "SELECT b.*, v.vehicle_name, u.username AS provider_name
              FROM bookings b
              JOIN vehicles v ON b.vehicle_id = v.vehicle_id
              JOIN users u ON v.provider_id = u.user_id
              WHERE b.customer_id = :customer_id
              ORDER BY b.start_time DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':customer_id', $this->customer_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // Get provider stats
    public function getProviderStats() {
        $stats = [
            'total_requests' => 0,
            'pending_requests' => 0,
            'accepted_requests' => 0,
            'completed_bookings' => 0,
            'cancelled_bookings' => 0,
            'total_earnings' => 0,
            'recent_bookings' => []
        ];

        $query = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                    SUM(CASE WHEN b.status = 'accepted' THEN 1 ELSE 0 END) as accepted_requests,
                    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN b.status IN ('cancelled','rejected') THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN b.status = 'completed' THEN b.total_amount ELSE 0 END) as total_earnings
                  FROM " . $this->table_name . " b
                  JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                  WHERE v.provider_id = :provider_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':provider_id', $this->provider_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $stats['total_requests'] = $row['total_requests'];
            $stats['pending_requests'] = $row['pending_requests'];
            $stats['accepted_requests'] = $row['accepted_requests'];
            $stats['completed_bookings'] = $row['completed_bookings'];
            $stats['cancelled_bookings'] = $row['cancelled_bookings'];
            $stats['total_earnings'] = $row['total_earnings'] ?? 0;
        }

        $query2 = "SELECT b.*, v.vehicle_name, u.username as customer_name 
                   FROM " . $this->table_name . " b
                   JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                   JOIN users u ON b.customer_id = u.user_id
                   WHERE v.provider_id = :provider_id
                   ORDER BY b.created_at DESC
                   LIMIT 5";

        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bindParam(':provider_id', $this->provider_id);
        $stmt2->execute();
        $stats['recent_bookings'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
    public function getActiveBookingsByCustomer(){
    $query = "SELECT b.*, v.vehicle_name FROM bookings b 
              JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
              WHERE b.customer_id = :customer_id 
              AND b.status IN ('pending','accepted') 
              ORDER BY b.start_time DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':customer_id', $this->customer_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getPastBookingsByCustomer(){
    $query = "SELECT b.*, v.vehicle_name FROM bookings b 
              JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
              WHERE b.customer_id = :customer_id 
              AND b.status IN ('rejected','completed','cancelled') 
              ORDER BY b.start_time DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':customer_id', $this->customer_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getByCustomerId() {
    $query = "SELECT b.*, v.vehicle_name, u.username AS provider_name
              FROM bookings b
              JOIN vehicles v ON b.vehicle_id = v.vehicle_id
              JOIN users u ON v.provider_id = u.user_id
              WHERE b.customer_id = :customer_id
              ORDER BY b.start_time DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':customer_id', $this->customer_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



}
?>
