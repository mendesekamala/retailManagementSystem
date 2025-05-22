<?php
// Set timezone at the very beginning
date_default_timezone_set('Africa/Dar_es_Salaam');
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="css/view-transactions.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        .flatpickr-calendar {
            z-index: 1001 !important;
        }
        .date-range-picker {
            display: none;
            position: absolute;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }
    </style>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Transactions Dashboard</h1>
            <div class="date-filter">
                <button class="btn active" data-days="7">Last 7 Days</button>
                <button class="btn" data-days="30">Last 30 Days</button>
                <button class="btn" data-days="90">Last 90 Days</button>
                <button class="btn" id="custom-range">
                    <i class='bx bx-calendar'></i> Custom Range
                </button>
            </div>
        </header>

        <div class="summary-scroller">
            <div class="summary-cards">
                <div class="card">
                    <div class="card-icon bg-green">
                        <i class='bx bx-cart-alt'></i>
                    </div>
                    <div class="card-info">
                        <h3>Sales</h3>
                        <span id="sales-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-blue">
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="card-info">
                        <h3>Purchases</h3>
                        <span id="purchases-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-orange">
                        <i class='bx bx-money-withdraw'></i>
                    </div>
                    <div class="card-info">
                        <h3>Drawings</h3>
                        <span id="drawings-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-red">
                        <i class='bx bx-wallet'></i>
                    </div>
                    <div class="card-info">
                        <h3>Expenses</h3>
                        <span id="expenses-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-purple">
                        <i class='bx bx-coin-stack'></i>
                    </div>
                    <div class="card-info">
                        <h3>Add Capital</h3>
                        <span id="capital-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-teal">
                        <i class='bx bx-user-pin'></i>
                    </div>
                    <div class="card-info">
                        <h3>Debtors</h3>
                        <span id="debtors-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-yellow">
                        <i class='bx bx-user-voice'></i>
                    </div>
                    <div class="card-info">
                        <h3>Creditors</h3>
                        <span id="creditors-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-gray">
                        <i class='bx bx-trash'></i>
                    </div>
                    <div class="card-info">
                        <h3>Destructions</h3>
                        <span id="destructions-count">0</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-pink">
                        <i class='bx bx-refresh'></i>
                    </div>
                    <div class="card-info">
                        <h3>Refund</h3>
                        <span id="refund-count">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <h2>Sales vs Purchases</h2>
                <canvas id="sales-purchases-chart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Transaction Types</h2>
                <canvas id="transaction-types-chart"></canvas>
            </div>
        </div>

        <div class="table-section">
            <h2>Recent Transactions</h2>
            <div class="table-controls">
                <input type="text" id="transaction-search" placeholder="Search transactions...">
                <select id="type-filter">
                    <option value="">All Types</option>
                    <option value="sale">Sales</option>
                    <option value="purchase">Purchases</option>
                    <option value="expenses">Expenses</option>
                    <option value="drawings">Drawings</option>
                    <option value="add_capital">Capital</option>
                    <option value="destruction">Destruction</option>
                </select>
            </div>
            <div class="table-responsive">
                <table id="transactions-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <button id="prev-page" disabled><i class='bx bx-chevron-left'></i></button>
                <span id="page-info">Page 1 of 1</span>
                <button id="next-page" disabled><i class='bx bx-chevron-right'></i></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@2.0.2"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="scripts/view-transactions.js"></script>
</body>
</html>