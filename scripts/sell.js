document.getElementById('search-product').addEventListener('keyup', function() {
    const query = this.value;

    if (query.length > 2) {
        // Send AJAX request to fetch products
        fetch('search-product.php?query=' + query)
            .then(response => response.json())
            .then(data => {
                const suggestions = document.getElementById('suggestions');
                suggestions.innerHTML = ''; // Clear previous suggestions
                data.forEach(product => {
                    const div = document.createElement('div');
                    div.classList.add('suggestion-item');  // Add the correct class
                    div.textContent = product.name;

                    div.addEventListener('click', function() {
                        fillProductDetails(product);
                        suggestions.innerHTML = '';
                    });

                    suggestions.appendChild(div);
                });
            });
    }
});


function fillProductDetails(product) {
    const wholeProductNameInput = document.getElementById('whole-product-name');
    wholeProductNameInput.value = product.name;
    wholeProductNameInput.dataset.productId = product.product_id; // Store product_id

    document.getElementById('whole-quantified-as').value = product.quantified;
    document.getElementById('whole-buying-price').value = product.buying_price;
    document.getElementById('whole-selling-price').value = product.selling_price;

    // Fetch unit details
    fetch('fetch-units.php?product_id=' + product.product_id)
        .then(response => response.json())
        .then(data => {
            const unitSelect = document.getElementById('unit-sell-as');
            unitSelect.innerHTML = '<option disabled selected>Select Unit</option>';
            data.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.unit_id;
                option.textContent = unit.name;
                option.dataset.perSingleQuantity = unit.per_single_quantity;
                option.dataset.buyingPrice = unit.buying_price;
                option.dataset.sellingPrice = unit.selling_price;
                unitSelect.appendChild(option);
            });
        });
}

document.getElementById('unit-sell-as').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('unit-relation').value = selectedOption.dataset.perSingleQuantity;
    document.getElementById('unit-buying-price').value = selectedOption.dataset.buyingPrice;
    document.getElementById('unit-selling-price').value = selectedOption.dataset.sellingPrice;
});


// Order list array to hold added items
let orderList = [];
let totalAmount = 0;
let totalProfit = 0;

document.getElementById('add-product').addEventListener('click', addProduct);

document.getElementById('add-product').addEventListener('click', addProduct);

document.getElementById('add-product').addEventListener('click', addProduct);

function addProduct() {
    const wholeProductName = document.getElementById('whole-product-name').value;
    const wholeQuantity = parseFloat(document.getElementById('whole-quantity').value) || 0;
    const wholeSellingPrice = parseFloat(document.getElementById('whole-selling-price').value) || 0;
    const wholeBuyingPrice = parseFloat(document.getElementById('whole-buying-price').value) || 0;
    const productId = document.getElementById('whole-product-name').dataset.productId;

    const unitSellAs = document.getElementById('unit-sell-as').value;
    const unitQuantity = parseFloat(document.getElementById('unit-quantity').value) || 0;
    const unitSellingPrice = parseFloat(document.getElementById('unit-selling-price').value) || 0;
    const unitBuyingPrice = parseFloat(document.getElementById('unit-buying-price').value) || 0;
    const unitId = document.getElementById('unit-sell-as').selectedOptions[0].value; // Get the selected unit_id

    if (!productId) {
        alert("Please select a product.");
        return;
    }

    // Fetch product details for validation
    fetch(`validate-product.php?product_id=${productId}&unit_id=${unitId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            const availableWholeQuantity = parseFloat(data.quantity);
            const availableUnitQuantity = parseFloat(data.available_units);

            // Validate whole quantity
            if (wholeQuantity > 0) {
                if (wholeQuantity > availableWholeQuantity) {
                    alert(`Input quantity to be sold is greater than the remaining quantity (${availableWholeQuantity}).`);
                    return;
                }

                const profit = (wholeSellingPrice - wholeBuyingPrice) * wholeQuantity;
                const productDetails = {
                    product_id: productId,
                    name: wholeProductName,
                    quantity: wholeQuantity,
                    price: wholeSellingPrice,
                    buying_price: wholeBuyingPrice,
                    sum: wholeQuantity * wholeSellingPrice,
                    profit: profit,
                    unit_type: 'whole',
                };

                orderList.push(productDetails);
            }

            // Validate unit quantity
            if (unitQuantity > 0) {
                if (unitQuantity > availableUnitQuantity) {
                    alert(`Unit quantity to be sold is greater than the remaining unit quantities (${availableUnitQuantity}).`);
                    return;
                }

                const unitRelation = document.getElementById('unit-relation').value;
                const selectedOption = document.getElementById('unit-sell-as').options[document.getElementById('unit-sell-as').selectedIndex];
                const unitName = selectedOption.text;

                const profit = (unitSellingPrice - unitBuyingPrice) * unitQuantity;
                const productDetails = {
                    product_id: productId,
                    name: `${wholeProductName} (${unitName})`,
                    quantity: unitQuantity,
                    price: unitSellingPrice,
                    buying_price: unitBuyingPrice,
                    sum: unitQuantity * unitSellingPrice,
                    profit: profit,
                    unit_type: 'unit',
                    unit_id: unitId,
                };

                orderList.push(productDetails);
            }

            if (wholeQuantity === 0 && unitQuantity === 0) {
                alert('Please provide quantity for either whole or unit.');
                return;
            }

            updateOrderList();
            resetOrderForm();
        })
        .catch(error => {
            console.error('Error validating product:', error);
            alert('Failed to validate product. Please try again.');
        });
}



function updateOrderList() {
    let orderTableBody = document.getElementById('order-list').getElementsByTagName('tbody')[0];
    orderTableBody.innerHTML = '';
    totalAmount = 0;
    totalProfit = 0;

    orderList.forEach((item, index) => {
        totalAmount += parseFloat(item.sum);
        totalProfit += parseFloat(item.profit);
        let row = `
            <tr>
                <td><button onclick="removeItem(${index})">X</button></td>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>@ ${item.price}</td>
                <td>${item.sum.toFixed(0)}</td>
            </tr>
        `;
        orderTableBody.innerHTML += row;
    });

    document.getElementById('total-sum').innerText = totalAmount.toFixed(0);
}

function removeItem(index) {
    orderList.splice(index, 1);
    updateOrderList();
}


function resetOrderForm() {
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
    document.getElementById('customer-name').value = '';
}




document.addEventListener("DOMContentLoaded", function () {


    // Get the toggle button and the section
    const switchUnitsBtn = document.getElementById('switch-units');
    const unitsSection = document.getElementById('units-section');
    const addButUnit = document.getElementById('add-but-unit');
    const addButWhole = document.getElementById('add-but-whole');

    // Ensure the units section is hidden initially
    unitsSection.style.display = 'none';
    // addButUnit.style.display = 'none';

    // Add click event listener to the toggle button
    switchUnitsBtn.addEventListener('click', function () {
        // Toggle the visibility of the units section
        if (unitsSection.style.display === 'none') {
            unitsSection.style.display = 'block';
            // addButUnit.style.display = 'flex';
            // addButWhole.style.display = 'none';
            
        } else {
            unitsSection.style.display = 'none';
            // addButUnit.style.display = 'none';
            // addButWhole.style.display = 'flex';
        }
    });
        
});




