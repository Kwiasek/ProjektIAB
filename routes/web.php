<?php

function routeRequest($uri) {
    switch ($uri) {
        case '/':
        case '/home':
            require_once __DIR__ . '/../views/facilities/list.php';
        break;

        case '/login':
            require_once __DIR__ . "/../views/auth/login.php";
            break;

        case '/register':
            require_once __DIR__ . "/../views/auth/register.php";
            break;

        case '/logout':
            require_once __DIR__ . "/../src/controllers/UserController.php";
            $controller = new UserController();
            $controller->logout();
            break;

        case '/api/facilities':
            require_once __DIR__ . "/../src/controllers/FacilityController.php";
            $controller = new FacilityController();
            $controller->getAllFiltered();
            break;

        case '/api/facilities/like':
            header('Content-type: application/json');
            $facilityId = $_POST['facility_id'] ?? null;
            require_once __DIR__ . "/../src/controllers/FacilityController.php";
            $controller = new FacilityController();
            $controller->toggleLike($facilityId);
            break;

        case '/api/facilities/is-liked':
            header('Content-type: application/json');
            $facilityId = $_GET['facility_id'] ?? null;
            require_once __DIR__ . "/../src/controllers/FacilityController.php";
            $controller = new FacilityController();
            $controller->isLiked($facilityId);
            break;

        case '/api/facility/availability':
            header('Content-type: application/json');
            $id = $_GET['id'];
            $date = $_GET['date'];
            require_once __DIR__ . "/../src/controllers/FacilityController.php";
            $controller = new FacilityController();
            $controller->getAvailibilityWithReservations($id, $date);
            break;

        case '/facility':
            $id = $_GET['id'];
            if (!isset($id)) {
                header('Location: /');
                break;
            }
            require_once __DIR__ . "/../src/controllers/FacilityController.php";
            $controller = new FacilityController();

            $facility = json_decode($controller->getFacilityById($id), true);
            if ($facility['data']) {
                // Get facility schedule
                require_once __DIR__ . "/../src/models/Facility.php";
                $facilityModel = new Facility();
                $schedule = $facilityModel->getFacilitySchedule($id);
                require_once __DIR__ . "/../views/facilities/facility.php";
            } else {
                header('Location: /');
            }
            break;

        case '/api/facilities/reserve':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/models/Reservation.php";
                $reservation = new Reservation();
                $reservation->makeReservation();
            }
            break;

        case '/my-reservations':
            require_once __DIR__ . "/../src/controllers/ReservationController.php";
            $controller = new ReservationController();
            $controller->myReservations();
            break;

        case '/pay-reservation':
            require_once __DIR__ . "/../src/controllers/ReservationController.php";
            $controller = new ReservationController();
            $controller->payReservation();
            break;

        case '/api/reservations/cancel':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . "/../src/models/Reservation.php";
                $reservation = new Reservation();
                $reservation->cancelReservation();
            }
            break;

        case '/api/reservations/pay':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . "/../src/models/Reservation.php";
                $reservation = new Reservation();
                $reservation->payReservation();
            }
            break;

        case '/facilities/add':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/controllers/FacilityController.php";
                $facilitiesController = new FacilityController();
                $facilitiesController->addFacility($_POST);
                break;
            }
            require_once __DIR__ . '/../views/facilities/add.php';
            break;

        case '/facilities/edit':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                header('Location: /');
                break;
            }
            require_once __DIR__ . "/../src/controllers/FacilityController.php";
            $controller = new FacilityController();
            $controller->editFacility($id);
            break;

        case '/facilities/update':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/controllers/FacilityController.php";
                $controller = new FacilityController();
                $controller->updateFacility($_POST, $_FILES);
            }
            break;

        case '/image/facility':
            // Serve images by image_id or facility_id (first image)
            require_once __DIR__ . "/../src/models/Facility.php";
            $facility = new Facility();
            $imageId = $_GET['image_id'] ?? null;
            $facilityId = $_GET['facility_id'] ?? null;
            if ($imageId) {
                $facility->serveImageById($imageId);
            } elseif ($facilityId) {
                $facility->serveFirstImageOfFacility($facilityId);
            } else {
                http_response_code(404);
            }
            break;

        case '/admin':
            require_once __DIR__ . "/../src/controllers/AdminController.php";
            $c = new AdminController();
            $c->index();
            break;

        case '/admin/facilities':
            require_once __DIR__ . "/../src/controllers/AdminController.php";
            $c = new AdminController();
            $c->myFacilities();
            break;

        case '/admin/reservations/confirm':
            // POST: { reservation_id }
            require_once __DIR__ . "/../src/controllers/AdminController.php";
            $c = new AdminController();
            header('Content-Type: application/json');
            $c->confirmReservation();
            break;

        case '/admin/reservations/reject':
            // POST: { reservation_id }
            require_once __DIR__ . "/../src/controllers/AdminController.php";
            $c = new AdminController();
            header('Content-Type: application/json');
            $c->rejectReservation();
            break;

        case '/admin/pending-count':
            require_once __DIR__ . "/../src/controllers/AdminController.php";
            $c = new AdminController();
            $c->pendingCount();
            break;

        case '/facilities/delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . "/../src/models/Facility.php";
                // basic ownership check
                $id = $_POST['id'] ?? null;
                if (!isset($_SESSION['user']) || !$id) {
                    header('Location: /');
                    break;
                }
                global $pdo;
                $stmt = $pdo->prepare("SELECT owner_id FROM facilities WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row || $row['owner_id'] != $_SESSION['user']['id']) {
                    header('Location: /');
                    break;
                }
                $facility = new Facility();
                $facility->deleteFacility($id);
                header('Location: /admin/facilities');
            }
            break;

        // Enddpointy do wysyłania formularzy
        case '/auth/login':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/controllers/UserController.php";
                $controller = new UserController();
                $controller->login($_POST);
            }
            break;

        case '/auth/register':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                require_once __DIR__ . "/../src/controllers/UserController.php";
                $controller = new UserController();
                $controller->register($_POST);
            }
            break;

        // Review routes
        case '/api/reviews/add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . "/../src/controllers/ReviewController.php";
                $controller = new ReviewController();
                $controller->addReview();
            }
            break;

        case '/api/reviews/update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . "/../src/controllers/ReviewController.php";
                $controller = new ReviewController();
                $controller->updateReview();
            }
            break;

        case '/api/reviews/delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . "/../src/controllers/ReviewController.php";
                $controller = new ReviewController();
                $controller->deleteReview();
            }
            break;

        case '/api/facility/reviews':
            require_once __DIR__ . "/../src/controllers/ReviewController.php";
            $controller = new ReviewController();
            $controller->getReviews();
            break;

        case '/api/facility/user-review':
            require_once __DIR__ . "/../src/controllers/ReviewController.php";
            $controller = new ReviewController();
            $controller->getUserReview();
            break;

        default:
            http_response_code(404);
            echo '<h1 class="underline">404 - Strona nie została znaleziona</h1>';
            break;
    }
}

