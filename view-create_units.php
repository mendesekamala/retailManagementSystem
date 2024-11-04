<?php
session_start();
include 'db_connection.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$product_query = "SELECT * FROM products WHERE product_id = $product_id";
$product_result = mysqli_query($conn, $product_query);
$product = mysqli_fetch_assoc($product_result);

// Fetch units for the product
$unit_query = "SELECT * FROM units WHERE product_id = $product_id";
$unit_result = mysqli_query($conn, $unit_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/view-create_units.css">
    <title>View/Create Units</title>
</head>

<?php include('sidebar.php'); ?>

<?php
// Display feedback messages
if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<div class='error-message'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}
?>


<body>
    <div class="main-content">
        <div class="units-container">
            <h1>Units for <?= $product['name']; ?></h1>

            <!-- Display units list -->
            <table class="units-table">
                <thead>
                    <tr>
                        <th>Unit Name</th>
                        <th>Per Single Quantity</th>
                        <th>Buying Price</th>
                        <th>Selling Price</th>
                        <th>Available Units</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($unit = mysqli_fetch_assoc($unit_result)): ?>
                    <tr>
                        <td><?= $unit['name']; ?></td>
                        <td><?= $unit['per_single_quantity']; ?></td>
                        <td><?= $unit['buying_price']; ?></td>
                        <td><?= $unit['selling_price']; ?></td>
                        <td><?= $unit['available_units']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Add new unit form -->
            <h2>Add New Unit</h2>
            <form action="confirm-new_unit.php" method="POST" class="unit-form">
                <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                <label>Unit Name:</label>
                <input type="text" name="name" required>
                
                <label>Per Single Quantity:</label>
                <input type="number" step="0.01" name="per_single_quantity" required oninput="calculateAvailableUnits(<?= $product['quantity']; ?>)">

                <label>Buying Price:</label>
                <input type="number" step="0.01" name="buying_price" required>

                <label>Selling Price:</label>
                <input type="number" step="0.01" name="selling_price" required>

                <label>Available Units:</label>
                <input type="text" id="available_units" name="available_units" readonly>

                <button type="submit">Confirm Unit</button>
            </form>
        </div>

        <script>
            function calculateAvailableUnits(quantity) {
                const perSingleQuantity = document.querySelector('input[name="per_single_quantity"]').value;
                const availableUnitsField = document.getElementById('available_units');
                availableUnitsField.value = (quantity * perSingleQuantity).toFixed(2);
            }
        </script>
    </div>    
</body>
</html>
