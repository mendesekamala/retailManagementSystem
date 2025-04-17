<?php
session_start();
include 'db_connection.php';

// Get the JSON data sent from the frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid data!"]);
    exit;
}

// Extract data from the request
$transactionType = $data['transactionType'];
$description = $data['description'];
$amount = $data['amount'];
$company_id = $_SESSION['company_id'];
$created_by = $_SESSION['user_id'];
$paymentMethods = $data['paymentMethods'];

// Validate transaction type
$validTypes = ['expenses', 'drawings', 'add_capital'];
if (!in_array($transactionType, $validTypes)) {
    echo json_encode(["status" => "error", "message" => "Invalid transaction type!"]);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Insert the transaction (with transType_id as NULL)
    $transaction_sql = "INSERT INTO transactions 
                        (transaction_type, transType_id, amount, company_id, created_by, date_made, description) 
                        VALUES (?, NULL, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($transaction_sql);
    $stmt->bind_param("sdiis", $transactionType, $amount, $company_id, $created_by, $description);
    $stmt->execute();
    $transaction_id = $conn->insert_id;

    // 2. Fetch current money values for the company
    $fetch_money_sql = "SELECT * FROM money WHERE company_id = ?";
    $money_stmt = $conn->prepare($fetch_money_sql);
    $money_stmt->bind_param("i", $company_id);
    $money_stmt->execute();
    $money_result = $money_stmt->get_result();
    
    if ($money_result->num_rows === 0) {
        throw new Exception("Money record for company not found!");
    }

    $money_data = $money_result->fetch_assoc();

    // 3. Process each payment method
    foreach ($paymentMethods as $payment) {
        $method = str_replace(" ", "_", $payment['method']); // Convert method to match column name
        $amount = $payment['amount'];

        // Validate payment method exists in money table
        if (!isset($money_data[$method])) {
            throw new Exception("Invalid payment method: " . $payment['method']);
        }

        // For expenses and drawings, check sufficient balance
        if (in_array($transactionType, ['expenses', 'drawings'])) {
            if ($money_data[$method] < $amount) {
                throw new Exception("Insufficient balance in " . $payment['method']);
            }
            // Deduct the amount
            $money_data[$method] -= $amount;
        } 
        // For add_capital, add the amount
        elseif ($transactionType === 'add_capital') {
            $money_data[$method] += $amount;
        }

        // Insert into methods_used table
        $insert_method_sql = "INSERT INTO methods_used 
                             (transaction_id, payment_method, partial_amount, total_amount) 
                             VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_method_sql);
        $stmt->bind_param("isdd", $transaction_id, $payment['method'], $amount, $data['amount']);
        $stmt->execute();
    }

    // 4. Update money table with the new balances
    $update_money_sql = "UPDATE money SET 
                        cash = ?, 
                        NMB = ?, 
                        CRDB = ?, 
                        NBC = ?, 
                        mpesa = ?, 
                        tigo_pesa = ?, 
                        airtel_money = ?, 
                        halo_pesa = ?,
                        azam_pesa = ?,
                        debt = ?
                        WHERE company_id = ?";
    
    $stmt = $conn->prepare($update_money_sql);
    $stmt->bind_param("ddddddddddi", 
        $money_data['cash'], 
        $money_data['NMB'], 
        $money_data['CRDB'], 
        $money_data['NBC'], 
        $money_data['mpesa'], 
        $money_data['tigo_pesa'], 
        $money_data['airtel_money'], 
        $money_data['halo_pesa'],
        $money_data['azam_pesa'],
        $money_data['debt'],
        $company_id
    );
    $stmt->execute();

    // Commit the transaction if all operations succeeded
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Transaction recorded successfully!"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Transaction failed! Error: " . $e->getMessage()]);
}
?>