<?php 
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Products</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/sell.css">
</head>

<?php include('sidebar.php'); ?>
<?php include('payment-modal_sales.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Sales Dashboard</h1>
        </header>

        <div class="sell-content">
            <div class="card sell-form-card">
                <form id="sell-form">
                    <div class="form-section">
                        <h3>Product Information</h3>
                        <div class="form-group">
                            <label for="search-product">Search Product</label>
                            <div class="search-container">
                                <input type="text" id="search-product" name="search-product" placeholder="Enter product name">
                                <div id="suggestions" class="suggestions-dropdown"></div>
                            </div>
                        </div>

                        <div class="form-section-inner whole-section">
                            <h4>Sell in Whole</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="whole-product-name">Product Name</label>
                                    <input type="text" id="whole-product-name" placeholder="Product Name" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="whole-quantified-as">Quantified As</label>
                                    <input type="text" id="whole-quantified-as" placeholder="Quantified As" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="whole-buying-price">Buying Price</label>
                                    <input type="text" id="whole-buying-price" placeholder="Buying Price" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="whole-selling-price">Selling Price</label>
                                    <input type="text" id="whole-selling-price" placeholder="Selling Price" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="whole-quantity">Quantity</label>
                                    <input type="number" id="whole-quantity" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="toggle-section">
                            <div class="toggle-btn" id="switch-units">
                                <span>Sell in Units</span>
                                <i class='bx bx-chevron-down'></i>
                            </div>
                        </div>

                        <div class="form-section-inner units-section" id="units-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="unit-sell-as">Unit Type</label>
                                    <select id="unit-sell-as">
                                        <option value="" disabled selected>Select Unit</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="unit-relation">Relation to Whole</label>
                                    <input type="text" id="unit-relation" placeholder="Relation to One" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="unit-buying-price">Buying Price</label>
                                    <input type="text" id="unit-buying-price" placeholder="Buying Price" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="unit-selling-price">Selling Price</label>
                                    <input type="text" id="unit-selling-price" placeholder="Selling Price" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="unit-quantity">Quantity</label>
                                    <input type="number" id="unit-quantity" placeholder="0" min="0">
                                </div>
                            </div>
                            <input type="number" id="unit-id" readonly hidden>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="add-product" class="btn-primary">
                                <i class='bx bx-plus'></i> Add Product
                            </button>
                            <button type="reset" class="btn-secondary">
                                <i class='bx bx-reset'></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card order-summary-card">
                <div class="form-section">
                    <h3>Invoice Details</h3>
                    <div class="form-group">
                        <label for="customer-name">Customer Name</label>
                        <input type="text" id="customer-name" placeholder="Enter customer name">
                    </div>
                </div>

                <div class="order-table-container">
                    <table id="order-list">
                        <thead>
                            <tr>
                                <th>X</th>
                                <th>S/N</th>
                                <th>Product Name</th>
                                <th>Qty</th>
                                <th>@</th>
                                <th>Sum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Order list will be populated dynamically -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="total-label">TOTAL</td>
                                <td id="total-sum">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button onclick="openPaymentModal()" class="btn-primary complete-order-btn">
                    <i class='bx bx-check-circle'></i> Complete Order
                </button>
            </div>
        </div>
    </div>

    <script src="scripts/sell.js"></script>
    <script src="scripts/payment_sales.js"></script>
    <script>
        // Global variables - only declare once
        window.App = window.App || {};
        App.orderList = [];
        App.totalProfit = 0;
        App.totalAmount = 0;

        // Open the modal - make sure this is globally accessible
        window.openPaymentModal = function() {
            console.log('Current orderList:', App.orderList); // Debug
            
            if (!App.orderList || App.orderList.length === 0) {
                alert('No items in the order list');
                return;
            }

            const suggestions = document.getElementById("suggestions");
            if (suggestions) suggestions.style.display = "none";

            const total = parseFloat(document.getElementById("total-sum").textContent);
            const customerName = document.getElementById('customer-name').value;

            App.salesData = {
                orderList: App.orderList, 
                customer_name: customerName,
                total: total,
                total_profit: App.totalProfit,
            };
            
            localStorage.setItem('salesData', JSON.stringify(App.salesData));
            
            // Call the modal open function from payment_sales.js
            if (typeof window.openModal === 'function') {
                window.openModal(total.toFixed(2));
            } else {
                console.error('openModal function not found');
            }
        };

        // Global function for removing items
        window.removeItem = function(index) {
            const event = new CustomEvent('removeItem', { detail: { index } });
            document.dispatchEvent(event);
        };
</script>
</body>
</html>