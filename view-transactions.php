<?php
session_start();

// Include database connection
include('db_connection.php');

// Handle date filtering if user submits a date range
$whereClause = "";
if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $whereClause = " WHERE date_made BETWEEN '$from_date' AND '$to_date'";
}

// Fetch transactions from the database with filtering
$transactions_query = "SELECT * FROM transactions $whereClause ORDER BY date_made DESC";
$result = $conn->query($transactions_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link rel="stylesheet" href="css/view-transactions.css">
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="content">
        <div class="filter-container">
            <form action="view-transactions.php" method="POST">
                <label for="from_date">Show transactions from:</label>
                <input type="date" id="from_date" name="from_date" required>
                <label for="to_date">To:</label>
                <input type="date" id="to_date" name="to_date" required>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <div class="container">
            
            <table border="1" class="table-style">
                <thead>
                    <tr>
                        <th>Transaction Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['transaction_type']; ?></td>
                                <td><?= number_format($row['amount'], 2); ?></td>
                                <td><?= $row['description']; ?></td>
                                <td><?= $row['date_made']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No transactions found in the selected date range.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
