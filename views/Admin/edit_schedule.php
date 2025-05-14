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

    $id = $_GET['id'];
    $schedule = $scheduleModel->getScheduleById($id);

    if (!$schedule) {
        throw new RuntimeException("Schedule not found!");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $train_number = $_POST['train_number'] ?? '';
        $source_station = $_POST['source_station'] ?? '';
        $destination_station = $_POST['destination_station'] ?? '';
        $expected_departure_time = $_POST['expected_departure_time'] ?? '';
        $expected_arrival_time = $_POST['expected_arrival_time'] ?? '';
        $actual_departure_time = $_POST['actual_departure_time'] ?? '';
        $actual_arrival_time = $_POST['actual_arrival_time'] ?? '';
        $status = $_POST['status'] ?? '';

        if (empty($train_number) || empty($source_station) || empty($destination_station) || 
            empty($expected_departure_time) || empty($expected_arrival_time) || empty($status)) {
            throw new InvalidArgumentException("All required fields must be filled");
        }

        if ($scheduleModel->updateSchedule($id, $train_number, $source_station, $destination_station, 
            $expected_departure_time, $expected_arrival_time, 
            $actual_departure_time, $actual_arrival_time, $status)) {
            $message = "Schedule updated successfully!";
        } else {
            throw new RuntimeException("Failed to update schedule");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
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

        <h2>Edit Train Schedule</h2>
        <form method="POST" id="scheduleForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Train Number</label>
                    <select class="form-select" name="train_number" id="trainSelect" required>
                        <option value="">Select Train</option>
                        <?php foreach ($trains as $train): ?>
                            <option value="<?= htmlspecialchars($train['train_number']) ?>"
                                    data-source="<?= htmlspecialchars($train['source_station']) ?>"
                                    data-destination="<?= htmlspecialchars($train['destination_station']) ?>"
                                    <?= $schedule['train_number'] === $train['train_number'] ? 'selected' : '' ?>>
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
                    <input class="form-control" type="datetime-local" name="expected_departure_time" 
                           value="<?= date('Y-m-d\TH:i', strtotime($schedule['expected_departure_time'])) ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Expected Arrival Time</label>
                    <input class="form-control" type="datetime-local" name="expected_arrival_time" 
                           value="<?= date('Y-m-d\TH:i', strtotime($schedule['expected_arrival_time'])) ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Actual Departure Time</label>
                    <input class="form-control" type="datetime-local" name="actual_departure_time" 
                           value="<?= !empty($schedule['actual_departure_time']) ? date('Y-m-d\TH:i', strtotime($schedule['actual_departure_time'])) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label>Actual Arrival Time</label>
                    <input class="form-control" type="datetime-local" name="actual_arrival_time" 
                           value="<?= !empty($schedule['actual_arrival_time']) ? date('Y-m-d\TH:i', strtotime($schedule['actual_arrival_time'])) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select class="form-select" name="status" required>
                        <option value="On Time" <?= $schedule['status'] === 'On Time' ? 'selected' : '' ?>>On Time</option>
                        <option value="Delayed" <?= $schedule['status'] === 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                        <option value="Cancelled" <?= $schedule['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Update Schedule</button>
                </div>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const trainSelect = document.getElementById('trainSelect');
        const sourceStation = document.getElementById('sourceStation');
        const destinationStation = document.getElementById('destinationStation');

        function updateStations() {
            const selectedOption = trainSelect.options[trainSelect.selectedIndex];
            const source = selectedOption.getAttribute('data-source');
            const destination = selectedOption.getAttribute('data-destination');

            sourceStation.innerHTML = '<option value="">Select Source Station</option>';
            if (source) {
                const option = document.createElement('option');
                option.value = source;
                option.textContent = source;
                option.selected = true;
                sourceStation.appendChild(option);
            }

            destinationStation.innerHTML = '<option value="">Select Destination Station</option>';
            if (destination) {
                const option = document.createElement('option');
                option.value = destination;
                option.textContent = destination;
                option.selected = true;
                destinationStation.appendChild(option);
            }
        }

        trainSelect.addEventListener('change', updateStations);
        updateStations(); 
    });
    </script>
</body>
</html>