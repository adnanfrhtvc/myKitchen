<?php
/**
 * Task Model
 * Handles task-related database operations
 */
class Task {
    private $db;
    
    public $id;
    public $user_id;
    public $title;
    public $description;
    public $priority;
    public $status;
    public $due_date;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO tasks (user_id, title, description, priority, status, due_date) 
                VALUES (:user_id, :title, :description, :priority, :status, :due_date)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':due_date', $data['due_date']);
        
        return $stmt->execute();
    }
    
    public function findByUserId($user_id) {
        $sql = "SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM tasks WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE tasks SET title = :title, description = :description, 
                priority = :priority, status = :status, due_date = :due_date 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':due_date', $data['due_date']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM tasks WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
