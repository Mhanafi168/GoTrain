<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoTrain - User Dashboard</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/dashboard.css">
</head>


<button class="sidebar-toggle d-lg-none">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">
    <div class="text-center mb-4">
        <h4 class="animate__animated animate__fadeInDown">GoTrain</h4>
    </div>
    <hr class="my-2 bg-light">

    <ul class="nav flex-column">
        <li class="nav-item animate__animated animate__fadeInLeft animate__delay-1s">
            <a class="nav-link active" href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item animate__animated animate__fadeInLeft animate__delay-2s">
            <a class="nav-link" href="../User/book_ticket.php">
                <i class="fas fa-ticket-alt"></i>
                <span>Book Ticket</span>
            </a>
        </li>
        <li class="nav-item animate__animated animate__fadeInLeft animate__delay-3s">
            <a class="nav-link" href="/GoTrain/views/ticket/index.php">
                <i class="fas fa-list-alt"></i>
                <span>My Tickets</span>
            </a>
        </li>
        <li class="nav-item animate__animated animate__fadeInLeft animate__delay-4s">
            <a class="nav-link" href="../User/TrainTimings.php">
                <i class="fas fa-clock"></i>
                <span>Train Timings</span>
            </a>
        </li>
        <li class="nav-item animate__animated animate__fadeInLeft animate__delay-5s">
            <a class="nav-link" href="../User//recharge.php">
                <i class="fas fa-wallet"></i>
                <span>Recharge</span>
            </a>
        </li>
        <li class="nav-item animate__animated animate__fadeInLeft ">
            <a class="nav-link" href="/GoTrain/views/User/account_history.php">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Account History</span>
            </a>
        </li>
        <li class="nav-item animate__animated animate__fadeInLeft ">
            <a class="nav-link" href="/GoTrain/views/User/apply_compensation.php">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Apply for Compensation</span>
            </a>
        </li>

        <li class="nav-item animate__animated animate__fadeInLeft animate__delay-6s">
            <a class="nav-link" href="../User/my_profile.php">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </li>
        <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-7s">
            <a class="nav-link" href="/GoTrain/views/auth/login.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 welcome-text">
        <div>
            <h2 class="animate__animated animate__fadeIn">Welcome, <span id="userName" class="text-primary"><?= htmlspecialchars($data['user']['name']) ?></span>!</h2>
            <p class="text-muted animate__animated animate__fadeIn animate__delay-1s">Here's your train travel summary</p>
        </div>
        <div class="animate__animated animate__fadeIn animate__delay-2s">
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> Book New Ticket
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="card balance-card h-100 animate-delay-1">
                <div class="card-body text-center">
                    <h5>Account Balance</h5>
                    <div class="balance-amount mt-3 mb-2">$ <span id="accountBalance"><?= number_format($data['user']['balance'], 2) ?></span></div>
                    <p>Last recharged on <span id="lastRecharge"><?= $data['user']['last_recharge_date'] ?></span></p>
                    <button class="btn btn-light mt-2">
                        <i class="fas fa-plus-circle"></i> Recharge Now
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 animate-delay-2">
                        <div class="card-body text-center">
                            <i class="fas fa-ticket-alt text-primary mb-3" style="font-size: 2rem;"></i>
                            <h3 id="totalBookings" class="counter"><?= $data['stats']['total_bookings'] ?></h3>
                            <p class="text-muted">Total Bookings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 animate-delay-3">
                        <div class="card-body text-center">
                            <i class="fas fa-route text-success mb-3" style="font-size: 2rem;"></i>
                            <h3 id="upcomingTrips" class="counter"><?= $data['stats']['upcoming_trips'] ?></h3>
                            <p class="text-muted">Upcoming Trips</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 animate-delay-4">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marker-alt text-danger mb-3" style="font-size: 2rem;"></i>
                            <h3 id="favoriteRoute"><?= $data['stats']['favorite_route'] ?></h3>
                            <p class="text-muted">Favorite Route</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="animate__animated animate__fadeIn">Quick Actions</h5>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="quick-action animate-delay-1">
                <i class="fas fa-ticket-alt"></i>
                <p>Book Ticket</p>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="quick-action animate-delay-2">
                <i class="fas fa-print"></i>
                <p>Print Ticket</p>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="quick-action animate-delay-3">
                <i class="fas fa-clock"></i>
                <p>Train Timings</p>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="quick-action animate-delay-4">
                <i class="fas fa-history"></i>
                <p>Booking History</p>
            </div>
        </div>
    </div>


    <script src="../public/js/dashboard.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/jquery-3.7.1.min.js"></script>
    <script src="../public/assets/js/bootstrap.js"></script>


    </body>

</html>