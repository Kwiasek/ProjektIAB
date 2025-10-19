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

        header("location: /");
        exit;
    }

    public function getFacilities() {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM facilities");
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }
}