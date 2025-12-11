<?php

require_once __DIR__ . "/../config/db.php";

class Review {
    /**
     * Create a new review for a facility
     * @param int $facilityId
     * @param int $userId
     * @param int $rating (1-5)
     * @param string $comment
     * @return bool
     */
    public function createReview(int $facilityId, int $userId, int $rating, string $comment): bool {
        global $pdo;
        
        // Check if user already has a review for this facility
        $stmt = $pdo->prepare("SELECT id FROM facility_reviews WHERE facility_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$facilityId, $userId]);
        if ($stmt->fetch()) {
            return false; // User already has a review
        }
        
        $stmt = $pdo->prepare(
            "INSERT INTO facility_reviews (facility_id, user_id, rating, comment, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        
        return $stmt->execute([$facilityId, $userId, $rating, $comment]);
    }

    /**
     * Update an existing review
     * @param int $reviewId
     * @param int $rating
     * @param string $comment
     * @param int $userId (for verification)
     * @return bool
     */
    public function updateReview(int $reviewId, int $rating, string $comment, int $userId): bool {
        global $pdo;
        
        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM facility_reviews WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$reviewId, $userId]);
        if (!$stmt->fetch()) {
            return false;
        }
        
        $stmt = $pdo->prepare(
            "UPDATE facility_reviews SET rating = ?, comment = ? WHERE id = ?"
        );
        
        return $stmt->execute([$rating, $comment, $reviewId]);
    }

    /**
     * Delete a review
     * @param int $reviewId
     * @param int $userId (for verification - owner or facility owner)
     * @return bool
     */
    public function deleteReview(int $reviewId, int $userId, ?int $facilityOwnerId = null): bool {
        global $pdo;
        
        // Get review
        $stmt = $pdo->prepare("SELECT user_id FROM facility_reviews WHERE id = ? LIMIT 1");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$review) {
            return false;
        }
        
        // Check if user is the review author or facility owner
        if ($review['user_id'] !== $userId && $facilityOwnerId !== $userId) {
            return false;
        }
        
        $stmt = $pdo->prepare("DELETE FROM facility_reviews WHERE id = ?");
        return $stmt->execute([$reviewId]);
    }

    /**
     * Get all reviews for a facility
     * @param int $facilityId
     * @return array
     */
    public function getReviewsByFacility(int $facilityId): array {
        global $pdo;
        
        $stmt = $pdo->prepare(
            "SELECT fr.id, fr.rating, fr.comment, fr.created_at, fr.user_id, u.name as user_name
             FROM facility_reviews fr
             LEFT JOIN users u ON u.id = fr.user_id
             WHERE fr.facility_id = ?
             ORDER BY fr.created_at DESC"
        );
        $stmt->execute([$facilityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get average rating for a facility
     * @param int $facilityId
     * @return float|null
     */
    public function getAverageRating(int $facilityId): ?float {
        global $pdo;
        
        $stmt = $pdo->prepare(
            "SELECT AVG(rating) as avg_rating FROM facility_reviews WHERE facility_id = ?"
        );
        $stmt->execute([$facilityId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['avg_rating'] ? floatval($result['avg_rating']) : null;
    }

    /**
     * Get rating count breakdown for a facility
     * @param int $facilityId
     * @return array
     */
    public function getRatingBreakdown(int $facilityId): array {
        global $pdo;
        
        $stmt = $pdo->prepare(
            "SELECT rating, COUNT(*) as count FROM facility_reviews WHERE facility_id = ? GROUP BY rating"
        );
        $stmt->execute([$facilityId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $breakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($results as $row) {
            $breakdown[$row['rating']] = (int)$row['count'];
        }
        
        return $breakdown;
    }

    /**
     * Check if user has already reviewed a facility
     * @param int $facilityId
     * @param int $userId
     * @return array|null
     */
    public function getUserReview(int $facilityId, int $userId): ?array {
        global $pdo;
        
        $stmt = $pdo->prepare(
            "SELECT id, rating, comment FROM facility_reviews WHERE facility_id = ? AND user_id = ? LIMIT 1"
        );
        $stmt->execute([$facilityId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    /**
     * Get a specific review by ID
     * @param int $reviewId
     * @return array|null
     */
    public function getReviewById(int $reviewId): ?array {
        global $pdo;
        
        $stmt = $pdo->prepare(
            "SELECT fr.id, fr.facility_id, fr.user_id, fr.rating, fr.comment, fr.created_at
             FROM facility_reviews fr
             WHERE fr.id = ? LIMIT 1"
        );
        $stmt->execute([$reviewId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}
