<?php
/**
 * Task Controller
 * Handles task-related operations
 */
require_once '../config/database.php';
require_once '../models/Task.php';
require_once '../factories/EntityFactory.php';

class TaskController {
    private $taskModel;
    
    public function __construct() {
        $this->taskModel = EntityFactory::create('task');
        session_start();
    }
    
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
    }
    
    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $data['user_id'] = $_SESSION['user_id'];
        
        if ($this->taskModel->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Task created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create task']);
        }
    }
    
    public function getAll() {
        $this->requireAuth();
        
        $tasks = $this->taskModel->findByUserId($_SESSION['user_id']);
        echo json_encode($tasks);
    }
    
    public function get($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->findById($id);
        
        if (!$task) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            return;
        }
        
        echo json_encode($task);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($this->taskModel->update($id, $data)) {
            echo json_encode(['message' => 'Task updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update task']);
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if ($this->taskModel->delete($id)) {
            echo json_encode(['message' => 'Task deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete task']);
        }
    }
}

// Handle the request
$controller = new TaskController();
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'getAll':
        $controller->getAll();
        break;
    case 'get':
        if ($id) $controller->get($id);
        break;
    case 'update':
        if ($id) $controller->update($id);
        break;
    case 'delete':
        if ($id) $controller->delete($id);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?>
