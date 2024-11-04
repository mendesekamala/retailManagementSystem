<?php
session_start();

// Connect to the database
include('db_connection.php');

// Handle date filtering
$whereClause = "";
if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $whereClause = "WHERE time BETWEEN '$from_date' AND '$to_date'";
}

// Fetch order status counts
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
    'cancelled' => 0
];

// Assign the counts to the correct statuses
while ($row = mysqli_fetch_assoc($resultStatusCounts)) {
    $orderCounts[$row['status']] = $row['count'];
}

// Fetch orders based on filter
$queryOrders = "SELECT * FROM orders $whereClause";
$resultOrders = mysqli_query($conn, $queryOrders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/orders.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <title>Orders</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin-left: 250px;
            background-color: #f4f4f4;
        }

        .content {
            padding: 0 120px;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);  /* Box-shadow effect */
        }

        .orders-table th, .orders-table td {
            padding: 12px;
            text-align: center;
        }

        /* Table Header Styling */
        .orders-table thead {
            background-color: #f8f8f8;  /* Special background for header */
        }

        .orders-table th {
            font-weight: bold;
            color: #333;
        }

        /* Row and Column Styling using nth-child */
        .orders-table tr:nth-child(even) {
            background-color: #f9f9f9;  /* Alternating row colors */
        }

        .orders-table tbody tr {
            transition: background-color 0.3s;
            border-bottom: 2px solid rgb(134, 128, 128);
        }

        .orders-table tbody tr:hover {
            background-color: #ececec;  /* Hover effect */
        }

        .orders-table td:nth-child(odd) {
            background-color: #ffffff;
        }

        .orders-table td:nth-child(even) {
            background-color: #f7f7f7;
        }

        /* Action Icons */
        .action-icon {
            margin: 0 8px;
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
            font-size: 17px;
        }

        .action-icon:hover {
            color: #0056b3;
        }

        .action-icon.edit:hover {
            color: #28a745; /* Green for edit */
        }

        .action-icon.delete:hover {
            color: #dc3545; /* Red for delete */
        }

        .action-icon.view:hover {
            color: #17a2b8; /* Blue for view */
        }

        /* Remove lines between rows and columns */
        .orders-table th, .orders-table td {
            border: none;  /* No borders */
        }

        /* Status Colors in Table */
        .orders-table .created {
            color: blue;
            font-weight: bold;
        }

        .orders-table .sent {
            color: rgb(255, 174, 0);
            font-weight: bold;
        }

        .orders-table .cancelled {
            color: red;
            font-weight: bold;
        }

        /* Filter Container */
        .filter-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .filter-container form {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-container label {
            font-weight: bold;
        }

        input[type="date"] {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Button Style with Filter Icon */
        .btn-filter {
            display: flex;
            align-items: center;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-filter:hover {
            background-color: #0056b3;
        }

        /* Summary Tiles */
        .summary-tiles {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tile {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 30%;
        }

        .tile p {
            font-size: 36px;
            margin: 0;
        }

        .tile span {
            font-size: 14px;
        }

        /* Tile Colors */
        .created {
            border-left: 4px solid blue;
        }

        .sent {
            border-left: 4px solid  rgb(255, 174, 0);
        }

        .cancelled {
            border-left: 4px solid red;
        }

    </style>

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
                        $time = date("H:i", strtotime($row['time']));  // Format time without seconds
                        $date = date("Y-m-d", strtotime($row['time']));  // Extract date
                        echo "<tr>";
                        echo "<td>" . $row['order_id'] . "</td>";
                        echo "<td>" . $date . "</td>";
                        echo "<td>" . $time . "</td>";
                        echo "<td class='" . $row['status'] . "'>" . ucfirst($row['status']) . "</td>";
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


