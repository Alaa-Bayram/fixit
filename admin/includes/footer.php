</section>

<script>
    // Toggle sidebar
    document.addEventListener('DOMContentLoaded', function() {
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        
        sidebarBtn.addEventListener('click', function() {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        });
        
        // Profile dropdown
        const profileDetails = document.querySelector('.profile-details');
        if (profileDetails) {
            profileDetails.addEventListener('click', function() {
                this.classList.toggle('show-dropdown');
            });
        }
        
        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function(event) {
            if (profileDetails && !profileDetails.contains(event.target)) {
                profileDetails.classList.remove('show-dropdown');
            }
        });
    });
</script>
</body>
</html>