<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$admin_user_id_processing = $_SESSION['user_id'];

require_once __DIR__ . '/../../config/DBController.php';
require_once __DIR__ . '/../../models/CompensationRequest.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Transactions.php';

$all_compensation_requests = [];
$page_level_message = $_SESSION['page_message'] ?? '';
$page_level_message_type = $_SESSION['page_message_type'] ?? '';
unset($_SESSION['page_message'], $_SESSION['page_message_type']);

$filter_by_status = $_GET['status'] ?? '';
$view_specific_request_id = $_GET['view_id'] ?? null;
$request_details_to_view = null;

try {
    $dbController = DBController::getInstance();
    $pdo_connection = $dbController->getConnection();

    if (!$pdo_connection) {
        throw new Exception("Database connection service is unavailable at the moment.");
    }

    $compensationRequestModel = new CompensationRequest($pdo_connection);
    $userModel = new User($pdo_connection);
    $transactionModel = new Transaction($pdo_connection);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_request_submit'])) {
        $request_id_to_process = $_POST['request_id_to_process'] ?? null;
        $new_status_for_request = $_POST['new_status'] ?? null;
        $admin_notes_for_request = trim($_POST['admin_notes'] ?? '');
        $compensation_amount_input = $_POST['compensation_amount'] ?? null;
        $parsed_compensation_amount = null;

        if ($new_status_for_request === 'APPROVED' || $new_status_for_request === 'PROCESSED') {
            if ($compensation_amount_input !== null && $compensation_amount_input !== '') {
                $parsed_compensation_amount = filter_var($compensation_amount_input, FILTER_VALIDATE_FLOAT);
                if ($parsed_compensation_amount === false || $parsed_compensation_amount < 0) {
                    $_SESSION['page_message'] = "Invalid compensation amount provided. Must be a non-negative number.";
                    $_SESSION['page_message_type'] = 'error';
                    header("Location: compensation_requests_admin.php?view_id=" . $request_id_to_process);
                    exit();
                }
            } elseif ($new_status_for_request === 'PROCESSED' && ($compensation_amount_input === null || $compensation_amount_input === '' || (float)$compensation_amount_input <= 0)) {
                $_SESSION['page_message'] = "To mark as 'Processed', a positive compensation amount is required.";
                $_SESSION['page_message_type'] = 'error';
                header("Location: compensation_requests_admin.php?view_id=" . $request_id_to_process);
                exit();
            }
        }

        if ($request_id_to_process && $new_status_for_request) {
            $pdo_connection->beginTransaction();
            try {
                $data_for_processing = [
                    'request_id' => $request_id_to_process,
                    'status' => $new_status_for_request,
                    'admin_notes' => !empty($admin_notes_for_request) ? $admin_notes_for_request : null,
                    'compensation_amount' => $parsed_compensation_amount,
                    'processed_by_user_id' => $admin_user_id_processing
                ];

                if ($compensationRequestModel->processRequest($data_for_processing)) {
                    if ($new_status_for_request === 'PROCESSED' && $parsed_compensation_amount > 0) {
                        $original_request_details = $compensationRequestModel->getByIdWithDetails($request_id_to_process);
                        if ($original_request_details && isset($original_request_details['user_id'])) {
                            $updateBalanceStmt = $pdo_connection->prepare("UPDATE users SET balance = balance + :amount WHERE user_id = :user_id");
                            if (!$updateBalanceStmt->execute([':amount' => $parsed_compensation_amount, ':user_id' => $original_request_details['user_id']])) {
                                throw new Exception("Critical: Failed to update user's balance after processing compensation.");
                            }

                            $transactionModel->user_id = $original_request_details['user_id'];
                            $transactionModel->amount = $parsed_compensation_amount;
                            $transactionModel->type = 'COMPENSATION_CREDIT';
                            $booking_id_display = $original_request_details['display_booking_id'] ?? null;
                            $transactionModel->description = 'Compensation for request ID ' . $request_id_to_process . ($booking_id_display ? ' (Ref Booking: ' . $booking_id_display . ')' : '');

                            if (!$transactionModel->addTransaction()) {
                                throw new Exception("Critical: Failed to log compensation credit transaction: " . ($transactionModel->getLastError() ?: 'Unknown transaction model error.'));
                            }
                        } else {
                            throw new Exception("Critical: Could not retrieve original request details to credit user for request ID " . $request_id_to_process);
                        }
                    }
                    $_SESSION['page_message'] = "Compensation request (ID: $request_id_to_process) successfully updated to '$new_status_for_request'.";
                    $_SESSION['page_message_type'] = 'success';
                    $pdo_connection->commit();
                } else {
                    throw new Exception("Failed to update request status in database: " . ($compensationRequestModel->getLastError() ?? 'Compensation model did not provide specific error.'));
                }
            } catch (Exception $e) {
                $pdo_connection->rollBack();
                $_SESSION['page_message'] = "Error processing request: " . $e->getMessage();
                $_SESSION['page_message_type'] = 'error';
                error_log("Admin Compensation Processing Error: " . $e->getMessage() . " | Data: " . json_encode($data_for_processing ?? []));
            }
            $redirect_url = "compensation_requests_admin.php" . ($view_specific_request_id ? '?view_id=' . $view_specific_request_id : ($request_id_to_process && $new_status_for_request ? '?view_id=' . $request_id_to_process : ''));
            header("Location: " . $redirect_url);
            exit();
        } else {
            $_SESSION['page_message'] = "Incomplete data received to process the request.";
            $_SESSION['page_message_type'] = 'error';
            header("Location: compensation_requests_admin.php" . ($view_specific_request_id ? '?view_id=' . $view_specific_request_id : ''));
            exit();
        }
    }

    if ($view_specific_request_id) {
        $request_details_to_view = $compensationRequestModel->getByIdWithDetails($view_specific_request_id);
        if (!$request_details_to_view) {
            if (empty($page_message)) {
                $page_message = "Compensation request (ID: " . htmlspecialchars($view_specific_request_id) . ") not found or could not be loaded.";
                $page_message_type = 'warning';
            }
        }
    } else {
        $all_compensation_requests = $compensationRequestModel->getAll($filter_by_status) ?: [];
    }
} catch (Exception $e) {
    error_log("Admin Compensation Page CRITICAL Error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
    if (empty($page_message)) {
        $page_message = "A critical error occurred while loading the page. Please check logs or contact support.";
        $page_message_type = 'error';
    }
    $all_compensation_requests = [];
    $request_details_to_view = null;
}

function get_status_badge_class_for_cra_final($status)
{
    if ($status === null) return 'bg-secondary';
    switch (strtoupper($status)) {
        case 'PENDING':
            return 'bg-warning text-dark';
        case 'APPROVED':
            return 'bg-info text-dark';
        case 'REJECTED':
            return 'bg-danger';
        case 'PROCESSED':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compensation Requests - Admin</title>
    <link href="../public/css/dashboard.css" rel="stylesheet">
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .cra-page-header h2 {
            color: var(--primary-color, #3a86ff);
            font-weight: 600;
        }

        .cra-table th {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--text-light-muted);
        }

        .cra-details-card .card-body {
            font-size: 0.9rem;
        }

        .cra-details-card strong {
            color: var(--text-dark);
        }

        .cra-details-card .badge {
            font-size: 0.85em;
            padding: 0.4em 0.65em;
        }

        .form-processing-actions button {
            margin-top: 0.5rem;
            margin-right: 0.5rem;
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-light-muted);
            font-size: 0.85rem;
        }

        .detail-value {
            color: var(--text-dark);
        }

        .detail-block {
            margin-bottom: 0.8rem;
        }

        .description-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: var(--card-border-radius-sm, 0.35rem);
            padding: 0.75rem;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.875rem;
        }

        .message-area-page {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: var(--card-border-radius-sm, 8px);
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .message-area-page .fas {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .message-success-page {
            background-color: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
            border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        }

        .message-error-page {
            background-color: rgba(var(--bs-danger-rgb), 0.1);
            color: var(--bs-danger);
            border: 1px solid rgba(var(--bs-danger-rgb), 0.2);
        }

        .message-warning-page {
            background-color: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
            border: 1px solid rgba(var(--bs-warning-rgb), 0.2);
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="cra-page-header mb-4 animate__animated animate__fadeInDown">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Manage Compensation Requests</h2>
                    <?php if ($request_details_to_view): ?>
                        <a href="compensation_requests_admin.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list me-1"></i> View All Requests</a>
                    <?php endif; ?>
                </div>
                <p class="text-muted mb-0">Review and process user-submitted compensation claims.</p>
            </div>

            <?php if ($page_level_message): ?>
                <div class="message-area-page <?= $page_level_message_type === 'success' ? 'message-success-page' : ($page_level_message_type === 'warning' ? 'message-warning-page' : 'message-error-page') ?> animate__animated animate__fadeIn" role="alert">
                    <i class="fas <?= $page_level_message_type === 'success' ? 'fa-check-circle' : ($page_level_message_type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle') ?>"></i>
                    <?= htmlspecialchars($page_level_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($request_details_to_view): ?>
                <div class="card dashboard-card cra-details-card mb-4 animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="mb-0">Review Request ID: <?= htmlspecialchars($request_details_to_view['request_id']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="detail-block"><span class="detail-label">User:</span> <span class="detail-value"><?= htmlspecialchars($request_details_to_view['user_username']) ?> (<?= htmlspecialchars($request_details_to_view['user_email']) ?>)</span></div>
                                <div class="detail-block"><span class="detail-label">Related Booking:</span>
                                    <a href="view_all_bookings.php?id=<?= htmlspecialchars($request_details_to_view['display_booking_id']) ?>" target="_blank" class="detail-value">
                                        <?= htmlspecialchars($request_details_to_view['display_booking_id']) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </div>
                                <div class="detail-block"><span class="detail-label">Journey:</span> <span class="detail-value"><?= htmlspecialchars($request_details_to_view['booking_source_station'] ?? 'N/A') ?> <i class="fas fa-long-arrow-alt-right mx-1"></i> <?= htmlspecialchars($request_details_to_view['booking_destination_station'] ?? 'N/A') ?></span></div>
                                <div class="detail-block"><span class="detail-label">Journey Date:</span> <span class="detail-value"><?= isset($request_details_to_view['booking_journey_date']) ? date('M j, Y', strtotime($request_details_to_view['booking_journey_date'])) : 'N/A' ?></span></div>
                                <div class="detail-block"><span class="detail-label">Train:</span> <span class="detail-value"><?= htmlspecialchars($request_details_to_view['booking_train_name'] ?? 'N/A') ?> (<?= htmlspecialchars($request_details_to_view['booking_train_number'] ?? 'N/A') ?>)</span></div>
                                <div class="detail-block"><span class="detail-label">Original Fare:</span> <span class="detail-value">EGP <?= isset($request_details_to_view['booking_original_fare']) ? number_format($request_details_to_view['booking_original_fare'], 2) : 'N/A' ?></span></div>
                                <div class="detail-block"><span class="detail-label">Request Submitted:</span> <span class="detail-value"><?= date('M j, Y, g:i a', strtotime($request_details_to_view['request_date'])) ?></span></div>
                            </div>
                            <div class="col-lg-5">
                                <div class="detail-block"><span class="detail-label">Current Status:</span> <span class="badge <?= get_status_badge_class_for_cra_final($request_details_to_view['status']) ?>"><?= htmlspecialchars(ucfirst(strtolower($request_details_to_view['status']))) ?></span></div>
                                <div class="detail-block"><span class="detail-label">Reason by User:</span> <span class="detail-value"><?= nl2br(htmlspecialchars($request_details_to_view['reason_for_request'])) ?></span></div>
                                <div class="detail-block"><span class="detail-label">User's Description:</span>
                                    <div class="description-box"><?= nl2br(htmlspecialchars($request_details_to_view['detailed_description'] ?? 'Not provided.')) ?></div>
                                </div>
                                <?php if ($request_details_to_view['processed_by_user_id']): ?>
                                    <div class="detail-block mt-2"><span class="detail-label">Processed By:</span> <span class="detail-value"><?= htmlspecialchars($request_details_to_view['processed_by_admin_username'] ?? 'Admin ID: ' . $request_details_to_view['processed_by_user_id']) ?></span></div>
                                    <div class="detail-block"><span class="detail-label">Processed Date:</span> <span class="detail-value"><?= date('M j, Y, g:i a', strtotime($request_details_to_view['processed_date'])) ?></span></div>
                                <?php endif; ?>
                                <?php if ($request_details_to_view['compensation_amount'] !== null): ?>
                                    <div class="detail-block"><span class="detail-label">Compensation Amount:</span> <span class="detail-value fw-bold text-success">EGP <?= number_format($request_details_to_view['compensation_amount'], 2) ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr class="my-3">
                        <?php if (!empty($request_details_to_view['admin_notes'])): ?>
                            <div class="mb-3">
                                <strong class="detail-label d-block mb-1">Admin Notes:</strong>
                                <div class="description-box"><?= nl2br(htmlspecialchars($request_details_to_view['admin_notes'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($request_details_to_view['status'] === 'PENDING' || $request_details_to_view['status'] === 'APPROVED'): ?>
                            <h5 class="mt-4">Process Request</h5>
                            <form method="POST" action="compensation_requests_admin.php?view_id=<?= $request_details_to_view['request_id'] ?>" class="mt-2">
                                <input type="hidden" name="request_id_to_process" value="<?= $request_details_to_view['request_id'] ?>">
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label detail-label">Admin Notes (Public to user if applicable, internal otherwise):</label>
                                    <textarea name="admin_notes" id="admin_notes" class="form-control form-control-sm" rows="3" placeholder="Add notes for this request..."><?= htmlspecialchars($request_details_to_view['admin_notes'] ?? '') ?></textarea>
                                </div>
                                <?php if ($request_details_to_view['status'] === 'PENDING' || ($request_details_to_view['status'] === 'APPROVED' && ($request_details_to_view['compensation_amount'] === null || $request_details_to_view['compensation_amount'] == 0))): ?>
                                    <div class="mb-3">
                                        <label for="compensation_amount" class="form-label detail-label">Set Compensation Amount (EGP):</label>
                                        <input type="number" name="compensation_amount" id="compensation_amount" class="form-control form-control-sm" step="0.01" min="0" value="<?= htmlspecialchars($request_details_to_view['compensation_amount'] ?? '') ?>" placeholder="0.00">
                                    </div>
                                <?php endif; ?>
                                <div class="form-processing-actions">
                                    <?php if ($request_details_to_view['status'] === 'PENDING'): ?>
                                        <button type="submit" name="new_status" value="APPROVED" class="btn btn-info btn-sm"><i class="fas fa-thumbs-up me-1"></i> Approve Request</button>
                                        <button type="submit" name="new_status" value="REJECTED" class="btn btn-danger btn-sm"><i class="fas fa-times-circle me-1"></i> Reject Request</button>
                                    <?php endif; ?>
                                    <?php if ($request_details_to_view['status'] === 'APPROVED' && isset($request_details_to_view['compensation_amount']) && (float)$request_details_to_view['compensation_amount'] > 0): ?>
                                        <button type="submit" name="new_status" value="PROCESSED" class="btn btn-success btn-sm"><i class="fas fa-check-circle me-1"></i> Mark as Processed & Credit User</button>
                                    <?php elseif ($request_details_to_view['status'] === 'APPROVED' && (!isset($request_details_to_view['compensation_amount']) || (float)$request_details_to_view['compensation_amount'] <= 0)): ?>
                                        <p class="text-muted small mt-2 mb-1"><i class="fas fa-info-circle"></i> To mark as processed, please set a positive compensation amount above and save.</p>
                                        <button type="submit" name="new_status" value="APPROVED" class="btn btn-info btn-sm"><i class="fas fa-save me-1"></i> Save Amount & Keep Approved</button>
                                    <?php endif; ?>
                                    <input type="hidden" name="process_request_submit" value="1">
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="card dashboard-card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <form method="GET" action="" class="d-flex justify-content-end align-items-center">
                            <label for="status_filter_select" class="form-label me-2 mb-0 small">Filter by status:</label>
                            <select name="status" id="status_filter_select" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="PENDING" <?= ($filter_by_status == 'PENDING' ? 'selected' : '') ?>>Pending</option>
                                <option value="APPROVED" <?= ($filter_by_status == 'APPROVED' ? 'selected' : '') ?>>Approved</option>
                                <option value="REJECTED" <?= ($filter_by_status == 'REJECTED' ? 'selected' : '') ?>>Rejected</option>
                                <option value="PROCESSED" <?= ($filter_by_status == 'PROCESSED' ? 'selected' : '') ?>>Processed</option>
                            </select>
                            <noscript><button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button></noscript>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm cra-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Booking</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_compensation_requests)): foreach ($all_compensation_requests as $req): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($req['request_id']) ?></td>
                                                <td><?= htmlspecialchars($req['user_username']) ?><br><small class="text-muted"><?= htmlspecialchars($req['user_email']) ?></small></td>
                                                <td>
                                                    <?php if (!empty($req['display_booking_id'])): ?>
                                                        <a href="view_all_bookings.php?booking_ref_id=<?= htmlspecialchars($req['display_booking_id']) ?>" target="_blank" title="View booking details">
                                                            <?= htmlspecialchars($req['display_booking_id']) ?>
                                                        </a>
                                                    <?php else: echo 'N/A';
                                                    endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($req['reason_for_request']) ?></td>
                                                <td><span class="badge <?= get_status_badge_class_for_cra_final($req['status']) ?>"><?= htmlspecialchars(ucfirst(strtolower($req['status']))) ?></span></td>
                                                <td><?= date('M j, Y', strtotime($req['request_date'])) ?></td>
                                                <td>
                                                    <a href="compensation_requests_admin.php?view_id=<?= $req['request_id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2"><i class="fas fa-search-plus me-1"></i> Review</a>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No compensation requests found <?= !empty($filter_by_status) ? "with status '" . htmlspecialchars($filter_by_status) . "'." : "." ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../../public/assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../public/js/dashboard.js"></script>
</body>

</html>