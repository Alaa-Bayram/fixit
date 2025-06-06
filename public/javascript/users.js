class UsersManager {
    constructor() {
        this.searchInput = document.querySelector('.search-input');
        this.usersList = document.getElementById('usersList');
        this.currentUserId = typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : '';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.fetchUsers('');
    }

    setupEventListeners() {
        // Search functionality
        this.searchInput.addEventListener('input', (e) => {
            this.fetchUsers(e.target.value.trim());
        });

        // Refresh users every 30 seconds
        setInterval(() => {
            if (!this.searchInput.value.trim()) {
                this.fetchUsers('');
            }
        }, 30000);
    }

    async fetchUsers(searchTerm = '') {
        try {
            const response = await fetch(`../../api/get-users.php?search=${encodeURIComponent(searchTerm)}`);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const users = await response.json();
            this.displayUsers(users);
        } catch (error) {
            console.error('Error fetching users:', error);
            this.showError('Failed to load users. Please try again.');
        }
    }

    displayUsers(users) {
        if (!Array.isArray(users)) {
            this.showError('Invalid data received from server');
            return;
        }

        if (users.length === 0) {
            this.usersList.innerHTML = `
                <li class="no-users">
                    <div class="no-users-content">
                        <i class="far fa-user-friends"></i>
                        <p>No contacts found</p>
                    </div>
                </li>
            `;
            return;
        }

        let usersHTML = users.map(user => {
            const isCurrentUser = user.unique_id === this.currentUserId;
            if (isCurrentUser) return '';

            return `
                <li class="user-item" data-user-id="${user.unique_id}">
                    <img src="../php/images/${user.img}" alt="${user.fname} ${user.lname}" class="user-avatar">
                    <div class="user-details">
                        <div class="user-name">
                            ${user.fname} ${user.lname}
                            <span class="status-badge ${user.status.toLowerCase()}"></span>
                        </div>
                        <div class="user-status">${user.status}</div>
                        ${user.last_seen ? `<div class="last-seen">Last seen ${this.formatLastSeen(user.last_seen)}</div>` : ''}
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </li>
            `;
        }).join('');

        this.usersList.innerHTML = usersHTML;

        // Add click event to each user item
        document.querySelectorAll('.user-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = item.getAttribute('data-user-id');
                const userName = item.querySelector('.user-name').textContent.trim();
                const userStatus = item.querySelector('.user-status').textContent.trim();
                window.location.href = `chat.php?unique_id=${userId}&user_name=${encodeURIComponent(userName)}&user_status=${encodeURIComponent(userStatus)}`;
            });
        });
    }

    formatLastSeen(timestamp) {
        // Implement your last seen formatting logic
        return 'recently';
    }

    showError(message) {
        this.usersList.innerHTML = `
            <li class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </li>
        `;
    }
}

// Initialize the users manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new UsersManager();
});