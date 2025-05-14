<?php
require_once '../../models/User.php';
require_once '../../controllers/Authcontroller.php';
require_once '../../config/DBController.php';


$db = DBController::getInstance();
if ($db->openConnection()) {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        $query = "SELECT * FROM users WHERE user_id = $id";
        $users = $db->select($query);

        if ($users !== false && !empty($users)) {
            $deleteQuery = "DELETE FROM users WHERE user_id = $id";
            $result = $db->execute($deleteQuery);
            if ($result) {
                header("Location: admin.php");
                exit;
            } else {
                echo "Error deleting user.";
            }
        } else {
            echo "User not found.";
        }
    }
}
