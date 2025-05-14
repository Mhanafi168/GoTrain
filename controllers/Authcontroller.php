<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/DBController.php';

class Authcontroller
{
    protected ?DBController $db = null;

    public function login(User $user): bool
    {
        $this->db = DBController::getInstance();
        if ($this->db->openConnection()) {
            $email = $user->email;
            $password = $user->password;
            
            $query = "SELECT * FROM users WHERE email=?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                $_SESSION["error"] = "Wrong email or password!";
                return false;
            }

            if (password_verify($password, $result['password']) || $password === $result['password']) {
                if ($password === $result['password']) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $this->db->getConnection()->prepare(
                        "UPDATE users SET password = ? WHERE user_id = ?"
                    );
                    $updateStmt->execute([$hashedPassword, $result['user_id']]);
                }

                $_SESSION["user_id"] = $result["user_id"];
                $_SESSION["userName"] = $result["username"];
                $_SESSION["userRole"] = $result["roleid"];
                $_SESSION["success"] = "Successfully logged in";
                return true;
            } else {
                $_SESSION["error"] = "Wrong email or password!";
                return false;
            }
        }
        return false;
    }

    public function register(User $user): bool
    {
        $this->db = DBController::getInstance();

        if ($this->db->openConnection()) {
            $email = $user->email;
            $password = password_hash($user->password, PASSWORD_DEFAULT);
            $name = $user->name;

            $checkStmt = $this->db->getConnection()->prepare("SELECT * FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $_SESSION["error"] = "Email already exists!";
                return false;
            }

            $insertStmt = $this->db->getConnection()->prepare(
                "INSERT INTO users (username, email, password, roleid, balance) 
                 VALUES (?, ?, ?, 2, 0)"
            );
            
            if ($insertStmt->execute([$name, $email, $password])) {
                $userId = $this->db->getConnection()->lastInsertId();
                
                $_SESSION["user_id"] = $userId;
                $_SESSION["userName"] = $name;
                $_SESSION["userRole"] = 2;
                $_SESSION["success"] = "Registration successful!";
                return true;
            } else {
                $_SESSION["error"] = "Failed to register user.";
                return false;
            }
        }
        return false;
    }
}
