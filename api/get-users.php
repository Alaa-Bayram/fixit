<?php
session_start();
include_once "../db.php";

// Fetch the unique ID of the logged-in user
$outgoing_id = $_SESSION['unique_id'];

// Initialize an array to hold the user data
$users = [];

// Get the search term from the query parameters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Check if the logged-in user is a worker or not
    $sql_check_worker = "SELECT usertype FROM users WHERE unique_id = :outgoing_id";
    $stmt = $conn->prepare($sql_check_worker);
    $stmt->bindParam(':outgoing_id', $outgoing_id, PDO::PARAM_INT);
    $stmt->execute();
    $row_check_worker = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the SQL query returns no results, set $is_worker to null
    $is_worker = $row_check_worker ? $row_check_worker['usertype'] : null;

    // Prepare SQL query based on user type
    if ($is_worker == 'worker') {
        // If the logged-in user is a worker, select only clients to display
        $sql = "SELECT * FROM users WHERE usertype = 'client' AND access_status != 'pending' AND access_status != 'in progress'";
    } else  {
        // If the logged-in user is not a worker, select only workers to display
        $sql = "SELECT * FROM users WHERE usertype = 'worker' AND access_status != 'pending' AND access_status != 'in progress'";
    }
    
    

    // Add search filter if a search term is provided
    if (!empty($searchTerm)) {
        $sql .= " AND (fname LIKE :searchTerm OR lname LIKE :searchTerm)";
    }

    // Order by status
    $sql .= " ORDER BY status ASC";

    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    if (!empty($searchTerm)) {
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    $stmt->execute();

    // Fetch user data and store it in the $users array
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = $row;
    }

    // Check if any users were found
    if (empty($users)) {
        $output = "No users are available to chat";
    } else {
        // Convert the $users array to JSON format
        $output = json_encode($users);
    }
} catch (PDOException $e) {
    // Handle database errors
    $output = "Error fetching users: " . $e->getMessage();
}

// Set the content type to JSON
header('Content-Type: application/json');

// Output the JSON data
echo $output;
?>
