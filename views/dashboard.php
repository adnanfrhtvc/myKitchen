<?php
require_once __DIR__ . '/../controllers/RecipeController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/TaskController.php';

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle recipe creation
$message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_recipe'])) {
    $controller = new RecipeController();
    
    // Manually call create method with parameters
    $recipe = new Recipe(
        $_SESSION['user_id'],
        $_POST['title'] ?? '',
        $_POST['ingredients'] ?? '',
        $_POST['steps'] ?? ''
    );
    
    $success = $controller->create($recipe);
    $message = $success ? 'Recipe created successfully!' : 'Failed to create recipe';
}

// Get existing recipes
$controller = new RecipeController();
$recipes = $controller->getRecipes();

// Handle task creation
$taskMessage = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_task'])) {
    $taskController = new TaskController();
    
    $task = new Task(
        $_SESSION['user_id'],
        $_POST['title'] ?? '',
        $_POST['description'] ?? '',
        $_POST['status'] ?? 'pending',
        $_POST['due_date'] ?? null
    );
    
    $success = $taskController->createTask($task);
    $taskMessage = $success ? 'Task created successfully!' : 'Failed to create task';
}

// Get existing tasks
$taskController = new TaskController();
$tasks = $taskController->getTasks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | myKitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .recipe-card { transition: transform 0.2s; }
        .recipe-card:hover { transform: translateY(-3px); }
        textarea { min-height: 100px; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Add this in the HTML body after the logout button but before recipes -->
<div class="row mb-5">
    <!-- Task Creation Card -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="card-title mb-4">Create New Task</h4>
                
                <?php if (!empty($taskMessage)): ?>
                    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
                        <?= htmlspecialchars($taskMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="datetime-local" name="due_date" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="create_task" class="btn btn-primary">Create Task</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Task List -->
    <div class="col-md-6">
        <h3 class="mb-4">Your Tasks</h3>
        <div class="task-list">
            <?php foreach ($tasks as $task): ?>
                <div class="card mb-3 shadow-sm <?= 
                    $task->getStatus() === 'completed' ? 'border-success' : 
                    ($task->getStatus() === 'in_progress' ? 'border-warning' : '') 
                ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title"><?= htmlspecialchars($task->getTitle()) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($task->getDescription()) ?></p>
                                <small class="text-muted">Due: <?= $task->getDueDate() ? date('M j, Y H:i', strtotime($task->getDueDate())) : 'No due date' ?></small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        type="button" 
                                        data-bs-toggle="dropdown">
                                    <?= ucfirst(str_replace('_', ' ', $task->getStatus())) ?>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Mark Complete</a></li>
                                    <li><a class="dropdown-item" href="#">Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    <!-- Create Recipe Form -->
    <div class="card mb-5 shadow">
        <div class="card-body">
            <h4 class="card-title mb-4">Create New Recipe</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ingredients</label>
                    <textarea name="ingredients" class="form-control" placeholder="Enter ingredients (one per line)"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Steps</label>
                    <textarea name="steps" class="form-control" placeholder="Enter cooking steps"></textarea>
                </div>
                
                <button type="submit" name="create_recipe" class="btn btn-primary">Save Recipe</button>
            </form>
        </div>
    </div>

    <!-- Recipes List -->
    <h3 class="mb-4">Your Recipes</h3>
    <div class="row g-4">
        <?php foreach ($recipes as $recipe): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card recipe-card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($recipe->getTitle()) ?></h5>
                    <div class="card-text">
                        <h6 class="text-muted mt-3">Ingredients</h6>
                        <pre class="bg-light p-3 rounded"><?= htmlspecialchars($recipe->getIngredients()) ?></pre>
                        
                        <h6 class="text-muted">Steps</h6>
                        <pre class="bg-light p-3 rounded"><?= htmlspecialchars($recipe->getSteps()) ?></pre>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <small class="text-muted">Created: <?= $recipe->getCreatedAt() ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>