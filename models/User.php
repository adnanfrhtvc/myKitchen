<?php

class User {
    private $id;
    private $username;
    private $email;
    private $passwordHash;
    private $createdAt;

    public function __construct($username, $email, $passwordHash, $id = null, $createdAt = null) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPasswordHash() {
        return $this->passwordHash;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    // Setters 
    public function setPasswordHash($passwordHash) {
        $this->passwordHash = $passwordHash;
    }
}
