<?php
$title = 'Panel administracyjny';
$stats = json_decode($data, true)['data'] ?? [];
ob_start();
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Panel administracyjny</h1>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="p-4 bg-white rounded shadow">
            <div class="text-sm text-gray-500">Ogólne zarobki</div>
            <div class="text-xl font-semibold"><?= number_format($stats['total_earnings'] ?? 0, 2) ?> zł</div>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <div class="text-sm text-gray-500">Zarobki w tym miesiącu</div>
            <div class="text-xl font-semibold"><?= number_format($stats['month_earnings'] ?? 0, 2) ?> zł</div>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <div class="text-sm text-gray-500">Liczba rezerwacji</div>
            <div class="text-xl font-semibold"><?= $stats['total_reservations'] ?? 0 ?></div>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <div class="text-sm text-gray-500">Rezerwacje w tym miesiącu</div>
            <div class="text-xl font-semibold"><?= $stats['month_reservations'] ?? 0 ?></div>
        </div>
    </div>

    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Rezerwacje oczekujące (<?= $stats['pending_reservations'] ?? 0 ?>)</h2>
        <div id="pendingList">
            <?php foreach ($stats['pending_list'] as $r): ?>
                <div class="flex items-center justify-between p-2 border-b">
                    <div>
                        <div class="font-medium"><?= htmlspecialchars($r['facility_name']) ?></div>
                        <div class="text-sm text-gray-600"><?= htmlspecialchars($r['user_name']) ?> — <?= $r['date'] ?> <?= $r['start_time'] ?>-<?= $r['end_time'] ?></div>
                    </div>
                    <div class="flex gap-2">
                        <button data-id="<?= $r['id'] ?>" class="confirmBtn px-3 py-1 bg-green-500 text-white rounded">Potwierdź</button>
                        <button data-id="<?= $r['id'] ?>" class="rejectBtn px-3 py-1 bg-red-400 text-white rounded">Odrzuć</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Potwierdzone rezerwacje (ostatnie)</h2>
        <div class="space-y-2">
            <?php if (!empty($stats['confirmed_list'])): foreach ($stats['confirmed_list'] as $c): ?>
                <div class="p-2 border-b flex justify-between items-center">
                    <div>
                        <div class="font-medium"><?= htmlspecialchars($c['facility_name']) ?></div>
                        <div class="text-sm text-gray-600"><?= htmlspecialchars($c['user_name']) ?> — <?= $c['date'] ?> <?= $c['start_time'] ?>-<?= $c['end_time'] ?></div>
                    </div>
                    <div class="text-sm text-gray-700 font-medium"><?= number_format($c['total_price'],2) ?> zł</div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-gray-500">Brak potwierdzonych rezerwacji.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Opłacone rezerwacje (ostatnie)</h2>
        <div class="space-y-2">
            <?php if (!empty($stats['paid_list'])): foreach ($stats['paid_list'] as $p): ?>
                <div class="p-2 border-b flex justify-between items-center">
                    <div>
                        <div class="font-medium"><?= htmlspecialchars($p['facility_name']) ?></div>
                        <div class="text-sm text-gray-600"><?= htmlspecialchars($p['user_name']) ?> — <?= $p['date'] ?> <?= $p['start_time'] ?>-<?= $p['end_time'] ?></div>
                    </div>
                    <div class="text-sm text-green-700 font-medium"><?= number_format($p['total_price'],2) ?> zł</div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-gray-500">Brak opłaconych rezerwacji.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Odrzucone / anulowane rezerwacje (ostatnie)</h2>
        <div class="space-y-2">
            <?php if (!empty($stats['cancelled_list'])): foreach ($stats['cancelled_list'] as $c): ?>
                <div class="p-2 border-b flex justify-between items-center">
                    <div>
                        <div class="font-medium"><?= htmlspecialchars($c['facility_name']) ?></div>
                        <div class="text-sm text-gray-600"><?= htmlspecialchars($c['user_name']) ?> — <?= $c['date'] ?> <?= $c['start_time'] ?>-<?= $c['end_time'] ?></div>
                    </div>
                    <div class="text-sm text-gray-500"><?= number_format($c['total_price'],2) ?> zł</div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-gray-500">Brak odrzuconych rezerwacji.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Zarządzaj obiektami</h2>
        <div class="flex gap-3">
            <a href="/facilities/add" class="px-3 py-2 bg-blue-500 text-white rounded">Dodaj nowy obiekt</a>
            <a href="/admin/facilities" class="px-3 py-2 bg-gray-200 rounded">Moje obiekty</a>
        </div>
    </div>

</div>

<script>
document.addEventListener('click', async (e) => {
    if (e.target.matches('.confirmBtn')) {
        const id = e.target.dataset.id;
        if (!confirm('Potwierdzić rezerwację?')) return;
        const res = await fetch('/admin/reservations/confirm', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ reservation_id: Number(id) })
        });
        const data = await res.json();
        if (data.success) location.reload(); else alert(data.error || 'Błąd');
    }
    if (e.target.matches('.rejectBtn')) {
        const id = e.target.dataset.id;
        if (!confirm('Odrzucić rezerwację?')) return;
        const res = await fetch('/admin/reservations/reject', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ reservation_id: Number(id) })
        });
        const data = await res.json();
        if (data.success) location.reload(); else alert(data.error || 'Błąd');
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
