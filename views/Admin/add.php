<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New User</title>
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

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--dark);
            background-color: #fff;
            border: 1px solid var(--border);
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
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

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>
    <?php
    require_once '../../config/DBController.php';
    require_once '../../models/User.php';
    require_once '../../controllers/Authcontroller.php';

    $db = DBController::getInstance();
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $roleid = $_POST['roleid'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($roleid)) {
            $message = 'All fields are required';
            $messageType = 'danger';
        } else {
            if ($db->openConnection()) {
                $checkQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
                $checkParams = [$username, $email];
                $existingUser = $db->select($checkQuery, $checkParams);

                if ($existingUser) {
                    $message = 'Username or email already exists';
                    $messageType = 'danger';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $insertQuery = "INSERT INTO users (username, email, password, roleid) VALUES (?, ?, ?, ?)";
                    $insertParams = [$username, $email, $hashedPassword, $roleid];
                    
                    if ($db->insert($insertQuery, $insertParams)) {
                        $message = 'User added successfully';
                        $messageType = 'success';
                        $_POST = array();
                    } else {
                        $message = 'Failed to add user';
                        $messageType = 'danger';
                    }
                }
            } else {
                $message = 'Database connection failed';
                $messageType = 'danger';
            }
        }
    }
    ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Add New User</h5>
                        <a href="blank.php" class="btn btn-primary btn-sm">Back to List</a>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="roleid">Role ID</label>
                                <select class="form-control" id="roleid" name="roleid" required>
                                    <option value="">Select Role</option>
                                    <option value="1" <?php echo (isset($_POST['roleid']) && $_POST['roleid'] == '1') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="2" <?php echo (isset($_POST['roleid']) && $_POST['roleid'] == '2') ? 'selected' : ''; ?>>User</option>
                                    <option value="3" <?php echo (isset($_POST['roleid']) && $_POST['roleid'] == '3') ? 'selected' : ''; ?>>Station Master</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>