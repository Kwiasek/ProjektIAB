<?php

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header('location: /');
    exit;
}

$title = 'Dodaj obiekt';

ob_start();

?>

<form action="/facilities/add" method="post" class="max-w-3xl px-12 mx-auto">
    <h1 class="font-bold text-3xl mb-2">Dodaj nowy obiekt</h1>
    <p class="font-medium text-gray-400 mb-4">Podaj szczegóły swojego obiektu aby przyciągnąć uwagę potencjalnych wypożyczających.</p>
    <div class="mb-4 flex flex-col gap-1">
        <label for="name" class="font-medium">Nazwa obiektu</label>
        <input type="text" required class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium" name="name" id="name" placeholder="np. 'Boisko szkolne'"/>
    </div>
    <div class="mb-4 flex flex-col gap-1">
        <label for="location" class="font-medium">Lokalizacja</label>
        <input type="text" required class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium" name="location" id="location" placeholder="np. 'ul. Aleksandrowska 128/130, Łódź'"/>
    </div>

    <div class="mb-4 flex flex-col gap-1">
        <label for="image_url" class="font-medium">Link do zdjęcia</label>
        <input type="url" class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium" name="image_url" id="image_url""/>
    </div>

    <div class="mb-4 flex flex-col gap-1">
        <label for="description" class="font-medium">Opis</label>
        <textarea name="description" id="description"  rows="4" class="py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium"></textarea>
    </div>

    <div class="mb-6 flex flex-col gap-1">
        <label for="price" class="font-medium">Cena za godzinę</label>
        <div class="relative w-full">
            <input type="number" name="price" id="price" class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-800 font-medium after:content-['ZŁ']" min="0.00" max="10000" step="0.5" />
            <span class="absolute top-3 right-10 font-medium text-gray-400">zł/h</span>
        </div>
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
        foreach ($days as $key => $label): ?>
            <div class="grid grid-cols-4 items-center gap-3 bg-gray-100 rounded-lg mb-4 px-3">
                <div class="font-medium"><?= $label ?></div>
                <div>
                    <label class="text-sm text-gray-600 block">Od</label>
                    <input type="time" name="availability[<?= $key ?>][open]" class="py-2 px-3 rounded bg-white border border-gray-300 w-full" value="08:00">
                </div>
                <div>
                    <label class="text-sm text-gray-600 block">Do</label>
                    <input type="time" name="availability[<?= $key ?>][close]" class="py-2 px-3 rounded bg-white border border-gray-300 w-full" value="22:00">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="availability[<?= $key ?>][is_open]" id="open_<?= $key ?>" value="1" checked>
                    <label for="open_<?= $key ?>" class="text-sm text-gray-600">Otwarte</label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <p class="text-red-500 text-center mb-3"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="flex justify-end gap-4">
        <a href="/" class="px-3 py-2 bg-gray-200 text-gray-600 font-medium rounded-lg text-lg">Anuluj</a>
        <button type="submit" class="font-medium px-3 py-2 bg-blue-500 text-white rounded-lg cursor-pointer">Dodaj nowy obiekt</button>
    </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
