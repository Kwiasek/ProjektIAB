<?php
$title = "Obiekt";

if (isset($facility['data'])) {
    $data = $facility['data'];
}

ob_start();

?>

<main class="max-w-6xl mx-auto flex gap-6">
    <img src="<?= $data['image_url'] ?>" alt="<?= $data['name'] ?>" class="rounded-lg shadow-lg max-w-3/5 h-max aspect-16/9 object-cover object-center">
    <div class="flex flex-col gap-3">
        <h1 class="font-bold text-2xl"><?= $data['name'] ?></h1>
        <p class="mb-2"><?= $data['description']?></p>
        <h2 class="font-bold">Dostępność</h2>
        <label for="date">Wybierz datę:</label>
        <input type="date" id="date" class="border px-3 py-2 rounded-sm" />
        <h2 class="font-bold mt-6 mb-2">Dostępne godziny</h2>
        <div id="hoursContainer" class="grid grid-cols-3 gap-2"></div>
        <button id="reserveBtn" class="mt-4 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:cursor-pointer disabled:cursor-not-allowed disabled:bg-blue-300" disabled>Zarezerwuj</button>
    </div>
</main>

<script>
const dateInput = document.getElementById('date');
const hoursContainer = document.getElementById('hoursContainer');
const reserveSection = document.getElementById('reserveSection');
const reserveBtn = document.getElementById('reserveBtn');

let selectedHour = null;
const facilityId = <?= $id ?>;

const today = new Date().toISOString().split('T')[0];
dateInput.value = today;
dateInput.min = today;

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
        console.error(err)
        hoursContainer.innerHTML = '<p>Błąd podczas pobierania terminów.</p>'
    }

}

function renderAvailableHours(hours) {
    hoursContainer.innerHTML = ''
    hours.forEach(hour => {
        const btn = document.createElement('button')
        btn.className = 'hour-btn px-3 py-2 bg-blue-300 text-white rounded hover:bg-blue-500'
        btn.textContent = `${hour}:00 - ${hour + 1}:00`

        btn.addEventListener('click', () => {
            document.querySelectorAll('.hour-btn').forEach(b => {
                b.classList.remove('bg-blue-500')
                b.classList.add('bg-blue-300')
            })
            btn.classList.remove('bg-blue-300')
            btn.classList.add('bg-blue-500')
            selectedHour = hour
            reserveBtn.disabled = false
        })

        hoursContainer.appendChild(btn)
    })
}

window.addEventListener('load', () => {
    dateInput.addEventListener('change', (e) => {
        fetchAvailability(e.target.value)
        reserveBtn.disabled = true
    })

    reserveBtn.addEventListener('click', async () => {
        if (!selectedHour) return
        const date = dateInput.value
        const start = selectedHour
        const end = selectedHour + 1

        await fetch('/api/facilities/reserve', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                facility_id: facilityId,
                date,
                start,
                end
            })
        }).then(r => r.json())
            .then(data => {
                console.log(data)
                if (data.error) {
                    alert(data.error)
                }
                if (data.success) {
                    alert('Rezerwacja pomyślna!')
                    fetchAvailability(date)
                } else {
                    alert('Nie udało się zarezerwować tego terminu.')
                }
            })
    })

    fetchAvailability(today)

})





</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layout.php";