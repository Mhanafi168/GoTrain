<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__, 3));
require_once '../../config/DBController.php';
require_once '../../models/User.php';
require_once '../../models/Transactions.php';

$transactions_list = [];
$user_balance_from_db = 0.00;
$user_name_from_db = 'User';
$page_message = '';
$page_message_type = 'info';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_flash_message'] = "Please login to view your account history.";
    header("Location: ../auth/login.php");
    exit();
}
$userId = (int)$_SESSION['user_id'];

$dbController = null;
$userModel = null;
$transactionModel = null;

try {
    $dbController = DBController::getInstance();

    $userModel = new User($dbController);
    if ($userModel->getUserById($userId)) {
        $user_balance_from_db = (float)$userModel->balance;
        $user_name_from_db = htmlspecialchars($userModel->name ?? 'User');
    } else {
        throw new Exception("Could not retrieve your user details. Error: " . htmlspecialchars($userModel->getLastError() ?: 'User not found.'));
    }

    $transactionModel = new Transaction($dbController);

    $filter_type = trim($_GET['type'] ?? '');
    $filter_month_str = trim($_GET['month'] ?? '');

    $all_user_transactions = $transactionModel->getRecentTransactions($userId, 200);

    if ($all_user_transactions === false) {
        throw new Exception("Could not fetch your transaction history: " . htmlspecialchars($transactionModel->getLastError()));
    }

    $transactions_list = $all_user_transactions;

    if (!empty($filter_type)) {
        $transactions_list = array_filter($transactions_list, function ($t) use ($filter_type) {
            return isset($t['transaction_type']) && strtoupper($t['transaction_type']) === strtoupper($filter_type);
        });
    }
    if (!empty($filter_month_str)) {
        $transactions_list = array_filter($transactions_list, function ($t) use ($filter_month_str) {
            return isset($t['created_at']) && strpos(date('Y-m', strtotime($t['created_at'])), $filter_month_str) === 0;
        });
    }
    $transactions_list = array_values($transactions_list);
} catch (InvalidArgumentException $e) {
    error_log("Account History Page Model Init Error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
    $page_message = "System error: Could not initialize services. " . htmlspecialchars($e->getMessage());
    $page_message_type = 'error';
} catch (Exception $e) {
    error_log("Account History Page Error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
    $page_message = "An error occurred while loading your account history: " . htmlspecialchars($e->getMessage());
    $page_message_type = 'error';
    $transactions_list = [];
}

if (isset($_SESSION['success_flash_message']) && empty($page_message)) {
    $page_message = $_SESSION['success_flash_message'];
    $page_message_type = 'success';
    unset($_SESSION['success_flash_message']);
}
if (isset($_SESSION['error_flash_message']) && empty($page_message)) {
    $page_message = $_SESSION['error_flash_message'];
    $page_message_type = 'error';
    unset($_SESSION['error_flash_message']);
}


$sidebar_animation_delay_counter = 0;
function get_sidebar_animation_class_local_ah()
{
    global $sidebar_animation_delay_counter;
    $sidebar_animation_delay_counter++;
    return "animate__delay-{$sidebar_animation_delay_counter}s";
}

function format_transaction_for_display_ah($type)
{
    $type_upper = strtoupper(trim($type ?? ''));
    switch ($type_upper) {
        case 'BOOKING_PAYMENT':
            return ['text' => 'Ticket Purchase', 'icon' => 'fas fa-receipt', 'color_class' => 'text-danger', 'bg_class' => 'bg-danger-light'];
        case 'ACCOUNT_RECHARGE':
            return ['text' => 'Wallet Top-up', 'icon' => 'fas fa-charging-station', 'color_class' => 'text-success', 'bg_class' => 'bg-success-light'];
        case 'BOOKING_REFUND':
            return ['text' => 'Ticket Refund', 'icon' => 'fas fa-undo-alt', 'color_class' => 'text-primary', 'bg_class' => 'bg-primary-light'];
        case 'COMPENSATION_CREDIT':
            return ['text' => 'Service Credit', 'icon' => 'fas fa-award', 'color_class' => 'text-info', 'bg_class' => 'bg-info-light'];
        default:
            return ['text' => ucwords(strtolower(str_replace('_', ' ', $type_upper))), 'icon' => 'fas fa-exchange-alt', 'color_class' => 'text-muted', 'bg_class' => 'bg-secondary-light'];
    }
}

$recent_transaction_to_highlight_id = null;
if (isset($_GET['highlight_tx_id']) && is_numeric($_GET['highlight_tx_id'])) {
    $recent_transaction_to_highlight_id = (int)$_GET['highlight_tx_id'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account History - GoTrain</title>
    <link href="../public/css/dashboard.css" rel="stylesheet">
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .bg-danger-light {
            background-color: rgba(var(--bs-danger-rgb), 0.1) !important;
        }

        .bg-success-light {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
        }

        .bg-primary-light {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .bg-info-light {
            background-color: rgba(var(--bs-info-rgb), 0.1) !important;
        }

        .bg-secondary-light {
            background-color: rgba(var(--bs-secondary-rgb), 0.1) !important;
        }

        .ah-page-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1.5rem;
        }

        .ah-page-header h2 {
            color: #0056b3;
            font-weight: 600;
        }

        .ah-balance-card {
            background: linear-gradient(135deg, #0056b3, #003d80);
            color: #fff;
            border-radius: 0.75rem;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .ah-balance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='52' height='26' viewBox='0 0 52 26' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.07'%3E%3Cpath d='M10 10c0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6h2c0 2.21 1.79 4 4 4s4-1.79 4-4h2c0 3.314-2.686 6-6 6zm25.464-1.95l8.486 8.486-1.414 1.414-8.486-8.486 1.414-1.414z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .ah-balance-card .balance-label {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }

        .ah-balance-card .balance-amount {
            font-size: 2.4rem;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .ah-balance-card .balance-icon {
            font-size: 3rem;
            opacity: 0.6;
        }

        .ah-filters-bar {
            margin-bottom: 1.5rem;
        }

        .ah-filters-bar .form-select,
        .ah-filters-bar .form-control {
            font-size: 0.9rem;
            max-width: 220px;
        }

        .ah-transaction-list .list-group-item {
            border-radius: 0.75rem !important;
            margin-bottom: 0.75rem;
            padding: 1rem 1.25rem;
            border-width: 1px;
            border-left-width: 5px !important;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .ah-transaction-list .list-group-item:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .transaction-item-BOOKING_PAYMENT {
            border-left-color: var(--bs-danger) !important;
        }

        .transaction-item-ACCOUNT_RECHARGE {
            border-left-color: var(--bs-success) !important;
        }

        .transaction-item-BOOKING_REFUND {
            border-left-color: var(--bs-primary) !important;
        }

        .transaction-item-COMPENSATION_CREDIT {
            border-left-color: var(--bs-info) !important;
        }

        .transaction-item-DEFAULT {
            border-left-color: var(--bs-secondary) !important;
        }

        .ah-transaction-icon {
            font-size: 1.5rem;
            padding: 0.6rem;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .ah-transaction-details .transaction-type-text {
            font-weight: 600;
            font-size: 1.05rem;
            color: #212529;
        }

        .ah-transaction-details .transaction-description-text {
            font-size: 0.85rem;
            color: #6c757d;
            display: block;
            margin-top: 0.1rem;
            word-break: break-word;
        }

        .ah-transaction-amount {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .ah-transaction-date {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .ah-view-details-btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
        }

        .message-area-local {
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 0.75rem;
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
        }

        .message-area-local .fas {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .message-success-local {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .message-error-local {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .highlight-transaction {
            background-color: rgba(25, 135, 84, 0.15) !important;
            border-left-color: var(--bs-success) !important;
            transform: scale(1.02);
            transition: background-color 0.5s ease-out, transform 0.3s ease-out;
        }
    </style>
</head>

<body>

    <div class="main-content">
        <div class="ah-page-header animate__animated animate__fadeInDown">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Account Statement</h2>
                <a href="../User/recharge.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus-circle me-1"></i>Add Funds
                </a>
            </div>
            <p class="text-muted mb-0">A summary of all your financial activities, <?= htmlspecialchars($user_name_from_db) ?>.</p>
        </div>

        <?php if ($page_message && $page_message_type === 'error'): ?>
            <div class="message-area-local message-error-local animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($page_message) ?>
            </div>
        <?php elseif ($page_message): ?>
            <div class="message-area-local message-success-local animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($page_message) ?>
            </div>
        <?php endif; ?>

        <div class="ah-balance-card animate__animated animate__fadeInUp animate__delay-1s">
            <div class="row align-items-center">
                <div class="col">
                    <div class="balance-label">Current Wallet Balance</div>
                    <div class="balance-amount">EGP <?= number_format($user_balance_from_db, 2) ?></div>
                </div>
                <div class="col-auto text-end">
                    <i class="fas fa-wallet balance-icon"></i>
                </div>
            </div>
        </div>

        <div class="card transactions-card animate__animated animate__fadeInUp animate__delay-2s">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <h5 class="mb-2 mb-md-0">Recent Transactions</h5>
                    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-flex ah-filters-bar">
                        <select name="type" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="ACCOUNT_RECHARGE" <?= ($filter_type == 'ACCOUNT_RECHARGE' ? 'selected' : '') ?>>Recharges</option>
                            <option value="BOOKING_PAYMENT" <?= ($filter_type == 'BOOKING_PAYMENT' ? 'selected' : '') ?>>Payments</option>
                            <option value="BOOKING_REFUND" <?= ($filter_type == 'BOOKING_REFUND' ? 'selected' : '') ?>>Refunds</option>
                            <option value="COMPENSATION_CREDIT" <?= ($filter_type == 'COMPENSATION_CREDIT' ? 'selected' : '') ?>>Credits</option>
                        </select>
                        <input type="month" name="month" class="form-control form-control-sm me-2" value="<?= htmlspecialchars($filter_month_str) ?>" onchange="this.form.submit()">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 print-hide" onclick="window.print();" title="Print History">
                            <i class="fas fa-print"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="list-group list-group-flush ah-transaction-list px-3 py-3">
                <?php if (!empty($transactions_list)): ?>
                    <?php foreach ($transactions_list as $index => $transaction): ?>
                        <?php
                        $type_key = strtoupper($transaction['transaction_type'] ?? 'UNKNOWN');
                        $type_info = format_transaction_for_display_ah($type_key);
                        $transaction_item_class_base = 'transaction-item-' . ($type_info['color_class'] !== 'text-muted' ? $type_key : 'DEFAULT');
                        $transaction_id_for_highlight = $transaction['transaction_id'] ?? null;
                        ?>
                        <div class="list-group-item <?= $transaction_item_class_base ?> animate__fadeIn"
                            data-transaction-id="<?= htmlspecialchars($transaction_id_for_highlight) ?>"
                            style="animation-delay: <?= ($index * 0.05) ?>s;">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="ah-transaction-icon <?= $type_info['color_class'] ?> <?= $type_info['bg_class'] ?>">
                                        <i class="<?= $type_info['icon'] ?>"></i>
                                    </div>
                                </div>
                                <div class="col ah-transaction-details">
                                    <div class="transaction-type-text"><?= $type_info['text'] ?></div>
                                    <small class="transaction-description-text"><?= htmlspecialchars($transaction['description'] ?? 'No description') ?></small>
                                </div>
                                <div class="col-md-3 text-md-end mt-2 mt-md-0">
                                    <div class="ah-transaction-amount <?= (isset($transaction['amount']) && (float)$transaction['amount'] > 0) ? 'text-success' : 'text-danger' ?>">
                                        <?= (isset($transaction['amount']) && (float)$transaction['amount'] > 0 ? '+' : '') . number_format(abs((float)($transaction['amount'] ?? 0)), 2) ?> EGP
                                    </div>
                                    <div class="ah-transaction-date">
                                        <?= isset($transaction['created_at']) ? date('M d, Y \a\t h:i A', strtotime($transaction['created_at'])) : 'N/A' ?>
                                    </div>
                                </div>
                                <div class="col-auto d-none d-md-block ms-2">
                                    <?php
                                    $booking_pk_id_from_txn = $transaction['booking_id'] ?? null;
                                    $bookingDisplayId = null;
                                    if (($type_key === 'BOOKING_PAYMENT' || $type_key === 'BOOKING_REFUND') && isset($transaction['description'])) {
                                        if (preg_match('/Booking ID: ([A-Za-z0-9\-]+)/i', $transaction['description'], $matches)) {
                                            $bookingDisplayId = trim($matches[1]);
                                        }
                                    }

                                    if ($bookingDisplayId):
                                    ?>
                                        <a href="../ticket/receipt.php?booking_id=<?= urlencode($bookingDisplayId) ?>"
                                            class="btn btn-sm btn-outline-primary ah-view-details-btn" title="View Related Ticket">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No transactions found.</h5>
                        <p class="text-muted">
                            <?php if (!empty($filter_type) || !empty($filter_month_str)): ?>
                                Try adjusting your filters or <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">view all transactions</a>.
                            <?php else: ?>
                                Your transaction history is currently empty.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($transactions_list) >= 10): ?>
                <div class="card-footer text-center bg-light py-2">
                    <small class="text-muted">Showing up to 200 recent transactions. For older records, please use date filters or contact support.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="<?= ROOT_PATH ?>/public/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const highlightTxId = urlParams.get('highlight_tx_id');

            if (highlightTxId) {
                const highlightedTransactionElement = document.querySelector(`.list-group-item[data-transaction-id="${highlightTxId}"]`);
                if (highlightedTransactionElement) {
                    highlightedTransactionElement.classList.add('highlight-transaction', 'animate__animated', 'animate__pulse');
                    highlightedTransactionElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    setTimeout(() => {
                        highlightedTransactionElement.classList.remove('highlight-transaction', 'animate__pulse');
                    }, 3500);
                }
            }

            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const mainContent = document.querySelector('.main-content');

            if (sidebarToggle && sidebar && mainContent) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    mainContent.classList.toggle('active');
                });
            }
        });
    </script>
</body>

</html>