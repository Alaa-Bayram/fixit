<?php
require_once 'config.php';
require_once 'functions.php';

// Initialize arrays to prevent undefined variable errors
$daily_tips = [];
$seasonal_tips = [];
$all_tips = [];
$all_seasonal_tips = [];
$total_tips = 0;
$recent_tips = [];
$popular_tips = [];
$daily_tips_count = 0;
$seasonal_tips_count = 0;
$user_tips = [];

// Set default language (you can get this from session, user preference, or URL parameter)
$current_language = $_SESSION['language'] ?? $_GET['lang'] ?? 'en';

try {
    // Check PDO connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Helper function to get localized field names based on current language
    function getLocalizedFields($lang = 'en') {
        switch ($lang) {
            case 'fr':
                return [
                    'title' => 'title_fr',
                    'description' => 'description_fr',
                    'f_tip' => 'f_tip_fr',
                    's_tip' => 's_tip_fr'
                ];
            case 'ar':
                return [
                    'title' => 'title_ar',
                    'description' => 'description_ar',
                    'f_tip' => 'f_tip_ar',
                    's_tip' => 's_tip_ar'
                ];
            default: // English
                return [
                    'title' => 'title',
                    'description' => 'description',
                    'f_tip' => 'f_tip',
                    's_tip' => 's_tip'
                ];
        }
    }

    $fields = getLocalizedFields($current_language);

    // Fetch daily tips (limited for dashboard display)
    $stmt = $pdo->prepare("
        SELECT 
            tip_id, 
            user_id, 
            {$fields['title']} as title,
            {$fields['description']} as description,
            {$fields['f_tip']} as f_tip,
            {$fields['s_tip']} as s_tip,
            images,
            date,
            type,
            -- Fallback to English if localized content is empty
            COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
            COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
            COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
            COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
        FROM tips 
        WHERE type = 'daily tips' 
        ORDER BY date DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $daily_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch seasonal tips (limited for dashboard display)
    $stmt = $pdo->prepare("
        SELECT 
            tip_id, 
            user_id, 
            {$fields['title']} as title,
            {$fields['description']} as description,
            {$fields['f_tip']} as f_tip,
            {$fields['s_tip']} as s_tip,
            images,
            date,
            type,
            COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
            COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
            COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
            COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
        FROM tips 
        WHERE type = 'seasonal tips' 
        ORDER BY date DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $seasonal_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all daily tips (for all_tips.php page)
    $stmt = $pdo->prepare("
        SELECT 
            tip_id, 
            user_id, 
            {$fields['title']} as title,
            {$fields['description']} as description,
            {$fields['f_tip']} as f_tip,
            {$fields['s_tip']} as s_tip,
            images,
            date,
            type,
            COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
            COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
            COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
            COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
        FROM tips 
        WHERE type = 'daily tips' 
        ORDER BY date DESC
    ");
    $stmt->execute();
    $all_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all seasonal tips (for all_tips.php page)
    $stmt = $pdo->prepare("
        SELECT 
            tip_id, 
            user_id, 
            {$fields['title']} as title,
            {$fields['description']} as description,
            {$fields['f_tip']} as f_tip,
            {$fields['s_tip']} as s_tip,
            images,
            date,
            type,
            COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
            COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
            COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
            COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
        FROM tips 
        WHERE type = 'seasonal tips' 
        ORDER BY date DESC
    ");
    $stmt->execute();
    $all_seasonal_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count of all tips
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tips");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_tips = $result['total'] ?? 0;

    // Get counts for each type
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE type = 'daily tips'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $daily_tips_count = $result['count'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE type = 'seasonal tips'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $seasonal_tips_count = $result['count'] ?? 0;

    // Recent tips for dashboard (mixed daily and seasonal)
    $stmt = $pdo->prepare("
        SELECT 
            tip_id, 
            user_id, 
            {$fields['title']} as title,
            {$fields['description']} as description,
            {$fields['f_tip']} as f_tip,
            {$fields['s_tip']} as s_tip,
            images,
            date,
            type,
            COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
            COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
            COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
            COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
        FROM tips 
        ORDER BY date DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $recent_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Popular tips (most recent ones - you can modify this logic based on your needs)
    $stmt = $pdo->prepare("
        SELECT 
            tip_id, 
            user_id, 
            {$fields['title']} as title,
            {$fields['description']} as description,
            {$fields['f_tip']} as f_tip,
            {$fields['s_tip']} as s_tip,
            images,
            date,
            type,
            COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
            COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
            COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
            COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
        FROM tips 
        ORDER BY tip_id DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $popular_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get tips by user (if user_id is provided)
    if (isset($_GET['user_id']) || isset($_SESSION['user_id'])) {
        $user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
        $stmt = $pdo->prepare("
            SELECT 
                tip_id, 
                user_id, 
                {$fields['title']} as title,
                {$fields['description']} as description,
                {$fields['f_tip']} as f_tip,
                {$fields['s_tip']} as s_tip,
                images,
                date,
                type,
                COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
                COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
                COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
                COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
            FROM tips 
            WHERE user_id = :user_id 
            ORDER BY date DESC
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Log database errors
    error_log('Database Error in tips_data.php: ' . $e->getMessage());
    
    // Set empty arrays to prevent PHP errors
    $daily_tips = [];
    $seasonal_tips = [];
    $all_tips = [];
    $all_seasonal_tips = [];
    $recent_tips = [];
    $popular_tips = [];
    $user_tips = [];
    $total_tips = 0;
    $daily_tips_count = 0;
    $seasonal_tips_count = 0;
    
    // Optionally show user-friendly error message
    $_SESSION['error_message'] = "Unable to load tips data. Please try again later.";
    
} catch (Exception $e) {
    // Log general errors
    error_log('General Error in tips_data.php: ' . $e->getMessage());
    
    // Set defaults
    $daily_tips = [];
    $seasonal_tips = [];
    $all_tips = [];
    $all_seasonal_tips = [];
    $recent_tips = [];
    $popular_tips = [];
    $user_tips = [];
    $total_tips = 0;
    $daily_tips_count = 0;
    $seasonal_tips_count = 0;
}

// Helper function to get tip by ID (used by edit functions)
function getTipById($tip_id, $lang = 'en') {
    global $pdo;
    
    try {
        $fields = getLocalizedFields($lang);
        
        $stmt = $pdo->prepare("
            SELECT 
                *,
                COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
                COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
                COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
                COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
            FROM tips 
            WHERE tip_id = :tip_id
        ");
        $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching tip by ID: ' . $e->getMessage());
        return false;
    }
}

// Helper function to check if tip exists
function tipExists($title, $type, $exclude_id = null, $lang = 'en') {
    global $pdo;
    
    try {
        $fields = getLocalizedFields($lang);
        $title_field = $fields['title'];
        
        if ($exclude_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE {$title_field} = :title AND type = :type AND tip_id != :exclude_id");
            $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE {$title_field} = :title AND type = :type");
        }
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] > 0);
        
    } catch (PDOException $e) {
        error_log('Error checking tip existence: ' . $e->getMessage());
        return false;
    }
}

// Helper function to format tip images
function formatTipImages($images_string) {
    if (empty($images_string)) {
        return [];
    }
    
    // Assuming images are stored as comma-separated values or JSON
    if (strpos($images_string, ',') !== false) {
        return array_map('trim', explode(',', $images_string));
    } elseif (json_decode($images_string, true)) {
        return json_decode($images_string, true);
    } else {
        return [$images_string]; // Single image
    }
}

// Helper function to get tip statistics by user
function getTipStatsByUser($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_tips,
                COUNT(CASE WHEN type = 'daily tips' THEN 1 END) as daily_tips,
                COUNT(CASE WHEN type = 'seasonal tips' THEN 1 END) as seasonal_tips,
                MAX(date) as latest_tip_date
            FROM tips 
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching user tip stats: ' . $e->getMessage());
        return [
            'total_tips' => 0,
            'daily_tips' => 0,
            'seasonal_tips' => 0,
            'latest_tip_date' => null
        ];
    }
}

// Helper function to get tips by date range
function getTipsByDateRange($start_date, $end_date, $type = null, $lang = 'en') {
    global $pdo;
    
    try {
        $fields = getLocalizedFields($lang);
        
        $sql = "
            SELECT 
                tip_id, 
                user_id, 
                {$fields['title']} as title,
                {$fields['description']} as description,
                {$fields['f_tip']} as f_tip,
                {$fields['s_tip']} as s_tip,
                images,
                date,
                type,
                COALESCE(NULLIF({$fields['title']}, ''), title) as display_title,
                COALESCE(NULLIF({$fields['description']}, ''), description) as display_description,
                COALESCE(NULLIF({$fields['f_tip']}, ''), f_tip) as display_f_tip,
                COALESCE(NULLIF({$fields['s_tip']}, ''), s_tip) as display_s_tip
            FROM tips 
            WHERE date BETWEEN :start_date AND :end_date
        ";
        
        if ($type) {
            $sql .= " AND type = :type";
        }
        
        $sql .= " ORDER BY date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        if ($type) {
            $stmt->bindParam(':type', $type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error fetching tips by date range: ' . $e->getMessage());
        return [];
    }
}

// Make variables available for templates
$tips_stats = [
    'total' => $total_tips,
    'daily' => $daily_tips_count,
    'seasonal' => $seasonal_tips_count
];

// Language-specific stats if needed
$language_stats = [
    'current_language' => $current_language,
    'available_languages' => ['en', 'fr', 'ar']
];
?>