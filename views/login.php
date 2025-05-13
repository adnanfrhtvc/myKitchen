<?php
require_once '../controllers/AuthController.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $auth = new AuthController();
    $response = $auth->login(
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );
    $message = $response['message'];

    if ($response['success']) {
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | myKitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <h4 class="card-title text-center mb-4">Login</h4>

            <?php if (!empty($message)): ?>
                <div class="alert <?= strpos($message, 'successful') !== false ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <small>Don't have an account? <a href="register.php">Register here</a></small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
