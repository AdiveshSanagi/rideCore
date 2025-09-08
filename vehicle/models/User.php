<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // Object properties
    // Object properties
public $user_id;
public $username;
public $email;
public $password;
public $user_type;
public $reset_token;
public $reset_token_expires;
public $created_at;
public $updated_at;
public $phone;     
public $address;   

    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Register user
    public function register() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, email=:email, password=:password, user_type=:user_type";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->user_type = htmlspecialchars(strip_tags($this->user_type));
        
        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":user_type", $this->user_type);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Login user
    public function login() {
        // Query to check if email exists
        $query = "SELECT user_id, username, password, user_type FROM " . $this->table_name . " WHERE email = ?";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the email
        $stmt->bindParam(1, $this->email);
        
        // Execute the query
        $stmt->execute();
        
        // Check if email exists
        if($stmt->rowCount() > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if(password_verify($this->password, $row['password'])) {
                // Set values to object properties
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                $this->user_type = $row['user_type'];
                
                return true;
            }
        }
        
        return false;
    }
    
    // Check if email exists
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT user_id, username, password, user_type FROM " . $this->table_name . " WHERE email = ?";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the email
        $stmt->bindParam(1, $this->email);
        
        // Execute the query
        $stmt->execute();
        
        // Return true if email exists, false otherwise
        return $stmt->rowCount() > 0;
    }
    
    // Generate password reset token
    public function generateResetToken() {
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        
        // Set token expiry time (1 hour from now)
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Update user with reset token
        $query = "UPDATE " . $this->table_name . " 
                  SET reset_token = :token, reset_token_expires = :expires 
                  WHERE email = :email";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $this->email);
        
        // Execute the query
        if($stmt->execute()) {
            $this->reset_token = $token;
            $this->reset_token_expires = $expires;
            return true;
        }
        
        return false;
    }
    
    // Verify reset token
    public function verifyResetToken($token) {
        // Query to check if token exists and is not expired
        $query = "SELECT user_id, username, email FROM " . $this->table_name . " 
                  WHERE reset_token = :token AND reset_token_expires > NOW()";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the token
        $stmt->bindParam(':token', $token);
        
        // Execute the query
        $stmt->execute();
        
        // Check if token is valid
        if($stmt->rowCount() > 0) {
            // Get user details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set user properties
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            
            return true;
        }
        
        return false;
    }
    
    // Check if reset token is valid
    // Check if reset token is valid
    public function checkResetToken() {
        // Query to check if token exists and is not expired
        $query = "SELECT user_id FROM " . $this->table_name . " 
                  WHERE reset_token = :reset_token 
                  AND reset_token_expires > NOW()";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the values
        $stmt->bindParam(":reset_token", $this->reset_token);
        
        // Execute the query
        $stmt->execute();
        
        // Check if token exists
        if($stmt->rowCount() > 0) {
            // Get user ID
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->user_id = $row['user_id'];
            return true;
        }
        
        return false;
    }
    
    // Reset password (original method at line ~200)
    public function resetPassword() {
        // Query to update password and clear reset token
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password, reset_token = NULL, reset_token_expires = NULL 
                  WHERE reset_token = :reset_token";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Hash the new password if it's not already hashed
        if(strlen($this->password) < 60) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }
        
        // Bind parameters
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':reset_token', $this->reset_token);
        
        // Execute the query
        return $stmt->execute();
    }
    
    // Get user by ID
    // Add or update the getById method in the User class
    public function getById() {
        // Query to get user by ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the user ID
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute the query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            // Set values to object properties
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->phone = isset($row['phone']) ? $row['phone'] : '';
            $this->address = isset($row['address']) ? $row['address'] : '';
            $this->user_type = $row['user_type'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return $row;
        }
        
        return false;
    }
    
    // Add the updateProfile method if it doesn't exist
    public function updateProfile() {
        // Query to update user profile
        $query = "UPDATE " . $this->table_name . "
                  SET 
                    username = :username,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    updated_at = NOW()
                  WHERE user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Add the verifyPassword method if it doesn't exist
    public function verifyPassword($password) {
        // Query to get user password
        $query = "SELECT password FROM " . $this->table_name . " WHERE user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the user ID
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute the query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            // Verify password
            return password_verify($password, $row['password']);
        }
        
        return false;
    }
    
    // Add the updatePassword method if it doesn't exist
    public function updatePassword($new_password) {
        // Query to update user password
        $query = "UPDATE " . $this->table_name . "
                  SET 
                    password = :password,
                    updated_at = NOW()
                  WHERE user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Hash the password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Add these methods to your User class
    
    // Validate reset token
    public function validateResetToken() {
        // Query to check if token exists and is not expired
        $query = "SELECT user_id, email FROM " . $this->table_name . " 
                  WHERE reset_token = ? AND reset_token_expires > NOW()";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        
        // Bind the token
        $stmt->bindParam(1, $this->reset_token);
        
        // Execute the query
        $stmt->execute();
        
        // Check if token exists
        if($stmt->rowCount() > 0) {
            // Get user data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set user properties
           $this->user_id = $row['user_id'];
            $this->email = $row['email'];
            
            return true;
        }
        
        return false;
    }
    
    // Remove this duplicate resetPassword method (around line 371)
    // public function resetPassword() {
    //     // Hash the password
    //     $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
    //     
    //     // Update query
    //     $query = "UPDATE " . $this->table_name . " 
    //               SET password = ?, reset_token = NULL, reset_token_expires = NULL 
    //               WHERE reset_token = ?";
    //     
    //     // Prepare the statement
    //     $stmt = $this->conn->prepare($query);
    //     
    //     // Bind parameters
    //     $stmt->bindParam(1, $password_hash);
    //     $stmt->bindParam(2, $this->reset_token);
    //     
    //     // Execute the query
    //     if($stmt->execute()) {
    //         return true;
    //     }
    //     
    //     return false;
    // }
    
    // Add this method to your User class
    
    // Save reset token
    public function saveResetToken() {
        // Update query
        $query = "UPDATE " . $this->table_name . " 
                  SET reset_token = ?, reset_token_expires = ? 
                  WHERE email = ?";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $this->reset_token);
        $stmt->bindParam(2, $this->reset_token_expires);
        $stmt->bindParam(3, $this->email);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>