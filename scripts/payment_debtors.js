let debtTotal = 0;
let paymentMethodsDebt = [];
let totalPaymentDebt = 0;
let debtorName = '';
let currentDebtId = ''; // Added to track the current debt ID

function openDebtModal(name, dueAmount, debtId) {
    debtorName = name;
    debtTotal = dueAmount;
    currentDebtId = debtId; // Store the debt_id
    
    document.getElementById("debtTotalValue").innerText = debtTotal;
    document.getElementById("paymentTotalValueDebt").innerText = totalPaymentDebt;
    document.getElementById("debtorName").value = name;
    document.getElementById("paymentModalDebt").style.display = "block";
}

function closeDebtModal() {
    document.getElementById("paymentModalDebt").style.display = "none";
    resetDebtPaymentForm();
}

function resetDebtPaymentForm() {
    paymentMethodsDebt = [];
    totalPaymentDebt = 0;
    document.getElementById("paymentListDebt").innerHTML = "";
    document.getElementById("paymentTotalValueDebt").innerText = "0";
    document.getElementById("paymentMethodDebt").value = "";
    document.getElementById("paymentAmountDebt").value = "";
}

function addPaymentMethodDebt() {
    let method = document.getElementById("paymentMethodDebt").value;
    let amount = parseFloat(document.getElementById("paymentAmountDebt").value);

    if (!method || amount <= 0 || paymentMethodsDebt.some(m => m.method === method)) {
        alert("Invalid input or method already added!");
        return;
    }

    if (totalPaymentDebt + amount > debtTotal) {
        alert("Total payments cannot exceed the due amount!");
        return;
    }

    paymentMethodsDebt.push({ method, amount });
    totalPaymentDebt += amount;

    updatePaymentListDebt();
}

function removePaymentMethodDebt(index) {
    totalPaymentDebt -= paymentMethodsDebt[index].amount;
    paymentMethodsDebt.splice(index, 1);
    updatePaymentListDebt();
}

function updatePaymentListDebt() {
    let list = document.getElementById("paymentListDebt");
    list.innerHTML = "";
    paymentMethodsDebt.forEach((p, index) => {
        let div = document.createElement("div");
        div.innerHTML = `${p.method}: Tsh ${p.amount.toFixed(2)} <button onclick="removePaymentMethodDebt(${index})">Remove</button>`;
        list.appendChild(div);
    });

    document.getElementById("paymentTotalValueDebt").innerText = totalPaymentDebt.toFixed(2);
}

document.getElementById("completeDebtPayment").addEventListener("click", function() {
    if (totalPaymentDebt !== debtTotal) {
        if (!confirm(`You're paying Tsh ${totalPaymentDebt.toFixed(2)} out of Tsh ${debtTotal.toFixed(2)}. Continue with partial payment?`)) {
            return;
        }
    }

    let paymentData = {
        debtorName: debtorName,
        paymentAmount: totalPaymentDebt,
        dueAmount: debtTotal,
        paymentMethods: paymentMethodsDebt,
        debt_id: currentDebtId // Include the debt_id in the payment data
    };

    fetch("paying_debt.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            closeDebtModal();
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
            const debtId = row.getAttribute('data-debt-id'); // Make sure your table rows have this attribute
            
            openDebtModal(name, dueAmount, debtId);
        });
    });
});