<?php
$title = "Obiekt";

if (isset($facility)) {
    $data = $facility['data'];
}

ob_start();

?>

<main class="max-w-6xl mx-auto flex justify-around gap-6">
    <img src="<?= $data['image_url'] ?>" alt="<?= $data['name'] ?>" class="rounded-lg shadow-lg max-w-fit">
    <div class="flex flex-col gap-3">
        <h1 class="font-bold text-2xl"><?= $data['name'] ?></h1>
        <p class="mb-2"><?= $data['description']?></p>
        <h2 class="font-bold">Dostępność</h2>
        <label for="date">Wybierz datę:</label>
        <input type="date" id="date" class="border px-3 py-2 rounded-sm" />
        <div id="available-hours" class="grid grid-cols-3 gap-2"></div>
    </div>
</main>

<script>
    const dateInput = document.getElementById('date')

    function defaultDate() {
        date = new Date()
        dateString = date.getFullYear() + '-' + (+date.getMonth() + 1) + '-' + (date.getDate().toString().length === 1 ? '0' + date.getDate().toString() : date.getDate());
        dateInput.defaultValue = dateString
        dateInput.min = dateString
        console.log(dateInput)
    }

    window.addEventListener('load', async () => {
        defaultDate()
        await getAvailibility()

        dateInput.addEventListener('change', async () => {
            await getAvailibility()
        })
    })

    const searchParams = new URLSearchParams(window.location.search)
    const id = searchParams.get('id')

    async function getAvailibility() {
        date = document.getElementById('date').value
        const response = await fetch(`/api/facility/availability?id=${id}&date=${date}`)
        if (!response.ok) {
            console.log('error')
            return
        }
        const result = await response.json()


        if (result.status === 'success') {
            const container = document.querySelector('#available-hours')
            console.log(result.data)

            const data = JSON.parse(result.data)
            container.innerHTML = ''

            if (!data.available?.length) {
                container.innerHTML = '<p class="text-gray-500">Brak dostępnych godzin.</p>';
                return;
            }

            data.available.forEach(hour => {
                const btn = document.createElement('button');
                btn.className = 'px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600';
                btn.textContent = `${hour}:00`
                btn.onclick = () => selectHour(hour, date);
                container.appendChild(btn);
            })
        }
    }

    function selectHour(hour, date) {
        const duration = prompt('Na ile godzin chcesz zarezerwować? (1-3)');
        if (!duration || duration < 1 || duration > 3) {
            alert('Nieprawidłowa liczba');
            return;
        }

        fetch('/api/facilities/reserve', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                facility_id: id,
                date,
                start: hour,
                duration
            })
        })
        .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Rezerwacja dokonana!');
                    dateInput.dispatchEvent(new Event('change'));
                } else {
                    alert(data.error | 'Błąd podczas rezerwacji.')
                }
            })
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layout.php";