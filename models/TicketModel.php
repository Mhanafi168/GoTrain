<?php
require_once __DIR__ . '/../config/DBController.php';

class TicketModel
{
    private $db;

    public function __construct()
    {
        $this->db = DBController::getInstance()->getConnection();
    }

    public function getTicketById($ticketId)
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE ticket_id = :ticket_id");
        $stmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            return $ticket ?: null;
        } else {
            return false;
        }
    }
    public function getTicketsByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }


    public function __destruct()
    {

        $this->db = null;
    }
}
