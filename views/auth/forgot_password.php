<?php
session_start();
require_once '../../config/DBController.php';
require_once '../../models/User.php';

$db = DBController::getInstance();
$error = "";
$success = "";
$show_password_form = false;
$user_email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            try {
                $query = "SELECT user_id, username FROM users WHERE email = ?";
                $stmt = $db->getConnection()->prepare($query);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $show_password_form = true;
                    $user_email = $email;
                } else {
                    $error = "This email is not registered in our system.";
                }
            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
            }
        }
    } elseif (isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_POST['user_email'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $email = $_POST['user_email'];

        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
            $show_password_form = true;
            $user_email = $email;
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
            $show_password_form = true;
            $user_email = $email;
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE email = ?";
                $update_stmt = $db->getConnection()->prepare($update_query);
                $update_stmt->execute([$hashed_password, $email]);

                if ($update_stmt->rowCount() > 0) {
                    $_SESSION['success_flash_message'] = "Your password has been reset successfully. You can now login with your new password.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Failed to reset password. Please try again.";
                    $show_password_form = true;
                    $user_email = $email;
                }
            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
                $show_password_form = true;
                $user_email = $email;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - GoTrain</title>
    <link rel="stylesheet" href="../public/css/stylelog.css">
    <style>
        .back-to-login {
            display: inline-block;
            color: #4a6bff;
            text-decoration: none;
            margin-top: 15px;
            font-size: 0.9em;
        }
        .back-to-login:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .message.error {
            background-color: #ffe6e6;
            border: 1px solid #ff9999;
            color: #cc0000;
        }
        .message.success {
            background-color: #e6ffe6;
            border: 1px solid #99ff99;
            color: #006600;
        }
        .password-requirements {
            font-size: 0.85em;
            color: #666;
            margin: 5px 0 15px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1><?php echo $show_password_form ? 'Change Password' : 'Forgot Password'; ?></h1>
            
            <?php if (!empty($error)) : ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($show_password_form): ?>
                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($user_email); ?>">
                
                <div class="input-box">
                    <label>New Password</label><br>
                    <input type="password" placeholder="Enter new password" name="password" required>
                    <div class="password-requirements">Password must be at least 8 characters long</div>
                </div>
                
                <div class="input-box">
                    <label>Confirm Password</label><br>
                    <input type="password" placeholder="Confirm new password" name="confirm_password" required>
                </div>

                <div>
                    <button type="submit">Change Password</button>
                </div>
            <?php else: ?>
                <div class="input-box">
                    <label>Email</label><br>
                    <input type="email" placeholder="Enter your email" name="email" required>
                </div>

                <div>
                    <button type="submit">Continue</button>
                </div>
            <?php endif; ?>
            
            <div>
                <a href="login.php" class="back-to-login">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html> 