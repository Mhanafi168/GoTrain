<?php
session_start();

require_once '../../config/DBController.php';
require_once '../../models/User.php';
require_once '../../models/Booking.php';
require_once '../../models/Transactions.php';
require_once '../../controllers/DashboardController.php';

$db = DBController::getInstance()->getConnection();

$userModel = new User($db);
$bookingModel = new Booking($db);

$controller = new DashboardController($userModel, $bookingModel);
$controller->index();
?>