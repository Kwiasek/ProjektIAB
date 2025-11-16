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
        $image_url = trim($data['image_url']);
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
            $this->facility->createFacility($name, $description, $location, $image_url, $price, $availability);
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
}