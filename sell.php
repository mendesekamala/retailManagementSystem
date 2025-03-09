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
<?php include('payment-modal_sales.php'); ?>

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

                

                
                <div class="switch-units-div">
                    <div class="toggle-btn" id="switch-units">
                        <span>Sell in Units</span>
                        <i class="bx bx-chevron-down"></i>
                    </div>
                    <hr>
                </div>


                <div class="section" id="units-section">
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
                        <td id="total-sum">0</td>
                    </tr>
                </tfoot>
            </table>
            <button onclick="openPaymentModal()">Complete Order</button>
        </div>
    </div>




    <script src="scripts/sell.js"></script>
    <script src="scripts/payment_sales.js"></script>
    <script>
    // Open the modal
    function openPaymentModal() {
        if (orderList.length === 0) {
            alert('No items in the order list');
            return;
        }

        const suggestions = document.getElementById("suggestions"); // Get the suggestions div
        suggestions.style.display = "none";  // This hides the suggestions div

        let grandTotal = document.getElementById("total-sum").textContent;
        const total = parseFloat(document.getElementById("total-sum").textContent);
        const customerName = document.getElementById('customer-name').value;


        let salesData = {
            orderList: orderList, 
            customer_name: customerName,
            total: total,
            total_profit: totalProfit,
        };
        

        localStorage.setItem('salesData', JSON.stringify(salesData));
        openModal(grandTotal);
    }
    </script>
</body>
</html>
