<!-- show sidebar by roles function -->
<?php
    include('sidebars-function.php');

    // Fetch roles from session
    if (isset($_SESSION['roles'])) {
        $roles = $_SESSION['roles'];
    } else {
        // Handle case where roles are not set in session (fallback)
        $roles = ['company_owner' => 'no', 'cashier' => 'no', 'store_keeper' => 'no', 'delivery_man' => 'no'];
    }
?>

<!-- Mobile menu toggle button -->
<button class="menu-toggle" id="menuToggle">
    <i class='bx bx-menu'></i>
</button>

<!-- Include the appropriate sidebar content -->
<?php include_sidebar_by_role($roles); ?>

<!-- Sidebar overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar toggle JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarClose = document.querySelector('.sidebar-close');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (menuToggle && sidebar && sidebarClose && sidebarOverlay) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.add('active');
                document.body.classList.add('sidebar-open');
            });

            sidebarClose.addEventListener('click', function() {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            });
        }
    });
</script>