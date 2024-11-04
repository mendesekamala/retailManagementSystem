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

    // Fetch roles from session
    $roles = $_SESSION['roles'];

    // Include the sidebar based on the role
    include_sidebar_by_role($roles);
?>