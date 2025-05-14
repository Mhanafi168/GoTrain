<?php
require_once '../../config/DBController.php';
require_once '../../models/User.php';

$db = DBController::getInstance();
$message = "";

if ($db->openConnection()) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_POST['user_id'];
        $amount = floatval($_POST['amount']);

        $user = $db->select("SELECT * FROM users WHERE user_id = ?", [$userId]);

        if ($user && count($user) > 0) {
            $user = $user[0];
            $newBalance = $user['balance'] + $amount;

            $query = "UPDATE users SET balance = ?, last_recharge_date = NOW() WHERE user_id = ?";
            if ($db->execute($query, [$newBalance, $userId])) {
                $message = "Balance updated successfully.";
            } else {
                $message = "Failed to update balance: " . $db->getLastError();
            }
        } else {
            $message = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Recharge User Balance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 40px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
        }

        .success {
            color: #155724;
        }

        .error {
            color: #721c24;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Recharge User Balance</h2>
        <form method="POST">
            <label for="user_id">User ID:</label>
            <input type="number" name="user_id" id="user_id" required>

            <label for="amount">Recharge Amount:</label>
            <input type="number" step="0.01" name="amount" id="amount" required>

            <button type="submit">Recharge</button>
        </form>
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Failed') === false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>