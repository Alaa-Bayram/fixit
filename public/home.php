<?php
session_start(); // Start session

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html"); // Redirect to login page if not authenticated
    exit();
}

// Retrieve user name and any other session data
$user_name = isset($_SESSION['fname']) ? $_SESSION['fname'] : 'Guest';
$profile_image = isset($_SESSION['img']) ? $_SESSION['img'] : 'images/default-profile.jpg'; // default.png is a fallback image

// Language selection (default to English)
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$lang_file = "lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("lang/en.php"); // fallback
}

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixit</title>
    <!-- Include header -->
    <?php include_once "header.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
</head>
<body>


<button class="chatbot-toggler">
    <a href="chatting/users.php?<?= http_build_query(array_merge($_GET, ['lang' => $lang])) ?>">
        <i class="bi bi-chat-left" style="color: white; font-size: 24px;"></i>
    </a>
</button>
<br><button class="emergency-toggler">
    <a href="emergency.php"><i class="bi bi-exclamation-triangle-fill" style="color: white;font-size: 24px;"></i></a>
</button>

<section class="homepage" id="home">
    <video autoplay loop muted playsinline>
        <source src="app_images/bg.mp4" type="video/mp4">
    </video>
    <div class="content">
        <div class="text">
            <h1><?= $translations['home_title'] ?></h1>
            <p><?= $translations['home_desc'] ?></p>
        </div>
        <a href="services.php" class="second"><?= $translations['our_services'] ?></a><br>
        <a href="emergency.php" class="first"><?= $translations['emergency_help'] ?></a>
    </div>
</section>
<section class="services" id="services">
    <h2><?= $translations['our_services'] ?></h2>
    <p><?= $translations['explore_services'] ?></p>
    <a href="services.php"> <button class="btn btn4"><?= $translations['view_all'] ?? 'View All' ?></button></a>
    <script>
    const bookNowText = <?= json_encode($translations['book_now'] ?? 'Book Now') ?>;
    </script>

    <ul class="cards" id="services-list">
        <!-- Services will be dynamically added here -->
    </ul>
</section>

<div class="container1">
    <div class="left-photo"><img src="app_images/em1.jpg"></div>
    <div class="right-content">
        <p><?= $translations['emergency_call'] ?? 'call us for' ?></p>
        <h1><?= $translations['emergency_title'] ?? 'EMERGENCY NEED HELP!' ?></h1>
        <p><?= $translations['emergency_desc'] ?? 'Rapid response when troubles arise, restoring flow and averting cries.' ?></p>
        <button type="submit" class="emBtn">
            <a href="emergency.php"><?= $translations['contact_us'] ?? 'Contact Us !!' ?></a>
        </button>
    </div>
</div>

    <div class="container2">
    <div class="left">
      <p><?= $translations['help'] ?></p>
      <h1><?= $translations['why_choose'] ?></h1>
    </div>
    <div class="right">
        <p><?= $translations['why_desc'] ?></p>
    </div>
    </div>

<div class="container">
    <h2><?= $translations['happy_clients'] ?></h2>
    <button id="openReviewFormBtn" class="butn butn2">
        <i class="bi bi-pencil-square"></i> <?= $translations['write_review'] ?>
    </button>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="review-modal">
    <div class="review-modal-content">
        <span class="close-review-modal">&times;</span>
        <h2><?= $translations['write_review'] ?? 'Share Your Feedback' ?></h2>
        
        <div class="rating-section">
            <h3><?= $translations['ease_of_use'] ?? 'Ease of Use' ?></h3>
            <div class="rating-container">
                <div class="rating-item">
                    <div class="rating-stars" data-rating-type="ease">
                        <i class="bi bi-star rating-star" data-value="1"></i>
                        <i class="bi bi-star rating-star" data-value="2"></i>
                        <i class="bi bi-star rating-star" data-value="3"></i>
                        <i class="bi bi-star rating-star" data-value="4"></i>
                        <i class="bi bi-star rating-star" data-value="5"></i>
                    </div>
                    <div class="rating-label">1 = Very Difficult, 5 = Very Easy</div>
                </div>
            </div>
        </div>
        
        <div class="rating-section">
            <h3><?= $translations['service_quality'] ?? 'Service Quality' ?></h3>
            <div class="rating-container">
                <div class="rating-item">
                    <div class="rating-stars" data-rating-type="quality">
                        <i class="bi bi-star rating-star" data-value="1"></i>
                        <i class="bi bi-star rating-star" data-value="2"></i>
                        <i class="bi bi-star rating-star" data-value="3"></i>
                        <i class="bi bi-star rating-star" data-value="4"></i>
                        <i class="bi bi-star rating-star" data-value="5"></i>
                    </div>
                    <div class="rating-label">1 = Poor, 5 = Excellent</div>
                </div>
            </div>
        </div>
        
        <div class="rating-section">
            <h3><?= $translations['customer_support'] ?? 'Customer Support' ?></h3>
            <div class="rating-container">
                <div class="rating-item">
                    <div class="rating-stars" data-rating-type="support">
                        <i class="bi bi-star rating-star" data-value="1"></i>
                        <i class="bi bi-star rating-star" data-value="2"></i>
                        <i class="bi bi-star rating-star" data-value="3"></i>
                        <i class="bi bi-star rating-star" data-value="4"></i>
                        <i class="bi bi-star rating-star" data-value="5"></i>
                    </div>
                    <div class="rating-label">1 = Unsatisfied, 5 = Very Satisfied</div>
                </div>
            </div>
        </div>
        
        <div class="rating-section">
            <h3><?= $translations['would_recommend'] ?? 'Would you recommend us?' ?></h3>
            <div class="recommend-container">
                <button class="recommend-btn yes" type="button"><?= $translations['yes'] ?? 'Yes' ?></button>
                <button class="recommend-btn no" type="button"><?= $translations['no'] ?? 'No' ?></button>
            </div>
        </div>
        
        <div class="rating-section">
            <h3><?= $translations['detailed_review'] ?? 'Your Detailed Review' ?></h3>
            <textarea id="reviewText" class="review-textarea" 
                    placeholder="<?= $translations['review_placeholder'] ?? 'Tell us more about your experience...' ?>"></textarea>
        </div>
        
        <button id="submitReviewBtn" class="submit-review-btn">
            <?= $translations['submit_review'] ?? 'Submit Review' ?>
        </button>
    </div>
</div>

<!-- Include the new CSS and JS files -->
<link rel="stylesheet" href="css/review-modal.css">
<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="testimonials-container">
        <h2 class="testimonials-title"><?= $translations['client_testimonials'] ?? 'What Our Clients Say' ?></h2>
        <p class="testimonials-subtitle"><?= $translations['testimonials_subtitle'] ?? 'Hear from people who have used our services<br>Click on the review to see the details' ?></p>
        
        <div class="testimonials-scroll" id="testimonialsScroll" >
            <!-- Testimonials will be loaded here dynamically -->
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <img src="app_images/loading-avatar.webp" alt="Loading" class="testimonial-avatar">
                    <div class="testimonial-user">
                        <h4>Loading...</h4>
                        <p>Loading reviews</p>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
                <p class="testimonial-text">Reviews are loading, please wait...</p>
            </div>
        </div>
        
        <div class="testimonials-nav" >
            <button id="testimonialPrev" style="background-color: #79bcb1;"><i class="bi bi-chevron-left"></i></button>
            <button id="testimonialNext" style="background-color: #79bcb1;"><i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</section>

<!-- Include testimonials JS -->
<script src="javascript/testimonials.js"></script>
    <?php include_once "footer.html"; ?>

<script src="javascript/review-modal.js"></script>
<!-- JavaScript to fetch data from API and populate services -->
<script>
    // Get current language from URL or default to 'en'
    const urlParams = new URLSearchParams(window.location.search);
    const currentLang = urlParams.get('lang') || 'en';
    
    fetch(`../api/home.php?lang=${currentLang}`)
    .then(response => response.json())
    .then(data => {
        const servicesList = document.getElementById('services-list');
        servicesList.innerHTML = ''; // Clear previous content
        data.forEach(service => {
            const li = document.createElement('li');
            li.className = 'card';
            li.innerHTML = `
                <img src="images/${service.images}" alt="${service.title}">
                <h3>${service.title}</h3>
                <p>${service.description}</p>
                <a href="list_workers.php?service_id=${encodeURIComponent(service.service_id)}">
                    <button class="btn btn2">${bookNowText}</button>
                </a>
            `;
            servicesList.appendChild(li);
        });
    })
    .catch(error => console.error('Error fetching services:', error));
</script>

</body>
</html>
