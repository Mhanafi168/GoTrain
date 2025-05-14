<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__, 3));

require_once '../../config/DBController.php';
require_once '../../models/Station.php';
require_once '../../models/StationDistance.php';

$page_message = $_SESSION['flash_message'] ?? null;
$page_message_type = $_SESSION['flash_message_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);

$dbController = null;
$stationModel = null;
$stationDistanceModel = null;

$stations_for_dropdown = [];
$station_distances = [];

try {
    $dbController = DBController::getInstance();
    $stationModel = new Station($dbController);
    $stationDistanceModel = new StationDistance($dbController);

    $stations_for_dropdown_result = $stationModel->getAllActiveStations();
    if ($stations_for_dropdown_result) {
        $stations_for_dropdown = $stations_for_dropdown_result;
    } else {
        $page_message = ($page_message ? $page_message . "<br>" : "") . "Warning: Could not load stations for dropdowns. " . htmlspecialchars($stationModel->getLastError());
        $page_message_type = 'warning';
    }

    $station_distances_result = $stationDistanceModel->getAllWithStationNames();
    if ($station_distances_result === false) {
        $page_message = ($page_message ? $page_message . "<br>" : "") . "Error fetching station distances: " . htmlspecialchars($stationDistanceModel->getLastError());
        $page_message_type = 'danger';
    } else {
        $station_distances = $station_distances_result;
    }
} catch (Exception $e) {
    error_log("Error on Station Distance Management Page (Setup): " . $e->getMessage());
    $page_message = "A critical setup error occurred: " . htmlspecialchars($e->getMessage());
    $page_message_type = 'danger';
    $stations_for_dropdown = [];
    $station_distances = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbController && $stationDistanceModel) {
    $action = $_POST['action'] ?? '';
    $redirect = true;

    try {
        if ($action === 'manage_station_distance') {
            $distance_data = [
                'distance_id' => filter_input(INPUT_POST, 'distance_id_edit', FILTER_VALIDATE_INT) ?: null,
                'station1_id' => filter_input(INPUT_POST, 'distance_station1_id', FILTER_VALIDATE_INT),
                'station2_id' => filter_input(INPUT_POST, 'distance_station2_id', FILTER_VALIDATE_INT),
                'distance' => filter_input(INPUT_POST, 'distance_value', FILTER_VALIDATE_FLOAT)
            ];

            if ($distance_data['station1_id'] && $distance_data['station2_id'] && $distance_data['distance'] !== false && $distance_data['distance'] >= 0) {
                if ($distance_data['station1_id'] == $distance_data['station2_id']) {
                    $_SESSION['flash_message'] = "Cannot set distance between a station and itself.";
                    $_SESSION['flash_message_type'] = 'danger';
                } elseif ($stationDistanceModel->createOrUpdate($distance_data)) {
                    $_SESSION['flash_message'] = "Station distance saved successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to save station distance: " . htmlspecialchars($stationDistanceModel->getLastError());
                    $_SESSION['flash_message_type'] = 'danger';
                }
            } else {
                $_SESSION['flash_message'] = "Invalid data for station distance. Please select two different stations and enter a valid distance.";
                $_SESSION['flash_message_type'] = 'danger';
            }
        } elseif ($action === 'delete_station_distance' && isset($_POST['distance_id'])) {
            $distance_id_to_delete = filter_input(INPUT_POST, 'distance_id', FILTER_VALIDATE_INT);
            if ($distance_id_to_delete) {
                if ($stationDistanceModel->delete($distance_id_to_delete)) {
                    $_SESSION['flash_message'] = "Station distance (ID: " . htmlspecialchars($distance_id_to_delete) . ") deleted.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to delete station distance: " . htmlspecialchars($stationDistanceModel->getLastError());
                    $_SESSION['flash_message_type'] = 'danger';
                }
            } else {
                $_SESSION['flash_message'] = "Invalid ID for station distance deletion.";
                $_SESSION['flash_message_type'] = 'danger';
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "An error occurred: " . htmlspecialchars($e->getMessage());
        $_SESSION['flash_message_type'] = 'danger';
        error_log("POST Error on Station Distance Management: " . $e->getMessage());
    }

    if ($redirect) {
        header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Station Distances - GoTrain Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 960px;
        }

        .card-header {
            background-color: #17a2b8 !important;
            color: white;
        }

        .card-header h5 {
            margin-bottom: 0;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .action-buttons button,
        .action-buttons form {
            margin-right: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Manage Station Distances</h1>
            <a href="admin.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Admin Dashboard
            </a>
        </div>


        <?php if ($page_message): ?>
            <div class="alert alert-<?= htmlspecialchars($page_message_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($page_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-rulers"></i> Station Distances List</h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#manageStationDistanceModal" onclick="resetDistanceModal()">
                    <i class="bi bi-plus-circle"></i> Add/Update Distance
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($station_distances)): ?>
                    <p class="text-muted">No station distances have been defined yet. Click "Add/Update Distance" to begin.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Station 1</th>
                                    <th>Station 2</th>
                                    <th class="text-end">Distance (KM)</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($station_distances as $dist): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($dist['distance_id']) ?></td>
                                        <td><?= htmlspecialchars($dist['station1_name']) ?> <small class="text-muted">(ID: <?= htmlspecialchars($dist['station1_id']) ?>)</small></td>
                                        <td><?= htmlspecialchars($dist['station2_name']) ?> <small class="text-muted">(ID: <?= htmlspecialchars($dist['station2_id']) ?>)</small></td>
                                        <td class="text-end"><?= htmlspecialchars(number_format((float)$dist['distance'], 2)) ?></td>
                                        <td class="text-center action-buttons">
                                            <button class="btn btn-sm btn-outline-info py-0 px-1" title="Edit Distance"
                                                onclick="editDistanceModal(
                                                        '<?= htmlspecialchars($dist['distance_id']) ?>',
                                                        '<?= htmlspecialchars($dist['station1_id']) ?>',
                                                        '<?= htmlspecialchars(addslashes($dist['station1_name'])) ?>',
                                                        '<?= htmlspecialchars($dist['station2_id']) ?>',
                                                        '<?= htmlspecialchars(addslashes($dist['station2_name'])) ?>',
                                                        '<?= htmlspecialchars((float)$dist['distance']) ?>'
                                                    )">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this distance entry? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete_station_distance">
                                                <input type="hidden" name="distance_id" value="<?= htmlspecialchars($dist['distance_id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete Distance">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageStationDistanceModal" tabindex="-1" aria-labelledby="manageStationDistanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="manage_station_distance">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manageStationDistanceModalLabel">Add/Update Station Distance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="distance_station1_id" class="form-label">Station 1 <span class="text-danger">*</span></label>
                            <select class="form-select" id="distance_station1_id" name="distance_station1_id" required>
                                <option value="">Select Station 1</option>
                                <?php foreach ($stations_for_dropdown as $station): ?>
                                    <option value="<?= htmlspecialchars($station['station_id']) ?>"><?= htmlspecialchars($station['station_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="distance_station2_id" class="form-label">Station 2 <span class="text-danger">*</span></label>
                            <select class="form-select" id="distance_station2_id" name="distance_station2_id" required>
                                <option value="">Select Station 2</option>
                                <?php foreach ($stations_for_dropdown as $station): ?>
                                    <option value="<?= htmlspecialchars($station['station_id']) ?>"><?= htmlspecialchars($station['station_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="distance_value" class="form-label">Distance (e.g., in KM) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="distance_value" name="distance_value" step="0.01" min="0" required>
                        </div>
                        <small class="form-text text-muted">If a distance for this pair of stations already exists, it will be updated.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Distance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetDistanceModal() {
            document.getElementById('manageStationDistanceModalLabel').textContent = 'Add New Station Distance';
            const form = document.getElementById('manageStationDistanceModal').querySelector('form');
            form.reset();

        }

        function editDistanceModal(distance_id, station1_id, station1_name, station2_id, station2_name, distance) {
            document.getElementById('manageStationDistanceModalLabel').textContent = 'Update Distance: ' + station1_name + ' ↔ ' + station2_name;

            const station1Select = document.getElementById('distance_station1_id');
            const station2Select = document.getElementById('distance_station2_id');
            const distanceInput = document.getElementById('distance_value');

            station1Select.value = station1_id;
            station2Select.value = station2_id;
            distanceInput.value = parseFloat(distance);

            var distanceModal = new bootstrap.Modal(document.getElementById('manageStationDistanceModal'));
            distanceModal.show();
        }
    </script>
</body>

</html>