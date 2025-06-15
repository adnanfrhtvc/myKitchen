<?php
/**
 * Recipe Model
 * Handles recipe-related database operations
 */
class Recipe {
    private $db;
    
    public $id;
    public $user_id;
    public $title;
    public $description;
    public $ingredients;
    public $instructions;
    public $prep_time;
    public $cook_time;
    public $servings;
    public $difficulty;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO recipes (user_id, title, description, ingredients, instructions, 
                prep_time, cook_time, servings, difficulty) 
                VALUES (:user_id, :title, :description, :ingredients, :instructions, 
                :prep_time, :cook_time, :servings, :difficulty)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':ingredients', $data['ingredients']);
        $stmt->bindParam(':instructions', $data['instructions']);
        $stmt->bindParam(':prep_time', $data['prep_time']);
        $stmt->bindParam(':cook_time', $data['cook_time']);
        $stmt->bindParam(':servings', $data['servings']);
        $stmt->bindParam(':difficulty', $data['difficulty']);
        
        return $stmt->execute();
    }
    
    public function findByUserId($user_id) {
        $sql = "SELECT * FROM recipes WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM recipes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE recipes SET title = :title, description = :description, 
                ingredients = :ingredients, instructions = :instructions, 
                prep_time = :prep_time, cook_time = :cook_time, 
                servings = :servings, difficulty = :difficulty 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':ingredients', $data['ingredients']);
        $stmt->bindParam(':instructions', $data['instructions']);
        $stmt->bindParam(':prep_time', $data['prep_time']);
        $stmt->bindParam(':cook_time', $data['cook_time']);
        $stmt->bindParam(':servings', $data['servings']);
        $stmt->bindParam(':difficulty', $data['difficulty']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM recipes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
