<?php
require_once '../../config/DBController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainId = $_POST['train_id'];
    $status = $_POST['status'];

    $db = DBController::getInstance();
    if ($db->openConnection()) {
        $stmt = $db->getConnection()->prepare(
            "UPDATE train_schedule SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $trainId]);
        echo "Train status updated.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Train Status</title>
    <link rel="stylesheet" href="../public/assets/css/bootstrap.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .form-control {
            width: 50%;
            margin-bottom: 15px;
        }

        .btn {
            width: 50%;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Report Train Status</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="train_id" class="form-label">Train ID</label>
                <input type="text" class="form-control" id="train_id" name="train_id" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="On Time">On Time</option>
                    <option value="Delayed">Delayed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Update Status</button>
        </form>
    </div>
</body>

</html>