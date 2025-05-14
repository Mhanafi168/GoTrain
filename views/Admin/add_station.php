<?php
require_once '../../config/DBController.php';
require_once '../../models/Station.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = DBController::getInstance();
if (!$db->openConnection()) {
    die("Database connection failed. Please check configuration or contact support.");
}
$stationModel = new Station($db);

$message = '';
$error = '';
$form_name = '';
$form_code = '';
$form_location = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $station_name_input = trim($_POST['name'] ?? '');
    $station_code_input = trim($_POST['code'] ?? '');
    $city_input = trim($_POST['location'] ?? '');

    $form_name = $station_name_input;
    $form_code = $station_code_input;
    $form_location = $city_input;


    try {
        if (empty($station_name_input) || empty($station_code_input) || empty($city_input)) {
            throw new Exception("All fields (Station Name, Station Code, Location/City) are required.");
        }

        if (strlen($station_name_input) > 100) {
            throw new Exception("Station Name must be 100 characters or less.");
        }
        if (strlen($station_code_input) > 10) {
            throw new Exception("Station code must be 10 characters or less.");
        }
        if (strlen($city_input) > 100) {
            throw new Exception("Location (City) must be 100 characters or less.");
        }

        if (!preg_match('/^[A-Z0-9]+$/i', $station_code_input)) {
            throw new Exception("Station code can only contain letters and numbers.");
        }

        if ($stationModel->addStation($station_name_input, $station_code_input, $city_input)) {
            $message = "Station added successfully!";
            $form_name = '';
            $form_code = '';
            $form_location = '';
        } else {
            throw new Exception("Failed to add station. This might be due to a duplicate station code or a database issue. Please try again or check logs.");
        }
    } catch (InvalidArgumentException $e) {
        $error = "Validation Error: " . $e->getMessage();
    } catch (RuntimeException $e) {
        $error = "Operation Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Add New Station</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Station Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?= htmlspecialchars($form_name) ?>" required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="code" class="form-label">Station Code</label>
                                <input type="text" class="form-control" id="code" name="code"
                                    value="<?= htmlspecialchars($form_code) ?>"
                                    maxlength="10" pattern="[A-Za-z0-9]+" title="Only letters and numbers, max 10 characters" required>
                                <small class="text-muted">Short code (e.g., GCT, PENN). Max 10 alphanumeric characters.</small>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Location (City)</label>
                                <input type="text" class="form-control" id="location" name="location"
                                    value="<?= htmlspecialchars($form_location) ?>" required maxlength="100">
                                <small class="text-muted">The city where the station is located.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Add Station</button>
                                <a href="manage_stations.php" class="btn btn-secondary">Back to Stations List</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>