<?php
function getTransactionSummary($conn, $company_id, $period = 'month') {
    $where = "WHERE company_id = ?";
    $params = [$company_id];
    
    switch($period) {
        case 'day':
            $where .= " AND DATE(date_made) = CURDATE()";
            break;
        case '3days':
            $where .= " AND date_made >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)";
            break;
        case 'week':
            $where .= " AND date_made >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $where .= " AND date_made >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
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
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProfitData($conn, $company_id, $period = 'month') {
    $where = "WHERE o.company_id = ?";
    $params = [$company_id];
    
    switch($period) {
        case 'day':
            $where .= " AND DATE(o.time) = CURDATE()";
            break;
        case '3days':
            $where .= " AND o.time >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)";
            break;
        case 'week':
            $where .= " AND o.time >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $where .= " AND o.time >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
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

function getRecentTransactions($conn, $company_id, $type = null, $limit = 15) {
    $where = "WHERE company_id = ?";
    $params = [$company_id];
    
    if ($type) {
        $where .= " AND transaction_type = ?";
        $params[] = $type;
    }
    
    $sql = "SELECT 
                transaction_id,
                transaction_type,
                amount,
                description,
                date_made
            FROM transactions
            $where
            ORDER BY date_made DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($type) {
        $stmt->bind_param("isi", $company_id, $type, $limit);
    } else {
        $stmt->bind_param("ii", $company_id, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>