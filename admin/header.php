<?php

include_once "php/config.php";

// Fetch admin details for the profile display
$query = "SELECT * FROM users WHERE usertype='admin'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}

?>
<nav>
    <div class="sidebar-button">
        <i class="bx bx-menu sidebarBtn"></i>
        <span class="dashboard">Dashboard</span>
    </div>
    <form method="GET" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
        <div class="search-box">
            <input type="text" name="search" placeholder="Search for workers or service" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
            <button type="submit"><i class="bx bx-search"></i></button>
        </div>
    </form>
    <?php
    // Check if there are admin details to display
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result); // Fetch first row (assuming there's only one admin)
    ?>
    <div class="profile-details">
        <img src="images/profile1.jpg" alt="Profile Image" />
        <span class="admin_name"><?php echo htmlspecialchars($row['fname']) . ' ' . htmlspecialchars($row['lname']); ?></span>
    </div>
    <?php } ?>
</nav>
