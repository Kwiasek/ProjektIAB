<?php
$title = "Lista obiektów sportowych";

ob_start(); // rozpocznij buforowanie treści
?>
<div class="relative flex flex-col justify-center w-full bg-[url('/images/venue.jpg')] bg-center bg-no-repeat bg-cover mx-auto min-h-[750px] rounded-xl mb-12 overflow-hidden shadow-md">
    <div class="absolute inset-0 bg-black/45"></div>

    <div class="relative px-4 gap-5 flex flex-col">
        <h1 class="text-6xl text-center font-bold text-white">
            Znajdź i zarezerwuj swoje ulubione obiekty sportowe
        </h1>
        <h3 class="text-xl text-center  text-white">
            Odkrywaj i rezerwuj obiekty sportowe niedaleko Ciebie. Czy to kort tenisowy, boisko do siatkówki albo basen, znajdziesz wszystko w jednym miejscu.
        </h3>
        <div class="self-center relative mt-2">
            <input type="text" id="hero_name" placeholder="Wpisz nazwę obiektu" class="bg-white rounded-xl w-2xl px-5 py-5"/>
            <button type="button" class="bg-blue-500 text-white rounded-xl px-5 py-3 absolute top-2 right-2 z-10 cursor-pointer font-medium" id="hero_find">Szukaj</button>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto" id="list">
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

    <h2 class="text-2xl font-bold mb-4">Dostępne obiekty</h2>
    <div id="facilities-container" class="flex flex-col gap-6"></div>
</div>
<script>
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

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
                card.className = 'bg-white shadow-md rounded-lg overflow-hidden flex p-3';
                const thumb = facility.image_id ? `/image/facility?image_id=${facility.image_id}` : '/images/venue.jpg';
                card.innerHTML = `
                    <img src="${thumb}" alt="${escapeHtml(facility.name)}" class="max-h-50 aspect-16/9 object-cover rounded-lg">
                    <div class="p-4 flex flex-col gap-2 justify-center">
                        <h2 class="font-bold text-xl">${escapeHtml(facility.name)}</h2>
                        <p class="text-gray-600">${escapeHtml(facility.location)}</p>
                        <p class="mb-2 text-gray-600">⭐ <bold class="font-semibold">4.7</bold> (600 ocen)</p>
                        <a href="/facility?id=${facility.id}" class="self-end font-medium text-blue-500 bg-blue-100 px-3 py-2 rounded-lg">Zobacz szczegóły</a>
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

    document.getElementById('hero_find').addEventListener('click', () => {
        hero_value = document.querySelector('#hero_name').value

        document.querySelector('#name').value = hero_value
        loadFacilities()
        document.querySelector('#list').scrollIntoView()

    })
</script>
<?php
$content = ob_get_clean(); // zakończ buforowanie i zapisz w $content
include __DIR__ . '/../layout.php'; // załaduj główny layout
