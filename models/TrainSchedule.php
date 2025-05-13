<?php
class TrainSchedule
{
    private $conn;
    private $table_name = "train_schedule";

    public function __construct($db)
    {
        if (!is_object($db)) {
            throw new InvalidArgumentException("Database connection must be an object");
        }

        if ($db instanceof PDO) {
            $this->conn = $db;
        } elseif ($db instanceof mysqli) {
            $this->conn = $db;
        } elseif (method_exists($db, 'getConnection')) {
            $connection = $db->getConnection();
            if ($connection instanceof PDO || $connection instanceof mysqli) {
                $this->conn = $connection;
            }
        }

        if (!$this->conn) {
            throw new RuntimeException("Invalid database connection provided");
        }

        if ($this->conn instanceof mysqli && !$this->conn->ping()) {
            throw new RuntimeException("Database connection is not active");
        }
    }

    public function getAllSchedules()
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY expected_departure_time DESC";

            if ($this->conn instanceof PDO) {
                $stmt = $this->conn->query($query);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = $this->conn->query($query);
                if (!$result) {
                    throw new RuntimeException("Query failed: " . $this->conn->error);
                }
                return $result->fetch_all(MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error getting all schedules: " . $e->getMessage());
            return false;
        }
    }

    public function addSchedule(
        $train_number,
        $source_station,
        $destination_station,
        $expected_departure_time,
        $expected_arrival_time,
        $actual_departure_time,
        $actual_arrival_time,
        $status
    ) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                (train_number, source_station, destination_station, 
                 expected_departure_time, expected_arrival_time, 
                 actual_departure_time, actual_arrival_time, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            if ($this->conn instanceof PDO) {
                $stmt = $this->conn->prepare($query);
                return $stmt->execute([
                    $train_number,
                    $source_station,
                    $destination_station,
                    $expected_departure_time,
                    $expected_arrival_time,
                    $actual_departure_time,
                    $actual_arrival_time,
                    $status
                ]);
            } else {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new RuntimeException("Prepare failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "ssssssss",
                    $train_number,
                    $source_station,
                    $destination_station,
                    $expected_departure_time,
                    $expected_arrival_time,
                    $actual_departure_time,
                    $actual_arrival_time,
                    $status
                );

                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error adding schedule: " . $e->getMessage());
            return false;
        }
    }

    public function getScheduleById($id)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";

            if ($this->conn instanceof PDO) {
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$id]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new RuntimeException("Prepare failed: " . $this->conn->error);
                }

                $stmt->bind_param("i", $id);
                $stmt->execute();

                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }
        } catch (Exception $e) {
            error_log("Error getting schedule by ID: " . $e->getMessage());
            return false;
        }
    }

    public function updateSchedule(
        $id,
        $train_number,
        $source_station,
        $destination_station,
        $expected_departure_time,
        $expected_arrival_time,
        $actual_departure_time,
        $actual_arrival_time,
        $status
    ) {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET train_number = ?, source_station = ?, destination_station = ?, 
                          expected_departure_time = ?, expected_arrival_time = ?, 
                          actual_departure_time = ?, actual_arrival_time = ?, 
                          status = ?
                      WHERE id = ?";

            if ($this->conn instanceof PDO) {
                $stmt = $this->conn->prepare($query);
                return $stmt->execute([
                    $train_number,
                    $source_station,
                    $destination_station,
                    $expected_departure_time,
                    $expected_arrival_time,
                    $actual_departure_time,
                    $actual_arrival_time,
                    $status,
                    $id
                ]);
            } else {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new RuntimeException("Prepare failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "ssssssssi",
                    $train_number,
                    $source_station,
                    $destination_station,
                    $expected_departure_time,
                    $expected_arrival_time,
                    $actual_departure_time,
                    $actual_arrival_time,
                    $status,
                    $id
                );

                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error updating schedule: " . $e->getMessage());
            return false;
        }
    }

    public function updateArrivalDepartureTimes(
        $id,
        $expected_departure_time,
        $expected_arrival_time,
        $actual_departure_time,
        $actual_arrival_time,
        $status
    ) {
        try {
            $query = "UPDATE " . $this->table_name . "
                      SET actual_departure_time = ?, actual_arrival_time = ?, status = ?
                      WHERE id = ?";

            if ($this->conn instanceof PDO) {
                $stmt = $this->conn->prepare($query);
                return $stmt->execute([
                    $actual_departure_time,
                    $actual_arrival_time,
                    $status,
                    $id
                ]);
            } else {
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new RuntimeException("Prepare failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "sssi",
                    $actual_departure_time,
                    $actual_arrival_time,
                    $status,
                    $id
                );

                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error updating arrival/departure times: " . $e->getMessage());
            return false;
        }
    }
}
