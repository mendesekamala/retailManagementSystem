<?php
session_start();
include('db_connection.php');

// Ensure the request is POST and contains a JSON body
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(file_get_contents('php://input'))) {
    $input = json_decode(file_get_contents('php://input'), true);
    $company_id = $input['company_id'];

    if ($company_id) {
        $query = "SELECT * FROM payment_methods WHERE company_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $methods = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($row as $key => $value) {
                    if ($value === 'yes') {
                        $methods[] = ['value' => $key, 'label' => ucfirst(str_replace('_', ' ', $key))];
                    }
                }
            }
            echo json_encode(['success' => true, 'methods' => $methods]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No payment methods found.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing company_id.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?>
