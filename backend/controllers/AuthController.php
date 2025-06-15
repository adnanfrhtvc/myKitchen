<?php
/**
 * Authentication Controller
 * Handles user authentication operations
 */
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../factories/EntityFactory.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = EntityFactory::create('user');
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!$this->validateRegistrationData($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input data']);
            return;
        }
        
        // Check if user already exists
        if ($this->userModel->emailExists($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        if ($this->userModel->usernameExists($data['username'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Username already exists']);
            return;
        }
        
        // Create user
        if ($this->userModel->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'User created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user']);
        }
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            return;
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        echo json_encode([
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ]);
    }
    
    public function logout() {
        session_start();
        session_destroy();
        echo json_encode(['message' => 'Logout successful']);
    }
    
    public function checkAuth() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'email' => $_SESSION['email']
                ]
            ]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
    }
    
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['new_password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and new password required']);
            return;
        }
        
        // Check if user exists
        $user = $this->userModel->findByEmail($data['email']);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        // Update password
        $sql = "UPDATE users SET password_hash = :password_hash WHERE email = :email";
        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        $stmt->bindParam(':password_hash', password_hash($data['new_password'], PASSWORD_DEFAULT));
        $stmt->bindParam(':email', $data['email']);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Password reset successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to reset password']);
        }
    }

    public function updateProfile() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                username = :username WHERE id = :id";
        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Update session data
            $_SESSION['username'] = $data['username'];
            echo json_encode(['message' => 'Profile updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile']);
        }
    }

    public function deleteAccount() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = Database::getInstance()->getConnection()->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            session_destroy();
            echo json_encode(['message' => 'Account deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete account']);
        }
    }
    
    private function validateRegistrationData($data) {
        return isset($data['username']) && 
               isset($data['email']) && 
               isset($data['password']) && 
               isset($data['first_name']) && 
               isset($data['last_name']) &&
               filter_var($data['email'], FILTER_VALIDATE_EMAIL) &&
               strlen($data['password']) >= 6;
    }
}

// Handle the request
$controller = new AuthController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $controller->register();
        break;
    case 'login':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'check':
        $controller->checkAuth();
        break;
    case 'resetPassword':
        $controller->resetPassword();
        break;
    case 'updateProfile':
        $controller->updateProfile();
        break;
    case 'deleteAccount':
        $controller->deleteAccount();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
}
?>
