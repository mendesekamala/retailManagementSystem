<?php

include('db_connection.php');

// Queries for the tiles
$underStockQuery = "SELECT COUNT(*) AS under_stock_reminders FROM units WHERE available_units < 10";
$zeroQuantityQuery = "SELECT COUNT(*) AS zero_quantity_products FROM products WHERE quantity = 0";
$leastSoldQuery = "SELECT COUNT(DISTINCT name) AS least_sold_products FROM order_items GROUP BY name HAVING SUM(quantity) < 5";
$destroyedProductsQuery = "SELECT COUNT(*) AS most_destroyed_products FROM products WHERE quantity = 0 AND quantified = 'destroyed'";

// Execute tile queries
$underStockResult = $conn->query($underStockQuery)->fetch_assoc()['under_stock_reminders'];
$zeroQuantityResult = $conn->query($zeroQuantityQuery)->fetch_assoc()['zero_quantity_products'];
$leastSoldResult = $conn->query($leastSoldQuery)->num_rows;
$destroyedProductsResult = $conn->query($destroyedProductsQuery)->fetch_assoc()['most_destroyed_products'];

// Queries for the lists
$mostSoldHighQuantityQuery = "SELECT name, SUM(quantity) AS total_quantity_sold FROM order_items GROUP BY name ORDER BY total_quantity_sold DESC LIMIT 3";
$mostSoldOrdersQuery = "SELECT name, COUNT(order_id) AS order_count FROM order_items GROUP BY name ORDER BY order_count DESC LIMIT 3";

// Execute list queries
$mostSoldHighQuantityResult = $conn->query($mostSoldHighQuantityQuery);
$mostSoldOrdersResult = $conn->query($mostSoldOrdersQuery);

$conn->close();
?>
