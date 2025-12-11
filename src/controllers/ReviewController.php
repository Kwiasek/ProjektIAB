<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Review.php";
require_once __DIR__ . "/../models/Facility.php";

class ReviewController {

    /**
     * Add a new review (POST /api/reviews/add)
     */
    public function addReview(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['facility_id'], $data['rating'], $data['comment'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakuje wymaganych pól.']);
            return;
        }

        $facilityId = (int)$data['facility_id'];
        $rating = (int)$data['rating'];
        $comment = trim($data['comment'] ?? '');
        $userId = $_SESSION['user']['id'];

        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Ocena musi być od 1 do 5.']);
            return;
        }

        try {
            $review = new Review();
            if ($review->createReview($facilityId, $userId, $rating, $comment)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(409);
                echo json_encode(['error' => 'Już dodałeś opinię do tego obiektu.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd serwera: ' . $e->getMessage()]);
        }
    }

    /**
     * Update an existing review (POST /api/reviews/update)
     */
    public function updateReview(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['review_id'], $data['rating'], $data['comment'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakuje wymaganych pól.']);
            return;
        }

        $reviewId = (int)$data['review_id'];
        $rating = (int)$data['rating'];
        $comment = trim($data['comment'] ?? '');
        $userId = $_SESSION['user']['id'];

        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Ocena musi być od 1 do 5.']);
            return;
        }

        try {
            $review = new Review();
            if ($review->updateReview($reviewId, $rating, $comment, $userId)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Brak dostępu do tej opinii.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd serwera: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a review (POST /api/reviews/delete)
     */
    public function deleteReview(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['review_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakuje review_id.']);
            return;
        }

        $reviewId = (int)$data['review_id'];
        $userId = $_SESSION['user']['id'];

        try {
            $review = new Review();
            $reviewData = $review->getReviewById($reviewId);

            if (!$reviewData) {
                http_response_code(404);
                echo json_encode(['error' => 'Opinia nie znaleziona.']);
                return;
            }

            // Get facility owner ID
            global $pdo;
            $stmt = $pdo->prepare("SELECT owner_id FROM facilities WHERE id = ? LIMIT 1");
            $stmt->execute([$reviewData['facility_id']]);
            $facility = $stmt->fetch(PDO::FETCH_ASSOC);
            $facilityOwnerId = $facility ? $facility['owner_id'] : null;

            if ($review->deleteReview($reviewId, $userId, $facilityOwnerId)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Brak dostępu do tej opinii.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd serwera: ' . $e->getMessage()]);
        }
    }

    /**
     * Get all reviews for a facility (GET /api/facility/reviews?facility_id=X)
     */
    public function getReviews(): void {
        header('Content-Type: application/json');

        if (!isset($_GET['facility_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakuje facility_id.']);
            return;
        }

        $facilityId = (int)$_GET['facility_id'];

        try {
            $review = new Review();
            $reviews = $review->getReviewsByFacility($facilityId);
            $avgRating = $review->getAverageRating($facilityId);
            $breakdown = $review->getRatingBreakdown($facilityId);
            $totalReviews = array_sum($breakdown);

            echo json_encode([
                'success' => true,
                'reviews' => $reviews,
                'average_rating' => $avgRating,
                'rating_breakdown' => $breakdown,
                'total_reviews' => $totalReviews
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd serwera: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if user has reviewed a facility and get the review (GET /api/facility/user-review?facility_id=X)
     */
    public function getUserReview(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['review' => null]);
            return;
        }

        if (!isset($_GET['facility_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakuje facility_id.']);
            return;
        }

        $facilityId = (int)$_GET['facility_id'];
        $userId = $_SESSION['user']['id'];

        try {
            $review = new Review();
            $userReview = $review->getUserReview($facilityId, $userId);

            echo json_encode([
                'success' => true,
                'review' => $userReview
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd serwera: ' . $e->getMessage()]);
        }
    }
}
