<?php

require_once __DIR__ . "/../config/db.php";

class Facility {
    /**
     * Create facility and save availability and uploaded images (BLOBs) to DB.
     * @param string $name
     * @param string $description
     * @param string $location
     * @param float $price
     * @param array $availability
     * @param array|null $imageFiles - contents of `$_FILES['images']` (optional)
     */
    public function createFacility($name, $description, $location, $price, $availability, $imageFiles = null) {
        global $pdo;
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO facilities (owner_id, name, description, location, price_per_hour, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user']['id'], $name, $description, $location, floatval($price)]);

            $res = $stmt->rowCount();

            if ($res <= 0) {
                $_SESSION['error'] = 'Błąd serwera';
                header("location: /facilities/add");
                exit;
            }

            $facilityId = $pdo->lastInsertId();
            foreach ($availability as $day => $data) {
                $open = $data['open'] ?? "00:00";
                $close = $data['close'] ?? "00:00";
                $is_open = isset($data['is_open']) ? 1 : 0;
                $result = $this->createFacilityAvailability($facilityId, $day, $open, $close, $is_open);
                if ($result <= 0) {
                    return json_encode([
                        'success' => false
                    ]);
                }
            }

            // Zapisz zdjęcia (jeśli przesłano)
            if ($imageFiles && isset($imageFiles['tmp_name']) ) {
                // obsługa kilku plików przesłanych jako images[]
                $count = is_array($imageFiles['tmp_name']) ? count($imageFiles['tmp_name']) : 0;
                for ($i = 0; $i < $count; $i++) {
                    $tmp = $imageFiles['tmp_name'][$i];
                    $name = $imageFiles['name'][$i];
                    $error = $imageFiles['error'][$i];
                    if ($error !== UPLOAD_ERR_OK) continue;
                    // Validation: max size 5MB, only images
                    $size = filesize($tmp);
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    if ($size > $maxSize) continue;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp) ?: 'application/octet-stream';
                    finfo_close($finfo);
                    $allowed = ['image/jpeg','image/png','image/webp'];
                    if (!in_array($mime, $allowed)) continue;
                    $dataBlob = file_get_contents($tmp);

                    $imgStmt = $pdo->prepare("INSERT INTO facility_images (facility_id, mime_type, data, created_at) VALUES (?, ?, ?, NOW())");
                    $imgStmt->execute([$facilityId, $mime, $dataBlob]);
                }
            }

            $pdo->commit();

            return json_encode([
                'success' => true
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();

            throw new Exception($e->getMessage(), $pdo->lastInsertId());
        }

    }

    public function updateFacility($id, $name, $description, $location, $price, $availability, $imageFiles = null, $deleteImageIds = []) {
        global $pdo;

        try {
            // Rozpocznij transakcję
            $pdo->beginTransaction();

            // Aktualizacja głównej tabeli facilities
            $stmt = $pdo->prepare("
            UPDATE facilities 
            SET name = ?, description = ?, location = ?, price_per_hour = ?
            WHERE id = ?
        ");

            if (!$stmt->execute([$name, $description, $location, $price, $id])) {
                throw new Exception('Nie udało się zaktualizować danych obiektu.');
            }

            // Aktualizacja dostępności dla każdego dnia tygodnia
            foreach ($availability as $day => $data) {
                $open = $data['open'] ?? "00:00";
                $close = $data['close'] ?? "00:00";
                $is_open = isset($data['is_open']) ? 1 : 0;

                // Sprawdź, czy rekord istnieje
                $checkStmt = $pdo->prepare("
                SELECT id FROM facility_availability
                WHERE facility_id = ? AND day_of_week = ?
            ");
                $checkStmt->execute([$id, $day]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Aktualizuj istniejący
                    $updateStmt = $pdo->prepare("
                    UPDATE facility_availability
                    SET open_time = ?, close_time = ?, is_open = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                    if (!$updateStmt->execute([$open, $close, $is_open, $existing['id']])) {
                        throw new Exception('Nie udało się zaktualizować godzin otwarcia dla ' . $day);
                    }
                } else {
                    // Dodaj nowy
                    $insertStmt = $pdo->prepare("
                    INSERT INTO facility_availability (facility_id, day_of_week, open_time, close_time, is_open)
                    VALUES (?, ?, ?, ?, ?)
                ");
                    if (!$insertStmt->execute([$id, $day, $open, $close, $is_open])) {
                        throw new Exception('Nie udało się dodać godzin otwarcia dla ' . $day);
                    }
                }
            }

            // Jeśli wszystko się udało — zatwierdź

            // Usuń wskazane zdjęcia
            if (!empty($deleteImageIds) && is_array($deleteImageIds)) {
                // zabezpieczenie: pracujemy tylko na intach
                $placeholders = implode(',', array_fill(0, count($deleteImageIds), '?'));
                $delStmt = $pdo->prepare("DELETE FROM facility_images WHERE id IN ($placeholders) AND facility_id = ?");
                $params = array_merge($deleteImageIds, [$id]);
                $delStmt->execute($params);
            }

            // Dodaj nowe zdjęcia jeśli przesłano
            if ($imageFiles && isset($imageFiles['tmp_name'])) {
                $count = is_array($imageFiles['tmp_name']) ? count($imageFiles['tmp_name']) : 0;
                for ($i = 0; $i < $count; $i++) {
                    $tmp = $imageFiles['tmp_name'][$i];
                    $error = $imageFiles['error'][$i];
                    if ($error !== UPLOAD_ERR_OK) continue;
                    $size = filesize($tmp);
                    $maxSize = 5 * 1024 * 1024;
                    if ($size > $maxSize) continue;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp) ?: 'application/octet-stream';
                    finfo_close($finfo);
                    $allowed = ['image/jpeg','image/png','image/webp'];
                    if (!in_array($mime, $allowed)) continue;
                    $dataBlob = file_get_contents($tmp);
                    $imgStmt = $pdo->prepare("INSERT INTO facility_images (facility_id, mime_type, data, created_at) VALUES (?, ?, ?, NOW())");
                    $imgStmt->execute([$id, $mime, $dataBlob]);
                }
            }

            $pdo->commit();

            return json_encode([
                'success' => true,
                'message' => 'Obiekt i jego dostępność zostały pomyślnie zaktualizowane.'
            ]);

        } catch (Exception $e) {
            // W razie błędu — wycofaj wszystkie zmiany
            $pdo->rollBack();
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    public function getFacility($id) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
        if (!$stmt->execute([$id])) {
            return null;
        }
        $facility = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($facility) {
            $imgs = $this->getFacilityImages($id);
            $facility['image_ids'] = $imgs['image_ids'] ?? [];
            // expose first image id for quick thumbnail use
            $facility['first_image_id'] = $facility['image_ids'][0] ?? null;
        }
        return $facility;
    }

    /**
     * Pobiera obrazy obiektu, zwraca tablicę data-URL (base64)
     */
    public function getFacilityImages($facilityId): array
    {
        global $pdo;

        $stmt = $pdo->prepare("SELECT id FROM facility_images WHERE facility_id = ? ORDER BY id ASC");
        $stmt->execute([$facilityId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $imageIds = [];
        foreach ($rows as $r) {
            $imageIds[] = $r['id'];
        }
        return ['image_ids' => $imageIds];
    }

    public function getFacilities() {
        global $pdo;
        $sql = "SELECT * FROM facilities";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deleteFacility($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM facilities WHERE id = ?");
        if (!$stmt->execute([$id])) {
            return false;
        }
        return true;
    }


    public function getFiltered($filters) {
        global $pdo;
        // Include first image id (thumbnail) as image_id, average rating and review count
        $sql = "SELECT f.*, (
            SELECT id FROM facility_images fi WHERE fi.facility_id = f.id ORDER BY id ASC LIMIT 1
        ) AS image_id,
        AVG(r.rating) AS avg_rating,
        COUNT(r.id) AS review_count
        FROM facilities f
        LEFT JOIN facility_reviews r ON r.facility_id = f.id";
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
                WHERE fa.day_of_week = DAYNAME(:date) AND fa.is_open = 1
            ";
            $params[':date'] = $filters['date'];
        }

        if (!empty($conditions)) {
            $sql .=  (str_contains($sql, 'WHERE') ? ' AND ' : ' WHERE ') . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY f.id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filtr po polubionych
        if (!empty($filters['liked_only'])) {
            $userId = $_SESSION['user']['id'] ?? null;
            if ($userId) {
                $likedIds = $this->getLikedFacilities($userId);
            } else {
                $likedIds = $_SESSION['liked_facilities'] ?? [];
            }
            $facilities = array_filter($facilities, function($f) use ($likedIds) {
                return in_array($f['id'], $likedIds);
            });
        }

        return $facilities;
    }

    /** Serve raw image blob by image id */
    public function serveImageById($imageId): void
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT mime_type, data FROM facility_images WHERE id = ? LIMIT 1");
        $stmt->execute([$imageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            // fallback to a static placeholder image if DB image not found
            $fallback = __DIR__ . '/../../public/images/venue.jpg';
            if (file_exists($fallback)) {
                $mime = mime_content_type($fallback) ?: 'image/jpeg';
                header('Content-Type: ' . $mime);
                readfile($fallback);
                return;
            }
            http_response_code(404);
            return;
        }
        $mime = $row['mime_type'];
        $data = $row['data'];

        // support size param: thumb or medium (else full)
        $size = $_GET['size'] ?? null;
        if ($size === 'thumb' || $size === 'medium') {
            // If GD is available, try to resize. If not, fall back to original blob.
            if (function_exists('imagecreatefromstring')) {
                // Try to create GD image from string
                $im = @imagecreatefromstring($data);
                if ($im !== false) {
                    $origW = imagesx($im);
                    $origH = imagesy($im);
                    $targetW = ($size === 'thumb') ? 300 : 900;
                    if ($origW <= $targetW) {
                        // no resize needed
                        header('Content-Type: ' . $mime);
                        echo $data;
                        imagedestroy($im);
                        exit;
                    }
                    $ratio = $origH / $origW;
                    $targetH = (int)round($targetW * $ratio);
                    $dst = imagecreatetruecolor($targetW, $targetH);
                    // preserve transparency for png/webp
                    if ($mime === 'image/png' || $mime === 'image/webp') {
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                        imagefilledrectangle($dst, 0, 0, $targetW, $targetH, $transparent);
                    }
                    imagecopyresampled($dst, $im, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

                    // output resized image in same mime
                    header('Content-Type: ' . $mime);
                    if ($mime === 'image/png') {
                        imagepng($dst);
                    } elseif ($mime === 'image/webp') {
                        if (function_exists('imagewebp')) imagewebp($dst);
                        else imagejpeg($dst, null, 85);
                    } else {
                        // default to jpeg
                        imagejpeg($dst, null, 85);
                    }
                    imagedestroy($dst);
                    imagedestroy($im);
                    exit;
                }
                // if GD failed to read image, fall through to original blob
            } else {
                // GD not available — return original blob to avoid fatal error
                header('Content-Type: ' . $mime);
                echo $data;
                exit;
            }
        }

        header('Content-Type: ' . $mime);
        echo $data;
        exit;
    }

    /** Serve first image of a facility (by facility id) */
    public function serveFirstImageOfFacility($facilityId): void
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM facility_images WHERE facility_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$facilityId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            return;
        }
        // delegate to serveImageById to respect size param
        $this->serveImageById($row['id']);
    }

    public function createFacilityAvailability($facility_id, $day, $open, $close, $is_open): ?int
    {
        global $pdo;

        $stmt = $pdo->prepare("
        INSERT INTO facility_availability
        (facility_id, day_of_week, open_time, close_time, is_open) 
        VALUES (?, ?, ?, ?, ?)       
        ");
        if (!$stmt->execute([$facility_id, $day, $open, $close, $is_open])) {
            return null;
        }
        return $stmt->rowCount();
    }

    public function getFacilityAvailability($facilityId, $day) {
        global $pdo;

        $stmt = $pdo->prepare("
        SELECT * FROM facility_availability
        WHERE facility_id = ? AND day_of_week = ?
        ");
        if (!$stmt->execute([$facilityId, $day])) {
            return null;
        }
        return $stmt->fetch();
    }

    public function updateFacilityAvailability($id, $day, $open, $close, $is_open) {
        global $pdo;

        $stmt = $pdo->prepare("
        UPDATE facility_availability
        SET day_of_week = ?, open_time = ?, close_time = ?, is_open = ?, updated_at = NOW()
        WHERE id = ?
    ");

        if (!$stmt->execute([$day, $open, $close, $is_open, $id])) {
            return null;
        }
        return $stmt->rowCount();
    }

    public function deleteFacilityAvailability($id) {
        global $pdo;

        $stmt = $pdo->prepare("DELETE FROM facility_availability WHERE id = ?");
        if (!$stmt->execute([$id])) {
            return false;
        }
        return true;
    }

    public function getAvailabilityWithReservations($facilityId, $date): array
    {
        global $pdo;

        $dayOfWeek = strtolower(date('l', strtotime($date)));

        $stmt = $pdo->prepare("
            SELECT open_time, close_time, is_open
            FROM facility_availability
            WHERE facility_id = ? AND day_of_week = ?
        ");
        $stmt->execute([$facilityId, $dayOfWeek]);
        $availability = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!isset($availability) || $availability['is_open'] !== 1) {
            return ['available' => [], 'open' => null, 'close' => null];
        }

        $open = (int) explode(':', $availability['open_time'])[0];
        $close = (int) explode(':', $availability['close_time'])[0];
        $availableHours = range($open, $close - 1);


        $stmt = $pdo->prepare("
            SELECT start_time, end_time
            FROM reservations
            WHERE facility_id = ? AND date = ? AND status != 'cancelled'
        ");
        $stmt->execute([$facilityId, $date]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as $res) {
            $start = (int) explode(':', $res['start_time'])[0];
            $end = (int) explode(':', $res['end_time'])[0];
            for ($i = $start; $i < $end; $i++) {
                $index = array_search($i, $availableHours);
                if ($index !== false) unset($availableHours[$index]);
            }
        }

        sort($availableHours);
        return [
            'available' => $availableHours,
            'open' => $open,
            'close' => $close,
        ];
    }

    /**
     * Get facility opening hours for all days of the week
     * @param int $facilityId
     * @return array
     */
    public function getFacilitySchedule($facilityId): array
    {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT day_of_week, open_time, close_time, is_open
            FROM facility_availability
            WHERE facility_id = ?
            ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
        ");
        $stmt->execute([$facilityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function likeFacility($userId, $facilityId) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT IGNORE INTO facility_likes (user_id, facility_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $facilityId]);
    }

    public function unlikeFacility($userId, $facilityId) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM facility_likes WHERE user_id = ? AND facility_id = ?");
        return $stmt->execute([$userId, $facilityId]);
    }

    public function isLiked($userId, $facilityId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT 1 FROM facility_likes WHERE user_id = ? AND facility_id = ?");
        $stmt->execute([$userId, $facilityId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function getLikedFacilities($userId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT facility_id FROM facility_likes WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}