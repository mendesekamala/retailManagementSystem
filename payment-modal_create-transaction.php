<?php
include 'db_connection.php'; // Include database connection

// Fetch active payment methods of the logged-in company
$company_id = $_SESSION['company_id']; // Replace with session company_id
$sql = "SELECT * FROM payment_methods WHERE company_id = $company_id";
$result = mysqli_query($conn, $sql);
$payment_methods = [];

if ($row = mysqli_fetch_assoc($result)) {
    foreach ($row as $method => $status) {
        if ($status === 'yes' && !in_array($method, ['method_id', 'created_by', 'company_id'])) {
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
    <title>Payment Modal</title>
    <link rel="stylesheet" href="css/payment-modal.css"> <!-- Using same CSS as other payment modal -->
</head>
<body>

<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Payment Methods</h2>
        <p id="grandTotalText">Grand Total: <span id="grandTotalValue"></span></p>

        <div class="payment-selection">
            <select id="paymentMethod" class="selecty">
                <option value="">Select Payment Method</option>
                <?php foreach ($payment_methods as $method): ?>
                    <option value="<?= $method ?>"><?= $method ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="paymentAmount" placeholder="Amount" min="1" class="inputy">
            <button onclick="addPaymentMethod()" class="buttony">+ Add Method</button>
        </div>

        <div id="paymentList"></div>

        <p id="paymentTotal">Payment Methods Total: <span id="paymentTotalValue">0</span></p>
        <button id="completeTransaction" class="buttony">Complete Transaction</button>
    </div>
</div>

<script src="scripts/payment_create-transaction.js"></script>

</body>
</html>