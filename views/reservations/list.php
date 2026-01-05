<?php
$title = "Moje rezerwacje";

ob_start();
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-4">Moje rezerwacje</h1>

    <section class="mb-8">
        <h2 class="text-2xl font-semibold mb-2">Nadchodzące rezerwacje</h2>
        <?php if (count($upcoming) === 0): ?>
            <p class="text-gray-600">Brak nadchodzących rezerwacji.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($upcoming as $res):
                    $start = (int)explode(':', $res['start_time'])[0];
                    $end = (int)explode(':', $res['end_time'])[0];
                    $startLabel = $res['start_time'];
                    $endLabel = $res['end_time'];
                    $startDt = new DateTime($res['date'] . ' ' . $res['start_time']);
                    $now = new DateTime();
                    $cancellable = ($startDt->getTimestamp() - $now->getTimestamp()) >= 2*3600;
                    $imageId = $res['image_id'] ?? null;
                    $totalPrice = isset($res['total_price']) ? number_format((float)$res['total_price'], 2) : null;
                ?>
                    <div class="p-4 bg-white rounded shadow flex justify-between items-center cursor-pointer hover:bg-gray-50 transition" data-facility-id="<?= $res['facility_id'] ?>" onclick="window.location.href='/facility?id=<?= $res['facility_id'] ?>'">
                        <div class="flex items-center gap-4">
                            <?php if ($imageId): ?>
                                <img src="/image/facility?image_id=<?= $imageId ?>&amp;size=thumb" alt="miniatura" class="w-20 h-12 object-cover rounded">
                            <?php else: ?>
                                <div class="w-20 h-12 bg-gray-100 rounded flex items-center justify-center text-sm text-gray-400">Brak</div>
                            <?php endif; ?>
                            <div>
                                <div class="text-lg font-medium"><?= htmlspecialchars($res['facility_name'] ?? 'Obiekt') ?></div>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($res['date']) ?> — <?= htmlspecialchars($startLabel) ?> do <?= htmlspecialchars($endLabel) ?></div>
                                <div class="text-sm">Status: <strong><?php
                                    if ($res['status'] === 'pending') echo 'Oczekuje na potwierdzenie';
                                    elseif ($res['status'] === 'confirmed') echo 'Potwierdzona';
                                    elseif ($res['status'] === 'paid') echo 'Opłacona';
                                    elseif ($res['status'] === 'cancelled') echo 'Anulowana';
                                    else echo htmlspecialchars($res['status']);
                                ?></strong></div>
                            </div>
                        </div>
                        <div class="flex gap-4 items-center" onclick="event.stopPropagation()">
                            <?php if ($totalPrice !== null): ?>
                                <div class="text-right text-sm text-gray-700">Cena: <strong><?= $totalPrice ?> zł</strong></div>
                            <?php endif; ?>
                            <?php if ($res['status'] === 'confirmed'): ?>
                                <button data-id="<?= $res['id'] ?>" class="pay-btn px-3 py-2 bg-green-100 text-green-600 rounded">Opłać</button>
                            <?php endif; ?>
                            <?php if ($cancellable): ?>
                                <button data-id="<?= $res['id'] ?>" class="cancel-btn px-3 py-2 bg-red-100 text-red-600 rounded">Anuluj</button>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">Anulowanie niedostępne</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-2">Przeszłe rezerwacje</h2>
        <?php if (count($past) === 0): ?>
            <p class="text-gray-600">Brak przeszłych rezerwacji.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($past as $res):
                    $imageId = $res['image_id'] ?? null;
                    $totalPrice = isset($res['total_price']) ? number_format((float)$res['total_price'], 2) : null;
                ?>
                    <div class="p-4 bg-white rounded shadow flex justify-between items-center cursor-pointer hover:bg-gray-50 transition" data-facility-id="<?= $res['facility_id'] ?>" onclick="window.location.href='/facility?id=<?= $res['facility_id'] ?>'">
                        <div class="flex items-center gap-4">
                            <?php if ($imageId): ?>
                                <img src="/image/facility?image_id=<?= $imageId ?>&amp;size=thumb" alt="miniatura" class="w-20 h-12 object-cover rounded" style="filter:grayscale(100%); opacity:0.78;">
                            <?php else: ?>
                                <div class="w-20 h-12 bg-gray-100 rounded flex items-center justify-center text-sm text-gray-400">Brak</div>
                            <?php endif; ?>
                            <div>
                                <div class="text-lg font-medium"><?= htmlspecialchars($res['facility_name'] ?? 'Obiekt') ?></div>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($res['date']) ?> — <?= htmlspecialchars($res['start_time']) ?> do <?= htmlspecialchars($res['end_time']) ?></div>
                            </div>
                        </div>
                        <div class="flex gap-4 items-center" onclick="event.stopPropagation()">
                            <?php if ($totalPrice !== null): ?>
                                <div class="text-sm text-gray-700">Cena: <strong><?= $totalPrice ?> zł</strong></div>
                            <?php endif; ?>
                            <?php if (!$res['user_has_review']): ?>
                                <button class="review-btn px-3 py-2 bg-blue-100 text-blue-600 rounded cursor-pointer" data-facility-id="<?= $res['facility_id'] ?>" data-facility-name="<?= htmlspecialchars($res['facility_name'] ?? 'Obiekt') ?>">Oceń</button>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">Już oceniono</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-2xl font-bold mb-4">Oceń obiekt</h2>
        <p id="facilityNameInModal" class="text-gray-600 mb-4"></p>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-3">Ocena:</label>
            <div class="flex gap-3 justify-center">
                <div id="starRating" class="flex gap-2">
                    <!-- Stars will be generated by JS -->
                </div>
            </div>
            <input type="hidden" id="ratingValue" value="0">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Komentarz:</label>
            <textarea id="commentText" class="w-full border rounded p-3 text-sm resize-none" rows="4" placeholder="Podziel się swoją opinią..."></textarea>
        </div>

        <div class="flex gap-3">
            <button id="submitReview" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Wyślij opinię</button>
            <button id="closeReviewModal" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Anuluj</button>
        </div>

        <input type="hidden" id="facilityIdForReview">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const reviewModal = document.getElementById('reviewModal');
    const closeReviewModal = document.getElementById('closeReviewModal');
    const submitReview = document.getElementById('submitReview');
    const facilityIdForReview = document.getElementById('facilityIdForReview');
    const ratingValue = document.getElementById('ratingValue');
    const commentText = document.getElementById('commentText');
    const facilityNameInModal = document.getElementById('facilityNameInModal');
    const starRating = document.getElementById('starRating');

    // Generate star rating UI
    function generateStars() {
        starRating.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('button');
            star.type = 'button';
            star.className = 'star text-3xl cursor-pointer transition';
            star.textContent = '☆';
            star.dataset.rating = i;
            star.addEventListener('click', (e) => {
                e.preventDefault();
                ratingValue.value = i;
                updateStarDisplay();
            });
            star.addEventListener('mouseenter', (e) => {
                e.preventDefault();
                // Highlight up to this star on hover
                document.querySelectorAll('.star').forEach((s, idx) => {
                    s.textContent = idx < i ? '★' : '☆';
                });
            });
            starRating.appendChild(star);
        }
        starRating.addEventListener('mouseleave', updateStarDisplay);
    }

    function updateStarDisplay() {
        const rating = parseInt(ratingValue.value) || 0;
        document.querySelectorAll('.star').forEach((s, idx) => {
            s.textContent = idx < rating ? '★' : '☆';
        });
    }

    // Open review modal
    document.querySelectorAll('.review-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const facilityId = btn.getAttribute('data-facility-id');
            const facilityName = btn.getAttribute('data-facility-name');

            facilityIdForReview.value = facilityId;
            facilityNameInModal.textContent = facilityName;
            ratingValue.value = 0;
            commentText.value = '';
            generateStars();

            // Check if user already has a review
            try {
                const res = await fetch(`/api/facility/user-review?facility_id=${facilityId}`);
                const data = await res.json();
                if (data.review) {
                    // User already has a review - hide the button
                    btn.style.display = 'none';
                } else {
                    reviewModal.style.display = 'flex';
                }
            } catch (err) {
                reviewModal.style.display = 'flex';
            }
        });
    });

    // Close modal
    closeReviewModal.addEventListener('click', () => {
        reviewModal.style.display = 'none';
    });

    // Submit review
    submitReview.addEventListener('click', async () => {
        const facilityId = facilityIdForReview.value;
        const rating = parseInt(ratingValue.value);
        const comment = commentText.value.trim();

        if (rating === 0) {
            alert('Wybierz ocenę.');
            return;
        }

        try {
            const res = await fetch('/api/reviews/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    facility_id: facilityId,
                    rating: rating,
                    comment: comment
                })
            });
            const data = await res.json();
            if (data.success) {
                reviewModal.style.display = 'none';
                // Hide the review button
                document.querySelector(`[data-facility-id="${facilityId}"].review-btn`).style.display = 'none';
                alert('Opinia dodana pomyślnie!');
            } else {
                alert(data.error || 'Błąd podczas dodawania opinii.');
            }
        } catch (err) {
            alert('Błąd sieci.');
        }
    });

    // Original cancel button logic
    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = btn.getAttribute('data-id');
            if (!confirm('Czy na pewno chcesz anulować tę rezerwację?')) return;

            try {
                const res = await fetch('/api/reservations/cancel', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reservation_id: Number(id) })
                });
                const data = await res.json();
                if (data.success) {
                    btn.closest('.p-4.bg-white')?.remove();
                } else if (data.error) {
                    alert(data.error);
                }
            } catch (err) {
                alert('Błąd sieci podczas anulowania.');
            }
        });
    });

    // Pay button logic
    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = btn.getAttribute('data-id');
            window.location.href = `/pay-reservation?id=${id}`;
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layout.php";
