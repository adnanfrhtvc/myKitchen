<?php

class Recipe {
    private $recipeId;
    private $userId;
    private $title;
    private $ingredients;
    private $steps;
    private $createdAt;

    public function __construct(
        $userId,
        $title,
        $ingredients,
        $steps,
        $recipeId = null,
        $createdAt = null
    ) {
        $this->recipeId = $recipeId;
        $this->userId = $userId;
        $this->title = $title;
        $this->ingredients = $ingredients;
        $this->steps = $steps;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getRecipeId() {
        return $this->recipeId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getIngredients() {
        return $this->ingredients;
    }

    public function getSteps() {
        return $this->steps;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    // Setters
    public function setTitle($title) {
        $this->title = $title;
    }

    public function setIngredients($ingredients) {
        $this->ingredients = $ingredients;
    }

    public function setSteps($steps) {
        $this->steps = $steps;
    }
}