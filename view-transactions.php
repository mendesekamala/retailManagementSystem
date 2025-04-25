<?php
require_once 'db_connection.php';
require_once 'includes/functions.php';

session_start();
$company_id = $_SESSION['company_id'] ?? 1;

// Default period to week (7 days)
$period = $_GET['period'] ?? 'week';
$type_filter = $_GET['type_filter'] ?? null;

// Get data
$summary = getTransactionSummary($conn, $company_id, $period);
$profit_data = getProfitData($conn, $company_id, $period);
$transactions = getRecentTransactions($conn, $company_id, $type_filter);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Dashboard</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/view-transactions.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        var chartData = <?php echo json_encode($chart_data); ?>;
        var summaryData = <?php echo json_encode($summary); ?>;
    </script>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <!-- Period Filter -->
        <div class="filter-container">
            <select id="period-filter" class="filter-dropdown">
                <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Today</option>
                <option value="3days" <?= $period == '3days' ? 'selected' : '' ?>>Last 3 Days</option>
                <option value="week" <?= $period == 'week' ? 'selected' : '' ?>>Last Week</option>
                <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Last Month</option>
            </select>
        </div>

        <!-- Summary Tiles -->
        <div class="grid-container">
            <div class="summary-grid">
                <?php 
                $tile_types = ['sale', 'profit', 'purchase', 'expenses', 'drawings', 'add_capitals'];
                foreach ($tile_types as $tile_type): 
                    $data = array_filter($summary, function($item) use ($tile_type) {
                        return $item['transaction_type'] == $tile_type;
                    });
                    $data = $data ? array_values($data)[0] : ['count' => 0, 'total' => 0];
                ?>
                <div class="summary-tile <?= $tile_type ?>">
                    <div class="tile-content">
                        <span class="count">0</span>|
                        <span class="amount">0</span>
                        <div class="type-label"><?= ucfirst(str_replace('_', ' ', $tile_type)) ?></div>
                    </div>
                    <div class="tile-data" data-count="<?= $data['count'] ?>" data-amount="<?= $data['total'] ?>"></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <div class="chart-container line-chart">
                    <canvas id="lineChart"></canvas>
                </div>
                <div class="chart-container pie-chart">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="table-grid">
                <div class="table-header">
                    <h3>Recent Transactions</h3>
                    <select id="type-filter" class="filter-dropdown">
                        <option value="">All Types</option>
                        <option value="sale" <?= $type_filter == 'sale' ? 'selected' : '' ?>>Sales</option>
                        <option value="purchase" <?= $type_filter == 'purchase' ? 'selected' : '' ?>>Purchases</option>
                        <option value="expenses" <?= $type_filter == 'expenses' ? 'selected' : '' ?>>Expenses</option>
                        <option value="add_capitals" <?= $type_filter == 'add_capitals' ? 'selected' : '' ?>>Add Capitals</option>
                        <option value="drawings" <?= $type_filter == 'drawings' ? 'selected' : '' ?>>Drawings</option>
                    </select>
                </div>
                <table class="transactions-table">
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
        </div>
    </div>

    <script src="scripts/view-transactions.js"></script>
</body>
</html>