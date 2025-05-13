<?php
require_once '../config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Database connection successful!";
} else {
    echo "❌ Failed to connect to the database.";
}
