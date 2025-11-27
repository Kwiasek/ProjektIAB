<?php
$title = "Obiekt";

if (isset($facility['data'])) {
    $data = $facility['data'];
}

ob_start();

?>

<main class="max-w-6xl mx-auto flex gap-6">
    <div class="rounded-lg shadow-lg max-w-3/5 h-max aspect-16/9 bg-gray-100 flex items-center justify-center overflow-hidden relative">
        <?php $imageIds = $data['image_ids'] ?? []; ?>
        <?php if (!empty($imageIds) && is_array($imageIds)): ?>
            <button id="prevImg" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/60 rounded-full px-3 py-2">‹</button>
            <img id="facilityImage" src="/image/facility?image_id=<?= $imageIds[0] ?>&amp;size=medium" alt="<?= htmlspecialchars($data['name']) ?>" class="w-full h-full object-cover object-center">
            <button id="nextImg" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/60 rounded-full px-3 py-2">›</button>
            <div id="imageDots" class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2"></div>
        <?php else: ?>
            <div class="text-center p-6 text-gray-500">Brak zdjęć obiektu</div>
        <?php endif; ?>
    </div>
    <div class="flex flex-col gap-3">
        <h1 class="font-bold text-2xl"><?= $data['name'] ?></h1>
        <p class="mb-2"><?= $data['description']?></p>
        <h2 class="font-bold">Dostępność</h2>
        <label for="date">Wybierz datę:</label>
        <input type="date" id="date" class="border px-3 py-2 rounded-sm" />
        <h2 class="font-bold mt-6 mb-2">Dostępne godziny</h2>
        <div id="hoursContainer" class="grid grid-cols-3 gap-2"></div>
        <div id="selectionSummary" class="text-sm text-gray-600 mt-2"></div>
        <div id="confirmMessage" class="mt-2 hidden p-3 rounded bg-green-50 text-green-800"></div>
        <button id="reserveBtn" class="mt-4 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:cursor-pointer disabled:cursor-not-allowed disabled:bg-blue-300" disabled>Zarezerwuj</button>
    </div>
</main>
<script>
    // DOM references and state
    const dateInput = document.getElementById('date');
    const hoursContainer = document.getElementById('hoursContainer');
    const reserveBtn = document.getElementById('reserveBtn');
    let selectedStart = null;
    let selectedEnd = null;
    const facilityId = <?= $id ?>;
    const pricePerHour = <?= floatval($data['price_per_hour'] ?? 0) ?>;
    const today = new Date().toISOString().split('T')[0];

    // Slider init using image IDs
    function slider(){
        const imageIds = <?= json_encode($data['image_ids'] ?? []) ?>;
        if (!imageIds || imageIds.length === 0) return;
        let idx = 0;
        const imgEl = document.getElementById('facilityImage');
        const prev = document.getElementById('prevImg');
        const next = document.getElementById('nextImg');
        const dots = document.getElementById('imageDots');

        function renderDots(){
            dots.innerHTML = '';
            imageIds.forEach((_, i) => {
                const d = document.createElement('button');
                d.className = 'w-2 h-2 rounded-full bg-white/60 cursor-pointer';
                d.addEventListener('click', () => show(i));
                dots.appendChild(d);
            });
        }

        function show(i){
            idx = (i + imageIds.length) % imageIds.length;
            imgEl.src = `/image/facility?image_id=${imageIds[idx]}&size=medium`;
            // update dots
            Array.from(dots.children).forEach((c, ci) => c.style.opacity = ci === idx ? '1' : '0.5');
        }

        prev.addEventListener('click', () => show(idx - 1));
        next.addEventListener('click', () => show(idx + 1));
        renderDots();
        show(0);
    };


function renderAvailableHours(hours) {
    hoursContainer.innerHTML = ''
    const selectedDate = dateInput.value || today;
    const now = new Date();
    const currentHour = now.getHours();
    const isToday = selectedDate === today;

        const filteredHours = hours.filter(hour => {
        // Jeśli wybrano dzisiaj — ukryj godziny, które już minęły (hour < currentHour)
        if (isToday) return hour >= currentHour;
        return true;
    });

    if (filteredHours.length === 0) {
        hoursContainer.innerHTML = '<p>Brak wolnych terminów w tym dniu.</p>'
        reserveBtn.disabled = true
        return
    }

        filteredHours.forEach(hour => {
            const btn = document.createElement('button')
            btn.className = 'hour-btn px-3 py-2 bg-blue-300 text-white rounded hover:bg-blue-500'
            btn.textContent = `${hour}:00 - ${hour + 1}:00`

            btn.addEventListener('click', () => {
                // If no start selected => set start
                if (selectedStart === null) {
                    selectedStart = hour;
                    selectedEnd = null;
                } else if (selectedStart !== null && selectedEnd === null) {
                    // set end
                    if (hour === selectedStart) {
                        // deselect
                        selectedStart = null;
                        selectedEnd = null;
                    } else {
                        selectedEnd = hour;
                        if (selectedEnd < selectedStart) {
                            const t = selectedStart; selectedStart = selectedEnd; selectedEnd = t;
                        }
                    }
                } else {
                    // both set => start new selection
                    selectedStart = hour;
                    selectedEnd = null;
                }

                // update UI: highlight range
                document.querySelectorAll('.hour-btn').forEach(b => {
                    b.classList.remove('bg-blue-500');
                    b.classList.add('bg-blue-300');
                });
                if (selectedStart !== null && selectedEnd === null) {
                    Array.from(hoursContainer.children).forEach(ch => {
                        if (ch.textContent.startsWith(`${selectedStart}:00`)) {
                            ch.classList.remove('bg-blue-300'); ch.classList.add('bg-blue-500');
                        }
                    });
                } else if (selectedStart !== null && selectedEnd !== null) {
                    for (let h = selectedStart; h <= selectedEnd; h++) {
                        Array.from(hoursContainer.children).forEach(ch => {
                            if (ch.textContent.startsWith(`${h}:00`)) {
                                ch.classList.remove('bg-blue-300'); ch.classList.add('bg-blue-500');
                            }
                        });
                    }
                }

                // enable/disable reserve button and update summary
                const summaryEl = document.getElementById('selectionSummary');
                if (selectedStart !== null && selectedEnd !== null) {
                    const duration = selectedEnd - selectedStart + 1;
                    const total = (pricePerHour * duration).toFixed(2);
                    summaryEl.textContent = `Wybrano: ${selectedStart}:00 - ${selectedEnd + 1}:00 — Cena: ${total} zł`;
                    reserveBtn.disabled = false;
                } else if (selectedStart !== null && selectedEnd === null) {
                    const duration = 1;
                    const total = (pricePerHour * duration).toFixed(2);
                    summaryEl.textContent = `Wybrano: ${selectedStart}:00 - ${selectedStart + 1}:00 — Cena: ${total} zł`;
                    reserveBtn.disabled = false;
                } else {
                    summaryEl.textContent = '';
                    reserveBtn.disabled = true;
                }
            })

            hoursContainer.appendChild(btn)
    })
}

async function fetchAvailability(date) {
    hoursContainer.innerHTML = '<p>Wczytywanie dostępnych terminów...</p>';
    try {
        const res = await fetch(`/api/facility/availability?id=${facilityId}&date=${date}`)
        const data = await res.json()
        if (!data.data || !data.data.available || data.data.available.length === 0) {
            hoursContainer.innerHTML = '<p>Brak wolnych terminów w tym dniu.</p>'
            reserveBtn.disabled = true
            return
        }
        renderAvailableHours(data.data.available)
    } catch (err) {
        hoursContainer.innerHTML = '<p>Błąd podczas pobierania terminów.</p>'
    }
}

window.addEventListener('load', () => {
    slider();
    dateInput.value = today;
    dateInput.min = today;
    dateInput.addEventListener('change', (e) => {
        fetchAvailability(e.target.value)
        reserveBtn.disabled = true
    })

    reserveBtn.addEventListener('click', async () => {
        if (selectedStart === null) return;
        const date = dateInput.value;
        const start = selectedStart;
        const end = (selectedEnd !== null) ? (selectedEnd + 1) : (selectedStart + 1);

        try {
            const res = await fetch('/api/facilities/reserve', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    facility_id: facilityId,
                    date,
                    start,
                    end
                })
            });
            const data = await res.json();
            const confirmEl = document.getElementById('confirmMessage');
            if (data.error) {
                confirmEl.classList.remove('hidden');
                confirmEl.classList.remove('bg-green-50');
                confirmEl.classList.add('bg-red-50');
                confirmEl.classList.remove('text-green-800');
                confirmEl.classList.add('text-red-800');
                confirmEl.textContent = data.error;
                return;
            }

            if (data.success) {
                const priceText = data.total_price ? ` Cena: ${data.total_price} zł.` : '';
                confirmEl.classList.remove('hidden');
                confirmEl.classList.remove('bg-red-50');
                confirmEl.classList.add('bg-green-50');
                confirmEl.classList.remove('text-red-800');
                confirmEl.classList.add('text-green-800');
                confirmEl.textContent = `Rezerwacja pomyślna!${priceText}`;
                // refresh availability to reflect the new booking
                fetchAvailability(date);
                // reset selection and UI
                selectedStart = null; selectedEnd = null;
                const summaryEl = document.getElementById('selectionSummary');
                summaryEl.textContent = '';
                reserveBtn.disabled = true;
            } else {
                confirmEl.classList.remove('hidden');
                confirmEl.classList.remove('bg-green-50');
                confirmEl.classList.add('bg-red-50');
                confirmEl.classList.remove('text-green-800');
                confirmEl.classList.add('text-red-800');
                confirmEl.textContent = 'Nie udało się zarezerwować tego terminu.';
            }
        } catch (e) {
            const confirmEl = document.getElementById('confirmMessage');
            confirmEl.classList.remove('hidden');
            confirmEl.classList.remove('bg-green-50');
            confirmEl.classList.add('bg-red-50');
            confirmEl.classList.remove('text-green-800');
            confirmEl.classList.add('text-red-800');
            confirmEl.textContent = 'Błąd sieci podczas rezerwacji.';
            console.error(e)
        }
    })

    fetchAvailability(today)

})





</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layout.php";