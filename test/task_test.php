<?php
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../config/Database.php';

// Start session and mock user
session_start();
$_SESSION['user_id'] = 1; // Use existing user ID

// Create test task
$task = new Task(
    $_SESSION['user_id'],
    'Test Task',
    'This is a test description',
    'pending',
    '2024-06-01 12:00:00'
);

$controller = new TaskController();

echo "🔹 Testing Task Creation...\n";
if ($controller->createTask($task)) {
    echo "✅ Task created successfully!\n";
    
    // Test retrieval
    echo "\n🔹 Testing Task Retrieval...\n";
    $tasks = $controller->getTasks();
    if (count($tasks) > 0) {
        echo "✅ Found " . count($tasks) . " tasks\n";
        $lastTask = end($tasks);
        echo "Last task title: " . $lastTask->getTitle() . "\n";
    } else {
        echo "❌ No tasks found\n";
    }
} else {
    echo "❌ Task creation failed\n";
}