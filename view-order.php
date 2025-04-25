<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
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
    </style>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Orders Dashboard</h1>
            <div class="date-filter">
                <button class="btn active" data-days="7">Last 7 Days</button>
                <button class="btn" data-days="30">Last 30 Days</button>
                <button class="btn" data-days="90">Last 90 Days</button>
                <button class="btn" id="custom-range">
                    <i class='bx bx-calendar'></i> Custom Range
                </button>
            </div>
        </header>

        <div class="summary-cards">
            <div class="card">
                <div class="card-icon bg-blue">
                    <i class='bx bx-receipt'></i>
                </div>
                <div class="card-info">
                    <h3>Total Orders</h3>
                    <span id="total-orders">0</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-green">
                    <i class='bx bx-credit-card'></i>
                </div>
                <div class="card-info">
                    <h3>Total Revenue</h3>
                    <span id="total-revenue">Tsh0.00</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-purple">
                    <i class='bx bx-trending-up'></i>
                </div>
                <div class="card-info">
                    <h3>Avg. Order Value</h3>
                    <span id="avg-order-value">Tsh0.00</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-orange">
                    <i class='bx bx-dollar-circle'></i>
                </div>
                <div class="card-info">
                    <h3>Total Profit</h3>
                    <span id="total-profit">Tsh0.00</span>
                </div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <h2>Revenue Trend</h2>
                <canvas id="revenue-chart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Profit Trend</h2>
                <canvas id="profit-chart"></canvas>
            </div>
        </div>

        <div class="table-section">
            <h2>Recent Orders</h2>
            <div class="table-controls">
                <input type="text" id="order-search" placeholder="Search orders...">
                <select id="status-filter">
                    <option value="">All Statuses</option>
                    <option value="created">Created</option>
                    <option value="sent">Sent</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="table-responsive">
                <table id="orders-table">
                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Profit</th>
                            <th>Status</th>
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
    <script src="scripts/view-orders.js"></script>
</body>
</html>
