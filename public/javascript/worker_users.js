document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector(".search-input"),
          usersList = document.getElementById("usersList"),
          currentUserId = CURRENT_USER_ID;

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        fetchUsers(searchTerm);
    });

    // Fetch users with optional search term
    function fetchUsers(searchTerm = "") {
        fetch(`../api/get-users.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayUsers(data);
            })
            .catch(error => {
                console.error('Error fetching users:', error);
                usersList.innerHTML = `<li class="error-message">Error loading users. Please try again later.</li>`;
            });
    }

    // Display users in the premium interface
    function displayUsers(users) {
        let usersHTML = '';

        if (Array.isArray(users) && users.length > 0) {
            users.forEach(user => {
                // Skip the current user
                if (user.unique_id === currentUserId) return;

                usersHTML += `
                    <li class="user-item" data-user-id="${user.unique_id}">
                        <img src="images/${user.img}" alt="${user.fname} ${user.lname}" class="user-avatar">
                        <div class="user-details">
                            <div class="user-name">
                                ${user.fname} ${user.lname}
                                <span class="status-badge ${user.status.toLowerCase()}"></span>
                            </div>
                            <div class="user-status">${user.status}</div>
                        </div>
                    </li>
                `;
            });

            if (usersHTML === '') {
                usersHTML = `<li class="error-message">No other users available to chat</li>`;
            }
        } else {
            usersHTML = `<li class="error-message">No users found</li>`;
        }

        usersList.innerHTML = usersHTML;
        setupUserClickHandlers();
    }

    // Setup click handlers for user items
    function setupUserClickHandlers() {
        const userItems = document.querySelectorAll('.user-item');
        userItems.forEach(item => {
            item.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const userName = this.querySelector('.user-name').textContent.trim();
                const userStatus = this.querySelector('.user-status').textContent.trim();
                
                // Navigate to chat page with user details
                window.location.href = `worker_chat.php?unique_id=${userId}&user_name=${encodeURIComponent(userName)}&user_status=${encodeURIComponent(userStatus)}`;
            });
        });
    }

    // Initial fetch of all users
    fetchUsers();

    // Periodically refresh user list (every 5 seconds)
    setInterval(() => {
        fetchUsers(searchInput.value.trim());
    }, 5000);
});