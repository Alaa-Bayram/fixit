<?php
session_start();
include_once "php/db.php";

// Language selection (default to English)
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;
$lang_file = "lang/$lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    $translations = include("lang/en.php");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?lang=$lang");
    exit();
}

// Get service_id from URL
$service_id = $_GET['service_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['workers_page_title'] ?? 'Workers' ?></title>
    <link rel="stylesheet" href="css/search.css">
</head>
<body>
    <?php include_once "header.php"; ?>

    <button class="chatbot-toggler">
        <a href="users.php?lang=<?= $lang ?>"><i class="bi bi-chat-left" style="color: white; font-size: 24px;"></i></a>
    </button>
        <br><button class="emergency-toggler">
        <a href="emergency.php"><i class="bi bi-exclamation-triangle-fill" style="color: white;font-size: 24px;"></i></a>
    </button>

    <section class="portfolio" id="portfolio">
        <h2><?= $translations['workers_heading'] ?? 'Workers' ?></h2>
        <p><?= $translations['workers_description'] ?? 'Browse through our skilled professionals and choose the perfect expert for your maintenance needs.' ?></p>
        
        <div class="wrapper">
            <div class="search-box">
                <i class="bx bx-search"></i>
                <input type="text" placeholder="<?= $translations['search_worker_placeholder'] ?? 'Search for a worker' ?>" />
                <div class="icon"><i class="fas fa-search"></i></div>
            </div>
            <button id="near-you-btn" class="btn btn-primary btn1"><?= $translations['near_you'] ?? 'Near You' ?></button>
            <div class="filter">
                <select id="region-select" name="region" required>
                    <option value="" disabled selected><?= $translations['select_region'] ?? 'Region' ?></option>
                    <option value="Beirut"><?= $translations['region_beirut'] ?? 'Beirut' ?></option>
                    <option value="Baalbek-Hermel"><?= $translations['region_baalbek'] ?? 'Baalbek-Hermel' ?></option>
                    <option value="Bekaa"><?= $translations['region_bekaa'] ?? 'Bekaa' ?></option>
                    <option value="South Lebanon"><?= $translations['region_south'] ?? 'South Lebanon' ?></option>
                    <option value="Nabatieh"><?= $translations['region_nabatieh'] ?? 'Nabatieh' ?></option>
                    <option value="Mount Lebanon"><?= $translations['region_mount'] ?? 'Mount Lebanon' ?></option>
                    <option value="North Lebanon"><?= $translations['region_north'] ?? 'North Lebanon' ?></option>
                    <option value="Akkar"><?= $translations['region_akkar'] ?? 'Akkar' ?></option>
                </select>
                <button id="filter-by-region-btn" class="btnFilter btn-primary btn1"><?= $translations['filter_region'] ?? 'Filter by Region' ?></button>
            </div>
        </div>

        <ul class="cards-worker" id="workers-list">
            <!-- Workers will be dynamically populated here -->
        </ul>
    </section>
    
    <!-- Appointment form -->
    <div class="blur-bg-overlay"></div>
    <div class="form-popup">
        <span class="close-btn material-symbols-rounded">close</span>
        <div class="form-box appointment">
            <div class="form-content">
                <h2><?= $translations['schedule_appointment'] ?? 'Schedule Appointment' ?></h2>
                <form id="appointment-form" method="POST">
                    <?= $translations['name_label'] ?? 'NAME' ?>: <h3 id="worker-name"></h3>
                    <input type="hidden" name="client_id" value="<?= $_SESSION['user_id'] ?>" required>
                    <input type="hidden" name="worker_id" id="worker-id">

                    <div class="input-field">
                        <label><?= $translations['date_label'] ?? 'Date' ?></label>
                        <input type="date" id="appointment-date" name="date" required>
                    </div>
                    <div class="input-field">
                        <label><?= $translations['time_label'] ?? 'Time' ?></label>
                        <input type="time" id="appointment-time" name="time" required min="07:00" max="20:00" step="900">
                    </div>
                    <div class="input-field">
                        <label><?= $translations['unavailable_times'] ?? 'Unavailable Times' ?></label>
                        <br>
                        <ul id="unavailable-times-list" class="list"></ul>
                        <p style="margin-top: 10px; font-style: italic; color: #888;">
                            <?= $translations['appointment_note'] ?? 'Please note: Appointments cannot be scheduled less than 2 hours apart from each other.' ?>
                        </p>
                    </div>

                    <button type="submit"><?= $translations['submit_button'] ?? 'Submit' ?></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Review Popup -->
    <div class="blur-bg-overlay-review"></div>
    <div class="form-popup-review">
        <span class="close-btn-review material-symbols-rounded">close</span>
        <div class="form-boxx review">
            <div class="form-contentt">
                <form id="review-form" method="POST">
                    <h2><?= $translations['add_review_for'] ?? 'Add Review for' ?> <span id="review-worker-name"></span></h2>
                    <p style="color:#79bcb1; font-weight: bold;"><?= $translations['service_label'] ?? 'Service' ?>: <span id="review-worker-service" name="worker_service"></span></p>
                    <input type="hidden" id="review-worker-id" name="worker_id">
                    <input type="hidden" name="review_date" value="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="reviewType" value="worker">

                    <div class="rating-criteria">
                        <div class="criteria-item">
                            <label><?= $translations['speed_rating'] ?? 'Speed' ?>:</label>
                            <div class="star-rating">
                                <!-- Star rating inputs remain the same -->
                            </div>
                        </div>
                        <!-- Other rating criteria -->
                    </div>

                    <div class="input-field">
                        <label for="comment"><?= $translations['detailed_feedback'] ?? 'Detailed Feedback' ?>:</label>
                        <textarea id="comment" name="comment" rows="4" required></textarea>
                    </div>
                    <button type="submit"><?= $translations['submit_review'] ?? 'Submit Review' ?></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pass data to JavaScript
        window.userData = {
            userId: <?= $_SESSION['user_id'] ?? 'null' ?>,
            currentLang: '<?= $lang ?>'
        };
    </script>
    
    <script src="javascript/workers.js"></script>
    <script src="javascript/review.js"></script>
    <script src="javascript/appointment.js"></script>
</body>
</html>