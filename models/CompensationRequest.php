<?php
class CompensationRequest
{
    private $db;
    private $table = 'compensation_requests';
    private $lastError;
    private $lastInsertId;

    public function __construct(PDO $pdo_connection)
    {
        $this->db = $pdo_connection;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }


    public function create($data)
    {
        $sql = "INSERT INTO " . $this->table . " 
                (user_id, booking_id, reason_for_request, detailed_description, status, request_date, created_at, updated_at) 
                VALUES (:user_id, :booking_id, :reason_for_request, :detailed_description, :status, NOW(), NOW(), NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':booking_id', $data['booking_id'], PDO::PARAM_INT);
            $stmt->bindParam(':reason_for_request', $data['reason_for_request']);
            $stmt->bindParam(':detailed_description', $data['detailed_description']);
            $stmt->bindParam(':status', $data['status']);

            if ($stmt->execute()) {
                $this->lastInsertId = $this->db->lastInsertId();
                return $this->lastInsertId;
            }
            $this->lastError = "SQL Create Error: " . implode(", ", $stmt->errorInfo());
            error_log("CompensationRequest Create SQL Error: " . $this->lastError . " | SQL: " . $sql . " | Data: " . json_encode($data));
            return false;
        } catch (PDOException $e) {
            $this->lastError = "PDOException on Create: " . $e->getMessage();
            error_log("CompensationRequest Create PDOException: " . $e->getMessage() . " | SQL: " . $sql . " | Data: " . json_encode($data));
            return false;
        }
    }
    public function getAll($status_filter = null, $limit = 50, $offset = 0)
    {
        $query = "SELECT cr.*, 
                         u.username as user_username, 
                         u.email as user_email, 
                         b.booking_id as display_booking_id  
                  FROM " . $this->table . " cr
                  JOIN users u ON cr.user_id = u.user_id
                  JOIN bookings b ON cr.booking_id = b.id  -- Assumes cr.booking_id (FK) links to bookings.id (PK)
                  WHERE 1=1";

        $params = [];
        if (!empty($status_filter)) {
            $query .= " AND cr.status = :status";
            $params[':status'] = $status_filter;
        }
        $query .= " ORDER BY cr.request_date DESC LIMIT :offset, :limit";


        try {
            $stmt = $this->db->prepare($query);
            if (!empty($status_filter)) {
                $stmt->bindParam(':status', $params[':status']);
            }
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = "GetAll CompensationRequests Error: " . $e->getMessage();
            error_log($this->lastError . " | SQL: " . $query . " | Status: " . $status_filter);
            return false;
        }
    }

    public function countAll($status_filter = null)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE 1=1";
        $params = [];
        if (!empty($status_filter)) {
            $query .= " AND status = :status";
            $params[':status'] = $status_filter;
        }
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = "CountAll CompensationRequests Error: " . $e->getMessage();
            error_log($this->lastError);
            return 0;
        }
    }

    public function getByIdWithDetails($request_id)
    {
        $query = "SELECT cr.*, 
                         u.username as user_username, u.email as user_email, 
                         b.booking_id as display_booking_id, 
                         b.source_station as booking_source_station, 
                         b.destination_station as booking_destination_station, 
                         b.booking_date as booking_journey_date, 
                         b.train_name as booking_train_name, 
                         b.train_number as booking_train_number, 
                         b.fare as booking_original_fare,
                         admin_user.username as processed_by_admin_username 
                  FROM " . $this->table . " cr
                  JOIN users u ON cr.user_id = u.user_id
                  JOIN bookings b ON cr.booking_id = b.id 
                  LEFT JOIN users admin_user ON cr.processed_by_user_id = admin_user.user_id
                  WHERE cr.request_id = :request_id 
                  LIMIT 1";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = "GetByIdWithDetails Error: " . $e->getMessage();
            error_log($this->lastError . " | Request ID: " . $request_id);
            return false;
        }
    }

    public function processRequest($data)
    {
        if (empty($data['request_id']) || empty($data['status']) || empty($data['processed_by_user_id'])) {
            $this->lastError = "ProcessRequest Error: Missing required fields (request_id, status, processed_by_user_id).";
            error_log($this->lastError . " | Data: " . json_encode($data));
            return false;
        }

        $query = "UPDATE " . $this->table . " SET 
                    status = :status, 
                    admin_notes = :admin_notes, 
                    compensation_amount = :compensation_amount, 
                    processed_by_user_id = :processed_by_user_id,
                    processed_date = NOW(),
                    updated_at = NOW()
                  WHERE request_id = :request_id";
        try {
            $stmt = $this->db->prepare($query);

            $admin_notes_param = $data['admin_notes'] ?? null;
            $compensation_amount_param = (isset($data['compensation_amount']) && is_numeric($data['compensation_amount'])) ? (float)$data['compensation_amount'] : null;

            $stmt->bindParam(':request_id', $data['request_id'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':admin_notes', $admin_notes_param, $admin_notes_param === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':compensation_amount', $compensation_amount_param, $compensation_amount_param === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':processed_by_user_id', $data['processed_by_user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return true;
            }
            $this->lastError = "SQL ProcessRequest Error: " . implode(", ", $stmt->errorInfo());
            error_log("CompensationRequest ProcessRequest SQL Error: " . $this->lastError . " | Data: " . json_encode($data));
            return false;
        } catch (PDOException $e) {
            $this->lastError = "PDOException on ProcessRequest: " . $e->getMessage();
            error_log("CompensationRequest ProcessRequest PDOException: " . $e->getMessage() . " | Data: " . json_encode($data));
            return false;
        }
    }
}
