<?php
/**
 * User Model
 * Handles user-related database operations
 */
class User {
    private $db;
    
    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $first_name;
    public $last_name;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name) 
                VALUES (:username, :email, :password_hash, :first_name, :last_name)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', password_hash($data['password'], PASSWORD_DEFAULT));
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        
        return $stmt->execute();
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function emailExists($email) {
        return $this->findByEmail($email) !== false;
    }
    
    public function usernameExists($username) {
        return $this->findByUsername($username) !== false;
    }
}
?>
