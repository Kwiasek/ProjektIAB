<?php
$title = "Lista obiektów sportowych";

ob_start(); // rozpocznij buforowanie treści
?>
<div class="max-w-6xl mx-auto">
    <input name="search" class="w-full mb-3 text-gray-800 py-3 px-5  border border-gray-300 rounded-lg bg-white" type="text" placeholder="Wyszukaj obiektów sportowych">
    <div class="flex gap-3 mb-12">
        <select class="bg-white border border-gray-300 text-gray-800 px-4 py-2 rounded font-medium">
            <option value="">Data</option>
        </select>
        <select class="bg-white border border-gray-300 text-gray-800 px-4 py-2 rounded font-medium">
            <option value="">Sport</option>
        </select>
        <select class="bg-white border border-gray-300 text-gray-800 px-4 py-2 rounded font-medium">
            <option value="">Lokalizacja</option>
        </select>
    </div>

    <h2 class="text-2xl font-semibold mb-4">Dostępne obiekty</h2>

    <div class="flex flex-col gap-6">
        <?php if (!empty($facilities)): ?>
            <?php foreach ($facilities as $f): ?>
                <div class="bg-white shadow p-4 rounded-lg flex gap-5">
                    <img src="<?= htmlspecialchars($f['image_url']) ?>" alt="<?= htmlspecialchars($f['name']) ?>" class="rounded-lg max-h-[200px]">
                    <div class="flex flex-col gap-2">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($f['name']) ?></h3>
                        <p class="text-gray-700"><?= htmlspecialchars($f['description']) ?></p>
                        <p class="text-blue-600 font-semibold mt-2"><?= $f['price_per_hour'] ?> zł/h</p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean(); // zakończ buforowanie i zapisz w $content
include __DIR__ . '/../layout.php'; // załaduj główny layout
