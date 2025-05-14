<?php
require_once '../../config/DBController.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainId = $_POST['train_id'];
    $delayMessage = $_POST['message'];

    $db = DBController::getInstance();
    if ($db->openConnection()) {
        try {
            $stmt = $db->getConnection()->prepare(
                "UPDATE train_schedule SET public_notice = :message, status = 'delayed' WHERE id = :train_id"
            );
            $stmt->execute([
                ':message' => $delayMessage,
                ':train_id' => $trainId
            ]);


            if ($stmt->rowCount() > 0) {
                $message = "Public delay declared successfully!";
            } else {
                $message = "No records updated. Check Train ID.";
            }
        } catch (PDOException $e) {
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
    <title>Declare Public Delay</title>
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
        <h2 class="mb-4">Declare Public Delay</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="border p-4 rounded">
            <div class="mb-3">
                <label for="train_id" class="form-label">Train ID</label>
                <input type="number" class="form-control" id="train_id" name="train_id" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Delay Announcement</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                <small class="text-muted">Example: "Train 123 delayed by 30 minutes due to signaling issues"</small>
            </div>
            <button type="submit" class="btn btn-warning">Declare Delay</button>
            <a href="update_arrival_departure.php" class="btn btn-secondary ms-2">Back to Schedule</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>