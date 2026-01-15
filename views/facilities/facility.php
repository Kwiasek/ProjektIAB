<?php
$title = "Obiekt";

if (isset($facility['data'])) {
    $data = $facility['data'];
}

ob_start();
?>

<main class="max-w-6xl mx-auto flex gap-6">
    <div class="flex overflow-hidden relative mb-auto">
        <?php $imageIds = $data['image_ids'] ?? []; ?>
        <?php if (!empty($imageIds) && is_array($imageIds)): ?>
            <button id="prevImg" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/60 rounded-full px-3 py-2">‹</button>
            <img id="facilityImage" src="/image/facility?image_id=<?= $imageIds[0] ?>&amp;size=medium" alt="<?= htmlspecialchars($data['name']) ?>" class="w-full object-contain">
            <button id="nextImg" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/60 rounded-full px-3 py-2">›</button>
            <div id="imageDots" class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2"></div>
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-center text-gray-500">Brak zdjęć obiektu</div>
        <?php endif; ?>
    </div>
    <div class="flex flex-col gap-3">
        <div class="flex items-center gap-2">
            <h1 class="font-bold text-2xl"><?= htmlspecialchars($data['name']) ?></h1>
            <button id="likeBtn" class="text-3xl cursor-pointer transition-transform hover:scale-125" title="Polub ten obiekt">❤️</button>
        </div>
        <p class="mb-2"><?= htmlspecialchars($data['description'])?></p>
        
        <!-- Schedule -->
        <h2 class="font-bold">Harmonogram</h2>
        <div class="space-y-1 text-sm">
            <?php 
            $dayTranslation = [
                'monday' => 'Poniedziałek',
                'tuesday' => 'Wtorek',
                'wednesday' => 'Środa',
                'thursday' => 'Czwartek',
                'friday' => 'Piątek',
                'saturday' => 'Sobota',
                'sunday' => 'Niedziela'
            ];
            foreach ($schedule as $day): ?>
                <div class="flex py-1 justify-between w-full">
                    <span><?= htmlspecialchars($dayTranslation[$day['day_of_week']] ?? $day['day_of_week']) ?>:</span>
                    <span class="font-medium">
                        <?php if ($day['is_open']): ?>
                            <?= substr($day['open_time'], 0, 5) ?> - <?= substr($day['close_time'], 0, 5) ?>
                        <?php else: ?>
                            <span class="text-red-600">Zamknięte</span>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['user'])): ?>
            <!-- Reservation section - visible only for logged-in users -->
            <h2 class="font-bold mt-6">Dostępność</h2>
            <label for="date">Wybierz datę:</label>
            <input type="date" id="date" class="border px-3 py-2 rounded-sm" />
            <h2 class="font-bold mt-6 mb-2">Dostępne godziny</h2>
            <div id="hoursContainer" class="grid grid-cols-3 gap-2"></div>
            <div id="selectionSummary" class="text-sm text-gray-600 mt-2"></div>
            <div id="confirmMessage" class="mt-2 hidden p-3 rounded bg-green-50 text-green-800"></div>
            <button id="reserveBtn" class="mt-4 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:cursor-pointer disabled:cursor-not-allowed disabled:bg-blue-300" disabled>Zarezerwuj</button>
        <?php else: ?>
            <!-- Message for non-logged users -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded text-blue-800">
                <p class="font-semibold">Aby dokonać rezerwacji, musisz się zalogować</p>
                <p class="text-sm mt-1"><a href="/login" class="underline font-medium">Zaloguj się</a> lub <a href="/register" class="underline font-medium">zarejestruj nowe konto</a></p>
            </div>
        <?php endif; ?>
    </div>
</main>
<script>
    // Slider init (always available)
    const facilityId = <?= $id ?>;
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

    slider();

    // Reservation section - only for logged-in users
    <?php if (isset($_SESSION['user'])): ?>
    // DOM references and state
    const dateInput = document.getElementById('date');
    const hoursContainer = document.getElementById('hoursContainer');
    const reserveBtn = document.getElementById('reserveBtn');
    let selectedStart = null;
    let selectedEnd = null;
    const pricePerHour = <?= floatval($data['price_per_hour'] ?? 0) ?>;
    const today = new Date().toISOString().split('T')[0];


function renderAvailableHours(data) {
    hoursContainer.innerHTML = ''
    const selectedDate = dateInput.value || today;
    const now = new Date();
    const currentHour = now.getHours();
    const isToday = selectedDate === today;

    const available = data.available || [];
    const open = data.open;
    const close = data.close;

    if (!open || !close) {
        hoursContainer.innerHTML = '<p>Obiekt zamknięty w tym dniu.</p>'
        reserveBtn.disabled = true
        return
    }

    const allHours = [];
    for (let h = open; h < close; h++) {
        allHours.push(h);
    }

    const filteredHours = allHours.filter(hour => {
        // Jeśli wybrano dzisiaj — ukryj godziny, które już minęły (hour < currentHour)
        if (isToday) return hour >= currentHour;
        return true;
    });

    if (filteredHours.length === 0) {
        hoursContainer.innerHTML = '<p>Brak terminów w tym dniu.</p>'
        reserveBtn.disabled = true
        return
    }

    filteredHours.forEach(hour => {
        const isAvailable = available.includes(hour);
        const btn = document.createElement('button')
        btn.className = isAvailable ? 'hour-btn px-3 py-2 bg-blue-300 text-white rounded hover:bg-blue-500' : 'hour-btn px-3 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed'
        btn.textContent = `${hour}:00 - ${hour + 1}:00`
        if (!isAvailable) {
            btn.disabled = true;
        }

        btn.addEventListener('click', () => {
            if (!isAvailable) return; // nie pozwalaj klikać na zajęte

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
                if (b.disabled) return; // nie zmieniaj zajętych
                b.classList.remove('bg-blue-500');
                b.classList.add('bg-blue-300');
            });
            if (selectedStart !== null && selectedEnd === null) {
                Array.from(hoursContainer.children).forEach(ch => {
                    if (ch.textContent.startsWith(`${selectedStart}:00`) && !ch.disabled) {
                        ch.classList.remove('bg-blue-300'); ch.classList.add('bg-blue-500');
                    }
                });
            } else if (selectedStart !== null && selectedEnd !== null) {
                for (let h = selectedStart; h <= selectedEnd; h++) {
                    Array.from(hoursContainer.children).forEach(ch => {
                        if (ch.textContent.startsWith(`${h}:00`) && !ch.disabled) {
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
        if (!data.data || (!data.data.available && !data.data.open)) {
            hoursContainer.innerHTML = '<p>Brak terminów w tym dniu.</p>'
            reserveBtn.disabled = true
            return
        }
        renderAvailableHours(data.data)
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
<?php endif; ?>
</script>

<!-- Reviews Section -->
<section id="reviewsSection" class="max-w-6xl mx-auto mt-12 py-8 border-t">
    <h2 class="text-3xl font-bold mb-6">Opinie użytkowników</h2>
    
    <!-- Rating Summary -->
    <div id="ratingSummary" class="bg-gray-50 rounded-lg p-6 mb-8">
        <div class="grid grid-cols-2 gap-8">
            <!-- Average Rating -->
            <div>
                <div class="flex items-baseline gap-2">
                    <span id="avgRating" class="text-4xl font-bold">-</span>
                    <span class="text-xl text-gray-600">/5</span>
                </div>
                <div id="starDisplay" class="text-3xl mt-2"></div>
                <p id="totalReviews" class="text-sm text-gray-600 mt-2">-</p>
            </div>
            
            <!-- Rating Breakdown -->
            <div>
                <div id="ratingBreakdown" class="space-y-2">
                    <!-- Generated by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Review Button -->
    <?php if (isset($_SESSION['user'])): ?>
        <div class="mb-8">
            <button id="addReviewBtn" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">Dodaj opinię</button>
        </div>
    <?php endif; ?>

    <!-- Reviews List -->
    <div id="reviewsList" class="space-y-6">
        <!-- Reviews will be loaded here -->
    </div>
</section>

<!-- Add Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-2xl font-bold mb-4">Oceń obiekt</h2>
        <p id="facilityNameInModal" class="text-gray-600 mb-4"></p>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-3">Ocena:</label>
            <div class="flex gap-3 justify-center">
                <div id="starRating" class="flex gap-2">
                    <!-- Stars will be generated by JS -->
                </div>
            </div>
            <input type="hidden" id="ratingValue" value="0">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Komentarz:</label>
            <textarea id="commentText" class="w-full border rounded p-3 text-sm resize-none" rows="4" placeholder="Podziel się swoją opinią..."></textarea>
        </div>

        <div class="flex gap-3">
            <button id="submitReview" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Wyślij opinię</button>
            <button id="closeReviewModal" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Anuluj</button>
        </div>

        <input type="hidden" id="facilityIdForReview">
    </div>
</div>

<!-- Edit Review Modal -->
<div id="editReviewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-2xl font-bold mb-4">Edytuj opinię</h2>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-3">Ocena:</label>
            <div id="editStarRating" class="flex gap-2 justify-center">
                <!-- Stars will be generated by JS -->
            </div>
            <input type="hidden" id="editRatingValue" value="0">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Komentarz:</label>
            <textarea id="editCommentText" class="w-full border rounded p-3 text-sm resize-none" rows="4"></textarea>
        </div>

        <div class="flex gap-3">
            <button id="submitEditReview" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz zmiany</button>
            <button id="closeEditModal" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Anuluj</button>
        </div>

        <input type="hidden" id="editReviewId">
    </div>
</div>

<script>
// Reviews functionality
document.addEventListener('DOMContentLoaded', () => {
    const facilityId = <?= $id ?>;
    const userId = <?= isset($_SESSION['user']) ? $_SESSION['user']['id'] : 'null' ?>;
    const facilityOwnerId = <?= $data['owner_id'] ?? 'null' ?>;

    async function loadReviews() {
        try {
            const res = await fetch(`/api/facility/reviews?facility_id=${facilityId}`);
            const data = await res.json();

            if (data.success) {
                displayRatingSummary(data.average_rating, data.rating_breakdown, data.total_reviews);
                displayReviews(data.reviews);

                // Check if user already has a review
                if (userId && userId !== 'null') {
                    const userReviewRes = await fetch(`/api/facility/user-review?facility_id=${facilityId}`);
                    const userReviewData = await userReviewRes.json();
                    const addReviewBtn = document.getElementById('addReviewBtn');
                    if (userReviewData.review && addReviewBtn) {
                        addReviewBtn.style.display = 'none';
                    }
                }
            }
        } catch (err) {
            console.error('Error loading reviews:', err);
        }
    }

    function displayRatingSummary(avgRating, breakdown, totalReviews) {
        const avgEl = document.getElementById('avgRating');
        const starDisplay = document.getElementById('starDisplay');
        const totalEl = document.getElementById('totalReviews');
        const breakdownEl = document.getElementById('ratingBreakdown');

        if (avgRating !== null) {
            avgEl.textContent = avgRating.toFixed(1);
            starDisplay.innerHTML = generateStars(Math.round(avgRating), false);
        }
        totalEl.textContent = `${totalReviews} ${totalReviews === 1 ? 'opinia' : 'opinii'}`;

        // Breakdown
        breakdownEl.innerHTML = '';
        for (let i = 5; i >= 1; i--) {
            const count = breakdown[i] || 0;
            const percent = totalReviews > 0 ? Math.round((count / totalReviews) * 100) : 0;
            const bar = document.createElement('div');
            bar.className = 'flex items-center gap-2';
            bar.innerHTML = `
                <span class="text-sm w-8">${i}★</span>
                <div class="flex-1 bg-gray-300 rounded h-2">
                    <div class="bg-yellow-400 h-2 rounded" style="width: ${percent}%"></div>
                </div>
                <span class="text-sm text-gray-600 w-12 text-right">${percent}%</span>
            `;
            breakdownEl.appendChild(bar);
        }
    }

    function displayReviews(reviews) {
        const reviewsList = document.getElementById('reviewsList');
        reviewsList.innerHTML = '';

        if (reviews.length === 0) {
            reviewsList.innerHTML = '<p class="text-center text-gray-600 py-8">Brak opinii. Bądź pierwszy do wystawienia opinii!</p>';
            return;
        }

        reviews.forEach(review => {
            const isOwner = userId === review.user_id;
            const isFacilityOwner = userId === facilityOwnerId;
            
            const reviewEl = document.createElement('div');
            reviewEl.className = 'rounded-lg p-4 bg-white';
            reviewEl.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-lg">${htmlEscape(review.user_name || 'Anonimowy')}</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400">${generateStars(review.rating, false)}</span>
                            <span class="text-sm text-gray-600">${review.rating}.0/5.0</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">${formatDate(review.created_at)}</p>
                    </div>
                    <div class="flex gap-2">
                        ${isOwner ? `
                            <button class="edit-review-btn px-3 py-1 bg-blue-100 text-blue-600 rounded text-sm" data-review-id="${review.id}" data-rating="${review.rating}" data-comment="${htmlEscape(review.comment)}">Edytuj</button>
                            <button class="delete-review-btn px-3 py-1 bg-red-100 text-red-600 rounded text-sm" data-review-id="${review.id}">Usuń</button>
                        ` : isFacilityOwner ? `
                            <button class="delete-review-btn px-3 py-1 bg-red-100 text-red-600 rounded text-sm" data-review-id="${review.id}">Usuń</button>
                        ` : ''}
                    </div>
                </div>
                <p class="text-gray-700">${htmlEscape(review.comment)}</p>
            `;
            reviewsList.appendChild(reviewEl);
        });

        // Attach event listeners
        document.querySelectorAll('.edit-review-btn').forEach(btn => {
            btn.addEventListener('click', openEditModal);
        });
        document.querySelectorAll('.delete-review-btn').forEach(btn => {
            btn.addEventListener('click', deleteReview);
        });
    }

    // Handle Add Review button
    const addReviewBtn = document.getElementById('addReviewBtn');
    if (addReviewBtn) {
        addReviewBtn.addEventListener('click', async () => {
            const reviewModal = document.getElementById('reviewModal');
            const facilityNameInModal = document.getElementById('facilityNameInModal');
            const facilityIdForReview = document.getElementById('facilityIdForReview');
            const ratingValue = document.getElementById('ratingValue');
            const commentText = document.getElementById('commentText');
            const starRating = document.getElementById('starRating');

            facilityIdForReview.value = facilityId;
            facilityNameInModal.textContent = <?= json_encode($data['name']) ?>;
            ratingValue.value = 0;
            commentText.value = '';
            generateReviewStars();

            // Check if user already has a review
            try {
                const res = await fetch(`/api/facility/user-review?facility_id=${facilityId}`);
                const data = await res.json();
                if (data.review) {
                    // User already has a review - show message
                    alert('Już wystawiłeś opinię dla tego obiektu.');
                    return;
                } else {
                    reviewModal.style.display = 'flex';
                }
            } catch (err) {
                reviewModal.style.display = 'flex';
            }
        });
    }

    // Handle Review Modal
    const reviewModal = document.getElementById('reviewModal');
    const closeReviewModal = document.getElementById('closeReviewModal');
    const submitReview = document.getElementById('submitReview');
    
    if (closeReviewModal) {
        closeReviewModal.addEventListener('click', () => {
            reviewModal.style.display = 'none';
        });
    }

    if (submitReview) {
        submitReview.addEventListener('click', async () => {
            const facilityIdForReview = document.getElementById('facilityIdForReview');
            const ratingValue = document.getElementById('ratingValue');
            const commentText = document.getElementById('commentText');

            const facilityId = facilityIdForReview.value;
            const rating = parseInt(ratingValue.value);
            const comment = commentText.value.trim();

            if (rating === 0) {
                alert('Wybierz ocenę.');
                return;
            }

            try {
                const res = await fetch('/api/reviews/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        facility_id: facilityId,
                        rating: rating,
                        comment: comment
                    })
                });
                const data = await res.json();
                if (data.success) {
                    reviewModal.style.display = 'none';
                    loadReviews();
                    if (addReviewBtn) {
                        addReviewBtn.style.display = 'none';
                    }
                    alert('Opinia dodana pomyślnie!');
                } else {
                    alert(data.error || 'Błąd podczas dodawania opinii.');
                }
            } catch (err) {
                alert('Błąd sieci.');
            }
        });
    }

    function generateReviewStars() {
        const starRating = document.getElementById('starRating');
        const ratingValue = document.getElementById('ratingValue');
        
        starRating.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('button');
            star.type = 'button';
            star.className = 'star text-3xl cursor-pointer transition';
            star.textContent = '☆';
            star.dataset.rating = i;
            star.addEventListener('click', (e) => {
                e.preventDefault();
                ratingValue.value = i;
                updateReviewStarDisplay();
            });
            star.addEventListener('mouseenter', (e) => {
                e.preventDefault();
                document.querySelectorAll('#starRating .star').forEach((s, idx) => {
                    s.textContent = idx < i ? '★' : '☆';
                });
            });
            starRating.appendChild(star);
        }
        starRating.addEventListener('mouseleave', updateReviewStarDisplay);
    }

    function updateReviewStarDisplay() {
        const ratingValue = document.getElementById('ratingValue');
        const rating = parseInt(ratingValue.value) || 0;
        document.querySelectorAll('#starRating .star').forEach((s, idx) => {
            s.textContent = idx < rating ? '★' : '☆';
        });
    }

    function openEditModal(e) {
        const reviewId = e.target.getAttribute('data-review-id');
        const rating = parseInt(e.target.getAttribute('data-rating'));
        const comment = e.target.getAttribute('data-comment');

        document.getElementById('editReviewId').value = reviewId;
        document.getElementById('editRatingValue').value = rating;
        document.getElementById('editCommentText').value = comment;

        generateEditStars();
        document.getElementById('editReviewModal').style.display = 'flex';
    }

    function generateEditStars() {
        const starContainer = document.getElementById('editStarRating');
        const ratingValue = document.getElementById('editRatingValue');
        
        starContainer.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('button');
            star.type = 'button';
            star.className = 'edit-star text-3xl cursor-pointer transition';
            star.textContent = i <= parseInt(ratingValue.value) ? '★' : '☆';
            star.dataset.rating = i;
            star.addEventListener('click', (e) => {
                e.preventDefault();
                ratingValue.value = i;
                updateEditStarDisplay();
            });
            star.addEventListener('mouseenter', (e) => {
                e.preventDefault();
                document.querySelectorAll('.edit-star').forEach((s, idx) => {
                    s.textContent = idx < i ? '★' : '☆';
                });
            });
            starContainer.appendChild(star);
        }
        starContainer.addEventListener('mouseleave', updateEditStarDisplay);
    }

    function updateEditStarDisplay() {
        const rating = parseInt(document.getElementById('editRatingValue').value) || 0;
        document.querySelectorAll('.edit-star').forEach((s, idx) => {
            s.textContent = idx < rating ? '★' : '☆';
        });
    }

    document.getElementById('closeEditModal').addEventListener('click', () => {
        document.getElementById('editReviewModal').style.display = 'none';
    });

    document.getElementById('submitEditReview').addEventListener('click', async () => {
        const reviewId = document.getElementById('editReviewId').value;
        const rating = parseInt(document.getElementById('editRatingValue').value);
        const comment = document.getElementById('editCommentText').value.trim();

        if (rating === 0) {
            alert('Wybierz ocenę.');
            return;
        }

        try {
            const res = await fetch('/api/reviews/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    review_id: reviewId,
                    rating: rating,
                    comment: comment
                })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('editReviewModal').style.display = 'none';
                loadReviews();
                alert('Opinia zaktualizowana!');
            } else {
                alert(data.error || 'Błąd podczas aktualizacji opinii.');
            }
        } catch (err) {
            alert('Błąd sieci.');
        }
    });

    async function deleteReview(e) {
        const reviewId = e.target.getAttribute('data-review-id');
        if (!confirm('Czy na pewno chcesz usunąć tę opinię?')) return;

        try {
            const res = await fetch('/api/reviews/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ review_id: reviewId })
            });
            const data = await res.json();
            if (data.success) {
                loadReviews();
                alert('Opinia usunięta!');
            } else {
                alert(data.error || 'Błąd podczas usuwania opinii.');
            }
        } catch (err) {
            alert('Błąd sieci.');
        }
    }

    function generateStars(rating, interactive = false) {
        let html = '';
        const fullStars = Math.floor(rating);
        const hasHalf = rating % 1 >= 0.5;
        
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                html += '★';
            } else if (i === fullStars + 1 && hasHalf) {
                html += '½';
            } else {
                html += '☆';
            }
        }
        return html;
    }

    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Dzisiaj o ' + date.getHours() + ':' + String(date.getMinutes()).padStart(2, '0');
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Wczoraj o ' + date.getHours() + ':' + String(date.getMinutes()).padStart(2, '0');
        } else {
            return date.toLocaleDateString('pl-PL');
        }
    }

    // Load reviews on page load
    loadReviews();
});

// Like button
const likeBtn = document.getElementById('likeBtn');
async function updateLikeButton() {
    const res = await fetch(`/api/facilities/is-liked?facility_id=${facilityId}`);
    const data = await res.json();
    if (data.liked) {
        likeBtn.textContent = '💔';
        likeBtn.title = 'Usuń polubienie';
    } else {
        likeBtn.textContent = '❤️';
        likeBtn.title = 'Polub ten obiekt';
    }
}
likeBtn.addEventListener('click', async () => {
    const res = await fetch('/api/facilities/like', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `facility_id=${facilityId}`
    });
    const data = await res.json();
    if (data.success) {
        updateLikeButton();
    }
});
updateLikeButton(); // initial load
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layout.php";