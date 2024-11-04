<?php
session_start();
include('db_connection.php');

// Helper function to check query success and display errors
function check_query($result, $query) {
    global $conn;
    if (!$result) {
        echo "Query failed: $query\nError: " . mysqli_error($conn);
        return false;
    }
    return true;
}

// Retrieve order ID from the POST data
$order_id = $_POST['order_id'];

$debtAmount = $orderDetails['debt_amount'] ?? 0;
$orderStatus = $_POST['order_status'];

// Start transaction to ensure atomicity
mysqli_begin_transaction($conn);

try {
    // Loop through each item in the order
    foreach ($_POST['items'] as $item_id => $item_data) {
        $quantity = (int)$item_data['quantity'];
        $removed = (int)$item_data['removed'];

        // Fetch details of the item from order_items table
        $query = "SELECT product_id, quantity, sold_in FROM order_items WHERE item_id = $item_id";
        $result = mysqli_query($conn, $query);
        if (!check_query($result, $query)) { mysqli_rollback($conn); exit; }

        $orderItem = mysqli_fetch_assoc($result);
        if (!$orderItem) {
            echo "Failed to fetch order item details for item_id = $item_id";
            mysqli_rollback($conn);
            exit;
        }

        $product_id = $orderItem['product_id'];
        $original_quantity = (int)$orderItem['quantity'];
        $sold_in = $orderItem['sold_in'];

        if ($removed) {
            // If item was removed, add back to products and update units
            if ($sold_in === 'whole') {
                // Add the quantity back to the products table for whole units
                $updateProductQty = "UPDATE products SET quantity = quantity + $original_quantity WHERE product_id = $product_id";
                mysqli_query($conn, $updateProductQty);

                // Fetch the updated quantity from the products table
                $resultProduct = mysqli_query($conn, "SELECT quantity FROM products WHERE product_id = $product_id");
                if (!check_query($resultProduct, "SELECT quantity FROM products WHERE product_id = $product_id")) { mysqli_rollback($conn); exit; }

                $product = mysqli_fetch_assoc($resultProduct);
                $updated_quantity = (float)$product['quantity'];

                // Recalculate each associated unit's available_units based on the updated quantity
                $updateUnits = "UPDATE units SET available_units = per_single_quantity * $updated_quantity WHERE product_id = $product_id";
                mysqli_query($conn, $updateUnits);
            } else {
                // For items sold in units, use unit ratio to adjust product quantity
                $queryUnit = "SELECT per_single_quantity FROM units WHERE product_id = $product_id AND name = '$sold_in'";
                $resultUnit = mysqli_query($conn, $queryUnit);
                if (!check_query($resultUnit, $queryUnit)) { mysqli_rollback($conn); exit; }

                $unitData = mysqli_fetch_assoc($resultUnit);
                $unit_ratio = isset($unitData['per_single_quantity']) ? (float)$unitData['per_single_quantity'] : 0;

                if ($unit_ratio == 0) {
                    echo "Error: per_single_quantity is zero or not set for product_id = $product_id";
                    mysqli_rollback($conn);
                    exit;
                }

                // Calculate the equivalent quantity in whole units
                $adjusted_quantity = $original_quantity / $unit_ratio; // Divide to get the equivalent in whole units

                // Update the products table with the calculated whole unit equivalent
                $updateProductQty = "UPDATE products SET quantity = quantity + $adjusted_quantity WHERE product_id = $product_id";
                mysqli_query($conn, $updateProductQty);

                // Fetch the updated quantity from the products table
                $resultProduct = mysqli_query($conn, "SELECT quantity FROM products WHERE product_id = $product_id");
                if (!check_query($resultProduct, "SELECT quantity FROM products WHERE product_id = $product_id")) { mysqli_rollback($conn); exit; }

                $product = mysqli_fetch_assoc($resultProduct);
                $updated_quantity = (float)$product['quantity'];

                // Recalculate each associated unit's available_units based on the updated quantity
                $updateUnits = "UPDATE units SET available_units = per_single_quantity * $updated_quantity WHERE product_id = $product_id";
                mysqli_query($conn, $updateUnits);
            }

            // Finally, delete the item from order_items
            $deleteOrderItem = "DELETE FROM order_items WHERE item_id = $item_id";
            mysqli_query($conn, $deleteOrderItem);
        } else {
            // Item was not removed, but quantity may have changed
            if ($quantity != $original_quantity) {
                $quantityDifference = $quantity - $original_quantity;

                if ($sold_in === 'whole') {
                    // Update products table for whole unit items
                    $updateProductQty = "UPDATE products SET quantity = quantity - $quantityDifference WHERE product_id = $product_id";
                    mysqli_query($conn, $updateProductQty);

                    // Fetch the updated quantity from the products table
                    $resultProduct = mysqli_query($conn, "SELECT quantity FROM products WHERE product_id = $product_id");
                    if (!check_query($resultProduct, "SELECT quantity FROM products WHERE product_id = $product_id")) { mysqli_rollback($conn); exit; }

                    $product = mysqli_fetch_assoc($resultProduct);
                    $updated_quantity = (float)$product['quantity'];

                    // Recalculate each associated unit's available_units based on the updated quantity
                    $updateUnits = "UPDATE units SET available_units = per_single_quantity * $updated_quantity WHERE product_id = $product_id";
                    mysqli_query($conn, $updateUnits);
                } else {
                    // For items sold in units, use unit ratio to adjust product quantity
                    $queryUnit = "SELECT per_single_quantity FROM units WHERE product_id = $product_id AND name = '$sold_in'";
                    $resultUnit = mysqli_query($conn, $queryUnit);
                    if (!check_query($resultUnit, $queryUnit)) { mysqli_rollback($conn); exit; }

                    $unitData = mysqli_fetch_assoc($resultUnit);
                    $unit_ratio = isset($unitData['per_single_quantity']) ? (float)$unitData['per_single_quantity'] : 0;

                    if ($unit_ratio == 0) {
                        echo "Error: per_single_quantity is zero or not set for product_id = $product_id";
                        mysqli_rollback($conn);
                        exit;
                    }

                    $adjusted_quantity_diff = $quantityDifference / $unit_ratio;

                    $updateProductQty = "UPDATE products SET quantity = quantity - $adjusted_quantity_diff WHERE product_id = $product_id";
                    mysqli_query($conn, $updateProductQty);

                    // Fetch the updated quantity from the products table
                    $resultProduct = mysqli_query($conn, "SELECT quantity FROM products WHERE product_id = $product_id");
                    if (!check_query($resultProduct, "SELECT quantity FROM products WHERE product_id = $product_id")) { mysqli_rollback($conn); exit; }

                    $product = mysqli_fetch_assoc($resultProduct);
                    $updated_quantity = (float)$product['quantity'];

                    // Recalculate each associated unit's available_units based on the updated quantity
                    $updateUnits = "UPDATE units SET available_units = per_single_quantity * $updated_quantity WHERE product_id = $product_id";
                    mysqli_query($conn, $updateUnits);
                }

                // Update the quantity in order_items table
                $updateOrderItemQty = "UPDATE order_items SET quantity = $quantity WHERE item_id = $item_id";
                mysqli_query($conn, $updateOrderItemQty);
            }
        }
    }

    // Recalculate total and profit for the order
    $query = "SELECT SUM(quantity * selling_price) AS order_total, 
    SUM((selling_price - buying_price) * quantity) AS order_profit 
    FROM order_items 
    WHERE order_id = $order_id";
    $result = mysqli_query($conn, $query);
    if (!check_query($result, $query)) { mysqli_rollback($conn); exit; }

    $totals = mysqli_fetch_assoc($result);
    $order_total = $totals['order_total'];
    $order_profit = $totals['order_profit'];

    //Fetch the original total for the order
    $queryOriginalTotal = "SELECT total FROM orders WHERE order_id = $order_id";
    $resultOriginalTotal = mysqli_query($conn, $queryOriginalTotal);
    if (!check_query($resultOriginalTotal, $queryOriginalTotal)) { mysqli_rollback($conn); exit; }

    $originalTotalRow = mysqli_fetch_assoc($resultOriginalTotal);
    $originalTotal = (float)$originalTotalRow['total'];

    // Fetch the current cash amount
    $queryCurrentCash = "SELECT cash FROM money LIMIT 1";
    $resultCurrentCash = mysqli_query($conn, $queryCurrentCash);
    if (!check_query($resultCurrentCash, $queryCurrentCash)) { mysqli_rollback($conn); exit; }

    $currentCashRow = mysqli_fetch_assoc($resultCurrentCash);
    $currentCash = (float)$currentCashRow['cash'];

    // Temporarily reduce the cash by the original order total
    $adjustedCash = $currentCash - $originalTotal;

    // Adjust the cash based on the new total and update the money table
    $newCash = $adjustedCash + $order_total;
    $updateCashQuery = "UPDATE money SET cash = $newCash WHERE money_id = 1";
    if (!check_query(mysqli_query($conn, $updateCashQuery), $updateCashQuery)) { mysqli_rollback($conn); exit; }


    // Update the orders table with the new total and profit
    $updateOrder = "UPDATE orders SET total = $order_total, profit = $order_profit, status = '$orderStatus', debt_amount = $debtAmount WHERE order_id = $order_id";
    if (!check_query(mysqli_query($conn, $updateOrder), $updateOrder)) { mysqli_rollback($conn); exit; }


    // Commit the transaction before redirect
    mysqli_commit($conn);
    echo "Order updated successfully.";

} catch (Exception $e) {
    // Roll back transaction on any failure
    mysqli_rollback($conn);
    echo "Failed to update order: " . $e->getMessage();
}

// Close database connection
mysqli_close($conn);

header("Location: orders.php");
exit;
?>
