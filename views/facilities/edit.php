<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header('location: /');
    exit;
}

$title = 'Edytuj obiekt';
ob_start();

$data = $facility ?? [];
?>

<form action="/facilities/update" method="post" enctype="multipart/form-data" class="max-w-3xl px-12 mx-auto">
    <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>" />
    <h1 class="font-bold text-3xl mb-2">Edytuj obiekt</h1>

    <div class="mb-4 flex flex-col gap-1">
        <label for="name" class="font-medium">Nazwa obiektu</label>
        <input type="text" required class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium" name="name" id="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>"/>
    </div>

    <div class="mb-4 flex flex-col gap-1">
        <label for="location" class="font-medium">Lokalizacja</label>
        <input type="text" required class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium" name="location" id="location" value="<?= htmlspecialchars($data['location'] ?? '') ?>"/>
    </div>

    <div class="mb-4">
        <label class="font-medium">Aktualne zdjęcia</label>
        <div class="flex gap-3 mt-2">
            <?php if (!empty($data['image_ids'])): foreach ($data['image_ids'] as $idx => $imgId): ?>
                <div class="text-center">
                    <img src="/image/facility?image_id=<?= $imgId ?>&amp;size=thumb" alt="img<?= $idx ?>" class="w-40 h-28 object-cover rounded" />
                    <div class="mt-2">
                        <label class="text-sm"><input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($imgId) ?>"> Usuń</label>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-gray-500">Brak zdjęć</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-4 flex flex-col gap-1">
        <label for="images" class="font-medium">Dodaj zdjęcia (opcjonalnie)</label>
        <input type="file" name="images[]" id="images" accept="image/*" multiple />
    </div>

    <div class="mb-4 flex flex-col gap-1">
        <label for="description" class="font-medium">Opis</label>
        <textarea name="description" id="description"  rows="4" class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-6 flex flex-col gap-1">
        <label for="price" class="font-medium">Cena za godzinę</label>
        <input type="number" name="price" id="price" value="<?= htmlspecialchars($data['price_per_hour'] ?? '') ?>" class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium" min="0" step="0.5" />
    </div>

    <hr class="my-6 border-gray-300">

    <h2 class="font-bold text-2xl mb-4">Godziny otwarcia</h2>
    <p class="text-gray-500 mb-3">Dla każdego dnia określ godziny otwarcia i zamknięcia. Zaznacz, jeśli obiekt jest otwarty danego dnia.</p>

    <div class="space-y-3">
        <?php
        $days = [
            'monday' => 'Poniedziałek',
            'tuesday' => 'Wtorek',
            'wednesday' => 'Środa',
            'thursday' => 'Czwartek',
            'friday' => 'Piątek',
            'saturday' => 'Sobota',
            'sunday' => 'Niedziela'
        ];
        // Prepare schedule array
        $scheduleArray = [];
        if (isset($schedule)) {
            foreach ($schedule as $s) {
                $scheduleArray[$s['day_of_week']] = $s;
            }
        }
        foreach ($days as $key => $label):
            $current = $scheduleArray[$key] ?? ['open_time' => '08:00', 'close_time' => '22:00', 'is_open' => 1];
        ?>
            <div class="grid grid-cols-4 items-center gap-3 bg-gray-100 rounded-lg mb-4 px-3">
                <div class="font-medium"><?= $label ?></div>
                <div>
                    <label class="text-sm text-gray-600 block">Od</label>
                    <input type="time" name="availability[<?= $key ?>][open]" class="py-2 px-3 rounded bg-white border border-gray-300 w-full" value="<?= htmlspecialchars(substr($current['open_time'], 0, 5)) ?>">
                </div>
                <div>
                    <label class="text-sm text-gray-600 block">Do</label>
                    <input type="time" name="availability[<?= $key ?>][close]" class="py-2 px-3 rounded bg-white border border-gray-300 w-full" value="<?= htmlspecialchars(substr($current['close_time'], 0, 5)) ?>">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="availability[<?= $key ?>][is_open]" id="open_<?= $key ?>" value="1" <?= $current['is_open'] ? 'checked' : '' ?>>
                    <label for="open_<?= $key ?>" class="text-sm text-gray-600">Otwarte</label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex justify-end gap-4">
        <a href="/facility?id=<?= htmlspecialchars($data['id']) ?>" class="px-3 py-2 bg-gray-200 text-gray-600 font-medium rounded-lg text-lg">Anuluj</a>
        <button type="submit" class="font-medium px-3 py-2 bg-blue-500 text-white rounded-lg cursor-pointer">Zapisz</button>
    </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
