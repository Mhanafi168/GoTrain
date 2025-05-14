<?php

class DashboardController  {
    private $user;
    private $booking;
    
   public function __construct(User $user, Booking $booking) {
    $this->user = $user;
    $this->booking = $booking;
}

    public function index() {

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        else {
            header("Location: ../views/auth/login.php");
            exit();
        }
        
        $this->user->getUserById($user_id);
        
        $data = [
            'user' => [
                'name' => $this->user->name,
                'balance' => $this->user->balance,
                'last_recharge_date' => date('d M Y', strtotime($this->user->last_recharge_date))
            ],
            'stats' => [
                'total_bookings' => $this->booking->countTotalBookings($user_id),
                'upcoming_trips' => $this->booking->countUpcomingTrips($user_id),
                'favorite_route' => $this->user->getFavoriteRoute($user_id)
            ],
            'upcoming_bookings' => [],
            'recent_bookings' => []
        ];
        
        $upcoming_bookings = $this->booking->getUpcomingBookings($user_id);
        while ($row = $upcoming_bookings->fetch(PDO::FETCH_ASSOC)) {
           
            $booking_date = new DateTime($row['date']);
            $today = new DateTime();
            $diff = $today->diff($booking_date);
            
            if ($diff->days == 0) {
                $label = "Today";
            } elseif ($diff->days == 1) {
                $label = "Tomorrow";
            } elseif ($diff->days <= 7) {
                $label = "This Week";
            } else {
                $label = "Next Week";
            }
            
            $row['label'] = $label;
            $row['formatted_date'] = date('M d, Y', strtotime($row['date']));
            $row['formatted_time'] = date('h:i A', strtotime($row['time']));
            
            $data['upcoming_bookings'][] = $row;
        }
       
        $recent_bookings = $this->booking->getRecentBookings($user_id);
        while ($row = $recent_bookings->fetch(PDO::FETCH_ASSOC)) {
            $row['formatted_date'] = date('d M Y', strtotime($row['booking_date']));
            $data['recent_bookings'][] = $row;
        }
        include_once(__DIR__ . '/../views/Dashboard/default.php');

    }
}