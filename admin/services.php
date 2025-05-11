<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Include services data
require_once 'includes/services_data.php';

// Check for success or error messages
$popup_message = '';
if (isset($_SESSION['error_message'])) {
    $popup_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $popup_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Services</div>
                <div class="number"><?php echo number_format($total_services); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Up from yesterday</span>
                </div>
            </div>
            <i class="bx bxs-wrench cart three"></i> <!-- Wrench icon -->
        </div>
    </div>

    <div class="sales-boxes">
        <div class="recent-sales box">
            <div class="title">Add Service</div>
            <div class="sales-details">
                <form method="POST" action="includes/add_services.php" enctype="multipart/form-data" class="service-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="field">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="field">
                        <label for="desc">Description</label>
                        <input type="text" id="desc" name="desc" required>
                    </div>
                    <div class="field">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" required class="upload">
                    </div>
                    <div id="popup" class="popup">
                        <?php if (!empty($popup_message)): ?>
                            <?php echo h($popup_message); ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn">Add</button>
                </form>
            </div>
        </div>

        <div class="top-sales box">
            <div class="title">All Services</div>
            <ul class="top-sales-details">
                <?php if (empty($serv)): ?>
                    <li class="no-data">No services available</li>
                <?php else: ?>
                    <?php foreach ($serv as $service): ?>
                        <li style="margin-bottom: 30px;">
                            <a href="#">
                                <img src="../public/images/<?php echo h($service['images']); ?>" alt="<?php echo h($service['title']); ?>">
                            </a>
                            <div class="service-info">
                                <span class="product" style="color:#f97f4b"><?php echo h($service['title']); ?></span>
                                <br>
                                <span class="details" style="font-size: 15px;font-style: italic;color:#3a3a3ae1"><?php echo h($service['description']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div class="button">
                <a href="all_services.php">See All</a>
            </div>
        </div>
    </div>
</div>

<style>
    .service-form {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Center the form content */
    }

    .service-form .field {
        margin-bottom: 15px;
        width: 100%;
        /* Keep fields full width */
    }

    .service-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .service-form input[type="text"],
    .service-form input[type="file"] {
        width: 750px;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    .service-form .upload {
        padding: 3px;
    }



    .service-form .btn {
        padding: 10px 20px;
        background-color: #ff6c40e4;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        justify-content: center;
    }


    .service-form .btn:hover {
        background-color: #ff6a3c;
    }

    .popup {
        color: var(--success);
        margin-top: 10px;
    }

    .popup.show {
        display: block;
    }
</style>

<script>
    // Display the popup if it has content
    window.onload = function() {
        var popup = document.getElementById('popup');
        if (popup.innerHTML.trim() !== '') {
            popup.classList.add('show');
            setTimeout(function() {
                popup.classList.remove('show');
            }, 3000); // Hide after 3 seconds
        }
    };
</script>

<?php include_once 'includes/footer.php'; ?>