<?php
require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Booking.php';

class DashboardController {
    private Dashboard $dashboard;
    private User $user;
    private Booking $booking;

    public function __construct(User $user, Booking $booking) {
        $this->user = $user;
        $this->booking = $booking;
        $this->dashboard = new Dashboard($user->getConnection());
    }

    public function index(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }

        $userId = $_SESSION['user_id'];
        
        $userData = $this->user->getUserById($userId);
        
        $stats = [
            'total_bookings' => $this->booking->countTotalBookings($userId),
            'upcoming_trips' => $this->booking->countUpcomingTrips($userId),
            'favorite_route' => $this->user->getFavoriteRoute($userId)
        ];

        $data = [
            'user' => [
                'name' => $this->user->name,
                'balance' => $this->user->balance,
                'last_recharge_date' => $this->user->last_recharge_date
            ],
            'stats' => $stats
        ];

        require_once __DIR__ . '/../views/Dashboard/default.php';
    }
}