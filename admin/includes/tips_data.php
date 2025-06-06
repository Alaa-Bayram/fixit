<?php
require_once 'config.php';
require_once 'functions.php';

// Initialize arrays to prevent undefined variable errors
$daily_tips = [];
$seasonal_tips = [];
$all_tips = [];
$all_seasonal_tips = [];
$total_tips = 0;

try {
    // Check PDO connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Fetch daily tips (limited for dashboard display)
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE type = 'daily tips' ORDER BY date DESC LIMIT 5");
    $stmt->execute();
    $daily_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch seasonal tips (limited for dashboard display)
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE type = 'seasonal tips' ORDER BY date DESC LIMIT 5");
    $stmt->execute();
    $seasonal_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all daily tips (for all_tips.php page)
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE type = 'daily tips' ORDER BY date DESC");
    $stmt->execute();
    $all_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all seasonal tips (for all_tips.php page)
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE type = 'seasonal tips' ORDER BY date DESC");
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
    $stmt = $pdo->prepare("SELECT * FROM tips ORDER BY date DESC LIMIT 6");
    $stmt->execute();
    $recent_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Popular tips (you can modify this query based on your needs)
    $stmt = $pdo->prepare("SELECT * FROM tips ORDER BY tip_id DESC LIMIT 3");
    $stmt->execute();
    $popular_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $total_tips = 0;
    $daily_tips_count = 0;
    $seasonal_tips_count = 0;
}

// Helper function to get tip by ID (used by edit functions)
function getTipById($tip_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM tips WHERE tip_id = :tip_id");
        $stmt->bindParam(':tip_id', $tip_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching tip by ID: ' . $e->getMessage());
        return false;
    }
}

// Helper function to check if tip exists
function tipExists($title, $type, $exclude_id = null) {
    global $pdo;
    
    try {
        if ($exclude_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE title = :title AND type = :type AND tip_id != :exclude_id");
            $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tips WHERE title = :title AND type = :type");
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

// Make variables available for templates
$tips_stats = [
    'total' => $total_tips,
    'daily' => $daily_tips_count,
    'seasonal' => $seasonal_tips_count
];
?>