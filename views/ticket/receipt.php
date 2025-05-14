<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__, 2));
require_once '../../config/DBController.php';
require_once '../../controllers/TicketController.php';

$page_error_message = null;
$single_ticket_details = null;
$booking_id_from_url = null;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_flash_message'] = "Please login to view your ticket receipt.";
    header("Location: ../auth/login.php");
    exit;
}
$loggedInUserId = (int)$_SESSION['user_id'];

try {
    $ticketController = new TicketController();

    if (!$ticketController->isDBConnected()) {
        throw new Exception("Ticket service is temporarily unavailable. Please try again later. (DB_CONN_FAIL)");
    }

    if (!isset($_GET['booking_id']) || empty(trim($_GET['booking_id']))) {
        throw new Exception("No booking ID specified for the receipt. Please go back to your tickets list.");
    }
    $booking_id_from_url = trim($_GET['booking_id']);

    $single_ticket_details = $ticketController->viewTicket($booking_id_from_url, $loggedInUserId);

    if (!$single_ticket_details) {
        $controllerError = $ticketController->getLastError() ?: "Ticket not found or you don't have permission to view it.";
        throw new Exception("Could not display ticket (ID: " . htmlspecialchars($booking_id_from_url) . "). " . htmlspecialchars($controllerError));
    }

    if (empty($single_ticket_details['passenger_name'])) {
        $single_ticket_details['passenger_name'] = $_SESSION['username'] ?? 'Valued Customer';
    }
} catch (Exception $e) {
    error_log("Error on views/ticket/receipt.php: " . $e->getMessage() . "\nBooking ID: " . htmlspecialchars($booking_id_from_url ?? 'N/A') . "\nTrace: " . $e->getTraceAsString());
    $page_error_message = "An error occurred while fetching ticket details: " . $e->getMessage();
    $single_ticket_details = null;
}

function format_receipt_page_time($timeString)
{
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Receipt: <?= htmlspecialchars($single_ticket_details['booking_id'] ?? ($page_error_message ? 'Error' : 'N/A')) ?> - GoTrain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= ROOT_PATH ?>/public/css/ticket-receipt.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .ticket-receipt-wrapper {
            max-width: 700px;
            margin: 2rem auto;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .ticket-receipt-header {
            background: linear-gradient(135deg, #0056b3, #003d80);
            color: #fff;
            padding: 1.5rem 2rem;
            text-align: center;
            border-top-left-radius: 9px;
            border-top-right-radius: 9px;
        }

        .ticket-receipt-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .ticket-receipt-body {
            padding: 2rem;
        }

        .ticket-receipt-id {
            background-color: rgba(0, 86, 179, 0.1);
            color: #0056b3;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: inline-block;
            font-size: 0.9rem;
        }

        .route-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #ced4da;
        }

        .route-station .label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        .route-station .name {
            font-size: 1.4rem;
            font-weight: 500;
            color: #212529;
            margin-top: 0.25rem;
        }

        .route-arrow {
            font-size: 1.8rem;
            color: #0056b3;
            flex-shrink: 0;
            margin: 0 1rem;
        }

        .detail-grid .row {
            margin-bottom: 0.85rem;
        }

        .detail-grid .label {
            font-size: 0.85rem;
            color: #6c757d;
            display: block;
            margin-bottom: 0.1rem;
        }

        .detail-grid .value {
            font-size: 1rem;
            color: #212529;
            font-weight: 500;
        }

        .qr-code-section {
            text-align: center;
            margin: 2rem 0;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .qr-code-section img {
            max-width: 180px;
            height: auto;
            border: 1px solid #eee;
            padding: 5px;
            background: white;
        }

        .ticket-receipt-footer {
            padding: 1rem 2rem;
            background-color: #f8f9fa;
            text-align: center;
            border-bottom-left-radius: 9px;
            border-bottom-right-radius: 9px;
            border-top: 1px solid #dee2e6;
        }

        .print-hide {
            display: initial;
        }

        @media print {
            body {
                background-color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0;
                padding: 0;
            }

            .ticket-receipt-wrapper {
                margin: 0 auto;
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }

            .ticket-receipt-header {
                background: #e9ecef !important;
                color: #000 !important;
                border-radius: 0 !important;
            }

            .ticket-receipt-header h1 {
                font-size: 1.5rem;
            }

            .ticket-receipt-id {
                background-color: #e9ecef !important;
                color: #000 !important;
            }

            .container {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .print-hide {
                display: none !important;
            }

            .qr-code-section img {
                max-width: 150px;
            }

            .route-arrow {
                color: #333 !important;
            }

            .ticket-receipt-footer {
                background-color: #f8f9fa !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if ($page_error_message): ?>
            <div class="alert alert-danger mt-4" role="alert">
                <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error Viewing Ticket</h4>
                <p><?= htmlspecialchars($page_error_message) ?></p>
                <hr>
                <a href="index.php" class="btn btn-secondary btn-sm print-hide"><i class="bi bi-list-ul"></i> My Tickets List</a>
                <a href="../Dashboard/default.php" class="btn btn-info btn-sm print-hide"><i class="bi bi-house-door"></i> Dashboard</a>
            </div>
        <?php elseif ($single_ticket_details): ?>
            <div class="ticket-receipt-wrapper mt-4">
                <div class="ticket-receipt-header">
                    <h1><i class="bi bi-train-front-fill me-2"></i>GoTrain E-Ticket</h1>
                    <p class="mb-0">Your Journey Details</p>
                </div>
                <div class="ticket-receipt-body">
                    <div class="text-center mb-4">
                        <span class="ticket-receipt-id">Booking ID: <?= htmlspecialchars($single_ticket_details['booking_id'] ?? 'N/A') ?></span>
                    </div>

                    <div class="route-display">
                        <div class="route-station text-center">
                            <div class="label">FROM</div>
                            <div class="name"><?= htmlspecialchars($single_ticket_details['source_station'] ?? 'N/A') ?></div>
                        </div>
                        <div class="route-arrow align-self-center"><i class="bi bi-arrow-right-circle-fill"></i></div>
                        <div class="route-station text-center">
                            <div class="label">TO</div>
                            <div class="name"><?= htmlspecialchars($single_ticket_details['destination_station'] ?? 'N/A') ?></div>
                        </div>
                    </div>

                    <div class="detail-grid">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="label">Passenger Name:</div>
                                <div class="value"><?= htmlspecialchars($single_ticket_details['passenger_name'] ?? 'N/A') ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="label">Passengers:</div>
                                <div class="value"><?= htmlspecialchars($single_ticket_details['passengers'] ?? '1') ?></div>
                            </div>
                        </div>
                        <hr class="my-2 opacity-50">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="label">Train:</div>
                                <div class="value"><?= htmlspecialchars($single_ticket_details['train_name'] ?? 'N/A') ?> (<?= htmlspecialchars($single_ticket_details['train_number'] ?? 'N/A') ?>)</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="label">Class:</div>
                                <div class="value"><?= htmlspecialchars(ucfirst($single_ticket_details['class'] ?? 'N/A')) ?></div>
                            </div>
                        </div>
                        <hr class="my-2 opacity-50">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="label">Journey Date:</div>
                                <div class="value"><?= !empty($single_ticket_details['journey_datetime']) ? htmlspecialchars(date('F j, Y (l)', strtotime($single_ticket_details['journey_datetime']))) : 'N/A' ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="label">Departure Time:</div>
                                <div class="value"><?= format_receipt_page_time($single_ticket_details['departure_time'] ?? '') ?></div>
                            </div>
                        </div>

                        <?php if (($single_ticket_details['ticket_type'] ?? '') === 'return' && !empty($single_ticket_details['return_date'])): ?>
                            <hr class="my-2 opacity-50">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="label">Ticket Type:</div>
                                    <div class="value text-primary fw-bold"><?= htmlspecialchars(ucfirst($single_ticket_details['ticket_type'])) ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="label">Return Date:</div>
                                    <div class="value"><?= htmlspecialchars(date('F j, Y (l)', strtotime($single_ticket_details['return_date']))) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-2 opacity-50">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="label">Status:</div>
                                <?php
                                $status_val_s = $single_ticket_details['status'] ?? 'Unknown';
                                $status_badge_s = 'secondary';
                                $status_s_l = strtolower($status_val_s);
                                if (str_contains($status_s_l, 'confirmed') || str_contains($status_s_l, 'upcoming')) $status_badge_s = 'success';
                                elseif (str_contains($status_s_l, 'cancelled')) $status_badge_s = 'danger';
                                elseif (str_contains($status_s_l, 'completed') || str_contains($status_s_l, 'arrived')) $status_badge_s = 'primary';
                                ?>
                                <div class="value"><span class="badge bg-<?= $status_badge_s ?> fs-6"><?= htmlspecialchars($status_val_s) ?></span></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="label">Total Fare:</div>
                                <div class="value fw-bold fs-5">EGP <?= isset($single_ticket_details['fare']) ? htmlspecialchars(number_format((float)$single_ticket_details['fare'], 2)) : 'N/A' ?></div>
                            </div>
                        </div>
                        <hr class="my-2 opacity-50">
                        <div class="row">
                            <div class="col-12">
                                <div class="label">Booked On:</div>
                                <div class="value"><?= !empty($single_ticket_details['booking_creation_date']) ? htmlspecialchars(date('M j, Y, g:i a', strtotime($single_ticket_details['booking_creation_date']))) : 'N/A' ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="qr-code-section">
                        <div id="qrcode_display_area_ticket"></div>
                        <p class="mt-2 text-muted small">Present this e-ticket at the gate for verification.</p>
                    </div>
                </div>
                <div class="ticket-receipt-footer">
                    <p class="mb-1 small">Thank you for choosing GoTrain!</p>
                    <p class="mb-0 small text-muted">Have a safe and pleasant journey.</p>
                </div>
                <div class="text-center mt-4 mb-5 print-hide ">
                    <button onclick="window.print()" class="btn btn-primary me-2"><i class="bi bi-printer me-1"></i> Print Ticket</button>
                    <a href="index.php" class="btn btn-outline-secondary me-2"><i class="bi bi-list-ul me-1"></i> My Tickets List</a>
                    <a href="../Dashboard/default.php" class="btn btn-secondary"><i class="bi bi-house-door me-1"></i> Dashboard</a>
                </div>
            </div>



        <?php else: ?>
            <div class="alert alert-warning mt-4 text-center">
                <p>The requested ticket could not be found or is unavailable.</p>
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-list-ul"></i> My Tickets List</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($single_ticket_details) && $single_ticket_details && !$page_error_message): ?>
                try {
                    const qr = qrcode(0, 'M');
                    let qrData = "GoTrain E-Ticket\n";
                    qrData += "Booking ID: <?= addslashes($single_ticket_details['booking_id'] ?? 'N/A') ?>\n";
                    qrData += "Passenger: <?= addslashes($single_ticket_details['passenger_name'] ?? 'N/A') ?>\n";
                    qrData += "Route: <?= addslashes($single_ticket_details['source_station'] ?? '') ?> to <?= addslashes($single_ticket_details['destination_station'] ?? '') ?>\n";
                    qrData += "Journey Date: <?= !empty($single_ticket_details['journey_datetime']) ? date('Y-m-d', strtotime($single_ticket_details['journey_datetime'])) : 'N/A' ?>\n";
                    qrData += "Departure: <?= addslashes(format_receipt_page_time($single_ticket_details['departure_time'] ?? '')) ?>\n";
                    qrData += "Train: <?= addslashes($single_ticket_details['train_number'] ?? '') ?>";

                    qr.addData(qrData);
                    qr.make();
                    var qrContainer = document.getElementById('qrcode_display_area_ticket');
                    if (qrContainer) {
                        qrContainer.innerHTML = qr.createImgTag(5, 8);
                    }
                } catch (e) {
                    console.error("QR Code Generation Error: ", e);
                    var qrContainer = document.getElementById('qrcode_display_area_ticket');
                    if (qrContainer) {
                        qrContainer.innerHTML = "<p class='text-danger small'>QR code could not be generated.</p>";
                    }
                }
            <?php endif; ?>
        });
    </script>
</body>

</html>