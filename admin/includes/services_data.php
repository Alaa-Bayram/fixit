<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Include language file to get current language
require_once 'lang.php';

// Check database connection
if (!isset($pdo)) {
    die("Database connection not established");
}

try {
    // Define column names based on current language
    $title_column = 'title';
    $description_column = 'description';
    
    switch($lang) {
        case 'ar':
            $title_column = 'title_ar';
            $description_column = 'description_ar';
            break;
        case 'fr':
            $title_column = 'title_fr';
            $description_column = 'description_fr';
            break;
        default:
            $title_column = 'title';
            $description_column = 'description';
            break;
    }

    // Query to fetch the top 3 requested services by appointment count
    $top_services_query = "
        SELECT s.{$title_column} as title, s.{$description_column} as description, s.images, COUNT(a.appointment_id) as appointment_count, u.skills
        FROM appointment a
        LEFT JOIN users u ON a.worker_id = u.user_id
        LEFT JOIN services s ON u.service_id = s.service_id
        WHERE s.{$title_column} IS NOT NULL AND s.{$title_column} != ''
        GROUP BY s.{$title_column}, s.{$description_column}, s.images, u.skills
        ORDER BY appointment_count DESC
        LIMIT 3
    ";

    $stmt = $pdo->query($top_services_query);
    $top_services = $stmt->fetchAll();

    // Fetch the count of services
    $count_query = "SELECT COUNT(*) as total_services FROM services";
    $stmt = $pdo->query($count_query);
    $row_services = $stmt->fetch();
    $total_services = $row_services['total_services'];

    // Show 3 services for preview with translation
    $preview_query = "
        SELECT service_id, {$title_column} as title, {$description_column} as description, images, date,
               title as title_en, title_fr, title_ar,
               description as description_en, description_fr, description_ar
        FROM services 
        WHERE {$title_column} IS NOT NULL AND {$title_column} != ''
        ORDER BY service_id DESC 
        LIMIT 3
    ";
    $stmt = $pdo->query($preview_query);
    $serv = $stmt->fetchAll();

    // Show all services with translation
    $all_query = "
        SELECT service_id, {$title_column} as title, {$description_column} as description, images, date,
               title as title_en, title_fr, title_ar,
               description as description_en, description_fr, description_ar
        FROM services 
        ORDER BY service_id DESC
    ";
    $stmt = $pdo->query($all_query);
    $all_services = $stmt->fetchAll();

    // If translated content is empty, fallback to English
    foreach ($all_services as &$service) {
        if (empty($service['title']) && !empty($service['title_en'])) {
            $service['title'] = $service['title_en'];
        }
        if (empty($service['description']) && !empty($service['description_en'])) {
            $service['description'] = $service['description_en'];
        }
    }

    // Same fallback for preview services
    foreach ($serv as &$service) {
        if (empty($service['title']) && !empty($service['title_en'])) {
            $service['title'] = $service['title_en'];
        }
        if (empty($service['description']) && !empty($service['description_en'])) {
            $service['description'] = $service['description_en'];
        }
    }

    // Same fallback for top services
    foreach ($top_services as &$service) {
        if (empty($service['title'])) {
            // Fetch English version as fallback
            $fallback_query = "SELECT title FROM services WHERE images = :images LIMIT 1";
            $fallback_stmt = $pdo->prepare($fallback_query);
            $fallback_stmt->bindParam(':images', $service['images']);
            $fallback_stmt->execute();
            $fallback = $fallback_stmt->fetch();
            if ($fallback) {
                $service['title'] = $fallback['title'];
            }
        }
    }

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    // Initialize with empty arrays if queries fail
    $top_services = [];
    $total_services = 0;
    $serv = [];
    $all_services = [];
}
?>