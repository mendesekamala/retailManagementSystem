<?php
include('db_connection.php');
session_start();

$order_id = $_GET['order_id'];
$created_by = $_SESSION['user_id'];

$conn->begin_transaction();

try {
    // Fetch order details
    $orderQuery = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $orderQuery->bind_param("i", $order_id);
    $orderQuery->execute();
    $orderResult = $orderQuery->get_result();

    if ($orderResult->num_rows === 0) {
        throw new Exception('Order not found.');
    }

    $orderData = $orderResult->fetch_assoc();
    if ($orderData['status'] === 'cancelled') {
        throw new Exception('The order is already cancelled.');
    }
    if ($orderData['status'] === 'delivered') {
        throw new Exception('The order is already delivered and cannot be cancelled.');
    }

    $company_id = $orderData['company_id'];
    $total = $orderData['total'];

    // Reverse product quantities
    $orderItemsQuery = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $orderItemsQuery->bind_param("i", $order_id);
    $orderItemsQuery->execute();
    $orderItemsResult = $orderItemsQuery->get_result();

    while ($item = $orderItemsResult->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $unit_type = $item['sold_in'];

        if ($unit_type === 'whole') {
            $updateProduct = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
            $updateProduct->bind_param("ii", $quantity, $product_id);
            $updateProduct->execute();
        } else {
            $unit_id = $item['unit_id'];
            $unitQuery = $conn->prepare("SELECT per_single_quantity FROM units WHERE unit_id = ?");
            $unitQuery->bind_param("i", $unit_id);
            $unitQuery->execute();
            $unitResult = $unitQuery->get_result();
            $unitData = $unitResult->fetch_assoc();

            if (!$unitData || $unitData['per_single_quantity'] <= 0) {
                throw new Exception("Invalid unit data for product ID: $product_id");
            }

            $quantity_restore = $quantity / $unitData['per_single_quantity'];
            $updateProduct = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
            $updateProduct->bind_param("di", $quantity_restore, $product_id);
            $updateProduct->execute();
        }
    }

    // Mark order items as cancelled
    $updateOrderItems = $conn->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?");
    $updateOrderItems->bind_param("i", $order_id);
    $updateOrderItems->execute();

    // Reverse transaction
    $reverseTransaction = $conn->prepare("INSERT INTO transactions 
        (transaction_type, transType_id, amount, company_id, created_by, date_made) 
        VALUES ('refund', ?, ?, ?, ?, NOW())");
    $negative_total = -1 * $total;
    $reverseTransaction->bind_param("idii", $order_id, $negative_total, $company_id, $created_by);
    $reverseTransaction->execute();


    $reverse_transaction_id = $conn->insert_id;

    // Restore money accounts
    $fetchMoney = $conn->prepare("SELECT * FROM money WHERE company_id = ?");
    $fetchMoney->bind_param("i", $company_id);
    $fetchMoney->execute();
    $moneyResult = $fetchMoney->get_result();

    if ($moneyResult->num_rows === 0) {
        throw new Exception("Money record for company not found!");
    }

    $moneyData = $moneyResult->fetch_assoc();

    // Get payment methods used
    $methodsQuery = $conn->prepare("SELECT * FROM methods_used 
        WHERE transaction_id = (
            SELECT transaction_id FROM transactions 
            WHERE transaction_type = 'sale' 
            AND transType_id = ? AND company_id = ? LIMIT 1
        )");
    $methodsQuery->bind_param("ii", $order_id, $company_id);
    $methodsQuery->execute();
    $methodsResult = $methodsQuery->get_result();

    while ($method = $methodsResult->fetch_assoc()) {
        $method_name = str_replace(" ", "_", $method['payment_method']); // Match column format
        $amount = $method['partial_amount'];

        if (!isset($moneyData[$method_name])) {
            throw new Exception("Invalid payment method: " . $method['payment_method']);
        }

        $moneyData[$method_name] -= $amount;

        // Insert negative record in methods_used
        $insertMethod = $conn->prepare("INSERT INTO methods_used (transaction_id, payment_method, partial_amount, total_amount) VALUES (?, ?, ?, ?)");
        $negative_amount = -1 * $amount;
        $insertMethod->bind_param("ssdd", $reverse_transaction_id, $method['payment_method'], $negative_amount, $total);
        $insertMethod->execute();
    }

    // Update money balances
    $updateMoney = $conn->prepare("UPDATE money SET cash = ?, NMB = ?, CRDB = ?, NBC = ?, mpesa = ?, tigo_pesa = ?, airtel_money = ?, halo_pesa = ? WHERE company_id = ?");
    $updateMoney->bind_param(
        "ddddddddi",
        $moneyData['cash'],
        $moneyData['NMB'],
        $moneyData['CRDB'],
        $moneyData['NBC'],
        $moneyData['mpesa'],
        $moneyData['tigo_pesa'],
        $moneyData['airtel_money'],
        $moneyData['halo_pesa'],
        $company_id
    );
    $updateMoney->execute();

    // Mark order as cancelled
    $updateOrder = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
    $updateOrder->bind_param("i", $order_id);
    $updateOrder->execute();

    // Commit transaction
    $conn->commit();

    echo "<script>alert('Order cancelled successfully'); window.location.href = 'orders.php';</script>";

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in cancel-order.php: " . $e->getMessage());
    echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'orders.php';</script>";
}

// Close connections
$conn->close();
?>
