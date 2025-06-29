<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$lang = 'en'; // Default

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$lang_file = "lang/{$lang}.php";
if (file_exists($lang_file)) {
    $translations = include $lang_file;
} else {
    $translations = include "lang/en.php";
}

?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/workerDash.css">
</head>
<body>
    <?php include_once "worker_header.php"; ?>
    
    <main class="appointments-container">
        <div class="appointments-header">
            <div class="header-content">
                <h1><?= $translations['appointments_title'] ?></h1>
                <p class="subtitle"><?= $translations['appointments_subtitle'] ?></p>
            </div>
            <div class="header-actions">
                <button id="toggleAcceptedBtn" class="toggle-btn" 
                data-show="<?= $translations['show_accepted'] ?>"
                data-hide="<?= $translations['hide_accepted'] ?>">
                <i class="bi bi-eye-slash"></i> <?= $translations['hide_accepted'] ?>
            </button>
                <div class="stats-summary">
                    <span class="stat-item"><i class="bi bi-hourglass-split"></i> <span id="pending-count">0</span> <?= $translations['pending'] ?></span>
<span class="stat-item"><i class="bi bi-check-circle"></i> <span id="accepted-count">0</span> <?= $translations['accepted'] ?></span>
                </div>
            </div>
        </div>

        <div class="appointments-grid" id="appointments-grid"></div>

        <div class="empty-state" id="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h3><?= $translations['no_appointments'] ?></h3>
            <p><?= $translations['no_appointments_msg'] ?></p>
        </div>

    </main>

    <!-- Floating Action Button -->
    <button class="chatbot-toggler">
        <a href="worker_users.php" aria-label="Client messages">
            <i class="bi bi-chat-left-text"></i>
        </a>
    </button>

    <script>
    document.addEventListener('DOMContentLoaded', fetchAppointments);

    let appointmentsData = [];

    function fetchAppointments() {
        fetch('../api/fetch_appointments.php')
            .then(response => response.json())
            .then(data => {
                appointmentsData = data;
                renderAppointments(data);
                updateStats(data);
            })
            .catch(error => {
                console.error('Error fetching appointments:', error);
                document.getElementById('empty-state').classList.remove('hidden');
            });
    }

    function renderAppointments(data) {
        const container = document.getElementById('appointments-grid');
        container.innerHTML = '';

        if (data.length === 0) {
            document.getElementById('empty-state').classList.remove('hidden');
            return;
        } else {
            document.getElementById('empty-state').classList.add('hidden');
        }

        data.forEach(appointment => {
            const date = new Date(appointment.date);
            const formattedDate = date.toLocaleDateString('en-US', { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            
            const timeString = appointment.time;
            const timeParts = timeString.split(':');
            const time = new Date();
            time.setHours(parseInt(timeParts[0], 10));
            time.setMinutes(parseInt(timeParts[1], 10));
            const formattedTime = time.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                hour12: true 
            });

            const statusClass = appointment.status === 'accepted' ? 'accepted' : 
                              (appointment.status === 'rejected' ? 'rejected' : 'pending');

            const appointmentCard = document.createElement('div');
            appointmentCard.className = `appointment-card ${statusClass}`;
            
            appointmentCard.innerHTML = `
                <div class="card-header">
                    <div class="client-info">
                        <div class="client-name">${appointment.fname} ${appointment.lname}</div>
                        <div class="appointment-status ${statusClass}">
                            <i class="bi ${statusClass === 'accepted' ? 'bi-check-circle' : 
                                          statusClass === 'rejected' ? 'bi-x-circle' : 'bi-hourglass'}"></i>
                            ${appointment.status}
                        </div>
                    </div>
                    <div class="appointment-time">
                        <i class="bi bi-calendar-event"></i>
                        ${formattedDate}
                        <i class="bi bi-clock"></i>
                        ${formattedTime}
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="bi bi-envelope"></i>
                            ${appointment.email}
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-telephone"></i>
                            ${appointment.phone}
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-geo-alt"></i>
                            ${appointment.address}
                        </div>
                    </div>
                    
                    ${appointment.status === 'accepted' ? `
                    <div class="accepted-label">
                        <i class="bi bi-check-circle"></i> Appointment Accepted
                    </div>
                ` : appointment.status === 'rejected' ? `
                    <div class="rejected-label">
                        <i class="bi bi-x-circle"></i> Appointment Rejected
                    </div>
                ` : `
                    <form action="php/update_status.php" method="POST" class="action-buttons">
                        <input type="hidden" name="appointment_id" value="${appointment.appointment_id}">
                        <input type="hidden" name="email" value="${appointment.email}">
                        <input type="hidden" name="fname" value="${appointment.fname}">
                        <input type="hidden" name="lname" value="${appointment.lname}">

                        <button type="submit" name="status" value="accepted" class="btn accept-btn">
                            <i class="bi bi-check-lg"></i> Accept
                        </button>
                        <button type="submit" name="status" value="rejected" class="btn reject-btn">
                            <i class="bi bi-x-lg"></i> Reject
                        </button>
                    </form>
                `}

                </div>
            `;
            
            container.appendChild(appointmentCard);
        });
    }

    function updateStats(data) {
        const pendingCount = data.filter(a => a.status === 'pending').length;
        const acceptedCount = data.filter(a => a.status === 'accepted').length;
        
        document.getElementById('pending-count').textContent = pendingCount;
        document.getElementById('accepted-count').textContent = acceptedCount;
    }

function toggleAcceptedAppointments() {
    const acceptedCards = document.querySelectorAll('.appointment-card.accepted');
    const toggleBtn = document.getElementById('toggleAcceptedBtn');

    acceptedCards.forEach(card => card.classList.toggle('hidden'));

    const isHidden = acceptedCards[0]?.classList.contains('hidden');
    toggleBtn.innerHTML = `<i class="bi ${isHidden ? 'bi-eye' : 'bi-eye-slash'}"></i> ${isHidden ? toggleBtn.dataset.show : toggleBtn.dataset.hide}`;
}


    document.getElementById('toggleAcceptedBtn').addEventListener('click', toggleAcceptedAppointments);
    </script>
</body>
</html>