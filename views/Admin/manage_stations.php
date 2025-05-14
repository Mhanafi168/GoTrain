<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Stations</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet" />
</head>

<body>
    <?php
    require_once '../../config/DBController.php';
    require_once '../../models/Station.php';
    $stations = [];
    $dbErrorMessage = '';

    $db = DBController::getInstance();

    if ($db->openConnection()) {
        try {
            $stationModel = new Station($db);

            $stations = $stationModel->getAllStations();

            if ($stations === false) {
                $stations = [];
                $dbErrorMessage = "Error retrieving stations: " . $stationModel->getLastError();
            }
        } catch (RuntimeException $e) {
            $stations = [];
            $dbErrorMessage = "Error: " . $e->getMessage();
            error_log("Manage Stations Error: " . $e->getMessage());
        }
    } else {
        $dbErrorMessage = "Failed to connect to the database: " . $db->getLastError();
    }
    ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">List of All Stations</h5>
                        <a href="admin.php" class="btn btn-primary">Go Back To Dashboard</a>
                        <a href="add_station.php" class="btn btn-primary">Add Station</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dbErrorMessage)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($dbErrorMessage); ?>
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Code</th>
                                        <th scope="col">City (Location)</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stations)) {
                                        $count = 1;
                                        foreach ($stations as $station) {
                                            echo "<tr>";
                                            echo "<th scope='row'>" . $count++ . "</th>";
                                            echo "<td>" . htmlspecialchars($station['station_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($station['station_code']) . "</td>";
                                            echo "<td>" . htmlspecialchars($station['city']) . "</td>";
                                            echo "<td>
                                  <a href='edit_station.php?id=" . htmlspecialchars($station['station_id']) . "' class='btn btn-success btn-sm'>Edit</a>
                                  <a href='delete_station.php?id=" . htmlspecialchars($station['station_id']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this station?\")'>Remove</a>
                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else if (empty($dbErrorMessage)) {
                                        echo "<tr><td colspan='5'>No stations found</td></tr>";
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>