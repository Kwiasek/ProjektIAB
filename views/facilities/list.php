<?php
$title = "Lista obiektów sportowych";

ob_start(); // rozpocznij buforowanie treści
?>
<div class="max-w-6xl mx-auto">

    <form class="max-w-4xl mb-8 bg-gray-100 rounded-lg flex flex-wrap gap-4" id="filter-form">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nazwa obiektu</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"
                   class="mt-1 block w-48 px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="np. boisko">
        </div>

        <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Lokalizacja</label>
            <input type="text" id="location" name="location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>"
                   class="mt-1 block w-48 px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   placeholder="np. Łódź">
        </div>

        <div>
            <label for="date" class="block text-sm font-medium text-gray-700">Data dostępności</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>"
                   class="mt-1 block w-48 px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="self-end">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-lg cursor-pointer">Szukaj</button>
        </div>
    </form>

    <h2 class="text-2xl font-semibold mb-4">Dostępne obiekty</h2>
    <div id="facilities-container" class="grid grid-cols-1 md:grid-cols-3 gap-6"></div>
</div>
<script>
    async function loadFacilities() {
        const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
        const response = await fetch(`/api/facilities?${params.toString()}`);
        const data = await response.json();

        const container = document.getElementById('facilities-container');
        container.innerHTML = '';

        if (data.status === 'success') {
            if (data.data.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500 col-span-3">Brak wyników</p>';
                return;
            }

            data.data.forEach(facility => {
                const card = document.createElement('div');
                card.className = 'bg-white shadow-md rounded-lg overflow-hidden';
                card.innerHTML = `
                    <img src="${facility.image_url}" alt="${facility.name}" class="w-full h-48 object-cover">
                    <div class="p-4 flex flex-col">
                        <h2 class="font-bold text-xl mb-1">${facility.name}</h2>
                        <p class="text-gray-600 mb-2">${facility.location}</p>
                        <p class="text-gray-700 min-h-12">${facility.description.slice(0, 80)}...</p>
                        <p class="font-semibold text-blue-600 mt-2">${facility.price_per_hour} zł/h</p>
                        <a href="/facility?id=${facility.id}" class="self-end font-medium text-white bg-blue-500 px-3 py-2 rounded-lg mt-auto">Zarezerwuj</a>
                    </div>
                `;
                container.appendChild(card);
            });
        } else {
            container.innerHTML = '<p class="text-center text-red-500">Błąd ładowania danych</p>';
        }
    }

    // Załaduj dane przy starcie
    document.addEventListener('DOMContentLoaded', loadFacilities);

    // Obsługa formularza
    document.getElementById('filter-form').addEventListener('submit', e => {
        e.preventDefault();
        loadFacilities();
    });
</script>
<?php
$content = ob_get_clean(); // zakończ buforowanie i zapisz w $content
include __DIR__ . '/../layout.php'; // załaduj główny layout
