<?php
header('Content-Type: application/json');
include_once "../db.php";

$response = array();

// Check if service_id is provided in the query string
if (isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];

    try {
        // Prepare and execute the SQL query
        $sql = "SELECT users.unique_id, users.user_id, users.fname, users.lname, users.skills, users.img, AVG(review.rating_stars) AS avg_rating
                FROM users
                LEFT JOIN review ON users.user_id = review.worker_id
                WHERE users.service_id = :service_id AND users.access_status = 'approved'
                GROUP BY users.user_id
                ORDER BY users.user_id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT); // Assuming service_id is integer
        $stmt->execute();

        // Check if there are results
        if ($stmt->rowCount() > 0) {
            $workers = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Convert avg_rating to star representation
                $row['stars_html'] = generateStarRating($row['avg_rating']);

                $workers[] = $row;
            }

            $response['success'] = true;
            $response['workers'] = $workers;
        } else {
            $response['success'] = false;
            $response['message'] = 'No workers found for this service.';
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Service ID not provided.';
}

// Output JSON response
echo json_encode($response);

// Close the database connection
$conn = null;

// Function to generate star rating HTML
function generateStarRating($rating) {
    $stars_html = '';
    $full_stars = floor($rating); // Full stars
    $half_star = $rating - $full_stars; // Half star

    // Full stars
    for ($i = 0; $i < $full_stars; $i++) {
        $stars_html .= '<span class="fa fa-star"></span>';
    }

    // Half star
    if ($half_star >= 0.5) {
        $stars_html .= '<span class="fa fa-star-half-o"></span>';
    }

    // Empty stars (if needed)
    $empty_stars = 5 - ceil($rating); // Remaining empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $stars_html .= '<span class="fa fa-star-o"></span>';
    }

    return $stars_html;
}
?>
