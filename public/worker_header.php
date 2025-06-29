<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation</title>
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');
*{
  margin: 0px;
  padding: 0px;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}
/* Language Switcher Styles */
.lang-switcher {
    position: relative;
    margin-left: 15px;
}

.language-selector {
    position: relative;
    display: flex;
    align-items: center;
}

.language-trigger {
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
    color: #fff;
    font-size: 1rem;
}

.language-trigger:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.language-trigger i {
    margin-right: 8px;
    font-size: 1.2rem;
}

.current-language {
    font-weight: 500;
    font-size: 0.9rem;
}

.language-dropdown {
    position: relative;
}

.language-select {
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 120px;
    padding: 8px 12px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s ease;
    cursor: pointer;
    z-index: 1000;
}

.language-selector:hover .language-select,
.language-select:focus {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* For mobile responsiveness */
@media (max-width: 768px) {
    .language-select {
        right: auto;
        left: 0;
    }
    
    .language-trigger {
        padding: 8px;
    }
    
    .current-language {
        display: none;
    }
}
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo"><a href="#"><img src="app_images/logo1.png"></a></div>
            <input type="checkbox" id="menu-toggler">
            <label for="menu-toggler" id="hamburger-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
                </svg>
            </label>
            <ul class="all-links">
                <li><a href="worker_home.php"><?= $translations['home'] ?? 'Home' ?></a></li>
                <li><a href="worker_emergencies.php"><?= $translations['emergency'] ?? 'Emergency' ?></a></li>
                <li><a href="worker_dash.php"><?= $translations['appointments'] ?? 'Appointments' ?></a></li>
                <li><a href="worker_schedule.php" target="_blank"><?= $translations['my_schedule'] ?? 'My Schedule' ?></a></li>
                <li class="lang-switcher">
                    <div class="language-selector">
                        <button class="language-trigger" aria-label="Language selector" title="<?= $translations['change_language'] ?? 'Change Language' ?>">
                            <i class="fas fa-globe"></i>
                            <span class="current-language"><?= strtoupper($lang) ?></span>
                        </button>
                        <div class="language-dropdown">
                            <select class="language-select" onchange="changeLang(this.value)">
                                <option value="en" <?= $lang == 'en' ? 'selected' : '' ?>>English</option>
                                <option value="ar" <?= $lang == 'ar' ? 'selected' : '' ?>>عربي</option>
                                <option value="fr" <?= $lang == 'fr' ? 'selected' : '' ?>>Français</option>
                            </select>
                        </div>
                    </div>
                </li>
                <li><a href="#" class="logout" onclick="logout()"><?= $translations['logout'] ?? 'Logout' ?></a></li>

            </ul>

        </nav>
    </header>

    <script>
        function changeLang(lang) {
    window.location.href = window.location.pathname + '?lang=' + lang;
}
        function logout() {
             // Display confirmation dialog
        const userConfirmed = confirm("Are you sure you want to logout?");
        if (userConfirmed) {
            fetch('../api/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to login page or show a message
                    window.location.href = 'login.html'; // Update with your login page
                } else {
                    alert('Logout failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });} else {
                console.log('Logout cancelled by user');
        }}
    </script>
</body>
</html>
