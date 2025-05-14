<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__, 2)); 
require_once '../../config/DBController.php';
require_once '../../controllers/TicketController.php';

$page_error_message = null;
$page_info_message = null;
$user_tickets_list = [];

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_flash_message'] = "Please login to view your tickets.";
    header("Location: ../auth/login.php"); 
    exit;
}
$loggedInUserId = (int)$_SESSION['user_id'];

try {
    $ticketController = new TicketController();

    if (!$ticketController->isDBConnected()) {
         throw new Exception("Ticket service is temporarily unavailable. Please try again later. (DB_CONN_FAIL)");
    }

    $user_tickets_list = $ticketController->listTickets($loggedInUserId); 

    if (empty($user_tickets_list) && !$page_error_message) { 
        $page_info_message = "You have not booked any tickets yet. ";
    }

} catch (Exception $e) {
    error_log("Error on views/ticket/index.php (List View): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
    $page_error_message = "An error occurred while fetching your tickets: " . $e->getMessage();
    $user_tickets_list = [];
}

function format_ticket_list_time($timeString) {
    if (empty($timeString) || $timeString === '00:00:00') return 'N/A';
    try {
        $dateTime = DateTime::createFromFormat('H:i:s', $timeString);
        if ($dateTime === false) {
            $testDate = new DateTime($timeString);
            return htmlspecialchars($testDate->format('g:i A'));
        }
        return htmlspecialchars($dateTime->format('g:i A'));
    } catch (Exception $ex) {
        return htmlspecialchars($timeString); 
    }
}

if (isset($_SESSION['success_flash_message']) && empty($page_info_message) && empty($page_error_message)) {
    $page_info_message = $_SESSION['success_flash_message'];
    unset($_SESSION['success_flash_message']);
}
if (isset($_SESSION['error_flash_message']) && empty($page_error_message) ) {
    $page_error_message = $_SESSION['error_flash_message'];
    unset($_SESSION['error_flash_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - GoTrain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= ROOT_PATH ?>/public/css/dashboard.css" rel="stylesheet"> 
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .ticket-page-container { padding-top: 2rem; padding-bottom: 2rem; }
        .dashboard-card { border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); background-color: #fff; }
        .ticket-list-table th { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #6c757d; background-color: #f8f9fa; border-bottom-width: 2px; }
        .ticket-list-table td { vertical-align: middle; font-size: 0.9rem; border-top: 1px solid #dee2e6; }
        .ticket-list-table .badge { font-size: 0.8rem; padding: 0.4em 0.6em; }
        .btn-outline-primary { color: #0d6efd; border-color: #0d6efd; }
        .btn-outline-primary:hover { background-color: #0d6efd; color: white; }
        .action-buttons a { margin-right: 5px; }
        .action-buttons a:last-child { margin-right: 0; }
        .page-header { border-bottom: 1px solid #dee2e6; padding-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container ticket-page-container">
        <div class="d-flex justify-content-between align-items-center mb-4 page-header">
            <h2 class="mb-0"><i class="bi bi-ticket-detailed me-2"></i>My Tickets</h2>
            <a href="../Dashboard/default.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($page_error_message): ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error</h4>
                <p><?= htmlspecialchars($page_error_message) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($page_info_message): ?>
             <div class="alert alert-info" role="alert">
                 <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($page_info_message) ?>
                 <?php if (strpos($page_info_message, "You have not booked any tickets yet") !== false): ?>
                    <a href="../User/book_ticket.php" class="btn btn-primary btn-sm mt-2 ms-2">Book a New Ticket</a>
                 <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($user_tickets_list)): ?>
        <div class="card dashboard-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover ticket-list-table mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Route</th>
                                <th>Journey Date</th>
                                <th>Departure Time</th>
                                <th>Fare (EGP)</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_tickets_list as $t): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($t['booking_id'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($t['source_station'] ?? 'N/A') ?> <i class="bi bi-arrow-right-short"></i> <?= htmlspecialchars($t['destination_station'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php 
                                        echo !empty($t['booking_date']) ? htmlspecialchars(date('M j, Y', strtotime($t['booking_date']))) : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        echo format_ticket_list_time($t['departure_time'] ?? '');
                                        ?>
                                    </td>
                                    <td><?= isset($t['fare']) ? htmlspecialchars(number_format((float)$t['fare'], 2)) : 'N/A' ?></td>
                                    <td>
                                        <?php
                                            $status_val_list = $t['status'] ?? 'Unknown';
                                            $status_badge_class_list = 'secondary'; 
                                            $status_l = strtolower($status_val_list);
                                            if (str_contains($status_l, 'confirmed') || str_contains($status_l, 'upcoming')) $status_badge_class_list = 'success';
                                            elseif (str_contains($status_l, 'cancelled')) $status_badge_class_list = 'danger';
                                            elseif (str_contains($status_l, 'completed') || str_contains($status_l, 'arrived')) $status_badge_class_list = 'primary';
                                        ?>
                                        <span class="badge bg-<?= $status_badge_class_list ?>"><?= htmlspecialchars($status_val_list) ?></span>
                                    </td>
                                    <td class="text-center action-buttons">
                                        <a href="receipt.php?booking_id=<?= urlencode($t['booking_id'] ?? '') ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Ticket">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (!$page_info_message && !$page_error_message): ?>
            <div class="alert alert-secondary text-center">
                <p class="mb-2">You currently have no tickets in your list.</p>
                <a href="../User/book_ticket.php" class="btn btn-primary">Book Your First Ticket</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>