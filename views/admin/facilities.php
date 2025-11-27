<?php
$title = 'Moje obiekty';
ob_start();
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Moje obiekty</h1>
    <div class="mb-4">
        <a href="/facilities/add" class="px-3 py-2 bg-blue-500 text-white rounded">Dodaj nowy obiekt</a>
    </div>
    <div class="space-y-4">
        <?php foreach ($facilities as $f): ?>
            <div class="p-4 bg-white rounded shadow flex justify-between items-center">
                <div>
                    <div class="font-semibold"><?= htmlspecialchars($f['name']) ?></div>
                    <div class="text-sm text-gray-600"><?= htmlspecialchars($f['location']) ?></div>
                </div>
                <div class="flex gap-2">
                    <a href="/facilities/edit?id=<?= $f['id'] ?>" class="px-3 py-1 bg-yellow-200 rounded">Edytuj</a>
                    <form method="post" action="/facilities/delete" onsubmit="return confirm('Usunąć obiekt?');">
                        <input type="hidden" name="id" value="<?= $f['id'] ?>" />
                        <button type="submit" class="px-3 py-1 bg-red-200 rounded">Usuń</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
