<?php
require_once '../../config/DBController.php';
require_once '../../models/Station.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$station_id = null;
$form_data = null;
$message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
$stationModel = null;

try {
    $db = DBController::getInstance();
    if (!$db->openConnection()) {
        throw new RuntimeException("Database connection failed. Please check configuration.");
    }
    $stationModel = new Station($db);
} catch (RuntimeException $e) {
    $error = "Initialization Error: " . $e->getMessage();
    error_log("Edit Station DB/Model Init Error: " . $e->getMessage());
}

if ($stationModel) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $station_id = (int)$_GET['id'];
        try {
            $fetched_station = $stationModel->getStationById($station_id);

            if (!$fetched_station) {
                $error = "Station with ID " . htmlspecialchars($station_id) . " not found.";
            } else {
                $form_data = [
                    'station_id' => $fetched_station['station_id'] ?? $station_id,
                    'station_name' => $fetched_station['station_name'] ?? '',
                    'station_code' => $fetched_station['station_code'] ?? '',
                    'city' => $fetched_station['city'] ?? '',
                    'is_active' => $fetched_station['is_active'] ?? 1
                ];
            }
        } catch (Exception $e) {
            $error = "Error fetching station details: " . $e->getMessage();
            error_log("Edit Station Error (fetch): " . $e->getMessage());
        }
    } else {
        $error = "Invalid or missing Station ID provided.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $stationModel && $station_id !== null) {
    $posted_station_name = trim($_POST['station_name'] ?? '');
    $posted_station_code = trim($_POST['station_code'] ?? '');
    $posted_city = trim($_POST['city'] ?? '');
    $posted_is_active = isset($_POST['is_active']) ? 1 : 0;

    $form_data = [
        'station_id' => $station_id,
        'station_name' => $posted_station_name,
        'station_code' => $posted_station_code,
        'city' => $posted_city,
        'is_active' => $posted_is_active
    ];

    try {
        if (empty($posted_station_name) || empty($posted_station_code) || empty($posted_city)) {
            throw new InvalidArgumentException("Station Name, Station Code, and City are required.");
        }
        if (strlen($posted_station_name) > 100) {
            throw new InvalidArgumentException("Station Name must be 100 characters or less.");
        }
        if (strlen($posted_station_code) > 10) {
            throw new InvalidArgumentException("Station code must be 10 characters or less.");
        }
        if (strlen($posted_city) > 100) {
            throw new InvalidArgumentException("City must be 100 characters or less.");
        }
        if (!preg_match('/^[A-Z0-9]+$/i', $posted_station_code)) {
            throw new InvalidArgumentException("Station code can only contain letters and numbers.");
        }

        $update_success = $stationModel->updateStation(
            $station_id,
            $posted_station_name,
            $posted_station_code,
            $posted_city,
            $posted_is_active
        );

        if ($update_success) {
            $_SESSION['flash_message'] = "Station '" . htmlspecialchars($posted_station_name) . "' (ID: " . htmlspecialchars($station_id) . ") updated successfully!";
            header("Location: manage_stations.php");
            exit;
        } else {
            throw new RuntimeException($stationModel->getLastError() ?: "Failed to update station.");
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    } catch (Exception $e) {
        $error = "An unexpected error occurred: " . $e->getMessage();
        error_log("Edit Station Error (update): " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Station</title>
    <link href="../public/assets/css/bootstrap.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: #198754;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Edit Station (ID: <?= htmlspecialchars($station_id ?? 'N/A') ?>)</h2>
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

                        <?php if ($form_data): ?>
                            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?id=<?= htmlspecialchars($station_id) ?>">
                                <div class="mb-3">
                                    <label for="station_name" class="form-label">Station Name</label>
                                    <input type="text" class="form-control" id="station_name" name="station_name"
                                        value="<?= htmlspecialchars($form_data['station_name'] ?? '') ?>" required maxlength="100">
                                </div>
                                <div class="mb-3">
                                    <label for="station_code" class="form-label">Station Code</label>
                                    <input type="text" class="form-control" id="station_code" name="station_code"
                                        value="<?= htmlspecialchars($form_data['station_code'] ?? '') ?>" required
                                        maxlength="10" pattern="[A-Za-z0-9]+" title="Only letters and numbers, max 10 characters">
                                    <small class="text-muted">Short code (e.g., GCT, PENN). Max 10 alphanumeric characters.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="city" class="form-label">City (Location)</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="<?= htmlspecialchars($form_data['city'] ?? '') ?>" required maxlength="100">
                                    <small class="text-muted">The city where the station is located.</small>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                        <?= ($form_data['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Active Station</label>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="manage_stations.php" class="btn btn-secondary">Cancel / Back to List</a>
                                    <button type="submit" class="btn btn-primary">Update Station</button>
                                </div>
                            </form>
                        <?php elseif (!$error): ?>
                            <p class="text-center">Please provide a valid station ID via the <a href="manage_stations.php">stations list</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>