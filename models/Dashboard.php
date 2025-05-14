<?php
class Dashboard {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserStats($userId) {
        $stats = [];
        
        $query = "SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_bookings'] = $result['total_bookings'];

        $query = "SELECT COUNT(*) as upcoming_trips 
                 FROM bookings 
                 WHERE user_id = ? 
                 AND DATE(booking_date) >= CURDATE() 
                 AND status = 'Confirmed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['upcoming_trips'] = $result['upcoming_trips'];

        $query = "SELECT CONCAT(source_station, ' - ', destination_station) as route, 
                        COUNT(*) as frequency
                 FROM bookings 
                 WHERE user_id = ?
                 GROUP BY source_station, destination_station
                 ORDER BY frequency DESC
                 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['favorite_route'] = $result ? $result['route'] : 'N/A';

        return $stats;
    }
} 