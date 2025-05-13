<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
require_once '../db_connection.php';

// Get parameters from query
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$page = $_GET['page'] ?? 1;
$itemsPerPage = $_GET['per_page'] ?? 10;
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

try {
    // Calculate offset for pagination
    $offset = ($page - 1) * $itemsPerPage;
    
    // Base query for orders
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
    ";
    
    // Add search and status filters if provided
    $params = [$startDate, $endDate];
    $types = 'ss';
    
    if (!empty($searchTerm)) {
        $query .= " AND (orderNo LIKE ? OR customer_name LIKE ?)";
        $searchParam = "%$searchTerm%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ss';
    }
    
    if (!empty($statusFilter)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
        $types .= 's';
    }
    
    // Add sorting and pagination
    $query .= " ORDER BY time DESC LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $types .= 'ii';
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM orders WHERE DATE(time) BETWEEN ? AND ?";
    $countParams = [$startDate, $endDate];
    $countTypes = 'ss';
    
    if (!empty($searchTerm)) {
        $countQuery .= " AND (orderNo LIKE ? OR customer_name LIKE ?)";
        $countParams[] = "%$searchTerm%";
        $countParams[] = "%$searchTerm%";
        $countTypes .= 'ss';
    }
    
    if (!empty($statusFilter)) {
        $countQuery .= " AND status = ?";
        $countParams[] = $statusFilter;
        $countTypes .= 's';
    }
    
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    
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
    $countStmt->close();
    $summaryStmt->close();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'summary' => $summary,
        'pagination' => [
            'total_items' => $totalCount,
            'current_page' => $page,
            'items_per_page' => $itemsPerPage,
            'total_pages' => ceil($totalCount / $itemsPerPage)
        ]
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