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

// TEMP: Log the full incoming data for debugging
file_put_contents('debug_order_input.txt', print_r($data, true));

$original_order_id = $data['original_order_id'];
$orderList = $data['orderList'];
$total = $data['total_amount'];
$paymentMethods = $data['paymentMethods'];
$company_id = $_SESSION['company_id'];
$created_by = $_SESSION['user_id'];

try {
    // At the start of the try block:
    error_log("Starting order edit for order ID: $original_order_id, company: $company_id");

    // Before each major operation:
    error_log("Inserting new order record...");
    error_log("Processing order items...");
    error_log("Updating inventory...");
    error_log("Creating transaction record...");
    error_log("Updating payment methods and money...");

    // Begin transaction
    $conn->begin_transaction();

    // 1. Get the original order details (including orderNo and customer_name)
    $stmt_order = $conn->prepare("SELECT orderNo, customer_name FROM orders WHERE order_id = ? AND company_id = ?");
    $stmt_order->bind_param("ii", $original_order_id, $company_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    
    if ($order_result->num_rows === 0) {
        throw new Exception("Original order not found!");
    }
    
    $order_data = $order_result->fetch_assoc();
    $orderNo = $order_data['orderNo'];
    $customer_name = $order_data['customer_name'];

    // 2. Calculate total profit from the edited items
    $total_profit = 0;
    foreach ($orderList as $item) {
        $total_profit += ($item['price'] - $item['buying_price']) * $item['quantity'];
    }

    // 3. Insert new order record with the same orderNo
    $stmt = $conn->prepare("INSERT INTO orders (company_id, created_by, customer_name, status, time, profit, total, orderNo) 
                            VALUES (?, ?, ?, 'created', NOW(), ?, ?, ?)");
    $stmt->bind_param("iisdds", $company_id, $created_by, $customer_name, $total_profit, $total, $orderNo);

    if (!$stmt->execute()) {
        throw new Exception("Database error while inserting order: " . $stmt->error);
    }

    $new_order_id = $conn->insert_id;

    // 4. Insert new order items and update inventory
    foreach ($orderList as $item) {
        $product_id = $item['product_id'];
        $item_name = $item['name'];
        $quantity = $item['quantity'];
        $buying_price = $item['buying_price'];
        $selling_price = $item['price'];
        $sum = $item['sum'];
        $unit_type = $item['unit_type'] ?? 'whole';
        $sold_in = $item['sold_in'];
        $unit_id = $item['unit_id'] ?? null;

        // Insert into order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, company_id, created_by, name, quantity, buying_price, selling_price, sum, sold_in) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisiddss", $new_order_id, $product_id, $company_id, $created_by, $item_name, $quantity, $buying_price, $selling_price, $sum, $sold_in);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order item: " . $stmt->error);
        }

        // Inventory update logic with enhanced error handling
        try {
            if ($unit_type === 'whole' || empty($sold_in) || $sold_in === 'whole') {
                error_log("Updating as whole product - Product ID: $product_id, Qty: $quantity");
                $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
                $stmt->bind_param("di", $quantity, $product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Whole product update failed: " . $stmt->error);
                }
            } else {
                error_log("Updating as unit - Product ID: $product_id, Unit: $sold_in, Unit ID: " . ($unit_id ?? 'null'));
                
                // Try to get conversion factor using unit_id if available
                if ($unit_id) {
                    $stmt = $conn->prepare("SELECT per_single_quantity FROM units WHERE unit_id = ?");
                    $stmt->bind_param("i", $unit_id);
                } else {
                    // Fallback to product_id + unit name
                    $stmt = $conn->prepare("SELECT per_single_quantity FROM units WHERE product_id = ? AND name = ? AND company_id = ?");
                    $stmt->bind_param("isi", $product_id, $sold_in, $company_id);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Unit query failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $per_single = 1; // Default fallback
                
                if ($result->num_rows > 0) {
                    $unit_data = $result->fetch_assoc();
                    $per_single = $unit_data['per_single_quantity'] ?? 1;
                    if ($per_single <= 0) {
                        error_log("Invalid per_single_quantity ($per_single) - using 1 as fallback");
                        $per_single = 1;
                    }
                } else {
                    error_log("Unit '$sold_in' not found - using whole product calculation");
                }
                
                $quantity_reduction = $quantity * $per_single;
                error_log("Calculated quantity reduction: $quantity_reduction");
                
                $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
                $stmt->bind_param("di", $quantity_reduction, $product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Unit inventory update failed: " . $stmt->error);
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            error_log("TRANSACTION ROLLBACK: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // More detailed error response
            $errorData = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ],
                'input_data' => [
                    'order_id' => $original_order_id,
                    'item_count' => count($orderList),
                    'payment_methods' => $paymentMethods
                ]
            ];
            
            echo json_encode($errorData);
        }
    }
    // 5. Insert new transaction record
    $transaction_sql = "INSERT INTO transactions (transaction_type, transType_id, amount, company_id, created_by, date_made) 
        VALUES ('sale', ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($transaction_sql);
    $stmt->bind_param("idii", $new_order_id, $total, $company_id, $created_by);
    $stmt->execute();
    $transaction_id = $conn->insert_id;

    // 6. Fetch current money values for the company
    $fetch_money_sql = "SELECT * FROM money WHERE company_id = ?";
    $money_stmt = $conn->prepare($fetch_money_sql);
    $money_stmt->bind_param("i", $company_id);
    $money_stmt->execute();
    $money_result = $money_stmt->get_result();
    
    if ($money_result->num_rows === 0) {
        throw new Exception("Money record for company not found!");
    }

    $money_data = $money_result->fetch_assoc();

    // 7. Insert payment methods used & update money accounts
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

    // 8. Update money table with the new balances
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

    echo json_encode([
        'status' => 'success', 
        'message' => 'Order updated successfully!',
        'new_order_id' => $new_order_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in finish_editing-order.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close database connection
if (isset($stmt)) $stmt->close();
if (isset($stmt_order)) $stmt_order->close();
$conn->close();
?>

