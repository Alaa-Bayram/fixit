<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Get admin details
$admin = get_admin_details($pdo);

// Process search if submitted
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = sanitize_input($_GET['search']);
    $search_results = search_workers_services($pdo, $search_term);
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <!-- Add CSRF token meta tag -->
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
</head>
<body>
    <?php include_once "includes/sidebar.php"; ?>
    
    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Dashboard</span>
            </div>
            
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-form">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search for workers or service" 
                           value="<?php echo isset($_GET['search']) ? h($_GET['search']) : ''; ?>" />
                    <button type="submit"><i class="bx bx-search"></i></button>
                </div>
            </form>
            
            <?php if ($admin): ?>
            <div class="profile-details">
                <img src="images/<?php echo h($admin['profile_image'] ?? 'default-profile.jpg'); ?>" alt="Profile" />
                <span class="admin_name"><?php echo h($admin['fname'] . ' ' . $admin['lname']); ?></span>
                <i class="bx bx-chevron-down"></i>
                <div class="dropdown-menu">
                    <a href="profile.php"><i class="bx bx-user"></i> Profile</a>
                    <a href="settings.php"><i class="bx bx-cog"></i> Settings</a>
                    <a href="logout.php"><i class="bx bx-log-out"></i> Logout</a>
                </div>
            </div>
            <?php endif; ?>
        </nav>

        <?php if (!empty($search_results)): ?>
        <div class="search-results">
            <h3>Search Results for: <?php echo h($_GET['search']); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Skills</th>
                        <th>Service</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_results as $result): ?>
                    <tr>
                        <td><?php echo h($result['fname'] . ' ' . $result['lname']); ?></td>
                        <td><?php echo h($result['skills'] ?? 'N/A'); ?></td>
                        <td><?php echo h($result['service_title'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="worker_detail.php?id=<?php echo h($result['user_id']); ?>" class="btn btn-view">
                                <i class="bx bx-show"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
