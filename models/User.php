<?php
class User
{
    private $conn;
    private $table_name = "users";
    private $lastError = '';

    public $id;
    public $name;
    public $email;
    public $balance;
    public $source_station;
    public $last_recharge_date;

    public function __construct($db)
    {
        if ($db instanceof DBController) {
            $this->conn = $db->getConnection();
            if (!$this->conn instanceof PDO) {
                error_log("User Model: DBController did not provide a valid PDO connection.");
                throw new InvalidArgumentException("DBController did not provide a valid PDO connection to User model.");
            }
        } elseif ($db instanceof PDO) {
            $this->conn = $db;
        } else {
            error_log("User Model: Constructor received unexpected DB connection type: " . gettype($db));
            throw new InvalidArgumentException("User model requires a PDO connection or a DBController that provides one.");
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function update($data)
    {
        $this->lastError = '';
        if (isset($data['transaction']) && isset($data['transaction']['amount'])) {
            if ($this->id === null) {
                $this->lastError = "User instance not loaded (ID is null) for transaction update.";
                error_log($this->lastError);
                return false;
            }

            $amount_change = (float)$data['transaction']['amount'];
            $query = "UPDATE " . $this->table_name . " 
                     SET balance = balance + :amount_to_change,
                         last_recharge_date = IF(:is_recharge > 0, NOW(), last_recharge_date),
                         updated_at = NOW()
                     WHERE user_id = :id";
            try {
                $stmt = $this->conn->prepare($query);
                $is_recharge_flag = ($amount_change > 0) ? 1 : 0;

                $stmt->bindParam(':amount_to_change', $amount_change);
                $stmt->bindParam(':is_recharge', $is_recharge_flag, PDO::PARAM_INT);
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $this->getUserById($this->id);
                    return true;
                }

                $this->lastError = "Database balance update failed: " . implode(", ", $stmt->errorInfo());
                error_log($this->lastError . " UserID: " . $this->id);
                return false;
            } catch (PDOException $e) {
                $this->lastError = "PDOException on balance update: " . $e->getMessage();
                error_log($this->lastError . " UserID: " . $this->id);
                return false;
            }
        }
        $this->lastError = "No valid transaction data provided for user update.";
        return false;
    }

    public function getUserById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->id = (int)$row['user_id'];
                $this->name = $row['username'];
                $this->email = $row['email'];
                $this->balance = (float)$row['balance'];
                $this->last_recharge_date = $row['last_recharge_date'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("User::getUserById - PDOException: " . $e->getMessage() . " for UserID: " . $id);
            return false;
        }
    }

    public function getFavoriteRoute($user_id)
    {
        $query = "SELECT source_station, destination_station, COUNT(*) as count
              FROM bookings
              WHERE bookings.user_id = ? 
              AND source_station IS NOT NULL AND destination_station IS NOT NULL
              GROUP BY bookings.source_station, bookings.destination_station
              ORDER BY count DESC
              LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return $row['source_station'] . ' - ' . $row['destination_station'];
            }
            return "None";
        } catch (PDOException $e) {
            error_log("User::getFavoriteRoute - PDOException: " . $e->getMessage() . " for UserID: " . $user_id);
            return "Error";
        }
    }

    public function updateProfile($userId, $username, $email)
    {
        $query = "UPDATE " . $this->table_name . " SET username = ?, email = ?, updated_at = NOW() WHERE user_id = ?";
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute([$username, $email, $userId])) {
                if ($this->id == $userId) {
                    $this->name = $username;
                    $this->email = $email;
                }
                return true;
            }
            error_log("User::updateProfile - DB Error: " . implode(", ", $stmt->errorInfo()) . " for UserID: " . $userId);
            return false;
        } catch (PDOException $e) {
            error_log("User::updateProfile - PDOException: " . $e->getMessage() . " for UserID: " . $userId);
            return false;
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        $this->lastError = '';
        $query_select = "SELECT password FROM " . $this->table_name . " WHERE user_id = ?";
        try {
            $stmt_select = $this->conn->prepare($query_select);
            $stmt_select->bindParam(1, $userId, PDO::PARAM_INT);
            $stmt_select->execute();
            $user = $stmt_select->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->lastError = "User not found for password update.";
                error_log($this->lastError . " UserID: " . $userId);
                return false;
            }

            if (!password_verify(trim($currentPassword), $user['password'])) {
                $this->lastError = "Incorrect current password provided.";
                return false;
            }

            if (strlen(trim($newPassword)) < 8) {
                $this->lastError = "New password must be at least 8 characters long.";
                return false;
            }

            $hashedPassword = password_hash(trim($newPassword), PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                $this->lastError = "Error hashing new password.";
                error_log($this->lastError . " UserID: " . $userId);
                return false;
            }

            $query_update = "UPDATE " . $this->table_name . " SET password = ?, updated_at = NOW() WHERE user_id = ?";
            $stmt_update = $this->conn->prepare($query_update);
            if ($stmt_update->execute([$hashedPassword, $userId])) {
                return true;
            }
            $this->lastError = "Database error: Could not update password.";
            error_log($this->lastError . " UserID: " . $userId . " DB Error: " . implode(", ", $stmt_update->errorInfo()));
            return false;
        } catch (PDOException $e) {
            $this->lastError = "Database operation failed during password update: " . $e->getMessage();
            error_log($this->lastError . " UserID: " . $userId);
            return false;
        }
    }
}
