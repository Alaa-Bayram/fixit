document.addEventListener("DOMContentLoaded", function () {
    const showReviewPopupButtons = document.querySelectorAll(".add-review-btn");
    const formPopupReview = document.querySelector(".form-popup-review");
    const blurOverlayReview = document.querySelector(".blur-bg-overlay-review");
    const closePopupBtnReview = formPopupReview.querySelector(".close-btn-review");
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
                        reviewForm.reset(); // Reset form after successful submission
                    } else {
                        alert('Failed to add review: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error adding review:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        } else {
            console.error("Review form not found");
        }
    } else {
        console.error("One or more required review elements not found");
    }
});
