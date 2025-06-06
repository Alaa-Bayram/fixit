<?php
header('Content-Type: application/json');
include_once "../db.php";

$response = array();

// Check if region and service_id are provided in the query string
if (isset($_GET['region']) && isset($_GET['service_id'])) {
    $region = $_GET['region'];
    $service_id = $_GET['service_id'];

    try {
        // Prepare and execute the SQL query
        $sql = "SELECT u.user_id, u.fname, u.lname, u.skills, u.img, u.unique_id, 
                       AVG(r.rating_stars) AS avg_rating
                FROM users u
                LEFT JOIN review r ON u.user_id = r.worker_id
                WHERE u.region = :region AND u.service_id = :service_id AND u.access_status = 'approved'
                GROUP BY u.user_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':region', $region, PDO::PARAM_STR);
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
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
            $response['message'] = 'No workers found for this region and service.';
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Region or service ID not provided.';
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
