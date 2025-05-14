<?php
require_once '../../config/DBController.php';
require_once '../../models/TrainSchedule.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = DBController::getInstance();
$message = '';
$error = '';

try {
    if (!$db->openConnection()) {
        throw new RuntimeException("Database connection failed");
    }

    $scheduleModel = new TrainSchedule($db);

    $trains_query = "SELECT t.train_id, t.train_number, t.train_name, 
                            s_source.station_name as source_station,
                            s_dest.station_name as destination_station
                     FROM trains t
                     JOIN stations s_source ON t.source_station_id = s_source.station_id
                     JOIN stations s_dest ON t.destination_station_id = s_dest.station_id
                     WHERE t.is_active = TRUE 
                     ORDER BY t.train_number";
    
    $trains = $db->select($trains_query);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $train_number = $_POST['train_number'] ?? '';
        $source = $_POST['source_station'] ?? '';
        $destination = $_POST['destination_station'] ?? '';
        $expected_departure = $_POST['expected_departure_time'] ?? '';
        $expected_arrival = $_POST['expected_arrival_time'] ?? '';
        $actual_departure = $_POST['actual_departure_time'] ?? '';
        $actual_arrival = $_POST['actual_arrival_time'] ?? '';
        $status = $_POST['status'] ?? '';

        if (empty($train_number) || empty($source) || empty($destination) || 
            empty($expected_departure) || empty($expected_arrival) || empty($status)) {
            throw new InvalidArgumentException("All required fields must be filled");
        }

        if ($scheduleModel->addSchedule($train_number, $source, $destination, 
            $expected_departure, $expected_arrival, 
            $actual_departure, $actual_arrival, $status)) {
            $message = "Schedule added successfully!";
        } else {
            throw new RuntimeException("Failed to add schedule");
        }
    }

    $schedules = $scheduleModel->getAllSchedules();
    if ($schedules === false) {
        throw new RuntimeException("Failed to fetch schedules");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Train Schedules</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
    <style>
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Add Train Schedule</h2>
    <form method="POST" id="scheduleForm">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Train Number</label>
                <select class="form-select" name="train_number" id="trainSelect" required>
                    <option value="">Select Train</option>
                    <?php foreach ($trains as $train): ?>
                        <option value="<?= htmlspecialchars($train['train_number']) ?>"
                                data-source="<?= htmlspecialchars($train['source_station']) ?>"
                                data-destination="<?= htmlspecialchars($train['destination_station']) ?>">
                            <?= htmlspecialchars($train['train_number'] . ' - ' . $train['train_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Source Station</label>
                <select class="form-select" name="source_station" id="sourceStation" required>
                    <option value="">Select Source Station</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Destination Station</label>
                <select class="form-select" name="destination_station" id="destinationStation" required>
                    <option value="">Select Destination Station</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Expected Departure Time</label>
                <input class="form-control" type="datetime-local" name="expected_departure_time" required>
            </div>
            <div class="col-md-4">
                <label>Expected Arrival Time</label>
                <input class="form-control" type="datetime-local" name="expected_arrival_time" required>
            </div>
            <div class="col-md-4">
                <label>Status</label>
                <select class="form-select" name="status" required>
                    <option value="On Time">On Time</option>
                    <option value="Delayed">Delayed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Add Schedule</button>

            </div>
            <div class="col-md-4">
            </div>
            <div class="col-md-4">
                <a href="admin.php" class="btn btn-primary w-100">Go Back To Dashboard</a>

            </div>
        </div>
    </form>

    <hr>
    <h4 class="mt-5">Existing Schedules</h4>
    <?php if (!empty($schedules)): ?>
        <table class="table table-bordered table-hover mt-3">
            <thead>
                <tr>
                    <th>Train</th><th>From</th><th>To</th>
                    <th>Exp. Depart</th><th>Exp. Arrive</th>
                    <th>Act. Depart</th><th>Act. Arrive</th>
                    <th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $sch): ?>
                    <tr>
                        <td><?= htmlspecialchars($sch['train_number']) ?></td>
                        <td><?= htmlspecialchars($sch['source_station']) ?></td>
                        <td><?= htmlspecialchars($sch['destination_station']) ?></td>
                        <td><?= htmlspecialchars($sch['expected_departure_time']) ?></td>
                        <td><?= htmlspecialchars($sch['expected_arrival_time']) ?></td>
                        <td><?= htmlspecialchars($sch['actual_departure_time'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($sch['actual_arrival_time'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($sch['status']) ?></td>
                        <td>
                            <a href="edit_schedule.php?id=<?= $sch['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No schedules found</div>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trainSelect = document.getElementById('trainSelect');
    const sourceStation = document.getElementById('sourceStation');
    const destinationStation = document.getElementById('destinationStation');

    trainSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const sourceValue = selectedOption.getAttribute('data-source');
        const destinationValue = selectedOption.getAttribute('data-destination');

        sourceStation.innerHTML = '<option value="">Select Source Station</option>';
        destinationStation.innerHTML = '<option value="">Select Destination Station</option>';

        if (sourceValue) {
            const sourceOption = document.createElement('option');
            sourceOption.value = sourceValue;
            sourceOption.textContent = sourceValue;
            sourceOption.selected = true;
            sourceStation.appendChild(sourceOption);
        }

        if (destinationValue) {
            const destOption = document.createElement('option');
            destOption.value = destinationValue;
            destOption.textContent = destinationValue;
            destOption.selected = true;
            destinationStation.appendChild(destOption);
        }
    });
});
</script>
</body>
</html>