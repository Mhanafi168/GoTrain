<?php
require_once '../../config/DBController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainId = $_POST['train_id'];
    $expectedArrival = $_POST['expected_arrival'];

    $db = DBController::getInstance();
    if ($db->openConnection()) {
        $stmt = $db->getConnection()->prepare(
            "UPDATE train_schedule SET expected_arrival = ? WHERE id = ?"
        );
        $stmt->execute([$expectedArrival, $trainId]);
        echo "Expected arrival time updated.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Expected Arrival</title>
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
        <h2>Set Expected Arrival</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="train_id" class="form-label">Train ID</label>
                <input