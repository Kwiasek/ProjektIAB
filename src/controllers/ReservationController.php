<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Reservation.php";

class ReservationController {

    public function myReservations(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $reservationModel = new Reservation();
        $reservations = $reservationModel->getUserReservations($userId);

        $now = new DateTime();
        $upcoming = [];
        $past = [];

        foreach ($reservations as $r) {
            // Compose reservation start and end datetimes
            $startDt = new DateTime($r['date'] . ' ' . $r['start_time']);
            $endDt = new DateTime($r['date'] . ' ' . $r['end_time']);

            if ($endDt <= $now) {
                $past[] = $r;
            } else {
                $upcoming[] = $r;
            }
        }

        require_once __DIR__ . "/../../views/reservations/list.php";
    }
}
