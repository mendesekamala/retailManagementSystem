<?php 
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Products</title>
    <link rel="stylesheet" href="css/sell.css">
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="sell-container">
        <div class="left-section">
            <form id="sell-form">
                <div class="section">
                    <label for="search-product">Search Product</label>
                    <input type="text" id="search-product" name="search-product" placeholder="Search product...">
                    <div id="suggestions" class="suggestions"></div>
                </div>

                <div class="section">
                    <p>Sell in Whole</p>
                    <hr>
                    <div class="row">
                        <input type="text" id="whole-product-name" placeholder="Product Name" readonly>
                        <input type="text" id="whole-quantified-as" placeholder="Quantified As" readonly>
                    </div>
                    <div class="row">
                        <input type="text" id="whole-buying-price" placeholder="Buying Price" readonly>
                        <input type="text" id="whole-selling-price" placeholder="Selling Price" readonly>
                        <input type="number" id="whole-quantity" placeholder="Quantity">
                    </div>
                </div>

                <div class="section">
                    <p>Sell in Units</p>
                    <hr>
                    <div class="row">
                        <select id="unit-sell-as">
                            <option value="" disabled selected>Select Unit</option>
                        </select>
                        <input type="text" id="unit-relation" placeholder="Relation to One" readonly>
                    </div>
                    <div class="row">
                        <input type="number" id="unit-id" readonly hidden >
                        <input type="text" id="unit-buying-price" placeholder="Buying Price" readonly>
                        <input type="text" id="unit-selling-price" placeholder="Selling Price" readonly>
                        <input type="number" id="unit-quantity" placeholder="Unit Quantities">
                    </div>
                </div>

                

                <div class="row buttons">
                    <button type="button" id="add-product">Add Product</button>
                    <button type="reset">Reset</button>
                </div>
            </form>
        </div>

        <div class="right-section">

                <!-- New Section: Customer Details and Payment Method -->
                <div class="section">
                <label for="Invoice-details">Invoice details </label>
                <input type="text" id="customer-name" placeholder="Customer Name" >
                </div>

                <div class="section">
                    <select id="payment-method">
                        <option value="cash">payment method</option>
                        <option value="cash">Cash</option>
                        <option value="debt">Debt</option>
                    </select>
                </div>

                <div class="section" id="debt-amount-section" style="display: none;">
                    <label for="debt-amount">Debt Amount</label>
                    <input type="number" id="debt-amount" placeholder="Enter Debt Amount">
                </div>

            <table id="order-list">
                <thead>
                    <tr>
                        <th>X</th>
                        <th>S/N</th>
                        <th>Product Name</th>
                        <th>Qnt</th>
                        <th>@</th>
                        <th>Sum</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Order list will be populated dynamically -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">TOTAL</td>
                        <td id="total-sum">0.00</td>
                    </tr>
                </tfoot>
            </table>
            <button id="complete-order">Complete Order</button>
        </div>
    </div>

    <script src="scripts/sell.js"></script>
</body>
</html>
