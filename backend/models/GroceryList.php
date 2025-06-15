<?php
/**
 * Grocery List Model
 * Handles grocery list-related database operations
 */
class GroceryList {
    private $db;
    
    public $id;
    public $user_id;
    public $item_name;
    public $quantity;
    public $is_completed;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO grocery_lists (user_id, item_name, quantity, is_completed) 
                VALUES (:user_id, :item_name, :quantity, :is_completed)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':item_name', $data['item_name']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':is_completed', $data['is_completed']);
        
        return $stmt->execute();
    }
    
    public function findByUserId($user_id) {
        $sql = "SELECT * FROM grocery_lists WHERE user_id = :user_id ORDER BY is_completed ASC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM grocery_lists WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE grocery_lists SET item_name = :item_name, quantity = :quantity, 
                is_completed = :is_completed WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':item_name', $data['item_name']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':is_completed', $data['is_completed']);
        
        return $stmt->execute();
    }
    
    public function toggleCompleted($id) {
        $sql = "UPDATE grocery_lists SET is_completed = NOT is_completed WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM grocery_lists WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function clearCompleted($user_id) {
        $sql = "DELETE FROM grocery_lists WHERE user_id = :user_id AND is_completed = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
}
?>
