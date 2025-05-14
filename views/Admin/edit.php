<?php
require_once '../../config/DBController.php';
require_once '../../models/userr.php';
require_once '../../controllers/Authcontroller.php';

$db = DBController::getInstance();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "User ID not provided.";
    exit;
}

$query = "SELECT * FROM users WHERE user_id = ?";
$users = $db->select($query, [$id]);

if (!$users || count($users) === 0) {
    echo "User not found.";
    exit;
}

$user = $users[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $roleid = $_POST['roleid'] ?? '';

    $updateQuery = "UPDATE users SET username = ?, email = ?, roleid = ? WHERE user_id = ?";
    $params = [$name, $email, $roleid, $id];

    if ($db->execute($updateQuery, $params)) {
        header("Location: admin.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Failed to update user: " . htmlspecialchars($db->getLastError()) . "</div>";
    }
}
?>

<div class="container mt-4">
    <h2>Edit User</h2>
    <form method="POST">
        <div class="form-group">
            <label>Name:</label>
            <input type="text" class="form-control" name="name"
                value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" class="form-control" name="email"
                value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Role ID:</label>
            <input type="number" class="form-control" name="roleid"
                value="<?php echo htmlspecialchars($user['roleid']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="admin.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --border-color: #ced4da;
        --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background-color: #f5f7fa;
        color: #212529;
        line-height: 1.6;
        padding: 0;
        margin: 0;
    }

    .container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 0.5rem;
        box-shadow: var(--box-shadow);
    }

    h2 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-color);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--dark-color);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid var(--border-color);
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.625rem 1.25rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        margin-right: 0.5rem;
    }

    .btn-primary {
        color: #fff;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: #3a56d4;
        border-color: #3a56d4;
    }

    .btn-secondary {
        color: #fff;
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    .alert {
        position: relative;
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
            margin: 1rem;
            padding: 1.5rem;
        }

        .btn {
            display: block;
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>