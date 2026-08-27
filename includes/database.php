<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;
    private $queries = [];
    private $queryTime = 0;
    
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            $this->conn->query("SET time_zone = '+07:00'");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function query($sql) {
        $start = microtime(true);
        $result = $this->conn->query($sql);
        $this->queryTime += microtime(true) - $start;
        $this->queries[] = $sql;
        return $result;
    }
    
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    public function affectedRows() {
        return $this->conn->affected_rows;
    }
    
    public function getError() {
        return $this->conn->error;
    }
    
    public function getErrorCode() {
        return $this->conn->errno;
    }
    
    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollback() {
        return $this->conn->rollback();
    }
    
    public function getQueryLog() {
        return $this->queries;
    }
    
    public function getQueryTime() {
        return round($this->queryTime, 4);
    }
    
    public function ping() {
        return $this->conn->ping();
    }
    
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}

// Helper function
function db() {
    return Database::getInstance()->getConnection();
}
?>
