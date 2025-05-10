<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_admin_logged_in()) {
    redirect('login.php', 'Please log in to access the dashboard');
}

// Fetch dashboard data
$total_workers = get_total_workers($pdo);
$total_clients = get_total_clients($pdo);
$total_profits = get_total_profits($pdo);
$total_services = get_total_services($pdo);
$top_services = get_top_services($pdo);
$recent_appointments = get_recent_appointments($pdo);

// Include header
include_once 'includes/header.php';
?>

<div class="home-content">
    <!-- Overview Boxes -->
    <div class="overview-boxes">
        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Workers</div>
                <div class="number"><?php echo number_format($total_workers); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Up from yesterday</span>
                </div>
            </div>
            <i class="bx bxs-user-circle cart"></i>
        </div>
        
        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Clients</div>
                <div class="number"><?php echo number_format($total_clients); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Up from yesterday</span>
                </div>
            </div>
            <i class="bx bxs-user cart four"></i>
        </div>
        
        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Profits</div>
                <div class="number">$<?php echo number_format($total_profits, 2); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Up from yesterday</span>
                </div>
            </div>
            <i class="bx bx-dollar-circle cart two"></i>
        </div>
        
        <div class="box">
            <div class="right-side">
                <div class="box-topic">Total Services</div>
                <div class="number"><?php echo number_format($total_services); ?></div>
                <div class="indicator">
                    <i class="bx bx-up-arrow-alt"></i>
                    <span class="text">Up from yesterday</span>
                </div>
            </div>
            <i class="bx bxs-wrench cart three"></i>
        </div>
    </div>
    
    <!-- Sales Boxes -->
    <div class="sales-boxes">
        <!-- Recent Appointments Box -->
        <div class="recent-sales box">
            <div class="title">Recent Appointments</div>
            <div class="sales-details">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Worker</th>
                            <th>Service</th>
                            <th>Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_appointments)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No appointments found</td>
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
                                        $status_text = h($appointment['status']);
                                        
                                        if ($appointment['is_done'] == 1) {
                                            $status_class = 'status-done';
                                            $status_text = 'Done';
                                        } elseif ($appointment['status'] == 'accepted') {
                                            $status_class = 'status-in-progress';
                                            $status_text = 'In Progress';
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
                <a href="appointments.php">See All</a> 
            </div>
        </div>
        
        <!-- Top Services Box -->
        <div class="top-sales box">
            <div class="title">Most Requested Services</div>
            <ul class="top-sales-details">
                <?php if (empty($top_services)): ?>
                    <li class="no-data">No service data available</li>
                <?php else: ?>
                    <?php foreach ($top_services as $service): ?>
                        <li>
                            <a href="service_detail.php?title=<?php echo urlencode(h($service['title'])); ?>">
                                <img src="assets/images/services/<?php echo h($service['images']); ?>" alt="<?php echo h($service['title']); ?>">
                                <span class="product"><?php echo h($service['title']); ?></span>
                            </a>
                            <span class="price"><?php echo h($service['appointment_count']); ?> requests</span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
