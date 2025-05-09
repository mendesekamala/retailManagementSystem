<?php
session_start();
include 'db_connection.php';

// Ensure the user is logged in by checking if `company_id` is in session
if (!isset($_SESSION['company_id'])) {
    header("Location: login.php");
    exit();
}

$companyId = $_SESSION['company_id'];  // Get company ID from session

// Fetch products with pagination
$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Get total count for pagination
$countQuery = "SELECT COUNT(*) AS total FROM products WHERE company_id = $companyId";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch products alphabetically (non-alphabetical first)
$query = "SELECT * FROM products WHERE company_id = $companyId 
          ORDER BY 
            CASE WHEN name REGEXP '^[^a-zA-Z]' THEN 0 ELSE 1 END,
            name ASC
          LIMIT $itemsPerPage OFFSET $offset";
$result = mysqli_query($conn, $query);

// Queries for the tiles
$underStockQuery = "SELECT COUNT(*) AS under_stock_reminders FROM products WHERE company_id = $companyId AND quantity < under_stock_reminder";
$zeroQuantityQuery = "SELECT COUNT(*) AS zero_quantity_products FROM products WHERE company_id = $companyId AND quantity = 0";
$leastSoldQuery = "SELECT COUNT(DISTINCT name) AS least_sold_products FROM order_items WHERE company_id = $companyId GROUP BY name HAVING SUM(quantity) < 5";
$destroyedProductsQuery = "SELECT COUNT(*) AS most_destroyed_products FROM quantity_destroyed WHERE company_id = $companyId AND quantity_destroyed > 0";

// Execute tile queries
$underStockResult = $conn->query($underStockQuery)->fetch_assoc()['under_stock_reminders'];
$zeroQuantityResult = $conn->query($zeroQuantityQuery)->fetch_assoc()['zero_quantity_products'];
$leastSoldResult = $conn->query($leastSoldQuery)->num_rows;
$destroyedProductsResult = $conn->query($destroyedProductsQuery)->fetch_assoc()['most_destroyed_products'];

// Queries for the lists
$mostSoldHighQuantityQuery = "SELECT name, SUM(quantity) AS total_quantity_sold FROM order_items WHERE company_id = $companyId GROUP BY name ORDER BY total_quantity_sold DESC LIMIT 3";
$mostSoldOrdersQuery = "SELECT name, COUNT(order_id) AS order_count FROM order_items WHERE company_id = $companyId GROUP BY name ORDER BY order_count DESC LIMIT 3";

// Execute list queries
$mostSoldHighQuantityResult = $conn->query($mostSoldHighQuantityQuery);
$mostSoldOrdersResult = $conn->query($mostSoldOrdersQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Products Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    <link href="css/products.css" rel="stylesheet">
    <style>
        html, body {
            overflow-x: hidden;
            max-width: 100%;
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
            <div class="table-responsive">
                <table id="products-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Buying Price</th>
                            <th>Selling Price</th>
                            <th>In Stock</th>
                            <th>Stock Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                                $productId = $row['product_id'];
                                $quantity = $row['quantity'];
                                $quantifiedAs = $row['quantified'];
                                $underStockReminder = $row['under_stock_reminder']; 

                                // Determine stock status
                                $stockStatus = $quantity < $underStockReminder ? 'under-stock' : 'fine';

                                // Split quantity into whole and decimal parts
                                $wholeQuantity = floor($quantity);
                                $decimalQuantity = $quantity - $wholeQuantity;

                                if ($decimalQuantity > 0) {
                                    // Fetch the unit with the lowest per_single_quantity for the product
                                    $unitQuery = "
                                        SELECT name, per_single_quantity, available_units
                                        FROM units 
                                        WHERE product_id = $productId AND company_id = $companyId
                                        ORDER BY per_single_quantity ASC 
                                        LIMIT 1
                                    ";
                                    $unitResult = mysqli_query($conn, $unitQuery);
                                    $unitRow = mysqli_fetch_assoc($unitResult);

                                    if ($unitRow) {
                                        // Calculate available units for just the decimal part
                                        $decimalUnits = $decimalQuantity * $unitRow['per_single_quantity'];
                                        $decimalUnits = floor($decimalUnits);

                                        // Format: whole quantity + quantified_as + decimal units + unit name
                                        $quantityDisplay = $wholeQuantity . " " . $quantifiedAs . " and " . $decimalUnits . " " . $unitRow['name'];
                                    } else {
                                        // Only display: whole quantity + quantified_as if no units are found
                                        $quantityDisplay = $wholeQuantity . " " . $quantifiedAs;
                                    }
                                } else {
                                    // If no decimal points, simply display: whole quantity + quantified_as
                                    $quantityDisplay = $wholeQuantity . " " . $quantifiedAs;
                                }
                            ?>
                            <tr>
                                <td><?= $row['name']; ?></td>
                                <td><?= $row['buying_price']; ?></td>
                                <td><?= $row['selling_price']; ?></td>
                                <td><?= $quantityDisplay; ?></td>
                                <td><span class="status-badge status-<?= $stockStatus; ?>"><?= ucfirst($stockStatus); ?></span></td>
                                <td>
                                    <a href="edit-product.php?id=<?= $row['product_id']; ?>" class="action-btn view"><i class='bx bx-pencil'></i></a>
                                    <a href="delete-product.php?id=<?= $row['product_id']; ?>" class="action-btn delete"><i class='bx bx-trash'></i></a>
                                    <a href="view-create_units.php?id=<?= $row['product_id']; ?>" class="action-btn view"><i class='bx bx-box'></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
        // Pagination functionality
        document.getElementById('prev-page').addEventListener('click', function() {
            const currentPage = <?= $page; ?>;
            if (currentPage > 1) {
                window.location.href = `products.php?page=${currentPage - 1}`;
            }
        });

        document.getElementById('next-page').addEventListener('click', function() {
            const currentPage = <?= $page; ?>;
            const totalPages = <?= $totalPages; ?>;
            if (currentPage < totalPages) {
                window.location.href = `products.php?page=${currentPage + 1}`;
            }
        });

        // Search functionality
        document.getElementById('product-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#products-table tbody tr');
            
            rows.forEach(row => {
                const productName = row.cells[0].textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Status filter functionality
        document.getElementById('status-filter').addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#products-table tbody tr');
            
            rows.forEach(row => {
                const rowStatus = row.cells[4].querySelector('.status-badge').className.includes(status);
                if (status === '' || rowStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>