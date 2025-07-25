<head>
    
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
</head>
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
