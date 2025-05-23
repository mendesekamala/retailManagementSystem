<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['company_id'])) {
    header("Location: login.php");
    exit();
}

$companyId = $_SESSION['company_id'];
$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Count total creditors (including those who have been paid)
$countQuery = "SELECT COUNT(DISTINCT name) AS total FROM debt_payments 
               WHERE company_id = $companyId AND debtor_creditor = 'creditor'";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get high credit (top 3 by total credit amount) - include all creditors
$highCreditQuery = "SELECT name, SUM(due_amount) AS total_credit 
                  FROM debt_payments 
                  WHERE company_id = $companyId AND debtor_creditor = 'creditor'
                  GROUP BY name 
                  ORDER BY total_credit DESC 
                  LIMIT 3";
$highCreditResult = mysqli_query($conn, $highCreditQuery);

// Get frequent creditors (top 3 by number of credits) - include all creditors
$frequentCreditorsQuery = "SELECT name, COUNT(*) AS credit_count 
                         FROM debt_payments 
                         WHERE company_id = $companyId AND debtor_creditor = 'creditor'
                         GROUP BY name 
                         ORDER BY credit_count DESC 
                         LIMIT 3";
$frequentCreditorsResult = mysqli_query($conn, $frequentCreditorsQuery);

// Get paginated list of all creditors with their current due amount
$query = "SELECT 
            name, 
            SUM(total) AS total_credit,
            SUM(due_amount) AS current_due_amount,
            MAX(date_created) AS last_credit_date,
            COUNT(*) AS credit_count
          FROM debt_payments 
          WHERE company_id = $companyId AND debtor_creditor = 'creditor'
          GROUP BY name 
          ORDER BY current_due_amount DESC
          LIMIT $itemsPerPage OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creditors Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    <link href="css/creditors.css" rel="stylesheet">
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
        
        /* Animation classes */
        .slide-in-right {
            animation: slideInRight 0.8s ease-out forwards;
        }
        
        .fall-down {
            animation: fallDown 0.8s ease-out forwards;
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fallDown {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .status-badge.status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.status-due {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>

<?php include('sidebar.php'); ?>
<?php include('payment-modal_creditors.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Creditors Dashboard</h1>
        </header>

        <div class="charts-section">
            <div class="chart-container slide-in-right">
                <h2>Highest Credit (Amount)</h2>
                <div class="list-container">
                    <ul>
                        <?php while($row = $highCreditResult->fetch_assoc()): ?>
                            <li><?= htmlspecialchars($row['name']) . " - TSh " . number_format($row['total_credit'], 2); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
            <div class="chart-container slide-in-right" style="animation-delay: 0.2s">
                <h2>Frequent Creditors (Count)</h2>
                <div class="list-container">
                    <ul>
                        <?php while($row = $frequentCreditorsResult->fetch_assoc()): ?>
                            <li><?= htmlspecialchars($row['name']) . " - " . $row['credit_count'] . " credits"; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="table-section">
            <h2>Creditors List</h2>
            <div class="table-controls">
                <input type="text" id="creditor-search" placeholder="Search creditors...">
                <select id="status-filter">
                    <option value="">All Statuses</option>
                    <option value="paid">Paid</option>
                    <option value="due">Due</option>
                </select>
            </div>
            <div id="table-container">
                <div class="table-responsive">
                    <?php include('creditors_table.php'); ?>
                </div>
            </div>
            <div class="pagination">
                <button id="prev-page" <?= $page <= 1 ? 'disabled' : ''; ?>>
                    <i class='bx bx-chevron-left'></i>
                </button>
                <span id="page-info">Page <?= $page; ?> of <?= $totalPages; ?></span>
                <button id="next-page" <?= $page >= $totalPages ? 'disabled' : ''; ?>>
                    <i class='bx bx-chevron-right'></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentPage = <?= $page; ?>;
        const totalPages = <?= $totalPages; ?>;

        function loadTableContent(page) {
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(loadingOverlay);

            fetch(`creditors_table.php?page=${page}`)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#table-container .table-responsive').innerHTML = data;
                    currentPage = page;
                    updatePagination();
                    document.body.removeChild(loadingOverlay);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.body.removeChild(loadingOverlay);
                });
        }

        function updatePagination() {
            document.getElementById('prev-page').disabled = currentPage <= 1;
            document.getElementById('next-page').disabled = currentPage >= totalPages;
            document.getElementById('page-info').textContent = `Page ${currentPage} of ${totalPages}`;
        }

        document.getElementById('prev-page').addEventListener('click', () => {
            if (currentPage > 1) loadTableContent(currentPage - 1);
        });

        document.getElementById('next-page').addEventListener('click', () => {
            if (currentPage < totalPages) loadTableContent(currentPage + 1);
        });

        document.getElementById('creditor-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#creditors-table tbody tr');
                
            rows.forEach(row => {
                const creditorName = row.cells[0].textContent.toLowerCase();
                row.style.display = creditorName.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('status-filter').addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#creditors-table tbody tr');
                
            rows.forEach(row => {
                const rowStatus = row.cells[3].querySelector('.status-badge').className.includes(status);
                row.style.display = (status === '' || rowStatus) ? '' : 'none';
            });
        });
    </script>
</body>
</html>