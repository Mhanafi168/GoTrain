<?php
session_start();
require_once '../../models/User.php';
require_once '../../controllers/Authcontroller.php';
require_once '../../config/DBController.php';

$db = DBController::getInstance();
$heading = "Login";
$erruser = "";
$errpass = "";
$error = "";
$success = "";

if (isset($_SESSION['error_flash_message'])) {
    $error = $_SESSION['error_flash_message'];
    unset($_SESSION['error_flash_message']);
}

if (isset($_SESSION['success_flash_message'])) {
    $success = $_SESSION['success_flash_message'];
    unset($_SESSION['success_flash_message']);
}

if (isset($_POST['email']) && isset($_POST['password'])) {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $user = new User($db);
        $auth = new Authcontroller;
        $user->email = $_POST['email'];
        $user->password = $_POST['password'];
        
        if (!$auth->login($user)) {
            $error = $_SESSION["error"] ?? "Login failed";
        } else {
            $redirect_url = isset($_SESSION['redirect_after_login']) ? 
                           $_SESSION['redirect_after_login'] : 
                           '../Dashboard/index.php';
            
            unset($_SESSION['redirect_after_login']);
            
            if ($_SESSION["userRole"] == 1) {
                header("Location: ../Admin/admin.php");
            } elseif ($_SESSION["userRole"] == 3) {
                header("Location: ../Station_Master/Station_master.php");
            } else {
                header("Location: " . $redirect_url);
            }
            exit();
        }
    } else {
        if (empty($_POST['email'])) {
            $erruser = 'Please enter your email';
        }
        if (empty($_POST['password'])) {
            $errpass = 'Please enter your password';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../public/css/stylelog.css">
    <title>Login - GoTrain</title>
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h1><?php echo htmlspecialchars($heading); ?></h1>
            
            <?php if (!empty($error)) : ?>
                <div style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)) : ?>
                <div style="color: green; margin-bottom: 10px;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="input-box">
                <label>Email</label><br>
                <input type="email" placeholder="Enter email" name="email" required>
                <?php if (!empty($erruser)) : ?>
                    <span style="color:red;"><?php echo htmlspecialchars($erruser); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="input-box">
                <label>Password</label><br>
                <input type="password" placeholder="Enter password" name="password" required>
                <?php if (!empty($errpass)) : ?>
                    <span style="color:red;"><?php echo htmlspecialchars($errpass); ?></span>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit">Login</button>
                <a class="forgot" href="forgot_password.php">Forgot password?</a>
            </div>
            
            <div class="mt-3">
                <a class="create-acc" href="register.php">Create Account</a>
            </div>
        </form>
    </div>
</body>
</html>