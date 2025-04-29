<?php
include('db_connection.php');
session_start();

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in or session missing.']);
    exit();
}

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

$orderList = $data['orderList'];
$total = $data['total'];
$total_profit = $data['total_profit'];
$customer_name = $data['customer_name'];
$company_id = $_SESSION['company_id'];
$created_by = $_SESSION['user_id'];
$paymentMethods = $data['paymentMethods'];

if (count($orderList) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No items to complete']);
    exit();
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Generate the order number
    $today = date('d/m'); // Format: day/month (e.g., 10/04)

    // Get the count of orders for this company today
    $stmt_count = $conn->prepare("SELECT COUNT(*) as order_count FROM orders 
                                WHERE company_id = ? AND DATE(time) = CURDATE()");
    $stmt_count->bind_param("i", $company_id);
    $stmt_count->execute();
    $result = $stmt_count->get_result();
    $row = $result->fetch_assoc();
    $order_count = $row['order_count'] + 1; // Add 1 for this new order

    // Format the order number with leading zeros
    $orderNo = $today . '/' . str_pad($order_count, 3, '0', STR_PAD_LEFT);

    // Insert into orders table with the generated orderNo
    $stmt = $conn->prepare("INSERT INTO orders (company_id, created_by, customer_name, status, time, profit, total, orderNo) 
                            VALUES (?, ?, ?, 'created', NOW(), ?, ?, ?)");
    $stmt->bind_param("iisdds", $company_id, $created_by, $customer_name, $total_profit, $total, $orderNo);

    if (!$stmt->execute()) {
        throw new Exception("Database error while inserting order: " . $stmt->error);
    }

    $order_id = $conn->insert_id;

    // Loop through each item in the order list 
    foreach ($orderList as $item) {
        $product_id = $item['product_id'];
        $item_name = $item['name'];
        $quantity = $item['quantity'];
        $buying_price = $item['buying_price'];
        $selling_price = $item['price'];
        $sum = $item['sum'];
        $unit_type = $item['unit_type'] ?? 'whole';
        $unit_id = null; // Initialize as null
        
        // Determine 'sold_in' value and unit_id based on unit type
        if ($unit_type === 'whole') {
            $sold_in = 'whole';
        } else {
            $unit_id = $item['unit_id'];
            $stmt_unit = $conn->prepare("SELECT name FROM units WHERE unit_id = ?");
            $stmt_unit->bind_param("i", $unit_id);
            $stmt_unit->execute();
            $result_unit = $stmt_unit->get_result();
            $unit_row = $result_unit->fetch_assoc();
            if (!$unit_row) {
                throw new Exception("Invalid unit ID: $unit_id");
            }
            $sold_in = $unit_row['name'];
            $stmt_unit->close();
        }

        // Insert into order_items table with unit_id
        $stmt = $conn->prepare("INSERT INTO order_items 
            (order_id, product_id, company_id, created_by, name, quantity, 
            buying_price, selling_price, sum, sold_in, unit_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisiddssi", 
            $order_id, $product_id, $company_id, $created_by, $item_name, 
            $quantity, $buying_price, $selling_price, $sum, $sold_in, $unit_id);
        $stmt->execute();

        // Inventory update logic remains the same
        if ($unit_type === 'whole') {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("SELECT per_single_quantity FROM units WHERE unit_id = ?");
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $unit_data = $result->fetch_assoc();

            if (!$unit_data || $unit_data['per_single_quantity'] <= 0) {
                throw new Exception("Invalid unit data, per_single_quantity cannot be zero.");
            }

            $quantity_reduction = $quantity / $unit_data['per_single_quantity'];
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param("di", $quantity_reduction, $product_id);
            $stmt->execute();
        }
    }

    // Insert transaction
    $transaction_sql = "INSERT INTO transactions (transaction_type, transType_id, amount, company_id, created_by, date_made, description) 
        VALUES ('sale', ?, ?, ?, ?, NOW() ,'sale')";
    $stmt = $conn->prepare($transaction_sql);
    $stmt->bind_param("idii", $order_id, $total, $company_id, $created_by);
    $stmt->execute();
    $transaction_id = $conn->insert_id;


    // Fetch current money values for the company
    $fetch_money_sql = "SELECT * FROM money WHERE company_id = ?";
    $money_stmt = $conn->prepare($fetch_money_sql);
    $money_stmt->bind_param("i", $company_id);
    $money_stmt->execute();
    $money_result = $money_stmt->get_result();
    
    if ($money_result->num_rows === 0) {
        throw new Exception("Money record for company not found!");
    }

    $money_data = $money_result->fetch_assoc();

    // Insert payment methods used & add money to respective accounts
    foreach ($paymentMethods as $payment) {
        $method = str_replace(" ", "_", $payment['method']); // Convert method to match column name
        $amount = $payment['amount'];

        if (!isset($money_data[$method])) {
            throw new Exception("Invalid payment method: " . $payment['method']);
        }

        $money_data[$method] += $amount;

        // Insert into methods_used table
        $insert_method_sql = "INSERT INTO methods_used (transaction_id, payment_method, partial_amount, total_amount) 
                              VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_method_sql);
        $stmt->bind_param("ssdd", $transaction_id, $payment['method'], $amount, $total);
        $stmt->execute();
    }

    // Update money table with the new balances
    $update_money_sql = "UPDATE money SET cash = ?, NMB = ?, CRDB = ?, NBC = ?, mpesa = ?, tigo_pesa = ?, airtel_money = ?, halo_pesa = ? WHERE company_id = ?";
    $stmt = $conn->prepare($update_money_sql);
    $stmt->bind_param("ddddddddi", 
        $money_data['cash'], 
        $money_data['NMB'], 
        $money_data['CRDB'], 
        $money_data['NBC'], 
        $money_data['mpesa'], 
        $money_data['tigo_pesa'], 
        $money_data['airtel_money'], 
        $money_data['halo_pesa'], 
        $company_id
    );
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Order completed successfully!']);

} catch (Exception $e) {
    $conn->rollback(); // Rollback changes in case of an error
    error_log("Error in complete-order.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close database connection
if (isset($stmt)) $stmt->close();
if (isset($stmt_count)) $stmt_count->close();
$conn->close();
?>