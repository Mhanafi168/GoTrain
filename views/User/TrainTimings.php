<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../../config/DBController.php';

function fetchAllTrainSchedules()
{
    $dbController = DBController::getInstance();
    $db = $dbController->getConnection();

    if (!$db) {
        error_log("Failed to get PDO connection from DBController: " . $dbController->getLastError());
        die("Could not connect to the database (via DBController). Please try again later.");
    }

    try {
        $query = "SELECT 
                    t.train_id,
                    t.train_number,
                    t.train_name,
                    s_source.station_name AS source_station,
                    s_dest.station_name AS destination_station,
                    TIME_FORMAT(t.departure_time, '%h:%i %p') AS departure_time_formatted,
                    TIME_FORMAT(t.arrival_time, '%h:%i %p') AS arrival_time_formatted,
                    t.running_days 
                  FROM 
                    trains t
                  JOIN 
                    stations s_source ON t.source_station_id = s_source.station_id
                  JOIN 
                    stations s_dest ON t.destination_station_id = s_dest.station_id
                  WHERE 
                    t.is_active = TRUE
                  ORDER BY 
                    t.departure_time ASC, t.train_number ASC";

        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching train schedules (PDO): " . $e->getMessage());
        return [];
    }
}

$schedules = fetchAllTrainSchedules();

$day_map = [
    0 => 'Sun',
    1 => 'Mon',
    2 => 'Tue',
    3 => 'Wed',
    4 => 'Thu',
    5 => 'Fri',
    6 => 'Sat'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Timings - GoTrain</title>
    <link href="/GoTrain/public/assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #343a40;
            --text-color: #212529;
            --text-muted-color: #6c757d;
            --success-color: #198754;
            --gradient-start: #5e72e4;
            --gradient-end: #825ee4;
            --card-shadow: 0 0 1.25rem rgba(30, 30, 30, .09);
            --border-radius: 0.5rem;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Open Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 15px;
            box-sizing: border-box;
        }

        .content-wrapper {
            width: 100%;
            max-width: 1200px;
            margin-top: 20px;
            margin-bottom: 40px;
            background-color: #ffffff;
            border-radius: var(--border-radius);
            padding: 0;
        }


        .page-header {
            padding: 30px 25px;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
            margin-bottom: 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            margin-bottom: 0;
            font-family: 'Roboto', sans-serif;
            font-weight: 300;
            font-size: 2.5rem;
            letter-spacing: 0.5px;
        }

        .page-header h1 .fas {
            margin-right: 12px;
            font-size: 2.2rem;
            vertical-align: middle;
        }

        .table-container {
            padding: 25px;
            background-color: #ffffff;
            border-bottom-left-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .table-responsive {
            border: none;
            margin-top: 0;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background-color: #f8f9fc;
            color: var(--dark-gray);
            text-align: center;
            vertical-align: middle;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            font-size: 0.85rem;
            border-bottom: 2px solid var(--medium-gray);
            border-top: none;
            padding: 1rem 0.75rem;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fcfdff;
        }

        .table tbody tr:hover {
            background-color: var(--medium-gray);
            transition: background-color 0.2s ease-in-out;
        }

        .table tbody td {
            vertical-align: middle;
            text-align: left;
            padding: 0.9rem 0.75rem;
            border-top: 1px solid var(--medium-gray);
            color: var(--text-muted-color);
        }

        .table tbody td:first-child {
            color: var(--text-color);
            font-weight: 500;
        }

        .table tbody td.text-center,
        .table thead th.text-center {
            text-align: center !important;
        }

        .days-badge-container {
            white-space: nowrap;
        }

        .days-badge {
            font-size: 0.7rem;
            padding: 0.35em 0.6em;
            margin: 2px;
            border-radius: 1rem;
            display: inline-block;
            min-width: 38px;
            text-align: center;
            font-weight: 500;
            line-height: 1;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07);
        }

        .days-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .day-active {
            background-color: var(--success-color);
            color: white;
            border: 1px solid darken(var(--success-color), 5%);
        }

        .day-inactive {
            background-color: var(--medium-gray);
            color: var(--text-muted-color);
            border: 1px solid #d3d9df;
        }

        .alert-info {
            background-color: #e0f3ff;
            border-color: #b8e7ff;
            color: #004085;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-top: 30px;
        }

        .back-link-container {
            padding: 25px;
            text-align: center;
            background-color: #ffffff;
            border-bottom-left-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }

        .back-link-container .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            padding: 0.6rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
        }

        .back-link-container .btn-secondary:hover {
            background-color: darken(var(--secondary-color), 10%);
            border-color: darken(var(--secondary-color), 10%);
            transform: translateY(-1px);
        }

        .back-link-container .btn-secondary .fas {
            margin-right: 8px;
        }
    </style>
</head>

<body>

    <div class="content-wrapper">
        <div class="page-header">
            <h1><i class="fas fa-train"></i> Train Timings & Schedules</h1>
        </div>

        <div class="table-container">
            <?php if (empty($schedules)): ?>
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-info-circle me-2"></i>No train schedules found at the moment. Please check back later.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Train No.</th>
                                <th>Train Name</th>
                                <th>Source</th>
                                <th class="text-center">Departure</th>
                                <th>Destination</th>
                                <th class="text-center">Arrival</th>
                                <th class="text-center">Runs On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($schedules as $schedule):
                                $running_day_numbers = [];
                                if (!empty($schedule['running_days'])) {
                                    $running_day_numbers = array_map('trim', explode(',', $schedule['running_days']));
                                }
                            ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($schedule['train_number']) ?></td>
                                    <td><?= htmlspecialchars($schedule['train_name']) ?></td>
                                    <td><?= htmlspecialchars($schedule['source_station']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($schedule['departure_time_formatted']) ?></td>
                                    <td><?= htmlspecialchars($schedule['destination_station']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($schedule['arrival_time_formatted']) ?></td>
                                    <td class="text-center days-badge-container">
                                        <?php foreach ($day_map as $day_num => $day_name): ?>
                                            <span class="days-badge <?= in_array((string)$day_num, $running_day_numbers, true) ? 'day-active' : 'day-inactive' ?>">
                                                <?= htmlspecialchars($day_name) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="back-link-container">
            <a href="/GoTrain/views/Dashboard/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="/GoTrain/public/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>