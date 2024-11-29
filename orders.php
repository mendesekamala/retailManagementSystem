<?php
session_start();

// Connect to the database
include('db_connection.php');

// Check if the user is logged in and the session has the required data
if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id'])) {
    echo "User not logged in or session data is missing.";
    exit(); // Exit if no session data
}

// Handle date filtering
$whereClause = "";
if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $whereClause = "WHERE time BETWEEN '$from_date' AND '$to_date' AND company_id = {$_SESSION['company_id']}";
} else {
    // Filter orders based on the company_id from the session if no date filter is applied
    $whereClause = "WHERE company_id = {$_SESSION['company_id']}";
}

// Fetch order status counts based on the company_id of the logged-in user
$queryStatusCounts = "
    SELECT status, COUNT(*) AS count 
    FROM orders 
    $whereClause
    GROUP BY status";
$resultStatusCounts = mysqli_query($conn, $queryStatusCounts);
if (!$resultStatusCounts) {
    die("Error with the query: " . mysqli_error($conn));
}

// Initialize status counts
$orderCounts = [
    'created' => 0,
    'sent' => 0,
    'cancelled' => 0,
    'delivered' => 0
];

// Assign the counts to the correct statuses
while ($row = mysqli_fetch_assoc($resultStatusCounts)) {
    $orderCounts[$row['status']] = $row['count'];
}

// Fetch orders based on filter and company_id
$queryOrders = "SELECT * FROM orders $whereClause";
$resultOrders = mysqli_query($conn, $queryOrders);

// Function to determine the order status based on associated order_items
function determineOrderStatus($orderId, $conn) {
    $itemStatusQuery = "
        SELECT status, COUNT(*) AS count 
        FROM order_items 
        WHERE order_id = $orderId 
        GROUP BY status
        ORDER BY count DESC";
    
    $itemStatusResult = mysqli_query($conn, $itemStatusQuery);

    if (mysqli_num_rows($itemStatusResult) == 1) {
        // All items have the same status
        $singleStatusRow = mysqli_fetch_assoc($itemStatusResult);
        return $singleStatusRow['status'];
    } else {
        // Items have mixed statuses, take the most frequent
        $row = mysqli_fetch_assoc($itemStatusResult);
        return $row['status'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/orders.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <title>Orders</title>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="content">
        <!-- Date Filter Form -->
        <div class="filter-container">
            <form method="POST" action="orders.php">
                <label for="from_date">From:</label>
                <input type="date" id="from_date" name="from_date" required>
                <label for="to_date">To:</label>
                <input type="date" id="to_date" name="to_date" required>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <!-- Order Status Tiles -->
        <div class="summary-tiles">
            <div class="tile created">
                <p><?php echo $orderCounts['created']; ?></p>
                <span>Created</span>
            </div>
            <div class="tile sent">
                <p><?php echo $orderCounts['sent']; ?></p>
                <span>Sent</span>
            </div>
            <div class="tile cancelled">
                <p><?php echo $orderCounts['cancelled']; ?></p>
                <span>Cancelled</span>
            </div>
            <div class="tile delivered">
                <p><?php echo $orderCounts['delivered']; ?></p>
                <span>Delivered</span>
            </div>
        </div>

        <!-- Orders Table -->
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($resultOrders) > 0) {
                    while ($row = mysqli_fetch_assoc($resultOrders)) {
                        // Determine the status for the current order based on its items
                        $orderStatus = determineOrderStatus($row['order_id'], $conn);
                        
                        // Update order status in the database if it differs
                        if ($row['status'] != $orderStatus) {
                            $updateOrderStatus = "UPDATE orders SET status='$orderStatus' WHERE order_id=" . $row['order_id'];
                            mysqli_query($conn, $updateOrderStatus);
                        }

                        // Format time and date
                        $time = date("H:i", strtotime($row['time']));
                        $date = date("Y-m-d", strtotime($row['time']));
                        
                        echo "<tr>";
                        echo "<td>" . $row['order_id'] . "</td>";
                        echo "<td>" . $date . "</td>";
                        echo "<td>" . $time . "</td>";
                        echo "<td class='" . $orderStatus . "'>" . ucfirst($orderStatus) . "</td>";
                        echo "<td>" . $row['total'] . "</td>";
                        echo "<td>
                                <a href='edit-order.php?order_id=" . $row['order_id'] . "' class='action-icon edit'><i class='bx bx-pencil'></i></a>
                                <a href='cancel-order.php?order_id=" . $row['order_id'] . "' class='action-icon delete'><i class='bx bx-x'></i></a>
                                <a href='view-order.php?order_id=" . $row['order_id'] . "' class='action-icon view'><i class='bx bx-show'></i></a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
