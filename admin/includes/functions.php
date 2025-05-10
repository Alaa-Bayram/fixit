<?php
require_once 'config.php';

// Get total approved workers
function get_total_workers($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE usertype = 'worker' AND access_status = 'approved'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return 0;
    }
}

// Get total clients
function get_total_clients($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE usertype = 'client'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return 0;
    }
}

// Get total profits
function get_total_profits($pdo) {
    try {
        // Get appointment costs
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(cost), 0) FROM appointment");
        $stmt->execute();
        $appointments_cost = $stmt->fetchColumn();
        
        // Get emergency costs
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(cost), 0) FROM emergencies");
        $stmt->execute();
        $emergencies_cost = $stmt->fetchColumn();
        
        return $appointments_cost + $emergencies_cost;
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return 0;
    }
}

// Get total services
function get_total_services($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM services");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return 0;
    }
}

// Get top requested services
function get_top_services($pdo, $limit = 3) {
    try {
        $stmt = $pdo->prepare("
            SELECT s.title, s.images, COUNT(a.appointment_id) as appointment_count, u.skills
            FROM appointment a
            LEFT JOIN users u ON a.worker_id = u.user_id
            LEFT JOIN services s ON u.service_id = s.service_id
            GROUP BY s.title, s.images, u.skills
            ORDER BY appointment_count DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

// Get recent appointments
function get_recent_appointments($pdo, $limit = 3) {
    try {
        $stmt = $pdo->prepare("
            SELECT a.date, a.time, CONCAT(u.fname, ' ', u.lname) as worker, 
                   a.status, a.cost, a.is_done, u.skills
            FROM appointment a
            LEFT JOIN users u ON a.worker_id = u.user_id
            ORDER BY a.request_date DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

// Get admin details
function get_admin_details($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE usertype = 'admin' LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

// Search function for workers or services
function search_workers_services($pdo, $search_term) {
    try {
        $search = '%' . $search_term . '%';
        $stmt = $pdo->prepare("
            SELECT u.user_id, u.fname, u.lname, u.email, u.skills, s.title AS service_title
            FROM users u
            LEFT JOIN services s ON u.service_id = s.service_id
            WHERE (u.usertype = 'worker' AND (
                u.fname LIKE :search OR
                u.lname LIKE :search OR
                u.skills LIKE :search
            )) OR (s.title LIKE :search)
            ORDER BY u.fname ASC
            LIMIT 20
        ");
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}


/**
 * Sets a flash message in the session
 * @param string $type    Message type (e.g., 'error', 'success')
 * @param string $message The message content
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_messages'][$type] = $message;
}

/**
 * Gets a flash message and removes it from the session
 * @param string $type Message type (e.g., 'error', 'success')
 * @return string|null The message content or null if not set
 */
function get_flash_message($type) {
    if (isset($_SESSION['flash_messages'][$type])) {
        $message = $_SESSION['flash_messages'][$type];
        unset($_SESSION['flash_messages'][$type]);
        return $message;
    }
    return null;
}

/**
 * Checks if a flash message exists
 * @param string $type Message type
 * @return bool
 */
function has_flash_message($type) {
    return !empty($_SESSION['flash_messages'][$type]);
}
?>
