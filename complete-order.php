<?php
include('db_connection.php'); // Connection to the database

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Get the order data from the request
$orderList = $data['orderList'];
$total = $data['total'];
$total_profit = $data['total_profit'];
$customer_name = $data['customer_name'];
$payment_method = $data['payment_method'];
$debt_amount = isset($data['debt_amount']) ? $data['debt_amount'] : 0;

if (count($orderList) > 0) {
    // Insert order into 'orders' table
    $stmt = $conn->prepare("INSERT INTO orders (total, time, status, customer_name, payment_method, debt_amount, profit) VALUES (?, NOW(), 'created', ?, ?, ?, ?)");
    $stmt->bind_param("dssdd", $total, $customer_name, $payment_method, $debt_amount, $total_profit);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Loop through each item in the order list
    // Loop through each item in the order list
    foreach ($orderList as $item) {
        $product_id = $item['product_id'];
        $item_name = $item['name'];
        $quantity = $item['quantity'];
        $buying_price = $item['buying_price'];
        $selling_price = $item['price'];
        $sum = $item['sum'];
        $unit_type = isset($item['unit_type']) ? $item['unit_type'] : 'whole';

        // Determine 'sold_in' value based on unit type
        if ($unit_type === 'whole') {
            $sold_in = 'whole';
        } else {
            $unit_id = $item['unit_id'];
            $stmt_unit = $conn->prepare("SELECT name FROM units WHERE unit_id = ?");
            $stmt_unit->bind_param("i", $unit_id);
            $stmt_unit->execute();
            $result_unit = $stmt_unit->get_result();
            $unit_row = $result_unit->fetch_assoc();
            $sold_in = $unit_row['name'];
            $stmt_unit->close();
        }

        // Insert into order_items table with product_id and sold_in column
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, quantity, buying_price, selling_price, sum, sold_in) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiddss", $order_id, $product_id, $item_name, $quantity, $buying_price, $selling_price, $sum, $sold_in);
        $stmt->execute();

        // Inventory update logic for whole or unit
        if ($unit_type === 'whole') {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();

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
            $unit_id = $item['unit_id'];
            $stmt = $conn->prepare("SELECT per_single_quantity FROM units WHERE unit_id = ?");
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $unit_data = $result->fetch_assoc();

            if ($unit_data['per_single_quantity'] > 0) {
                $quantity_reduction = $quantity / $unit_data['per_single_quantity'];

                $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
                $stmt->bind_param("di", $quantity_reduction, $product_id);
                $stmt->execute();

                $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $current_quantity = $row['quantity'];

                $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
                $stmt->bind_param("di", $current_quantity, $product_id);
                $stmt->execute();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid unit data, per_single_quantity cannot be zero.']);
                exit;
            }
        }
    }


    if ($payment_method === 'cash') {
        $stmt = $conn->prepare("UPDATE money SET cash = cash + ?");
        $stmt->bind_param("d", $total);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No items to complete']);
}

$stmt->close();
$conn->close();
?>
