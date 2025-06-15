<?php
/**
 * Recipe Controller
 * Handles recipe-related operations
 */
require_once '../config/database.php';
require_once '../models/Recipe.php';
require_once '../factories/EntityFactory.php';

class RecipeController {
    private $recipeModel;
    
    public function __construct() {
        $this->recipeModel = EntityFactory::create('recipe');
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
        
        if ($this->recipeModel->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Recipe created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create recipe']);
        }
    }
    
    public function getAll() {
        $this->requireAuth();
        
        $recipes = $this->recipeModel->findByUserId($_SESSION['user_id']);
        echo json_encode($recipes);
    }
    
    public function get($id) {
        $this->requireAuth();
        
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
            return;
        }
        
        echo json_encode($recipe);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($this->recipeModel->update($id, $data)) {
            echo json_encode(['message' => 'Recipe updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update recipe']);
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if ($this->recipeModel->delete($id)) {
            echo json_encode(['message' => 'Recipe deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete recipe']);
        }
    }
}

// Handle the request
$controller = new RecipeController();
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
