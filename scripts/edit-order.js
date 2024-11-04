// let grandTotal = <?php echo $grandTotal; ?>;

function updateQuantity(itemId, delta) {
    const quantityElement = document.getElementById(`quantity-${itemId}`);
    let quantity = parseInt(quantityElement.textContent) + delta;

    if (quantity < 1) return;

    quantityElement.textContent = quantity;
    document.getElementById(`input-quantity-${itemId}`).value = quantity;

    const sellingPrice = parseFloat(document.getElementById(`selling-price-${itemId}`).value);
    const newSubtotal = quantity * sellingPrice;
    document.getElementById(`subtotal-${itemId}`).textContent = ` => ${newSubtotal.toFixed(2)}`;

    grandTotal += delta * sellingPrice;
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
}

function removeItem(itemId) {
    // Hide the item visually
    document.getElementById(`item-${itemId}`).style.display = 'none';
    // Mark item as removed in the form data
    document.getElementById(`input-removed-${itemId}`).value = 1;

    // Calculate the subtotal based on quantity and price, instead of parsing from text content
    const quantity = parseInt(document.getElementById(`input-quantity-${itemId}`).value);
    const sellingPrice = parseFloat(document.getElementById(`selling-price-${itemId}`).value);
    const subtotal = quantity * sellingPrice;

    // Subtract the calculated subtotal from grandTotal
    grandTotal -= subtotal;
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
}

function updateStatusText() {
    const status = document.getElementById('orderStatus').value;
    const statusText = document.getElementById('statusText');
    const statusIcon = document.querySelector('.status-icon i');

    statusText.className = 'status-text text-' + status;
    statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);

    switch (status) {
        case 'created':
            statusIcon.className = 'bx bx-plus-circle icon-created';
            break;
        case 'sent':
            statusIcon.className = 'bx bx-right-arrow-circle icon-sent';
            break;
        case 'delivered':
            statusIcon.className = 'bx bx-check-circle icon-delivered';
            break;
        case 'cancelled':
            statusIcon.className = 'bx bx-x-circle icon-cancelled';
            break;
    }
}

function formatPlain() {
    const debtInput = document.getElementById('debtAmount');
    // Remove commas to make it editable as a plain number
    debtInput.value = debtInput.value.replace(/,/g, '');
}

function formatCommas() {
    const debtInput = document.getElementById('debtAmount');
    // Add commas back for display
    const value = parseFloat(debtInput.value);
    if (!isNaN(value)) {
        debtInput.value = value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

// Ensure commas are removed before form submission
document.getElementById('editOrderForm').addEventListener('submit', function() {
    formatPlain();
});
