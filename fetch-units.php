<?php
include('db_connection.php');

$product_id = $_GET['product_id'];
$sql = "SELECT * FROM units WHERE product_id = $product_id";
$result = $conn->query($sql);

$units = [];
while ($row = $result->fetch_assoc()) {
    $units[] = $row;
}

echo json_encode($units);
?>
