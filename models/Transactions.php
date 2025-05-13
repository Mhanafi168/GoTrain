<?php
class Transaction
{
    private $conn;
    private $table_name = "transactions";

    public $id;
    public $user_id;
    public $amount;
    public $transaction_type;
    public $description;
    public $created_at;

    private $lastError = '';
    private $lastInsertId;

    public function __construct($db)
    {
        if ($db instanceof PDO) {
            $this->conn = $db;
        } elseif ($db instanceof DBController) {
            $this->conn = $db->getConnection();
        } else {
            throw new InvalidArgumentException("Constructor expects either PDO or DBController instance");
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    public function addTransaction()
    {
        $this->lastError = '';

        if (empty($this->user_id) || !isset($this->amount) || empty($this->transaction_type)) {
            $this->lastError = "Missing required fields for transaction: user_id, amount, or transaction_type is empty.";
            error_log("Transaction::addTransaction Validation Error: " . $this->lastError . " | UID: {$this->user_id}, Amt: {$this->amount}, Type: {$this->transaction_type}");
            return false;
        }

        $sql = "INSERT INTO " . $this->table_name . " 
                  (user_id, amount, transaction_type, description) 
                  VALUES (:user_id, :amount, :transaction_type_param, :description_param)";
        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':transaction_type_param', $this->transaction_type);
            $stmt->bindParam(':description_param', $this->description);

            if ($stmt->execute()) {
                $this->lastInsertId = $this->conn->lastInsertId();
                $this->id = $this->lastInsertId;
                return $this->lastInsertId;
            }

            $this->lastError = "SQL Error in addTransaction: " . implode(", ", $stmt->errorInfo());
            error_log("Transaction::addTransaction SQL Error: " . $this->lastError . " | Query: " . $sql);
            return false;
        } catch (PDOException $e) {
            $this->lastError = "PDOException in addTransaction: " . $e->getMessage();
            error_log("Transaction::addTransaction PDOException: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }
    public function getRecentTransactions($user_id, $limit = 10)
    {
        try {
            $this->validateUserId($user_id);
            $this->validateLimit($limit);

            $query = "SELECT 
                    transaction_id, 
                    user_id, 
                    amount, 
                    transaction_type, 
                    description, 
                    transaction_date as created_at,
                    booking_id
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY transaction_date DESC 
                  LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = "Database error while fetching transactions: " . $e->getMessage();
            error_log("Transaction::getRecentTransactions PDO error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Transaction::getRecentTransactions error: " . $e->getMessage());
            return false;
        }
    }

    private function validateUserId($user_id)
    {
        if (!is_numeric($user_id)) {
            throw new InvalidArgumentException("Invalid user ID");
        }
    }

    private function validateLimit($limit)
    {
        if (!is_numeric($limit)) {
            throw new InvalidArgumentException("Invalid limit value");
        }
    }

    private function validateTransactionData()
    {
        if (empty($this->user_id)) {
            throw new InvalidArgumentException("User ID cannot be empty");
        }

        if (!is_numeric($this->amount)) {
            throw new InvalidArgumentException("Invalid amount");
        }

        if (empty($this->type)) {
            throw new InvalidArgumentException("Transaction type cannot be empty");
        }

        if (empty($this->description)) {
            $this->description = "No description provided";
        }
    }
}
