<?php
include('db_connection.php');

$query = $_GET['query'];

// Search products by name
$sql = "SELECT product_id, name, quantified, buying_price, selling_price FROM products WHERE name LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>
