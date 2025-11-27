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
                    <div class="p-4 bg-white rounded shadow flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <?php if ($imageId): ?>
                                <img src="/image/facility?image_id=<?= $imageId ?>&amp;size=thumb" alt="miniatura" class="w-20 h-12 object-cover rounded">
                            <?php else: ?>
                                <div class="w-20 h-12 bg-gray-100 rounded flex items-center justify-center text-sm text-gray-400">Brak</div>
                            <?php endif; ?>
                            <div>
                                <div class="text-lg font-medium"><?= htmlspecialchars($res['facility_name'] ?? 'Obiekt') ?></div>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($res['date']) ?> — <?= htmlspecialchars($startLabel) ?> do <?= htmlspecialchars($endLabel) ?></div>
                                <div class="text-sm">Status: <strong><?= $res['status'] === 'pending' ? 'Oczekuje na potwierdzenie' : 'Potwierdzona' ?></strong></div>
                            </div>
                        </div>
                        <div class="flex gap-4 items-center">
                            <?php if ($totalPrice !== null): ?>
                                <div class="text-right text-sm text-gray-700">Cena: <strong><?= $totalPrice ?> zł</strong></div>
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
                    <div class="p-4 bg-white rounded shadow flex justify-between items-center">
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
                        <?php if ($totalPrice !== null): ?>
                            <div class="text-sm text-gray-700">Cena: <strong><?= $totalPrice ?> zł</strong></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
                    // usuń element z DOM (najbliższy element karty)
                    btn.closest('.p-4.bg-white')?.remove();
                } else if (data.error) {
                    alert(data.error);
                }
            } catch (err) {
                alert('Błąd sieci podczas anulowania.');
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layout.php";
