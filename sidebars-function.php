<?php    
    function include_sidebar_by_role($roles) {
        if ($roles['company_owner'] === 'yes') {
            include('sidebars/company_owner-sidebar.php');
        } elseif ($roles['cashier'] === 'yes') {
            include('sidebars/cashier-sidebar.php');
        } elseif ($roles['store_keeper'] === 'yes') {
            include('sidebars/store_keeper-sidebar.php');
        } elseif ($roles['delivery_man'] === 'yes') {
            include('sidebars/delivery_man-sidebar.php');
        } else {
            include('sidebars/default-sidebar.php');
        }
    }
?>