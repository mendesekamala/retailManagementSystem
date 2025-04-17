let grandTotal = 0;
let paymentMethods = [];
let totalPayment = 0;

function openModal(total) {
    grandTotal = total;
    document.getElementById("grandTotalValue").innerText = total.toFixed(2);
    document.getElementById("paymentTotalValue").innerText = totalPayment.toFixed(2);
    document.getElementById("paymentModal").style.display = "block";
}

function closeModal() {
    document.getElementById("paymentModal").style.display = "none";
    // Reset payment methods when closing
    paymentMethods = [];
    totalPayment = 0;
    updatePaymentList();
}

function addPaymentMethod() {
    let method = document.getElementById("paymentMethod").value;
    let amount = parseFloat(document.getElementById("paymentAmount").value);

    if (!method || amount <= 0 || paymentMethods.some(m => m.method === method)) {
        alert("Invalid input or method already added!");
        return;
    }

    paymentMethods.push({ method, amount });
    totalPayment += amount;

    updatePaymentList();
}

function removePaymentMethod(index) {
    totalPayment -= paymentMethods[index].amount;
    paymentMethods.splice(index, 1);
    updatePaymentList();
}

function updatePaymentList() {
    let list = document.getElementById("paymentList");
    list.innerHTML = "";
    paymentMethods.forEach((p, index) => {
        let div = document.createElement("div");
        div.innerHTML = `${p.method}: $${p.amount} <button onclick="removePaymentMethod(${index})">Remove</button>`;
        list.appendChild(div);
    });

    document.getElementById("paymentTotalValue").innerText = totalPayment;
}

document.getElementById("completeTransaction").addEventListener("click", function() {
    console.log("grandTotal:", grandTotal, "totalPayment:", totalPayment); // Log values here
    if (Math.abs(totalPayment - grandTotal) > 0.01) {
        alert("Total payments must match the grand total!");
        return;
    }

    let transactionData = JSON.parse(localStorage.getItem("transactionData"));
    if (!transactionData) {
        alert("Transaction data missing! Restart process.");
        return;
    }

    let requestData = {
        ...transactionData,
        paymentMethods
    };

    console.log("Data being sent:", requestData);

    fetch("record-transaction.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        closeModal();
        location.reload();
    })
    .catch(error => console.error("Error:", error));
});