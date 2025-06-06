<?php
header('Content-Type: application/json');
include '../db.php';

$query = "SELECT 
    r.*, 
    u.fname AS user_name, 
    u.img AS profile_image,
    CONCAT(u.fname, ' ', u.lname) AS full_name,
    r.ease_rating,
    r.quality_rating,
    r.support_rating,
    r.would_recommend,
    r.comment,
    DATE_FORMAT(r.review_date, '%M %e, %Y') AS formatted_date
FROM review r
LEFT JOIN users u ON r.user_id = u.user_id
WHERE r.review_type = 'app' AND (r.comment IS NOT NULL AND r.comment != '')
ORDER BY r.review_date DESC
LIMIT 10";

try {
    $stmt = $conn->query($query);
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($testimonials);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
