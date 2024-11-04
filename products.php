<?php
    session_start();
    include 'db_connection.php';

    // Fetch products from database
    $query = "SELECT * FROM products";
    $result = mysqli_query($conn, $query);


    // Queries for the tiles
    $underStockQuery = "SELECT COUNT(*) AS under_stock_reminders FROM products WHERE quantity < under_stock_reminder";
    $zeroQuantityQuery = "SELECT COUNT(*) AS zero_quantity_products FROM products WHERE quantity = 0";
    $leastSoldQuery = "SELECT COUNT(DISTINCT name) AS least_sold_products FROM order_items GROUP BY name HAVING SUM(quantity) < 5";
    $destroyedProductsQuery = "SELECT COUNT(*) AS most_destroyed_products FROM quantity_destroyed WHERE quantity_destroyed > 0 ";

    // Execute tile queries
    $underStockResult = $conn->query($underStockQuery)->fetch_assoc()['under_stock_reminders'];
    $zeroQuantityResult = $conn->query($zeroQuantityQuery)->fetch_assoc()['zero_quantity_products'];
    $leastSoldResult = $conn->query($leastSoldQuery)->num_rows;
    $destroyedProductsResult = $conn->query($destroyedProductsQuery)->fetch_assoc()['most_destroyed_products'];

    // Queries for the lists
    $mostSoldHighQuantityQuery = "SELECT name, SUM(quantity) AS total_quantity_sold FROM order_items GROUP BY name ORDER BY total_quantity_sold DESC LIMIT 3";
    $mostSoldOrdersQuery = "SELECT name, COUNT(order_id) AS order_count FROM order_items GROUP BY name ORDER BY order_count DESC LIMIT 3";

    // Execute list queries
    $mostSoldHighQuantityResult = $conn->query($mostSoldHighQuantityQuery);
    $mostSoldOrdersResult = $conn->query($mostSoldOrdersQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/products.css">
    <title>Products</title>
</head>

<?php include('sidebar.php'); ?>

<body>

<div class="main-content">
    <!-- Tiles Section -->
    <div class="tile-container">
        <!-- Left Tile -->
        <div class="tile left-tile">
            <div class="room upper-left">
                <div class="icon-count">
                    <i class='bx bx-alarm-exclamation icon-under'></i>
                    <span class="count"><?= $underStockResult; ?></span>
                </div>
                <p class="label">Under Stock Reminders</p>
            </div>
            <div class="room upper-right">
                <div class="icon-count">
                    <i class='bx bx-block icon-zero'></i>
                    <span class="count"><?= $zeroQuantityResult; ?></span>
                </div>
                <p class="label">Zero Quantity Products</p>
            </div>
            <div class="room lower-left">
                <div class="icon-count">
                    <i class='bx bx-arrow-from-top icon-least'></i>
                    <span class="count"><?= $leastSoldResult; ?></span>
                </div>
                <p class="label">Least Sold Products</p>
            </div>
            <div class="room lower-right">
                <div class="icon-count">
                    <i class='bx bx-bomb icon-most'></i>
                    <span class="count"><?= $destroyedProductsResult; ?></span>
                </div>
                <p class="label">Most Destroyed Products</p>
            </div>
        </div>

        <!-- Right Tile -->
        <div class="tile right-tile">
            <div class="list-section">
                <h3>Most Sold (High Quantity)</h3>
                <ul>
                    <?php while($row = $mostSoldHighQuantityResult->fetch_assoc()): ?>
                        <li><?= $row['name'] . " - " . $row['total_quantity_sold'] . " units"; ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="list-section">
                <h3>Most Sold (Most Orders)</h3>
                <ul>
                    <?php while($row = $mostSoldOrdersResult->fetch_assoc()): ?>
                        <li><?= $row['name'] . " - " . $row['order_count'] . " orders"; ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Table -->
    <table class="products-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
                <th>In Stock</th>
                <th>Stock Status</th> <!-- New column for stock status -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <?php
                    $productId = $row['product_id'];
                    $quantity = $row['quantity'];
                    $quantifiedAs = $row['quantified'];
                    $underStockReminder = $row['under_stock_reminder']; // Fetch this field from `products`

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
                            WHERE product_id = $productId 
                            ORDER BY per_single_quantity ASC 
                            LIMIT 1
                        ";
                        $unitResult = mysqli_query($conn, $unitQuery);
                        $unitRow = mysqli_fetch_assoc($unitResult);

                        if ($unitRow) {
                            // Calculate available units for just the decimal part
                            $decimalUnits = $decimalQuantity * $unitRow['per_single_quantity'];
                            $decimalUnits = floor($decimalUnits); // show whole units for the decimal part

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
                    <td style="text-align:left"> <?= $quantityDisplay; ?></td>
                    <td> <p class="stock-status <?= $stockStatus; ?>"><?= ucfirst($stockStatus); ?> </p> </td> <!-- Apply the CSS class -->
                    <td>
                        <a href="edit-product.php?id=<?= $row['product_id']; ?>" class="action-icon"><i class='bx bx-pencil'></i></a>
                        <a href="delete-product.php?id=<?= $row['product_id']; ?>" class="action-icon"><i class='bx bx-trash'></i></a>
                        <a href="view-create_units.php?id=<?= $row['product_id']; ?>" class="action-icon"><i class='bx bx-box'></i></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

