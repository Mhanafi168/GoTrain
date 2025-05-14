<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station Master Dashboard | GoTrain</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <style>
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #8338ec;
            --success-color: #06d6a0;
            --warning-color: #ffbe0b;
            --danger-color: #ef476f;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            overflow-x: hidden;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-toggle {
            position: fixed;
            left: 10px;
            top: 10px;
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: none;
        }

        .sidebar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 0 30px 30px 0;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .welcome-text {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }

        .balance-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
        }

        .quick-action {
            background-color: white;
            padding: 20px 10px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .quick-action:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-5px);
        }

        .quick-action i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .booking-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .booking-item:hover {
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .badge-upcoming {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        @media (max-width: 992px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }
        }

        .animate-delay-1 {
            animation-delay: 0.1s;
        }

        .animate-delay-2 {
            animation-delay: 0.2s;
        }

        .animate-delay-3 {
            animation-delay: 0.3s;
        }

        .animate-delay-4 {
            animation-delay: 0.4s;
        }

        .animate-delay-5 {
            animation-delay: 0.5s;
        }
    </style>
</head>

<body>
    <?php require_once '../../models/User.php';
    require_once '../../controllers/Authcontroller.php';
    $db = DBController::getInstance();
    if ($db->openConnection()) {
        $query = "SELECT * FROM users";
        $users = $db->select($query);
    }
    ?>
    <button class="sidebar-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <div class="sidebar-brand text-center mb-4">
            <h4 class="animate__animated animate__fadeInDown">GoTrain Station Master</h4>
        </div>
        <hr class="my-2 bg-light">

        <ul class="nav flex-column">
            <li class="nav-item animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="update_arrival_departure.php">
                    <i class="fas fa-clock"></i>
                    <span>Arrival/Departure Times</span>
                </a>
            </li>
            <li class="nav-item animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="delete_delay_record.php">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete Delay Record</span>
                </a>
            </li>
            <li class="nav-item animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="declare_public_delay.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Declare Public Delay</span>
                </a>
            </li>
            <li class="nav-item animate__animated animate__fadeInLeft animate__delay-2s">
                <a class="nav-link" href="../User/my_profile.php">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-2s">
                <a class="nav-link" href="../auth/login.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 welcome-text">
            <div>
                <h2 class="animate__animated animate__fadeIn">Welcome, Station Master</h2>
                <p class="text-muted animate__animated animate__fadeIn animate__delay-1s">Station management dashboard</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary p-2">Station Master</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 animate-delay-1">
                    <div class="card-body text-center">
                        <i class="fas fa-train text-primary mb-3" style="font-size: 2rem;"></i>
                        <h3 id="arrivingTrains" class="counter">12</h3>
                        <p class="text-muted">Trains Arriving Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 animate-delay-2">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-warning mb-3" style="font-size: 2rem;"></i>
                        <h3 id="delayedTrains" class="counter">3</h3>
                        <p class="text-muted">Delayed Trains</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 animate-delay-3">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 2rem;"></i>
                        <h3 id="onTimeTrains" class="counter">9</h3>
                        <p class="text-muted">On Time Trains</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 animate-delay-4">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 2rem;"></i>
                        <h3 id="cancelledTrains" class="counter">0</h3>
                        <p class="text-muted">Cancelled Trains</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <h5 class="animate__animated animate__fadeIn">Quick Actions</h5>
                <hr>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="quick-action animate-delay-1" onclick="location.href='update_arrival_departure.php'">
                    <i class="fas fa-clock"></i>
                    <p>Update Times</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="quick-action animate-delay-2" onclick="location.href='declare_public_delay.php'">
                    <i class="fas fa-bullhorn"></i>
                    <p>Announce Delay</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="quick-action animate-delay-3" onclick="location.href='delete_delay_record.php'">
                    <i class="fas fa-trash-alt"></i>
                    <p>Remove Delay</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Today's Train Schedule</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary me-2">Print</button>
                            <button class="btn btn-sm btn-primary">Refresh</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Train No.</th>
                                        <th>Train Name</th>
                                        <th>Arrival</th>
                                        <th>Departure</th>
                                        <th>Platform</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="animate__animated animate__fadeIn animate-delay-1">
                                        <td>12345</td>
                                        <td>Express</td>
                                        <td>08:00</td>
                                        <td>08:05</td>
                                        <td>3</td>
                                        <td><span class="badge bg-success">On Time</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Update</button>
                                        </td>
                                    </tr>
                                    <tr class="animate__animated animate__fadeIn animate-delay-2">
                                        <td>67890</td>
                                        <td>Superfast</td>
                                        <td>09:30</td>
                                        <td>09:35</td>
                                        <td>1</td>
                                        <td><span class="badge bg-warning">Delayed (15 min)</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Update</button>
                                        </td>
                                    </tr>
                                    <tr class="animate__animated animate__fadeIn animate-delay-3">
                                        <td>54321</td>
                                        <td>Local</td>
                                        <td>10:15</td>
                                        <td>10:20</td>
                                        <td>2</td>
                                        <td><span class="badge bg-success">On Time</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Update</button>
                                        </td>
                                    </tr>
                                    <tr class="animate__animated animate__fadeIn animate-delay-4">
                                        <td>98765</td>
                                        <td>Intercity</td>
                                        <td>12:00</td>
                                        <td>12:05</td>
                                        <td>4</td>
                                        <td><span class="badge bg-success">On Time</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">Update</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Delay Announcements</h5>
                        <a href="declare_public_delay.php" class="btn btn-sm btn-outline-primary">New Announcement</a>
                    </div>
                    <div class="card-body">
                        <div class="announcement-list">
                            <div class="announcement-item p-3 mb-3 animate-delay-1">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Train #67890 - Superfast</h6>
                                        <p class="text-muted mb-1">Delayed by 15 minutes due to technical issues</p>
                                        <small class="text-muted">Posted 1 hour ago</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </div>
                                </div>
                            </div>
                            <div class="announcement-item p-3 mb-3 animate-delay-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Train #54321 - Local</h6>
                                        <p class="text-muted mb-1">Platform changed from 3 to 2</p>
                                        <small class="text-muted">Posted 3 hours ago</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </div>
                                </div>
                            </div>
                            <div class="announcement-item p-3 animate-delay-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6>Train #12345 - Express</h6>
                                        <p class="text-muted mb-1">Rescheduled departure to 08:10</p>
                                        <small class="text-muted">Posted yesterday</small>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Platform Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="platform-status platform-available">
                                    <h6>Platform 1</h6>
                                    <p>Available</p>
                                    <small>Next train in 15 min</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="platform-status platform-occupied">
                                    <h6>Platform 2</h6>
                                    <p>Occupied</p>
                                    <small>Local #54321</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="platform-status platform-maintenance">
                                    <h6>Platform 3</h6>
                                    <p>Maintenance</p>
                                    <small>Until 14:00</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="platform-status platform-available">
                                    <h6>Platform 4</h6>
                                    <p>Available</p>
                                    <small>Next train in 30 min</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../public/js/dashboard.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/jquery-3.7.1.min.js"></script>
    <script src="../public/assets/js/bootstrap.js"></script>
    <script>
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        $(document).ready(function() {
            $('.counter').each(function() {
                $(this).prop('Counter', 0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function(now) {
                        $(this).text(Math.ceil(now));
                    }
                });
            });
        });
    </script>
</body>

</html>