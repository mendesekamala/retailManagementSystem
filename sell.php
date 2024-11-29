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
                    <div id="suggestions"></div>
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
            <button onclick="openModal()">Complete Order</button>
        </div>
    </div>




    <!-- Popup Modal -->
    <div id="popup-modal" class="popup-modal">
        <div class="popup-content">
            <!-- Close button -->
            <i class="bx bx-x close-btn" onclick="closeModal()"></i>

            <!-- Grand Total Section -->
            <div class="grandtotal-section">
                <h2>Grand Total: <span class="amount">0.00</span></h2>

                <!-- Customer Name Input -->
                <div class="customer-name-section">
                    <label for="customer-name">Customer Name</label>
                    <input type="text" id="customer-name" placeholder="Enter customer name">
                    <input type="hidden" id="session-company-id" value="<?php echo htmlspecialchars($_SESSION['company_id'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="toggle-btn" onclick="toggleSection('full-payment')">
                    <span>Full Payment</span>
                    <i class="bx bx-chevron-down"></i>
                </div>
                <hr>

                <!-- Full Payment Section -->
                <div class="full-payment-section" id="full-payment">
                    <label for="pay-via">Pay Via:</label>
                    <select id="pay-via"></select>
                </div>

                <div class="toggle-btn" onclick="toggleSection('double-payment')">
                    <span>Double Payment</span>
                    <i class="bx bx-chevron-down"></i>
                </div>
                <hr>

                <!-- Double Payment Section -->
                <div class="double-payment-section" id="double-payment">
                    <div class="payment-select-container">
                        <div class="payment-input">
                            <label for="payment-one">Payment One:</label>
                            <select id="payment-one"></select>
                            <input type="number" id="amount-one" placeholder="Amount One">
                        </div>
                        <div class="payment-input">
                            <label for="payment-two">Payment Two:</label>
                            <select id="payment-two"></select>
                            <input type="number" id="amount-two" placeholder="Amount Two">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finish Button -->
            <div class="finish-container">
                <button class="finish-btn">Finish</button>
            </div>
        </div>
    </div>






    <script src="scripts/sell.js"></script>
</body>
</html>
