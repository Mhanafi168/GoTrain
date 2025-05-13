<?php

require_once __DIR__ . '/../models/userr.php';
require_once __DIR__ . '/../config/DBController.php';

class Authcontroller
{
    protected $db;

    public function login(User $user)
    {
        $this->db = DBController::getInstance();
        if ($this->db->openConnection()) {
            $email = $user->email;
            $password = $user->password;
            $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
            $result = $this->db->select($query);

            if (!$result || count($result) == 0) {
                session_start();
                $_SESSION["error"] = "Wrong email or password!";
                return false;
            } else {
                session_start();
                $_SESSION["user_id"] = $result[0]["user_id"];
                $_SESSION["userName"] = $result[0]["username"];
                $_SESSION["userRole"] = $result[0]["roleid"];
                $_SESSION["success"] = "logged in";

                return true;
            }
        }
    }
    public function register(User $user)
    {
        $this->db = DBController::getInstance();

        if ($this->db->openConnection()) {
            $email = $user->email;
            $password = $user->password;
            $name = $user->name;
            $role = $user->roleid;

            $checkQuery = "SELECT * FROM users WHERE email = '$email'";
            $checkResult = $this->db->select($checkQuery);

            if ($checkResult && count($checkResult) > 0) {
                $_SESSION["error"] = "Email already exists!";
                return false;
            }

            $insertQuery = "INSERT INTO users (username, email, password, roleid) 
                        VALUES ('$name', '$email', '$password', 2)";
            $isInserted = $this->db->insert($insertQuery);

            if ($isInserted) {
                session_start();
                $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
                $result = $this->db->select($query);
                $_SESSION["userId"] = $result[0]["user_id"];
                $_SESSION["userName"] = $result[0]["username"];
                $_SESSION["userRole"] = $result[0]["roleid"];
                $_SESSION["success"] = "Registration successful!";
                return true;
            } else {
                session_start();
                $_SESSION["error"] = "Failed to register user.";
                return false;
            }
        } else {
            return false;
        }
    }
}
