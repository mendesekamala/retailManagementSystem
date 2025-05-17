<?php
include 'db_connection.php';

// Fetch active payment methods of the logged-in company (excluding debt)
$company_id = $_SESSION['company_id'];
$sql = "SELECT * FROM payment_methods WHERE company_id = $company_id";
$result = mysqli_query($conn, $sql);
$payment_methods = [];

if ($row = mysqli_fetch_assoc($result)) {
    foreach ($row as $method => $status) {
        if ($status === 'yes' && !in_array($method, ['method_id', 'created_by', 'company_id', 'debt'])) {
            $payment_methods[] = str_replace('_', ' ', $method);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debt Payment</title>
    <link rel="stylesheet" href="css/payment-modal.css">
</head>
<body>

<div id="paymentModalDebt" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDebtModal()">&times;</span>
        <h2>Payment Methods</h2>
        <p id="debtTotalText">Due Amount: <span id="debtTotalValue"></span></p>
        <input type="hidden" id="debtorName">

        <div class="payment-selection">
            <select id="paymentMethodDebt" class="selecty">
                <option value="">Select Payment Method</option>
                <?php foreach ($payment_methods as $method): ?>
                    <option value="<?= $method ?>"><?= $method ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="paymentAmountDebt" placeholder="Amount" min="1" class="inputy">
            <button onclick="addPaymentMethodDebt()" class="buttony">+ Add Method</button>
        </div>

        <div id="paymentListDebt"></div>

        <p id="paymentTotalDebt">Payment Methods Total: <span id="paymentTotalValueDebt">0</span></p>
        <button id="completeDebtPayment" class="buttony">Complete Payment</button>
    </div>
</div>

<script src="scripts/payment_debtors.js"></script>
</body>
</html>