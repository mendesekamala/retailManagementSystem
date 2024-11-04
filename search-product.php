<?php
include('db_connection.php');

$query = $_GET['query'];
$sql = "SELECT * FROM products WHERE name LIKE '%$query%'";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>
