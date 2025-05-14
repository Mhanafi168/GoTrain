<?php
require_once '../../config/DBController.php';
require_once '../../models/TrainSchedule.php';

$db = DBController::getInstance();
$scheduleModel = new TrainSchedule($db->getConnection());

if (isset($_GET['delete_delay_id'])) {
    $id = $_GET['delete_delay_id'];
    $scheduleModel->clearDelay($id);
    header("Location: station_master.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['schedule_id'];

    if (isset($_POST['actual_departure_time']) || isset($_POST['actual_arrival_time'])) {
        $scheduleModel->updateActualTimes(
            $id,
            $_POST['actual_departure_time'],
            $_POST['actual_arrival_time']
        );
    }

    if (isset($_POST['expected_arrival_time'])) {
        $scheduleModel->updateExpectedArrival($id, $_POST['expected_arrival_time']);
    }

    if (isset($_POST['public_delay'])) {
        $scheduleModel->declarePublicDelay($id);
    }

    if (isset($_POST['report_status'])) {
        $scheduleModel->reportTrainStatus($id, $_POST['status']);
    }

    header("Location: station_master.php");
    exit;
}

$schedules = $scheduleModel->getAllSchedules();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Station Master Panel</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Station Master - Manage Train Times</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Train</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Expected Arrival</th>
                    <th>Actual Arrival</th>
                    <th>Expected Departure</th>
                    <th>Actual Departure</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['train_number']) ?></td>
                        <td><?= htmlspecialchars($s['source_station']) ?></td>
                        <td><?= htmlspecialchars($s['destination_station']) ?></td>
                        <td><?= $s['expected_arrival_time'] ?></td>
                        <td><?= $s['actual_arrival_time'] ?></td>
                        <td><?= $s['expected_departure_time'] ?></td>
                        <td><?= $s['actual_departure_time'] ?></td>
                        <td><?= $s['status'] ?></td>
                        <td>
                            <form method="POST" class="d-grid gap-2">
                                <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
                                <input type="datetime-local" name="actual_arrival_time" placeholder="Actual Arrival" class="form-control mb-1">
                                <input type="datetime-local" name="actual_departure_time" placeholder="Actual Departure" class="form-control mb-1">
                                <input type="datetime-local" name="expected_arrival_time" placeholder="Expected Arrival" class="form-control mb-1">
                                <select name="status" class="form-select mb-1">
                                    <option value="On Time">On Time</option>
                                    <option value="Delayed">Delayed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                <button type="submit" name="report_status" value="1" class="btn btn-primary">Report Status</button>
                                <button type="submit" name="public_delay" value="1" class="btn btn-warning">Declare Public Delay</button>
                                <button type="submit" class="btn btn-success">Update Times</button>
                                <a href="?delete_delay_id=<?= $s['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete delay record?')">Delete Delay</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>