<?php
require_once 'db_connection.php';
require_once 'includes/functions.php';

session_start();
$company_id = $_SESSION['company_id'] ?? 1;

// Default period to week (7 days)
$period = $_GET['period'] ?? 'week';
$type_filter = $_GET['type_filter'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;

// Get data
$summary = getTransactionSummary($conn, $company_id, $period);
$profit_data = getProfitData($conn, $company_id, $period);

// Get paginated transactions
$transactions = getRecentTransactions($conn, $company_id, $type_filter, $page, $itemsPerPage);
$totalTransactions = getTotalTransactionsCount($conn, $company_id, $type_filter);
$totalPages = ceil($totalTransactions / $itemsPerPage);

// Prepare chart data
$chart_data = [
    'dates' => array_column($profit_data, 'date'),
    'sales' => array_column($profit_data, 'sales'),
    'purchases' => array_column($profit_data, 'cost'),
    'profits' => array_column($profit_data, 'profit')
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transactions Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    <link href="css/view-transactions.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }
    </style>
    <script>
        var chartData = <?php echo json_encode($chart_data); ?>;
        var summaryData = <?php echo json_encode($summary); ?>;
    </script>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Transactions Dashboard</h1>
            <div class="date-filter">
                <select id="period-filter" class="filter-dropdown">
                    <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Today</option>
                    <option value="3days" <?= $period == '3days' ? 'selected' : '' ?>>Last 3 Days</option>
                    <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>Last Week</option>
                    <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Last Month</option>
                </select>
            </div>
        </header>

        <div class="summary-scroller">
            <div class="summary-cards">
                <?php 
                $tile_types = ['sale', 'profit', 'purchase', 'expenses', 'drawings', 'add_capitals'];
                $colors = [
                    'sale' => 'blue',
                    'profit' => 'green',
                    'purchase' => 'orange',
                    'expenses' => 'red',
                    'drawings' => 'purple',
                    'add_capitals' => 'dark'
                ];
                $icons = [
                    'sale' => 'bx-credit-card',
                    'profit' => 'bx-trending-up',
                    'purchase' => 'bx-cart',
                    'expenses' => 'bx-money',
                    'drawings' => 'bx-wallet',
                    'add_capitals' => 'bx-dollar'
                ];
                
                foreach ($tile_types as $tile_type): 
                    $data = array_filter($summary, function($item) use ($tile_type) {
                        return $item['transaction_type'] == $tile_type;
                    });
                    $data = $data ? array_values($data)[0] : ['count' => 0, 'total' => 0];
                ?>
                <div class="card">
                    <div class="card-icon bg-<?= $colors[$tile_type] ?>">
                        <i class='bx <?= $icons[$tile_type] ?>'></i>
                    </div>
                    <div class="card-info">
                        <h3><?= ucfirst(str_replace('_', ' ', $tile_type)) ?></h3>
                        <span><?= number_format($data['total'], 2) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <h2>Profit Trend</h2>
                <canvas id="lineChart" height="300"></canvas>
            </div>
            <div class="chart-container">
                <h2>Transaction Distribution</h2>
                <canvas id="pieChart" height="300"></canvas>
            </div>
        </div>

        <div class="table-section">
            <h2>Recent Transactions</h2>
            <div class="table-controls">
                <select id="type-filter" class="filter-dropdown">
                    <option value="">All Types</option>
                    <option value="sale" <?= $type_filter == 'sale' ? 'selected' : '' ?>>Sales</option>
                    <option value="purchase" <?= $type_filter == 'purchase' ? 'selected' : '' ?>>Purchases</option>
                    <option value="expenses" <?= $type_filter == 'expenses' ? 'selected' : '' ?>>Expenses</option>
                    <option value="add_capitals" <?= $type_filter == 'add_capitals' ? 'selected' : '' ?>>Add Capitals</option>
                    <option value="drawings" <?= $type_filter == 'drawings' ? 'selected' : '' ?>>Drawings</option>
                </select>
            </div>
            <div class="table-responsive">
                <table id="transactions-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td class="type-<?= $transaction['transaction_type'] ?>"><?= ucfirst(str_replace('_', ' ', $transaction['transaction_type'])) ?></td>
                            <td><?= number_format($transaction['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($transaction['description']) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($transaction['date_made'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <button id="prev-page" <?= $page <= 1 ? 'disabled' : '' ?>>
                    <i class='bx bx-chevron-left'></i>
                </button>
                <span id="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
                <button id="next-page" <?= $page >= $totalPages ? 'disabled' : '' ?>>
                    <i class='bx bx-chevron-right'></i>
                </button>
            </div>
        </div>
    </div>

    <script src="scripts/view-transactions.js"></script>
</body>
</html>