<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/DBController.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Transactions.php';

$page_global_message = '';
$page_global_message_type = 'error';
$pdo_connection = null;
$loggedInUserId = $_SESSION['user_id'] ?? null;
$stations_list = [];
$available_trains_list = [];
$station_names_map = [];
$fare_rates_config = ['second' => 1.12, 'first' => 1.2];
$form_source_station_id = $_POST['source_station'] ?? ($_GET['source_station'] ?? null);
$form_destination_station_id = $_POST['destination_station'] ?? ($_GET['destination_station'] ?? null);
$form_selected_train_id = $_POST['train_id'] ?? null;
$form_departure_date = $_POST['departure_date'] ?? date('Y-m-d', strtotime('+1 day'));
$form_ticket_type = $_POST['ticket_type'] ?? 'one-way';
$form_return_date = $_POST['return_date'] ?? '';
$form_class = $_POST['class'] ?? 'second';
$form_passengers = $_POST['passengers'] ?? 1;

if (!$loggedInUserId) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error_flash_message'] = "Please login to book a ticket.";
    header("Location: ../auth/login.php");
    exit;
}

try {
    $dbController = DBController::getInstance();
    $pdo_connection = $dbController->getConnection();

    if (!$pdo_connection) {
        throw new Exception("Database service is temporarily unavailable. (Code: DBC_GET_CONN_FAIL)");
    }

    $pdo_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt_stations = $pdo_connection->prepare("SELECT station_id, station_name FROM stations WHERE is_active = TRUE ORDER BY station_name");
    $stmt_stations->execute();
    $stations_list_result = $stmt_stations->fetchAll(PDO::FETCH_ASSOC);

    if ($stations_list_result) {
        $stations_list = $stations_list_result;
        foreach ($stations_list as $s) {
            $station_names_map[$s['station_id']] = $s['station_name'];
        }
    }

    if ($form_source_station_id && $form_destination_station_id && $form_source_station_id != $form_destination_station_id) {
        $stmt_trains = $pdo_connection->prepare("
            SELECT t.train_id, t.train_number, t.train_name, 
                   TIME_FORMAT(t.departure_time, '%h:%i %p') as departure_time_formatted,
                   t.departure_time as departure_time_value,
                   TIME_FORMAT(t.arrival_time, '%h:%i %p') as arrival_time_formatted
            FROM trains t
            WHERE t.source_station_id = :source_id AND t.destination_station_id = :dest_id AND t.is_active = TRUE
            ORDER BY t.departure_time ASC
        ");
        $stmt_trains->execute([':source_id' => $form_source_station_id, ':dest_id' => $form_destination_station_id]);
        $available_trains_list = $stmt_trains->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("PDOException in book_ticket.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $page_global_message = "Error loading booking options. Please refresh. (Code: FETCH_DROPDOWNS_PDO)";
} catch (Exception $e) {
    error_log("Exception in book_ticket.php: " . $e->getMessage());
    $page_global_message = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_ticket_confirm') {
    if (!$pdo_connection) {
        $page_global_message = "Booking failed: Database unavailable. (Code: BOOK_NO_DB_POST)";
    } else {
        try {
            $pdo_connection->beginTransaction();

            $userModel = new User($pdo_connection);
            $userData = $userModel->getUserById($loggedInUserId);

            if (!$userData) {
                throw new Exception("Your user session appears invalid. Please log out and try again. (Code: BOOK_USER_LOAD_FAIL)");
            }

            $current_user_balance = (float)$userModel->balance;

            $source_station_id = filter_input(INPUT_POST, 'source_station', FILTER_VALIDATE_INT);
            $destination_station_id = filter_input(INPUT_POST, 'destination_station', FILTER_VALIDATE_INT);
            $train_id = filter_input(INPUT_POST, 'train_id', FILTER_VALIDATE_INT);
            $selected_class = strtolower($_POST['class'] ?? '');
            $num_passengers = filter_input(INPUT_POST, 'passengers', FILTER_VALIDATE_INT, [
                "options" => ["min_range" => 1, "max_range" => 10]
            ]);
            $ticket_type = $_POST['ticket_type'] ?? '';
            $departure_date = $_POST['departure_date'] ?? '';
            $return_date = ($ticket_type === 'return') ? ($_POST['return_date'] ?? null) : null;

            if (!$source_station_id || !$destination_station_id) {
                throw new Exception("Please select both source and destination stations.");
            }

            if ($source_station_id == $destination_station_id) {
                throw new Exception("Source and destination stations cannot be the same.");
            }

            if (!$train_id) {
                throw new Exception("Please select a valid train.");
            }

            if (!in_array($selected_class, ['second', 'first'])) {
                throw new Exception("Invalid class selected.");
            }

            if (!$num_passengers) {
                throw new Exception("Number of passengers must be between 1 and 10.");
            }

            if (!in_array($ticket_type, ['one-way', 'return'])) {
                throw new Exception("Invalid ticket type.");
            }

            if (empty($departure_date) || strtotime($departure_date) < strtotime(date('Y-m-d'))) {
                throw new Exception("Invalid departure date.");
            }

            if ($ticket_type === 'return' && (empty($return_date) || strtotime($return_date) <= strtotime($departure_date))) {
                throw new Exception("Return date must be after departure date.");
            }

            $stmt = $pdo_connection->prepare("
                SELECT train_name, train_number, departure_time 
                FROM trains 
                WHERE train_id = ? 
                AND source_station_id = ? 
                AND destination_station_id = ? 
                AND is_active = TRUE 
                LIMIT 1
            ");
            $stmt->execute([$train_id, $source_station_id, $destination_station_id]);
            $train = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$train) {
                throw new Exception("The selected train is not available for this route.");
            }

            $stmt = $pdo_connection->prepare("
                SELECT distance 
                FROM station_distances 
                WHERE (station1_id = ? AND station2_id = ?) 
                OR (station1_id = ? AND station2_id = ?) 
                LIMIT 1
            ");
            $stmt->execute([$source_station_id, $destination_station_id, $destination_station_id, $source_station_id]);
            $distance = $stmt->fetchColumn();

            if (!$distance) {
                throw new Exception("Could not determine distance for the selected route.");
            }

            $fare_per_passenger = (float)$distance * $fare_rates_config[$selected_class];
            $total_fare = round($fare_per_passenger * $num_passengers, 2);

            if ($ticket_type === 'return') {
                $total_fare = round($total_fare * 1.8, 2);
            }

            if ($total_fare > $current_user_balance) {
                $recharge_url = "../User/recharge.php";
                throw new Exception(
                    "Insufficient balance. Required: EGP " . number_format($total_fare, 2) .
                        ", Available: EGP " . number_format($current_user_balance, 2) . ". " .
                        "<a href='$recharge_url' class='alert-link'>Please recharge your account</a>"
                );
            }

            $booking_id = 'BK-' . strtoupper(uniqid());

            $booking_data = [
                'booking_id' => $booking_id,
                'user_id' => $loggedInUserId,
                'source_station' => $station_names_map[$source_station_id] ?? "Station $source_station_id",
                'destination_station' => $station_names_map[$destination_station_id] ?? "Station $destination_station_id",
                'booking_date' => date('Y-m-d H:i:s'),
                'departure_time' => $train['departure_time'],
                'class' => $selected_class,
                'fare' => $total_fare,
                'train_name' => $train['train_name'],
                'train_number' => $train['train_number'],
                'status' => 'Confirmed',
                'passengers' => $num_passengers,
                'ticket_type' => $ticket_type,
                'return_date' => $return_date
            ];

            $bookingModel = new Booking($pdo_connection);
            $new_booking_id = $bookingModel->createBooking($booking_data);

            if ($new_booking_id) {
                $stmt = $pdo_connection->prepare("
        UPDATE users 
        SET balance = balance - ? 
        WHERE user_id = ? AND balance >= ?
    ");
                $stmt->execute([$total_fare, $loggedInUserId, $total_fare]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception("Failed to deduct payment from your account.");
                }

                $transaction = new Transaction($pdo_connection);
                $transaction->user_id = $loggedInUserId;
                $transaction->amount = $total_fare;
                $transaction->transaction_type = 'BOOKING_PAYMENT';
                $transaction->description = "Ticket payment for Booking $booking_id";

                if (!$transaction->addTransaction()) {
                    error_log("Failed to record transaction for booking $booking_id: " . $transaction->getLastError());
                }

                $pdo_connection->commit();

                $_SESSION['booking_success'] = [
                    'booking_id' => $booking_id,
                    'message' => "Ticket successfully booked!"
                ];
                header("Location: ../ticket/index.php?id=" . urlencode($booking_id));
                exit;
            } else {
                error_log("Booking creation failed. Error: " . $bookingModel->lastError);
                error_log("Booking data: " . print_r($booking_data, true));
                throw new Exception("Failed to create booking record. Error: " . $bookingModel->lastError);
            }
        } catch (PDOException $e) {
            $pdo_connection->rollBack();
            error_log("Booking PDOException: " . $e->getMessage());
            $page_global_message = "A database error occurred while processing your booking. Please try again. (Code: BOOK_CONFIRM_PDO_EX)";
        } catch (Exception $e) {
            if ($pdo_connection->inTransaction()) {
                $pdo_connection->rollBack();
            }
            error_log("Booking Exception: " . $e->getMessage());
            $page_global_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Train Ticket - GoTrain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            padding-top: 20px;
        }

        .container.booking-container {
            max-width: 700px;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .booking-container h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.3rem;
        }

        .form-control,
        .form-select {
            font-size: 0.95rem;
            border-radius: 0.25rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .alert.message-display {
            text-align: left;
        }

        .alert.message-display a.alert-link {
            font-weight: bold;
            text-decoration: underline;
        }

        #return-date-group {
            display: none;
        }

        .badge {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container booking-container">
        <h2><i class="fas fa-train me-2"></i>Book Your Train Ticket</h2>

        <?php if (!empty($page_global_message)): ?>
            <div class="alert alert-<?= $page_global_message_type === 'success' ? 'success' : ($page_global_message_type === 'info' ? 'info' : 'danger') ?> message-display" role="alert">
                <?= $page_global_message ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="stationSelectionForm" action="book_ticket.php">
            <input type="hidden" name="action" value="select_stations_trains">
            <fieldset>
                <legend class="h5 mb-3"><span class="badge bg-primary me-1">1</span> Select Your Route</legend>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="source_station" class="form-label">From:</label>
                        <select name="source_station" id="source_station" class="form-select form-select-sm" required onchange="this.form.submit()">
                            <option value="">-- Select Source --</option>
                            <?php foreach ($stations_list as $station): ?>
                                <option value="<?= htmlspecialchars($station['station_id']) ?>"
                                    <?= $form_source_station_id == $station['station_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($station['station_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="destination_station" class="form-label">To:</label>
                        <select name="destination_station" id="destination_station" class="form-select form-select-sm" required onchange="this.form.submit()">
                            <option value="">-- Select Destination --</option>
                            <?php foreach ($stations_list as $station): ?>
                                <option value="<?= htmlspecialchars($station['station_id']) ?>"
                                    <?= ($form_source_station_id != $station['station_id'] && $form_destination_station_id == $station['station_id']) ? 'selected' : '' ?>
                                    <?= ($form_source_station_id == $station['station_id']) ? 'disabled class="text-muted"' : '' ?>>
                                    <?= htmlspecialchars($station['station_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>
        </form>
        <hr class="my-4" />

        <form method="POST" id="bookingConfirmForm" action="book_ticket.php">
            <input type="hidden" name="action" value="book_ticket_confirm">
            <input type="hidden" name="source_station" value="<?= htmlspecialchars($form_source_station_id ?? '') ?>">
            <input type="hidden" name="destination_station" value="<?= htmlspecialchars($form_destination_station_id ?? '') ?>">

            <fieldset <?= (!$form_source_station_id || !$form_destination_station_id || $form_source_station_id == $form_destination_station_id) ? 'disabled' : '' ?>>
                <legend class="h5 mb-3"><span class="badge bg-primary me-1">2</span> Complete Journey Details</legend>

                <div class="mb-3">
                    <label for="train_id" class="form-label">Select Train:</label>
                    <select name="train_id" id="train_id" class="form-select form-select-sm" required>
                        <?php if (empty($available_trains_list) && $form_source_station_id && $form_destination_station_id && $form_source_station_id != $form_destination_station_id): ?>
                            <option value="">No direct trains currently available for this route.</option>
                        <?php elseif (!empty($available_trains_list)): ?>
                            <option value="">-- Select a Train --</option>
                            <?php foreach ($available_trains_list as $train): ?>
                                <option value="<?= htmlspecialchars($train['train_id']) ?>"
                                    <?= $form_selected_train_id == $train['train_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($train['train_number'] . ' - ' . $train['train_name'] . ' (Dep: ' . $train['departure_time_formatted'] . ', Arr: ' . $train['arrival_time_formatted'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Please select source and destination first.</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="departure_date" class="form-label">Departure Date:</label>
                        <input type="date" name="departure_date" id="departure_date" class="form-control form-control-sm"
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($form_departure_date) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="passengers" class="form-label">Passengers:</label>
                        <input type="number" name="passengers" id="passengers" class="form-control form-control-sm"
                            min="1" max="10" value="<?= htmlspecialchars($form_passengers) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ticket_type" class="form-label">Ticket Type:</label>
                        <select name="ticket_type" id="ticket_type" class="form-select form-select-sm" required>
                            <option value="one-way" <?= $form_ticket_type === 'one-way' ? 'selected' : '' ?>>One-Way</option>
                            <option value="return" <?= $form_ticket_type === 'return' ? 'selected' : '' ?>>Return</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="return-date-group" style="<?= $form_ticket_type === 'return' ? 'display:block;' : 'display:none;' ?>">
                        <label for="return_date" class="form-label">Return Date:</label>
                        <input type="date" name="return_date" id="return_date" class="form-control form-control-sm"
                            value="<?= htmlspecialchars($form_return_date) ?>"
                            <?= $form_ticket_type === 'return' ? 'required' : '' ?>>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="class" class="form-label">Class:</label>
                    <select name="class" id="class" class="form-select form-select-sm" required>
                        <option value="second" <?= $form_class === 'second' ? 'selected' : '' ?>>second</option>
                        <option value="first" <?= $form_class === 'first' ? 'selected' : '' ?>>First</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary"
                    <?= (!$form_source_station_id || !$form_destination_station_id || $form_source_station_id == $form_destination_station_id || empty($available_trains_list)) ? 'disabled' : '' ?>>
                    <i class="fas fa-check-circle me-2"></i>Confirm & Book Ticket
                </button>
            </fieldset>
        </form>

        <div class="text-center mt-4">
            <a href="../Dashboard/default.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ticketTypeSelect = document.getElementById('ticket_type');
            const returnDateGroup = document.getElementById('return-date-group');
            const departureDateInput = document.getElementById('departure_date');
            const returnDateInput = document.getElementById('return_date');

            function setMinReturnDate() {
                if (departureDateInput.value) {
                    let depDate = new Date(departureDateInput.value);
                    depDate.setDate(depDate.getDate() + 1);
                    let minReturnFormatted = depDate.toISOString().split('T')[0];
                    returnDateInput.min = minReturnFormatted;
                    if (returnDateInput.value && returnDateInput.value < minReturnFormatted) {
                        returnDateInput.value = minReturnFormatted;
                    }
                } else {
                    let todayPlusOne = new Date();
                    todayPlusOne.setDate(todayPlusOne.getDate() + 1);
                    returnDateInput.min = todayPlusOne.toISOString().split('T')[0];
                }
            }

            function toggleReturnDate() {
                if (ticketTypeSelect.value === 'return') {
                    returnDateGroup.style.display = 'block';
                    returnDateInput.required = true;
                    setMinReturnDate();
                } else {
                    returnDateGroup.style.display = 'none';
                    returnDateInput.required = false;
                    returnDateInput.value = '';
                }
            }

            ticketTypeSelect.addEventListener('change', toggleReturnDate);
            departureDateInput.addEventListener('change', function() {
                if (ticketTypeSelect.value === 'return') {
                    setMinReturnDate();
                }
            });

            toggleReturnDate();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>