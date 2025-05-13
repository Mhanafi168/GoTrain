<?php
require_once '../../config/DBController.php';
require_once '../../models/Station.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$station_id = null;
$error_message = '';
$stationModel = null;

try {
    $db = DBController::getInstance();
    if (!$db->openConnection()) {
        throw new RuntimeException("Database connection failed. Please check configuration or contact support.");
    }
    $stationModel = new Station($db);
} catch (RuntimeException $e) {
    error_log("Delete Station DB/Model Init Error: " . $e->getMessage());
    $_SESSION['error_message'] = "A critical error occurred during setup. Please try again later or contact support.";
    header("Location: manage_stations.php");
    exit();
}


if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $station_id = (int)$_GET['id'];
} else {
    $error_message = "Invalid or missing Station ID provided.";
}

if ($station_id !== null && $stationModel) {
    try {
        if (!$stationModel->stationExists($station_id)) {
            throw new RuntimeException("Station with ID " . htmlspecialchars($station_id) . " not found. Cannot delete.");
        }

        $delete_success = $stationModel->deleteStation($station_id);

        if ($delete_success) {
            $_SESSION['flash_message'] = "Station (ID: " . htmlspecialchars($station_id) . ") deleted successfully.";
        } else {
            throw new RuntimeException("Failed to delete station (ID: " . htmlspecialchars($station_id) . "). The database operation did not succeed. It might be referenced elsewhere.");
        }
    } catch (RuntimeException $e) {
        $error_message = "Operation Error: " . $e->getMessage();
        error_log("Delete Station Operation Error (ID: $station_id): " . $e->getMessage());
    } catch (Exception $e) {
        $error_message = "An unexpected error occurred during deletion: " . $e->getMessage();
        error_log("Delete Station Unexpected Error (ID: $station_id): " . $e->getMessage());
    }
}

if (!empty($error_message)) {
    $_SESSION['error_message'] = $error_message;
}


header("Location: manage_stations.php");
exit();
