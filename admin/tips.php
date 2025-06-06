<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Include tips data
require_once 'includes/tips_data.php';

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
    <!-- Overview Boxes -->
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic">Daily Tips</div>
                <div class="number"><?php echo number_format(count($daily_tips ?? [])); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Active tips</span>
                </div>
            </div>
            <i class="bx bx-bulb cart one"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic">Seasonal Tips</div>
                <div class="number"><?php echo number_format(count($seasonal_tips ?? [])); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Active tips</span>
                </div>
            </div>
            <i class="bx bx-calendar cart two"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Tips</div>
                <div class="number"><?php echo number_format(count($daily_tips ?? []) + count($seasonal_tips ?? [])); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">All categories</span>
                </div>
            </div>
            <i class="bx bx-book cart three"></i>
        </div>
    </div>

    <!-- Main Content Boxes -->
    <div class="sales-boxes">
        <!-- Forms Container - Equal width for both forms in one row -->
        <div class="forms-container">
            <!-- Add Daily Tip Form -->
            <div class="recent-sales box form-box">
                <div class="title">Add Daily Tip</div>
                <div class="sales-details">
                    <form id="dailyTipForm" method="POST" action="includes/add_dailyTip.php" enctype="multipart/form-data" class="tip-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                        <div class="field">
                            <label for="daily_title">Title</label>
                            <input type="text" id="daily_title" name="title" required maxlength="100" placeholder="Enter tip title">
                            <div class="error-message" id="daily_title_error"></div>
                        </div>

                        <div class="field">
                            <label for="daily_desc">Description</label>
                            <textarea id="daily_desc" name="description" required maxlength="500" rows="3" placeholder="Enter tip description"></textarea>
                            <div class="error-message" id="daily_desc_error"></div>
                        </div>

                        <div class="field">
                            <label for="daily_image">Image</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="daily_image" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" required class="upload">
                                <label for="daily_image" class="file-upload-label">
                                    <i class="bx bx-cloud-upload"></i>
                                    <span>Choose a file</span>
                                </label>
                                <div class="file-upload-name" id="daily_file_name">No file chosen</div>
                            </div>
                            <small class="form-text">Max size: 2MB | Formats: JPG, PNG, GIF</small>
                            <div class="error-message" id="daily_image_error"></div>
                        </div>

                        <input type="hidden" name="type" value="daily tips">

                        <div id="popup_daily" class="popup">
                            <?php if (!empty($popup_message)): ?>
                                <?php echo h($popup_message); ?>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-daily">
                            <span class="btn-text">Add Daily Tip</span>
                            <span class="btn-loader" style="display:none;">
                                <i class="bx bx-loader bx-spin"></i>
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Add Seasonal Tip Form -->
            <div class="recent-sales box form-box">
                <div class="title">Add Seasonal Tip</div>
                <div class="sales-details">
                    <form id="seasonalTipForm" method="POST" action="includes/add_seasonalTip.php" enctype="multipart/form-data" class="tip-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                        <div class="field">
                            <label for="seasonal_title">Title</label>
                            <input type="text" id="seasonal_title" name="title" required maxlength="100" placeholder="Enter seasonal tip title">
                            <div class="error-message" id="seasonal_title_error"></div>
                        </div>

                        <div class="field">
                            <label for="seasonal_desc">Description</label>
                            <textarea id="seasonal_desc" name="desc" required maxlength="500" rows="3" placeholder="Enter seasonal tip description"></textarea>
                            <div class="error-message" id="seasonal_desc_error"></div>
                        </div>

                        <div class="field">
                            <label for="tip1">First Tip</label>
                            <input type="text" id="tip1" name="tip1" required maxlength="255" placeholder="Enter first seasonal tip">
                            <div class="error-message" id="tip1_error"></div>
                        </div>

                        <div class="field">
                            <label for="tip2">Second Tip</label>
                            <input type="text" id="tip2" name="tip2" required maxlength="255" placeholder="Enter second seasonal tip">
                            <div class="error-message" id="tip2_error"></div>
                        </div>

                        <input type="hidden" name="type" value="seasonal tips">

                        <div id="popup_seasonal" class="popup">
                            <?php if (!empty($popup_message)): ?>
                                <?php echo h($popup_message); ?>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-seasonal">
                            <span class="btn-text">Add Seasonal Tip</span>
                            <span class="btn-loader" style="display:none;">
                                <i class="bx bx-loader bx-spin"></i>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Tips Display - Full width table-like layout in separate row -->
        <div class="top-sales box recent-tips-container">
            <div class="title">Recent Tips</div>

            <div class="tips-tabs">
                <button class="tab-button active" data-tab="daily-tips">Daily Tips</button>
                <button class="tab-button" data-tab="seasonal-tips">Seasonal Tips</button>
                <div class="search-box">
                    <input type="text" id="tips-search" placeholder="Search tips...">
                    <i class="bx bx-search"></i>
                </div>
            </div>

            <div id="daily-tips" class="tab-content active">
                <div class="tips-table">
                    <div class="tips-table-header">
                        <div class="header-item">Image</div>
                        <div class="header-item">Title</div>
                        <div class="header-item">Description</div>

                    </div>

                    <div class="tips-table-body" id="daily-tips-body">
                        <?php if (!empty($daily_tips)): ?>
                            <?php foreach (array_slice($daily_tips, 0, 5) as $tip): ?>
                                <div class="tips-table-row">
                                    <div class="tips-table-cell">
                                        <?php if (!empty($tip['images']) || !empty($tip['image'])): ?>
                                            <img src="../public/images/tips/<?php echo h($tip['images'] ?? $tip['image']); ?>"
                                                alt="<?php echo h($tip['title']); ?>"
                                                class="tip-thumbnail"
                                                onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <span class="no-image">No Image</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tips-table-cell"><?php echo h($tip['title']); ?></div>
                                    <div class="tips-table-cell"><?php echo h($tip['description']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="tips-table-row no-tips">
                                <div class="tips-table-cell" colspan="4">No daily tips available</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="seasonal-tips" class="tab-content">
                <div class="tips-table">
                    <div class="tips-table-header">

                        <div class="header-item">Title</div>
                        <div class="header-item">Tip 1</div>
                        <div class="header-item">Tip 2</div>
                        <div class="header-item"></div>
                    </div>

                    <div class="tips-table-body" id="seasonal-tips-body">
                        <?php if (!empty($seasonal_tips)): ?>
                            <?php foreach (array_slice($seasonal_tips, 0, 5) as $tip): ?>
                                <div class="tips-table-row">

                                    <div class="tips-table-cell"><?php echo h($tip['title']); ?></div>
                                    <div class="tips-table-cell">
                                        <div class="seasonal-tip-item">• <?php echo h($tip['f_tip']); ?></div>

                                    </div>
                                    <div class="tips-table-cell">
                                        <div class="seasonal-tip-item">• <?php echo h($tip['s_tip']); ?></div>
                                    </div>
                                    
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="tips-table-row no-tips">
                                <div class="tips-table-cell" colspan="4">No seasonal tips available</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="button">
                <a href="all_tips.php">See All Tips</a>
            </div>
        </div>
    </div>
</div>

<style>


    /* Main sales-boxes container */
    .sales-boxes {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Forms container styling - Forms in one row */
    .forms-container {
        display: flex;
        gap: 20px;
        width: 100%;
    }

    .form-box {
        flex: 1;
        min-width: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }

    .form-box:hover {
        transform: translateY(-3px);
    }

    /* Form styling */
    .tip-form {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 20px 20px;
    }

    .tip-form .field {
        margin-bottom: 15px;
        width: 100%;
        max-width: 750px;
    }

    .tip-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #444;
        font-size: 14px;
    }

    .tip-form input[type="text"],
    .tip-form textarea {
        width: 100%;
        padding: 12px 15px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-sizing: border-box;
        font-family: inherit;
        font-size: 14px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .tip-form textarea {
        resize: vertical;
        min-height: 100px;
    }

    .file-upload-wrapper {
        position: relative;
        margin-top: 5px;
    }

    .file-upload-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 15px;
        background: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .file-upload-label:hover {
        background: #eee;
        border-color: #999;
    }

    .file-upload-label i {
        margin-right: 8px;
        font-size: 18px;
        color: #666;
    }

    .file-upload-label span {
        color: #666;
    }

    .file-upload-name {
        margin-top: 5px;
        font-size: 13px;
        color: #666;
        text-align: center;
    }

    .upload {
        position: absolute;
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        z-index: -1;
    }

    .tip-form small.form-text {
        color: #888;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    /* Button styling */
    .btn {
        padding: 12px 24px;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: 15px;
        min-width: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .btn-daily {
        background-color: #79bcb1;
    }

    .btn-daily:hover {
        background-color: rgb(68, 147, 134);
    }

    .btn-seasonal {
        background-color: #e67e22;
    }

    .btn-seasonal:hover {
        background-color: #d35400;
    }

    .btn-loader {
        margin-left: 8px;
    }

    /* Popup styling */
    .popup {
        color: var(--success, #27ae60);
        margin-top: 10px;
        padding: 12px 15px;
        border-radius: 8px;
        display: none;
        background-color: rgba(39, 174, 96, 0.1);
        border-left: 4px solid var(--success, #27ae60);
        width: 100%;
        text-align: center;
        animation: fadeInOut 3s ease-in-out;
    }

    /* Recent tips container - Full width in separate row */
    .recent-tips-container {
        width: 100%;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        padding: 20px;
        transition: transform 0.3s ease;
    }

    .recent-tips-container:hover {
        transform: translateY(-3px);
    }

    /* Tabs styling - FIXED */
    .tips-tabs {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        position: relative;
    }

    .tab-button {
        padding: 10px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: #666;
        position: relative;
        transition: all 0.3s ease;
        margin-right: 5px;
    }

    .tab-button:hover {
        color: #f97f4b;
    }

    .tab-button.active {
        color: #f97f4b;
        font-weight: 600;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #f97f4b;
    }

    .search-box {
        margin-left: auto;
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-box input {
        padding: 8px 15px 8px 35px;
        border: 1px solid #ddd;
        border-radius: 20px;
        font-size: 13px;
        transition: all 0.3s;
        width: 200px;
    }

    .search-box input:focus {
        border-color: #f97f4b;
        box-shadow: 0 0 0 2px rgba(249, 127, 75, 0.2);
        outline: none;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        color: #999;
    }

    /* Tab content styling */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Table-like styling */
    .tips-table {
        display: flex;
        flex-direction: column;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
    }

    .tips-table-header {
        display: flex;
        background-color: #f9f9f9;
        font-weight: 600;
        color: #555;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tips-table-body {
        max-height: 400px;
        overflow-y: auto;
    }

    .tips-table-row {
        display: flex;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }

    .tips-table-row:last-child {
        border-bottom: none;
    }

    .tips-table-row:hover {
        background-color: #f9f9f9;
    }

    .tips-table-header .header-item,
    .tips-table-cell {
        flex: 1;
        padding: 15px;
        display: flex;
        align-items: center;
    }

    .tips-table-cell {
        word-break: break-word;
        font-size: 14px;
        color: #555;
    }

    /* Specific column widths */
    .tips-table-header .header-item:nth-child(1),
    .tips-table-cell:nth-child(1) {
        flex: 0 0 100px;
        justify-content: center;
    }

    .tips-table-header .header-item:nth-child(2),
    .tips-table-cell:nth-child(2) {
        flex: 0 0 200px;
    }

    .tips-table-header .header-item:nth-child(4),
    .tips-table-cell:nth-child(4) {
        flex: 0 0 120px;
        justify-content: center;
    }

    /* Image styling */
    .tip-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .no-image {
        color: #999;
        font-size: 12px;
        font-style: italic;
    }

    /* Seasonal tips items */
    .seasonal-tip-item {
        font-size: 13px;
        margin-bottom: 5px;
        color: #555;
        line-height: 1.4;
    }

    /* Action buttons */
    .action-btn {
        padding: 8px;
        margin: 0 3px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }

    .edit-btn {
        background-color: rgb(194, 237, 230);
        color: rgb(10, 61, 53);
    }

    .edit-btn:hover {
        background-color: rgb(143, 200, 190);
    }

    .delete-btn {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }

    .delete-btn:hover {
        background-color: rgba(231, 76, 60, 0.2);
    }

    /* No tips message */
    .no-tips {
        justify-content: center;
        color: #666;
        font-style: italic;
        padding: 30px;
        text-align: center;
    }

    /* See all button */
    .button {
        text-align: center;
        margin-top: 20px;
    }

    .button a {
        display: inline-block;
        padding: 10px 20px;
        background: #f97f4b;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
    }

    .button a:hover {
        background: #e76b3c;
        transform: translateY(-2px);
    }

    /* Animations */
    @keyframes fadeInOut {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }

        10% {
            opacity: 1;
            transform: translateY(0);
        }

        90% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsive adjustments */
/* Enhanced Responsive CSS - Add this to your existing styles */

/* Mobile First Approach - Base styles for mobile */
@media (max-width: 480px) {
    .home-content {
        padding: 10px;
    }

    /* Overview boxes - Stack vertically on very small screens */
    .overview-boxes {
        flex-direction: column;
        gap: 10px;
    }

    .overview-boxes .box {
        min-width: 100%;
        padding: 15px;
    }

    .overview-boxes .box .number {
        font-size: 1.5rem;
    }

    /* Forms container - Better mobile spacing */
    .forms-container {
        flex-direction: column;
        gap: 15px;
    }

    .form-box {
        margin: 0;
        padding: 15px;
    }

    .tip-form {
        padding: 0 10px 15px;
    }

    .tip-form .field {
        margin-bottom: 12px;
    }

    .tip-form input[type="text"],
    .tip-form textarea {
        padding: 10px 12px;
        font-size: 16px; /* Prevents zoom on iOS */
    }

    /* Button adjustments */
    .btn {
        padding: 12px 20px;
        font-size: 16px;
        width: 100%;
    }

    /* Recent tips container */
    .recent-tips-container {
        padding: 15px;
        margin: 10px 0;
        overflow-x: hidden;
    }

    /* Tabs for mobile */
    .tips-tabs {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .tab-button {
        padding: 12px 15px;
        font-size: 16px;
        border-radius: 6px;
        background: #f5f5f5;
        margin-right: 0;
        margin-bottom: 5px;
    }

    .tab-button.active {
        background: #f97f4b;
        color: white;
    }

    .search-box {
        margin: 10px 0 0 0;
    }

    .search-box input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        font-size: 16px;
    }

    /* Mobile table layout - Card style */
    .tips-table {
        border: none;
    }

    .tips-table-header {
        display: none;
    }

    .tips-table-row {
        flex-direction: column;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .tips-table-row:hover {
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .tips-table-cell {
        flex: 1 1 100% !important;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
        justify-content: flex-start;
        align-items: flex-start;
        flex-direction: column;
    }

    .tips-table-cell:last-child {
        border-bottom: none;
        flex-direction: row;
        justify-content: flex-start;
        padding-top: 15px;
    }

    .tips-table-cell::before {
        content: attr(data-label) ':';
        font-weight: 600;
        color: #666;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
        letter-spacing: 0.5px;
    }

    .tips-table-cell:last-child::before {
        content: 'Actions:';
        margin-bottom: 10px;
    }

    /* Image adjustments for mobile */
    .tip-thumbnail {
        width: 80px;
        height: 80px;
        margin-top: 5px;
    }

    .no-image {
        margin-top: 5px;
        display: block;
    }

    /* Action buttons for mobile */
    .action-btn {
        width: 35px;
        height: 35px;
        font-size: 16px;
        margin-right: 8px;
    }

    /* Seasonal tip items */
    .seasonal-tip-item {
        font-size: 14px;
        margin-top: 5px;
        padding: 5px 0;
        line-height: 1.5;
    }

    /* No tips message */
    .no-tips .tips-table-cell {
        text-align: center;
        padding: 30px 15px;
        font-size: 16px;
    }

    /* See all button */
    .button a {
        padding: 12px 24px;
        font-size: 16px;
        display: block;
        text-align: center;
    }
}

/* Tablet styles */
@media (min-width: 481px) and (max-width: 768px) {
    .home-content {
        padding: 15px;
    }

    .overview-boxes {
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .overview-boxes .box {
        flex: 0 0 calc(50% - 10px);
        margin-bottom: 15px;
    }

    .forms-container {
        flex-direction: column;
        gap: 20px;
    }

    .tips-table-header .header-item:nth-child(3),
    .tips-table-cell:nth-child(3) {
        display: none;
    }

    .search-box input {
        width: 250px;
    }
}

/* Small desktop styles */
@media (min-width: 769px) and (max-width: 1024px) {
    .forms-container {
        gap: 15px;
    }

    .tips-table-header .header-item:nth-child(3),
    .tips-table-cell:nth-child(3) {
        flex: 0 0 250px;
    }

    .search-box input {
        width: 200px;
    }
}

/* Large desktop styles */
@media (min-width: 1025px) {
    .home-content {
        max-width: 1400px;
        margin: 0 auto;
    }

    .forms-container {
        gap: 25px;
    }

    .recent-tips-container {
        padding: 25px;
    }
}

/* Landscape phone styles */
@media (max-width: 768px) and (orientation: landscape) {
    .overview-boxes {
        flex-direction: row;
        flex-wrap: wrap;
    }

    .overview-boxes .box {
        flex: 0 0 calc(33.333% - 10px);
        margin-bottom: 10px;
    }

    .forms-container {
        flex-direction: row;
    }

    .form-box {
        flex: 1;
    }
}

/* Fix for very small screens */
@media (max-width: 320px) {
    .home-content {
        padding: 8px;
    }

    .form-box {
        padding: 10px;
    }

    .tip-form {
        padding: 0 5px 10px;
    }

    .recent-tips-container {
        padding: 10px;
    }

    .tips-table-row {
        padding: 12px;
        margin-bottom: 12px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
}

/* Print styles */
@media print {
    .home-content {
        background: white !important;
    }

    .btn, .action-btn, .search-box {
        display: none !important;
    }

    .tips-table-row {
        break-inside: avoid;
        border: 1px solid #000;
        margin-bottom: 10px;
    }

    .tip-thumbnail {
        max-width: 50px;
        max-height: 50px;
    }
}
</style>

<script>
    // Display the popup if it has content
    document.addEventListener('DOMContentLoaded', function() {
        // Popup handling
        var popups = document.querySelectorAll('.popup');
        popups.forEach(function(popup) {
            if (popup.innerHTML.trim() !== '') {
                popup.style.display = 'block';
                setTimeout(function() {
                    popup.style.display = 'none';
                }, 3000);
            }
        });

        // File upload name display
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            input.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
                const fileDisplay = this.closest('.file-upload-wrapper').querySelector('.file-upload-name');
                fileDisplay.textContent = fileName;
            });
        });

        // Add data labels for responsive view
        const headers = document.querySelectorAll('.tips-table-header .header-item');

        headers.forEach((header, index) => {
            const label = header.textContent;
            document.querySelectorAll(`.tips-table-cell:nth-child(${index + 1})`).forEach(cell => {
                cell.setAttribute('data-label', label);
            });
        });

        // Initialize tab functionality
        initializeTabs();
    });

    // Tab functionality - Fixed
    function initializeTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                openTab(tabId, this);
            });
        });
    }

    function openTab(tabId, clickedButton) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Deactivate all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Show the selected tab content
        const targetTab = document.getElementById(tabId);
        if (targetTab) {
            targetTab.classList.add('active');
        }

        // Activate the clicked button
        if (clickedButton) {
            clickedButton.classList.add('active');
        }

        // Clear search when switching tabs
        const searchInput = document.getElementById('tips-search');
        if (searchInput) {
            searchInput.value = '';
            // Reset all rows visibility
            document.querySelectorAll('.tips-table-row:not(.no-tips)').forEach(row => {
                row.style.display = 'flex';
            });
        }
    }

    // Form validation
    function validateForm(formId) {
        const form = document.getElementById(formId);
        let isValid = true;

        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        // Validate title
        const title = form.querySelector('input[name="title"]');
        if (title.value.trim().length < 3) {
            const errorEl = form.querySelector(`#${formId.replace('Form', '')}_title_error`);
            errorEl.textContent = 'Title must be at least 3 characters long';
            errorEl.style.display = 'block';
            isValid = false;
        }

        // Validate description
        const desc = form.querySelector('textarea[name="desc"]');
        if (desc.value.trim().length < 10) {
            const errorEl = form.querySelector(`#${formId.replace('Form', '')}_desc_error`);
            errorEl.textContent = 'Description must be at least 10 characters long';
            errorEl.style.display = 'block';
            isValid = false;
        }

        // For seasonal form, validate tips
        if (formId === 'seasonalTipForm') {
            const tip1 = form.querySelector('input[name="tip1"]');
            const tip2 = form.querySelector('input[name="tip2"]');

            if (tip1.value.trim().length < 3) {
                const errorEl = form.querySelector('#tip1_error');
                errorEl.textContent = 'First tip must be at least 3 characters long';
                errorEl.style.display = 'block';
                isValid = false;
            }

            if (tip2.value.trim().length < 3) {
                const errorEl = form.querySelector('#tip2_error');
                errorEl.textContent = 'Second tip must be at least 3 characters long';
                errorEl.style.display = 'block';
                isValid = false;
            }
        }

        // Validate image for daily form
        if (formId === 'dailyTipForm') {
            const image = form.querySelector('input[name="image"]');
            if (image.files.length === 0) {
                const errorEl = form.querySelector('#daily_image_error');
                errorEl.textContent = 'Please select an image';
                errorEl.style.display = 'block';
                isValid = false;
            }
        }

        return isValid;
    }

    // Form submission with loading state
    document.addEventListener('DOMContentLoaded', function() {
        const dailyForm = document.getElementById('dailyTipForm');
        const seasonalForm = document.getElementById('seasonalTipForm');

        if (dailyForm) {
            dailyForm.addEventListener('submit', function(e) {
                if (!validateForm('dailyTipForm')) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                const btn = this.querySelector('.btn-daily');
                if (btn) {
                    const btnText = btn.querySelector('.btn-text');
                    const btnLoader = btn.querySelector('.btn-loader');
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoader) btnLoader.style.display = 'block';
                    btn.disabled = true;
                }
            });
        }

        if (seasonalForm) {
            seasonalForm.addEventListener('submit', function(e) {
                if (!validateForm('seasonalTipForm')) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                const btn = this.querySelector('.btn-seasonal');
                if (btn) {
                    const btnText = btn.querySelector('.btn-text');
                    const btnLoader = btn.querySelector('.btn-loader');
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoader) btnLoader.style.display = 'block';
                    btn.disabled = true;
                }
            });
        }
    });

    // File upload validation
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="file"]').forEach(function(input) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                const errorEl = this.closest('.field').querySelector('.error-message');

                if (file) {
                    // Check file size (2MB limit)
                    if (file.size > 2 * 1024 * 1024) {
                        errorEl.textContent = 'File size must be less than 2MB';
                        errorEl.style.display = 'block';
                        this.value = '';
                        return;
                    }

                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        errorEl.textContent = 'Please select a valid image file (JPG, PNG, or GIF)';
                        errorEl.style.display = 'block';
                        this.value = '';
                        return;
                    }

                    // Hide error if valid
                    errorEl.style.display = 'none';
                }
            });
        });
    });

    // Search functionality - Fixed
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('tips-search');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const activeTab = document.querySelector('.tab-content.active');

                if (activeTab) {
                    const rows = activeTab.querySelectorAll('.tips-table-row:not(.no-tips)');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = 'flex';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Show/hide no results message
                    const noTipsRow = activeTab.querySelector('.tips-table-row.no-tips');
                    if (visibleCount === 0 && rows.length > 0 && searchTerm.trim() !== '') {
                        // Create or show "no results" message
                        if (!activeTab.querySelector('.no-search-results')) {
                            const noResultsRow = document.createElement('div');
                            noResultsRow.className = 'tips-table-row no-search-results';
                            noResultsRow.innerHTML = '<div class="tips-table-cell" style="flex: 1; justify-content: center; padding: 30px; color: #666; font-style: italic;">No tips found matching your search</div>';
                            activeTab.querySelector('.tips-table-body').appendChild(noResultsRow);
                        } else {
                            activeTab.querySelector('.no-search-results').style.display = 'flex';
                        }
                    } else {
                        // Hide "no results" message
                        const noResultsRow = activeTab.querySelector('.no-search-results');
                        if (noResultsRow) {
                            noResultsRow.style.display = 'none';
                        }
                    }
                }
            });
        }
    });

    // Tip management functions - Enhanced
    function editTip(type, id) {
        if (!id || id === 0) {
            alert('Invalid tip ID');
            return;
        }

        // You can implement modal editing or redirect to edit page
        console.log(`Editing ${type} tip with ID: ${id}`);

        // Or show a confirmation
        if (confirm(`Do you want to edit this ${type} tip?`)) {
            // Implement your edit functionality here
            // This could open a modal, redirect to an edit page, etc.
            alert(`Edit functionality for ${type} tip ID: ${id} would be implemented here`);
        }
    }

    function deleteTip(type, id) {
        if (!id || id === 0) {
            alert('Invalid tip ID');
            return;
        }

        if (confirm(`Are you sure you want to delete this ${type} tip? This action cannot be undone.`)) {
            // Show loading state
            const deleteBtn = event.target.closest('.delete-btn');
            if (deleteBtn) {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i>';
            }

            // You can implement AJAX deletion here
            console.log(`Deleting ${type} tip with ID: ${id}`);

            // For now, just simulate deletion
            setTimeout(() => {
                if (deleteBtn) {
                    const row = deleteBtn.closest('.tips-table-row');
                    if (row) {
                        row.remove();
                        alert(`${type} tip deleted successfully`);
                    }
                }
            }, 1000);
        }
    }

    // Image error handling
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tip-thumbnail').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                // Create a placeholder
                const placeholder = document.createElement('span');
                placeholder.className = 'no-image';
                placeholder.textContent = 'No Image';
                this.parentNode.insertBefore(placeholder, this);
            });
        });
    });

    // Utility function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            color: white;
            border-radius: 5px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Add CSS for notification animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
</script>

<?php include_once 'includes/footer.php'; ?>