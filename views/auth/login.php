<?php
require_once '../../models/userr.php';
require_once '../../controllers/Authcontroller.php';
require_once '../../config/DBController.php';
$db =  DBController::getInstance();;

$heading = "Login";
$erruser;
$errpass;
$error = "";
$sucess = "";

if (isset($_POST['email']) && isset($_POST['password'])) {
  if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $user = new User($db);
    $auth = new Authcontroller;
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    if (!$auth->login($user)) {

      $error = $_SESSION["error"];
    } else {
      if ($_SESSION["userRole"] == 1) {

        header("Location: ../Admin/admin.php");
      } elseif ($_SESSION["userRole"] == 3) {

        header("Location: ../Station_Master/Station_master.php");
      } else {
        header("Location:../Dashboard/index.php"); //:حط الداش بورد هنا بعد ال 

      }
    }
  } else {
    if (empty($_POST['email'])) {
      $erruser = 'please enter the email';
    }
    if (empty($_POST['password'])) {
      $errpass = 'please enter the password';
    }
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="../public/css/stylelog.css">
</head>

<body>
  <div class="wrapper">
    <form action="" method="post">
      <h1><?php echo $heading; ?></h1>
      <?php if (!empty($error)) : ?>
        <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
      <?php endif;
      ?>
      <?php if (!empty($sucess)) : ?>
        <div style="color: green; margin-bottom: 10px;"><?php echo $sucess; ?></div>
      <?php endif;
      ?>
      <div class="input-box">
        <label>email</label><br>
        <input type="text" placeholder="Enter email" name="email">
        <span style="color:red;">
          <?php if (isset($erruser)) echo $erruser; ?>
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
        <button type="submit">Login</button>
        <a class="forgot" href="#">forgot password?</a>
      </div>
      <br> <br>
      <a class="create acc" href="register.php"> create Account?</a>


    </form>

</body>

</html>