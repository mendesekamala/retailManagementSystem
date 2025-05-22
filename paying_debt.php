<?php
session_start();
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid data!"]);
    exit;
}

// Check if debt_id is provided
if (!isset($data['debt_id'])) {
    echo json_encode(["status" => "error", "message" => "Debt ID is required!"]);
    exit;
}

$debtorName = $data['debtorName'];
$paymentAmount = $data['paymentAmount'];
$dueAmount = $data['dueAmount'];
$paymentMethods = $data['paymentMethods'];
$debt_id = $data['debt_id'];
$company_id = $_SESSION['company_id'];
$created_by = $_SESSION['user_id'];

$conn->begin_transaction();

try {
    // 1. Get the specific debt to pay
    $getDebtQuery = "SELECT debt_id, due_amount FROM debt_payments 
                    WHERE debt_id = ? AND company_id = ? AND name = ? 
                    AND debtor_creditor = 'debtor' AND due_amount > 0";
    $stmt = $conn->prepare($getDebtQuery);
    $stmt->bind_param("iis", $debt_id, $company_id, $debtorName);
    $stmt->execute();
    $debt = $stmt->get_result()->fetch_assoc();
    
    if (!$debt) {
        throw new Exception("Debt not found or already paid");
    }
    
    $amountToPay = min($debt['due_amount'], $paymentAmount);
    
    // 2. Update the debt record
    $updateDebtQuery = "UPDATE debt_payments SET due_amount = due_amount - ? 
                       WHERE debt_id = ?";
    $stmt = $conn->prepare($updateDebtQuery);
    $stmt->bind_param("di", $amountToPay, $debt_id);
    $stmt->execute();
    
    // 3. Record the transaction
    $insertTransactionQuery = "INSERT INTO transactions 
        (transaction_type, transType_id, amount, company_id, created_by, date_made, description) 
        VALUES ('debtors', 0, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($insertTransactionQuery);
    $description = "$debtorName debt no $debt_id";
    $stmt->bind_param("diis", $paymentAmount, $company_id, $created_by, $description);
    $stmt->execute();
    $transaction_id = $conn->insert_id;  // Fixed method name
    
    // 4. Record payment methods used and update money table
    $fetchMoneyQuery = "SELECT * FROM money WHERE company_id = ?";
    $stmt = $conn->prepare($fetchMoneyQuery);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $moneyData = $stmt->get_result()->fetch_assoc();
    
    foreach ($paymentMethods as $payment) {
        $method = str_replace(" ", "_", $payment['method']);
        $amount = $payment['amount'];
        
        // Record payment method used
        $insertMethodQuery = "INSERT INTO methods_used 
            (transaction_id, payment_method, partial_amount, total_amount) 
            VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertMethodQuery);
        $stmt->bind_param("isdd", $transaction_id, $payment['method'], $amount, $paymentAmount);
        $stmt->execute();
        
        // Update money balance for this method
        if (isset($moneyData[$method])) {
            $moneyData[$method] += $amount;
        }
    }
    
    // Update money table with new balances
    $updateMoneyQuery = "UPDATE money SET 
        cash = ?, NMB = ?, CRDB = ?, NBC = ?, mpesa = ?, tigo_pesa = ?, airtel_money = ?, halo_pesa = ? 
        WHERE company_id = ?";
    $stmt = $conn->prepare($updateMoneyQuery);
    $stmt->bind_param("ddddddddi", 
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
    $stmt->execute();
    
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Payment recorded successfully!"]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Payment failed: " . $e->getMessage()]);
}