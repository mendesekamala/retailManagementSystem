<?php
session_start();
include('db_connection.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get transaction data from the form
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $payment_method = $_POST['payment_method'];  // Get selected payment method

    // Fetch the current balance for the selected payment method from the money table
    $company_id = $_SESSION['company_id'];
    $created_by = $_SESSION['user_id'];  // Get the user ID from session
    $cash_query = "SELECT * FROM money WHERE company_id = ?";
    $result = $conn->prepare($cash_query);
    $result->bind_param("i", $company_id);
    $result->execute();
    $current_balance = $result->get_result()->fetch_assoc();

    // Check if there's sufficient funds for 'expenses' or 'drawings' transactions
    if (($transaction_type === 'expenses' || $transaction_type === 'drawings') && $current_balance[$payment_method] < $amount) {
        echo json_encode(['success' => false, 'message' => "Insufficient funds in the selected payment method."]);
        exit;
    }

    // If there are sufficient funds, proceed with the transaction
    $insert_transaction_query = "
        INSERT INTO transactions (
            transaction_type, amount, description, date_made, company_id, created_by, 
            completed_in, payment_method_one
        ) 
        VALUES (?, ?, ?, NOW(), ?, ?, 'full', ?)";
    $stmt = $conn->prepare($insert_transaction_query);

    // Check if the statement preparation was successful
    if (!$stmt) {
        // Return an error response as JSON
        echo json_encode(['success' => false, 'message' => "Prepare failed: (" . $conn->errno . ") " . $conn->error]);
        exit;
    }

    // Bind the parameters for the transaction insertion
    $stmt->bind_param("sdsiis", $transaction_type, $amount, $description, $company_id, $created_by, $payment_method);

    if ($stmt->execute()) {
        // Calculate the new balance based on the transaction type
        if ($transaction_type === 'add_capital') {
            // For 'add_capital', add the amount to the selected payment method
            $new_balance = $current_balance[$payment_method] + $amount;
        } elseif ($transaction_type === 'expenses' || $transaction_type === 'drawings') {
            // For 'expenses' or 'drawings', subtract the amount from the selected payment method
            $new_balance = $current_balance[$payment_method] - $amount;
        }

        // Update the value of the corresponding payment method in the `money` table
        $update_balance_query = "UPDATE money SET $payment_method = ? WHERE company_id = ?";
        $stmt_update = $conn->prepare($update_balance_query);
        $stmt_update->bind_param("di", $new_balance, $company_id);
        $stmt_update->execute();

        // Return success response with the updated balance
        echo json_encode(['success' => true, 'new_balance' => $new_balance]);
    } else {
        // Return error if transaction insertion failed
        echo json_encode(['success' => false, 'message' => "Error inserting the transaction. Please try again."]);
    }

}
?>
