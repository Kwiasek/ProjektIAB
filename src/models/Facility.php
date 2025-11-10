<?php

require_once __DIR__ . "/../config/db.php";

class Facility {


    public static function getAll() {
        global $pdo;
        $sql = "SELECT * FROM facilities";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public static function getFiltered($filters) {
        global $pdo;
        $sql = "SELECT f.* FROM facilities f";
        $params = [];
        $conditions = [];

        // Filtr po nazwie
        if (!empty($filters['name'])) {
            $conditions[] = "f.name LIKE :name";
            $params[':name'] = "%" . $filters['name'] . "%";
        }

        // Filtr po lokalizacji
        if (!empty($filters['location'])) {
            $conditions[] = "f.location LIKE :location";
            $params[':location'] = "%" . $filters['location'] . "%";
        }

        // Filtr po dacie
        if (!empty($filters['date'])) {
            $sql .= "
                JOIN facility_availability fa ON fa.facility_id = f.id
                WHERE fa.day_of_week = DAYNAME(:date)
            ";
            $params[':date'] = $filters['date'];
        }

        if (!empty($conditions)) {
            $sql .=  (str_contains($sql, 'WHERE') ? ' AND ' : ' WHERE ') . implode(' AND ', $conditions);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        global $pdo;
        $sql = "SELECT * FROM facilities WHERE id = :id ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAvailibility($id, $date) {
        global $pdo;

        $dayOfWeek = strtolower(date('l', strtotime($date)));

        $stmt = $pdo->prepare("
           SELECT open_time, close_time, is_open
           FROM facility_availability 
           WHERE facility_id = ? AND day_of_week = ?
        ");

        $stmt->execute([$id, $dayOfWeek]);
        $availability = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$availability || !$availability['is_open']) {
            echo json_encode(['available' => []]);
            return;
        }

        $open = (int) explode(':', $availability['open_time'])[0];
        $close = (int) explode(':', $availability['close_time'])[0];
        $availableHours = range($open, $close - 1);

        $stmt = $pdo->prepare("
            SELECT start_time, end_time
            FROM reservations
            WHERE facility_id = ? AND date = ?
        ");

        $stmt->execute([$id, $date]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as $res) {
            $start = (int) explode(':', $res['start_time'])[0];
            $end = (int) explode(':', $res['end_time'])[0];
            for ($i = $start; $i < $end; $i++) {
                $index = array_search($i, $availableHours);
                if ($index !== false) {
                    unset($availableHours[$index]);
                }
            }
        }

        sort($availableHours);

        return json_encode([
            'available' => $availableHours,
            'open' => $open,
            'close' => $close
        ]);
    }

    public static function add($name, $description, $location, $image_url, $price, $availability) {
        global $pdo;

        $stmt = $pdo->prepare("INSERT INTO facilities (owner_id, name, description, location, price_per_hour, image_url, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt->execute([$_SESSION['user']['id'], $name, $description, $location, $price, $image_url])) {
            $_SESSION['error'] = 'Błąd serwera';
            header("location: /facilities/add");
            exit;
        }

        $facilityId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO facility_availability (facility_id, day_of_week, open_time, close_time, is_open) VALUES (?, ?, ?, ?, ?)");
        foreach ($availability as $day => $data) {
            $open = $data['open'] ?? "00:00";
            $close = $data['close'] ?? "00:00";
            $is_open = isset($data['is_open']) ? 1 : 0;
            $stmt->execute([$facilityId, $day, $open, $close, $is_open]);
        }

        return $stmt->rowCount();
    }
}