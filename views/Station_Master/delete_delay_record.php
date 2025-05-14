<?php
require_once '../../config/DBController.php';
require_once "../../models/TrainSchedule.php";

$message = '';
$schedules = [];

$db = DBController::getInstance();
if ($db->openConnection()) {
    try {
        $scheduleModel = new TrainSchedule($db);
        $schedules = $scheduleModel->getAllSchedules();
    } catch (Exception $e) {
        $message = "Error fetching schedules: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainId = $_POST['train_id'];

    if ($db->openConnection()) {
        try {
            $stmt = $db->getConnection()->prepare(
                "UPDATE train_schedule SET expected_arrival_time = NULL, status = 'on_time', public_notice = NULL WHERE id = ?"
            );
            $stmt->execute([$trainId]);

            if ($stmt->rowCount() > 0) {
                $message = "Delay record cleared successfully!";
            } else {
                $message = "No records were updated. Please check the Train ID.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Database connection failed";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Delay Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
        }

        .form-control {
            margin-bottom: 15px;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Delete Delay Record</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="border p-4 rounded">
            <div class="mb-3">
                <label for="train_id" class="form-label">Select Train</label>
                <select class="form-select" id="train_id" name="train_id" required>
                    <option value="">Choose a train...</option>
                    <?php foreach ($schedules as $schedule): ?>
                        <option value="<?= htmlspecialchars($schedule['id']) ?>">
                            Train #<?= htmlspecialchars($schedule['id']) ?> - 
                            <?= htmlspecialchars($schedule['train_number']) ?> 
                            (<?= htmlspecialchars($schedule['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-danger">Clear Delay Status</button>
            <a href="update_arrival_departure.php" class="btn btn-secondary ms-2">Back to Schedule</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>