<?php

require_once __DIR__ . "/../config/db.php";

class Reservation {

    public function makeReservation(): void
    {

        global $pdo;

        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!is_array($data) || !isset($data['facility_id'], $data['date'], $data['start'], $data['end'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nieprawidłowe dane wejściowe.']);
            return;
        }

        $facilityId = $data['facility_id'];
        $date = $data['date'];
        $start = (int) $data['start'];
        $end = (int) $data['end'];

        $stmt = $pdo->prepare("
            SELECT count(*) FROM reservations 
            WHERE facility_id = ? AND date = ? AND status != 'cancelled'
            AND ((start_time < ? AND end_time > ?) OR (start_time >= ? AND start_time < ?))
        ");

        $stmt->execute([$facilityId, $date, "$end:00", "$start:00", "$start:00", "$end:00"]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['error' => 'Ten termin jest już zajęty.']);
            return;
        }

        // Validate start < end
        if ($start >= $end) {
            echo json_encode(['error' => 'Niewłaściwy zakres godzin.']);
            return;
        }

        // Calculate total price based on facility price_per_hour
        $priceStmt = $pdo->prepare("SELECT price_per_hour FROM facilities WHERE id = ? LIMIT 1");
        $priceStmt->execute([$facilityId]);
        $priceRow = $priceStmt->fetch(PDO::FETCH_ASSOC);
        $hourPrice = $priceRow ? floatval($priceRow['price_per_hour']) : 0.0;
        $duration = $end - $start; // integer hours
        if ($duration <= 0) $duration = 1;
        $totalPrice = round($hourPrice * $duration, 2);

        $stmt = $pdo->prepare("
            INSERT INTO reservations (facility_id, user_id, date, start_time, end_time, created_at, persons, total_price)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)
        ");

        $stmt->execute([
            $facilityId,
            $_SESSION['user']['id'],
            $date,
            "$start:00",
            "$end:00",
            1,
            $totalPrice
        ]);

        echo json_encode(['success' => true, 'total_price' => $totalPrice]);
    }

    /**
     * Pobiera rezerwacje konkretnego użytkownika (bez anulowanych)
     * @param int $userId
     * @return array
     */
    public function getUserReservations(int $userId): array
    {
        global $pdo;

        $stmt = $pdo->prepare(
            "SELECT r.*, f.name as facility_name, (
                SELECT id FROM facility_images fi WHERE fi.facility_id = f.id ORDER BY fi.id ASC LIMIT 1
            ) as image_id
            FROM reservations r
            LEFT JOIN facilities f ON f.id = r.facility_id
            WHERE r.user_id = ? AND r.status != 'cancelled'
            ORDER BY r.date DESC, r.start_time ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Anuluje rezerwację należącą do zalogowanego użytkownika jeśli min. 2h przed startem
     * Oczekuje JSONu: { "reservation_id": 123 }
     */
    public function cancelReservation(): void
    {
        global $pdo;

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $reservationId = $data['reservation_id'] ?? null;

        if (!$reservationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak id rezerwacji.']);
            return;
        }

        // Pobierz rezerwację i sprawdź właściciela
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ? AND status != 'cancelled'");
        $stmt->execute([$reservationId, $_SESSION['user']['id']]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            http_response_code(404);
            echo json_encode(['error' => 'Rezerwacja nie znaleziona.']);
            return;
        }

        $startDt = new DateTime($reservation['date'] . ' ' . $reservation['start_time']);
        $now = new DateTime();
        $diff = $startDt->getTimestamp() - $now->getTimestamp();

        if ($diff < 2 * 3600) {
            http_response_code(400);
            echo json_encode(['error' => 'Anulowanie możliwe jedynie minimum 2 godziny przed rozpoczęciem rezerwacji.']);
            return;
        }

        $update = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        if (!$update->execute([$reservationId])) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd serwera podczas anulowania.']);
            return;
        }

        echo json_encode(['success' => true]);
    }

}