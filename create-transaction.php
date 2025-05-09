<?php
session_start();
include 'db_connection.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transactions Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    <link href="css/create-transactions.css" rel="stylesheet">
    <style>
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Transactions Dashboard</h1>
        </header>

        <!-- Scrollable balance tiles -->
        <div class="balances-scroller">
            <div class="balances-container">
                <?php
                $colors = [
                    'cash' => 'bg-blue',
                    'NMB' => 'bg-orange',
                    'CRDB' => 'bg-green',
                    'NBC' => 'bg-purple',
                    'mpesa' => 'bg-red',
                    'airtel_money' => 'bg-red',
                    'tigo_pesa' => 'bg-blue',
                    'halo_pesa' => 'bg-orange',
                    'azam_pesa' => 'bg-blue',
                    'debt' => 'bg-red'
                ];
                
                $icons = [
                    'cash' => 'bx-money',
                    'NMB' => 'bx-credit-card',
                    'CRDB' => 'bx-credit-card',
                    'NBC' => 'bx-credit-card',
                    'mpesa' => 'bx-mobile',
                    'airtel_money' => 'bx-mobile',
                    'tigo_pesa' => 'bx-mobile',
                    'halo_pesa' => 'bx-mobile',
                    'azam_pesa' => 'bx-mobile',
                    'debt' => 'bx-receipt'
                ];
                
                foreach ($methods as $method => $active) {
                    if ($active == 'yes' && isset($balances[$method])) {
                        echo '<div class="balance-card fade-in">';
                        echo '<div class="balance-icon ' . $colors[$method] . '">';
                        echo '<i class="bx ' . $icons[$method] . '"></i>';
                        echo '</div>';
                        echo '<div class="balance-info">';
                        echo '<h3>' . strtoupper(str_replace('_', ' ', $method)) . '</h3>';
                        echo '<span>' . number_format($balances[$method], 2) . '</span>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>

        <div class="main-content-section">
            <div class="form-section">
                <div class="form-container slide-in-left">
                    <h2>Record New Transaction</h2>
                    <form class="transaction-form">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select id="transactionType" class="form-control">
                                <option value="expenses">Expenses</option>
                                <option value="drawings">Drawings</option>
                                <option value="add_capital">Add Capital</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" id="transactionDescription" class="form-control" placeholder="Enter description">
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" id="transactionAmount" class="form-control" step="0.01" placeholder="Enter amount">
                        </div>
                        <button type="button" class="btn-primary" onclick="openPaymentModal()">
                            Record Transaction
                        </button>
                    </form>
                </div>
            </div>

            <div class="recent-transactions-section">
                <div class="table-container">
                    <h2>Recent Transactions</h2>
                    <div class="table-responsive">
                        <table id="transactions-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($transactions_result)) { ?>
                                <tr>
                                    <td><?= $row['transaction_id'] ?></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $row['transaction_type'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['date_made'])) ?></td>
                                    <td><?= number_format($row['amount'], 2) ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
            openModal(parseFloat(amount));
        }
    </script>
    
    <?php include('payment-modal_create-transaction.php'); ?>
</body>
</html>