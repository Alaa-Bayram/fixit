<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_login_to_access_dashboard']);
}

// Include reviews data
require_once 'includes/reviews_data.php';

// Check for success or error messages
$popup_message = '';
if (isset($_SESSION['error_message'])) {
    $popup_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $popup_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['worker_reviews']; ?></div>
                <div class="number"><?php echo number_format($total_reviews); ?></div>
                <div class="indicator">
                    <i class="bx bx-star"></i>
                    <span class="text"><?php echo $trans['client_feedback']; ?></span>
                </div>
            </div>
            <i class="bx bx-comment-detail cart three"></i>
        </div>
        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['app_reviews']; ?></div>
                <div class="number"><?php echo number_format($total_app_reviews); ?></div>
                <div class="indicator">
                    <i class="bx bx-mobile-alt"></i>
                    <span class="text"><?php echo $trans['app_feedback']; ?></span>
                </div>
            </div>
            <i class="bx bx-star cart two"></i>
        </div>
        <div class="box" onclick="showAverageRatingChart()" style="cursor: pointer;">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['average_rating']; ?></div>
                <div class="number"><?php echo $avg_worker_rating; ?>/5</div>
                <div class="indicator">
                    <i class="bx bx-star"></i>
                    <span class="text"><?php echo $trans['overall_performance']; ?></span>
                </div>
            </div>
            <i class="bx bx-chart cart four"></i>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="switchTab('worker')" id="worker-tab">
            <i class="bx bx-user-check"></i>
            <?php echo $trans['worker_reviews']; ?>
        </button>
        <button class="tab-btn" onclick="switchTab('app')" id="app-tab">
            <i class="bx bx-mobile-alt"></i>
            <?php echo $trans['app_reviews']; ?>
        </button>
    </div>

    <!-- Average Rating Chart Modal -->
    <div id="averageRatingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $trans['average_ratings_by_worker']; ?></h2>
                <span class="close" onclick="closeAverageModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="averageRatingChart"></canvas>
                </div>
                <div class="chart-info">
                    <p><?php echo $trans['click_bar_details']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Worker Rating Modal -->
    <div id="workerRatingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="workerModalTitle"><?php echo $trans['worker_rating_details']; ?></h2>
                <span class="close" onclick="closeWorkerModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="workerRatingChart"></canvas>
                </div>
                <div class="worker-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php echo $trans['total_reviews']; ?>:</span>
                        <span class="stat-value" id="worker-total-reviews">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php echo $trans['overall_average']; ?>:</span>
                        <span class="stat-value" id="worker-overall-avg">0/5</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Details Chart Modal for Worker Reviews -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $trans['worker_rating_breakdown']; ?></h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="ratingChart"></canvas>
                </div>
                <div class="rating-summary">
                    <div class="summary-card">
                        <h3><?php echo $trans['speed_rating']; ?></h3>
                        <div class="rating-value" id="speed-rating-display">0/5</div>
                    </div>
                    <div class="summary-card">
                        <h3><?php echo $trans['cleanliness']; ?></h3>
                        <div class="rating-value" id="cleanliness-rating-display">0/5</div>
                    </div>
                    <div class="summary-card">
                        <h3><?php echo $trans['professionalism']; ?></h3>
                        <div class="rating-value" id="professionalism-rating-display">0/5</div>
                    </div>
                    <div class="summary-card">
                        <h3><?php echo $trans['communication']; ?></h3>
                        <div class="rating-value" id="communication-rating-display">0/5</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- App Rating Details Chart Modal -->
    <div id="appRatingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $trans['app_rating_details']; ?></h2>
                <span class="close" onclick="closeAppModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="appRatingChart"></canvas>
                </div>
                <div class="rating-summary">
                    <div class="summary-card app-rating">
                        <h3><?php echo $trans['ease_of_use']; ?></h3>
                        <div class="rating-value" id="ease-rating-display">0/5</div>
                    </div>
                    <div class="summary-card app-rating">
                        <h3><?php echo $trans['quality']; ?></h3>
                        <div class="rating-value" id="quality-rating-display">0/5</div>
                    </div>
                    <div class="summary-card app-rating">
                        <h3><?php echo $trans['support']; ?></h3>
                        <div class="rating-value" id="support-rating-display">0/5</div>
                    </div>
                    <div class="summary-card app-rating">
                        <h3><?php echo $trans['would_recommend']; ?></h3>
                        <div class="rating-value" id="recommend-display">N/A</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Worker Reviews Tab Content -->
    <div class="sales-boxes" id="worker-reviews-content">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['worker_reviews']; ?></div>
            <div class="sales-details">
                <div class="details">
                    <ul>
                        <li class="topic"><?php echo $trans['client']; ?></li>
                        <?php if (empty($worker_reviews)): ?>
                            <li class="no-data"><?php echo $trans['no_reviews_available']; ?></li>
                        <?php else: ?>
                            <?php foreach ($worker_reviews as $review): ?>
                                <li>
                                    <a href="#"><?php echo h($review['client_name']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="topic"><?php echo $trans['worker']; ?></li>
                        <?php if (!empty($worker_reviews)): ?>
                            <?php foreach ($worker_reviews as $review): ?>
                                <li>
                                    <a href="#"><?php echo h($review['worker_name'] ?? 'N/A'); ?></a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="topic"><?php echo $trans['rating']; ?></li>
                        <?php if (!empty($worker_reviews)): ?>
                            <?php foreach ($worker_reviews as $review): ?>
                                <li>
                                    <a href="#" class="rating-link" onclick="showIndividualRatingChart(<?php echo htmlspecialchars(json_encode($review)); ?>)">
                                        <?php echo generate_star_rating($review['rating_stars']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="topic"><?php echo $trans['comment']; ?></li>
                        <?php if (!empty($worker_reviews)): ?>
                            <?php foreach ($worker_reviews as $review): ?>
                                <li>
                                    <a href="#">
                                        <?php echo h($review['comment']); ?>
                                        <span class="delete-btn" onclick="confirmDelete(<?php echo $review['review_id']; ?>, 'worker')">
                                            <i class="bx bx-trash"></i> <?php echo $trans['delete']; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- App Reviews Tab Content -->
    <div class="sales-boxes" id="app-reviews-content" style="display: none;">
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['app_reviews']; ?></div>
            <div class="sales-details">
                <div class="details">
                    <ul>
                        <li class="topic"><?php echo $trans['user']; ?></li>
                        <?php if (empty($app_reviews)): ?>
                            <li class="no-data"><?php echo $trans['no_app_reviews_available']; ?></li>
                        <?php else: ?>
                            <?php foreach ($app_reviews as $review): ?>
                                <li>
                                    <a href="#"><?php echo h($review['user_name']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="topic"><?php echo $trans['overall_average']; ?></li>
                        <?php if (!empty($app_reviews)): ?>
                            <?php foreach ($app_reviews as $review): ?>
                                <li>
                                    <a href="#" class="rating-link" onclick="showAppRatingChart(<?php echo htmlspecialchars(json_encode($review)); ?>)">
                                        <?php echo generate_star_rating($review['rating_stars']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="topic"><?php echo $trans['recommended']; ?></li>
                        <?php if (!empty($app_reviews)): ?>
                            <?php foreach ($app_reviews as $review): ?>
                                <li>
                                    <a href="">
                                        <?php
                                        if ($review['would_recommend'] == 1) {
                                            $status_class = 'Recommended';
                                            $status_text = $trans['recommended'];
                                        } else {
                                            $status_class = 'Not_Recommended';
                                            $status_text = $trans['not_recommended'];
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <li class="topic"><?php echo $trans['comment']; ?></li>
                        <?php if (!empty($app_reviews)): ?>
                            <?php foreach ($app_reviews as $review): ?>
                                <li>
                                    <a href="#">
                                        <?php echo h($review['comment']); ?>
                                        <span class="delete-btn" onclick="confirmDelete(<?php echo $review['review_id']; ?>, 'app')">
                                            <i class="bx bx-trash"></i> <?php echo $trans['delete']; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" action="" style="display: none;">
    <input type="hidden" name="review_id" id="delete-review-id">
    <input type="hidden" name="review_type" id="delete-review-type">
    <input type="hidden" name="delete_review" value="1">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
</form>

<div id="popup" class="popup <?php echo !empty($popup_message) ? 'show' : ''; ?>">
    <?php if (!empty($popup_message)): ?>
        <?php echo h($popup_message); ?>
    <?php endif; ?>
</div>

<style>
    /* Tab Navigation Styles */
    .tab-navigation {
        display: flex;
        margin: 20px 0;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .tab-btn {
        flex: 1;
        padding: 12px 20px;
        border: none;
        background: transparent;
        color: #666;
        font-size: 16px;
        font-weight: 500;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .tab-btn:hover {
        background: rgba(121, 188, 177, 0.1);
        color: #79bcb1;
    }

    .tab-btn.active {
        background: #79bcb1;
        color: white;
        box-shadow: 0 2px 8px rgba(121, 188, 177, 0.3);
    }

    .tab-btn i {
        font-size: 18px;
    }

    .sales-boxes {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 20px;
        margin-top: 20px;
    }

    .recent-sales {
        flex-basis: 100%;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .title {
        font-size: 24px;
        font-weight: 500;
        margin-bottom: 20px;
        color: #333;
    }

    .details {
        display: flex;
        justify-content: space-between;
        width: 100%;
    }

    .details ul {
        flex: 1;
        list-style: none;
        padding: 0;
        margin-right: 15px;
    }

    .details ul:last-child {
        margin-right: 0;
    }

    .details ul li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .details ul li.topic {
        font-weight: 600;
        color: #333;
        font-size: 16px;
    }

    .details ul li a {
        color: #666;
        text-decoration: none;
        display: block;
        word-wrap: break-word;
        height: auto;
        min-height: 40px;
        padding: 5px 0;
        position: relative;
    }

    .rating-link {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .rating-link:hover {
        color: #007bff !important;
        transform: scale(1.05);
    }

    .rating-text {
        margin-left: 8px;
        font-weight: 500;
        color: #333;
    }

    .delete-btn {
        color: #e74c3c;
        cursor: pointer;
        float: right;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .delete-btn:hover {
        color: #c0392b;
    }

    /* Badge Styles */
    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    span.badge.Recommended {
        background-color: #d4edda !important;
        color: #155724 !important;
        border: 1px solid #c3e6cb !important;
    }

    span.badge.Not_Recommended {
        background-color: #f8d7da !important;
        color: #721c24 !important;
        border: 1px solid #f5c6cb !important;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border-radius: 12px;
        width: 85%;
        max-width: 650px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: modalFadeIn 0.3s ease;
    }

    .modal-header {
        padding: 15px 20px;
        background: #79bcb1;
        color: white;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close:hover {
        transform: scale(1.1);
    }

    .modal-body {
        padding: 20px;
    }

    .chart-container {
        width: 100%;
        height: 280px;
        margin-bottom: 25px;
    }

    .chart-info {
        text-align: center;
        color: #666;
        font-style: italic;
        margin-top: 10px;
    }

    .worker-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 15px;
    }

    .stat-item {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        flex: 1;
        margin: 0 5px;
    }

    .stat-label {
        display: block;
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-value {
        display: block;
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }

    .rating-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .summary-card {
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    /* Worker review colors */
    .summary-card:nth-child(1):not(.app-rating) {
        background: linear-gradient(135deg, rgba(121, 188, 177, 0.9) 0%, rgba(121, 188, 177, 0.7) 100%);
    }

    .summary-card:nth-child(2):not(.app-rating) {
        background: linear-gradient(135deg, rgba(255, 108, 64, 0.9) 0%, rgba(255, 108, 64, 0.7) 100%);
    }

    .summary-card:nth-child(3):not(.app-rating) {
        background: linear-gradient(135deg, rgba(54, 162, 235, 0.9) 0%, rgba(54, 162, 235, 0.7) 100%);
    }

    .summary-card:nth-child(4):not(.app-rating) {
        background: linear-gradient(135deg, rgba(255, 205, 86, 0.9) 0%, rgba(255, 205, 86, 0.7) 100%);
    }

    /* App review colors */
    .summary-card.app-rating:nth-child(1) {
        background: linear-gradient(135deg, rgba(121, 188, 177, 0.9) 0%, rgba(121, 188, 177, 0.7) 100%);
    }

    .summary-card.app-rating:nth-child(2) {
        background: linear-gradient(135deg, rgba(255, 108, 64, 0.9) 0%, rgba(255, 108, 64, 0.7) 100%);
    }

    .summary-card.app-rating:nth-child(3) {
        background: linear-gradient(135deg, rgba(54, 162, 235, 0.9) 0%, rgba(54, 162, 235, 0.7) 100%);
    }

    .summary-card.app-rating:nth-child(4) {
        background: linear-gradient(135deg, rgba(255, 205, 86, 0.9) 0%, rgba(255, 205, 86, 0.7) 100%);
    }

    .summary-card h3 {
        margin: 0 0 8px 0;
        font-size: 14px;
        font-weight: 500;
        opacity: 0.9;
    }

    .summary-card .rating-value {
        font-size: 22px;
        font-weight: bold;
        margin: 0;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .popup {
        position: fixed;
        bottom: 30px;
        right: 30px;
        padding: 15px 25px;
        background: #4CAF50;
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        display: none;
        z-index: 1000;
    }

    .popup.show {
        display: block;
        animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        animation-fill-mode: forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    .no-data {
        color: #888;
        font-style: italic;
    }

    /* RTL (Right-to-Left) Support */
    html[dir="rtl"] .details {
        direction: rtl;
    }

    html[dir="rtl"] .details ul {
        margin-right: 0;
        margin-left: 15px;
    }

    html[dir="rtl"] .details ul:last-child {
        margin-left: 0;
    }

    html[dir="rtl"] .delete-btn {
        float: left;
    }

    html[dir="rtl"] .tab-navigation {
        direction: rtl;
    }

    html[dir="rtl"] .worker-stats {
        direction: rtl;
    }

    html[dir="rtl"] .rating-summary {
        direction: rtl;
    }

    html[dir="rtl"] .modal-header {
        direction: rtl;
    }

    html[dir="rtl"] .close {
        float: left;
    }

    html[dir="rtl"] .chart-info {
        direction: rtl;
    }

    html[dir="rtl"] .summary-card {
        direction: rtl;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .tab-navigation {
            flex-direction: column;
        }

        .tab-btn {
            margin-bottom: 5px;
        }

        .details {
            flex-direction: column;
        }

        .details ul {
            margin-right: 0;
            margin-bottom: 20px;
        }

        .rating-summary {
            grid-template-columns: repeat(2, 1fr);
        }

        html[dir="rtl"] .details ul {
            margin-left: 0;
        }
    }
.home-section {
    position: relative;
    width: calc(100% - 240px);
    left: 240px;
    min-height: 100vh;
    transition: all 0.5s ease;
}

[dir="rtl"] .home-section {
    left: 0;
    right: 240px;
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Multilingual text for JavaScript
    const jsTranslations = {
        average_ratings_by_worker: "<?php echo $trans['average_ratings_by_worker']; ?>",
        total_reviews: "<?php echo $trans['total_reviews']; ?>",
        rating_categories: "<?php echo $trans['rating_categories']; ?>",
        rating_breakdown: "<?php echo $trans['rating_breakdown']; ?>",
        app_rating_details: "<?php echo $trans['app_rating_details']; ?>",
        confirm_delete: "<?php echo $trans['confirm_delete_review']; ?>",
        speed: "<?php echo $trans['speed_rating']; ?>",
        cleanliness: "<?php echo $trans['cleanliness']; ?>",
        professionalism: "<?php echo $trans['professionalism']; ?>",
        communication: "<?php echo $trans['communication']; ?>",
        ease_of_use: "<?php echo $trans['ease_of_use']; ?>",
        quality: "<?php echo $trans['quality']; ?>",
        support: "<?php echo $trans['support']; ?>",
        yes: "<?php echo $trans['yes']; ?>",
        no: "<?php echo $trans['no']; ?>",
        confirm_delete: "<?php echo $trans['confirm_delete_review']; ?>",
        worker_reviews: "<?php echo $trans['worker_reviews']; ?>",
        app_reviews: "<?php echo $trans['app_reviews']; ?>",
        rating_breakdown: "<?php echo $trans['rating_breakdown']; ?>",
        confirm_delete: <?php echo json_encode($trans['confirm_delete_review']); ?>,
        worker_reviews: <?php echo json_encode($trans['worker_reviews']); ?>,
        app_reviews: <?php echo json_encode($trans['app_reviews']); ?>
    };

    let ratingChart;
    let averageRatingChart;
    let workerRatingChart;
    let appRatingChart;

    // Worker data - populated from PHP
    const workerData = <?php
                        // Calculate average ratings per worker
                        $worker_averages = [];
                        if (!empty($worker_reviews)) {
                            $worker_stats = [];
                            foreach ($worker_reviews as $review) {
                                $worker_name = $review['worker_name'] ?? 'N/A';
                                if (!isset($worker_stats[$worker_name])) {
                                    $worker_stats[$worker_name] = [
                                        'total_reviews' => 0,
                                        'speed_sum' => 0,
                                        'cleanliness_sum' => 0,
                                        'professionalism_sum' => 0,
                                        'communication_sum' => 0,
                                        'overall_sum' => 0
                                    ];
                                }
                                $worker_stats[$worker_name]['total_reviews']++;
                                $worker_stats[$worker_name]['speed_sum'] += (float)$review['speed_rating'];
                                $worker_stats[$worker_name]['cleanliness_sum'] += (float)$review['cleanliness_rating'];
                                $worker_stats[$worker_name]['professionalism_sum'] += (float)$review['professionalism_rating'];
                                $worker_stats[$worker_name]['communication_sum'] += (float)$review['communication_rating'];
                                $worker_stats[$worker_name]['overall_sum'] += (float)$review['rating_stars'];
                            }

                            foreach ($worker_stats as $worker_name => $stats) {
                                $worker_averages[] = [
                                    'name' => $worker_name,
                                    'total_reviews' => $stats['total_reviews'],
                                    'speed_avg' => round($stats['speed_sum'] / $stats['total_reviews'], 1),
                                    'cleanliness_avg' => round($stats['cleanliness_sum'] / $stats['total_reviews'], 1),
                                    'professionalism_avg' => round($stats['professionalism_sum'] / $stats['total_reviews'], 1),
                                    'communication_avg' => round($stats['communication_sum'] / $stats['total_reviews'], 1),
                                    'overall_avg' => round($stats['overall_sum'] / $stats['total_reviews'], 1)
                                ];
                            }
                        }
                        echo json_encode($worker_averages);
                        ?>;

    // Tab switching functionality
    function switchTab(tabType) {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

        // Add active class to clicked tab
        document.getElementById(tabType + '-tab').classList.add('active');

        // Hide all content
        document.getElementById('worker-reviews-content').style.display = 'none';
        document.getElementById('app-reviews-content').style.display = 'none';

        // Show selected content
        document.getElementById(tabType + '-reviews-content').style.display = 'block';
    }

    function showAverageRatingChart() {
        document.getElementById('averageRatingModal').style.display = 'block';

        const ctx = document.getElementById('averageRatingChart').getContext('2d');

        if (averageRatingChart) {
            averageRatingChart.destroy();
        }

        // Use consistent color palette that matches other charts
        const consistentColors = [
            'rgba(121, 188, 177, 0.7)', // Teal - matches other charts
            'rgba(255, 108, 64, 0.7)', // Orange - matches other charts
            'rgba(54, 162, 235, 0.7)', // Blue - matches other charts
            'rgba(255, 205, 86, 0.7)', // Yellow - matches other charts
            'rgba(153, 102, 255, 0.7)', // Purple
            'rgba(255, 159, 64, 0.7)', // Light Orange
            'rgba(75, 192, 192, 0.7)', // Light Teal
            'rgba(255, 99, 132, 0.7)', // Pink
            'rgba(201, 203, 207, 0.7)', // Gray
            'rgba(83, 102, 255, 0.7)' // Light Blue
        ];

        // Generate background and border colors for each worker
        const backgroundColors = workerData.map((_, index) =>
            consistentColors[index % consistentColors.length]
        );

        const borderColors = workerData.map((_, index) =>
            consistentColors[index % consistentColors.length].replace('0.7)', '1)')
        );

        const data = {
            labels: workerData.map(worker => worker.name),
            datasets: [{
                label: jsTranslations.average_ratings_by_worker,
                data: workerData.map(worker => worker.overall_avg),
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 6
            }]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const worker = workerData[index];
                        showWorkerRatingChart(worker);
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: jsTranslations.average_ratings_by_worker,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const worker = workerData[context.dataIndex];
                                return `${jsTranslations.total_reviews}: ${worker.total_reviews}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        averageRatingChart = new Chart(ctx, config);
    }

    function showWorkerRatingChart(worker) {
        document.getElementById('workerRatingModal').style.display = 'block';
        document.getElementById('workerModalTitle').textContent = `${worker.name} - ${jsTranslations.rating_breakdown} (${worker.overall_avg}/5)`;
        document.getElementById('worker-total-reviews').textContent = worker.total_reviews;
        document.getElementById('worker-overall-avg').textContent = worker.overall_avg + '/5';

        const ctx = document.getElementById('workerRatingChart').getContext('2d');

        if (workerRatingChart) {
            workerRatingChart.destroy();
        }

        const data = {
            labels: [
                jsTranslations.speed,
                jsTranslations.cleanliness,
                jsTranslations.professionalism,
                jsTranslations.communication
            ],
            datasets: [{
                label: jsTranslations.rating_categories,
                data: [
                    worker.speed_avg,
                    worker.cleanliness_avg,
                    worker.professionalism_avg,
                    worker.communication_avg
                ],
                backgroundColor: [
                    'rgba(121, 188, 177, 0.7)',
                    'rgba(255, 108, 64, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 205, 86, 0.7)'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        };

        const config = {
            type: 'polarArea',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: jsTranslations.rating_categories,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}/5`;
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        };

        workerRatingChart = new Chart(ctx, config);
    }

    // Worker individual rating chart function
    function showIndividualRatingChart(review) {
        document.getElementById('ratingModal').style.display = 'block';

        // Update summary cards with individual review data
        document.getElementById('speed-rating-display').textContent = review.speed_rating + '/5';
        document.getElementById('cleanliness-rating-display').textContent = review.cleanliness_rating + '/5';
        document.getElementById('professionalism-rating-display').textContent = review.professionalism_rating + '/5';
        document.getElementById('communication-rating-display').textContent = review.communication_rating + '/5';

        // Initialize chart
        const ctx = document.getElementById('ratingChart').getContext('2d');

        // Destroy existing chart if it exists
        if (ratingChart) {
            ratingChart.destroy();
        }

        // Define distinct colors for each rating category
        const colors = {
            speed: 'rgba(121, 188, 177, 0.7)', // green
            cleanliness: 'rgba(255, 108, 64, 0.9)', // Red
            professionalism: 'rgba(54, 162, 235, 0.9)', // blue
            communication: 'rgba(255, 205, 86, 0.9)' // Orange
        };

        const data = {
            labels: [jsTranslations.speed,
                jsTranslations.cleanliness,
                jsTranslations.professionalism,
                jsTranslations.communication
            ],
            datasets: [{
                label: 'Rating',
                data: [
                    parseFloat(review.speed_rating),
                    parseFloat(review.cleanliness_rating),
                    parseFloat(review.professionalism_rating),
                    parseFloat(review.communication_rating)
                ],
                backgroundColor: [
                    colors.speed,
                    colors.cleanliness,
                    colors.professionalism,
                    colors.communication
                ],
                borderColor: [
                    colors.speed,
                    colors.cleanliness,
                    colors.professionalism,
                    colors.communication
                ],
                borderWidth: 2,
                borderRadius: 6
            }]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: jsTranslations.rating_breakdown + ' - ' + review.client_name,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        ratingChart = new Chart(ctx, config);
    }

    // App rating chart function
    function showAppRatingChart(review) {
        document.getElementById('appRatingModal').style.display = 'block';

        // Update summary cards with app review data
        document.getElementById('ease-rating-display').textContent = review.ease_rating + '/5';
        document.getElementById('quality-rating-display').textContent = review.quality_rating + '/5';
        document.getElementById('support-rating-display').textContent = review.support_rating + '/5';
        document.getElementById('recommend-display').textContent = review.would_recommend == 1 ? jsTranslations.yes : jsTranslations.no;

        // Initialize chart
        const ctx = document.getElementById('appRatingChart').getContext('2d');

        // Destroy existing chart if it exists
        if (appRatingChart) {
            appRatingChart.destroy();
        }

        const data = {
            labels: [jsTranslations.ease_of_use,
                jsTranslations.quality,
                jsTranslations.support
            ],
            datasets: [{
                label: 'Rating',
                data: [
                    parseFloat(review.ease_rating),
                    parseFloat(review.quality_rating),
                    parseFloat(review.support_rating)
                ],
                backgroundColor: [
                    'rgba(121, 188, 177, 0.7)',
                    'rgba(255, 108, 64, 0.9)',
                    'rgba(54, 162, 235, 0.9)'
                ],
                borderColor: [
                    'rgba(121, 188, 177, 0.7)',
                    'rgba(255, 108, 64, 0.9)',
                    'rgba(54, 162, 235, 0.9)'
                ],
                borderWidth: 2,
                borderRadius: 6
            }]
        };

        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: jsTranslations.app_rating_details + ' - ' + review.user_name,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}/5`;
                            }
                        }
                    }
                }
            }
        };

        appRatingChart = new Chart(ctx, config);
    }

    // Modal close functions
    function closeModal() {
        document.getElementById('ratingModal').style.display = 'none';
    }

    function closeAverageModal() {
        document.getElementById('averageRatingModal').style.display = 'none';
    }

    function closeWorkerModal() {
        document.getElementById('workerRatingModal').style.display = 'none';
    }

    function closeAppModal() {
        document.getElementById('appRatingModal').style.display = 'none';
    }

    function confirmDelete(reviewId, reviewType) {
        if (confirm(jsTranslations.confirm_delete)) {
            document.getElementById('delete-review-id').value = reviewId;
            document.getElementById('delete-review-type').value = reviewType;
            document.getElementById('delete-form').submit();
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const ratingModal = document.getElementById('ratingModal');
        const averageRatingModal = document.getElementById('averageRatingModal');
        const workerRatingModal = document.getElementById('workerRatingModal');
        const appRatingModal = document.getElementById('appRatingModal');

        if (event.target == ratingModal) {
            closeModal();
        }
        if (event.target == averageRatingModal) {
            closeAverageModal();
        }
        if (event.target == workerRatingModal) {
            closeWorkerModal();
        }
        if (event.target == appRatingModal) {
            closeAppModal();
        }
    }

    // Auto-hide popup after 3 seconds
    window.onload = function() {
        var popup = document.getElementById('popup');
        if (popup.classList.contains('show')) {
            setTimeout(function() {
                popup.classList.remove('show');
            }, 3000);
        }
    };
</script>
<?php include_once 'includes/footer.php'; ?>