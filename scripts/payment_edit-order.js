// Global variables with initialization logging
console.group("Payment Edit Script Initialization");
window.grandTotal = 0;
window.paymentMethods = [];
window.totalPayment = 0;
window.orderId = 0;
window.orderItems = [];
console.log("Initialized global variables");
console.groupEnd();

function addPaymentMethod() {
    console.group("Adding Payment Method");
    let method = document.getElementById("paymentMethod").value;
    let amount = parseFloat(document.getElementById("paymentAmount").value);
    
    console.log("Attempting to add payment method:", method, "Amount:", amount);

    if (!method) {
        console.error("No payment method selected");
        alert("Please select a payment method!");
        console.groupEnd();
        return;
    }

    if (amount <= 0 || isNaN(amount)) {
        console.error("Invalid amount:", amount);
        alert("Please enter a valid amount!");
        console.groupEnd();
        return;
    }

    if (paymentMethods.some(m => m.method === method)) {
        console.error("Method already exists:", method);
        alert("This payment method has already been added!");
        console.groupEnd();
        return;
    }

    paymentMethods.push({ method, amount });
    totalPayment += amount;
    console.log("Added payment method:", { method, amount });
    console.log("Current payment methods:", paymentMethods);
    console.log("New total payment:", totalPayment);

    updatePaymentList();
    console.groupEnd();
}

function removePaymentMethod(index) {
    console.group("Removing Payment Method");
    console.log("Removing payment method at index:", index);
    console.log("Current payment methods before removal:", paymentMethods);
    
    totalPayment -= paymentMethods[index].amount;
    paymentMethods.splice(index, 1);
    
    console.log("Payment methods after removal:", paymentMethods);
    console.log("New total payment:", totalPayment);
    
    updatePaymentList();
    console.groupEnd();
}

function updatePaymentList() {
    console.log("Updating payment list display");
    let list = document.getElementById("paymentList");
    list.innerHTML = "";
    
    paymentMethods.forEach((p, index) => {
        let div = document.createElement("div");
        div.className = "payment-row";
        div.innerHTML = `
            <span>${p.method}:</span>
            <span>${p.amount.toFixed(2)}</span>
            <span onclick="removePaymentMethod(${index})" class="remove-payment">❌</span>
        `;
        list.appendChild(div);
    });

    document.getElementById("paymentTotalValue").innerText = totalPayment.toFixed(2);
    console.log("Payment list updated");
}

function handleCompleteTransaction(e) {
    if (e.target && e.target.id === 'completeTransaction') {
        console.group("Complete Transaction Processing");
        console.log("=== TRANSACTION INITIATED ===");
        console.log("Order ID:", orderId);
        console.log("Grand Total:", grandTotal);
        console.log("Payment Methods:", paymentMethods);
        console.log("Order Items:", orderItems);

        // Validate payment total
        const diff = Math.abs(totalPayment - grandTotal);
        if (diff > 0.01) {
            console.error(`Payment validation failed - difference of ${diff}`);
            alert("Total payments must match the grand total!");
            console.groupEnd();
            return;
        }

        // Prepare request data
        let requestData = {
            original_order_id: orderId,
            orderList: orderItems,
            total_amount: grandTotal,
            paymentMethods: paymentMethods
        };

        console.log("Prepared request data:", JSON.stringify(requestData, null, 2));

        // Send to server
        console.log("Sending to finish_editing-order.php");
        fetch("finish_editing-order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log("Received response, status:", response.status);
            if (!response.ok) {
                console.error("Response not OK:", response.status, response.statusText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Parsed response data:", data);
            if (data.success) {
                console.log("Transaction successful, new order ID:", data.new_order_id);
                closeModal();
                window.location.href = `view-order.php?id=${data.new_order_id}`;
            } else {
                console.error("Server reported error:", data.message);
                let errorMsg = data.message || "An error occurred";
                if (data.error_details) {
                    console.error("Error details:", data.error_details);
                    errorMsg += `\nDetails: ${JSON.stringify(data.error_details)}`;
                }
                alert(errorMsg);
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
            if (error instanceof TypeError) {
                console.error("TypeError details:", error.message);
            }
            alert("An error occurred. Please check console for details.");
        })
        .finally(() => {
            console.groupEnd();
        });
    }
}

// Setup function to properly manage the event listener
function setupCompleteTransactionListener() {
    console.log("Setting up complete transaction listener");
    document.removeEventListener('click', handleCompleteTransaction);
    document.addEventListener('click', handleCompleteTransaction);
}

// Enhanced openModal function
window.openModal = function(total, id) {
    console.group("Opening Payment Modal");
    grandTotal = parseFloat(total);
    orderId = id;
    console.log("Order ID:", orderId);
    console.log("Grand Total:", grandTotal);

    document.getElementById("grandTotalValue").innerText = grandTotal.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById("paymentTotalValue").innerText = totalPayment.toFixed(2);
    document.getElementById("paymentModal").style.display = "flex";
    
    setupCompleteTransactionListener();
    console.groupEnd();
};

window.closeModal = function() {
    console.log("Closing payment modal");
    const modal = document.getElementById("paymentModal");
    if (modal) {
        modal.style.display = "none";
        modal.parentNode.removeChild(modal);
    }
};