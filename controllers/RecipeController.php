<?php
require_once __DIR__ . '/../repositories/RecipeRepository.php';

class RecipeController {
    private $recipeRepo;

    public function __construct() {
        $this->recipeRepo = new RecipeRepository();
    }

    // Simple create method
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Fixed syntax error here: added closing parenthesis
            if (empty($_POST['title'])) {
                die('Title is required');
            }

            // Create recipe
            $recipe = new Recipe(
                $_SESSION['user_id'],  // Make sure you have session_start()
                $_POST['title'],
                $_POST['ingredients'] ?? '',
                $_POST['steps'] ?? ''
            );

            if ($this->recipeRepo->createRecipe($recipe)) {
                header('Location: /myKitchen/views/dashboard.php');
                exit();
            } else {
                die('Error saving recipe');
            }
        }
    }

    // Basic get all recipes for user
    public function getRecipes() {
        return $this->recipeRepo->getUserRecipes($_SESSION['user_id']);
    }
}