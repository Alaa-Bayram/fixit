document.addEventListener("DOMContentLoaded", function () {
    const openReviewFormBtn = document.getElementById('openReviewFormBtn');
    const reviewFormPopup = document.getElementById('reviewFormPopup');
    const closeReviewFormBtn = document.querySelector('.close-btn-review');
    const blurOverlayReview = document.createElement('div');
    blurOverlayReview.className = 'blur-bg-overlay-review';
    blurOverlayReview.style.display = 'none'; // Initially hidden
    document.body.appendChild(blurOverlayReview);

    openReviewFormBtn.addEventListener('click', function () {
        reviewFormPopup.style.display = 'block';
        blurOverlayReview.style.display = 'block';
    });

    blurOverlayReview.addEventListener('click', function () {
        reviewFormPopup.style.display = 'none';
        blurOverlayReview.style.display = 'none';
    });

    closeReviewFormBtn.addEventListener('click', function () {
        reviewFormPopup.style.display = 'none';
        blurOverlayReview.style.display = 'none';
    });

    // Function to submit the review form
    document.getElementById('reviewForm').addEventListener('submit', function (event) {
        event.preventDefault();
        
        const reviewStars = document.getElementById('reviewStars').value;
        const reviewText = document.getElementById('reviewText').value;
        const reviewType = 'app'; // Set reviewType to 'app'
        const workerId = document.getElementById('workerId') ? document.getElementById('workerId').value : ''; // Add workerId element to the form if it's present

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "php/submitReview.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                alert(response.message);

                if (response.success) {
                    reviewFormPopup.style.display = 'none';
                    blurOverlayReview.style.display = 'none';
                }
            }
        };
        xhr.send(`reviewStars=${reviewStars}&reviewText=${encodeURIComponent(reviewText)}&reviewType=${reviewType}&workerId=${workerId}`);
    });
});
