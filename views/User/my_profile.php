<?php
require_once '../../config/DBController.php';
require_once "../../models/User.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['userRole'])) {
    $_SESSION['error_flash_message'] = "Please login to access your profile.";
    header("Location: ../auth/login.php");
    exit();
}

try {
    $db = DBController::getInstance();
    if (!$db->openConnection()) {
        throw new Exception("Could not connect to database");
    }
    
    $pdo = $db->getConnection();
    $userModel = new User($pdo);

    if (!$userModel->getUserById($_SESSION['user_id'])) {
        error_log("Failed to load user data for ID: " . $_SESSION['user_id']);
        throw new Exception("Failed to load user data. Please try logging in again.");
    }

    $message = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        try {
            if (empty($username) || empty($email)) {
                throw new Exception("Username and email are required");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            if (!empty($current_password)) {
                if (empty($new_password)) {
                    throw new Exception("New password is required");
                }
                if (strlen($new_password) < 8) {
                    throw new Exception("Password must be at least 8 characters");
                }
                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords don't match");
                }

                if (!$userModel->updatePassword($_SESSION['user_id'], $current_password, $new_password)) {
                    throw new Exception($userModel->getLastError() ?: "Failed to update password");
                }
                $message = "Password updated successfully!";
            }

            if ($userModel->updateProfile($_SESSION['user_id'], $username, $email)) {
                $message = $message ? $message . " Profile updated successfully!" : "Profile updated successfully!";
                $userModel->getUserById($_SESSION['user_id']); 
            } else {
                throw new Exception("Failed to update profile");
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
} catch (Exception $e) {
    $_SESSION['error_flash_message'] = $e->getMessage();
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "My Profile - GoTrain";
$additional_css = '<style>
    .profile-card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .profile-header {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        border-radius: 15px 15px 0 0;
    }

    .profile-pic {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 5px solid white;
    }

    .nav-pills .nav-link.active {
        background-color: #6a11cb;
    }

    .nav-pills .nav-link {
        color: #495057;
    }
</style>';

require_once '../includes/header.php';
?>

<div class="main-content">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-4">
                <div class="card profile-card mb-4">
                    <div class="profile-header text-center py-4">
                        <div class="d-flex justify-content-center">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($userModel->name) ?>&background=random"
                                alt="Profile Picture" class="profile-pic rounded-circle">
                        </div>
                        <h3 class="mt-3 mb-0"><?= htmlspecialchars($userModel->name) ?></h3>
                        <p class="text-light mb-2"><?= htmlspecialchars($userModel->email) ?></p>
                        <span class="badge bg-light text-dark"><?= htmlspecialchars($userModel->getRoleName()) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Balance</span>
                            <strong>$<?= number_format($userModel->balance, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Favorite Route</span>
                            <strong><?= htmlspecialchars($userModel->getFavoriteRoute($_SESSION['user_id'])) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Last Recharge</span>
                            <strong><?= $userModel->last_recharge_date ? date('M j, Y', strtotime($userModel->last_recharge_date)) : 'Never' ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card profile-card">
                    <div class="card-header bg-white">
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="pill"
                                    data-bs-target="#profile" type="button" role="tab">
                                    <i class="fas fa-user"></i> Profile
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="pill"
                                    data-bs-target="#password" type="button" role="tab">
                                    <i class="fas fa-lock"></i> Password
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" name="username"
                                            value="<?= htmlspecialchars($userModel->name) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email address</label>
                                        <input type="email" class="form-control" name="email"
                                            value="<?= htmlspecialchars($userModel->email) ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="password" role="tabpanel">
                                <form method="POST">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($userModel->name) ?>">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($userModel->email) ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required 
                                               minlength="8" pattern=".{8,}" 
                                               title="Password must be at least 8 characters long">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>