<?php

class StationDistance
{
    private $db;
    private $table = "station_distances";
    private $lastError = '';

    public function __construct(DBController $dbController)
    {
        if (!($dbController instanceof DBController)) {
            throw new InvalidArgumentException("StationDistance constructor expects an instance of DBController.");
        }
        $this->db = $dbController;
    }

    public function getLastError()
    {
        $err = $this->lastError;
        $this->lastError = '';
        if (empty($err) && $this->db instanceof DBController) {
            return $this->db->getLastError();
        }
        return $err;
    }

    private function setLastError($msg)
    {
        $this->lastError = $msg;
        error_log("StationDistanceModel Error: " . $msg);
    }

    public function getAllWithStationNames()
    {
        $this->setLastError('');
        $query = "SELECT
                    sd.distance_id,
                    sd.station1_id,
                    s1.station_name as station1_name,
                    sd.station2_id,
                    s2.station_name as station2_name,
                    sd.distance,
                    sd.created_at
                  FROM " . $this->table . " sd
                  JOIN stations s1 ON sd.station1_id = s1.station_id
                  JOIN stations s2 ON sd.station2_id = s2.station_id
                  ORDER BY s1.station_name ASC, s2.station_name ASC";
        try {
            $result = $this->db->select($query);
            if ($result === false) {
                $this->setLastError("GetAllWithStationNames DB Error: " . $this->db->getLastError());
                return false;
            }
            return $result;
        } catch (Exception $e) {
            $this->setLastError("GetAllWithStationNames Exception: " . $e->getMessage());
            return false;
        }
    }

    public function getById($distance_id)
    {
        $this->setLastError('');
        if (!is_numeric($distance_id) || $distance_id <= 0) {
            $this->setLastError("Invalid distance_id provided.");
            return false;
        }
        $query = "SELECT * FROM " . $this->table . " WHERE distance_id = ? LIMIT 1";
        $params = [$distance_id];
        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("GetById DB Error: " . $this->db->getLastError());
                return false;
            }
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            $this->setLastError("GetById Exception: " . $e->getMessage());
            return false;
        }
    }

    public function getDistance($station1_id, $station2_id)
    {
        $this->setLastError('');
        if (!is_numeric($station1_id) || $station1_id <= 0 || !is_numeric($station2_id) || $station2_id <= 0) {
            $this->setLastError("Invalid station IDs provided for getDistance.");
            return false;
        }
        if ($station1_id == $station2_id) {
            return 0.0;
        }
        if ($station1_id > $station2_id) {
            list($station1_id, $station2_id) = [$station2_id, $station1_id];
        }
        $query = "SELECT distance FROM " . $this->table . "
                  WHERE station1_id = ? AND station2_id = ?
                  LIMIT 1";
        $params = [$station1_id, $station2_id];
        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("getDistance DB Error: " . $this->db->getLastError());
                return false;
            }
            return !empty($result) ? (float)$result[0]['distance'] : null;
        } catch (Exception $e) {
            $this->setLastError("getDistance Exception: " . $e->getMessage());
            return false;
        }
    }

    public function createOrUpdate(array $data)
    {
        $this->setLastError('');
        if (
            !isset($data['station1_id'], $data['station2_id'], $data['distance']) ||
            !is_numeric($data['station1_id']) || !is_numeric($data['station2_id']) || !is_numeric($data['distance']) ||
            (int)$data['station1_id'] <= 0 || (int)$data['station2_id'] <= 0 || (float)$data['distance'] < 0
        ) {
            $this->setLastError("Invalid data provided for createOrUpdate station distance (IDs must be positive, distance non-negative).");
            return false;
        }
        if ($data['station1_id'] == $data['station2_id']) {
            $this->setLastError("Cannot set distance between a station and itself.");
            return false;
        }

        $station1_id = (int)$data['station1_id'];
        $station2_id = (int)$data['station2_id'];
        $distance = (float)$data['distance'];

        if ($station1_id > $station2_id) {
            list($station1_id, $station2_id) = [$station2_id, $station1_id];
        }

        $existingRecord = $this->getDistanceRecordByStationPair($station1_id, $station2_id);

        if ($existingRecord) {
            $query = "UPDATE " . $this->table . " SET distance = ? WHERE distance_id = ?";
            $params = [$distance, $existingRecord['distance_id']];
            if ($this->db->execute($query, $params)) {
                return $existingRecord['distance_id'];
            } else {
                $this->setLastError("Update Station Distance Error: " . $this->db->getLastError());
                return false;
            }
        } else {
            $query = "INSERT INTO " . $this->table . " (station1_id, station2_id, distance) VALUES (?, ?, ?)";
            $params = [$station1_id, $station2_id, $distance];
            $lastInsertId = $this->db->insert($query, $params);
            if ($lastInsertId !== false && $lastInsertId > 0) {
                return $lastInsertId;
            } else {
                $this->setLastError("Create Station Distance Error: " . $this->db->getLastError());
                return false;
            }
        }
    }

    private function getDistanceRecordByStationPair($station1_id, $station2_id)
    {
        $query = "SELECT distance_id, distance FROM " . $this->table . "
                  WHERE station1_id = ? AND station2_id = ? LIMIT 1";
        $params = [$station1_id, $station2_id];
        $result = $this->db->select($query, $params);
        return ($result && !empty($result)) ? $result[0] : false;
    }

    public function delete($distance_id)
    {
        $this->setLastError('');
        if (!is_numeric($distance_id) || $distance_id <= 0) {
            $this->setLastError("Invalid distance_id for delete.");
            return false;
        }
        $query = "DELETE FROM " . $this->table . " WHERE distance_id = ?";
        $params = [$distance_id];
        try {
            if ($this->db->execute($query, $params)) {
                return true;
            }
            $this->setLastError("Delete Station Distance Error: " . $this->db->getLastError());
            return false;
        } catch (Exception $e) {
            $this->setLastError("Delete Station Distance Exception: " . $e->getMessage());
            return false;
        }
    }
}
