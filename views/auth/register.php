<?php
require_once '../../models/User.php';
require_once '../../controllers/Authcontroller.php';
require_once '../../config/DBController.php';

if (!isset($_SESSION["userid"])) {
    session_start();
}

$erruser = "";
$errpass = "";
$erremail = "";
$error = "";
$success = "";

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        
        $db = DBController::getInstance();
        $user = new User($db);

        $user->name = $username;
        $user->email = $email;
        $user->password = $password;
        $user->role_id = 2;
        
        $auth = new Authcontroller;
        if (!$auth->register($user)) {
            $error = $_SESSION["error"];
        } else {
            header("Location: ../Dashboard/index.php");
            exit();
        }
    } else {
        if (empty($_POST['username'])) {
            $erruser = 'Please enter your username';
        }
        if (empty($_POST['password'])) {
            $errpass = 'Please enter your password';
        }
        if (empty($_POST['email'])) {
            $erremail = 'Please enter your email';
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Create Account - GoTrain</title>
  <link rel="stylesheet" href="../public/css/styleregister.css">
</head>

<body>
  <div class="wrapper1">
    <form action="" method="post">
      <h1>Create Account</h1>
      
      <?php if (!empty($error)) : ?>
        <div style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <?php if (!empty($success)) : ?>
        <div style="color: green; margin-bottom: 10px;"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      
      <div class="input-box">
        <label>Username</label><br>
        <input type="text" placeholder="Enter username" name="username" required>
        <?php if (!empty($erruser)) : ?>
          <span style="color:red;"><?php echo htmlspecialchars($erruser); ?></span>
        <?php endif; ?>
      </div>

      <div class="input-box">
        <label>Email</label><br>
        <input type="email" placeholder="Enter email" name="email" required>
        <?php if (!empty($erremail)) : ?>
          <span style="color:red;"><?php echo htmlspecialchars($erremail); ?></span>
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
        <button type="submit">Create Account</button>
      </div>

      <div class="mt-3">
        <a href="login.php" class="back-to-login">Already have an account? Login</a>
      </div>
    </form>
  </div>
</body>

</html>