<?php

require_once __DIR__ . "/../config/db.php";

class AdminController
{
    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function index()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];

        // Total earnings (sum of total_price for confirmed reservations for owner's facilities)
        $stmt = $this->pdo->prepare("SELECT IFNULL(SUM(r.total_price),0) as total FROM reservations r
            JOIN facilities f ON f.id = r.facility_id
            WHERE f.owner_id = ? AND r.status = 'confirmed'");
        $stmt->execute([$userId]);
        $total = $stmt->fetchColumn();

        // Earnings this month (by reservation date)
        $stmt = $this->pdo->prepare("SELECT IFNULL(SUM(r.total_price),0) as month_total FROM reservations r
            JOIN facilities f ON f.id = r.facility_id
            WHERE f.owner_id = ? AND r.status = 'confirmed' AND YEAR(r.date) = YEAR(CURDATE()) AND MONTH(r.date) = MONTH(CURDATE())");
        $stmt->execute([$userId]);
        $monthTotal = $stmt->fetchColumn();

        // Total reservations
        $stmt = $this->pdo->prepare("SELECT COUNT(r.id) FROM reservations r JOIN facilities f ON f.id = r.facility_id WHERE f.owner_id = ?");
        $stmt->execute([$userId]);
        $totalRes = $stmt->fetchColumn();

        // Reservations this month (by reservation date)
        $stmt = $this->pdo->prepare("SELECT COUNT(r.id) FROM reservations r JOIN facilities f ON f.id = r.facility_id WHERE f.owner_id = ? AND YEAR(r.date) = YEAR(CURDATE()) AND MONTH(r.date) = MONTH(CURDATE())");
        $stmt->execute([$userId]);
        $monthRes = $stmt->fetchColumn();

        // Pending reservations (status = 'pending')
        $stmt = $this->pdo->prepare("SELECT r.*, u.name as user_name, f.name as facility_name FROM reservations r JOIN users u ON u.id = r.user_id JOIN facilities f ON f.id = r.facility_id WHERE f.owner_id = ? AND r.status = 'pending' ORDER BY r.created_at DESC");
        $stmt->execute([$userId]);
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Confirmed reservations (recent)
        $stmt = $this->pdo->prepare("SELECT r.*, u.name as user_name, f.name as facility_name FROM reservations r JOIN users u ON u.id = r.user_id JOIN facilities f ON f.id = r.facility_id WHERE f.owner_id = ? AND r.status = 'confirmed' ORDER BY r.date DESC, r.start_time DESC LIMIT 50");
        $stmt->execute([$userId]);
        $confirmed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cancelled / rejected reservations (recent)
        $stmt = $this->pdo->prepare("SELECT r.*, u.name as user_name, f.name as facility_name FROM reservations r JOIN users u ON u.id = r.user_id JOIN facilities f ON f.id = r.facility_id WHERE f.owner_id = ? AND r.status = 'cancelled' ORDER BY r.date DESC, r.start_time DESC LIMIT 50");
        $stmt->execute([$userId]);
        $cancelled = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'total_earnings' => $total,
            'month_earnings' => $monthTotal,
            'total_reservations' => $totalRes,
            'month_reservations' => $monthRes,
            'pending_reservations' => count($pending),
            'pending_list' => $pending
            , 'confirmed_list' => $confirmed
            , 'cancelled_list' => $cancelled
        ];

        $data = json_encode(['status' => 'success', 'data' => $stats]);
        require_once __DIR__ . '/../../views/admin/hub.php';
    }

    public function confirmReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $reservationId = $data['reservation_id'] ?? null;
        if (!$reservationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak id rezerwacji.']);
            return;
        }

        // Verify owner owns the facility
        $stmt = $this->pdo->prepare("SELECT r.* FROM reservations r JOIN facilities f ON f.id = r.facility_id WHERE r.id = ? AND f.owner_id = ?");
        $stmt->execute([$reservationId, $_SESSION['user']['id']]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            http_response_code(403);
            echo json_encode(['error' => 'Brak uprawnień lub rezerwacja nie istnieje.']);
            return;
        }

        $upd = $this->pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
        if ($upd->execute([$reservationId])) {
            // Clear cached pending count for this owner so badge updates immediately
            if (session_status() === PHP_SESSION_NONE) session_start();
            $cacheKey = 'pending_count_cache_' . ($_SESSION['user']['id'] ?? '');
            if ($cacheKey && isset($_SESSION[$cacheKey])) unset($_SESSION[$cacheKey]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd podczas potwierdzania.']);
        }
    }

    public function rejectReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Musisz być zalogowany.']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $reservationId = $data['reservation_id'] ?? null;
        if (!$reservationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak id rezerwacji.']);
            return;
        }

        // Verify owner owns the facility
        $stmt = $this->pdo->prepare("SELECT r.* FROM reservations r JOIN facilities f ON f.id = r.facility_id WHERE r.id = ? AND f.owner_id = ?");
        $stmt->execute([$reservationId, $_SESSION['user']['id']]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            http_response_code(403);
            echo json_encode(['error' => 'Brak uprawnień lub rezerwacja nie istnieje.']);
            return;
        }

        $upd = $this->pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        if ($upd->execute([$reservationId])) {
            // Clear cached pending count for this owner so badge updates immediately
            if (session_status() === PHP_SESSION_NONE) session_start();
            $cacheKey = 'pending_count_cache_' . ($_SESSION['user']['id'] ?? '');
            if ($cacheKey && isset($_SESSION[$cacheKey])) unset($_SESSION[$cacheKey]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd podczas odrzucania.']);
        }
    }

    public function myFacilities()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare("SELECT * FROM facilities WHERE owner_id = ?");
        $stmt->execute([$userId]);
        $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../../views/admin/facilities.php';
    }

    /**
     * Return pending reservations count for the logged-in owner.
     * Uses session caching to reduce DB load (TTL = 60s).
     */
    public function pendingCount()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'count' => 0]);
            return;
        }
        $userId = $_SESSION['user']['id'];
        $cacheKey = 'pending_count_cache_' . $userId;
        $now = time();
        $ttl = 60; // seconds

        if (!isset($_SESSION[$cacheKey]) || !isset($_SESSION[$cacheKey]['ts']) || ($_SESSION[$cacheKey]['ts'] + $ttl) < $now) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(r.id) FROM reservations r JOIN facilities f ON f.id = r.facility_id WHERE f.owner_id = ? AND r.status = 'pending'");
                $stmt->execute([$userId]);
                $count = (int)$stmt->fetchColumn();
            } catch (Exception $e) {
                $count = 0;
            }
            $_SESSION[$cacheKey] = ['count' => $count, 'ts' => $now];
        } else {
            $count = (int)$_SESSION[$cacheKey]['count'];
        }

        echo json_encode(['success' => true, 'count' => $count]);
    }
}
