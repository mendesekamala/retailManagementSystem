let creditTotal = 0;
let paymentMethodsCredit = [];
let totalPaymentCredit = 0;
let creditorName = '';
let currentDebtId = '';

function openCreditModal(name, dueAmount, debtId) {
    creditorName = name;
    creditTotal = dueAmount;
    currentDebtId = debtId;
    
    document.getElementById("creditTotalValue").innerText = creditTotal;
    document.getElementById("paymentTotalValueCredit").innerText = totalPaymentCredit;
    document.getElementById("creditorName").value = name;
    document.getElementById("paymentModalCredit").style.display = "block";
}

function closeCreditModal() {
    document.getElementById("paymentModalCredit").style.display = "none";
    resetCreditPaymentForm();
}

function resetCreditPaymentForm() {
    paymentMethodsCredit = [];
    totalPaymentCredit = 0;
    document.getElementById("paymentListCredit").innerHTML = "";
    document.getElementById("paymentTotalValueCredit").innerText = "0";
    document.getElementById("paymentMethodCredit").value = "";
    document.getElementById("paymentAmountCredit").value = "";
}

function addPaymentMethodCredit() {
    let method = document.getElementById("paymentMethodCredit").value;
    let amount = parseFloat(document.getElementById("paymentAmountCredit").value);

    if (!method || amount <= 0 || paymentMethodsCredit.some(m => m.method === method)) {
        alert("Invalid input or method already added!");
        return;
    }

    if (totalPaymentCredit + amount > creditTotal) {
        alert("Total payments cannot exceed the due amount!");
        return;
    }

    paymentMethodsCredit.push({ method, amount });
    totalPaymentCredit += amount;

    updatePaymentListCredit();
}

function removePaymentMethodCredit(index) {
    totalPaymentCredit -= paymentMethodsCredit[index].amount;
    paymentMethodsCredit.splice(index, 1);
    updatePaymentListCredit();
}

function updatePaymentListCredit() {
    let list = document.getElementById("paymentListCredit");
    list.innerHTML = "";
    paymentMethodsCredit.forEach((p, index) => {
        let div = document.createElement("div");
        div.innerHTML = `${p.method}: Tsh ${p.amount.toFixed(2)} <button onclick="removePaymentMethodCredit(${index})">Remove</button>`;
        list.appendChild(div);
    });

    document.getElementById("paymentTotalValueCredit").innerText = totalPaymentCredit.toFixed(2);
}

document.getElementById("completeCreditPayment").addEventListener("click", function() {
    if (totalPaymentCredit !== creditTotal) {
        if (!confirm(`You're paying Tsh ${totalPaymentCredit.toFixed(2)} out of Tsh ${creditTotal.toFixed(2)}. Continue with partial payment?`)) {
            return;
        }
    }

    let paymentData = {
        creditorName: creditorName,
        paymentAmount: totalPaymentCredit,
        dueAmount: creditTotal,
        paymentMethods: paymentMethodsCredit,
        debt_id: currentDebtId
    };

    fetch("paying_credit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            closeCreditModal();
            window.location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while processing the payment.");
    });
});

// Add event listeners to mark-paid buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-paid').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const name = this.getAttribute('data-name');
            const dueAmount = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('Tsh ', '').replace(',', ''));
            const debtId = row.getAttribute('data-debt-id');
            
            openCreditModal(name, dueAmount, debtId);
        });
    });
});