<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', $trans['please_login']);
}

// Fetch dashboard data
$total_workers = get_total_workers($pdo);
$total_clients = get_total_clients($pdo);
$total_profits = get_total_profits($pdo);
$total_services = get_total_services($pdo);
$top_services = get_top_services($pdo);
$recent_appointments = get_recent_appointments($pdo);

include_once 'includes/header.php';
?>

<style>
    /* RTL Support for Arabic */
    [dir="rtl"] {
        text-align: right;
    }
    
    [dir="rtl"] .overview-boxes .box {
        flex-direction: row-reverse;
    }
    
    [dir="rtl"] .overview-boxes .cart {
        margin-left: 0;
        margin-right: 10px;
    }
    
    [dir="rtl"] .overview-boxes .indicator i {
        margin-right: 0;
        margin-left: 4px;
    }
    
    [dir="rtl"] table {
        direction: rtl;
    }
    
    [dir="rtl"] .sales-details table th,
    [dir="rtl"] .sales-details table td {
        text-align: right;
    }

    /* Compact Overview Boxes Styling - Only affects overview-boxes section */
    .overview-boxes {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .overview-boxes .box {
        background: #fff;
        padding: 18px;
        border-radius: 12px;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 100px;
    }

    .overview-boxes .box .right-side {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .overview-boxes .box-topic {
        font-size: 16px;
        font-weight: 500;
        color: #3C3C3C;
        margin-bottom: 5px;
    }

    .overview-boxes .number {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .overview-boxes .indicator {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #666;
    }

    .overview-boxes .indicator i {
        font-size: 16px;
        margin-right: 4px;
    }

    .overview-boxes .cart {
        font-size: 28px;
        color: #11101d;
        margin-left: 10px;
    }

    .overview-boxes .cart.two {
        color: #2D8BBA;
    }

    .overview-boxes .cart.three {
        color: #ffc107;
    }

    .overview-boxes .cart.four {
        color: #27AE60;
    }

    /* Responsive Design - Only for overview-boxes */
    @media (max-width: 1200px) {
        .overview-boxes {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .overview-boxes .box {
            padding: 15px;
        }

        .overview-boxes .number {
            font-size: 20px;
        }

        .overview-boxes .box-topic {
            font-size: 14px;
        }
    }

    @media (max-width: 768px) {
        .overview-boxes {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .overview-boxes .box {
            padding: 12px;
            min-height: 80px;
        }

        .overview-boxes .number {
            font-size: 18px;
        }

        .overview-boxes .box-topic {
            font-size: 13px;
        }

        .overview-boxes .cart {
            font-size: 24px;
        }
    }

    @media (max-width: 480px) {
        .overview-boxes {
            grid-template-columns: 1fr;
        }
    }
    <?php if ($lang === 'ar'): ?>
/* Arabic RTL Styling - Properly Fixed */
body {
    direction: rtl;
    text-align: right;
    font-family: 'Tajawal', 'Arial', sans-serif;
}

/* Sidebar Positioning - ON THE RIGHT SIDE */
.sidebar {
    left: auto;
    right: 0;
    transition: all 0.5s ease;
}

/* Main Content Area - positioned from the right */
.home-section {
    position: relative;
    left: auto;
    right: 260px;
    width: calc(100% - 260px);
    transition: all 0.5s ease;
}

/* When Sidebar is Closed/Minimized */
.sidebar.close {
    width: 78px;
}

.sidebar.close ~ .home-section {
    right: 78px;
    left: auto;
    width: calc(100% - 78px);
}

/* Mobile view when sidebar is toggled */
@media (max-width: 1090px) {
    .sidebar {
        right: -260px;
        left: auto;
    }
    
    .sidebar.active {
        right: 0;
    }
    
    .home-section {
        right: 0;
        left: auto;
        width: 100%;
    }
    
    .sidebar.active ~ .home-section {
        right: 260px;
        left: auto;
        width: calc(100% - 260px);
    }
    
    .sidebar.close.active ~ .home-section {
        right: 78px;
        left: auto;
        width: calc(100% - 78px);
    }
}

/* Fix service items layout */
.top-sales-details li {
    padding: 10px 0 10px 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    flex-direction: row-reverse;
}

/* Service info alignment */
.service-info {
    margin-left: 15px;
    margin-right: 0;
    text-align: right;
    flex: 1;
}

/* Form field alignment */
.service-form .field {
    text-align: right;
}

/* Input fields styling */
.service-form input[type="text"] {
    text-align: right;
    padding: 12px 15px;
}

/* Fix file upload label alignment */
.file-upload-wrapper {
    direction: rtl;
}

.file-upload-label {
    justify-content: center;
    flex-direction: row-reverse;
}

.file-upload-label i {
    margin-right: 0;
    margin-left: 8px;
}

/* Box layout adjustments */
.box {
    text-align: right;
}

.box-topic, .number, .indicator {
    text-align: right;
}

/* Icon positioning in boxes - move to left side */
.box i {
    left: 15px;
    right: auto;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .top-sales-details li {
        flex-direction: column;
        align-items: flex-end;
    }
    
    .service-info {
        margin-left: 0;
        margin-top: 10px;
        text-align: right;
    }
}

@media (max-width: 480px) {
    .sidebar {
        right: -100%;
        left: auto;
    }
    
    .sidebar.active {
        right: 0;
        width: 260px;
    }
    
    .sidebar.close.active {
        width: 78px;
    }
    
    .home-section {
        right: 0;
        left: auto;
        width: 100%;
    }
    
    .sidebar.active ~ .home-section {
        right: 260px;
        left: auto;
        width: calc(100% - 260px);
    }
    
    .sidebar.close.active ~ .home-section {
        right: 78px;
        left: auto;
        width: calc(100% - 78px);
    }
}

/* Additional RTL layout fixes */
.sales-boxes, .overview-boxes {
    direction: rtl;
}

/* File upload specific fixes */
.file-upload-name, .form-text, .error-message {
    text-align: right;
}

/* Dropdown menus */
.dropdown-menu {
    left: auto;
    right: 0;
}

/* Service thumbnail container */
.top-sales-details li a {
    order: 2;
}

.service-info {
    order: 1;
}
<?php endif; ?>
</style>

<div class="home-content" dir="<?php echo $lang == 'ar' ? 'rtl' : 'ltr'; ?>">
    <!-- Overview Boxes -->
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['total_workers']; ?></div>
                <div class="number"><?php echo number_format($total_workers); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text"><?php echo $trans['up_from_yesterday']; ?></span>
                </div>
            </div>
            <i class="bx bxs-user-circle cart"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['total_clients']; ?></div>
                <div class="number"><?php echo number_format($total_clients); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text"><?php echo $trans['up_from_yesterday']; ?></span>
                </div>
            </div>
            <i class="bx bxs-user cart four"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['total_profits']; ?></div>
                <div class="number">$<?php echo number_format($total_profits, 2); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text"><?php echo $trans['up_from_yesterday']; ?></span>
                </div>
            </div>
            <i class="bx bx-dollar-circle cart two"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['total_services']; ?></div>
                <div class="number"><?php echo number_format($total_services); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text"><?php echo $trans['up_from_yesterday']; ?></span>
                </div>
            </div>
            <i class="bx bxs-wrench cart three"></i>
        </div>
    </div>

    <!-- Sales Boxes -->
    <div class="sales-boxes">
        <!-- Recent Appointments Box -->
        <div class="recent-sales box">
            <div class="title"><?php echo $trans['recent_appointments']; ?></div>
            <div class="sales-details">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo $trans['date']; ?></th>
                            <th><?php echo $trans['time']; ?></th>
                            <th><?php echo $trans['worker']; ?></th>
                            <th><?php echo $trans['service']; ?></th>
                            <th><?php echo $trans['cost']; ?></th>
                            <th><?php echo $trans['status']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_appointments)): ?>
                            <tr>
                                <td colspan="6" class="text-center"><?php echo $trans['no_appointments']; ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo date("d M Y", strtotime(h($appointment['date']))); ?></td>
                                    <td><?php echo date("h:i A", strtotime(h($appointment['time']))); ?></td>
                                    <td><?php echo h($appointment['worker']); ?></td>
                                    <td><?php echo h($appointment['skills']); ?></td>
                                    <td>$<?php echo number_format((float)$appointment['cost'], 2); ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'status-pending';
                                        $status_text = $trans['pending'];

                                        if ($appointment['is_done'] == 1) {
                                            $status_class = 'status-done';
                                            $status_text = $trans['done'];
                                        } elseif ($appointment['status'] == 'accepted') {
                                            $status_class = 'status-in-progress';
                                            $status_text = $trans['in_progress'];
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="button">
                <a href="appointments.php?lang=<?php echo $lang; ?>"><?php echo $trans['see_all']; ?></a>

            </div>
        </div>

        <!-- Top Services Box -->
        <div class="top-sales box">
            <div class="title"><?php echo $trans['most_requested_services']; ?></div>
            <ul class="top-sales-details">
                <?php if (empty($top_services)): ?>
                    <li class="no-data"><?php echo $trans['no_services']; ?></li>
                <?php else: ?>
                    <?php foreach ($top_services as $service): ?>
                        <li>
                            <a href="#">
                                <img src="../public/images/<?php echo h($service['images']); ?>" alt="<?php echo h($service['title']); ?>">
                                <span class="product"><?php echo h($service['title']); ?></span>
                            </a>
                            <span class="price"><?php echo h($service['appointment_count']); ?> <?php echo $trans['requests']; ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>