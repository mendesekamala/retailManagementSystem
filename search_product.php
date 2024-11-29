<?php
session_start();
include('db_connection.php');

// Ensure the user is logged in and the company_id session is set
if (!isset($_SESSION['company_id'])) {
    // Optionally, you could redirect the user to a login page if they are not logged in
    echo json_encode(['error' => 'User not logged in or session expired']);
    exit();
}

$query = $_GET['query']; // User input for search
$company_id = $_SESSION['company_id']; // Get the company_id from session

// Prepare the SQL query to filter products by company_id and search query
$sql = "SELECT * FROM products WHERE name LIKE ? AND company_id = ?";
$stmt = $conn->prepare($sql);
$search_term = "%" . $query . "%"; // Add wildcards for the LIKE clause
$stmt->bind_param('si', $search_term, $company_id); // 'si' means string and integer (for query and company_id)

$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to store the products
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Return the products as JSON
echo json_encode($products);
?>
