<?php
session_start();
include('db_connection.php');

// Initialize an empty message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];

    // Update product information
    $update_sql = "UPDATE products SET buying_price = ?, selling_price = ?, quantity = quantity + ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ddii", $buying_price, $selling_price, $quantity, $product_id);

    if ($stmt->execute()) {
        // Update table units (assuming similar logic for available_units)
        $update_units_sql = "UPDATE units SET available_units = available_units + ? WHERE product_id = ?";
        $units_stmt = $conn->prepare($update_units_sql);
        $units_stmt->bind_param("ii", $quantity, $product_id);
        $units_stmt->execute();

        // Calculate total purchase amount and update cash table
        $total_purchase = $buying_price * $quantity;
        $update_cash_sql = "UPDATE money SET cash = cash - ?";
        $cash_stmt = $conn->prepare($update_cash_sql);
        $cash_stmt->bind_param("d", $total_purchase);
        $cash_stmt->execute();

        // Insert purchased item directly into `purchases_items` table
        $insert_item_sql = "INSERT INTO purchases_items (product_id, product_name, quantity, buying_price, selling_price, date_made, total)
                            VALUES (?, (SELECT name FROM products WHERE product_id = ?), ?, ?, ?, NOW(), ?)";
        $item_stmt = $conn->prepare($insert_item_sql);
        $item_stmt->bind_param("iiiddd", $product_id, $product_id, $quantity, $buying_price, $selling_price, $total_purchase);
        $item_stmt->execute();

        $message = "Stock updated and purchase recorded successfully!";
    } else {
        $message = "Error updating product: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock</title>
    <link rel="stylesheet" href="css/add-stock.css">
</head>

<?php include('sidebar.php'); ?>

<body>

<div class="main-content">
    <h1>Add Stock</h1>
    
    <!-- Display success/error message -->
    <?php if ($message): ?>
        <div class="message"><?= $message; ?></div>
    <?php endif; ?>

    <!-- Search bar to look for products -->
    <input type="text" id="search" placeholder="Search for a product...">
    <div id="search-results"></div>

    <form action="add-stock.php" method="POST" class="create-form">
        <input type="hidden" id="product_id" name="product_id">

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" disabled>
        </div>

        <div class="form-group">
            <label for="quantified_as">Quantified As</label>
            <input type="text" id="quantified_as" name="quantified_as" disabled>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buying_price">Buying Price</label>
                <input type="number" id="buying_price" name="buying_price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="selling_price">Selling Price</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>
        </div>

        <button type="submit">Purchase</button>
        <button type="reset">Reset</button>
    </form>
</div>

<!-- Include JavaScript to handle product search and autofill -->
<script>
document.getElementById('search').addEventListener('input', function() {
    var searchQuery = this.value;

    if (searchQuery.length > 2) {
        fetch('search_product.php?query=' + searchQuery)
        .then(response => response.json())
        .then(data => {
            var results = document.getElementById('search-results');
            results.innerHTML = '';
            data.forEach(function(product) {
                var div = document.createElement('div');
                div.textContent = product.name;
                div.addEventListener('click', function() {
                    document.getElementById('product_id').value = product.product_id;
                    document.getElementById('name').value = product.name;
                    document.getElementById('quantified_as').value = product.quantified;
                    document.getElementById('buying_price').value = product.buying_price;
                    document.getElementById('selling_price').value = product.selling_price;
                });
                results.appendChild(div);
            });
        });
    }
});
</script>

</body>
</html>
