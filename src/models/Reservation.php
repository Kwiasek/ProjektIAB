<?php

require_once __DIR__ . "/../config/db.php";

class Reservation {

    public function makeReservation(): void
    {

        global $pdo;

        if (!isset($_SESSION['user'])) {
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $facilityId = $data['facility_id'];
        $date = $data['date'];
        $start = (int) $data['start'];
        $duration = (int) $data['duration'];
        $end = $start + $duration;

        $stmt = $pdo->prepare("
            SELECT count(*) FROM reservations 
            WHERE facility_id = ? AND date = ?
            AND ((start_time < ? AND end_time > ?) OR (start_time >= ? AND start_time < ?))
        ");

        $stmt->execute([$facilityId, $date, "$end:00", "$start:00", "$start:00", "$end:00"]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['error' => 'Ten termin jest już zajęty.']);
            return;
        }

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
            10
        ]);

        echo json_encode(['success' => true]);
    }

}