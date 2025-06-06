<?php

// Check database connection
if (!isset($pdo)) {
    die("Database connection not established");
}

// Initialize variables with default values
$total_reviews = 0;
$total_app_reviews = 0;
$avg_worker_rating = '0.0';
$avg_speed_rating = '0.0';
$avg_cleanliness_rating = '0.0';
$avg_professionalism_rating = '0.0';
$avg_communication_rating = '0.0';

// App rating averages
$avg_app_rating = '0.0';
$avg_ease_rating = '0.0';
$avg_quality_rating = '0.0';
$avg_support_rating = '0.0';
$avg_recommendation = '0.0';

$worker_reviews = [];
$app_reviews = [];
$rating_distribution = [];
$app_rating_distribution = [];
$recent_reviews = [];
$recent_app_reviews = [];
$top_workers = [];
$monthly_stats = [];

try {
    // Handle review deletion
    if (isset($_POST['delete_review']) && isset($_POST['review_id'])) {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = "Invalid security token. Please try again.";
        } else {
            $review_id = (int)$_POST['review_id'];

            if ($review_id > 0) {
                $delete_query = "DELETE FROM review WHERE review_id = ?";
                $stmt = $pdo->prepare($delete_query);

                if ($stmt->execute([$review_id])) {
                    $_SESSION['success_message'] = "Review deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to delete review. Please try again.";
                }
            } else {
                $_SESSION['error_message'] = "Invalid review ID.";
            }
        }

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Generate CSRF token if not exists
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // 1. Fetch total number of worker reviews
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_reviews FROM review WHERE review_type = 'worker'");
    $stmt->execute();
    $row_total = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_reviews = $row_total['total_reviews'] ?? 0;

    // 2. Fetch total number of app reviews
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_app_reviews FROM review WHERE review_type = 'app'");
    $stmt->execute();
    $row_app_total = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_app_reviews = $row_app_total['total_app_reviews'] ?? 0;

    // 3. Calculate average ratings for worker reviews
    $stmt = $pdo->prepare("
        SELECT 
            AVG(CASE WHEN rating_stars > 0 THEN rating_stars END) as avg_rating,
            AVG(CASE WHEN speed_rating > 0 THEN speed_rating END) as avg_speed,
            AVG(CASE WHEN cleanliness_rating > 0 THEN cleanliness_rating END) as avg_cleanliness,
            AVG(CASE WHEN professionalism_rating > 0 THEN professionalism_rating END) as avg_professionalism,
            AVG(CASE WHEN communication_rating > 0 THEN communication_rating END) as avg_communication
        FROM review
        WHERE review_type = 'worker'
    ");
    $stmt->execute();
    $avg_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $avg_worker_rating = $avg_data['avg_rating'] ? number_format($avg_data['avg_rating'], 1) : '0.0';
    $avg_speed_rating = $avg_data['avg_speed'] ? number_format($avg_data['avg_speed'], 1) : '0.0';
    $avg_cleanliness_rating = $avg_data['avg_cleanliness'] ? number_format($avg_data['avg_cleanliness'], 1) : '0.0';
    $avg_professionalism_rating = $avg_data['avg_professionalism'] ? number_format($avg_data['avg_professionalism'], 1) : '0.0';
    $avg_communication_rating = $avg_data['avg_communication'] ? number_format($avg_data['avg_communication'], 1) : '0.0';

    // 4. Calculate average ratings for app reviews
    $stmt = $pdo->prepare("
        SELECT 
            AVG(CASE WHEN rating_stars > 0 THEN rating_stars END) as avg_rating,
            AVG(CASE WHEN ease_rating > 0 THEN ease_rating END) as avg_ease,
            AVG(CASE WHEN quality_rating > 0 THEN quality_rating END) as avg_quality,
            AVG(CASE WHEN support_rating > 0 THEN support_rating END) as avg_support,
            AVG(CASE WHEN would_recommend IS NOT NULL THEN would_recommend END) as avg_recommendation
        FROM review
        WHERE review_type = 'app'
    ");
    $stmt->execute();
    $app_avg_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $avg_app_rating = $app_avg_data['avg_rating'] ? number_format($app_avg_data['avg_rating'], 1) : '0.0';
    $avg_ease_rating = $app_avg_data['avg_ease'] ? number_format($app_avg_data['avg_ease'], 1) : '0.0';
    $avg_quality_rating = $app_avg_data['avg_quality'] ? number_format($app_avg_data['avg_quality'], 1) : '0.0';
    $avg_support_rating = $app_avg_data['avg_support'] ? number_format($app_avg_data['avg_support'], 1) : '0.0';
    $avg_recommendation = $app_avg_data['avg_recommendation'] ? number_format($app_avg_data['avg_recommendation'], 1) : '0.0';

    // 5. Fetch all worker reviews with user information
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            u1.fname as client_first_name,
            u1.lname as client_last_name,
            u1.email as client_email,
            u2.fname as worker_first_name,
            u2.lname as worker_last_name,
            u2.email as worker_email,
            u2.skills as worker_skills
        FROM review r
        LEFT JOIN users u1 ON r.user_id = u1.user_id
        LEFT JOIN users u2 ON r.worker_id = u2.user_id
        WHERE r.review_type = 'worker'
        ORDER BY r.review_date DESC
    ");
    $stmt->execute();
    $reviews_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process worker reviews to add formatted names
    $worker_reviews = [];
    foreach ($reviews_raw as $review) {
        $review['client_name'] = trim(($review['client_first_name'] ?? '') . ' ' . ($review['client_last_name'] ?? ''));
        $review['worker_name'] = trim(($review['worker_first_name'] ?? '') . ' ' . ($review['worker_last_name'] ?? ''));

        // Set default values if names are empty
        if (empty($review['client_name'])) {
            $review['client_name'] = 'Unknown Client';
        }
        if (empty($review['worker_name'])) {
            $review['worker_name'] = 'Unknown Worker';
        }

        $worker_reviews[] = $review;
    }

    // 6. Fetch all app reviews with user information
    $stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.fname as user_first_name,
        u.lname as user_last_name,
        u.email as user_email,
        ROUND((COALESCE(r.ease_rating, 0) + COALESCE(r.quality_rating, 0) + COALESCE(r.support_rating, 0)) / 3, 1) as calculated_overall_rating
    FROM review r
    LEFT JOIN users u ON r.user_id = u.user_id
    WHERE r.review_type = 'app'
    ORDER BY r.review_date DESC
");
    $stmt->execute();
    $app_reviews_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process app reviews to add formatted names
    $app_reviews = [];
    foreach ($app_reviews_raw as $review) {
        $review['user_name'] = trim(($review['user_first_name'] ?? '') . ' ' . ($review['user_last_name'] ?? ''));

        // Set default values if name is empty
        if (empty($review['user_name'])) {
            $review['user_name'] = 'Anonymous User';
        }

        // Use calculated overall rating or fallback to existing rating_stars
        if (isset($review['calculated_overall_rating']) && $review['calculated_overall_rating'] > 0) {
            $review['rating_stars'] = $review['calculated_overall_rating'];
        } elseif (!isset($review['rating_stars']) || $review['rating_stars'] <= 0) {
            // Calculate on the fly if not available
            $ease = (float)($review['ease_rating'] ?? 0);
            $quality = (float)($review['quality_rating'] ?? 0);
            $support = (float)($review['support_rating'] ?? 0);
            $review['rating_stars'] = $ease + $quality + $support > 0 ? round(($ease + $quality + $support) / 3, 1) : 0;
        }

        // Convert would_recommend to readable format
        $review['recommendation_text'] = '';
        if ($review['would_recommend'] === 1) {
            $review['recommendation_text'] = 'Yes';
        } elseif ($review['would_recommend'] === 0) {
            $review['recommendation_text'] = 'No';
        } else {
            $review['recommendation_text'] = 'N/A';
        }

        $app_reviews[] = $review;
    }

    // 7. Fetch rating distribution for worker reviews
    $stmt = $pdo->prepare("
        SELECT 
            rating_stars,
            COUNT(*) as count
        FROM review 
        WHERE rating_stars IS NOT NULL AND rating_stars > 0 AND review_type = 'worker'
        GROUP BY rating_stars
        ORDER BY rating_stars DESC
    ");
    $stmt->execute();
    $rating_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Fetch rating distribution for app reviews
    $stmt = $pdo->prepare("
        SELECT 
            rating_stars,
            COUNT(*) as count
        FROM review 
        WHERE rating_stars IS NOT NULL AND rating_stars > 0 AND review_type = 'app'
        GROUP BY rating_stars
        ORDER BY rating_stars DESC
    ");
    $stmt->execute();
    $app_rating_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 9. Fetch recent worker reviews (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            r.review_id,
            r.comment,
            r.rating_stars,
            r.review_date,
            u1.fname as client_first_name,
            u1.lname as client_last_name,
            u2.fname as worker_first_name,
            u2.lname as worker_last_name
        FROM review r
        LEFT JOIN users u1 ON r.user_id = u1.user_id
        LEFT JOIN users u2 ON r.worker_id = u2.user_id
        WHERE r.review_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND r.review_type = 'worker'
        ORDER BY r.review_date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_reviews_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process recent worker reviews
    $recent_reviews = [];
    foreach ($recent_reviews_raw as $review) {
        $review['client_name'] = trim(($review['client_first_name'] ?? '') . ' ' . ($review['client_last_name'] ?? ''));
        $review['worker_name'] = trim(($review['worker_first_name'] ?? '') . ' ' . ($review['worker_last_name'] ?? ''));

        if (empty($review['client_name'])) {
            $review['client_name'] = 'Unknown Client';
        }
        if (empty($review['worker_name'])) {
            $review['worker_name'] = 'Unknown Worker';
        }

        $recent_reviews[] = $review;
    }

    // 10. Fetch recent app reviews (last 30 days)
    $stmt = $pdo->prepare("
    SELECT 
        r.review_id,
        r.comment,
        r.rating_stars,
        r.review_date,
        r.would_recommend,
        r.ease_rating,
        r.quality_rating,
        r.support_rating,
        u.fname as user_first_name,
        u.lname as user_last_name,
        ROUND((COALESCE(r.ease_rating, 0) + COALESCE(r.quality_rating, 0) + COALESCE(r.support_rating, 0)) / 3, 1) as calculated_overall_rating
    FROM review r
    LEFT JOIN users u ON r.user_id = u.user_id
    WHERE r.review_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND r.review_type = 'app'
    ORDER BY r.review_date DESC
    LIMIT 10
");
    $stmt->execute();
    $recent_app_reviews_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process recent app reviews
    $recent_app_reviews = [];
    foreach ($recent_app_reviews_raw as $review) {
        $review['user_name'] = trim(($review['user_first_name'] ?? '') . ' ' . ($review['user_last_name'] ?? ''));

        if (empty($review['user_name'])) {
            $review['user_name'] = 'Anonymous User';
        }

        // Convert would_recommend to readable format
        $review['recommendation_text'] = '';
        if ($review['would_recommend'] === 1) {
            $review['recommendation_text'] = 'Yes';
        } elseif ($review['would_recommend'] === 0) {
            $review['recommendation_text'] = 'No';
        } else {
            $review['recommendation_text'] = 'N/A';
        }

        $recent_app_reviews[] = $review;
    }
} catch (PDOException $e) {
    error_log('Database Error in reviews_data.php: ' . $e->getMessage());
    error_log('Query failed at line: ' . $e->getLine());

    // Keep default values that were initialized at the top
    $_SESSION['error_message'] = "Database connection issue. Please check your database configuration.";
} catch (Exception $e) {
    error_log('General Error in reviews_data.php: ' . $e->getMessage());
    $_SESSION['error_message'] = "An unexpected error occurred while loading review data.";
}

// Helper function to format review date
function format_review_date($date)
{
    if (!$date) return 'N/A';

    try {
        $review_date = new DateTime($date);
        $now = new DateTime();
        $diff = $now->diff($review_date);

        if ($diff->days == 0) {
            return 'Today';
        } elseif ($diff->days == 1) {
            return 'Yesterday';
        } elseif ($diff->days < 7) {
            return $diff->days . ' days ago';
        } elseif ($diff->days < 30) {
            $weeks = floor($diff->days / 7);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } else {
            return $review_date->format('M j, Y');
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}

// Helper function to get rating color based on value
function get_rating_color($rating)
{
    $rating = (float)$rating;
    if ($rating >= 4.5) return '#4CAF50'; // Green
    if ($rating >= 3.5) return '#FF9800'; // Orange
    if ($rating >= 2.5) return '#FFC107'; // Yellow
    if ($rating >= 1.5) return '#FF5722'; // Red-Orange
    return '#F44336'; // Red
}

// Helper function to generate star rating HTML
function generate_star_rating($rating, $show_text = true)
{
    $rating = (int)$rating;
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="bx bxs-star" style="color: gold;"></i>';
        } else {
            $html .= '<i class="bx bx-star" style="color: #ccc;"></i>';
        }
    }

    if ($show_text) {
        $html .= '<span class="rating-text">' . $rating . '/5</span>';
    }

    return $html;
}

// Helper function to get recommendation badge
function get_recommendation_badge($would_recommend)
{
    if ($would_recommend === 1) {
        return '<span class="badge badge-success">Recommended</span>';
    } elseif ($would_recommend === 0) {
        return '<span class="badge badge-danger">Not Recommended</span>';
    } else {
        return '<span class="badge badge-secondary">N/A</span>';
    }
}

// Use calculated overall rating or fallback
if (isset($review['calculated_overall_rating']) && $review['calculated_overall_rating'] > 0) {
    $review['rating_stars'] = $review['calculated_overall_rating'];
} elseif (!isset($review['rating_stars']) || $review['rating_stars'] <= 0) {
    // Calculate on the fly if not available
    $ease = (float)($review['ease_rating'] ?? 0);
    $quality = (float)($review['quality_rating'] ?? 0);
    $support = (float)($review['support_rating'] ?? 0);
    $review['rating_stars'] = $ease + $quality + $support > 0 ? round(($ease + $quality + $support) / 3, 1) : 0;
}
