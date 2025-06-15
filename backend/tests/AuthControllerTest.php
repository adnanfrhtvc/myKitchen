<?php
/**
 * Authentication Controller Test
 * Tests user authentication functionality including registration, login, and password reset
 */

require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../models/User.php';

class AuthControllerTest {
    private $db;
    private $testEmail = 'test_user_' . time() . '@example.com';
    private $testUsername = 'test_user_' . time();
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        echo "=== Authentication Controller Tests ===\n";
    }
    
    public function runAllTests() {
        $this->testUserRegistration();
        $this->testUserLogin();
        $this->testPasswordReset();
        $this->testDuplicateEmailRegistration();
        $this->testInvalidLogin();
        $this->cleanup();
        
        echo "\n=== All Authentication Tests Completed ===\n";
    }
    
    public function testUserRegistration() {
        echo "\n--- Test: User Registration ---\n";
        
        // Prepare test data
        $userData = [
            'username' => $this->testUsername,
            'email' => $this->testEmail,
            'first_name' => 'Test',
            'last_name' => 'User',
            'password' => 'testpassword123'
        ];
        
        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture output
        ob_start();
        
        // Mock the input
        $this->mockJsonInput($userData);
        
        // Create controller and test registration
        $controller = new AuthController();
        $controller->register();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // Assertions
        if ($response && isset($response['message']) && $response['message'] === 'User created successfully') {
            echo "✅ PASS: User registration successful\n";
            
            // Verify user exists in database
            $userModel = new User();
            $user = $userModel->findByEmail($this->testEmail);
            
            if ($user) {
                echo "✅ PASS: User found in database\n";
                echo "   - Username: {$user['username']}\n";
                echo "   - Email: {$user['email']}\n";
                echo "   - Password is hashed: " . (strlen($user['password_hash']) > 50 ? 'Yes' : 'No') . "\n";
            } else {
                echo "❌ FAIL: User not found in database\n";
            }
        } else {
            echo "❌ FAIL: User registration failed\n";
            echo "   Response: " . $output . "\n";
        }
    }
    
    public function testUserLogin() {
        echo "\n--- Test: User Login ---\n";
        
        // Prepare login data
        $loginData = [
            'email' => $this->testEmail,
            'password' => 'testpassword123'
        ];
        
        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Start session for login test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Capture output
        ob_start();
        
        // Mock the input
        $this->mockJsonInput($loginData);
        
        // Create controller and test login
        $controller = new AuthController();
        $controller->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // Assertions
        if ($response && isset($response['message']) && $response['message'] === 'Login successful') {
            echo "✅ PASS: User login successful\n";
            
            // Check session variables
            if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
                echo "✅ PASS: Session variables set correctly\n";
                echo "   - User ID: {$_SESSION['user_id']}\n";
                echo "   - Email: {$_SESSION['email']}\n";
            } else {
                echo "❌ FAIL: Session variables not set\n";
            }
            
            // Check user data in response
            if (isset($response['user']) && $response['user']['email'] === $this->testEmail) {
                echo "✅ PASS: User data returned correctly\n";
            } else {
                echo "❌ FAIL: User data not returned correctly\n";
            }
        } else {
            echo "❌ FAIL: User login failed\n";
            echo "   Response: " . $output . "\n";
        }
    }
    
    public function testPasswordReset() {
        echo "\n--- Test: Password Reset ---\n";
        
        $newPassword = 'newpassword456';
        $resetData = [
            'email' => $this->testEmail,
            'new_password' => $newPassword
        ];
        
        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture output
        ob_start();
        
        // Mock the input
        $this->mockJsonInput($resetData);
        
        // Create controller and test password reset
        $controller = new AuthController();
        $controller->resetPassword();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // Assertions
        if ($response && isset($response['message']) && $response['message'] === 'Password reset successfully') {
            echo "✅ PASS: Password reset successful\n";
            
            // Test login with new password
            $loginData = [
                'email' => $this->testEmail,
                'password' => $newPassword
            ];
            
            ob_start();
            $this->mockJsonInput($loginData);
            $controller->login();
            $loginOutput = ob_get_clean();
            $loginResponse = json_decode($loginOutput, true);
            
            if ($loginResponse && isset($loginResponse['message']) && $loginResponse['message'] === 'Login successful') {
                echo "✅ PASS: Login with new password successful\n";
            } else {
                echo "❌ FAIL: Login with new password failed\n";
            }
        } else {
            echo "❌ FAIL: Password reset failed\n";
            echo "   Response: " . $output . "\n";
        }
    }
    
    public function testDuplicateEmailRegistration() {
        echo "\n--- Test: Duplicate Email Registration ---\n";
        
        // Try to register with same email
        $userData = [
            'username' => 'another_user',
            'email' => $this->testEmail, // Same email as before
            'first_name' => 'Another',
            'last_name' => 'User',
            'password' => 'anotherpassword'
        ];
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->mockJsonInput($userData);
        
        $controller = new AuthController();
        $controller->register();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if ($response && isset($response['error']) && $response['error'] === 'Email already exists') {
            echo "✅ PASS: Duplicate email registration properly rejected\n";
        } else {
            echo "❌ FAIL: Duplicate email registration not handled correctly\n";
            echo "   Response: " . $output . "\n";
        }
    }
    
    public function testInvalidLogin() {
        echo "\n--- Test: Invalid Login ---\n";
        
        $invalidLoginData = [
            'email' => $this->testEmail,
            'password' => 'wrongpassword'
        ];
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->mockJsonInput($invalidLoginData);
        
        $controller = new AuthController();
        $controller->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if ($response && isset($response['error']) && $response['error'] === 'Invalid credentials') {
            echo "✅ PASS: Invalid login properly rejected\n";
        } else {
            echo "❌ FAIL: Invalid login not handled correctly\n";
            echo "   Response: " . $output . "\n";
        }
    }
    
    private function mockJsonInput($data) {
        // Create a temporary file with JSON data
        $tempFile = tempnam(sys_get_temp_dir(), 'test_input');
        file_put_contents($tempFile, json_encode($data));
        
        // Override php://input for testing
        $GLOBALS['test_input'] = json_encode($data);
    }
    
    private function cleanup() {
        echo "\n--- Cleanup: Removing Test Data ---\n";
        
        // Remove test user
        $sql = "DELETE FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $this->testEmail);
        
        if ($stmt->execute()) {
            echo "✅ Test user cleaned up successfully\n";
        } else {
            echo "❌ Failed to cleanup test user\n";
        }
    }
}

// Override file_get_contents for testing
if (!function_exists('original_file_get_contents')) {
    function original_file_get_contents($filename) {
        return file_get_contents($filename);
    }
}

// Mock file_get_contents for php://input during tests
function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null) {
    if ($filename === 'php://input' && isset($GLOBALS['test_input'])) {
        return $GLOBALS['test_input'];
    }
    return original_file_get_contents($filename);
}

// Run the tests
$authTest = new AuthControllerTest();
$authTest->runAllTests();
?>
