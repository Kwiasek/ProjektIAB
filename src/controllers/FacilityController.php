<?php

require_once __DIR__ . "/../config/db.php";

class FacilityController {
    public function addFacility($data) {
        global $pdo;

        $name = trim($data['name']);
        $description = trim($data['description']);
        $location = trim($data['location']);
        $image_url = trim($data['image_url']);
        $price = $data['price'];

        if (!$_SESSION['user']) {
            header("Location: /");
            exit;
        }

        if ($name == "") {
            $_SESSION['error'] = 'Nazwa nie może być pusta';
            header("location: /facilities/add");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO facilities (owner_id, name, description, location, price_per_hour, image_url, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt->execute([$_SESSION['user']['id'], $name, $description, $location, $price, $image_url])) {
            $_SESSION['error'] = 'Błąd serwera';
            header("location: /facilities/add");
            exit;
        }

        $facilityId = $pdo->lastInsertId();

        if (!empty($_POST['availability'])) {
            $stmt = $pdo->prepare("INSERT INTO facility_availability (facility_id, day_of_week, open_time, close_time, is_open)
                VALUES (?, ?, ?, ?, ?)");

            foreach ($_POST['availability'] as $day => $data) {
                $open = $data['open'] ?? "00:00";
                $close = $data['close'] ?? "00:00";
                $is_open = isset($data['is_open']) ? 1 : 0;
                $stmt->execute([$facilityId, $day, $open, $close, $is_open]);
            }
        }

        header("location: /");
        exit;
    }

    public function getAll() {
        require_once __DIR__ . '/../models/Facility.php';
        header('Content-Type: application/json');

        $filters = [
            'name' => $_GET['name'] ?? null,
            'location' => $_GET['location'] ?? null,
            'date' => $_GET['date'] ?? null,
            'sort' => $_GET['sort'] ?? null,
            'available_only' => isset($_GET['available_only'])
        ];

        try {
            $facilityModel = new Facility();
            $facilities = $facilityModel->getFiltered($filters);
            echo json_encode([
                "status" => "success",
                "data" => $facilities
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getFacilityById($facilityId) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE facility_id = ?");
        if (!$stmt->execute([$facilityId])) {}
    }
}