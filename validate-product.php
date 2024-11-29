<?php
session_start();
include('db_connection.php');

// Ensure the user is logged in and the company_id session is set
if (!isset($_SESSION['company_id'])) {
    echo json_encode(['error' => 'User not logged in or session expired']);
    exit();
}

$product_id = $_GET['product_id'];
$unit_id = $_GET['unit_id']; // Get the unit_id from the request
$company_id = $_SESSION['company_id']; // Get the company_id from session

// Fetch product details from `products` and the specific unit from `units` table
$sql = "
    SELECT 
        p.quantity AS quantity, 
        u.available_units 
    FROM products p 
    LEFT JOIN units u ON p.product_id = u.product_id AND u.unit_id = ? 
    WHERE p.product_id = ? AND p.company_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $unit_id, $product_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Product or unit not found']);
}
?>
