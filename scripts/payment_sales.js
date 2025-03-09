let grandTotal = 0;
let paymentMethods = [];
let totalPayment = 0;

function openModal(total) {
    grandTotal = parseFloat(total); // Convert to number
    document.getElementById("grandTotalValue").innerText = grandTotal.toFixed(2);
    document.getElementById("paymentTotalValue").innerText = totalPayment.toFixed(2);
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

    let salesData = JSON.parse(localStorage.getItem("salesData"));
    if (!salesData) {
        alert("sales data missing! Restart process.");
        return;
    }

    let requestData = {
        ...salesData,
        paymentMethods
    };

    // Debug: Check the payment methods before sending
    console.log("Data being sent:", requestData);

    fetch("complete-order.php", {
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
