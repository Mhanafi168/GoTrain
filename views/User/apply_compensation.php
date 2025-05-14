<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/DBController.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/CompensationRequest.php';

$user_name_from_db = 'User';
$user_bookings_for_form = [];
$page_message = '';
$page_message_type = '';
$form_data_persisted = [
    'booking_id_pk' => $_POST['booking_id_pk'] ?? '',
    'reason_for_request' => $_POST['reason_for_request'] ?? '',
    'detailed_description' => $_POST['detailed_description'] ?? ''
];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$userId = $_SESSION['user_id'];

try {
    $dbController = DBController::getInstance();
    $pdo_connection = $dbController->getConnection();

    $userModel = new User($pdo_connection);
    if ($userModel->getUserById($userId)) {
        $user_name_from_db = htmlspecialchars($userModel->name);
    }

    $bookingModel = new Booking($pdo_connection);
    $stmt = $pdo_connection->prepare("SELECT id, booking_id, source_station, destination_station, booking_date FROM bookings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($bookings) {
        foreach ($bookings as $booking) {
            $user_bookings_for_form[] = [
                'pk_id' => $booking['id'],
                'display_id' => $booking['booking_id'],
                'route' => $booking['source_station'] . ' to ' . $booking['destination_station'],
                'journey_date' => date('M j, Y', strtotime($booking['booking_date']))
            ];
        }
    }
} catch (Exception $e) {
    error_log("Apply Compensation Page Error: " . $e->getMessage());
    $page_message = "An error occurred while loading your information.";
    $page_message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_compensation'])) {
    if (empty($form_data_persisted['booking_id_pk'])) {
        $page_message = "Please select a booking related to your compensation request.";
        $page_message_type = 'error';
    } elseif (empty($form_data_persisted['reason_for_request'])) {
        $page_message = "Please provide a reason for your compensation request.";
        $page_message_type = 'error';
    } elseif (empty($form_data_persisted['detailed_description']) || strlen($form_data_persisted['detailed_description']) < 20) {
        $page_message = "The detailed description must be at least 20 characters long.";
        $page_message_type = 'error';
    } else {
        try {
            $valid_booking = false;
            foreach ($user_bookings_for_form as $booking) {
                if ($booking['pk_id'] == $form_data_persisted['booking_id_pk']) {
                    $valid_booking = true;
                    break;
                }
            }

            if (!$valid_booking) {
                throw new Exception("Invalid booking selected");
            }

            $compensationRequestModel = new CompensationRequest($pdo_connection);
            $requestData = [
                'user_id' => $userId,
                'booking_id' => $form_data_persisted['booking_id_pk'],
                'reason_for_request' => $form_data_persisted['reason_for_request'],
                'detailed_description' => $form_data_persisted['detailed_description'],
                'status' => 'PENDING'
            ];

            $request_id = $compensationRequestModel->create($requestData);

            if ($request_id) {
                $page_message = "Your compensation request has been submitted successfully!";
                $page_message_type = 'success';
                $form_data_persisted = [
                    'booking_id_pk' => '',
                    'reason_for_request' => '',
                    'detailed_description' => ''
                ];
            } else {
                throw new Exception("Database error: " . $compensationRequestModel->getLastError());
            }
        } catch (Exception $e) {
            error_log("Compensation Submission Error: " . $e->getMessage());
            $page_message = "Could not submit your request: " . $e->getMessage();
            $page_message_type = 'error';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Compensation - GoTrain</title>
    <link href="../public/css/dashboard.css" rel="stylesheet">
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .ac-page-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color, #dee2e6);
            margin-bottom: 2rem;
        }

        .ac-page-header h2 {
            color: var(--primary-color, #3253a8);
            font-weight: 600;
        }

        .ac-form-card .card-body {
            padding: 1.5rem 2rem;
        }

        .ac-form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .ac-form-card .form-control,
        .ac-form-card .form-select {
            margin-bottom: 1.25rem;
        }

        .ac-form-card textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .ac-info-box {
            background-color: rgba(var(--bs-info-rgb), 0.07);
            border-left: 4px solid var(--bs-info);
            padding: 1rem 1.25rem;
            border-radius: var(--card-border-radius-sm, 0.35rem);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .ac-info-box p {
            margin-bottom: 0.5rem;
        }

        .ac-info-box ul {
            padding-left: 1.2rem;
            margin-bottom: 0;
        }

        .ac-info-box ul li {
            margin-bottom: 0.25rem;
        }

        .message-area-ac {
            margin-bottom: 1.5rem;
            padding: 0.9rem 1.25rem;
            border-radius: var(--card-border-radius-sm, 0.35rem);
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
        }

        .message-area-ac .fas {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .message-success-ac {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .message-error-ac {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="ac-page-header animate__animated animate__fadeInDown">
            <h2>Apply for Compensation</h2>
            <p class="text-muted">Request compensation for eligible travel disruptions, <?= $user_name_from_db ?>.</p>
        </div>

        <?php if ($page_message): ?>
            <div class="message-area-ac <?= $page_message_type === 'success' ? 'message-success-ac' : 'message-error-ac' ?> animate__animated animate__fadeIn">
                <i class="fas <?= $page_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($page_message) ?>
            </div>
        <?php endif; ?>

        <div class="card ac-form-card animate__animated animate__fadeInUp animate__delay-1s">
            <div class="card-body">
                <div class="ac-info-box">
                    <p class="mb-1"><i class="fas fa-info-circle me-2"></i>Before submitting, please review our compensation guidelines.</p>
                    <ul>
                        <li>Significant train delays (e.g., over 60 minutes)</li>
                        <li>Train cancellations without adequate alternative travel</li>
                        <li>Substantial service quality issues</li>
                    </ul>
                </div>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="booking_id_pk" class="form-label ac-form-label">Related Booking <span class="text-danger">*</span></label>
                        <select class="form-select" id="booking_id_pk" name="booking_id_pk" required>
                            <option value="">Select a booking...</option>
                            <?php if (!empty($user_bookings_for_form)): ?>
                                <?php foreach ($user_bookings_for_form as $booking_opt): ?>
                                    <option value="<?= htmlspecialchars($booking_opt['pk_id']) ?>" <?= ($form_data_persisted['booking_id_pk'] == $booking_opt['pk_id'] ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($booking_opt['display_id']) ?> (<?= htmlspecialchars($booking_opt['route']) ?> on <?= htmlspecialchars($booking_opt['journey_date']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No eligible bookings found</option>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">Choose the specific journey your claim relates to.</small>
                    </div>

                    <div class="mb-3">
                        <label for="reason_for_request" class="form-label ac-form-label">Reason for Request <span class="text-danger">*</span></label>
                        <select class="form-select" id="reason_for_request" name="reason_for_request" required>
                            <option value="">Select a reason...</option>
                            <option value="Significant Delay" <?= ($form_data_persisted['reason_for_request'] == 'Significant Delay' ? 'selected' : '') ?>>Significant Train Delay</option>
                            <option value="Train Cancellation" <?= ($form_data_persisted['reason_for_request'] == 'Train Cancellation' ? 'selected' : '') ?>>Train Cancellation</option>
                            <option value="Service Quality Issue" <?= ($form_data_persisted['reason_for_request'] == 'Service Quality Issue' ? 'selected' : '') ?>>Service Quality Issue</option>
                            <option value="Other" <?= ($form_data_persisted['reason_for_request'] == 'Other' ? 'selected' : '') ?>>Other (Please specify below)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="detailed_description" class="form-label ac-form-label">Detailed Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="detailed_description" name="detailed_description" rows="5"
                            placeholder="Please include all relevant details (Min. 20 characters)" required><?= htmlspecialchars($form_data_persisted['detailed_description']) ?></textarea>
                    </div>

                    <div class="d-grid pt-2">
                        <button type="submit" name="submit_compensation" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../public/js/dashboard.js"></script>
    <script src="../../public/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>