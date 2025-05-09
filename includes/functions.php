<?php
function getTransactionSummary($conn, $company_id, $period = 'week') {
    $where = "WHERE company_id = ?";
    $params = [$company_id];
    
    switch($period) {
        case 'day': $where .= " AND DATE(date_made) = CURDATE()"; break;
        case '3days': $where .= " AND date_made >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)"; break;
        case 'week': $where .= " AND date_made >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)"; break;
        case 'month': $where .= " AND date_made >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"; break;
    }
    
    $sql = "SELECT 
                transaction_type,
                COUNT(*) as count,
                SUM(amount) as total
            FROM transactions
            $where
            GROUP BY transaction_type";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    
    // Add profit calculation from orders table
    $where_profit = "WHERE company_id = ?";
    switch($period) {
        case 'day': $where_profit .= " AND DATE(time) = CURDATE()"; break;
        case '3days': $where_profit .= " AND time >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)"; break;
        case 'week': $where_profit .= " AND time >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)"; break;
        case 'month': $where_profit .= " AND time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"; break;
    }
    
    $sql_profit = "SELECT SUM(profit) as total FROM orders $where_profit";
    $stmt = $conn->prepare($sql_profit);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $profit_result = $stmt->get_result()->fetch_assoc();
    
    $results[] = [
        'transaction_type' => 'profit',
        'count' => 1,
        'total' => $profit_result['total'] ?? 0
    ];
    
    return $results;
}

function getProfitData($conn, $company_id, $period = 'week') {
    $where = "WHERE o.company_id = ?";
    $params = [$company_id];
    
    switch($period) {
        case 'day': $where .= " AND DATE(o.time) = CURDATE()"; break;
        case '3days': $where .= " AND o.time >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)"; break;
        case 'week': $where .= " AND o.time >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)"; break;
        case 'month': $where .= " AND o.time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"; break;
    }
    
    $sql = "SELECT 
                DATE(o.time) as date,
                SUM(o.total) as sales,
                SUM(oi.quantity * oi.buying_price) as cost,
                SUM(o.profit) as profit
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            $where
            GROUP BY DATE(o.time)
            ORDER BY DATE(o.time)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalTransactionsCount($conn, $company_id, $type_filter = null) {
    $query = "SELECT COUNT(*) as total FROM transactions WHERE company_id = ?";
    $params = [$company_id];
    
    if ($type_filter) {
        $query .= " AND transaction_type = ?";
        $params[] = $type_filter;
    }
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters dynamically
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getRecentTransactions($conn, $company_id, $type_filter = null, $page = 1, $itemsPerPage = 10) {
    $offset = ($page - 1) * $itemsPerPage;
    $query = "SELECT * FROM transactions WHERE company_id = ?";
    $params = [$company_id];
    
    if ($type_filter) {
        $query .= " AND transaction_type = ?";
        $params[] = $type_filter;
    }
    
    $query .= " ORDER BY date_made DESC LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters dynamically
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}
?>