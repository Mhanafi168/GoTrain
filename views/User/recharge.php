<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/DBController.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Transactions.php';

$message = '';
$message_type = '';
$current_balance_display = 0.00;
$user_name_display = 'User';
$userId_from_session = null;
$userModelInstance = null;
$pdo_connection = null;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$userId_from_session = $_SESSION['user_id'];

try {
    $dbController = DBController::getInstance();
    $pdo_connection = $dbController->getConnection();

    if (!$pdo_connection) {
        throw new Exception("Database connection failed. " . ($dbController->getLastError() ?: "DBController error."));
    }

    $userModelInstance = new User($pdo_connection);

    if ($userModelInstance->getUserById($userId_from_session)) {
        $current_balance_display = (float)$userModelInstance->balance;
        $user_name_display = htmlspecialchars($userModelInstance->name ?? 'User');
    } else {
        throw new Exception("Could not retrieve your user details. Error: " . ($userModelInstance->getLastError() ?: 'User not found.'));
    }
} catch (Exception $e) {
    error_log("Recharge Page Error (Setup): " . $e->getMessage());
    $message = "An error occurred loading your information: " . $e->getMessage();
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_recharge_user'])) {
    if ($userModelInstance === null || $userModelInstance->id === null || $pdo_connection === null) {
        $message = "Cannot process recharge. User data missing or DB connection failed. Please refresh.";
        $message_type = 'error';
    } else {
        $rechargeAmount = filter_input(INPUT_POST, 'recharge_amount', FILTER_VALIDATE_FLOAT);

        if ($rechargeAmount === false || $rechargeAmount <= 0) {
            $message = "Invalid recharge amount. Please enter a positive number (e.g., 10.00).";
            $message_type = 'error';
        } else {
            $pdo_connection->beginTransaction();
            try {
                $dataForUserUpdate = [
                    'transaction' => ['amount' => $rechargeAmount]
                ];

                if ($userModelInstance->update($dataForUserUpdate)) {
                    $current_balance_display = (float)$userModelInstance->balance;

                    $transactionModel = new Transaction($pdo_connection);
                    $transactionModel->user_id = $userId_from_session;
                    $transactionModel->amount = $rechargeAmount;
                    $transactionModel->transaction_type = 'ACCOUNT_RECHARGE';
                    $transactionModel->description = 'Wallet recharge';

                    if (!$transactionModel->addTransaction()) {
                        $pdo_connection->rollBack();
                        $error_detail = $transactionModel->getLastError() ?? 'Unknown transaction logging error.';
                        error_log("CRITICAL: UserID {$userId_from_session} balance updated, but FAILED to log transaction: {$error_detail}");
                        $message = "Recharge processed, but failed to record it. Please contact support. (Ref: User {$userId_from_session})";
                        $message_type = 'error';
                    } else {
                        $pdo_connection->commit();
                        $message = "Recharge successful! Your new balance is EGP " . number_format($current_balance_display, 2);
                        $message_type = 'success';
                    }
                } else {
                    $pdo_connection->rollBack();
                    $error_detail = $userModelInstance->getLastError() ?? 'Unknown error during balance update.';
                    $message = "Recharge failed: {$error_detail}";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                if ($pdo_connection->inTransaction()) {
                    $pdo_connection->rollBack();
                }
                error_log("Recharge Page POST Exception: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                $message = "An error occurred during recharge: " . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge Account - GoTrain</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3253a8;
            --secondary-color: #6c757d;
            --primary-accent: #6a11cb;
            --secondary-accent: #2575fc;
            --gradient-main: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
            --text-dark: #212529;
            --text-light-muted: #6c757d;
            --text-on-dark: #ffffff;
            --bg-light: #f4f7f6;
            --bg-white: #ffffff;
            --border-color: #dee2e6;
            --card-border-radius: 0.75rem;
            --border-radius-sm: 0.3rem;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.06);
            --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
            --success-bg: #d1e7dd;
            --success-text: #0a3622;
            --success-border: #a3cfbb;
            --error-bg: #f8d7da;
            --error-text: #58151c;
            --error-border: #f1aeb5;
            --font-primary: 'Poppins', sans-serif;
            --font-secondary: 'Roboto', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--bg-light);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            min-height: 100vh;
            box-sizing: border-box;
            line-height: 1.6;
        }

        .recharge-wrapper {
            width: 100%;
            max-width: 500px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .recharge-container {
            background-color: var(--bg-white);
            border-radius: var(--card-border-radius);
            box-shadow: var(--card-shadow);
            text-align: center;
            overflow: hidden;
        }

        .page-header-recharge {
            padding: 2rem 1.5rem;
            background: var(--gradient-main);
            color: var(--text-on-dark);
            margin-bottom: 0;
            border-bottom: 5px solid var(--secondary-accent);
        }

        .page-header-recharge h1 {
            font-family: var(--font-secondary);
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
        }

        .page-header-recharge .fas,
        .page-header-recharge .bi {
            margin-right: 0.75rem;
            font-size: 1.6rem;
            vertical-align: -0.1em;
        }

        .recharge-content {
            padding: 2rem 2.5rem;
        }

        .balance-info {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: var(--text-light-muted);
        }

        .balance-info .user-name {
            font-weight: 600;
            color: var(--primary-accent);
        }

        .balance-info strong {
            font-size: 2.4rem;
            color: var(--primary-accent);
            font-weight: 700;
            display: block;
            margin-top: 0.25rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            display: block;
            text-align: left;
            font-size: 0.9rem;
        }

        .input-group-recharge .form-control {
            padding-left: 2.75rem;
            font-size: 1rem;
        }

        .input-group-recharge .input-group-icon {
            position: absolute;
            left: 1px;
            top: 1px;
            bottom: 1px;
            display: flex;
            align-items: center;
            padding: 0 0.9rem;
            color: var(--text-light-muted);
            font-size: 1.1rem;
            z-index: 4;
            border-right: 1px solid var(--border-color);
            background-color: var(--bg-light);
            border-top-left-radius: var(--border-radius-sm);
            border-bottom-left-radius: var(--border-radius-sm);
        }

        .form-control {
            border-radius: var(--border-radius-sm);
            padding: 0.65rem 1rem;
            border: 1px solid var(--border-color);
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.2);
        }

        .btn-recharge-submit {
            background: var(--gradient-main);
            border: none;
            color: var(--text-on-dark);
            padding: 0.75rem 1.5rem;
            font-size: 1.05rem;
            font-weight: 500;
            border-radius: var(--border-radius-sm);
            width: 100%;
            margin-top: 1.5rem;
            transition: all 0.25s ease-out;
            box-shadow: var(--shadow-sm);
            letter-spacing: 0.5px;
        }

        .btn-recharge-submit:hover,
        .btn-recharge-submit:focus {
            opacity: 0.85;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-accent-rgb, 106, 17, 203), 0.3);
        }

        .btn-recharge-submit .fas,
        .btn-recharge-submit .bi {
            margin-right: 0.5rem;
        }

        .message-area {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .message-area .fas,
        .message-area .bi {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .message-success {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }

        .message-error {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
        }

        .back-link-container {
            margin-top: 2rem;
            padding-bottom: 1rem;
        }

        .back-link.btn-outline-secondary {
            color: var(--text-light-muted);
            border-color: #adb5bd;
            font-weight: 500;
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
        }

        .back-link.btn-outline-secondary:hover {
            background-color: var(--secondary-color, #6c757d);
            color: var(--text-on-dark, white);
            border-color: var(--secondary-color, #6c757d);
        }
    </style>
</head>

<body class="recharge-page-body">

    <div class="recharge-wrapper">
        <div class="recharge-container">
            <div class="page-header-recharge">
                <h1><i class="fas fa-wallet"></i> Recharge Your Wallet</h1>
            </div>

            <div class="recharge-content">
                <p class="balance-info">
                    Hello, <span class="user-name"><?= $user_name_display ?></span>!
                    <br>Current Balance:
                    <strong>EGP <?= number_format($current_balance_display, 2) ?></strong>
                </p>

                <?php if ($message): ?>
                    <div class="message-area <?= $message_type === 'success' ? 'message-success' : 'message-error' ?>">
                        <i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($message_type !== 'success'): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="recharge_amount" class="form-label">Amount to Add (EGP)</label>
                            <div class="position-relative input-group-recharge">
                                <span class="input-group-icon"><i class="fas fa-coins"></i></span>
                                <input type="number" class="form-control" id="recharge_amount" name="recharge_amount"
                                    min="10" step="0.01" placeholder="e.g., 100.00" required>
                            </div>
                            <small class="form-text text-muted d-block text-start mt-1">Minimum recharge amount is EGP 10.00.</small>
                        </div>
                        <button type="submit" name="submit_recharge_user" class="btn btn-recharge-submit">
                            <i class="fas fa-credit-card"></i> Add Funds to Wallet
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="back-link-container text-center">
            <a href="../Dashboard/default.php" class="back-link btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Go Back to Dashboard
            </a>
        </div>
    </div>

    <script src="../../public/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>