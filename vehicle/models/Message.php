<?php
class Message {
    private $conn;
    private $table = 'messages';

    public $id;
    public $sender_id;
    public $receiver_id;
    public $vehicle_id;
    public $subject;
    public $message;
    public $parent_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Send a message or reply
    public function send() {
    $query = "INSERT INTO {$this->table} 
              (sender_id, receiver_id, vehicle_id, subject, message, parent_id, created_at) 
              VALUES 
              (:sender_id, :receiver_id, :vehicle_id, :subject, :message, :parent_id, NOW())";

    $stmt = $this->conn->prepare($query);

    $vehicle_id = !empty($this->vehicle_id) ? $this->vehicle_id : null;
    $subject    = !empty($this->subject) ? $this->subject : "No Subject"; // ✅ Default value
    $parent_id  = !empty($this->parent_id) ? $this->parent_id : null;

    $stmt->bindParam(':sender_id', $this->sender_id);
    $stmt->bindParam(':receiver_id', $this->receiver_id);
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $this->message);
    $stmt->bindParam(':parent_id', $parent_id);

    return $stmt->execute();
}


    // ⚠ Old: only fetched received messages
    // ✅ Fixed: fetch both sent and received (like inbox+sent together)
    public function getMessagesByUser($user_id) {
        $query = "SELECT m.*, u.username AS sender_name, v.vehicle_name
                  FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.user_id
                  LEFT JOIN vehicles v ON m.vehicle_id = v.vehicle_id
                  WHERE m.receiver_id = :user_id OR m.sender_id = :user_id
                  ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Provider inbox view (still works)
    public function getMessagesForProvider($provider_id) {
        $query = "SELECT m.*, u.username AS sender_name, v.vehicle_name
                  FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.user_id
                  LEFT JOIN vehicles v ON m.vehicle_id = v.vehicle_id
                  WHERE m.receiver_id = :provider_id
                  ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':provider_id', $provider_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Single message fetch
    public function getMessageById($message_id, $user_id) {
        $query = "SELECT m.*, u.username AS sender_name, v.vehicle_name
                  FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.user_id
                  LEFT JOIN vehicles v ON m.vehicle_id = v.vehicle_id
                  WHERE m.message_id = :message_id 
                  AND (m.receiver_id = :user_id OR m.sender_id = :user_id)
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':message_id', $message_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Replies to a message
    public function getReplies($parent_id) {
        $query = "SELECT m.*, u.username AS sender_name
                  FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.user_id
                  WHERE m.parent_id = :parent_id
                  ORDER BY m.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🚀 NEW: Get full conversation (WhatsApp-style chat)
    public function getConversation($user1, $user2, $vehicle_id = null) {
        $query = "SELECT m.*, u.username AS sender_name
                  FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.user_id
                  WHERE ((m.sender_id = :user1 AND m.receiver_id = :user2)
                      OR (m.sender_id = :user2 AND m.receiver_id = :user1))";

        if ($vehicle_id !== null) {
            $query .= " AND m.vehicle_id = :vehicle_id";
        }

        $query .= " ORDER BY m.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1', $user1);
        $stmt->bindParam(':user2', $user2);
        if ($vehicle_id !== null) {
            $stmt->bindParam(':vehicle_id', $vehicle_id);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
