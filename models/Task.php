<?php

class Task {
    private $taskId;
    private $userId;
    private $title;
    private $description;
    private $status;
    private $dueDate;
    private $createdAt;

    public function __construct(
        $userId,
        $title,
        $description = '',
        $status = 'pending',
        $dueDate = null,
        $taskId = null,
        $createdAt = null
    ) {
        $this->taskId = $taskId;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->dueDate = $dueDate;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getTaskId() { return $this->taskId; }
    public function getUserId() { return $this->userId; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getStatus() { return $this->status; }
    public function getDueDate() { return $this->dueDate; }
    public function getCreatedAt() { return $this->createdAt; }

    // Setters
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
    public function setStatus($status) { $this->status = $status; }
    public function setDueDate($dueDate) { $this->dueDate = $dueDate; }
}