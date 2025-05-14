<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Train Schedule Management</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a56d4;
            --success: #28a745;
            --success-hover: #218838;
            --danger: #dc3545;
            --danger-hover: #c82333;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border: #dee2e6;
            --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            padding: 2rem 1rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            overflow: hidden;
            text-align: center;
        }

        .card-header {
            background-color: var(--dark);
            color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header h5 {
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: var(--dark);
            border-collapse: collapse;
        }

        .table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid var(--border);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--success-hover);
            border-color: var(--success-hover);
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
            border-color: var(--danger-hover);
            transform: translateY(-1px);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .card-header {
                padding: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table th,
            .table td {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .btn {
                margin-bottom: 0.25rem;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table tbody tr {
            animation: fadeIn 0.3s ease forwards;
        }

        .table tbody tr:nth-child(1) {
            animation-delay: 0.1s;
        }

        .table tbody tr:nth-child(2) {
            animation-delay: 0.2s;
        }

        .table tbody tr:nth-child(3) {
            animation-delay: 0.3s;
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

    if ($db->openConnection()) {
        $query = "SELECT * FROM users";
        $result = $db->select($query);

        if ($result !== false) {
            $users = $result;
        } else {
            echo "<div class='alert alert-danger'>Query failed: Unable to fetch users.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Database connection failed.</div>";
    }
    ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">List of All Users</h5>
                        <a href="admin.php" class="btn btn-primary  btn-sm">Go Back To Dashboard</a>
                        <a href="add.php" class="btn btn-primary btn-sm">Add User</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <?php if (!empty($users)): ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Username</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Role ID</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        foreach ($users as $user): ?>
                                            <tr>
                                                <th scope='row'><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['roleid']) ?></td>
                                                <td>
                                                    <a href='edit.php?id=<?= $user['user_id'] ?>' class='btn btn-success btn-sm'>Edit</a>
                                                    <a href='remove.php?id=<?= $user['user_id'] ?>' class='btn btn-danger btn-sm' onclick='return confirm("Are you sure you want to delete this user?")'>Remove</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No users found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
