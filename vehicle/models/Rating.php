<?php
class Rating {
    // Database connection and table name
    private $conn;
    private $table_name = "ratings";
    
    // Object properties
    public $rating_id;
    public $booking_id;
    public $user_id;
    public $rated_id;
    public $rating;
    public $comment;
    public $created_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create rating
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET booking_id=:booking_id, user_id=:user_id, 
                      rated_id=:rated_id, rating=:rating, comment=:comment";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->booking_id = htmlspecialchars(strip_tags($this->booking_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->rated_id = htmlspecialchars(strip_tags($this->rated_id));
        $this->rating = htmlspecialchars(strip_tags($this->rating));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        
        // Bind values
        $stmt->bindParam(":booking_id", $this->booking_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":rated_id", $this->rated_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Check if rating exists
    public function exists() {
        // Query to check if rating exists
        $query = "SELECT rating_id FROM " . $this->table_name . " 
                  WHERE booking_id = ? AND user_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(1, $this->booking_id);
        $stmt->bindParam(2, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // Get average rating for a user
    public function getAverageRating() {
        // Query to get average rating
        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table_name . " 
                  WHERE rated_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $this->rated_id);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['avg_rating'] ? $row['avg_rating'] : 0;
    }
    
    // Get ratings for a user
    public function getRatingsByUser() {
        // Query to get ratings
        $query = "SELECT r.*, u.username as rater_name, b.booking_id 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.user_id = u.user_id
                  JOIN bookings b ON r.booking_id = b.booking_id
                  WHERE r.rated_id = ?
                  ORDER BY r.created_at DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $this->rated_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>