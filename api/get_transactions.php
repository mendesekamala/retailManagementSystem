<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
require_once '../db_connection.php';

// Set timezone to Tanzania
date_default_timezone_set('Africa/Dar_es_Salaam');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has a company_id
if (!isset($_SESSION['company_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access - no company specified'
    ]);
    exit();
}

$company_id = $_SESSION['company_id'];

// Get parameters from query
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$page = $_GET['page'] ?? 1;
$itemsPerPage = $_GET['per_page'] ?? 10;
$searchTerm = $_GET['search'] ?? '';
$typeFilter = $_GET['type'] ?? '';

try {
    // Calculate offset for pagination
    $offset = ($page - 1) * $itemsPerPage;
    
    // Base query for transactions
    $query = "
        SELECT 
            transaction_id,
            transaction_type,
            amount,
            description,
            date_made
        FROM 
            transactions 
        WHERE 
            date_made >= ? AND date_made < DATE_ADD(?, INTERVAL 1 DAY)
            AND company_id = ?
    ";
    
    // Add search and type filters if provided
    $params = [$startDate, $endDate, $company_id];
    $types = 'ssi';
    
    if (!empty($searchTerm)) {
        $query .= " AND (description LIKE ? OR transaction_id LIKE ?)";
        $searchParam = "%$searchTerm%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ss';
    }
    
    if (!empty($typeFilter)) {
        $query .= " AND transaction_type = ?";
        $params[] = $typeFilter;
        $types .= 's';
    }
    
    // Add sorting and pagination
    $query .= " ORDER BY date_made DESC LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $types .= 'ii';
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM transactions WHERE date_made >= ? AND date_made < DATE_ADD(?, INTERVAL 1 DAY) AND company_id = ?";
    $countParams = [$startDate, $endDate, $company_id];
    $countTypes = 'ssi';
    
    if (!empty($searchTerm)) {
        $countQuery .= " AND (description LIKE ? OR transaction_id LIKE ?)";
        $countParams[] = "%$searchTerm%";
        $countParams[] = "%$searchTerm%";
        $countTypes .= 'ss';
    }
    
    if (!empty($typeFilter)) {
        $countQuery .= " AND transaction_type = ?";
        $countParams[] = $typeFilter;
        $countTypes .= 's';
    }
    
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    
    // Calculate summary statistics
    $summaryQuery = "
        SELECT 
            COUNT(*) as total_transactions,
            SUM(CASE WHEN transaction_type = 'sale' THEN 1 ELSE 0 END) as sales_count,
            SUM(CASE WHEN transaction_type = 'sale' THEN amount ELSE 0 END) as sales_amount,
            SUM(CASE WHEN transaction_type = 'purchase' THEN 1 ELSE 0 END) as purchases_count,
            SUM(CASE WHEN transaction_type = 'purchase' THEN amount ELSE 0 END) as purchases_amount,
            SUM(CASE WHEN transaction_type = 'drawings' THEN 1 ELSE 0 END) as drawings_count,
            SUM(CASE WHEN transaction_type = 'drawings' THEN amount ELSE 0 END) as drawings_amount,
            SUM(CASE WHEN transaction_type = 'expenses' THEN 1 ELSE 0 END) as expenses_count,
            SUM(CASE WHEN transaction_type = 'expenses' THEN amount ELSE 0 END) as expenses_amount,
            SUM(CASE WHEN transaction_type = 'add_capital' THEN 1 ELSE 0 END) as capital_count,
            SUM(CASE WHEN transaction_type = 'add_capital' THEN amount ELSE 0 END) as capital_amount,
            SUM(CASE WHEN transaction_type = 'debtors' THEN 1 ELSE 0 END) as debtors_count,
            SUM(CASE WHEN transaction_type = 'debtors' THEN amount ELSE 0 END) as debtors_amount,
            SUM(CASE WHEN transaction_type = 'creditors' THEN 1 ELSE 0 END) as creditors_count,
            SUM(CASE WHEN transaction_type = 'creditors' THEN amount ELSE 0 END) as creditors_amount,
            SUM(CASE WHEN transaction_type = 'destruction' THEN 1 ELSE 0 END) as destructions_count,
            SUM(CASE WHEN transaction_type = 'destruction' THEN amount ELSE 0 END) as destructions_amount,
            SUM(CASE WHEN transaction_type = 'refund' THEN 1 ELSE 0 END) as refund_count,
            SUM(CASE WHEN transaction_type = 'refund' THEN amount ELSE 0 END) as refund_amount
        FROM 
            transactions 
        WHERE 
            date_made >= ? AND date_made < DATE_ADD(?, INTERVAL 1 DAY)
            AND company_id = ?
    ";
    
    $summaryStmt = $conn->prepare($summaryQuery);
    $summaryStmt->bind_param('ssi', $startDate, $endDate, $company_id);
    $summaryStmt->execute();
    $summaryResult = $summaryStmt->get_result();
    $summary = $summaryResult->fetch_assoc();
    
    // Get data for transaction types chart
    $typesQuery = "
        SELECT 
            transaction_type,
            COUNT(*) as count,
            SUM(amount) as total_amount
        FROM 
            transactions 
        WHERE 
            date_made >= ? AND date_made < DATE_ADD(?, INTERVAL 1 DAY)
            AND company_id = ?
        GROUP BY transaction_type
    ";
    
    $typesStmt = $conn->prepare($typesQuery);
    $typesStmt->bind_param('ssi', $startDate, $endDate, $company_id);
    $typesStmt->execute();
    $typesResult = $typesStmt->get_result();
    $transactionTypes = $typesResult->fetch_all(MYSQLI_ASSOC);
    
    // Close statements
    $stmt->close();
    $countStmt->close();
    $summaryStmt->close();
    $typesStmt->close();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'transactions' => $transactions,
        'summary' => $summary,
        'transactionTypes' => $transactionTypes,
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