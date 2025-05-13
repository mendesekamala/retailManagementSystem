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

$countQuery = "SELECT COUNT(*) AS total FROM products WHERE company_id = $companyId";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$query = "SELECT * FROM products WHERE company_id = $companyId 
          ORDER BY 
            CASE WHEN name REGEXP '^[^a-zA-Z]' THEN 0 ELSE 1 END,
            name ASC
          LIMIT $itemsPerPage OFFSET $offset";
$result = mysqli_query($conn, $query);

$underStockQuery = "SELECT COUNT(*) AS under_stock_reminders FROM products WHERE company_id = $companyId AND quantity < under_stock_reminder";
$zeroQuantityQuery = "SELECT COUNT(*) AS zero_quantity_products FROM products WHERE company_id = $companyId AND quantity = 0";
$leastSoldQuery = "SELECT COUNT(DISTINCT name) AS least_sold_products FROM order_items WHERE company_id = $companyId GROUP BY name HAVING SUM(quantity) < 5";
$destroyedProductsQuery = "SELECT COUNT(*) AS most_destroyed_products FROM quantity_destroyed WHERE company_id = $companyId AND quantity_destroyed > 0";

$underStockResult = $conn->query($underStockQuery)->fetch_assoc()['under_stock_reminders'];
$zeroQuantityResult = $conn->query($zeroQuantityQuery)->fetch_assoc()['zero_quantity_products'];
$leastSoldResult = $conn->query($leastSoldQuery)->num_rows;
$destroyedProductsResult = $conn->query($destroyedProductsQuery)->fetch_assoc()['most_destroyed_products'];

$mostSoldHighQuantityQuery = "SELECT name, SUM(quantity) AS total_quantity_sold FROM order_items WHERE company_id = $companyId GROUP BY name ORDER BY total_quantity_sold DESC LIMIT 3";
$mostSoldOrdersQuery = "SELECT name, COUNT(order_id) AS order_count FROM order_items WHERE company_id = $companyId GROUP BY name ORDER BY order_count DESC LIMIT 3";

$mostSoldHighQuantityResult = $conn->query($mostSoldHighQuantityQuery);
$mostSoldOrdersResult = $conn->query($mostSoldOrdersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    <link href="css/products.css" rel="stylesheet">
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
    </style>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Products Dashboard</h1>
        </header>

        <div class="summary-cards">
            <div class="card fall-down">
                <div class="card-icon bg-blue">
                    <i class='bx bx-alarm-exclamation'></i>
                </div>
                <div class="card-info">
                    <h3>Under Stock</h3>
                    <span><?= $underStockResult; ?></span>
                </div>
            </div>
            <div class="card fall-down" style="animation-delay: 0.1s">
                <div class="card-icon bg-red">
                    <i class='bx bx-block'></i>
                </div>
                <div class="card-info">
                    <h3>Zero Quantity</h3>
                    <span><?= $zeroQuantityResult; ?></span>
                </div>
            </div>
            <div class="card fall-down" style="animation-delay: 0.2s">
                <div class="card-icon bg-purple">
                    <i class='bx bx-arrow-from-top'></i>
                </div>
                <div class="card-info">
                    <h3>Least Sold</h3>
                    <span><?= $leastSoldResult; ?></span>
                </div>
            </div>
            <div class="card fall-down" style="animation-delay: 0.3s">
                <div class="card-icon bg-orange">
                    <i class='bx bx-bomb'></i>
                </div>
                <div class="card-info">
                    <h3>Most Destroyed</h3>
                    <span><?= $destroyedProductsResult; ?></span>
                </div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container slide-in-right">
                <h2>Most Sold (High Quantity)</h2>
                <div class="list-container">
                    <ul>
                        <?php while($row = $mostSoldHighQuantityResult->fetch_assoc()): ?>
                            <li><?= $row['name'] . " - " . $row['total_quantity_sold'] . " units"; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
            <div class="chart-container slide-in-right" style="animation-delay: 0.2s">
                <h2>Most Sold (Most Orders)</h2>
                <div class="list-container">
                    <ul>
                        <?php while($row = $mostSoldOrdersResult->fetch_assoc()): ?>
                            <li><?= $row['name'] . " - " . $row['order_count'] . " orders"; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="table-section">
            <h2>Products Inventory</h2>
            <div class="table-controls">
                <input type="text" id="product-search" placeholder="Search products...">
                <select id="status-filter">
                    <option value="">All Statuses</option>
                    <option value="fine">In Stock</option>
                    <option value="under-stock">Under Stock</option>
                </select>
            </div>
            <div id="table-container">
                <div class="table-responsive">
                    <?php include('products_table.php'); ?>
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

            fetch(`products_table.php?page=${page}`)
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

        document.getElementById('product-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#products-table tbody tr');
                
            rows.forEach(row => {
                const productName = row.cells[0].textContent.toLowerCase();
                row.style.display = productName.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('status-filter').addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#products-table tbody tr');
                
            rows.forEach(row => {
                const rowStatus = row.cells[4].querySelector('.status-badge').className.includes(status);
                row.style.display = (status === '' || rowStatus) ? '' : 'none';
            });
        });
    </script>
</body>
</html>