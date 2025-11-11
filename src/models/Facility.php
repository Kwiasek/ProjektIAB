<?php

require_once __DIR__ . "/../config/db.php";

class Facility {
    public function createFacility($name, $description, $location, $image_url, $price, $availability) {
        global $pdo;

        $stmt = $pdo->prepare("INSERT INTO facilities (owner_id, name, description, location, price_per_hour, image_url, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt->execute([$_SESSION['user']['id'], $name, $description, $location, $price, $image_url])) {
            $_SESSION['error'] = 'Błąd serwera';
            header("location: /facilities/add");
            exit;
        }

        $facilityId = $pdo->lastInsertId();
        foreach ($availability as $day => $data) {
            $open = $data['open'] ?? "00:00";
            $close = $data['close'] ?? "00:00";
            $is_open = isset($data['is_open']) ? 1 : 0;
            $result = $this->createFacilityAvailability($facilityId, $day, $open, $close, $is_open);
            if (!$result['success']) {
                return $result;
            }
        }

        return json_encode([
            'success' => true
        ]);
    }

    public function updateFacility($id, $name, $description, $location, $image_url, $price, $availability) {
        global $pdo;

        try {
            // Rozpocznij transakcję
            $pdo->beginTransaction();

            // Aktualizacja głównej tabeli facilities
            $stmt = $pdo->prepare("
            UPDATE facilities 
            SET name = ?, description = ?, location = ?, image_url = ?, price_per_hour = ?, updated_at = NOW()
            WHERE id = ?
        ");

            if (!$stmt->execute([$name, $description, $location, $image_url, $price, $id])) {
                throw new Exception('Nie udało się zaktualizować danych obiektu.');
            }

            // Aktualizacja dostępności dla każdego dnia tygodnia
            foreach ($availability as $day => $data) {
                $open = $data['open'] ?? "00:00";
                $close = $data['close'] ?? "00:00";
                $is_open = isset($data['is_open']) ? 1 : 0;

                // Sprawdź, czy rekord istnieje
                $checkStmt = $pdo->prepare("
                SELECT id FROM facility_availability
                WHERE facility_id = ? AND day_of_week = ?
            ");
                $checkStmt->execute([$id, $day]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Aktualizuj istniejący
                    $updateStmt = $pdo->prepare("
                    UPDATE facility_availability
                    SET open_time = ?, close_time = ?, is_open = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                    if (!$updateStmt->execute([$open, $close, $is_open, $existing['id']])) {
                        throw new Exception('Nie udało się zaktualizować godzin otwarcia dla ' . $day);
                    }
                } else {
                    // Dodaj nowy
                    $insertStmt = $pdo->prepare("
                    INSERT INTO facility_availability (facility_id, day_of_week, open_time, close_time, is_open)
                    VALUES (?, ?, ?, ?, ?)
                ");
                    if (!$insertStmt->execute([$id, $day, $open, $close, $is_open])) {
                        throw new Exception('Nie udało się dodać godzin otwarcia dla ' . $day);
                    }
                }
            }

            // Jeśli wszystko się udało — zatwierdź
            $pdo->commit();

            return json_encode([
                'success' => true,
                'message' => 'Obiekt i jego dostępność zostały pomyślnie zaktualizowane.'
            ]);

        } catch (Exception $e) {
            // W razie błędu — wycofaj wszystkie zmiany
            $pdo->rollBack();
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    public function getFacility($id) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        if (!$stmt->execute([$id])) {
            return null;
        }
        return $stmt->fetch();
    }

    public function getFacilities() {
        global $pdo;
        $sql = "SELECT * FROM facilities";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deleteFacility($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM facilities WHERE id = ?");
        if (!$stmt->execute([$id])) {
            return false;
        }
        return true;
    }


    public function getFiltered($filters) {
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

    public function createFacilityAvailability($facility_id, $day, $open, $close, $is_open) {
        global $pdo;

        $stmt = $pdo->prepare("
        INSERT INTO facility_availability
        (facility_id, day_of_week, open_time, close_time) 
        VALUES (?, ?, ?, ?)       
        ");
        if (!$stmt->execute([$facility_id, $day, $open, $close, $is_open])) {
            return null;
        }
        return $stmt->rowCount();
    }

    public function getFacilityAvailability($facilityId, $day) {
        global $pdo;

        $stmt = $pdo->prepare("
        SELECT * FROM facility_availability
        WHERE facility_id = ? AND day_of_week = ?
        ");
        if (!$stmt->execute([$facilityId, $day])) {
            return null;
        }
        return $stmt->fetch();
    }

    public function updateFacilityAvailability($id, $day, $open, $close, $is_open) {
        global $pdo;

        $stmt = $pdo->prepare("
        UPDATE facility_availability
        SET day_of_week = ?, open_time = ?, close_time = ?, is_open = ?, updated_at = NOW()
        WHERE id = ?
    ");

        if (!$stmt->execute([$day, $open, $close, $is_open, $id])) {
            return null;
        }
        return $stmt->rowCount();
    }

    public function deleteFacilityAvailability($id) {
        global $pdo;

        $stmt = $pdo->prepare("DELETE FROM facility_availability WHERE id = ?");
        if (!$stmt->execute([$id])) {
            return false;
        }
        return true;
    }

    public function getAvailabilityWithReservations($facilityId, $date): array
    {
        global $pdo;

        $dayOfWeek = strtolower(date('l', strtotime($date)));

        $stmt = $pdo->prepare("
            SELECT open_time, close_time, is_open
            FROM facility_availability
            WHERE facility_id = ? AND day_of_week = ?
        ");
        $stmt->execute([$facilityId, $dayOfWeek]);
        $availability = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!isset($availability) || $availability['is_open'] !== 1) {
            echo 'xd';
            return ['available' => [], 'open' => null, 'close' => null];
        }

        $open = (int) explode(':', $availability['open_time'])[0];
        $close = (int) explode(':', $availability['close_time'])[0];
        $availableHours = range($open, $close - 1);

        $stmt = $pdo->prepare("
            SELECT start_time, end_time
            FROM reservations
            WHERE facility_id = ? AND date = ?
        ");
        $stmt->execute([$facilityId, $date]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as $res) {
            $start = (int) explode(':', $res['start_time'])[0];
            $end = (int) explode(':', $res['end_time'])[0];
            for ($i = $start; $i < $end; $i++) {
                $index = array_search($i, $availableHours);
                if ($index !== false) unset($availableHours[$index]);
            }
        }

        sort($availableHours);
        return [
            'available' => $availableHours,
            'open' => $open,
            'close' => $close,
        ];
    }
}