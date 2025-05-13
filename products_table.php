<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

if (!isset($_SESSION['company_id'])) {
    die("Unauthorized access");
}

$companyId = $_SESSION['company_id'];
$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$query = "SELECT * FROM products WHERE company_id = $companyId 
          ORDER BY 
            CASE WHEN name REGEXP '^[^a-zA-Z]' THEN 0 ELSE 1 END,
            name ASC
          LIMIT $itemsPerPage OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

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
                $stockStatus = $quantity < $underStockReminder ? 'under-stock' : 'fine';
                
                $wholeQuantity = floor($quantity);
                $decimalQuantity = $quantity - $wholeQuantity;
                
                if ($decimalQuantity > 0) {
                    $unitQuery = "SELECT name, per_single_quantity FROM units 
                                WHERE product_id = $productId 
                                ORDER BY per_single_quantity ASC LIMIT 1";
                    $unitResult = mysqli_query($conn, $unitQuery);
                    
                    if ($unitRow = mysqli_fetch_assoc($unitResult)) {
                        $perSingleQuantity = isset($unitRow['per_single_quantity']) ? $unitRow['per_single_quantity'] : 1;
                        $decimalUnits = floor($decimalQuantity * $perSingleQuantity);
                        $quantityDisplay = $wholeQuantity . " " . $quantifiedAs . " and " . $decimalUnits . " " . $unitRow['name'];
                    } else {
                        $quantityDisplay = $wholeQuantity . " " . $quantifiedAs;
                    }
                } else {
                    $quantityDisplay = $wholeQuantity . " " . $quantifiedAs;
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['buying_price']) ?></td>
                <td><?= htmlspecialchars($row['selling_price']) ?></td>
                <td><?= $quantityDisplay ?></td>
                <td><span class="status-badge status-<?= $stockStatus ?>"><?= ucfirst($stockStatus) ?></span></td>
                <td>
                    <a href="edit-product.php?id=<?= $row['product_id'] ?>" class="action-btn view"><i class='bx bx-pencil'></i></a>
                    <a href="delete-product.php?id=<?= $row['product_id'] ?>" class="action-btn delete"><i class='bx bx-trash'></i></a>
                    <a href="view-create_units.php?id=<?= $row['product_id'] ?>" class="action-btn view"><i class='bx bx-box'></i></a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>