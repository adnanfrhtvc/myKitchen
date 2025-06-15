<?php
/**
 * Grocery List Controller
 * Handles grocery list-related operations
 */
require_once '../config/database.php';
require_once '../models/GroceryList.php';
require_once '../factories/EntityFactory.php';

class GroceryController {
    private $groceryModel;
    
    public function __construct() {
        $this->groceryModel = EntityFactory::create('grocerylist');
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
        $data['is_completed'] = false;
        
        if ($this->groceryModel->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Grocery item added successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add grocery item']);
        }
    }
    
    public function getAll() {
        $this->requireAuth();
        
        $items = $this->groceryModel->findByUserId($_SESSION['user_id']);
        echo json_encode($items);
    }
    
    public function get($id) {
        $this->requireAuth();
        
        $item = $this->groceryModel->findById($id);
        
        if (!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Grocery item not found']);
            return;
        }
        
        echo json_encode($item);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($this->groceryModel->update($id, $data)) {
            echo json_encode(['message' => 'Grocery item updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update grocery item']);
        }
    }
    
    public function toggle($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if ($this->groceryModel->toggleCompleted($id)) {
            echo json_encode(['message' => 'Grocery item status updated']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update grocery item status']);
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if ($this->groceryModel->delete($id)) {
            echo json_encode(['message' => 'Grocery item deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete grocery item']);
        }
    }
    
    public function clearCompleted() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if ($this->groceryModel->clearCompleted($_SESSION['user_id'])) {
            echo json_encode(['message' => 'Completed items cleared successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to clear completed items']);
        }
    }
}

// Handle the request
$controller = new GroceryController();
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
    case 'toggle':
        if ($id) $controller->toggle($id);
        break;
    case 'delete':
        if ($id) $controller->delete($id);
        break;
    case 'clearCompleted':
        $controller->clearCompleted();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?>
