<?php
session_start();
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid data!"]);
    exit;
}

$product_id = $data['product_id'];
$quantity = $data['quantity'];
$buying_price = $data['buying_price'];
$selling_price = $data['selling_price'];
$supplier_name = $data['supplier_name'] ?? null;
$company_id = $_SESSION['company_id'];
$created_by = $_SESSION['user_id'];
$grandTotal = $data['grandTotal'];
$paymentMethods = $data['paymentMethods'];

$conn->begin_transaction();

try {
    // Update product quantity
    $update_product_sql = "UPDATE products SET buying_price = ?, selling_price = ?, quantity = quantity + ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_product_sql);
    $stmt->bind_param("ddii", $buying_price, $selling_price, $quantity, $product_id);
    $stmt->execute();

    // Update units table based on the updated quantity
    $fetch_units_sql = "SELECT unit_id, available_units, per_single_quantity FROM units WHERE product_id = ?";
    $units_stmt = $conn->prepare($fetch_units_sql);
    $units_stmt->bind_param("i", $product_id);
    $units_stmt->execute();
    $units_result = $units_stmt->get_result();

    while ($unit = $units_result->fetch_assoc()) {
        $unit_id = $unit['unit_id'];
        $available_units = $unit['available_units'];
        $per_single_quantity = $unit['per_single_quantity'];

        $units_to_add = $per_single_quantity * $quantity;
        $new_available_units = $available_units + $units_to_add;

        $update_units_sql = "UPDATE units SET available_units = ? WHERE unit_id = ?";
        $update_units_stmt = $conn->prepare($update_units_sql);
        $update_units_stmt->bind_param("di", $new_available_units, $unit_id);
        $update_units_stmt->execute();
    }

    // Insert purchase record with supplier name
    $insert_purchase_sql = "INSERT INTO purchases_items (product_id, quantity, buying_price, selling_price, total, date_made, company_id, created_by, supplier_name) 
    VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($insert_purchase_sql);
    $stmt->bind_param("iidddiis", $product_id, $quantity, $buying_price, $selling_price, $grandTotal, $company_id, $created_by, $supplier_name);
    $stmt->execute();
    $purchase_item_id = $conn->insert_id;

    // Insert transaction with transType_id as purchase_item_id
    $transaction_sql = "INSERT INTO transactions (transaction_type, transType_id, amount, company_id, created_by, date_made, description) 
    VALUES ('purchase', ?, ?, ?, ?, NOW(), 'purchase')";
    $stmt = $conn->prepare($transaction_sql);
    $stmt->bind_param("idii", $purchase_item_id, $grandTotal, $company_id, $created_by);
    $stmt->execute();
    $transaction_id = $conn->insert_id;

    // Check for debt payment and record it
    $debtAmount = 0;
    foreach ($paymentMethods as $payment) {
        if (strtolower($payment['method']) === 'debt') {
            $debtAmount = $payment['amount'];
            break;
        }
    }

    if ($debtAmount > 0) {
        $insert_debt_sql = "INSERT INTO debt_payments (company_id, created_by, transaction_id, total, name) 
                            VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_debt_sql);
        $stmt->bind_param("iiids", $company_id, $created_by, $transaction_id, $debtAmount, $supplier_name);
        $stmt->execute();
    }

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

    // Insert payment methods used & Deduct money from respective accounts
    foreach ($paymentMethods as $payment) {
        $method = str_replace(" ", "_", $payment['method']);
        $amount = $payment['amount'];

        if (!isset($money_data[$method])) {
            throw new Exception("Invalid payment method: " . $payment['method']);
        }

        // Skip deduction for debt payments
        if (strtolower($payment['method']) === 'debt') {
            continue;
        }

        if ($money_data[$method] < $amount) {
            throw new Exception("Insufficient balance in " . $payment['method']);
        }

        $new_balance = $money_data[$method] - $amount;
        $money_data[$method] = $new_balance;

        $insert_method_sql = "INSERT INTO methods_used (transaction_id, payment_method, partial_amount, total_amount) 
                              VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_method_sql);
        $stmt->bind_param("ssdd", $transaction_id, $payment['method'], $amount, $grandTotal);
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

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Transaction completed successfully!"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Transaction failed! Error: " . $e->getMessage()]);
}