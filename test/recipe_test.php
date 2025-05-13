<?php
require_once __DIR__ . '/../controllers/RecipeController.php';
require_once __DIR__ . '/../config/Database.php';

// Start session and mock logged-in user
session_start();
$_SESSION['user_id'] = 2; // Use existing user ID from your database
$_SESSION['username'] = 'testuser';

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'title' => 'Spaghetti Carbonara',
    'ingredients' => "Spaghetti\nEggs\nPecorino Cheese\nGuanciale",
    'steps' => "1. Cook pasta\n2. Mix eggs and cheese\n3. Combine with cooked guanciale"
];

$controller = new RecipeController();

echo "🔹 Testing Recipe Creation...\n";

// Capture output to prevent header redirect from terminating script
ob_start();
$controller->create();
$output = ob_get_clean();

// Check database directly
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM recipes WHERE title = ? AND user_id = ?");
$stmt->execute([$_POST['title'], $_SESSION['user_id']]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($recipe) {
    echo "✅ Recipe created successfully!\n";
    echo "Recipe ID: " . $recipe['recipe_id'] . "\n";
} else {
    echo "❌ Recipe creation failed\n";
    echo "Debug output: " . $output . "\n";
}