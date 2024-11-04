document.getElementById('search-product').addEventListener('keyup', function() {
    const query = this.value;

    if (query.length > 2) {
        // Send AJAX request to fetch products
        fetch('search-product.php?query=' + query)
            .then(response => response.json())
            .then(data => {
                const suggestions = document.getElementById('suggestions');
                suggestions.innerHTML = '';
                data.forEach(product => {
                    const div = document.createElement('div');
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

// Dynamic behavior for Payment Method and Debt Amount
document.getElementById('payment-method').addEventListener('change', function() {
    const paymentMethod = this.value;
    const debtAmountSection = document.getElementById('debt-amount-section');

    if (paymentMethod === 'debt') {
        debtAmountSection.style.display = 'block'; // Show debt amount input
    } else {
        debtAmountSection.style.display = 'none';  // Hide debt amount input
        document.getElementById('debt-amount').value = ''; // Clear debt amount input
    }
});

// Order list array to hold added items
let orderList = [];
let totalAmount = 0;
let totalProfit = 0;

document.getElementById('add-product').addEventListener('click', addProduct);

function addProduct() {
    const wholeProductName = document.getElementById('whole-product-name').value;
    const wholeQuantity = document.getElementById('whole-quantity').value;
    const wholeSellingPrice = document.getElementById('whole-selling-price').value;
    const wholeBuyingPrice = document.getElementById('whole-buying-price').value;
    const productId = document.getElementById('whole-product-name').dataset.productId;

    const unitSellAs = document.getElementById('unit-sell-as').value;
    const unitQuantity = document.getElementById('unit-quantity').value;
    const unitSellingPrice = document.getElementById('unit-selling-price').value;
    const unitBuyingPrice = document.getElementById('unit-buying-price').value;

    let productDetails = {};
    let profit = 0;

    if (wholeQuantity) {
        profit = (wholeSellingPrice - wholeBuyingPrice) * wholeQuantity;

        productDetails = {
            product_id: productId,
            name: wholeProductName,
            quantity: wholeQuantity,
            price: wholeSellingPrice,
            buying_price: wholeBuyingPrice, // Add buying price here
            sum: wholeQuantity * wholeSellingPrice,
            profit: profit,
            unit_type: 'whole',
        };
    } else if (unitQuantity) {
        const unitRelation = document.getElementById('unit-relation').value;
        const selectedOption = document.getElementById('unit-sell-as').options[document.getElementById('unit-sell-as').selectedIndex];
        const unitName = selectedOption.text;
        const unitId = selectedOption.value;

        profit = (unitSellingPrice - unitBuyingPrice) * unitQuantity;

        productDetails = {
            product_id: productId,
            unit_id: unitId,
            name: `${wholeProductName} (${unitName})`,
            quantity: unitQuantity,
            price: unitSellingPrice,
            buying_price: unitBuyingPrice, // Add buying price here
            sum: unitQuantity * unitSellingPrice,
            profit: profit,
            unit_type: 'unit',
        };
    } else {
        alert('Please provide quantity for either whole or unit.');
        return;
    }

    orderList.push(productDetails);
    updateOrderList();
    resetOrderForm();
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
                <td>${item.sum.toFixed(2)}</td>
            </tr>
        `;
        orderTableBody.innerHTML += row;
    });

    document.getElementById('total-sum').innerText = totalAmount.toFixed(2);
}

function removeItem(index) {
    orderList.splice(index, 1);
    updateOrderList();
}

// Completing the order
document.getElementById('complete-order').addEventListener('click', completeOrder);

function completeOrder() {
    if (orderList.length === 0) {
        alert('No items in the order list');
        return;
    }

    const customerName = document.getElementById('customer-name').value;
    const paymentMethod = document.getElementById('payment-method').value;
    const debtAmount = document.getElementById('debt-amount').value;

    if (!customerName) {
        alert('Please enter customer name');
        return;
    }

    // AJAX to send order data to the server
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "complete-order.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    alert('Order completed successfully');
                    // Reset the order list and the form
                    orderList = [];
                    updateOrderList();
                    resetOrderForm();
                } else {
                    alert('Error: ' + response.message);
                }
            } else {
                alert('Error completing order: ' + xhr.status);
            }
        }
    };
    

    let orderData = {
        customer_name: customerName,
        payment_method: paymentMethod,
        debt_amount: paymentMethod === 'debt' ? debtAmount : 0,
        orderList: orderList,
        total: totalAmount,
        total_profit: totalProfit
    };

    xhr.send(JSON.stringify(orderData));
}

function resetOrderForm() {
    document.getElementById('whole-product-name').value = '';
    document.getElementById('whole-quantity').value = '';
    document.getElementById('whole-buying-price').value = '';
    document.getElementById('whole-selling-price').value = '';
    document.getElementById('unit-sell-as').value = '';
    document.getElementById('unit-quantity').value = '';
    document.getElementById('unit-buying-price').value = '';
    document.getElementById('unit-selling-price').value = '';
    document.getElementById('unit-relation').value = '';
    document.getElementById('customer-name').value = '';
    document.getElementById('payment-method').value = 'cash';
    document.getElementById('debt-amount').value = '';
}
