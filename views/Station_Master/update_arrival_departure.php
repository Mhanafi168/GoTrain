<?php
require_once '../../config/DBController.php';
require_once "../../models/TrainSchedule.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = DBController::getInstance();
if (!$db->openConnection()) {
    $_SESSION['error_message'] = "Database connection failed";
    header("Location: station_master_dashboard.php");
    exit();
}

try {
    $scheduleModel = new TrainSchedule($db);
} catch (Exception $e) {
    $_SESSION['error_message'] = "Failed to initialize schedule model: " . $e->getMessage();
    header("Location: station_master_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $actual_departure_time = $_POST['actual_departure_time'] ?? null;
    $actual_arrival_time = $_POST['actual_arrival_time'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($actual_departure_time) {
        $actual_departure_time = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $actual_departure_time)));
    }
    if ($actual_arrival_time) {
        $actual_arrival_time = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $actual_arrival_time)));
    }

    if (!$id || !$actual_departure_time || !$actual_arrival_time || !$status) {
        $_SESSION['error_message'] = "All fields are required";
    } else {
        try {
            $success = $scheduleModel->updateArrivalDepartureTimes(
                $id,
                $actual_departure_time,
                $actual_arrival_time,
                $status
            );

            if ($success) {
                $_SESSION['success_message'] = "Train schedule updated successfully";
            } else {
                $_SESSION['error_message'] = "Failed to update schedule";
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: update_arrival_departure.php");
    exit();
}

try {
    $schedules = $scheduleModel->getAllSchedules();
    if ($schedules === false) {
        throw new Exception("Failed to fetch schedules");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error fetching schedules: " . $e->getMessage();
    $schedules = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Schedule Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="update_arrival_departure.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
        }

        .container {
            position: relative;
            padding-top: 2rem;
        }

        .back-to-dashboard {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-to-dashboard:hover {
            background-color: #3253a8;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        h2 {
            color: var(--text-dark);
            margin-bottom: 2rem;
            padding-right: 150px;
        }

        .table th {
            background-color: var(--text-dark);
            color: white;
        }

        .form-control-sm, .form-select-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Train Schedule Management</h2>
        <a href="station_master.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <?php if (count($schedules) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Train No</th>
                            <th>Source</th>
                            <th>Destination</th>
                            <th>Expected Departure</th>
                            <th>Expected Arrival</th>
                            <th>Actual Departure</th>
                            <th>Actual Arrival</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $row): ?>
                            <tr>
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['train_number']) ?></td>
                                    <td><?= htmlspecialchars($row['source_station']) ?></td>
                                    <td><?= htmlspecialchars($row['destination_station']) ?></td>
                                    <td><?= date('M j, Y H:i', strtotime($row['expected_departure_time'])) ?></td>
                                    <td><?= date('M j, Y H:i', strtotime($row['expected_arrival_time'])) ?></td>
                                    <td>
                                        <input type="datetime-local"
                                            name="actual_departure_time"
                                            class="form-control form-control-sm"
                                            value="<?= !empty($row['actual_departure_time']) ? date('Y-m-d\TH:i', strtotime($row['actual_departure_time'])) : '' ?>"
                                            required>
                                    </td>
                                    <td>
                                        <input type="datetime-local"
                                            name="actual_arrival_time"
                                            class="form-control form-control-sm"
                                            value="<?= !empty($row['actual_arrival_time']) ? date('Y-m-d\TH:i', strtotime($row['actual_arrival_time'])) : '' ?>"
                                            required>
                                    </td>
                                    <td>
                                        <select name="status" class="form-select form-select-sm" required>
                                            <option value="<?= TrainSchedule::STATUS_SCHEDULED ?>" <?= $row['status'] === TrainSchedule::STATUS_SCHEDULED ? 'selected' : '' ?>>Scheduled</option>
                                            <option value="<?= TrainSchedule::STATUS_DEPARTED ?>" <?= $row['status'] === TrainSchedule::STATUS_DEPARTED ? 'selected' : '' ?>>Departed</option>
                                            <option value="<?= TrainSchedule::STATUS_ARRIVED ?>" <?= $row['status'] === TrainSchedule::STATUS_ARRIVED ? 'selected' : '' ?>>Arrived</option>
                                            <option value="<?= TrainSchedule::STATUS_DELAYED ?>" <?= $row['status'] === TrainSchedule::STATUS_DELAYED ? 'selected' : '' ?>>Delayed</option>
                                            <option value="<?= TrainSchedule::STATUS_CANCELLED ?>" <?= $row['status'] === TrainSchedule::STATUS_CANCELLED ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="submit" name="update" class="btn btn-sm btn-primary">Update</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No train schedules found.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>

</html>