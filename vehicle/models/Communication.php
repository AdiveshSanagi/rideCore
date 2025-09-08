<?php
class Communication {
    private $conn;
    private $table_name = "communications";

    public $communication_id;
    public $request_id;
    public $sender_id;
    public $receiver_id;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Send message
    public function send() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET request_id = :request_id, 
                      sender_id = :sender_id, 
                      receiver_id = :receiver_id, 
                      message = :message";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));
        $this->sender_id = htmlspecialchars(strip_tags($this->sender_id));
        $this->receiver_id = htmlspecialchars(strip_tags($this->receiver_id));
        $this->message = htmlspecialchars(strip_tags($this->message));
        
        // Bind values
        $stmt->bindParam(":request_id", $this->request_id);
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":message", $this->message);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Get conversation
    public function getConversation() {
        $query = "SELECT c.*, u.username as sender_name 
                  FROM " . $this->table_name . " c
                  JOIN users u ON c.sender_id = u.user_id
                  WHERE c.request_id = ? 
                  ORDER BY c.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->request_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>