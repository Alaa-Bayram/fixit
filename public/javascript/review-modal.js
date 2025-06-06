document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const openReviewBtn = document.getElementById('openReviewFormBtn');
    const reviewModal = document.getElementById('reviewModal');
    const closeModalBtn = document.querySelector('.close-review-modal');
    const submitReviewBtn = document.getElementById('submitReviewBtn');
    
    // Rating data
    const ratings = {
        ease: 0,
        quality: 0,
        support: 0
    };
    let wouldRecommend = null;

    // Open modal when button is clicked
    openReviewBtn.addEventListener('click', function() {
        reviewModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });

    // Close modal
    closeModalBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === reviewModal) {
            closeModal();
        }
    });

    // Star rating functionality
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('click', function() {
            const ratingType = this.closest('.rating-stars').dataset.ratingType;
            const value = parseInt(this.dataset.value);
            
            // Set rating for this category
            ratings[ratingType] = value;
            
            // Update star display
            const stars = this.closest('.rating-stars').querySelectorAll('.rating-star');
            stars.forEach((s, i) => {
                if (i < value) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        // Hover effects
        star.addEventListener('mouseover', function() {
            const ratingType = this.closest('.rating-stars').dataset.ratingType;
            const value = parseInt(this.dataset.value);
            const stars = this.closest('.rating-stars').querySelectorAll('.rating-star');
            
            stars.forEach((s, i) => {
                if (i < value) {
                    s.style.color = '#ffc107';
                }
            });
        });
        
        star.addEventListener('mouseout', function() {
            const ratingType = this.closest('.rating-stars').dataset.ratingType;
            const currentRating = ratings[ratingType];
            const stars = this.closest('.rating-stars').querySelectorAll('.rating-star');
            
            stars.forEach((s, i) => {
                if (currentRating === 0 || i >= currentRating) {
                    s.style.color = '#ccc';
                }
            });
        });
    });

    // Recommendation buttons
    document.querySelectorAll('.recommend-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.recommend-btn').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            wouldRecommend = this.classList.contains('yes');
        });
    });

    // Submit review
    submitReviewBtn.addEventListener('click', function() {
        // Validate ratings
        if (ratings.ease === 0 || ratings.quality === 0 || ratings.support === 0) {
            alert('Please complete all rating sections');
            return;
        }
        
        if (wouldRecommend === null) {
            alert('Please let us know if you would recommend us');
            return;
        }
        
        const reviewText = document.getElementById('reviewText').value.trim();
        
        const formData = new FormData();
        formData.append('reviewType', 'app');
        formData.append('ease_rating', ratings.ease);
        formData.append('quality_rating', ratings.quality);
        formData.append('support_rating', ratings.support);
        formData.append('would_recommend', wouldRecommend ? '1' : '0');
        formData.append('comment', reviewText);
        
        submitReviewBtn.disabled = true;
        submitReviewBtn.textContent = 'Submitting...';
        
        fetch('php/submitAppReview.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for your feedback!');
                closeModal();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting your review');
        })
        .finally(() => {
            submitReviewBtn.disabled = false;
            submitReviewBtn.textContent = 'Submit Review';
        });
    });
    
    function closeModal() {
        reviewModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForm();
    }
    
    function resetForm() {
        // Reset ratings
        ratings.ease = 0;
        ratings.quality = 0;
        ratings.support = 0;
        wouldRecommend = null;
        
        // Reset UI
        document.querySelectorAll('.rating-star').forEach(star => {
            star.classList.remove('active');
            star.style.color = '#ccc';
        });
        
        document.querySelectorAll('.recommend-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.getElementById('reviewText').value = '';
    }
});