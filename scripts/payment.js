let grandTotal = 0;
let paymentMethods = [];
let totalPayment = 0;

function openModal(total) {
    grandTotal = total;
    document.getElementById("grandTotalValue").innerText = total;
    document.getElementById("paymentTotalValue").innerText = totalPayment;
    document.getElementById("paymentModal").style.display = "block";
}

function closeModal() {
    document.getElementById("paymentModal").style.display = "none";
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
    if (totalPayment !== grandTotal) {
        alert("Total payments must match the grand total!");
        return;
    }

    let purchaseData = JSON.parse(localStorage.getItem("purchaseData"));
    if (!purchaseData) {
        alert("Purchase data missing! Restart process.");
        return;
    }

    let requestData = {
        ...purchaseData,
        paymentMethods
    };

    // Debug: Check the payment methods before sending
    console.log("Data being sent:", requestData);

    fetch("purchase-transaction.php", {
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
