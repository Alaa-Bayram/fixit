function logout() {
    // Display confirmation dialog
    const userConfirmed = confirm("Are you sure you want to logout?");
    if (userConfirmed) {
        fetch('../../api/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to login page or show a message
                window.location.href = '../login.html'; // Update with your login page
            } else {
                alert('Logout failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    } else {
        console.log('Logout cancelled by user');
    }
}
