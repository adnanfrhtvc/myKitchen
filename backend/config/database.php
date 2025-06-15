<?php
/**
 * Database configuration and connection
 * Implements Singleton pattern for database connection
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private $host = 'localhost';
    private $port = 3307; 
    private $username = 'root';
    private $password = '';
    private $database = 'mykitchen';
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->database}",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
?>
