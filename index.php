<?php
require_once './models/bookings.php';
require_once './config/DBController.php';
$book = new bookings;
$db = DBController::getInstance();
$result;
$db->openConnection();

$columnAliases = [
    'journey_date' => 'booking_date'
];

$result = $db->select(
    "SELECT source_station, destination_station, booking_date, departure_time FROM bookings",
    [],
    $columnAliases
);

$db->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GO Train - Book Your Journey</title>
    <link href="./views/public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="./views/public/css/landing.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">TrainEase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="btn btn-auth" href="./views/auth/login.php">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-auth" href="./views/auth/register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero d-flex align-items-center">
        <div class="container hero-content text-center">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="hook-text" id="typingText">Your Journey Begins With A Click</h1>
                    <p class="hook-subtitle">Seamless train ticket booking for your next adventure.</p>
                    <a class="btn btn-auth" href="./views/auth/login.php">Book Your Ticket</a>


                </div>
            </div>
        </div>
        <a href="#features" class="scroll-down" id="scrollDown">
            <i class="fas fa-chevron-down"></i>
        </a>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="row text-center">
                <?php if (!empty($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fa-solid fa-train"></i>
                            </div>
                            <h3 class="feature-title">
                                <?php echo 'Available ' . htmlspecialchars($result[7]['destination_station']) . ' Trip' ?>
                            </h3>
                            <p>
                                <?php echo 'Journey date: ' . htmlspecialchars($result[7]['journey_date']) ?><br>
                                <?php echo 'Departure: ' . htmlspecialchars($result[7]['departure_time']) ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fa-solid fa-train"></i>
                            </div>
                            <h3 class="feature-title">
                                <?php echo 'Available ' . htmlspecialchars($result[1]['destination_station']) . ' Trip' ?>
                            </h3>
                            <p>
                                <?php echo 'Journey date: ' . htmlspecialchars($result[1]['journey_date']) ?><br>
                                <?php echo 'Departure: ' . htmlspecialchars($result[1]['departure_time']) ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fa-solid fa-train"></i>
                            </div>
                            <h3 class="feature-title">
                                <?php echo 'Available ' . htmlspecialchars($result[13]['destination_station']) . ' Trip' ?>
                            </h3>
                            <p>
                                <?php echo 'Journey date: ' . htmlspecialchars($result[13]['journey_date']) ?><br>
                                <?php echo 'Departure: ' . htmlspecialchars($result[13]['departure_time']) ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">No upcoming trips available at the moment.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="./views/public/assets/js/popper.min.js"></script>
    <script src="./views/public/assets/js/jquery-3.7.1.min.js"></script>
    <script src="./views/public/assets/js/bootstrap.js"></script>
    <script src="./views/public/js/landing.js"></script>
</body>

</html>