<?php
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../config/Database.php';

class RecipeRepository {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function createRecipe(Recipe $recipe) {
        $stmt = $this->conn->prepare(
            "INSERT INTO recipes (user_id, title, ingredients, steps) 
            VALUES (:user_id, :title, :ingredients, :steps)"
        );
        return $stmt->execute([
            ':user_id' => $recipe->getUserId(),
            ':title' => $recipe->getTitle(),
            ':ingredients' => $recipe->getIngredients(),
            ':steps' => $recipe->getSteps()
        ]);
    }

    public function getRecipeById($recipeId, $userId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM recipes 
            WHERE recipe_id = :recipe_id AND user_id = :user_id"
        );
        $stmt->execute([
            ':recipe_id' => $recipeId,
            ':user_id' => $userId
        ]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Recipe(
            $data['user_id'],
            $data['title'],
            $data['ingredients'],
            $data['steps'],
            $data['recipe_id'],
            $data['created_at']
        ) : null;
    }

    public function getUserRecipes($userId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM recipes WHERE user_id = :user_id ORDER BY created_at DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        
        $recipes = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $recipes[] = new Recipe(
                $data['user_id'],
                $data['title'],
                $data['ingredients'],
                $data['steps'],
                $data['recipe_id'],
                $data['created_at']
            );
        }
        return $recipes;
    }

    public function updateRecipe(Recipe $recipe) {
        $stmt = $this->conn->prepare(
            "UPDATE recipes SET 
            title = :title,
            ingredients = :ingredients,
            steps = :steps
            WHERE recipe_id = :recipe_id AND user_id = :user_id"
        );
        return $stmt->execute([
            ':title' => $recipe->getTitle(),
            ':ingredients' => $recipe->getIngredients(),
            ':steps' => $recipe->getSteps(),
            ':recipe_id' => $recipe->getRecipeId(),
            ':user_id' => $recipe->getUserId()
        ]);
    }

    public function deleteRecipe($recipeId, $userId) {
        $stmt = $this->conn->prepare(
            "DELETE FROM recipes 
            WHERE recipe_id = :recipe_id AND user_id = :user_id"
        );
        return $stmt->execute([
            ':recipe_id' => $recipeId,
            ':user_id' => $userId
        ]);
    }
}