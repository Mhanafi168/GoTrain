<?php
class Booking
{
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $booking_id;
    public $user_id;
    public $source;
    public $destination;
    public $date;
    public $time;
    public $class;
    public $fare;
    public $train_name;
    public $train_number;
    public $status;

    public $lastError;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createBooking($data)
    {
        try {
            $required = [
                'booking_id',
                'user_id',
                'source_station',
                'destination_station',
                'booking_date',
                'departure_time',
                'class',
                'fare',
                'train_name',
                'train_number',
                'passengers',
                'ticket_type'
            ];

            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $query = "INSERT INTO " . $this->table_name . " 
                  (booking_id, user_id, source_station, destination_station, 
                   booking_date, departure_time, class, fare, 
                   train_name, train_number, status, passengers, ticket_type, return_date)
                  VALUES 
                  (:booking_id, :user_id, :source_station, :destination_station, 
                   :booking_date, :departure_time, :class, :fare, 
                   :train_name, :train_number, :status, :passengers, :ticket_type, :return_date)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindValue(':booking_id', $data['booking_id']);
            $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':source_station', $data['source_station']);
            $stmt->bindValue(':destination_station', $data['destination_station']);
            $stmt->bindValue(':booking_date', $data['booking_date']);
            $stmt->bindValue(':departure_time', $data['departure_time']);
            $stmt->bindValue(':class', $data['class']);
            $stmt->bindValue(':fare', $data['fare']);
            $stmt->bindValue(':train_name', $data['train_name']);
            $stmt->bindValue(':train_number', $data['train_number']);
            $stmt->bindValue(':status', $data['status'] ?? 'Confirmed');
            $stmt->bindValue(':passengers', $data['passengers'], PDO::PARAM_INT);
            $stmt->bindValue(':ticket_type', $data['ticket_type']);
            $stmt->bindValue(':return_date', $data['return_date'] ?? null, $data['return_date'] ? PDO::PARAM_STR : PDO::PARAM_NULL);

            if ($stmt->execute()) {
                return $data['booking_id'];
            }

            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database error: " . ($errorInfo[2] ?? 'Unknown error'));
        } catch (PDOException $e) {
            $this->lastError = "Database error while creating booking: " . $e->getMessage();
            error_log("Booking creation PDO error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Booking creation error: " . $e->getMessage());
            return false;
        }
    }
    public function getRecentBookings($user_id, $limit = 3)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = ? 
                  ORDER BY booking_date DESC 
                  LIMIT 0, ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getUpcomingBookings($user_id, $limit = 2)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = ? 
                    AND status = 'Upcoming' 
                    AND booking_date >= CURDATE() 
                  ORDER BY booking_date ASC, departure_time ASC 
                  LIMIT 0, ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function countTotalBookings($user_id)
    {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function countUpcomingTrips($user_id)
    {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE user_id = ? 
                    AND status = 'Upcoming' 
                    AND booking_date >= CURDATE()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
