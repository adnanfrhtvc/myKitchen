<?php
require_once '../controllers/AuthController.php';

$auth = new AuthController();

// 🔹 Test Registration
echo "🔹 Testing Registration...\n";
$response = $auth->register("testuser", "testuser@example.com", "mypassword123");
echo $response['message'] . "\n";

// 🔹 Test Login
echo "\n🔹 Testing Login...\n";
$response = $auth->login("testuser@example.com", "mypassword123");
echo $response['message'] . "\n";
