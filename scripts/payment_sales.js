// Make sure these are only declared once
if (!window.paymentGlobals) {
    window.paymentGlobals = {
        grandTotal: 0,
        paymentMethods: [],
        totalPayment: 0,
        transactionInProgress: false // Add this flag
    };
}

window.openModal = function(total) {
    paymentGlobals.grandTotal = parseFloat(total);
    document.getElementById("grandTotalValue").innerText = paymentGlobals.grandTotal.toFixed(2);
    document.getElementById("paymentTotalValue").innerText = paymentGlobals.totalPayment.toFixed(2);
    document.getElementById("paymentModal").style.display = "block";
    
    // Reset transaction flag when modal opens
    paymentGlobals.transactionInProgress = false;
};

window.closeModal = function() {
    document.getElementById("paymentModal").style.display = "none";
};

window.addPaymentMethod = function() {
    let method = document.getElementById("paymentMethod").value;
    let amount = parseFloat(document.getElementById("paymentAmount").value);

    if (!method || amount <= 0 || paymentGlobals.paymentMethods.some(m => m.method === method)) {
        alert("Invalid input or method already added!");
        return;
    }

    paymentGlobals.paymentMethods.push({ method, amount });
    paymentGlobals.totalPayment += amount;

    updatePaymentList();
};

window.removePaymentMethod = function(index) {
    paymentGlobals.totalPayment -= paymentGlobals.paymentMethods[index].amount;
    paymentGlobals.paymentMethods.splice(index, 1);
    updatePaymentList();
};

function updatePaymentList() {
    let list = document.getElementById("paymentList");
    list.innerHTML = "";
    paymentGlobals.paymentMethods.forEach((p, index) => {
        let div = document.createElement("div");
        div.innerHTML = `${p.method}: $${p.amount} <button onclick="removePaymentMethod(${index})">Remove</button>`;
        list.appendChild(div);
    });

    document.getElementById("paymentTotalValue").innerText = paymentGlobals.totalPayment;
}

// Only attach the event listener once
if (!document.getElementById('completeTransaction').hasListener) {
    document.getElementById('completeTransaction').addEventListener("click", function completeTransactionHandler() {
        if (paymentGlobals.transactionInProgress) {
            return; // Prevent multiple submissions
        }
        
        if (paymentGlobals.totalPayment !== paymentGlobals.grandTotal) {
            alert("Total payments must match the grand total!");
            return;
        }

        let salesData = JSON.parse(localStorage.getItem("salesData"));
        if (!salesData) {
            alert("Sales data missing! Restart process.");
            return;
        }

        paymentGlobals.transactionInProgress = true; // Set flag
        
        let requestData = {
            ...salesData,
            paymentMethods: paymentGlobals.paymentMethods
        };

        fetch("complete-order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Clear the order list after successful submission
                localStorage.removeItem('salesData');
                paymentGlobals.paymentMethods = [];
                paymentGlobals.totalPayment = 0;
                
                alert(data.message);
                closeModal();
                window.location.href = window.location.href; // Proper reload
            } else {
                alert(data.message || "Transaction failed");
                paymentGlobals.transactionInProgress = false;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred during transaction");
            paymentGlobals.transactionInProgress = false;
        });
    });
    
    // Mark the button as having a listener
    document.getElementById('completeTransaction').hasListener = true;
}