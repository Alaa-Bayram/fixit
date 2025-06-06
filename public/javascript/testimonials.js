document.addEventListener('DOMContentLoaded', function() {
    const testimonialsScroll = document.getElementById('testimonialsScroll');
    const prevBtn = document.getElementById('testimonialPrev');
    const nextBtn = document.getElementById('testimonialNext');
    
    // Fetch testimonials from API
    fetch('../api/getTestimonials.php')
        .then(response => response.json())
        .then(testimonials => {
            if (testimonials.length === 0) {
                testimonialsScroll.innerHTML = `
                    <div class="testimonial-card">
                        <p class="testimonial-text">No reviews yet. Be the first to review!</p>
                    </div>
                `;
                return;
            }
            
            testimonialsScroll.innerHTML = '';
            
            testimonials.forEach(testimonial => {
                const card = document.createElement('div');
                card.className = 'testimonial-card';
                card.style.cursor = 'pointer';
                
                // Calculate average rating
                const ratings = [
                    testimonial.ease_rating,
                    testimonial.quality_rating,
                    testimonial.support_rating
                ].filter(r => r !== null);
                
                const avgRating = ratings.length > 0 ? 
                    (ratings.reduce((a, b) => a + b, 0) / ratings.length).toFixed(1) : 
                    'No rating';
                
                card.innerHTML = `
                    <div class="testimonial-header">
                        <img src="images/${testimonial.profile_image || 'images/default-profile.jpg'}" 
                             alt="${testimonial.user_name}" class="testimonial-avatar">
                        <div class="testimonial-user">
                            <h4>${testimonial.user_name || 'Anonymous'}</h4>
                            <p>${testimonial.review_date}</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        ${getStarRating(avgRating)}
                        <span style="margin-left: 10px; color: #333;">${avgRating}</span>
                    </div>
                    <p class="testimonial-text">"${testimonial.comment || 'No comment provided'}"</p>
                `;
                 card.addEventListener('click', () => {
                 showDetailedReview(testimonial);
             });
    
                testimonialsScroll.appendChild(card);
            });
            
            // Enable navigation buttons
            setupNavigation();
        })
        .catch(error => {
            console.error('Error loading testimonials:', error);
            testimonialsScroll.innerHTML = `
                <div class="testimonial-card">
                    <p class="testimonial-text">Error loading reviews. Please try again later.</p>
                </div>
            `;
        });
    
    function getStarRating(avgRating) {
        if (avgRating === 'No rating') return 'No rating';
        
        const fullStars = Math.floor(avgRating);
        const hasHalfStar = avgRating % 1 >= 0.5;
        let stars = '';
        
        for (let i = 0; i < fullStars; i++) {
            stars += '<i class="bi bi-star-fill"></i>';
        }
        
        if (hasHalfStar) {
            stars += '<i class="bi bi-star-half"></i>';
        }
        
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i class="bi bi-star"></i>';
        }
        
        return stars;
    }
    
    function setupNavigation() {
        const scrollAmount = 400;
        
        prevBtn.addEventListener('click', () => {
            testimonialsScroll.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });
        
        nextBtn.addEventListener('click', () => {
            testimonialsScroll.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
        
        // Hide/show buttons based on scroll position
        testimonialsScroll.addEventListener('scroll', () => {
            prevBtn.style.visibility = testimonialsScroll.scrollLeft > 0 ? 'visible' : 'hidden';
            nextBtn.style.visibility = 
                testimonialsScroll.scrollLeft < (testimonialsScroll.scrollWidth - testimonialsScroll.clientWidth) ? 
                'visible' : 'hidden';
        });
        
        // Initial state
        prevBtn.style.visibility = 'hidden';
    }
    // Add this to your testimonials.js after the existing code

// Detailed Review Modal
const detailedReviewModal = document.createElement('div');
detailedReviewModal.className = 'detailed-review-modal';
document.body.appendChild(detailedReviewModal);

// Function to show detailed review
function showDetailedReview(testimonial) {
    // Calculate average rating
    const ratings = [
        testimonial.ease_rating,
        testimonial.quality_rating,
        testimonial.support_rating
    ].filter(r => r !== null);
    
    const avgRating = ratings.length > 0 ? 
        (ratings.reduce((a, b) => a + b, 0) / ratings.length).toFixed(1) : 
        null;
    
    // Prepare recommendation text
    let recommendationText = '';
    if (testimonial.would_recommend !== null) {
        recommendationText = testimonial.would_recommend == 1 ?
            '<span class="detailed-recommendation"><i class="bi bi-check-circle-fill"></i> Would recommend</span>' :
            '<span class="detailed-recommendation no"><i class="bi bi-x-circle-fill"></i> Would not recommend</span>';
    }
    
    detailedReviewModal.innerHTML = `
        <div class="detailed-review-content">
            <span class="close-detailed-review">&times;</span>
            <div class="detailed-review-header">
                <img src="images/${testimonial.profile_image || 'images/default-profile.jpg'}" 
                     alt="${testimonial.user_name}" class="detailed-review-avatar">
                <div class="detailed-review-user">
                    <h3>${testimonial.user_name || 'Anonymous'}</h3>
                    <p class="detailed-review-date">${new Date(testimonial.review_date).toLocaleDateString()}</p>
                    ${avgRating ? `
                    <div class="detailed-rating-stars">
                        ${getStarRating(avgRating)}
                        <span style="margin-left: 10px;">${avgRating}/5</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="detailed-review-body">
                ${testimonial.ease_rating ? `
                <div class="detailed-rating-item">
                    <div class="detailed-rating-label">
                        <span>Ease of Use</span>
                        <span>${testimonial.ease_rating}/5</span>
                    </div>
                    <div class="detailed-rating-stars">
                        ${getStarRating(testimonial.ease_rating)}
                    </div>
                </div>
                ` : ''}
                
                ${testimonial.quality_rating ? `
                <div class="detailed-rating-item">
                    <div class="detailed-rating-label">
                        <span>Service Quality</span>
                        <span>${testimonial.quality_rating}/5</span>
                    </div>
                    <div class="detailed-rating-stars">
                        ${getStarRating(testimonial.quality_rating)}
                    </div>
                </div>
                ` : ''}
                
                ${testimonial.support_rating ? `
                <div class="detailed-rating-item">
                    <div class="detailed-rating-label">
                        <span>Customer Support</span>
                        <span>${testimonial.support_rating}/5</span>
                    </div>
                    <div class="detailed-rating-stars">
                        ${getStarRating(testimonial.support_rating)}
                    </div>
                </div>
                ` : ''}
            </div>
            
            ${testimonial.comment ? `
            <div class="detailed-review-comment">
                <h4>Detailed Review</h4>
                <p>"${testimonial.comment}"</p>
            </div>
            ` : ''}
            
            ${recommendationText}
        </div>
    `;
    
    detailedReviewModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Close modal handlers
    const closeBtn = detailedReviewModal.querySelector('.close-detailed-review');
    closeBtn.addEventListener('click', () => {
        detailedReviewModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === detailedReviewModal) {
            detailedReviewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
}
});