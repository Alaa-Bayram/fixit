<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_type = isset($_POST['reviewType']) ? $_POST['reviewType'] : '';
    
    // Get all rating values (no more overall_rating)
    $speed_rating = isset($_POST['speed_rating']) ? (int)$_POST['speed_rating'] : 0;
    $cleanliness_rating = isset($_POST['cleanliness_rating']) ? (int)$_POST['cleanliness_rating'] : 0;
    $professionalism_rating = isset($_POST['professionalism_rating']) ? (int)$_POST['professionalism_rating'] : 0;
    $communication_rating = isset($_POST['communication_rating']) ? (int)$_POST['communication_rating'] : 0;
    
    // Calculate overall rating as average of the 4 criteria
    $overall_rating = round(($speed_rating + $cleanliness_rating + $professionalism_rating + $communication_rating) / 4);
    
    $review_text = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $review_date = date('Y-m-d H:i:s');

    // Validate ratings
    $required_ratings = [
        'Speed' => $speed_rating,
        'Cleanliness' => $cleanliness_rating,
        'Professionalism' => $professionalism_rating,
        'Communication' => $communication_rating
    ];
    
    foreach ($required_ratings as $name => $rating) {
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => "$name rating must be between 1 and 5"]);
            exit;
        }
    }
    
    if (empty($review_text)) {
        echo json_encode(['success' => false, 'message' => 'Detailed feedback cannot be empty']);
        exit;
    }

    if ($review_type === 'worker') {
        $worker_id = isset($_POST['worker_id']) ? (int)$_POST['worker_id'] : 0;
        
        if ($worker_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid worker ID']);
            exit;
        }

        // Insert with calculated overall rating
        $stmt = $conn->prepare("INSERT INTO review 
            (user_id, worker_id, rating_stars, speed_rating, cleanliness_rating, 
             professionalism_rating, communication_rating, comment, review_date, review_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'worker')");
        
        $stmt->bind_param("iiiiiiiss", 
            $user_id, 
            $worker_id, 
            $overall_rating, // This is now the calculated value
            $speed_rating,
            $cleanliness_rating,
            $professionalism_rating,
            $communication_rating,
            $review_text,
            $review_date
        );
    } elseif ($review_type === 'app') {
        // For app reviews (if you want to keep this option)
        $stmt = $conn->prepare("INSERT INTO review 
            (user_id, rating_stars, speed_rating, cleanliness_rating, 
             professionalism_rating, communication_rating, comment, review_date, review_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'app')");
        
        $stmt->bind_param("iiiiiiss", 
            $user_id, 
            $overall_rating, // This is now the calculated value
            $speed_rating,
            $cleanliness_rating,
            $professionalism_rating,
            $communication_rating,
            $review_text,
            $review_date
        );
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid review type']);
        exit;
    }

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
?>