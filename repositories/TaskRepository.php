<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../config/Database.php';

class TaskRepository {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function createTask(Task $task) {
        $stmt = $this->conn->prepare(
            "INSERT INTO tasks (user_id, title, description, status, due_date) 
            VALUES (:user_id, :title, :description, :status, :due_date)"
        );
        
        return $stmt->execute([
            ':user_id' => $task->getUserId(),
            ':title' => $task->getTitle(),
            ':description' => $task->getDescription(),
            ':status' => $task->getStatus(),
            ':due_date' => $task->getDueDate()
        ]);
    }

    public function getTaskById($taskId, $userId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM tasks 
            WHERE task_id = :task_id AND user_id = :user_id"
        );
        
        $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId
        ]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? new Task(
            $data['user_id'],
            $data['title'],
            $data['description'],
            $data['status'],
            $data['due_date'],
            $data['task_id'],
            $data['created_at']
        ) : null;
    }

    public function getUserTasks($userId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM tasks 
            WHERE user_id = :user_id 
            ORDER BY due_date ASC"
        );
        
        $stmt->execute([':user_id' => $userId]);
        
        $tasks = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task(
                $data['user_id'],
                $data['title'],
                $data['description'],
                $data['status'],
                $data['due_date'],
                $data['task_id'],
                $data['created_at']
            );
        }
        return $tasks;
    }

    public function updateTask(Task $task) {
        $stmt = $this->conn->prepare(
            "UPDATE tasks SET
            title = :title,
            description = :description,
            status = :status,
            due_date = :due_date
            WHERE task_id = :task_id AND user_id = :user_id"
        );
        
        return $stmt->execute([
            ':title' => $task->getTitle(),
            ':description' => $task->getDescription(),
            ':status' => $task->getStatus(),
            ':due_date' => $task->getDueDate(),
            ':task_id' => $task->getTaskId(),
            ':user_id' => $task->getUserId()
        ]);
    }

    public function deleteTask($taskId, $userId) {
        $stmt = $this->conn->prepare(
            "DELETE FROM tasks 
            WHERE task_id = :task_id AND user_id = :user_id"
        );
        
        return $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId
        ]);
    }
}