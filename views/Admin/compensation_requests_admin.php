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
                            $transactionModel->transaction_type = 'COMPENSATION_CREDIT';
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

$page_title = "Compensation Requests - Admin";
$additional_css = '<style>
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

    .back-to-dashboard {
        margin-bottom: 1rem;
    }
</style>';

require_once '../includes/header.php';
?>

<div class="main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-hand-holding-usd me-2"></i>
                <?= $view_specific_request_id ? 'Compensation Request Details' : 'Compensation Requests' ?>
            </h2>
            <a href="../Admin/admin.php" class="btn btn-light back-to-dashboard">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($page_level_message): ?>
            <div class="message-area-page message-<?= $page_level_message_type ?>-page">
                <i class="fas <?= $page_level_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($page_level_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($view_specific_request_id && $request_details_to_view): ?>
            <div class="card cra-details-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-block">
                                <div class="detail-label">Request ID</div>
                                <div class="detail-value"><?= htmlspecialchars($request_details_to_view['request_id']) ?></div>
                            </div>
                            <div class="detail-block">
                                <div class="detail-label">User</div>
                                <div class="detail-value"><?= htmlspecialchars($request_details_to_view['user_username']) ?></div>
                            </div>
                            <div class="detail-block">
                                <div class="detail-label">Booking Reference</div>
                                <div class="detail-value"><?= htmlspecialchars($request_details_to_view['display_booking_id'] ?? 'N/A') ?></div>
                            </div>
                            <div class="detail-block">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="badge <?= get_status_badge_class_for_cra_final($request_details_to_view['status']) ?>">
                                        <?= htmlspecialchars($request_details_to_view['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-block">
                                <div class="detail-label">Submitted On</div>
                                <div class="detail-value"><?= date('M j, Y g:i A', strtotime($request_details_to_view['created_at'])) ?></div>
                            </div>
                            <?php if ($request_details_to_view['processed_date']): ?>
                                <div class="detail-block">
                                    <div class="detail-label">Processed On</div>
                                    <div class="detail-value"><?= date('M j, Y g:i A', strtotime($request_details_to_view['processed_date'])) ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($request_details_to_view['compensation_amount']): ?>
                                <div class="detail-block">
                                    <div class="detail-label">Compensation Amount</div>
                                    <div class="detail-value">EGP <?= number_format($request_details_to_view['compensation_amount'], 2) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-block mt-3">
                        <div class="detail-label">Request Description</div>
                        <div class="description-box"><?= nl2br(htmlspecialchars($request_details_to_view['detailed_description'])) ?></div>
                    </div>

                    <?php if ($request_details_to_view['admin_notes']): ?>
                        <div class="detail-block mt-3">
                            <div class="detail-label">Admin Notes</div>
                            <div class="description-box"><?= nl2br(htmlspecialchars($request_details_to_view['admin_notes'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($request_details_to_view['status'] === 'PENDING' || $request_details_to_view['status'] === 'APPROVED'): ?>
                        <form method="POST" class="mt-4 form-processing-actions">
                            <input type="hidden" name="request_id_to_process" value="<?= htmlspecialchars($request_details_to_view['request_id']) ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Update Status</label>
                                        <select name="new_status" class="form-select" required>
                                            <option value="">Select new status...</option>
                                            <?php if ($request_details_to_view['status'] === 'PENDING'): ?>
                                                <option value="APPROVED">Approve Request</option>
                                                <option value="REJECTED">Reject Request</option>
                                            <?php elseif ($request_details_to_view['status'] === 'APPROVED'): ?>
                                                <option value="PROCESSED">Mark as Processed</option>
                                                <option value="REJECTED">Reject Request</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Compensation Amount (EGP)</label>
                                        <input type="number" name="compensation_amount" class="form-control" 
                                               step="0.01" min="0" placeholder="Enter amount if approving/processing">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea name="admin_notes" class="form-control" rows="3" 
                                          placeholder="Add any notes about this decision (optional"><?= htmlspecialchars($request_details_to_view['admin_notes'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" name="process_request_submit" class="btn btn-primary">
                                    Update Request
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="btn-group">
                            <a href="?status=" class="btn btn-outline-secondary <?= $filter_by_status === '' ? 'active' : '' ?>">All</a>
                            <a href="?status=PENDING" class="btn btn-outline-secondary <?= $filter_by_status === 'PENDING' ? 'active' : '' ?>">Pending</a>
                            <a href="?status=APPROVED" class="btn btn-outline-secondary <?= $filter_by_status === 'APPROVED' ? 'active' : '' ?>">Approved</a>
                            <a href="?status=PROCESSED" class="btn btn-outline-secondary <?= $filter_by_status === 'PROCESSED' ? 'active' : '' ?>">Processed</a>
                            <a href="?status=REJECTED" class="btn btn-outline-secondary <?= $filter_by_status === 'REJECTED' ? 'active' : '' ?>">Rejected</a>
                        </div>
                    </div>

                    <?php if (empty($all_compensation_requests)): ?>
                        <div class="alert alert-info">
                            No compensation requests found<?= $filter_by_status ? " with status: $filter_by_status" : '' ?>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover cra-table">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>User</th>
                                        <th>Booking Ref</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_compensation_requests as $request): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($request['request_id']) ?></td>
                                            <td><?= htmlspecialchars($request['user_username']) ?></td>
                                            <td><?= htmlspecialchars($request['display_booking_id'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge <?= get_status_badge_class_for_cra_final($request['status']) ?>">
                                                    <?= htmlspecialchars($request['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($request['created_at'])) ?></td>
                                            <td>
                                                <?= $request['compensation_amount'] ? 'EGP ' . number_format($request['compensation_amount'], 2) : '-' ?>
                                            </td>
                                            <td>
                                                <a href="?view_id=<?= urlencode($request['request_id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>