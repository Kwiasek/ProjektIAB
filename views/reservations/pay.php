<?php
$title = "Opłać rezerwację";

ob_start();
?>
<div class="max-w-md mx-auto">
    <h1 class="text-3xl font-bold mb-4">Opłać rezerwację</h1>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($reservation['facility_name']) ?></h2>
        <p class="text-gray-600 mb-4">
            Data: <?= htmlspecialchars($reservation['date']) ?><br>
            Godziny: <?= htmlspecialchars($reservation['start_time']) ?> - <?= htmlspecialchars($reservation['end_time']) ?><br>
            Cena: <?= number_format($reservation['total_price'], 2) ?> zł
        </p>

        <form id="paymentForm" class="space-y-4">
            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">

            <div>
                <label class="block text-sm font-medium mb-1">Numer karty kredytowej</label>
                <input type="text" name="card_number" id="card_number" class="w-full border rounded p-2" placeholder="1234 5678 9012 3456" maxlength="19">
                <span class="text-red-500 text-sm" id="card_number_error"></span>
            </div>

            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">Data ważności (MM/YY)</label>
                    <input type="text" name="expiry" id="expiry" class="w-full border rounded p-2" placeholder="12/25" maxlength="5">
                    <span class="text-red-500 text-sm" id="expiry_error"></span>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">CVV</label>
                    <input type="text" name="cvv" id="cvv" class="w-full border rounded p-2" placeholder="123" maxlength="3">
                    <span class="text-red-500 text-sm" id="cvv_error"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Nazwisko na karcie</label>
                <input type="text" name="cardholder_name" id="cardholder_name" class="w-full border rounded p-2" placeholder="Jan Kowalski">
                <span class="text-red-500 text-sm" id="cardholder_name_error"></span>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Zapłać</button>
        </form>
    </div>
</div>

<script>
document.getElementById('paymentForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    // Clear previous errors
    document.querySelectorAll('.text-red-500').forEach(el => el.textContent = '');

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    // Validation
    let isValid = true;

    // Card number: 16 digits, spaces allowed
    const cardNumber = data.card_number.replace(/\s/g, '');
    if (!/^\d{16}$/.test(cardNumber)) {
        document.getElementById('card_number_error').textContent = 'Numer karty musi mieć 16 cyfr.';
        isValid = false;
    }

    // Expiry: MM/YY
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(data.expiry)) {
        document.getElementById('expiry_error').textContent = 'Nieprawidłowa data ważności (MM/YY).';
        isValid = false;
    } else {
        const [month, year] = data.expiry.split('/');
        const now = new Date();
        const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
        if (expiryDate <= now) {
            document.getElementById('expiry_error').textContent = 'Karta jest przedawniona.';
            isValid = false;
        }
    }

    // CVV: 3 digits
    if (!/^\d{3}$/.test(data.cvv)) {
        document.getElementById('cvv_error').textContent = 'CVV musi mieć 3 cyfry.';
        isValid = false;
    }

    // Cardholder name: not empty
    if (!data.cardholder_name.trim()) {
        document.getElementById('cardholder_name_error').textContent = 'Nazwisko jest wymagane.';
        isValid = false;
    }

    if (!isValid) return;

    try {
        const res = await fetch('/api/reservations/pay', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert('Płatność została zrealizowana pomyślnie!');
            window.location.href = '/my-reservations';
        } else {
            alert(result.error || 'Błąd podczas płatności.');
        }
    } catch (err) {
        alert('Błąd sieci.');
    }
});

// Format card number with spaces
document.getElementById('card_number').addEventListener('input', (e) => {
    let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value;
});

// Format expiry
document.getElementById('expiry').addEventListener('input', (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// CVV only digits
document.getElementById('cvv').addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layout.php";
?>