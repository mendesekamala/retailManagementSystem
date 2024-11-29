<?php
session_start();
include 'db_connection.php';

// Check if POST data is set
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form inputs
    $product_id = intval($_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $per_single_quantity = floatval($_POST['per_single_quantity']);
    $buying_price = floatval($_POST['buying_price']);
    $selling_price = floatval($_POST['selling_price']);
    $available_units = floatval($_POST['available_units']);
    $created_by = intval($_POST['created_by']);  // Get created_by (user ID from session)
    $company_id = intval($_POST['company_id']);  // Get company_id (company ID from session)

    // Validate inputs
    if ($product_id > 0 && $per_single_quantity > 0 && $buying_price >= 0 && $selling_price >= 0) {
        // Prepare the SQL insert query
        $query = "INSERT INTO units (product_id, name, per_single_quantity, buying_price, selling_price, available_units, created_by, company_id) 
                  VALUES ('$product_id', '$name', '$per_single_quantity', '$buying_price', '$selling_price', '$available_units', '$created_by', '$company_id')";

        // Execute the query and check for success
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Unit added successfully!";
            header("Location: view-create_units.php?id=" . $product_id);
            exit();
        } else {
            $_SESSION['error_message'] = "Error: Could not save the unit. " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Please ensure all fields are filled correctly.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back to the unit creation page in case of error
header("Location: view-create_units.php?id=" . $product_id);
exit();
?>
