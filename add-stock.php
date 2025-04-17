<?php
session_start();
include('db_connection.php');

// Fetch available payment methods
$company_id = $_SESSION['company_id'];
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

<?php include('payment-modal.php'); ?>

<body>

<div class="main-content">
    <h1>Add Stock</h1>

    <input type="text" id="search" placeholder="Search for a product...">
    <div id="search-results"></div>

    <form id="addStockForm" class="create-form">
        <input type="hidden" id="product_id" name="product_id">

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" disabled>
        </div>

        <div class="form-row">
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
        </div>

        <button type="button" onclick="openPaymentModal()">Purchase</button>
        <button type="reset">Reset</button>
    </form>
</div>

<script src="scripts/payment.js"></script>
<script>
        function openPaymentModal() {
            let product_id = document.getElementById('product_id').value;
            let quantity = document.getElementById('quantity').value;
            let buying_price = document.getElementById('buying_price').value;
            let selling_price = document.getElementById('selling_price').value;

            if (!product_id || !quantity || !buying_price || !selling_price) {
                alert("Please fill all fields before proceeding!");
                return;
            }

            let grandTotal = quantity * buying_price;
            let purchaseData = {
                product_id, quantity, buying_price, selling_price, grandTotal
            };

            localStorage.setItem('purchaseData', JSON.stringify(purchaseData));
            openModal(grandTotal);
        }

    // <!-- Include JavaScript to handle product search and autofill -->
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
