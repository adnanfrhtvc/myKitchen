<?php
require_once __DIR__ . '/../repositories/TaskRepository.php';

class TaskController {
    private $taskRepo;

    public function __construct() {
        $this->taskRepo = new TaskRepository();
    }

    public function createTask(Task $task) {
        return $this->taskRepo->createTask($task);
    }

    public function getTasks() {
        return $this->taskRepo->getUserTasks($_SESSION['user_id']);
    }

    public function updateTask(Task $task) {
        return $this->taskRepo->updateTask($task);
    }

    public function deleteTask($taskId) {
        return $this->taskRepo->deleteTask($taskId, $_SESSION['user_id']);
    }

    public function markAsComplete($taskId) {
        $task = $this->taskRepo->getTaskById($taskId, $_SESSION['user_id']);
        if ($task) {
            $task->setStatus('completed');
            return $this->taskRepo->updateTask($task);
        }
        return false;
    }
}