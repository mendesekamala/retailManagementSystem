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
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/add-stock.css">
</head>

<?php include('sidebar.php'); ?>

<?php include('payment-modal.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="page-header">
            <h1><i class='bx bx-plus-circle'></i> Add Stock</h1>
        </header>

        <div class="two-panel-layout">
            <!-- Left Panel - Form -->
            <div class="form-panel">
                <div class="card create-form-card">
                    <div class="search-container">
                        <div class="input-with-icon">
                            <i class='bx bx-search'></i>
                            <input type="text" id="search" placeholder="Search for a product...">
                        </div>
                        <div id="search-results" class="search-results-dropdown"></div>
                    </div>

                    <form id="addStockForm" class="create-form">
                        <input type="hidden" id="product_id" name="product_id">

                        <div class="form-section">
                            <h3>Product Information</h3>

                            <div class="form-group">
                                <label for="name">Product Name</label>
                                <input type="text" id="name" name="name" disabled>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="buying_price">Buying Price</label>
                                    <div class="input-with-icon">
                                        <i class=''>Tsh</i>
                                        <input type="number" id="buying_price" name="buying_price" step="0.01" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="selling_price">Selling Price</label>
                                    <div class="input-with-icon">
                                        <i class=''>Tsh</i>
                                        <input type="number" id="selling_price" name="selling_price" step="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" required>
                                </div>

                                <div class="form-group">
                                    <label for="supplier_name">Supplier's Name</label>
                                    <input type="text" id="supplier_name" name="supplier_name" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-primary" onclick="openPaymentModal()">
                                    <i class='bx bx-purchase-tag'></i> Purchase
                                </button>
                                <button type="reset" class="btn-secondary">
                                    <i class='bx bx-reset'></i> Reset
                                </button>
                                <button type="button" class="btn-view-image" id="viewImageBtn" style="display: none;">
                                    <i class='bx bx-image'></i> View Image
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Panel - Purchase Information -->
            <div class="welcome-panel">
                <div class="welcome-content">
                    <div class="welcome-header">
                        <h2>Stock Purchase Guide</h2>
                        <p class="system-version">Best Practices</p>
                    </div>
                    
                    <div class="welcome-features">
                        <div class="feature-item">
                            <i class='bx bx-check-circle'></i>
                            <span>Always verify product details before purchase</span>
                        </div>
                        <div class="feature-item">
                            <i class='bx bx-check-circle'></i>
                            <span>Update prices to reflect current market rates</span>
                        </div>
                        <div class="feature-item">
                            <i class='bx bx-check-circle'></i>
                            <span>Record accurate quantities for inventory tracking</span>
                        </div>
                    </div>
                    
                    <div class="quick-stats">
                        <div class="stat-card">
                            <i class='bx bx-info-circle'></i>
                            <div>
                                <h3>Purchase Tips</h3>
                                <p>Check supplier reliability</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class='bx bx-calendar'></i>
                            <div>
                                <h3>Next Order</h3>
                                <p id="nextOrderDate">Check stock levels</p>
                            </div>
                        </div>
                    </div>

                    <div class="recent-purchases">
                        <h3>Recent Purchases</h3>
                        <div class="purchase-list" id="recentPurchases">
                            <!-- Will be populated by JavaScript -->
                            <div class="empty-state">
                                <i class='bx bx-package'></i>
                                <p>No recent purchases found</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img id="modalImage" src="" alt="Product Image" style="max-width: 100%; max-height: 80vh;">
        </div>
    </div>

    <script src="scripts/payment.js"></script>
    <script>
        function openPaymentModal() {
            let product_id = document.getElementById('product_id').value;
            let quantity = document.getElementById('quantity').value;
            let buying_price = document.getElementById('buying_price').value;
            let selling_price = document.getElementById('selling_price').value;
            let supplier_name = document.getElementById('supplier_name').value;

            if (!product_id || !quantity || !buying_price || !selling_price || !supplier_name) {
                alert("Please fill all fields before proceeding!");
                return;
            }

            let grandTotal = quantity * buying_price;
            let purchaseData = {
                product_id, quantity, buying_price, selling_price, supplier_name, grandTotal
            };

            localStorage.setItem('purchaseData', JSON.stringify(purchaseData));
            openModal(grandTotal);
        }

        document.getElementById('search').addEventListener('input', function() {
            var searchQuery = this.value;

            if (searchQuery.length > 2) {
                fetch('search_product.php?query=' + searchQuery)
                .then(response => response.json())
                .then(data => {
                    var results = document.getElementById('search-results');
                    results.innerHTML = '';
                    
                    if (data.length > 0) {
                        results.style.display = 'block';
                        data.forEach(function(product) {
                            var div = document.createElement('div');
                            div.className = 'search-result-item';
                            div.innerHTML = `
                                <div class="product-name">${product.name}</div>
                                ${product.image_path ? `<div class="product-image-preview" style="background-image: url('${product.image_path}')"></div>` : ''}
                            `;
                            
                            div.addEventListener('click', function() {
                                document.getElementById('product_id').value = product.product_id;
                                document.getElementById('name').value = product.name;
                                document.getElementById('buying_price').value = product.buying_price;
                                document.getElementById('selling_price').value = product.selling_price;
                                
                                // Show/hide view image button based on whether product has image
                                const viewImageBtn = document.getElementById('viewImageBtn');
                                if (product.image_path) {
                                    viewImageBtn.style.display = 'inline-flex';
                                    viewImageBtn.onclick = function() {
                                        document.getElementById('modalImage').src = product.image_path;
                                        document.getElementById('imageModal').style.display = 'flex';
                                    };
                                } else {
                                    viewImageBtn.style.display = 'none';
                                }
                                
                                // Load recent purchases for this product
                                loadRecentPurchases(product.product_id);
                                
                                results.style.display = 'none';
                            });
                            results.appendChild(div);
                        });
                    } else {
                        results.style.display = 'none';
                    }
                });
            } else {
                document.getElementById('search-results').style.display = 'none';
            }
        });

        function loadRecentPurchases(productId) {
            // This would be replaced with actual API call to fetch recent purchases
            fetch('get_recent_purchases.php?product_id=' + productId)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recentPurchases');
                    if (data.length > 0) {
                        container.innerHTML = '';
                        data.forEach(purchase => {
                            const purchaseItem = document.createElement('div');
                            purchaseItem.className = 'purchase-item';
                            purchaseItem.innerHTML = `
                                <div class="purchase-date">${new Date(purchase.purchase_date).toLocaleDateString()}</div>
                                <div class="purchase-details">
                                    <span>${purchase.quantity} units</span>
                                    <span>at $${purchase.buying_price} each</span>
                                    ${purchase.supplier_name ? `<span>from ${purchase.supplier_name}</span>` : ''}
                                </div>
                                <div class="purchase-total">$${(purchase.quantity * purchase.buying_price).toFixed(2)}</div>
                            `;
                            container.appendChild(purchaseItem);
                        });
                    } else {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class='bx bx-package'></i>
                                <p>No recent purchases found</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading recent purchases:', error);
                });
        }

        // Close the image modal when clicking the X
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('imageModal').style.display = 'none';
        });

        // Close the image modal when clicking outside the image
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Initialize with empty state
        loadRecentPurchases(null);
    </script>
</body>
</html>