<?php

require_once __DIR__ . "/../config/db.php";

class Facility {
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
}