<?php

class PricingRule
{
    private $db;
    private $table = "pricing_rules";
    private $lastError = '';

    const BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION = 'SYSTEM_BASE_RATE_PER_DISTANCE';

    public function __construct(DBController $dbController)
    {
        if (!($dbController instanceof DBController)) {
            throw new InvalidArgumentException("PricingRule constructor expects an instance of DBController.");
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
        error_log("PricingRuleModel Error: " . $msg);
    }

    public function getBaseRatePerDistance()
    {
        $this->setLastError('');
        $query = "SELECT base_fare FROM " . $this->table . " WHERE description = ? LIMIT 1";
        $params = [self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION];
        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("Failed to get base rate: " . $this->db->getLastError());
                return 0.15;
            }
            return !empty($result) ? (float)$result[0]['base_fare'] : 0.15;
        } catch (Exception $e) {
            $this->setLastError("GetBaseRate Exception: " . $e->getMessage());
            return 0.15;
        }
    }

    public function setBaseRatePerDistance($rate)
    {
        $this->setLastError('');
        if (!is_numeric($rate) || $rate < 0) {
            $this->setLastError("Invalid rate provided for base distance rate.");
            return false;
        }
        $rate = (float)$rate;
        $description = self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION;

        $checkQuery = "SELECT rule_id FROM " . $this->table . " WHERE description = ? LIMIT 1";
        $existingResult = $this->db->select($checkQuery, [$description]);

        if ($existingResult === false) {
            $this->setLastError("Error checking existing base rate: " . $this->db->getLastError());
            return false;
        }

        if (!empty($existingResult)) {
            $existingRuleId = $existingResult[0]['rule_id'];
            $query = "UPDATE " . $this->table . " SET base_fare = ?, class='SYSTEM', ticket_type='SYSTEM', is_active=1, updated_at=NOW() WHERE rule_id = ?";
            $params = [$rate, $existingRuleId];
            if ($this->db->execute($query, $params)) {
                return true;
            }
            $this->setLastError("SetBaseRate (Update) Error: " . $this->db->getLastError());
            return false;
        } else {
            $query = "INSERT INTO " . $this->table . " (description, base_fare, class, ticket_type, is_active, source_station_id, destination_station_id, created_at, updated_at)
                      VALUES (?, ?, 'SYSTEM', 'SYSTEM', 1, NULL, NULL, NOW(), NOW())";
            $params = [$description, $rate];
            $insertId = $this->db->insert($query, $params);
            if ($insertId !== false) {
                return true;
            }
            $this->setLastError("SetBaseRate (Insert) Error: " . $this->db->getLastError());
            return false;
        }
    }

    public function getAllClassRules()
    {
        $this->setLastError('');
        $query = "SELECT pr.*,
                    s_source.station_name as source_station_name,
                    s_dest.station_name as destination_station_name
                  FROM " . $this->table . " pr
                  LEFT JOIN stations s_source ON pr.source_station_id = s_source.station_id
                  LEFT JOIN stations s_dest ON pr.destination_station_id = s_dest.station_id
                  WHERE pr.description != ? OR pr.description IS NULL
                  ORDER BY pr.class ASC, pr.is_active DESC, pr.source_station_id ASC, pr.destination_station_id ASC";
        $params = [self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION];
        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("GetAllClassRules DB Error: " . $this->db->getLastError());
                return false;
            }
            return $result;
        } catch (Exception $e) {
            $this->setLastError("GetAllClassRules Exception: " . $e->getMessage());
            return false;
        }
    }

    public function getClassRuleById($rule_id)
    {
        $this->setLastError('');
        if (!is_numeric($rule_id) || $rule_id <= 0) {
            $this->setLastError("Invalid rule_id for getClassRuleById.");
            return false;
        }
        $query = "SELECT * FROM " . $this->table . " WHERE rule_id = ? AND (description != ? OR description IS NULL) LIMIT 1";
        $params = [$rule_id, self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION];
        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("GetClassRuleById DB Error: " . $this->db->getLastError());
                return false;
            }
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            $this->setLastError("GetClassRuleById Exception: " . $e->getMessage());
            return false;
        }
    }

    public function createClassRule($data)
    {
        $this->setLastError('');
        if (empty($data['class_name']) || empty($data['adjustment_type']) || !isset($data['value'])) {
            $this->setLastError("Missing required fields for creating class rule.");
            return false;
        }
        if (!is_numeric($data['value'])) {
            $this->setLastError("Rule value must be numeric.");
            return false;
        }

        $source_station_id = (!empty($data['source_station_id']) && is_numeric($data['source_station_id'])) ? (int)$data['source_station_id'] : null;
        $destination_station_id = (!empty($data['destination_station_id']) && is_numeric($data['destination_station_id'])) ? (int)$data['destination_station_id'] : null;
        $is_active = isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1;
        $description = $data['description'] ?? null;

        $query = "INSERT INTO " . $this->table . "
                  (class, ticket_type, base_fare, source_station_id, destination_station_id, description, is_active, created_at, updated_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $params = [
            $data['class_name'],
            $data['adjustment_type'],
            (float)$data['value'],
            $source_station_id,
            $destination_station_id,
            $description,
            $is_active
        ];

        try {
            $lastInsertId = $this->db->insert($query, $params);
            if ($lastInsertId !== false) {
                return ($lastInsertId === true) ? true : $lastInsertId;
            }
            $this->setLastError("CreateClassRule DB Error: " . $this->db->getLastError());
            return false;
        } catch (Exception $e) {
            $this->setLastError("CreateClassRule Exception: " . $e->getMessage());
            return false;
        }
    }

    public function updateClassRule($data)
    {
        $this->setLastError('');
        if (empty($data['rule_id']) || !is_numeric($data['rule_id']) || empty($data['class_name']) || empty($data['adjustment_type']) || !isset($data['value'])) {
            $this->setLastError("Missing required fields or invalid rule_id for updating class rule.");
            return false;
        }
        if (!is_numeric($data['value'])) {
            $this->setLastError("Rule value must be numeric.");
            return false;
        }

        $source_station_id = (!empty($data['source_station_id']) && is_numeric($data['source_station_id'])) ? (int)$data['source_station_id'] : null;
        $destination_station_id = (!empty($data['destination_station_id']) && is_numeric($data['destination_station_id'])) ? (int)$data['destination_station_id'] : null;
        $is_active = isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1;
        $description = $data['description'] ?? null;

        $query = "UPDATE " . $this->table . " SET
                    class = ?,
                    ticket_type = ?,
                    base_fare = ?,
                    source_station_id = ?,
                    destination_station_id = ?,
                    description = ?,
                    is_active = ?,
                    updated_at = NOW()
                  WHERE rule_id = ? AND (description != ? OR description IS NULL)";
        $params = [
            $data['class_name'],
            $data['adjustment_type'],
            (float)$data['value'],
            $source_station_id,
            $destination_station_id,
            $description,
            $is_active,
            (int)$data['rule_id'],
            self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION
        ];

        try {
            if ($this->db->execute($query, $params)) {
                return true;
            }
            $this->setLastError("UpdateClassRule DB Error: " . $this->db->getLastError());
            return false;
        } catch (Exception $e) {
            $this->setLastError("UpdateClassRule Exception: " . $e->getMessage());
            return false;
        }
    }

    public function deleteRule($rule_id)
    {
        $this->setLastError('');
        if (!is_numeric($rule_id) || $rule_id <= 0) {
            $this->setLastError("Invalid rule_id for delete.");
            return false;
        }
        $query = "DELETE FROM " . $this->table . " WHERE rule_id = ? AND (description != ? OR description IS NULL)";
        $params = [$rule_id, self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION];
        try {
            if ($this->db->execute($query, $params)) {
                return true;
            }
            $this->setLastError("DeleteRule DB Error: " . $this->db->getLastError());
            return false;
        } catch (Exception $e) {
            $this->setLastError("DeleteRule Exception: " . $e->getMessage());
            return false;
        }
    }

    public function findClassAdjustment($class, $source_id = null, $dest_id = null)
    {
        $this->setLastError('');
        if (empty($class)) {
            $this->setLastError("Class name cannot be empty for findClassAdjustment.");
            return false;
        }

        $sql_conditions = "class = ? AND is_active = 1 AND (description != ? OR description IS NULL)";
        $params = [$class, self::BASE_RATE_PER_DISTANCE_RULE_DESCRIPTION];
        $orderByPriority = [];

        $specific_route_condition = "";
        if ($source_id !== null && $dest_id !== null && is_numeric($source_id) && is_numeric($dest_id)) {
            $specific_route_condition = "(source_station_id = ? AND destination_station_id = ?)";
            $params[] = (int)$source_id;
            $params[] = (int)$dest_id;
        }

        $global_route_condition = "(source_station_id IS NULL AND destination_station_id IS NULL)";

        if (!empty($specific_route_condition)) {
            $sql_conditions .= " AND (" . $specific_route_condition . " OR " . $global_route_condition . ")";
            $orderByPriority[] = "CASE WHEN source_station_id = " . (int)$source_id . " AND destination_station_id = " . (int)$dest_id . " THEN 0 ELSE 1 END";
        } else {
            $sql_conditions .= " AND " . $global_route_condition;
        }

        $query = "SELECT ticket_type as adjustment_type, base_fare as value
                  FROM " . $this->table . "
                  WHERE " . $sql_conditions . "
                  ORDER BY " . (empty($orderByPriority) ? "rule_id" : implode(", ", $orderByPriority)) . " ASC
                  LIMIT 1";
        try {
            $result = $this->db->select($query, $params);
            if ($result === false) {
                $this->setLastError("FindClassAdjustment DB Error: " . $this->db->getLastError());
                return false;
            }
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            $this->setLastError("FindClassAdjustment Exception: " . $e->getMessage());
            return false;
        }
    }
}
