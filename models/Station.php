<?php
class Station
{
    private $db;
    private $table_name = "stations";
    private $lastError = '';

    private $col_id = "station_id";
    private $col_name = "station_name";
    private $col_code = "station_code";
    private $col_city = "city";

    public function __construct($dbConnection)
    {
        if (!($dbConnection instanceof DBController) && !($dbConnection instanceof PDO)) {
            throw new InvalidArgumentException("Station constructor expects either DBController or PDO instance.");
        }
        $this->db = $dbConnection;
    }
    public function getLastError()
    {
        $err = $this->lastError;
        $this->lastError = '';
        if (empty($err)) {
            return $this->db->getLastError();
        }
        return $err;
    }

    private function setLastError($msg)
    {
        $this->lastError = $msg;
        error_log("StationModel Error: " . $msg);
    }

    public function getAll()
    {
        return $this->getAllStations();
    }

    public function getAllStations()
    {
        $this->setLastError('');
        $query = "SELECT {$this->col_id} as station_id,
                         {$this->col_name} as station_name,
                         {$this->col_code} as station_code,
                         {$this->col_city} as city
                  FROM {$this->table_name}
                  ORDER BY {$this->col_name} ASC";

        try {
            $result = $this->db->select($query);
            if ($result === false) {
                $this->setLastError("DB Error fetching stations: " . $this->db->getLastError());
                return false;
            }
            return $result;
        } catch (Exception $e) {
            $this->setLastError("Exception fetching stations: " . $e->getMessage());
            return false;
        }
    }


    public function getAllActiveStations()
    {
        $this->setLastError('');
        $query = "SELECT {$this->col_id} as station_id,
                         {$this->col_name} as station_name,
                         {$this->col_code} as station_code,
                         {$this->col_city} as city
                  FROM {$this->table_name}
                  WHERE is_active = TRUE
                  ORDER BY {$this->col_name} ASC";

        try {
            $result = $this->db->select($query);
            if ($result === false) {
                $this->setLastError("DB Error fetching active stations: " . $this->db->getLastError());
                return false;
            }
            return $result;
        } catch (Exception $e) {
            $this->setLastError("Exception fetching active stations: " . $e->getMessage());
            return false;
        }
    }

    public function getStationById($station_id)
    {
        $this->setLastError('');
        if (!is_numeric($station_id) || $station_id <= 0) {
            $this->setLastError("Invalid station ID provided.");
            return false;
        }
        $query = "SELECT * FROM {$this->table_name} WHERE {$this->col_id} = ? LIMIT 1";
        $params = [(int)$station_id];
        $result = $this->db->select($query, $params);
        if ($result === false) {
            $this->setLastError("DB Error getting station by ID: " . $this->db->getLastError());
            return false;
        }
        return !empty($result) ? $result[0] : false;
    }

    public function addStation($station_name, $station_code, $city, $is_active = 1, $other_data = [])
    {
        $this->setLastError('');
        if (empty($station_name) || empty($station_code) || empty($city)) {
            $this->setLastError("Station name, code, and city are required.");
            return false;
        }

        $query = "INSERT INTO {$this->table_name}
                    (station_name, station_code, city, is_active)
                  VALUES (?, ?, ?, ?)";
        $params = [$station_name, $station_code, $city, (int)$is_active];

        $lastInsertId = $this->db->insert($query, $params);
        if ($lastInsertId !== false && $lastInsertId > 0) {
            return $lastInsertId;
        } else {
            $this->setLastError("Failed to add station. DB Error: " . $this->db->getLastError());
            return false;
        }
    }

    public function getStationOptions()
    {
        $stations = $this->getAllActiveStations();
        if ($stations === false) {
            return false;
        }

        $options = [];
        foreach ($stations as $station) {
            $options[] = [
                'station_id' => $station['station_id'],
                'station_name' => $station['station_name'],
                'station_code' => $station['station_code'] ?? '',
                'city' => $station['city'] ?? ''
            ];
        }
        return $options;
    }
    public function updateStation($station_id, $station_name, $station_code, $city, $is_active = 1)
    {
        $this->setLastError('');

        if (!is_numeric($station_id) || $station_id <= 0) {
            $this->setLastError("Invalid station ID provided.");
            return false;
        }

        if (empty($station_name) || empty($station_code) || empty($city)) {
            $this->setLastError("Station name, code, and city are required.");
            return false;
        }

        $query = "UPDATE {$this->table_name} 
              SET {$this->col_name} = ?, 
                  {$this->col_code} = ?, 
                  {$this->col_city} = ?, 
                  is_active = ?,
                  updated_at = CURRENT_TIMESTAMP()
              WHERE {$this->col_id} = ?";

        $params = [$station_name, $station_code, $city, (int)$is_active, (int)$station_id];

        try {
            $result = $this->db->execute($query, $params);

            if ($result === false) {
                $this->setLastError("DB Error updating station: " . $this->db->getLastError());
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->setLastError("Exception updating station: " . $e->getMessage());
            return false;
        }
    }

    public function stationExists($station_id)
    {
        $this->setLastError('');

        if (!is_numeric($station_id) || $station_id <= 0) {
            $this->setLastError("Invalid station ID provided.");
            return false;
        }

        $query = "SELECT COUNT(*) as count 
              FROM {$this->table_name} 
              WHERE {$this->col_id} = ?";

        $params = [(int)$station_id];

        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("DB Error checking station existence: " . $this->db->getLastError());
                return false;
            }
            return (!empty($result) && $result[0]['count'] > 0);
        } catch (Exception $e) {
            $this->setLastError("Exception checking station existence: " . $e->getMessage());
            return false;
        }
    }
    public function deleteStation($station_id)
    {
        $this->setLastError('');

        if (!is_numeric($station_id) || $station_id <= 0) {
            $this->setLastError("Invalid station ID provided.");
            return false;
        }

        if (!$this->stationExists($station_id)) {
            $this->setLastError("Station with ID $station_id does not exist.");
            return false;
        }

        $query = "DELETE FROM {$this->table_name} WHERE {$this->col_id} = ?";
        $params = [(int)$station_id];

        try {
            $result = $this->db->execute($query, $params);

            if ($result === false) {
                $this->setLastError("DB Error deleting station: " . $this->db->getLastError());
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->setLastError("Exception deleting station: " . $e->getMessage());
            return false;
        }
    }
}
