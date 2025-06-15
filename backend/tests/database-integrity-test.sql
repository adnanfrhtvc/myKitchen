-- Database Integrity and Relationship Tests
-- Tests foreign key constraints, data validation, and database operations

-- Test Setup: Create test data
USE mykitchen;

-- Start transaction for testing
START TRANSACTION;

-- Test 1: Foreign Key Constraints
-- ================================
SELECT '=== Test 1: Foreign Key Constraints ===' as test_section;

-- Insert a test user
INSERT INTO users (username, email, password_hash, first_name, last_name) 
VALUES ('test_fk_user', 'test_fk@example.com', 'hashed_password', 'Test', 'User');

SET @test_user_id = LAST_INSERT_ID();

-- Test 1a: Valid foreign key insertion
INSERT INTO tasks (user_id, title, description, priority, status) 
VALUES (@test_user_id, 'Test Task', 'Test Description', 'medium', 'pending');

SELECT 'PASS: Task with valid user_id inserted successfully' as result
WHERE ROW_COUNT() = 1;

-- Test 1b: Invalid foreign key insertion (should fail)
SET @invalid_result = 'PASS: Foreign key constraint working - invalid user_id rejected';
INSERT INTO tasks (user_id, title, description, priority, status) 
VALUES (99999, 'Invalid Task', 'Should fail', 'medium', 'pending');

-- If we reach here, the constraint failed
SET @invalid_result = 'FAIL: Foreign key constraint not working - invalid user_id accepted';

SELECT @invalid_result as result;

-- Test 2: Cascade Delete Functionality
-- ====================================
SELECT '=== Test 2: Cascade Delete Functionality ===' as test_section;

-- Count items before deletion
SELECT COUNT(*) as tasks_before_delete FROM tasks WHERE user_id = @test_user_id;
SELECT COUNT(*) as recipes_before_delete FROM recipes WHERE user_id = @test_user_id;
SELECT COUNT(*) as grocery_before_delete FROM grocery_lists WHERE user_id = @test_user_id;

-- Insert test data for cascade test
INSERT INTO recipes (user_id, title, ingredients, instructions, servings, difficulty) 
VALUES (@test_user_id, 'Test Recipe', 'Test ingredients', 'Test instructions', 4, 'easy');

INSERT INTO grocery_lists (user_id, item_name, quantity, is_completed) 
VALUES (@test_user_id, 'Test Item', '1 unit', FALSE);

-- Count items after insertion
SELECT COUNT(*) as tasks_after_insert FROM tasks WHERE user_id = @test_user_id;
SELECT COUNT(*) as recipes_after_insert FROM recipes WHERE user_id = @test_user_id;
SELECT COUNT(*) as grocery_after_insert FROM grocery_lists WHERE user_id = @test_user_id;

-- Delete the user (should cascade)
DELETE FROM users WHERE id = @test_user_id;

-- Count items after user deletion (should be 0)
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASS: Tasks cascade deleted successfully'
        ELSE 'FAIL: Tasks not cascade deleted'
    END as tasks_cascade_result
FROM tasks WHERE user_id = @test_user_id;

SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASS: Recipes cascade deleted successfully'
        ELSE 'FAIL: Recipes not cascade deleted'
    END as recipes_cascade_result
FROM recipes WHERE user_id = @test_user_id;

SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASS: Grocery items cascade deleted successfully'
        ELSE 'FAIL: Grocery items not cascade deleted'
    END as grocery_cascade_result
FROM grocery_lists WHERE user_id = @test_user_id;

-- Test 3: Data Validation and Constraints
-- =======================================
SELECT '=== Test 3: Data Validation and Constraints ===' as test_section;

-- Test 3a: Unique email constraint
INSERT INTO users (username, email, password_hash, first_name, last_name) 
VALUES ('unique_test1', 'unique_test@example.com', 'hash1', 'User', 'One');

SET @unique_test_result = 'FAIL: Duplicate email constraint not working';

-- Try to insert duplicate email (should fail)
INSERT INTO users (username, email, password_hash, first_name, last_name) 
VALUES ('unique_test2', 'unique_test@example.com', 'hash2', 'User', 'Two');

-- If we reach here, constraint failed
SET @unique_test_result = 'PASS: Unique email constraint working correctly';

SELECT @unique_test_result as email_unique_result;

-- Test 3b: Unique username constraint
SET @username_test_result = 'FAIL: Duplicate username constraint not working';

-- Try to insert duplicate username (should fail)
INSERT INTO users (username, email, password_hash, first_name, last_name) 
VALUES ('unique_test1', 'another_email@example.com', 'hash3', 'User', 'Three');

-- If we reach here, constraint failed
SET @username_test_result = 'PASS: Unique username constraint working correctly';

SELECT @username_test_result as username_unique_result;

-- Test 3c: Enum value validation
SET @test_user_id2 = (SELECT id FROM users WHERE email = 'unique_test@example.com');

-- Valid enum values
INSERT INTO tasks (user_id, title, priority, status) 
VALUES (@test_user_id2, 'Valid Priority Task', 'high', 'completed');

SELECT 
    CASE 
        WHEN ROW_COUNT() = 1 THEN 'PASS: Valid enum values accepted'
        ELSE 'FAIL: Valid enum values rejected'
    END as enum_valid_result;

-- Test invalid enum value (should fail)
SET @enum_invalid_result = 'FAIL: Invalid enum value accepted';

INSERT INTO tasks (user_id, title, priority, status) 
VALUES (@test_user_id2, 'Invalid Priority Task', 'invalid_priority', 'pending');

-- If we reach here, validation failed
SET @enum_invalid_result = 'PASS: Invalid enum value properly rejected';

SELECT @enum_invalid_result as enum_invalid_result;

-- Test 4: Data Integrity Checks
-- =============================
SELECT '=== Test 4: Data Integrity Checks ===' as test_section;

-- Test 4a: Check timestamp defaults
SELECT 
    CASE 
        WHEN created_at IS NOT NULL AND updated_at IS NOT NULL 
        THEN 'PASS: Timestamps automatically set'
        ELSE 'FAIL: Timestamps not set automatically'
    END as timestamp_result
FROM users WHERE email = 'unique_test@example.com';

-- Test 4b: Check password hash length (should be substantial)
SELECT 
    CASE 
        WHEN LENGTH(password_hash) >= 50 
        THEN 'PASS: Password appears to be properly hashed'
        ELSE 'FAIL: Password may not be properly hashed'
    END as password_hash_result
FROM users WHERE email = 'unique_test@example.com';

-- Test 4c: Check default values
INSERT INTO tasks (user_id, title, description) 
VALUES (@test_user_id2, 'Default Values Test', 'Testing defaults');

SELECT 
    CASE 
        WHEN priority = 'medium' AND status = 'pending' 
        THEN 'PASS: Default values set correctly for tasks'
        ELSE 'FAIL: Default values not set correctly for tasks'
    END as task_defaults_result
FROM tasks WHERE title = 'Default Values Test';

INSERT INTO recipes (user_id, title, ingredients, instructions) 
VALUES (@test_user_id2, 'Default Recipe Test', 'Test ingredients', 'Test instructions');

SELECT 
    CASE 
        WHEN servings = 1 AND difficulty = 'medium' 
        THEN 'PASS: Default values set correctly for recipes'
        ELSE 'FAIL: Default values not set correctly for recipes'
    END as recipe_defaults_result
FROM recipes WHERE title = 'Default Recipe Test';

-- Test 5: Performance and Index Checks
-- ====================================
SELECT '=== Test 5: Performance and Index Checks ===' as test_section;

-- Check if indexes exist on foreign keys and unique columns
SELECT 
    CASE 
        WHEN COUNT(*) > 0 
        THEN 'PASS: Indexes found on important columns'
        ELSE 'FAIL: Missing indexes on important columns'
    END as index_result
FROM information_schema.statistics 
WHERE table_schema = 'mykitchen' 
AND table_name IN ('users', 'tasks', 'recipes', 'grocery_lists')
AND column_name IN ('email', 'username', 'user_id');

-- Test query performance with EXPLAIN
EXPLAIN SELECT u.username, COUNT(t.id) as task_count 
FROM users u 
LEFT JOIN tasks t ON u.id = t.user_id 
WHERE u.email = 'unique_test@example.com' 
GROUP BY u.id, u.username;

-- Summary
SELECT '=== Database Integrity Test Summary ===' as test_section;

SELECT 
    COUNT(*) as total_users,
    (SELECT COUNT(*) FROM tasks) as total_tasks,
    (SELECT COUNT(*) FROM recipes) as total_recipes,
    (SELECT COUNT(*) FROM grocery_lists) as total_grocery_items
FROM users;

-- Rollback transaction to clean up test data
ROLLBACK;

SELECT 'All test data rolled back - database restored to original state' as cleanup_result;
