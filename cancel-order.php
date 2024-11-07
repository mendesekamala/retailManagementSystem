<?php
include('db_connection.php'); // Database connection

// Get the order_id from the URL
$order_id = $_GET['order_id'];

// Check if the order exists and get its details
$orderQuery = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$orderQuery->bind_param("i", $order_id);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();

if ($orderResult->num_rows === 0) {
    echo "<script>alert('Order not found'); window.location.href = 'orders.php';</script>";
    exit;
}

$orderData = $orderResult->fetch_assoc();

// Check if the order is already cancelled
if ($orderData['status'] === 'cancelled') {
    echo "<script>alert('The order is already cancelled'); window.location.href = 'orders.php';</script>";
    exit;
}

// Check if the order is already delivered
if ($orderData['status'] === 'delivered') {
    echo "<script>alert('The order is already delivered'); window.location.href = 'orders.php';</script>";
    exit;
}

$orderData = $orderResult->fetch_assoc();
$total = $orderData['total'];
$payment_method = $orderData['payment_method'];

// Set the status of each order item to 'cancelled'
$updateOrderItemsStatus = $conn->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?");
$updateOrderItemsStatus->bind_param("i", $order_id);
$updateOrderItemsStatus->execute();

// Reverse the quantity of each item in the order back to the products table
$orderItemsQuery = $conn->prepare("
    SELECT oi.*, p.product_id 
    FROM order_items AS oi 
    JOIN products AS p ON oi.name = p.name 
    WHERE oi.order_id = ?
");
$orderItemsQuery->bind_param("i", $order_id);
$orderItemsQuery->execute();
$orderItemsResult = $orderItemsQuery->get_result();

while ($item = $orderItemsResult->fetch_assoc()) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $unit_type = isset($item['unit_type']) ? $item['unit_type'] : 'whole'; // Default to 'whole' if not set

    if ($unit_type === 'whole') {
        // Add quantity back to products
        $updateProductQuantity = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
        $updateProductQuantity->bind_param("ii", $quantity, $product_id);
        $updateProductQuantity->execute();

        // Update units for the restored product quantity
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_quantity = $row['quantity'];

        $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
        $stmt->bind_param("ii", $current_quantity, $product_id);
        $stmt->execute();

    } else {
        // If using units, add quantity back to products based on units
        $unit_id = $item['unit_id'];
        
        // Get per_single_quantity to calculate restoration
        $unitQuery = $conn->prepare("SELECT per_single_quantity FROM units WHERE unit_id = ?");
        $unitQuery->bind_param("i", $unit_id);
        $unitQuery->execute();
        $unitResult = $unitQuery->get_result();
        $unitData = $unitResult->fetch_assoc();

        if ($unitData['per_single_quantity'] > 0) {
            $quantity_restore = $quantity / $unitData['per_single_quantity'];

            // Add the calculated quantity back to products
            $updateProductQuantity = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
            $updateProductQuantity->bind_param("di", $quantity_restore, $product_id);
            $updateProductQuantity->execute();
            
            // Update units for restored product quantity
            $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $current_quantity = $row['quantity'];

            $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
            $stmt->bind_param("di", $current_quantity, $product_id);
            $stmt->execute();
        }
    }
}

// reduce the cash amount by the order total
$updateCash = $conn->prepare("UPDATE money SET cash = cash - ?");
$updateCash->bind_param("d", $total);
$updateCash->execute();

$conn->close();

// Redirect back to orders.php
header("Location: orders.php");
exit;
?>