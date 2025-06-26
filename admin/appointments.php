<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Get all appointments function
function get_all_appointments($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT a.date, a.time, CONCAT(u.fname, ' ', u.lname) as worker, a.status, a.cost, a.is_done, u.skills
            FROM appointment a
            LEFT JOIN users u ON a.worker_id = u.user_id
            ORDER BY a.request_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return [];
    }
}

// Fetch all appointments
$all_appointments = get_all_appointments($pdo);

// Calculate stats
$total_appointments = count($all_appointments);
$completed = array_filter($all_appointments, function ($apt) {
    return $apt['is_done'] == 1;
});
$in_progress = array_filter($all_appointments, function ($apt) {
    return $apt['status'] == 'accepted' && $apt['is_done'] != 1;
});
$pending = array_filter($all_appointments, function ($apt) {
    return $apt['status'] == 'pending';
});
$cancelled = array_filter($all_appointments, function ($apt) {
    return $apt['status'] == 'cancelled';
});
$total_revenue = array_reduce($completed, function ($carry, $item) {
    return $carry + $item['cost'];
}, 0);

// Include header
include_once 'includes/header.php';
?>

<style>
    /* Compact Overview Boxes Styling - Same as index.php */
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
        color: #28a745;
    }

    .overview-boxes .cart.three {
        color: #ffc107;
    }

    .overview-boxes .cart.four {
        color: #17a2b8;
    }

    /* Table Section Styles */
    .sales-boxes {
        display: grid;
        gap: 20px;
    }

    .recent-sales {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    }

    .recent-sales.box {
        display: flex;
        flex-direction: column;
        padding: 20px;
    }

    .title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .sales-details {
        width: 100% !important;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        padding: 12px 15px;
        text-align: left;
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #dee2e6;
    }

    .table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        color: #555;
    }

    /* Status badges */
    .badge {
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .status-pending {
        background: rgba(243, 156, 18, 0.1);
        color: var(--warning);
    }

    .status-done {
        background: rgba(46, 204, 113, 0.1);
        color: var(--success);
    }

    .status-in-progress {
        background: rgba(52, 152, 219, 0.1);
        color: var(--secondary);
    }

    .status-cancelled {
        background: rgba(231, 76, 60, 0.1);
        color: var(--danger);
    }

    /* Button styles */
    .button {
        margin-top: 20px;
        text-align: right;
        width: 100%;
    }

    .button a {
        display: inline-block;
        padding: 8px 16px;
        background: #ff6c40e4;
        color: white;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .button a:hover {
        background: #ff6a3c;
        transform: translateY(-2px);
    }

    .text-center {
        text-align: center;
    }

    /* RTL Support for Arabic */
    <?php if ($lang === 'ar'): ?>
    body {
        direction: rtl;
        text-align: right;
    }
    
    .overview-boxes .box {
        flex-direction: row-reverse;
    }
    
    .overview-boxes .cart {
        margin-left: 0;
        margin-right: 10px;
    }
    
    .overview-boxes .indicator i {
        margin-right: 0;
        margin-left: 4px;
    }
    
    .table th,
    .table td {
        text-align: right;
    }
    
    .button {
        text-align: left;
    }
    <?php endif; ?>

    /* Responsive Design - Same as index.php */
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

        .table th,
        .table td {
            padding: 8px 10px;
            font-size: 14px;
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
    .home-section {
    position: relative;
    width: calc(100% - 240px);
    left: 240px;
    min-height: 100vh;
    transition: all 0.5s ease;
}

[dir="rtl"] .home-section {
    left: 0;
    right: 240px;
}
<?php endif; ?>
</style>

<div class="home-content">
    <!-- Overview Boxes for Appointments Stats -->
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['total_appointments']; ?></div>
                <div class="number"><?php echo number_format($total_appointments); ?></div>
                <div class="indicator">
                    <i class="bx bx-calendar"></i>
                    <span class="text"><?php echo $trans['all_appointments']; ?></span>
                </div>
            </div>
            <i class="bx bxs-calendar cart"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['completed']; ?></div>
                <div class="number"><?php echo number_format(count($completed)); ?></div>
                <div class="indicator">
                    <i class="bx bx-check-circle"></i>
                    <span class="text"><?php echo $trans['finished_jobs']; ?></span>
                </div>
            </div>
            <i class="bx bxs-check-circle cart two"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['pending']; ?></div>
                <div class="number"><?php echo number_format(count($pending)); ?></div>
                <div class="indicator">
                    <i class="bx bx-time-five"></i>
                    <span class="text"><?php echo $trans['waiting_jobs']; ?></span>
                </div>
            </div>
            <i class="bx bxs-time-five cart three"></i>
        </div>

        <div class="box">
            <div class="right-side">
                <div class="box-topic"><?php echo $trans['revenue']; ?></div>
                <div class="number">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="indicator">
                    <i class="bx bx-dollar-circle"></i>
                    <span class="text"><?php echo $trans['from_completed_jobs']; ?></span>
                </div>
            </div>
            <i class="bx bx-dollar-circle cart four"></i>
        </div>
    </div>

    <!-- Full-width Table Section -->
    <div class="sales-boxes" style="grid-template-columns: 1fr;">
        <div class="recent-sales box">
            <div class="sales-details">
                <div class="title"><?php echo $trans['all_worker_appointments']; ?></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $trans['date']; ?></th>
                                <th><?php echo $trans['time']; ?></th>
                                <th><?php echo $trans['worker']; ?></th>
                                <th><?php echo $trans['status']; ?></th>
                                <th><?php echo $trans['service']; ?></th>
                                <th><?php echo $trans['cost']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_appointments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php echo $trans['no_appointments']; ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo date("d M Y", strtotime(h($appointment['date']))); ?></td>
                                        <td><?php echo date("h:i A", strtotime(h($appointment['time']))); ?></td>
                                        <td><?php echo h($appointment['worker'] ?? 'N/A'); ?></td>
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
                                            } elseif ($appointment['status'] == 'cancelled') {
                                                $status_class = 'status-cancelled';
                                                $status_text = $trans['cancelled'];
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td><?php echo h($appointment['skills'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format((float)$appointment['cost'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="button">
                <a href="index.php?lang=<?php echo $lang; ?>"><?php echo $trans['back']; ?></a>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>