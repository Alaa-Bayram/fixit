// workers.js - Main functionality for workers page

// Helper function to fetch unavailable times
function fetchUnavailableTimes(workerId, date, ulElement) {
    if (!workerId || !date || !ulElement) {
        console.error('Invalid parameters:', { workerId, date, ulElement });
        return;
    }

    fetch(`../api/fetch_unavailability.php?worker_id=${workerId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            console.log('Fetched unavailability:', data);
            ulElement.innerHTML = '';

            if (data.success) {
                const unavailableTimes = data.unavailable_times;
                if (unavailableTimes.length > 0) {
                    unavailableTimes.forEach(time => {
                        const li = document.createElement('li');
                        li.textContent = time;
                        ulElement.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.textContent = 'No appointments';
                    ulElement.appendChild(li);
                }
            } else {
                console.error('Error fetching unavailability:', data.message);
                const li = document.createElement('li');
                li.textContent = 'Error fetching unavailability';
                ulElement.appendChild(li);
            }
        })
        .catch(error => {
            console.error('Error fetching unavailability:', error);
            const li = document.createElement('li');
            li.textContent = 'Error fetching unavailability';
            ulElement.appendChild(li);
        });
}

// Enable search functionality
function enableSearch() {
    const search = document.querySelector(".search-box input");
    const cards = document.querySelectorAll(".card-worker");

    if (!search || !cards.length) return;

    search.addEventListener("keyup", () => {
        const searchValue = search.value.trim().toLowerCase();

        cards.forEach(card => {
            const cardName = card.querySelector('h3')?.textContent.toLowerCase() || '';
            card.style.display = cardName.includes(searchValue) ? "flex" : "none";
        });
    });

    search.addEventListener("keyup", () => {
        if (search.value === "") {
            cards.forEach(card => {
                card.style.display = "flex";
            });
        }
    });
}

// Initialize appointment buttons
function initAppointmentButtons() {
    document.querySelectorAll('.take-appointment-btn').forEach(button => {
        button.addEventListener('click', () => {
            const workerId = button.getAttribute('data-worker-id');
            const workerName = button.getAttribute('data-worker-name');

            document.getElementById('worker-id').value = workerId;
            document.getElementById('worker-name').innerText = workerName;
            document.body.classList.add("show-popup");

            const dateInput = document.getElementById('appointment-date');
            const unavailableTimesList = document.getElementById('unavailable-times-list');

            if (dateInput && unavailableTimesList) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);

                dateInput.addEventListener('change', () => {
                    const selectedDate = dateInput.value;
                    fetchUnavailableTimes(workerId, selectedDate, unavailableTimesList);
                });

                if (dateInput.value) {
                    fetchUnavailableTimes(workerId, dateInput.value, unavailableTimesList);
                }
            }
        });
    });
}

// Initialize review buttons
function initReviewButtons() {
    document.querySelectorAll('.add-review-btn').forEach(button => {
        button.addEventListener('click', () => {
            const workerId = button.getAttribute('data-worker-id');
            const workerName = button.getAttribute('data-worker-name');
            const workerService = button.getAttribute('data-worker-service');

            document.getElementById('review-worker-id').value = workerId;
            document.getElementById('review-worker-name').textContent = workerName;
            document.getElementById('review-worker-service').textContent = workerService;

            document.body.classList.add("show-popup-review");
        });
    });
}

// Fetch workers by region
function fetchWorkersByRegion(region, serviceId) {
    if (!region || !serviceId) return;

    fetch(`../api/fetch_workers_by_region.php?region=${encodeURIComponent(region)}&service_id=${encodeURIComponent(serviceId)}`)
        .then(response => response.json())
        .then(data => {
            const workersList = document.getElementById('workers-list');
            workersList.innerHTML = '';

            if (data.success && data.workers.length > 0) {
                renderWorkersList(data.workers);
            } else {
                workersList.innerHTML = `
                    <li class="no-workers-message">
                        <p>${data.message || 'No workers available in this region'}</p>
                    </li>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching workers:', error);
            document.getElementById('workers-list').innerHTML = `
                <li class="no-workers-message">
                    <p>Error fetching workers by region</p>
                </li>
            `;
        });
}

// Fetch workers near client
function fetchWorkersNearYou(clientId, serviceId) {
    fetch(`../api/workers_nearby.php?client_id=${clientId}&service_id=${encodeURIComponent(serviceId)}`)
        .then(response => response.json())
        .then(data => {
            const workersList = document.getElementById('workers-list');
            workersList.innerHTML = '';

            if (data.success && data.workers.length > 0) {
                renderWorkersList(data.workers);
            } else {
                workersList.innerHTML = `
                    <li class="no-workers-message">
                        <p>${data.message || 'No workers near you for this service'}</p>
                    </li>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching nearby workers:', error);
            document.getElementById('workers-list').innerHTML = `
                <li class="no-workers-message">
                    <p>Error finding nearby workers</p>
                </li>
            `;
        });
}

// Render workers list
function renderWorkersList(workers) {
    const workersList = document.getElementById('workers-list');

    workers.forEach(worker => {
        const avgRating = worker.avg_rating || 0;
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= avgRating ? '<span class="star">&#9733;</span>' : '<span class="star">&#9734;</span>';
        }

        const li = document.createElement('li');
        li.className = 'card-worker';
        li.innerHTML = `
            <div class="card-content">
                <h3>${worker.fname} ${worker.lname}</h3>
                <div class="ratings">
                    <div class="stars" style="color: gold;">
                        ${starsHtml}
                    </div>
                </div>   
            <button class="btn btn2 take-appointment-btn"
            data-worker-name="${worker.fname} ${worker.lname}"
            data-worker-id="${worker.user_id}">
            ${translations.appointment_btn}
        </button>
                <button class="btn btn4 add-review-btn"
            data-worker-name="${worker.fname} ${worker.lname}"
            data-worker-id="${worker.user_id}"
            data-worker-service="${worker.skills}">
            ${translations.review_btn}
        </button>

            </div>
            <div class="card-image">
                <img src="php/images/${worker.img}" class="worker">
            </div>
        `;

        workersList.appendChild(li);
    });

    initAppointmentButtons();
    initReviewButtons();
    enableSearch();
}

// Main initialization
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const serviceId = urlParams.get('service_id');
    const clientId = window.CURRENT_USER_ID || null;    

    // Fetch initial workers list
    fetch(`../api/fetch_ratings.php?service_id=${encodeURIComponent(serviceId)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderWorkersList(data.workers);
            } else {
                document.getElementById('workers-list').innerHTML = `
                    <li class="no-workers-message">
                        <p>${data.message || 'Error loading workers'}</p>
                    </li>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching workers:', error);
            document.getElementById('workers-list').innerHTML = `
                <li class="no-workers-message">
                    <p>Error loading workers</p>
                </li>
            `;
        });

        // Near You button
        document.getElementById('near-you-btn')?.addEventListener('click', () => {
            const clientId = window.userData?.userId;
            
            if (!clientId || clientId === 'null') {
                alert('Please log in to use this feature');
                return;
            }

            fetchWorkersNearYou(clientId, serviceId);
        });

    // Filter by region
    document.getElementById('filter-by-region-btn')?.addEventListener('click', () => {
        const region = document.getElementById('region-select').value;
        if (region) {
            fetchWorkersByRegion(region, serviceId);
        } else {
            alert('Please select a region first');
        }
    });
});