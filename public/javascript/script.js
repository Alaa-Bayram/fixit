document.addEventListener("DOMContentLoaded", function () {
    // ==================== Review Popup Functionality ====================
    const showReviewPopupButtons = document.querySelectorAll(".add-review-btn");
    const formPopupReview = document.querySelector(".form-popup-review");
    const blurOverlayReview = document.querySelector(".blur-bg-overlay-review");
    const closePopupBtnReview = formPopupReview?.querySelector(".close-btn-review");
    const reviewWorkerNameField = document.getElementById("review-worker-name");
    const reviewWorkerIdField = document.getElementById("review-worker-id");
    const reviewWorkerServiceField = document.getElementById("review-worker-service");

    if (showReviewPopupButtons && formPopupReview && blurOverlayReview && closePopupBtnReview && reviewWorkerNameField && reviewWorkerIdField && reviewWorkerServiceField) {
        showReviewPopupButtons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const workerName = button.getAttribute("data-worker-name");
                const workerId = button.getAttribute("data-worker-id");
                const workerService = button.getAttribute("data-worker-service");

                if (workerName && workerId && workerService) {
                    reviewWorkerNameField.value = workerName;
                    reviewWorkerIdField.value = workerId;
                    reviewWorkerServiceField.value = workerService;
                    document.body.classList.add("show-popup-review");
                } else {
                    console.error("Worker name, ID, or service not found in button attributes");
                }
            });
        });

        closePopupBtnReview.addEventListener("click", () => {
            document.body.classList.remove("show-popup-review");
        });

        blurOverlayReview.addEventListener("click", () => {
            document.body.classList.remove("show-popup-review");
        });

        const reviewForm = document.getElementById("review-form");
        if (reviewForm) {
            reviewForm.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(reviewForm);

                fetch('../api/submit_review.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Review added successfully!');
                        document.body.classList.remove("show-popup-review");
                        reviewForm.reset();
                    } else {
                        alert('Failed to add review: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error adding review:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        }
    }

    // ==================== Services Fetching and Search Functionality ====================
    // Get current language from URL or default to 'en'
    const urlParams = new URLSearchParams(window.location.search);
    const currentLang = urlParams.get('lang') || 'en';
    
    // Pass language to API
    fetch(`../api/services.php?lang=${currentLang}`)
    .then(response => response.json())
    .then(data => {
        console.log('Fetched services:', data);

        if (!Array.isArray(data)) {
            console.error('Expected an array but got:', data);
            return;
        }

        const servicesContainer = document.createElement('ul');
        servicesContainer.className = 'cards';

        data.forEach(service => {
            const li = document.createElement('li');
            li.className = 'card';
            li.setAttribute('data-name', service.title);
            li.innerHTML = `
                <img src="images/${service.images}" alt="${service.title}">
                <h3>${service.title}</h3>
                <p>${service.description}</p>
                <a href="list_workers.php?service_id=${encodeURIComponent(service.service_id)}&lang=${currentLang}">
                    <button class="btn btn2">${document.documentElement.lang === 'fr' ? 'Réserver' : 
                                           document.documentElement.lang === 'ar' ? 'احجز الان' : 
                                           'Book Now'}</button>
                </a>
            `;
            servicesContainer.appendChild(li);
        });

        const servicesList = document.getElementById('services-list');
        if (servicesList) {
            servicesList.appendChild(servicesContainer);
            setupSearchFunctionality(); // Initialize search after cards are loaded
        }
    })
    .catch(error => console.error('Error fetching services:', error));

    function setupSearchFunctionality() {
        const search = document.querySelector(".search-box input");
        const cards = document.querySelectorAll(".card");

        if (search && cards.length > 0) {
            search.addEventListener("keyup", () => {
                const searchValue = search.value.trim().toLowerCase();

                cards.forEach(card => {
                    const cardName = card.getAttribute("data-name").toLowerCase();
                    if (cardName.includes(searchValue)) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });
            });

            search.addEventListener("keyup", () => {
                if (search.value === "") {
                    cards.forEach(card => {
                        card.style.display = "block";
                    });
                }
            });
        }
    }
});