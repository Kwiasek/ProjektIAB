<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Reservation.php";
require_once __DIR__ . "/../models/Review.php";

class ReservationController {

    public function myReservations(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $reservationModel = new Reservation();
        $reviewModel = new Review();
        $reservations = $reservationModel->getUserReservations($userId);

        $now = new DateTime();
        $upcoming = [];
        $past = [];

        foreach ($reservations as $r) {
            // Compose reservation start and end datetimes
            $startDt = new DateTime($r['date'] . ' ' . $r['start_time']);
            $endDt = new DateTime($r['date'] . ' ' . $r['end_time']);

            // Check if user already has a review for this facility
            $userReview = $reviewModel->getUserReview($r['facility_id'], $userId);
            $r['user_has_review'] = $userReview !== null;

            if ($endDt <= $now) {
                $past[] = $r;
            } else {
                $upcoming[] = $r;
            }
        }

        require_once __DIR__ . "/../../views/reservations/list.php";
    }
}
