<?php
class DBController {
    private string $dbHost = 'localhost';
    private string $dbUser = 'root';
    private string $dbPassword = '';
    private string $dbName = 'ticket_system';
    
    private ?mysqli $connection = null;
    private ?PDO $pdoConnection = null;
    private string $lastError = '';
    
    private static ?DBController $instance = null;

    private function __construct(string $dbName = 'ticket_system') {
        $this->dbName = $dbName;
    }
    
    public static function getInstance(string $dbName = 'ticket_system'): DBController {
        if (self::$instance === null) {
            self::$instance = new DBController($dbName);
        }
        return self::$instance;
    }
    
    public function openConnection(): bool {
        $this->connection = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
        if ($this->connection->connect_error) {
            $this->lastError = "Connection failed: " . $this->connection->connect_error;
            return false;
        }
        return true;
    }
    
    public function closeConnection(): void {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
        if ($this->pdoConnection) {
            $this->pdoConnection = null;
        }
    }
    
    public function select(string $qry, array $params = []): array|false {
        if (!$this->connection && !$this->openConnection()) {
            $this->lastError = "No database connection";
            return false;
        }
        
        if (!empty($params)) {
            $stmt = $this->connection->prepare($qry);
            if (!$stmt) {
                $this->lastError = "Prepare failed: " . $this->connection->error;
                return false;
            }
            
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        $result = $this->connection->query($qry);
        if (!$result) {
            $this->lastError = "Query failed: " . $this->connection->error;
            return false;
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function insert(string $qry, array $params = []): int|false {
        if (!$this->connection && !$this->openConnection()) {
            $this->lastError = "No database connection";
            return false;
        }
        
        if (!empty($params)) {
            $stmt = $this->connection->prepare($qry);
            if (!$stmt) {
                $this->lastError = "Prepare failed: " . $this->connection->error;
                return false;
            }
            
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            
            $success = $stmt->execute();
            $insertId = $stmt->insert_id;
            $stmt->close();
            
            return $success ? $insertId : false;
        }
        
        $result = $this->connection->query($qry);
        if (!$result) {
            $this->lastError = "Query failed: " . $this->connection->error;
            return false;
        }
        return $this->connection->insert_id;
    }
    
    public function execute(string $qry, array $params = []): bool {
        if (!$this->connection && !$this->openConnection()) {
            $this->lastError = "No database connection";
            return false;
        }
        
        if (!empty($params)) {
            $stmt = $this->connection->prepare($qry);
            if (!$stmt) {
                $this->lastError = "Prepare failed: " . $this->connection->error;
                return false;
            }
            
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            
            $success = $stmt->execute();
            $stmt->close();
            
            return $success;
        }
        
        $result = $this->connection->query($qry);
        if (!$result) {
            $this->lastError = "Query failed: " . $this->connection->error;
            return false;
        }
        return true;
    }
    
    public function getConnection(): PDO|false {
        if (!$this->pdoConnection) {
            try {
                $this->pdoConnection = new PDO(
                    "mysql:host=" . $this->dbHost . ";dbname=" . $this->dbName,
                    $this->dbUser,
                    $this->dbPassword
                );
                $this->pdoConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $this->lastError = "PDO Connection failed: " . $e->getMessage();
                return false;
            }
        }
        return $this->pdoConnection;
    }
    
    public function getLastError(): string {
        return $this->lastError;
    }

    private function __clone() {}
}
?>