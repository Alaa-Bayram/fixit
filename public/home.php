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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixit</title>
    <!-- Include header -->
    <?php include_once "header.html"; ?>
    <!-- Include CSS and other scripts -->
</head>
<body>
<button class="chatbot-toggler">
    <a href="users.php"><i class="bi bi-chat-left" style="color: white;font-size: 24px;"></i></a>
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
            <h1>MAINTENANCE MASTERY EVERYWHERE</h1>
            <p>Your all-in-one solution for any repair or maintenance task, wherever you are.<br>From homes to offices, we've got the fix for you.</p>
        </div>
        <a href="services.php" class="second">Our Services</a><br>
        <a href="emergency.php" class="first">Emergency Help</a>
    </div>
</section>
<section class="services" id="services">
    <h2>Our Services</h2>
    <p>Explore our wide range of maintenance services.</p>
    <a href="services.php"> <button class="btn btn4"> View All</button></a>
    <ul class="cards" id="services-list">
        <!-- Services will be dynamically added here -->
    </ul>
</section>

<div class="container1">
    <div class="left-photo"><img src="app_images/em1.jpg"></div>
    <div class="right-content">
        <p>call us for</p>
        <h1>EMERGENCY NEED HELP!</h1>
        <p>Rapid response when troubles arise, restoring flow and averting cries.</p>
       <button type="submit" class="emBtn"><a href="emergency.php">Contact Us !!</a></button>
    </div>
    </div>

    <div class="container2">
    <div class="left">
      <p>WE CAN HELP YOU</p>
      <h1>WHY CHOOSE FixIt ?</h1>
    </div>
    <div class="right">
        <p>At FixIt, we're not just about fixing problems; we're about restoring your peace of mind. Choose us for our unwavering commitment to excellence, rapid response, and trusted expertise. Because when you need help, you deserve nothing but the best.</p>
    </div>
    </div>

    <div class="container">
    <h2> Our Happy Clients </h2>
    <button id="openReviewFormBtn" class="butn butn2">Write a Review</button>
    
    <!-- Review Form Popup (initially hidden) -->
    <div id="reviewFormPopup" class="reviewFormPopup" style="display: none;">
        <span class="close-btn-review material-symbols-rounded">close</span>
        <form id="reviewForm" onsubmit="submitReview(event)">
            <h3 class="title">Write Your Review</h3><br>
            <div class="form-group">
                <label for="reviewStars">Your Rating:</label>
                <select id="reviewStars" name="reviewStars">
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5">5 Stars</option>
                </select>
            </div><br>
            <div class="form-group">
                <label for="reviewText">Your Review:</label>
                <textarea id="reviewText" name="reviewText" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit" value="Submit Review">Submit Review</button>
            </div>
        </form>
    </div>

<!-- Review Form Popup (initially hidden) -->
<!-- Review Form Popup (initially hidden) -->
<div id="reviewFormPopup" class="reviewFormPopup" style="display: none;">
    <span class="close-btn-review material-symbols-rounded">close</span>
    <form id="reviewForm" onsubmit="submitReview(event)">
        <h3 class="title">Write Your Review</h3><br>
        <div class="form-group">
            <label for="reviewStars">Your Rating:</label>
            <select id="reviewStars" name="reviewStars">
                <option value="1">1 Star</option>
                <option value="2">2 Stars</option>
                <option value="3">3 Stars</option>
                <option value="4">4 Stars</option>
                <option value="5">5 Stars</option>
            </select>
        </div><br>
        <div class="form-group">
            <label for="reviewText">Your Review:</label>
            <textarea id="reviewText" name="reviewText" rows="4" required></textarea>
        </div>
        <input type="hidden" id="reviewType" name="reviewType" value="app"> <!-- Hidden review type field -->
        <div class="form-group">
            <button type="submit" value="Submit Review">Submit Review</button>
        </div>
    </form>
</div>

        <script src="javascript/appReview.js"></script>


<!-- JavaScript to fetch data from API and populate services -->
<script>
    fetch('../api/home.php')
    .then(response => response.json())
    .then(data => {
        const servicesList = document.getElementById('services-list');
        data.forEach(service => {
            const li = document.createElement('li');
            li.className = 'card';
            li.innerHTML = `
                <img src="images/${service.images}" alt="${service.title}">
                <h3>${service.title}</h3>
                <p>${service.description}</p>
                <a href="list_workers.php?service_id=${encodeURIComponent(service.service_id)}"><button class="btn btn2">Book Now</button></a>
            `;
            servicesList.appendChild(li);
        });
    })
    .catch(error => console.error('Error fetching services:', error));

</script>
</body>
</html>
