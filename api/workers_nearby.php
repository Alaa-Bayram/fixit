<?php
header('Content-Type: application/json');
include_once "../db.php";

$response = array();

// Check if client_id and service_id are provided in the query string
if (isset($_GET['client_id']) && isset($_GET['service_id'])) {
    $client_id = $_GET['client_id'];
    $service_id = $_GET['service_id'];

    try {
        // Get the region of the client
        $sql = "SELECT region FROM users WHERE user_id = :client_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array(':client_id' => $client_id));
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        $client_region = $client['region'];

        // Fetch workers in the same region who provide the service
        $sql = "SELECT users.unique_id, users.user_id, users.fname, users.lname, users.skills, users.img, AVG(review.rating_stars) AS avg_rating
                FROM users
                LEFT JOIN review ON users.user_id = review.worker_id
                WHERE users.region = :region AND users.service_id = :service_id AND users.usertype = 'worker' AND users.access_status = 'approved'
                GROUP BY users.user_id
                ORDER BY users.user_id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array(':region' => $client_region, ':service_id' => $service_id));
        $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare response based on fetched data
        if ($workers) {
            $response['success'] = true;
            $response['workers'] = array();

            foreach ($workers as $worker) {
                // Convert avg_rating to star representation
                $worker['stars_html'] = generateStarRating($worker['avg_rating']);
                $response['workers'][] = $worker;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No workers found in your region for this service.";
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Client ID or Service ID not provided.";
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
