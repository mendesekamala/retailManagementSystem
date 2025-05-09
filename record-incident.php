<?php
include('db_connection.php');
session_start();

$logFile = 'incident_errors.log';

function logError($message, $stmt = null) {
    global $logFile;
    $error = $stmt ? $stmt->error : '';
    $fullMessage = date("[Y-m-d H:i:s] ") . $message;
    if ($error) {
        $fullMessage .= " | MySQL Error: " . $error;
    }
    $fullMessage .= "\n";
    error_log($fullMessage, 3, $logFile);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $created_by = $_SESSION['user_id'];
    $company_id = $_SESSION['company_id'];

    $conn->begin_transaction();

    foreach ($data['incidentList'] as $incident) {
        if ($incident['unit_type'] === 'whole') {

            // Get current stock and price
            $stmt = $conn->prepare("SELECT quantity, buying_price FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $incident['product_id']);
            if (!$stmt->execute()) {
                logError("Failed to fetch product data", $stmt);
                throw new Exception("Failed to fetch product data");
            }
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $current_quantity = $row['quantity'];
            $buying_price = $row['buying_price'];

            if ($incident['quantity_destroyed'] > $current_quantity) {
                throw new Exception("Cannot destroy more quantity than available in stock (whole).");
            }

            // Reduce product quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param('di', $incident['quantity_destroyed'], $incident['product_id']);
            if (!$stmt->execute()) {
                logError("Failed to update products quantity", $stmt);
                throw new Exception("Failed to update products quantity");
            }

            // Calculate new quantity for units update
            $new_quantity = $current_quantity - $incident['quantity_destroyed'];
            
            // Update units table
            $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
            $stmt->bind_param("di", $new_quantity, $incident['product_id']);
            if (!$stmt->execute()) {
                logError("Failed to update available units", $stmt);
                throw new Exception("Failed to update available units");
            }

            // Log quantity destroyed
            $stmt = $conn->prepare("INSERT INTO quantity_destroyed (created_by, company_id, product_id, name, quantity_destroyed, date_destroyed) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('iiisi', $created_by, $company_id, $incident['product_id'], $incident['name'], $incident['quantity_destroyed']);
            if (!$stmt->execute()) {
                logError("Failed to insert into quantity_destroyed", $stmt);
                throw new Exception("Failed to insert into quantity_destroyed");
            }
            $qnt_dstr_id = $stmt->insert_id;

            // Record transaction
            $amount = $buying_price * $incident['quantity_destroyed'];
            $stmt = $conn->prepare("INSERT INTO transactions (transType_id, company_id, created_by, transaction_type, amount, description, date_made) VALUES (?, ?, ?, 'destruction', ?, 'Destruction loss', NOW())");
            $stmt->bind_param('iiid', $qnt_dstr_id, $company_id, $created_by, $amount);
            if (!$stmt->execute()) {
                logError("Failed to insert into transactions", $stmt);
                throw new Exception("Failed to insert into transactions");
            }

        } elseif ($incident['unit_type'] === 'unit') {

            // Get unit data
            $stmt = $conn->prepare("SELECT per_single_quantity, buying_price, product_id FROM units WHERE unit_id = ?");
            $stmt->bind_param("i", $incident['unit_id']);
            if (!$stmt->execute()) {
                logError("Failed to fetch unit data", $stmt);
                throw new Exception("Failed to fetch unit data");
            }
            $result = $stmt->get_result();
            $unit_data = $result->fetch_assoc();

            if ($unit_data['per_single_quantity'] <= 0) {
                throw new Exception("Invalid unit data, per_single_quantity cannot be zero.");
            }

            $quantity_reduction = $incident['units_destroyed'] / $unit_data['per_single_quantity'];

            // Get current stock
            $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $incident['product_id']);
            if (!$stmt->execute()) {
                logError("Failed to fetch product quantity (unit type)", $stmt);
                throw new Exception("Failed to fetch product quantity (unit type)");
            }
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $current_quantity = $row['quantity'];

            if ($quantity_reduction > $current_quantity) {
                throw new Exception("Cannot destroy more quantity than available in stock (unit).");
            }

            // Reduce product quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param("di", $quantity_reduction, $incident['product_id']);
            if (!$stmt->execute()) {
                logError("Failed to update product quantity (unit type)", $stmt);
                throw new Exception("Failed to update product quantity (unit type)");
            }

            // Calculate new quantity for units update
            $new_quantity = $current_quantity - $quantity_reduction;
            
            // Update units table
            $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
            $stmt->bind_param("di", $new_quantity, $incident['product_id']);
            if (!$stmt->execute()) {
                logError("Failed to update available units (unit type)", $stmt);
                throw new Exception("Failed to update available units (unit type)");
            }

            // Log units destroyed
            $stmt = $conn->prepare("INSERT INTO units_destroyed (created_by, company_id, unit_id, product_id, name, units_destroyed, date_destroyed) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('iiiisi', $created_by, $company_id, $incident['unit_id'], $incident['product_id'], $incident['name'], $incident['units_destroyed']);
            if (!$stmt->execute()) {
                logError("Failed to insert into units_destroyed", $stmt);
                throw new Exception("Failed to insert into units_destroyed");
            }
            $unt_dstr_id = $stmt->insert_id;

            // Record transaction
            $amount = $unit_data['buying_price'] * $incident['units_destroyed'];
            $stmt = $conn->prepare("INSERT INTO transactions (transType_id, company_id, created_by, transaction_type, amount, description, date_made) VALUES (?, ?, ?, 'destruction', ?, 'Destruction loss', NOW())");
            $stmt->bind_param('iiid', $unt_dstr_id, $company_id, $created_by, $amount);
            if (!$stmt->execute()) {
                logError("Failed to insert into transactions (unit type)", $stmt);
                throw new Exception("Failed to insert into transactions (unit type)");
            }
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Incident reported successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    logError("Transaction rolled back due to error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error occurred while reporting incident. Check logs.']);
}
?>