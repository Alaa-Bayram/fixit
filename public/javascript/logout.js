function logout() {
    // Get current language from PHP or default to English
    const lang = "<?= $lang ?? 'en' ?>";
    
    // Get translated messages from PHP
    const confirmMessage = "<?= $translations['logout_confirm'] ?? 'Are you sure you want to logout?' ?>";
    const errorMessage = "<?= $translations['logout_error'] ?? 'Error during logout' ?>";
    
    // Display confirmation dialog with translated message
    if (confirm(confirmMessage)) {
        fetch(`../api/logout.php?lang=${lang}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to login page with language parameter
                window.location.href = `login.html?lang=${lang}`;
            } else {
                // Show translated error message
                alert(data.message || errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(errorMessage);
        });
    }
}