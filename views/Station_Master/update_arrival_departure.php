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

    if (!$id || !$actual_departure_time || !$actual_arrival_time || !$status) {
        $_SESSION['error_message'] = "All fields are required";
    } elseif (!in_array($status, ['on_time', 'delayed', 'cancelled'])) {
        $_SESSION['error_message'] = "Invalid status selected";
    } else {
        try {
            $success = $scheduleModel->updateArrivalDepartureTimes(
                $id,
                null,
                null,
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
    <link href="update_arrival_departure.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">
        <h2 class="mb-4">Train Schedule Management</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
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
                                            <option value="on_time" <?= $row['status'] === 'on_time' ? 'selected' : '' ?>>On Time</option>
                                            <option value="delayed" <?= $row['status'] === 'delayed' ? 'selected' : '' ?>>Delayed</option>
                                            <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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