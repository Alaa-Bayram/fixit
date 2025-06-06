class ChatManager {
    constructor() {
        // Chat elements
        this.form = document.getElementById('messageForm');
        this.messageInput = this.form?.querySelector('input[name="message"]');
        this.sendBtn = this.form?.querySelector('button[type="submit"]');
        this.messagesContainer = document.getElementById('messagesContainer');
        this.incomingId = this.form?.querySelector('input[name="incoming_id"]')?.value;
        
        // Users list elements
        this.usersList = document.getElementById('usersList');
        this.searchInput = document.querySelector('.search-input');
        
        this.currentUserId = typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : '';
        this.isTyping = false;
        
        this.init();
    }

    init() {
        if (this.form && this.messagesContainer) {
            this.setupChatEventListeners();
            this.loadMessages();
            this.setupMessagePolling();
        }
        
        if (this.usersList) {
            this.setupUsersEventListeners();
            this.fetchUsers('');
            this.setupUsersPolling();
        }
    }

    /* ========== CHAT FUNCTIONALITY ========== */
    setupChatEventListeners() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        // Input field events
        this.messageInput.addEventListener('input', () => {
            this.sendBtn.classList.toggle('active', this.messageInput.value.trim() !== '');
        });

        this.messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.messageInput.value.trim()) {
                    this.sendMessage();
                }
            }
        });
    }

    async sendMessage() {
        const message = this.messageInput.value.trim();
        if (!message) return;

        try {
            const formData = new FormData(this.form);
            
            const response = await fetch('../php/insert-chat.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Failed to send message');
            }

            this.messageInput.value = '';
            this.sendBtn.classList.remove('active');
            this.loadMessages();
            this.scrollToBottom();
        } catch (error) {
            console.error('Error sending message:', error);
            this.showMessageError('Failed to send message. Please try again.');
        }
    }

    async loadMessages() {
        try {
            const response = await fetch('../php/get-chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `incoming_id=${this.incomingId}`
            });

            if (!response.ok) {
                throw new Error('Failed to load messages');
            }

            const messagesHTML = await response.text();
            this.messagesContainer.innerHTML = messagesHTML;
            
            if (!this.isUserScrolledUp()) {
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            this.showMessageError('Failed to load messages. Please refresh the page.');
        }
    }

    setupMessagePolling() {
        // Load new messages every second
        setInterval(() => {
            this.loadMessages();
        }, 1000);

        // Check for new messages when window gains focus
        window.addEventListener('focus', () => {
            this.loadMessages();
        });
    }

    /* ========== USERS LIST FUNCTIONALITY ========== */
    setupUsersEventListeners() {
        // Search functionality
        this.searchInput.addEventListener('input', (e) => {
            this.fetchUsers(e.target.value.trim());
        });
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
            this.showUsersError('Failed to load users. Please try again.');
        }
    }

    displayUsers(users) {
        if (!Array.isArray(users)) {
            this.showUsersError('Invalid data received from server');
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

    setupUsersPolling() {
        // Refresh users list every 30 seconds when not searching
        setInterval(() => {
            if (!this.searchInput.value.trim()) {
                this.fetchUsers('');
            }
        }, 30000);
    }

    /* ========== UTILITY METHODS ========== */
    scrollToBottom() {
        if (this.messagesContainer) {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }
    }

    isUserScrolledUp() {
        if (!this.messagesContainer) return false;
        
        const threshold = 100; // pixels from bottom
        return this.messagesContainer.scrollTop + this.messagesContainer.clientHeight < 
               this.messagesContainer.scrollHeight - threshold;
    }

    formatLastSeen(timestamp) {
        // Implement your last seen formatting logic
        return 'recently';
    }

    showMessageError(message) {
        if (!this.messagesContainer) return;
        
        const errorElement = document.createElement('div');
        errorElement.className = 'message-error';
        errorElement.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
        `;
        this.messagesContainer.appendChild(errorElement);

        setTimeout(() => {
            if (errorElement.parentNode) {
                errorElement.parentNode.removeChild(errorElement);
            }
        }, 5000);
    }

    showUsersError(message) {
        if (!this.usersList) return;
        
        this.usersList.innerHTML = `
            <li class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>${message}</p>
            </li>
        `;
    }
}

// Initialize the chat manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ChatManager();
});