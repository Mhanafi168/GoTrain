<?php

require_once __DIR__ . '/../config/DBController.php';

class TicketController
{
    private $db;
    private $lastError;

    public function __construct()
    {
        try {
            $this->db = DBController::getInstance();
        } catch (Exception $e) {
            $this->setLastError("Failed to initialize database controller: " . $e->getMessage());
            $this->db = null;
        }
    }

    private function setLastError($message)
    {
        $this->lastError = $message;
        error_log("TicketController Error: " . $message);
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function isDBConnected()
    {
        return ($this->db instanceof DBController);
    }

    public function viewTicket($displayBookingId, $userId)
    {
        $this->setLastError('');
        if (!$this->isDBConnected()) {
            $this->setLastError("Database service is unavailable in TicketController.");
            return false;
        }
        if (empty($displayBookingId) || !is_numeric($userId) || $userId <= 0) {
            $this->setLastError("Booking ID or User ID missing or invalid for viewing ticket.");
            return false;
        }

        $sql = "SELECT
                    b.id as internal_pk_id,
                    b.booking_id,
                    b.user_id,
                    b.source_station,
                    b.destination_station,
                    b.booking_date,      
                    b.departure_time,    
                    b.class,
                    b.fare,
                    b.train_name,
                    b.train_number,
                    b.status,
                    b.passengers,
                    b.ticket_type,
                    b.return_date,
                    u.username as passenger_name,
                    COALESCE(b.created_at, b.booking_date) as booking_creation_date 
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                WHERE b.booking_id = ? AND b.user_id = ?
                LIMIT 1";
        $params = [$displayBookingId, $userId];
        $result = $this->db->select($sql, $params);

        if ($result === false) {
            $this->setLastError("Database error while fetching ticket: " . $this->db->getLastError());
            return false;
        }
        if (empty($result)) {
            $this->setLastError("Ticket not found or you do not have permission to view it.");
            return false;
        }

        $ticket = $result[0];
        $ticket['seat_info'] = $ticket['class'] . ' / Coach ' . chr(65 + ($ticket['internal_pk_id'] % 4)) . '-' . (($ticket['internal_pk_id'] * $ticket['passengers']) % 50 + 1);

        $ticket['journey_datetime'] = $ticket['booking_date'];
        $ticket['journey_date_only'] = date('Y-m-d', strtotime($ticket['booking_date']));


        return $ticket;
    }
    public function listTickets($userId, $limit = 50, $offset = 0)
    {
        $this->setLastError('');
        if (!$this->isDBConnected()) {
            $this->setLastError("Database service is unavailable in TicketController.");
            throw new Exception("Database service unavailable.");
        }
        if (empty($userId) || !is_numeric($userId) || $userId <= 0) {
            $this->setLastError("User ID missing or invalid for listing tickets.");
            throw new Exception("User context not found for listing tickets.");
        }

        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT
                    booking_id,
                    source_station,
                    destination_station,
                    booking_date,       
                    departure_time,     
                    fare,
                    status
                FROM bookings
                WHERE user_id = ?
                ORDER BY booking_date DESC, departure_time DESC
                LIMIT ?, ?";
        $params = [$userId, $offset, $limit];
        $result = $this->db->select($sql, $params);

        if ($result === false) {
            $this->setLastError("Database error while listing tickets: " . $this->db->getLastError());
            throw new Exception($this->lastError);
        }
        return $result;
    }
}
