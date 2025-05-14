<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #f8f9fa;
            --sidebar-bg: rgb(6, 80, 191);
            --sidebar-text: #ecf0f1;
            --sidebar-hover: #34495e;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary-color);
            overflow-x: hidden;
            color: var(--text-dark);
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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

        .nav-link {
            color: var(--sidebar-text);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--sidebar-hover);
            color: white;
            text-decoration: none;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
            background-color: var(--secondary-color);
        }

        .dashboard-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
            background: var(--card-bg);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: var(--card-bg);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            color: var(--text-dark);
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            background: var(--card-bg);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .stat-card h3 {
            font-weight: 700;
            color: var(--primary-color);
        }

        .quick-action {
            background: var(--card-bg);
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .quick-action i {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .badge {
            padding: 6px 10px;
            font-weight: 500;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .bg-success {
            background-color: var(--success-color) !important;
        }

        .bg-warning {
            background-color: var(--warning-color) !important;
        }

        .bg-danger {
            background-color: var(--accent-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 992px) {
            .sidebar {
                left: -var(--sidebar-width);
            }

            .sidebar.active {
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
    </style>
</head>

<body>
    <?php
    require_once '../../config/DBController.php';
    require_once '../../models/User.php';
    require_once '../../controllers/Authcontroller.php';
    $db = DBController::getInstance();
    
    $users = [];
    $total_bookings = 0;
    $active_trains = 0;
    
    if ($db->openConnection()) {
        $users_query = "SELECT * FROM users";
        $users = $db->select($users_query) ?: [];
        
        $bookings_query = "SELECT COUNT(*) as total FROM bookings";
        $bookings_result = $db->select($bookings_query);
        $total_bookings = $bookings_result ? $bookings_result[0]['total'] : 0;
        
        $trains_query = "SELECT COUNT(*) as total FROM trains WHERE is_active = 1";
        $trains_result = $db->select($trains_query);
        $active_trains = $trains_result ? $trains_result[0]['total'] : 0;
    }
    ?>

    <button class="sidebar-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header text-center">
            <h4>GoTrain Admin</h4>
        </div>
        <hr class="my-2 bg-light">

        <ul class="nav flex-column nav-menu">
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="blank.php">
                    <i class="fas fa-users-cog"></i><span>Manage Users</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="manage_stations.php">
                    <i class="fas fa-map-marked-alt"></i><span>Manage Stations</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="admin_schedule.php">
                    <i class="fas fa-calendar-alt"></i><span>Train Schedules</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="view_tickets.php">
                    <i class="fas fa-receipt"></i><span>View All Bookings</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="recharge_user.php">
                    <i class="fas fa-wallet"></i><span>Recharge User Wallets</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="pricing_management.php">
                    <i class="fas fa-dollar-sign"></i><span>Manage Station Distances</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <a class="nav-link" href="compensation_requests_admin.php">
                    <i class="fas fa-gavel"></i><span>Compensation Requests</span>
                </a>
            </li>
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-2s">
                <a class="nav-link" href="../User/my_profile.php">
                    
                    <i class="fas fa-user-shield"></i><span>My Profile</span>
                </a>
            </li>
        </ul>
        <ul class="nav flex-column mt-auto">
            <li class="nav-item mt-4 animate__animated animate__fadeInLeft animate__delay-2s">
                <a class="nav-link" href="../auth/login.php">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">Welcome, Admin</h2>
                            <p class="text-muted">Here's your administration overview</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">Admin</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-card stat-card">
                        <i class="fas fa-users"></i>
                        <h3><?= count($users) ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card stat-card">
                        <i class="fas fa-ticket-alt"></i>
                        <h3><?= number_format($total_bookings) ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card stat-card">
                        <i class="fas fa-train"></i>
                        <h3><?= number_format($active_trains) ?></h3>
                        <p>Active Trains</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="quick-action" onclick="location.href='blank.php'">
                                        <i class="fas fa-user-plus"></i>
                                        <p>Add User</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="quick-action" onclick="location.href='admin_schedule.php'">
                                        <i class="fas fa-calendar-plus"></i>
                                        <p>Add Schedule</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="quick-action" onclick="location.href='manage_stations.php'">
                                        <i class="fas fa-plus-circle"></i>
                                        <p>Add Station</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="quick-action" onclick="location.href='recharge_user.php'">
                                        <i class="fas fa-wallet"></i>
                                        <p>Recharge User</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Users</h5>
                            <a href="blank.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Last Login</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($users)): ?>
                                            <?php foreach (array_slice($users, 0, 5) as $user): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $user['roleid'] == 1 ? 'danger' : ($user['roleid'] == 3 ? 'warning' : 'primary') ?>">
                                                            <?= $user['roleid'] == 1 ? 'Admin' : ($user['roleid'] == 3 ? 'Station Master' : 'User') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= !empty($user['last_login']) ? date('M j, Y g:i a', strtotime($user['last_login'])) : 'Never' ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No users found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../public/assets/js/jquery-3.7.1.min.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/bootstrap.js"></script>
    <script src="../public/js/dashboard.js"></script>
    <script>
        $('.sidebar-toggle').click(function() {
            $('.sidebar').toggleClass('active');
        });

        $(document).click(function(e) {
            if ($(window).width() < 992) {
                if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('.sidebar-toggle').length) {
                    $('.sidebar').removeClass('active');
                }
            }
        });

        $('.sidebar').click(function(e) {
            e.stopPropagation();
        });
    </script>
</body>

</html>