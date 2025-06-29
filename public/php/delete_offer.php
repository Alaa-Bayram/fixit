<?php
session_start();
include_once "db.php";

if (!isset($_SESSION['authenticated']) || !isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['offer_id']) || !isset($_GET['emergency_id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Offer ID and Emergency ID are required']);
    exit();
}

$offer_id = $_GET['offer_id'];
$emergency_id = $_GET['emergency_id'];
$client_id = $_SESSION['user_id'];

try {
    // Verify the emergency belongs to the current client
    $verify_sql = "SELECT id FROM emergencies WHERE id = ? AND client_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $emergency_id, $client_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        header("HTTP/1.1 403 Forbidden");
        echo json_encode(['success' => false, 'message' => 'You are not authorized to delete offers for this emergency']);
        exit();
    }
    
    // Verify the offer exists for this emergency
    $check_offer = $conn->prepare("SELECT id FROM offers WHERE id = ? AND emergency_id = ?");
    $check_offer->bind_param("ii", $offer_id, $emergency_id);
    $check_offer->execute();
    $offer_result = $check_offer->get_result();
    
    if ($offer_result->num_rows === 0) {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['success' => false, 'message' => 'Offer not found for this emergency']);
        exit();
    }
    
    // Delete the offer
    $delete_sql = "DELETE FROM offers WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $offer_id);
    $delete_stmt->execute();
    
    if ($delete_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Offer deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No offer was deleted']);
    }
    
    $delete_stmt->close();
    $check_offer->close();
    $verify_stmt->close();
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>