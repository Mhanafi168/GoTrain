<?php
require_once '../../config/DBController.php';

$db = DBController::getInstance();
$bookings = [];
$error = null;

if ($db->openConnection()) {
    try {
        $query = "SELECT b.id, b.booking_id, u.username, 
                  b.source_station as source_station, 
                  b.destination_station, 
                  DATE(b.booking_date) as booking_date,
                  b.departure_time,
                  b.class, 
                  b.ticket_type, 
                  b.fare as amount,
                  b.train_name,
                  b.train_number,
                  b.status,
                  b.passengers
                  FROM bookings b 
                  JOIN users u ON b.user_id = u.user_id 
                  ORDER BY b.booking_date DESC";

        $bookings = $db->select($query);
        if ($bookings === false) {
            $error = $db->getLastError();
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Booking Records</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            padding: 40px;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .back-to-dashboard {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-to-dashboard:hover {
            background-color: #3253a8;
            transform: translateY(-2px);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--text-dark);
            padding-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background-color: var(--primary-color);
            color: white;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .no-data {
            text-align: center;
            color: var(--text-light);
            padding: 20px;
        }

        .error {
            color: #dc3545;
            padding: 15px;
            background-color: #f8d7da;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .status-confirmed {
            color: #28a745;
            font-weight: bold;
        }

        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .container {
                padding: 20px;
            }

            .back-to-dashboard {
                position: relative;
                top: 0;
                right: 0;
                margin-bottom: 20px;
                display: inline-flex;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="admin.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <h2>Booking Records</h2>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
                <p>Please make sure the database tables are properly set up.</p>
            </div>
        <?php elseif (!empty($bookings)): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Train</th>
                        <th>Passengers</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $index => $booking): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['source_station']) ?></td>
                            <td><?= htmlspecialchars($booking['destination_station']) ?></td>
                            <td><?= $booking['booking_date'] ?></td>
                            <td><?= $booking['departure_time'] ?></td>
                            <td><?= $booking['class'] ?></td>
                            <td><?= ucfirst(str_replace('-', ' ', $booking['ticket_type'])) ?></td>
                            <td>EGP <?= number_format($booking['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($booking['train_number'] . ' - ' . $booking['train_name']) ?></td>
                            <td><?= $booking['passengers'] ?></td>
                            <td class="status-<?= strtolower($booking['status']) ?>">
                                <?= $booking['status'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">No bookings found.</div>
        <?php endif; ?>
    </div>

</body>

</html>