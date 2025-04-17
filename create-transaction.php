<?php
session_start();
include 'db_connection.php'; // Database connection file

// Fetch logged-in company ID
$company_id = $_SESSION['company_id'];

// Fetch active payment methods
$methods_query = "SELECT * FROM payment_methods WHERE company_id = $company_id";
$methods_result = mysqli_query($conn, $methods_query);
$methods = mysqli_fetch_assoc($methods_result);

// Fetch payment balances
$money_query = "SELECT * FROM money WHERE company_id = $company_id";
$money_result = mysqli_query($conn, $money_query);
$balances = mysqli_fetch_assoc($money_result);

// Fetch last 5 transactions
$transactions_query = "SELECT transaction_id, transaction_type, date_made, amount FROM transactions WHERE company_id = $company_id ORDER BY date_made DESC LIMIT 5";
$transactions_result = mysqli_query($conn, $transactions_query);


// Fetch last 30 transactions for pie chart
$chart_query = "    SELECT m.payment_method, COUNT(*) AS count 
                    FROM methods_used m
                    JOIN (
                    SELECT transaction_id FROM transactions 
                    WHERE company_id = $company_id 
                    ORDER BY date_made DESC 
                    LIMIT 30
                    ) t ON m.transaction_id = t.transaction_id
                    GROUP BY m.payment_method
                ";
$chart_result = mysqli_query($conn, $chart_query);

if (!$chart_result) {
    die("Query failed: " . mysqli_error($conn)); // Display the SQL error
}
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_result)) {
    $chart_data[$row['payment_method']] = $row['count'];
}

// Fetch last 30 transactions for pie chart
// $chart_query = "SELECT payment_method, COUNT(*) as count FROM methods_used WHERE transaction_id IN (SELECT transaction_id FROM transactions WHERE company_id = $company_id ORDER BY date_made DESC LIMIT 30) GROUP BY payment_method";
// $chart_result = mysqli_query($conn, $chart_query);
// $chart_data = [];
// while ($row = mysqli_fetch_assoc($chart_result)) {
//     $chart_data[$row['payment_method']] = $row['count'];
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/create-transactions.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="container">
        <div class="top-section">
            <?php
            $colors = ['cash' => 'yellow', 'NMB' => 'orange', 'CRDB' => 'green', 'NBC' => 'purple', 'mpesa' => 'red', 'airtel_money' => 'red', 'tigo_pesa' => 'blue', 'halo_pesa' => 'orange', 'azam_pesa' => 'blue', 'debt' => 'red'];
            $count = 0;
            echo '<div class="tiles-container">';
            foreach ($methods as $method => $active) {
                if ($active == 'yes' && isset($balances[$method])) {
                    echo "<div class='tile' style='border-bottom: 4px solid {$colors[$method]};'>";
                    echo "<div class='balance'>" . number_format($balances[$method], 2) . "</div>";
                    echo "<div class='method-name'>" . strtoupper(str_replace('_', ' ', $method)) . "</div>";
                    echo "</div>";
                    $count++;
                }
            }
            echo '</div>';
            ?>
        </div>
        <div class="bottom-section">
            <div class="left-panel">
                <h2>Record New Transaction</h2>
                <form>
                    <label>Transaction Type</label>
                    <select id="transactionType">
                        <option value="expenses">Expenses</option>
                        <option value="drawings">Drawings</option>
                        <option value="add_capital">Add Capital</option>
                    </select>
                    <label>Description</label>
                    <input type="text" id="transactionDescription" placeholder="Enter description">
                    <label>Amount</label>
                    <input type="number" id="transactionAmount" step="0.01" placeholder="Enter amount">
                    <button type="button" onclick="openPaymentModal()">Record Transaction</button>
                </form>
            </div>
            <div class="right-panel">
                
                <div class="transactions-list">
                    <h3>Last 5 Transactions</h3>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                        <?php while ($row = mysqli_fetch_assoc($transactions_result)) { ?>
                        <tr>
                            <td><?= $row['transaction_id'] ?></td>
                            <td><?= $row['transaction_type'] ?></td>
                            <td><?= $row['date_made'] ?></td>
                            <td><?= number_format($row['amount'], 2) ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="transactions.js"></script>
    <script>
        const chartData = <?php echo json_encode($chart_data); ?>;
        const ctx = document.getElementById('paymentChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(chartData),
                datasets: [{
                    data: Object.values(chartData),
                    backgroundColor: ['yellow', 'orange', 'green', 'purple', 'red', 'blue', 'cyan', 'pink']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });


        function openPaymentModal() {
            let transactionType = document.getElementById('transactionType').value;
            let description = document.getElementById('transactionDescription').value;
            let amount = document.getElementById('transactionAmount').value;

            if (!transactionType || !description || !amount) {
                alert("Please fill all fields before proceeding!");
                return;
            }

            let transactionData = {
                transactionType, 
                description, 
                amount: parseFloat(amount)
            };

            localStorage.setItem('transactionData', JSON.stringify(transactionData));

            // Call the function from the JS file that sets grandTotal
            openModal(parseFloat(amount));

        }

    </script>
<?php include('payment-modal_create-transaction.php'); ?>
</body>
</html>
