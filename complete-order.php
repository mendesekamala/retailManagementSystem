<?php
include('db_connection.php'); // Connection to the database
session_start(); // Start the session to access session data

// Check if the user is logged in and has session data
if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id'])) {
    echo "User not logged in or session data is missing.";
    exit(); // Exit if no session data
}

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Get the order data from the request
$orderList = $data['orderList'];
$total = $data['total'];
$total_profit = $data['total_profit'];
$customer_name = $data['customer_name'];
$payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
$debt_amount = isset($data['debt_amount']) ? $data['debt_amount'] : 0;
$company_id = $_SESSION['company_id'];
$created_by = $_SESSION['user_id'];

if (count($orderList) > 0) {
    // Insert order into 'orders' table, including company_id and created_by
    $stmt = $conn->prepare("INSERT INTO orders (company_id, created_by, total, time, status, customer_name, payment_method, debt_amount, profit) 
                            VALUES (?, ?, ?, NOW(), 'created', ?, ?, ?, ?)");
    $stmt->bind_param("iidssdd", $company_id, $created_by, $total, $customer_name, $payment_method, $debt_amount, $total_profit);
    $stmt->execute();

    // Get the last inserted order_id
    $order_id = $conn->insert_id;

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
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, company_id, created_by, name, quantity, buying_price, selling_price, sum, sold_in) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisiddss", $order_id, $product_id, $company_id, $created_by, $item_name, $quantity, $buying_price, $selling_price, $sum, $sold_in);
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

    // Insert the transaction into the 'transactions' table, including company_id and created_by
    $stmt = $conn->prepare("INSERT INTO transactions (transaction_type, amount, description, date_made, company_id, created_by) 
                            VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("sdsii", $transaction_type, $amount, $description, $company_id, $created_by);

    // Set values for the transaction
    $transaction_type = 'sale';        // The type of transaction is 'sale'
    $amount = $total;                  // The total amount of the order
    $description = $customer_name;     // The description is the customer's name

    $stmt->execute();
    $stmt->close();

    // Handling payment methods
    if ($payment_method === 'double') {
        // Double payment scenario
        $payment_one_method = $data['payment_one']['method'];
        $payment_one_amount = $data['payment_one']['amount'];
        $payment_two_method = $data['payment_two']['method'];
        $payment_two_amount = $data['payment_two']['amount'];

        // Update the money table based on the payment methods
        $stmt = $conn->prepare("UPDATE money SET {$payment_one_method} = {$payment_one_method} + ? WHERE company_id = ?");
        $stmt->bind_param("di", $payment_one_amount, $company_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE money SET {$payment_two_method} = {$payment_two_method} + ? WHERE company_id = ?");
        $stmt->bind_param("di", $payment_two_amount, $company_id);
        $stmt->execute();
    } else {
        // Single payment scenario
        if ($payment_method !== 'debt') {
            $stmt = $conn->prepare("UPDATE money SET {$payment_method} = {$payment_method} + ? WHERE company_id = ?");
            $stmt->bind_param("di", $total, $company_id);
            $stmt->execute();
        } else {
            // Update debt if debt payment method is used
            $stmt = $conn->prepare("UPDATE money SET debt = debt + ? WHERE company_id = ?");
            $stmt->bind_param("di", $debt_amount, $company_id);
            $stmt->execute();
        }
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No items to complete']);
}

$stmt->close();
$conn->close();
?>
