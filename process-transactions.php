<?php
// Include database connection
include('db_connection.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get transaction data from the form
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Insert the transaction details into the transactions table
    $insert_transaction_query = "INSERT INTO transactions (transaction_type, amount, description, date_made) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_transaction_query);

    // Check if the statement preparation was successful
    if (!$stmt) {
        // Return an error response as JSON
        echo json_encode(['success' => false, 'message' => "Prepare failed: (" . $conn->errno . ") " . $conn->error]);
        exit;
    }

    // Bind the parameters
    $stmt->bind_param("sds", $transaction_type, $amount, $description);

    if ($stmt->execute()) {
        // Fetch the current business cash
        $cash_query = "SELECT cash FROM money WHERE money_id = 1";
        $result = $conn->query($cash_query);

        if ($result && $result->num_rows > 0) {
            $current_cash = $result->fetch_assoc()['cash'];

            // Update the cash balance depending on the transaction type
            if ($transaction_type === 'expenses' || $transaction_type === 'drawings') {
                $new_cash = $current_cash - $amount;
            } elseif ($transaction_type === 'add_capital') {
                $new_cash = $current_cash + $amount;
            }

            // Ensure the new cash balance is not negative for expenses/drawings
            if ($new_cash >= 0) {
                // Update the cash balance in the money table
                $update_cash_query = "UPDATE money SET cash = ? WHERE money_id = 1";
                $stmt = $conn->prepare($update_cash_query);

                if (!$stmt) {
                    echo json_encode(['success' => false, 'message' => "Prepare failed: (" . $conn->errno . ") " . $conn->error]);
                    exit;
                }

                $stmt->bind_param("d", $new_cash);
                $stmt->execute();

                // Return success response as JSON
                echo json_encode(['success' => true, 'new_cash' => number_format($new_cash, 2)]);
            } else {
                // Return error if cash is insufficient
                echo json_encode(['success' => false, 'message' => "Insufficient business cash for this transaction."]);
            }
        } else {
            // Return error if cash fetching failed
            echo json_encode(['success' => false, 'message' => "Error fetching current cash."]);
        }
    } else {
        // Return error if transaction insertion failed
        echo json_encode(['success' => false, 'message' => "Error inserting the transaction. Please try again."]);
    }
}
?>
