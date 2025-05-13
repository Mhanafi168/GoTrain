<?php
require_once '../../models/userr.php';
require_once '../../controllers/Authcontroller.php';

if (!isset($_SESSION["userid"])) {
  session_start();
}
$erruser;
$errpass;
$erremail;
$error = "";
$sucess = "";
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
  if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $user = new User;

    $user->name = $username;
    $user->email = $email;
    $user->password = $password;
    $user->roleid = 2;
    $auth = new Authcontroller;
    if (!$auth->register($user)) {

      $error = $_SESSION["error"];
    } else {
      header("Location: /Gotrain/views/Dashboard/index.php");
    }
  } else {
    if (empty($_POST['username'])) {
      $erruser = 'please enter the username';
    }
    if (empty($_POST['password'])) {
      $errpass = 'please enter the password';
    }
    if (empty($_POST['email'])) {
      $erremail = 'please enter the email';
    }
  }
}


?>


<!DOCTYPE html>
<html>

<head>
  <title>create account</title>
  <link rel="stylesheet" href="../public/css/styleregister.css">
</head>

<body>
  <div class="wrapper1">
    <form action="" method="post">
      <h1>create account</h1>
      <?php if (!empty($error)) : ?>
        <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
      <?php endif;
      ?>
      <?php if (!empty($sucess)) : ?>
        <div style="color: green; margin-bottom: 10px;"><?php echo $sucess; ?></div>
      <?php endif;
      ?>
      <div class="input-box">
        <label>Username</label><br>
        <input type="text" placeholder="Enter username" name="username">
        <span style="color:red;">
          <?php if (isset($erruser)) echo $erruser; ?>
        </span>
      </div>
      <br></br>



      <div class="input-box">
        <label>email</label><br>
        <input type="email" placeholder="enter email" name="email">
        <span style="color:red;">
          <?php if (isset($erremail)) echo $erremail; ?>
        </span>
      </div>
      <br></br>

      <div class="input-box">
        <label>Password</label><br>
        <input type="password" placeholder="Enter password" name="password">
        <span style="color:red;">
          <?php if (isset($errpass)) echo $errpass; ?>
        </span>
      </div>


      <br>
      <div>
        <a href="/Gotrain/views/Dashboard/index.php">
          <button type="submit">submit</button>
        </a>
      </div>


    </form>
</body>

</html>