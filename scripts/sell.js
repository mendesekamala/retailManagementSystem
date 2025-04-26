// Global namespace for our application variables
window.App = window.App || {};
App.orderList = App.orderList || [];
App.totalAmount = App.totalAmount || 0;
App.totalProfit = App.totalProfit || 0;

document.addEventListener("DOMContentLoaded", function () {
    // Get DOM elements
    const switchUnitsBtn = document.getElementById('switch-units');
    const unitsSection = document.getElementById('units-section');
    const wholeSection = document.querySelector('.whole-section');
    const suggestionsContainer = document.getElementById('suggestions');
    
    // Hide units section initially and set whole as default
    unitsSection.style.display = 'none';
    wholeSection.classList.add('active-selling-mode');

    // Setup event listeners
    setupEventListeners();

    function setupEventListeners() {
        // Product search functionality
        document.getElementById('search-product').addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                fetchProducts(query);
            } else {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
            }
        });

        // Unit selection change
        document.getElementById('unit-sell-as').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                document.getElementById('unit-relation').value = selectedOption.dataset.perSingleQuantity;
                document.getElementById('unit-buying-price').value = selectedOption.dataset.buyingPrice;
                document.getElementById('unit-selling-price').value = selectedOption.dataset.sellingPrice;
            }
        });

        // Toggle between selling modes
        switchUnitsBtn.addEventListener('click', toggleSellingMode);

        // Add product button
        document.getElementById('add-product').addEventListener('click', addProduct);

        // Prevent negative quantities
        document.getElementById('whole-quantity').addEventListener('input', validateQuantity);
        document.getElementById('unit-quantity').addEventListener('input', validateQuantity);
        
        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    function fetchProducts(query) {
        fetch('search-product.php?query=' + query)
            .then(response => response.json())
            .then(data => {
                suggestionsContainer.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(product => {
                        const div = document.createElement('div');
                        div.classList.add('suggestion-item');
                        div.textContent = product.name;

                        div.addEventListener('click', function() {
                            fillProductDetails(product);
                            suggestionsContainer.style.display = 'none';
                        });

                        suggestionsContainer.appendChild(div);
                    });
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                suggestionsContainer.style.display = 'none';
            });
    }

    function fillProductDetails(product) {
        // Fill whole sale fields
        const wholeProductNameInput = document.getElementById('whole-product-name');
        wholeProductNameInput.value = product.name;
        wholeProductNameInput.dataset.productId = product.product_id;

        document.getElementById('whole-quantified-as').value = product.quantified;
        document.getElementById('whole-buying-price').value = product.buying_price;
        document.getElementById('whole-selling-price').value = product.selling_price;
        document.getElementById('whole-quantity').value = '';
        document.getElementById('search-product').value = product.name;

        // Fetch and fill unit details
        fetch('fetch-units.php?product_id=' + product.product_id)
            .then(response => response.json())
            .then(data => {
                const unitSelect = document.getElementById('unit-sell-as');
                unitSelect.innerHTML = '<option value="" disabled selected>Select Unit</option>';
                
                if (data.length > 0) {
                    data.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.unit_id;
                        option.textContent = unit.name;
                        option.dataset.perSingleQuantity = unit.per_single_quantity;
                        option.dataset.buyingPrice = unit.buying_price;
                        option.dataset.sellingPrice = unit.selling_price;
                        unitSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching units:', error);
            });
    }

    function toggleSellingMode() {
        const chevronIcon = switchUnitsBtn.querySelector('i');
        
        if (unitsSection.style.display === 'none') {
            // Switching to units mode
            unitsSection.style.display = 'block';
            wholeSection.classList.remove('active-selling-mode');
            wholeSection.classList.add('disabled-input');
            chevronIcon.style.transform = 'rotate(180deg)';
        } else {
            // Switching back to whole mode
            unitsSection.style.display = 'none';
            wholeSection.classList.add('active-selling-mode');
            wholeSection.classList.remove('disabled-input');
            chevronIcon.style.transform = 'rotate(0deg)';
        }
    }

    function validateQuantity() {
        if (this.value < 0) this.value = 0;
    }

    function addProduct() {
        // Get whole sale values
        const wholeProductName = document.getElementById('whole-product-name').value;
        const wholeQuantity = parseFloat(document.getElementById('whole-quantity').value) || 0;
        const wholeSellingPrice = parseFloat(document.getElementById('whole-selling-price').value) || 0;
        const wholeBuyingPrice = parseFloat(document.getElementById('whole-buying-price').value) || 0;
        const productId = document.getElementById('whole-product-name').dataset.productId;

        // Get unit sale values
        const unitSellAs = document.getElementById('unit-sell-as').value;
        const unitQuantity = parseFloat(document.getElementById('unit-quantity').value) || 0;
        const unitSellingPrice = parseFloat(document.getElementById('unit-selling-price').value) || 0;
        const unitBuyingPrice = parseFloat(document.getElementById('unit-buying-price').value) || 0;
        const unitId = document.getElementById('unit-sell-as').selectedOptions[0]?.value;

        // Validate product selection
        if (!productId) {
            alert("Please select a product.");
            return;
        }

        // Validate selling mode (can't do both)
        if (wholeQuantity > 0 && unitQuantity > 0) {
            alert("Please sell either in whole OR in units, not both.");
            return;
        }

        // Validate unit selection when selling in units
        if (unitQuantity > 0 && (!unitSellAs || unitSellAs === "")) {
            alert("Please select a unit type when selling in units.");
            return;
        }

        // Validate quantity
        if (wholeQuantity <= 0 && unitQuantity <= 0) {
            alert("Please provide quantity for either whole or unit.");
            return;
        }

        // Validate product availability
        validateProductAvailability(productId, unitId, wholeQuantity, unitQuantity, 
            wholeProductName, wholeSellingPrice, wholeBuyingPrice, unitSellingPrice, unitBuyingPrice);
    }

    function validateProductAvailability(productId, unitId, wholeQuantity, unitQuantity, 
        wholeProductName, wholeSellingPrice, wholeBuyingPrice, unitSellingPrice, unitBuyingPrice) {
        
        fetch(`validate-product.php?product_id=${productId}&unit_id=${unitId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const availableWholeQuantity = parseFloat(data.quantity);
                const availableUnitQuantity = parseFloat(data.available_units);

                // Process whole sale
                if (wholeQuantity > 0) {
                    if (wholeQuantity > availableWholeQuantity) {
                        alert(`Input quantity to be sold is greater than the remaining quantity (${availableWholeQuantity}).`);
                        return;
                    }

                    addToOrderList({
                        product_id: productId,
                        name: wholeProductName,
                        quantity: wholeQuantity,
                        price: wholeSellingPrice,
                        buying_price: wholeBuyingPrice,
                        sum: wholeQuantity * wholeSellingPrice,
                        profit: (wholeSellingPrice - wholeBuyingPrice) * wholeQuantity,
                        unit_type: 'whole'
                    });
                }
                // Process unit sale
                else if (unitQuantity > 0) {
                    if (unitQuantity > availableUnitQuantity) {
                        alert(`Unit quantity to be sold is greater than the remaining unit quantities (${availableUnitQuantity}).`);
                        return;
                    }

                    const selectedOption = document.getElementById('unit-sell-as').options[document.getElementById('unit-sell-as').selectedIndex];
                    const unitName = selectedOption.text;

                    addToOrderList({
                        product_id: productId,
                        name: `${wholeProductName} (${unitName})`,
                        quantity: unitQuantity,
                        price: unitSellingPrice,
                        buying_price: unitBuyingPrice,
                        sum: unitQuantity * unitSellingPrice,
                        profit: (unitSellingPrice - unitBuyingPrice) * unitQuantity,
                        unit_type: 'unit',
                        unit_id: unitId
                    });
                }

                updateOrderList();
                resetOrderForm();
            })
            .catch(error => {
                console.error('Error validating product:', error);
                alert('Failed to validate product. Please try again.');
            });
    }

    function addToOrderList(productDetails) {
        // Check for duplicate product in the same mode
        const existingIndex = App.orderList.findIndex(item => 
            item.product_id === productDetails.product_id && 
            item.unit_type === productDetails.unit_type &&
            (!productDetails.unit_id || item.unit_id === productDetails.unit_id)
        );

        if (existingIndex >= 0) {
            // Update existing entry instead of adding duplicate
            App.orderList[existingIndex] = productDetails;
        } else {
            App.orderList.push(productDetails);
        }
    }

    function updateOrderList() {
        const orderTableBody = document.getElementById('order-list').getElementsByTagName('tbody')[0];
        orderTableBody.innerHTML = '';
        App.totalAmount = 0;
        App.totalProfit = 0;

        App.orderList.forEach((item, index) => {
            App.totalAmount += item.sum;
            App.totalProfit += item.profit;
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><button onclick="removeItem(${index})">X</button></td>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>@ ${item.price.toFixed(2)}</td>
                <td>${item.sum.toFixed(2)}</td>
            `;
            orderTableBody.appendChild(row);
        });

        document.getElementById('total-sum').innerText = App.totalAmount.toFixed(2);
    }

    function resetOrderForm() {
        document.getElementById('search-product').value = '';
        document.getElementById('whole-product-name').value = '';
        document.getElementById('whole-quantified-as').value = '';
        document.getElementById('whole-quantity').value = '';
        document.getElementById('whole-buying-price').value = '';
        document.getElementById('whole-selling-price').value = '';
        document.getElementById('unit-sell-as').value = '';
        document.getElementById('unit-quantity').value = '';
        document.getElementById('unit-buying-price').value = '';
        document.getElementById('unit-selling-price').value = '';
        document.getElementById('unit-relation').value = '';
    }
});

// Global function for removing items
window.removeItem = function(index) {
    const event = new CustomEvent('removeItem', { detail: { index } });
    document.dispatchEvent(event);
};

// Global event listener for removeItem
document.addEventListener('removeItem', function(e) {
    App.orderList.splice(e.detail.index, 1);
    
    // Recalculate totals
    App.totalAmount = 0;
    App.totalProfit = 0;
    
    App.orderList.forEach(item => {
        App.totalAmount += item.sum;
        App.totalProfit += item.profit;
    });
    
    // Update the display
    document.getElementById('total-sum').innerText = App.totalAmount.toFixed(2);
    
    // Update the order list display
    const orderTableBody = document.getElementById('order-list').getElementsByTagName('tbody')[0];
    orderTableBody.innerHTML = '';
    
    App.orderList.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><button onclick="removeItem(${index})">X</button></td>
            <td>${index + 1}</td>
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>@ ${item.price.toFixed(2)}</td>
            <td>${item.sum.toFixed(2)}</td>
        `;
        orderTableBody.appendChild(row);
    });
});