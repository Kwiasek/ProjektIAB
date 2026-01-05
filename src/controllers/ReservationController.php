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

    public function payReservation(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $reservationId = $_GET['id'] ?? null;
        if (!$reservationId) {
            header('Location: /my-reservations');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $reservationModel = new Reservation();

        // Pobierz rezerwację
        global $pdo;
        $stmt = $pdo->prepare("SELECT r.*, f.name as facility_name FROM reservations r JOIN facilities f ON f.id = r.facility_id WHERE r.id = ? AND r.user_id = ?");
        $stmt->execute([$reservationId, $userId]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation || $reservation['status'] !== 'confirmed') {
            header('Location: /my-reservations');
            exit;
        }

        require_once __DIR__ . "/../../views/reservations/pay.php";
    }
}
