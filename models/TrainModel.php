<?php

class TrainModel
{
    private $db; 
    private $trains_table = "trains";
    private $stations_table = "stations";
    private $lastError = '';

    public function __construct(DBController $db)
    {
        if (!($db instanceof DBController)) {
            throw new InvalidArgumentException("TrainModel constructor expects DBController instance.");
        }
        $this->db = $db;
    }

    public function getLastError()
    {
        $err = $this->lastError;
        $this->lastError = '';
        if (empty($err)) return $this->db->getLastError();
        return $err;
    }
    private function setLastError($msg)
    {
        $this->lastError = $msg;
        error_log("TrainModel Error: " . $msg);
    }

    public function getActiveTrainsForRoute($source_station_id, $destination_station_id, $journey_date_string)
    {
        $this->setLastError('');
        if (!is_numeric($source_station_id) || !is_numeric($destination_station_id) || empty($journey_date_string)) {
            $this->setLastError("Invalid parameters for getActiveTrainsForRoute.");
            return []; 
        }

        try {
            $day_of_week = date('w', strtotime($journey_date_string)); 

            $query = "SELECT
                        t.train_id, t.train_number, t.train_name,
                        s_source.station_name AS source_station_name,
                        s_dest.station_name AS destination_station_name,
                        TIME_FORMAT(t.departure_time, '%h:%i %p') as departure_time_formatted,
                        t.departure_time as departure_time_value,
                        TIME_FORMAT(t.arrival_time, '%h:%i %p') as arrival_time_formatted,
                        t.arrival_time as arrival_time_value
                      FROM {$this->trains_table} t
                      JOIN {$this->stations_table} s_source ON t.source_station_id = s_source.station_id
                      JOIN {$this->stations_table} s_dest ON t.destination_station_id = s_dest.station_id
                      WHERE t.source_station_id = ?
                        AND t.destination_station_id = ?
                        AND t.is_active = TRUE  
                        AND FIND_IN_SET(?, t.running_days) > 0
                      ORDER BY t.departure_time ASC";

            $params = [$source_station_id, $destination_station_id, (string)$day_of_week];
            $result = $this->db->select($query, $params);

            if ($result === false) {
                $this->setLastError("DB error fetching trains for route: " . $this->db->getLastError());
                return []; 
            }
            return $result; 

        } catch (Exception $e) {
            $this->setLastError("Exception fetching trains: " . $e->getMessage());
            return [];
        }
    }

    public function getTrainDetailsByIdForBooking($train_id, $source_station_id, $destination_station_id)
    {
        $this->setLastError('');
        if (!is_numeric($train_id) || !is_numeric($source_station_id) || !is_numeric($destination_station_id)) {
            $this->setLastError("Invalid parameters for getTrainDetailsByIdForBooking.");
            return false;
        }
        $query = "SELECT train_id, train_number, train_name, departure_time as departure_time_value
                  FROM {$this->trains_table}
                  WHERE train_id = ?
                    AND source_station_id = ?
                    AND destination_station_id = ?
                    AND is_active = TRUE -- or 1
                  LIMIT 1";
        $params = [$train_id, $source_station_id, $destination_station_id];
        $result = $this->db->select($query, $params);

        if ($result === false) {
            $this->setLastError("DB error fetching train details: " . $this->db->getLastError());
            return false;
        }
        return !empty($result) ? $result[0] : false; 
    }
}
