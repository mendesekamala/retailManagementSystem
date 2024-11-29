<?php
session_start();
include('db_connection.php');

// Initialize an empty message variable
$message = '';

// Fetch available payment methods for the logged-in user's company
$company_id = $_SESSION['company_id'];
$payment_methods_sql = "SELECT * FROM payment_methods WHERE company_id = ?";
$payment_methods_stmt = $conn->prepare($payment_methods_sql);
$payment_methods_stmt->bind_param("i", $company_id);
$payment_methods_stmt->execute();
$payment_methods_result = $payment_methods_stmt->get_result();
$available_payment_methods = [];

while ($row = $payment_methods_result->fetch_assoc()) {
    foreach ($row as $method => $value) {
        // Only add methods that are active (value is 'yes')
        if ($value == 'yes' && $method != 'company_id' && $method != 'created_by') {
            $available_payment_methods[] = $method;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $payment_method = $_POST['payment_method'];

    // Get the created_by value from session
    $created_by = $_SESSION['user_id'];

    // Update product information
    $update_sql = "UPDATE products SET buying_price = ?, selling_price = ?, quantity = quantity + ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ddii", $buying_price, $selling_price, $quantity, $product_id);

    if ($stmt->execute()) {
        // Fetch units with the matching product_id
        $fetch_units_sql = "SELECT unit_id, available_units, per_single_quantity FROM units WHERE product_id = ?";
        $units_stmt = $conn->prepare($fetch_units_sql);
        $units_stmt->bind_param("i", $product_id);
        $units_stmt->execute();
        $units_result = $units_stmt->get_result();

        while ($unit = $units_result->fetch_assoc()) {
            $unit_id = $unit['unit_id'];
            $available_units = $unit['available_units'];
            $per_single_quantity = $unit['per_single_quantity'];

            // Calculate the total units to add
            $units_to_add = $per_single_quantity * $quantity;
            $new_available_units = $available_units + $units_to_add;

            // Update the units table
            $update_units_sql = "UPDATE units SET available_units = ? WHERE unit_id = ?";
            $update_units_stmt = $conn->prepare($update_units_sql);
            $update_units_stmt->bind_param("di", $new_available_units, $unit_id);
            $update_units_stmt->execute();
        }

        // Calculate total purchase amount
        $total_purchase = $buying_price * $quantity;

        // Update the money table based on the selected payment method
        if (in_array($payment_method, $available_payment_methods)) {
            $update_money_sql = "UPDATE money SET $payment_method = $payment_method - ? WHERE company_id = ?";
            $money_stmt = $conn->prepare($update_money_sql);
            $money_stmt->bind_param("di", $total_purchase, $company_id);
            $money_stmt->execute();
        }

        // Insert purchased item directly into `purchases_items` table
        $insert_item_sql = "INSERT INTO purchases_items (product_id, product_name, quantity, buying_price, selling_price, date_made, total)
                            VALUES (?, (SELECT name FROM products WHERE product_id = ?), ?, ?, ?, NOW(), ?)";
        $item_stmt = $conn->prepare($insert_item_sql);
        $item_stmt->bind_param("iiiddd", $product_id, $product_id, $quantity, $buying_price, $selling_price, $total_purchase);
        $item_stmt->execute();

        // Insert the purchase transaction into `transactions` table
        $get_product_name_sql = "SELECT name FROM products WHERE product_id = ?";
        $product_name_stmt = $conn->prepare($get_product_name_sql);
        $product_name_stmt->bind_param("i", $product_id);
        $product_name_stmt->execute();
        $product_name_result = $product_name_stmt->get_result();
        $product_name_row = $product_name_result->fetch_assoc();
        $product_name = $product_name_row['name'];

        // Insert the transaction with company_id and created_by
        $insert_transaction_sql = "INSERT INTO transactions (transaction_type, amount, description, date_made, company_id, created_by)
                                   VALUES ('purchase', ?, ?, NOW(), ?, ?)";
        $transaction_stmt = $conn->prepare($insert_transaction_sql);
        $transaction_stmt->bind_param("dsii", $total_purchase, $product_name, $company_id, $created_by);
        $transaction_stmt->execute();

        $message = "Stock updated, purchase recorded, and transaction added successfully!";
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

        <div class="form-row">
            <div class="form-group">
                <label for="quantified_as">Quantified As</label>
                <input type="text" id="quantified_as" name="quantified_as" disabled>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>
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
                <label for="payment_method">Pay By</label>
                <select name="payment_method" id="payment_method">
                    <?php
                    // Dynamically populate payment methods based on the available methods
                    foreach ($available_payment_methods as $method) {
                        echo "<option value=\"$method\">$method</option>";
                    }
                    ?>
                </select>
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
