<?php
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function register($username, $email, $password) {
        // Basic input validation (you can improve later)
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        // Check if user exists
        if ($this->userRepository->getUserByEmail($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Create User object
        $user = new User($username, $email, $passwordHash);

        // Insert into DB
        $result = $this->userRepository->createUser($user);

        if ($result) {
            return ['success' => true, 'message' => 'Registration successful.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed.'];
        }
    }

    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        $user = $this->userRepository->getUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'No account found with this email.'];
        }

        if (password_verify($password, $user->getPasswordHash())) {
            session_start();
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            return ['success' => true, 'message' => 'Login successful.'];
        } else {
            return ['success' => false, 'message' => 'Incorrect password.'];
        }
    }

    public function logout() {
        session_start();
        session_destroy();
    }
}
