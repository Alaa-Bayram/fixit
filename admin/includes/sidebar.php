<?php
$servicePages = ['services.php', 'all_services.php', 'edit_service.php', 'delete_service.php'];
$isServicesActive = in_array($currentPage, $servicePages);
?>

<div class="sidebar">
    <div class="logo-details">
        <img src="assets/images/logo1.png" class="logo" alt="FixIt Logo">
    </div>
    <ul class="nav-links">
        <li>
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="bx bx-grid-alt"></i>
                <span class="links_name">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="workers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'workers.php' ? 'active' : ''; ?>">
                <i class="bx bx-user-plus"></i>
                <span class="links_name">Workers Requests</span>
            </a>
        </li>
        <li>
            <a href="view_approved_workers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_approved_workers.php' ? 'active' : ''; ?>">
                <i class="bx bx-user-check"></i>
                <span class="links_name">FixIt App Workers </span>
            </a>
        </li>
        <li>
            <a href="services.php" class="<?php echo $isServicesActive ? 'active' : ''; ?>">
                <i class="bx bx-wrench"></i>
                <span class="links_name">Services</span>
            </a>
        </li>
        <li>
            <a href="Articles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="bx bx-book-content"></i>
                <span class="links_name">Articles</span>
            </a>
        </li>
        <li>
            <a href=".php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bx bx-bulb"></i>
                <span class="links_name">Tips</span>
            </a>
        </li>
        <li>
            <a href=".php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bx bx-message"></i>
                <span class="links_name">Client Reviews</span>
            </a>
        </li>
        <!-- <li class="log_out">
            <a href="logout.php">
                <i class="bx bx-log-out"></i>
                <span class="links_name">Log out</span>
            </a>
        </li> -->
    </ul>
</div>