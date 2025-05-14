<?php
require_once '../../config/DBController.php';
require_once '../../models/TrainSchedule.php';

$db = DBController::getInstance();
$scheduleModel = new TrainSchedule($db->getConnection());

$id = $_GET['id'];
$schedule = $scheduleModel->getScheduleById($id);

if (!$schedule) {
    echo "Schedule not found!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_number = $_POST['train_number'];
    $source_station = $_POST['source_station'];
    $destination_station = $_POST['destination_station'];
    $expected_departure_time = $_POST['expected_departure_time'];
    $expected_arrival_time = $_POST['expected_arrival_time'];
    $actual_departure_time = $_POST['actual_departure_time'];
    $actual_arrival_time = $_POST['actual_arrival_time'];
    $status = $_POST['status'];

    $scheduleModel->updateSchedule($id, $train_number, $source_station, $destination_station, $expected_departure_time, $expected_arrival_time, $actual_departure_time, $actual_arrival_time, $status);
    header("Location: admin_schedule.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Edit Train Schedule</h2>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4"><input class="form-control" name="train_number" value="<?= $schedule['train_number'] ?>" required></div>
                <div class="col-md-4"><input class="form-control" name="source_station" value="<?= $schedule['source_station'] ?>" required></div>
                <div class="col-md-4"><input class="form-control" name="destination_station" value="<?= $schedule['destination_station'] ?>" required></div>
                <div class="col-md-4"><input class="form-control" type="datetime-local" name="expected_departure_time" value="<?= date('Y-m-d\TH:i', strtotime($schedule['expected_departure_time'])) ?>" required></div>
                <div class="col-md-4"><input class="form-control" type="datetime-local" name="expected_arrival_time" value="<?= date('Y-m-d\TH:i', strtotime($schedule['expected_arrival_time'])) ?>" required></div>
                <div class="col-md-4"><input class="form-control" type="datetime-local" name="actual_departure_time" value="<?= date('Y-m-d\TH:i', strtotime($schedule['actual_departure_time'])) ?>"></div>
                <div class="col-md-4"><input class="form-control" type="datetime-local" name="actual_arrival_time" value="<?= date('Y-m-d\TH:i', strtotime($schedule['actual_arrival_time'])) ?>"></div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option <?= $schedule['status'] === 'On Time' ? 'selected' : '' ?>>On Time</option>
                        <option <?= $schedule['status'] === 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                        <option <?= $schedule['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4"><button type="submit" class="btn btn-success w-100">Update</button></div>
            </div>
        </form>
    </div>
</body>

</html>