<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Facility.php";

class FacilityController {

    private Facility $facility;

    function __construct() {
        $this->facility = new Facility();
    }
    public function addFacility($data) {
        $name = trim($data['name']);
        $description = trim($data['description']);
        $location = trim($data['location']);
        // obsługa wielu zdjęć przesłanych przez formularz (input name="images[]")
        $imageFiles = $_FILES['images'] ?? null;
        $price = trim($data['price']);
        $availability = $data['availability'];

        if (!isset($_SESSION['user'])) {
            header("Location: /");
            exit;
        }

        if ($name == "") {
            $_SESSION['error'] = 'Nazwa nie może być pusta';
            header("location: /facilities/add");
            exit;
        }

        try {
            $this->facility->createFacility($name, $description, $location, $price, $availability, $imageFiles);
            header('Location: /');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Błąd przy dodawaniu obiektu: ' . $e->getMessage();
            header("location: /facilities/add");
            exit;
        }
    }

    public function getAllFiltered() {
        $filters = [
            'name' => $_GET['name'] ?? null,
            'location' => $_GET['location'] ?? null,
            'date' => $_GET['date'] ?? null,
            'sort' => $_GET['sort'] ?? null,
            'available_only' => isset($_GET['available_only'])
        ];

        try {
            $facilities = $this->facility->getFiltered($filters);
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
        try {
            $facility = $this->facility->getFacility($facilityId);
            return json_encode([
                "status" => "success",
                "data" => $facility
            ]);
        }
        catch (Exception $e) {
            http_response_code(500);
            return json_encode([
                "status" => "error",
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Zwraca dostępność dla konkretnego dnia tygodnia bez uwzględniania rezerwacji.
     */
    public function getAvailabilityWithoutReservations($facilityId, $date) {
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        try {
            $availability = $this->facility->getFacilityAvailability($facilityId, $dayOfWeek);
            echo json_encode([
                "status" => "success",
                "data" => $availability
            ]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Zwraca dostępne godziny z uwzględnieniem rezerwacji.
     */
    public function getAvailibilityWithReservations($facilityId, $date): void
    {
        try {
            $availability = $this->facility->getAvailabilityWithReservations($facilityId, $date);
            echo json_encode([
                "status" => "success",
                "data" => $availability
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                'message' => $e->getMessage()
            ]);
        }
    }

    public function editFacility($id): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $facility = $this->facility->getFacility($id);
        if (!$facility) {
            header('Location: /');
            exit;
        }
        require_once __DIR__ . "/../../views/facilities/edit.php";
    }

    public function updateFacility($post, $files): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        
        $id = $post['id'];
        $name = trim($post['name']);
        $description = trim($post['description']);
        $location = trim($post['location']);
        $price = trim($post['price']);
        $availability = $post['availability'] ?? [];
        $deleteImages = $post['delete_images'] ?? [];
        // Normalize delete images input: ensure array of ints
        if (!is_array($deleteImages)) {
            if (empty($deleteImages)) {
                $deleteImages = [];
            } else {
                $deleteImages = [$deleteImages];
            }
        }
        $deleteImages = array_values(array_filter(array_map(function($v){
            return is_numeric($v) ? (int)$v : null;
        }, $deleteImages)));
        $imageFiles = $files['images'] ?? null;

        // The model returns a JSON string with success/error instead of throwing.
        $result = $this->facility->updateFacility($id, $name, $description, $location, $price, $availability, $imageFiles, $deleteImages);
        $parsed = null;
        if (is_string($result)) {
            $parsed = json_decode($result, true);
        }

        if ($parsed && isset($parsed['success']) && $parsed['success'] === true) {
            header('Location: /facility?id=' . $id);
            exit;
        }

        // If we reach here — update failed. Use message from model if available.
        $err = 'Błąd przy aktualizacji obiektu.';
        if (is_array($parsed) && !empty($parsed['error'])) $err = $parsed['error'];
        if (is_array($parsed) && !empty($parsed['message'])) $err = $parsed['message'];
        $_SESSION['error'] = $err;
        header('Location: /facilities/edit?id=' . $id);
        exit;
    }
}