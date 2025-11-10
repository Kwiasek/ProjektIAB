<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Facility.php";

class FacilityController {

    private $facility;

    function __construct() {
        $this->facility = new Facility();
    }
    public function addFacility($data) {
        $name = trim($data['name']);
        $description = trim($data['description']);
        $location = trim($data['location']);
        $image_url = trim($data['image_url']);
        $price = $data['price'];
        $availability = $data['availability'];

        if (!$_SESSION['user']) {
            header("Location: /");
            exit;
        }

        if ($name == "") {
            $_SESSION['error'] = 'Nazwa nie może być pusta';
            header("location: /facilities/add");
            exit;
        }

        $this->facility->add($name, $description, $location, $image_url, $price, $availability);

        header("location: /");
        exit;
    }

    public function getAllFiltered() {
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
            $facility = $this->facility->getById($facilityId);
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

    public function getAvailabilityById($facilityId, $date) {
        header('Content-Type: application/json');
        try {
            $availability = $this->facility->getAvailibility($facilityId, $date);
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
}