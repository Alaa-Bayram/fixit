<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate all ratings
    $requiredRatings = ['ease_rating', 'quality_rating', 'support_rating'];
    foreach ($requiredRatings as $rating) {
        if (!isset($_POST[$rating]) || $_POST[$rating] < 1 || $_POST[$rating] > 5) {
            echo json_encode(['success' => false, 'message' => 'Please provide valid ratings for all categories']);
            exit;
        }
    }

    if (!isset($_POST['would_recommend'])) {
        echo json_encode(['success' => false, 'message' => 'Please indicate if you would recommend us']);
        exit;
    }

    $review_text = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $review_date = date('Y-m-d H:i:s');
    $would_recommend = $_POST['would_recommend'] === '1' ? 1 : 0;

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO review 
        (user_id, ease_rating, quality_rating, support_rating, would_recommend, comment, review_date, review_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'app')");
    
    $stmt->bind_param("iiiiiss", 
        $user_id,
        $_POST['ease_rating'],
        $_POST['quality_rating'],
        $_POST['support_rating'],
        $would_recommend,
        $review_text,
        $review_date
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting review: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}