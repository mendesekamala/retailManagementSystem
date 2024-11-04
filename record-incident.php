<!-- record-incident.php -->
<?php
include('db_connection.php');
$data = json_decode(file_get_contents('php://input'), true);

foreach ($data['incidentList'] as $incident) {
    if ($incident['unit_type'] === 'whole') {
        // Update products table for whole quantity
        $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
        $stmt->bind_param('di', $incident['quantity_destroyed'], $incident['product_id']);
        $stmt->execute();

        // Update available units for all related units based on the current product quantity
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $incident['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_quantity = $row['quantity'];

        $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
        $stmt->bind_param("di", $current_quantity, $incident['product_id']);
        $stmt->execute();

        // Insert into quantity_destroyed table
        $stmt = $conn->prepare("INSERT INTO quantity_destroyed (product_id, name, quantity_destroyed, date_destroyed) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('isd', $incident['product_id'], $incident['name'], $incident['quantity_destroyed']);
        $stmt->execute();

    } else if ($incident['unit_type'] === 'unit') {

        // If incident is in units (e.g., kg, half_kg)
        $unit_id = $incident['unit_id']; // Get unit_id from the passed data

        $stmt = $conn->prepare("SELECT per_single_quantity FROM units WHERE unit_id = ?");
        $stmt->bind_param("i", $unit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $unit_data = $result->fetch_assoc();

        // Avoid division by zero by checking per_single_quantity
        if ($unit_data['per_single_quantity'] > 0) {
            // Calculate the reduction in product quantity based on the unit relationship
            $quantity_reduction = $incident['units_destroyed'] / $unit_data['per_single_quantity'];

            // Update product quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->bind_param("di", $quantity_reduction, $incident['product_id']);
            $stmt->execute();

            // Update available units for all related units based on the current product quantity
            $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $incident['product_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $current_quantity = $row['quantity'];

            $stmt = $conn->prepare("UPDATE units SET available_units = per_single_quantity * ? WHERE product_id = ?");
            $stmt->bind_param("di", $current_quantity, $incident['product_id']);
            $stmt->execute();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid unit data, per_single_quantity cannot be zero.']);
            exit;
        }

        // Insert into units_destroyed table
        $stmt = $conn->prepare("INSERT INTO units_destroyed (unit_id, product_id, name, units_destroyed, date_destroyed) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('iisi', $incident['unit_id'], $incident['product_id'], $incident['name'], $incident['units_destroyed']);
        $stmt->execute();
    }
}

echo "Incident reported successfully!";
?>
