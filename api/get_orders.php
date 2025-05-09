<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
require_once '../db_connection.php';

// Get date range from query parameters
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    // Fetch ALL orders within date range (including cancelled)
    $query = "
        SELECT 
            order_id, 
            orderNo, 
            company_id, 
            created_by, 
            total, 
            time, 
            status, 
            customer_name, 
            profit 
        FROM 
            orders 
        WHERE 
            DATE(time) BETWEEN ? AND ?
        ORDER BY 
            time DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate summary statistics EXCLUDING cancelled orders
    $summaryQuery = "
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total), 0) as total_revenue,
            COALESCE(AVG(total), 0) as avg_order_value,
            COALESCE(SUM(profit), 0) as total_profit
        FROM 
            orders 
        WHERE 
            time >= ? AND time < DATE_ADD(?, INTERVAL 1 DAY)
            AND status != 'cancelled'
    ";

    $summaryStmt = $conn->prepare($summaryQuery);
    $summaryStmt->bind_param('ss', $startDate, $endDate);
    $summaryStmt->execute();
    $summaryResult = $summaryStmt->get_result();
    $summary = $summaryResult->fetch_assoc();

    // Close statements
    $stmt->close();
    $summaryStmt->close();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'orders' => $orders,          // Includes all orders for table display
        'summary' => $summary         // Excludes cancelled orders in calculations
    ]);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>


