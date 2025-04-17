<?php
session_start();
include('db_connection.php');

// Create detailed log entry
$logContent = "=== PAYMENT MODAL STARTED ===\n";
$logContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
$logContent .= "Session Data:\n" . print_r($_SESSION, true) . "\n";
$logContent .= "GET Parameters:\n" . print_r($_GET, true) . "\n";

// Validate session and order ID
if (!isset($_GET['order_id']) || !isset($_SESSION['company_id'])) {
    $logContent .= "ERROR: No order ID provided or not logged in\n";
    file_put_contents('payment_modal_debug.log', $logContent, FILE_APPEND);
    die("No order ID provided or not logged in.");
}

$order_id = intval($_GET['order_id']);
$company_id = $_SESSION['company_id'];
$logContent .= "Processing order ID: $order_id for company ID: $company_id\n";

// Fetch active payment methods (using prepared statement)
$payment_methods = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM payment_methods WHERE company_id = ?");
mysqli_stmt_bind_param($stmt, "i", $company_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    $logContent .= "ERROR: Payment methods query failed: " . mysqli_error($conn) . "\n";
    file_put_contents('payment_modal_debug.log', $logContent, FILE_APPEND);
    die("Payment methods query failed: " . mysqli_error($conn));
}

if ($row = mysqli_fetch_assoc($result)) {
    foreach ($row as $method => $status) {
        if ($status === 'yes' && !in_array($method, ['method_id', 'created_by', 'company_id'])) {
            $payment_methods[] = str_replace('_', ' ', $method);
        }
    }
}
mysqli_stmt_close($stmt);

$logContent .= "Available payment methods:\n" . print_r($payment_methods, true) . "\n";

// Fetch previous payment methods using transType_id
$previous_payments = [];
$previous_total = 0;

$paymentQuery = "SELECT m.payment_method, m.partial_amount 
                FROM methods_used m
                JOIN transactions t ON m.transaction_id = t.transaction_id
                WHERE t.company_id = ? 
                AND t.transType_id = ?
                ORDER BY t.date_made DESC";

$stmt = mysqli_prepare($conn, $paymentQuery);
mysqli_stmt_bind_param($stmt, "ii", $company_id, $order_id);
mysqli_stmt_execute($stmt);
$paymentResult = mysqli_stmt_get_result($stmt);

if (!$paymentResult) {
    $logContent .= "ERROR: Payment history query failed: " . mysqli_error($conn) . "\n";
    file_put_contents('payment_modal_debug.log', $logContent, FILE_APPEND);
    die("Payment history query failed: " . mysqli_error($conn));
}

while ($payment = mysqli_fetch_assoc($paymentResult)) {
    $previous_payments[] = $payment;
    $previous_total += $payment['partial_amount'];
}
mysqli_stmt_close($stmt);

$logContent .= "Previous payments found: " . count($previous_payments) . "\n";
$logContent .= "Previous total: $previous_total\n";
$logContent .= "Previous payment details:\n" . print_r($previous_payments, true) . "\n";

file_put_contents('payment_modal_debug.log', $logContent, FILE_APPEND);
?>

<div id="paymentModal" class="payment-modal">
    <div class="payment-modal-content edit-order-payment">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Payment Methods</h2>
        
        <div class="payment-container">
            <!-- Left Grid - New Payment Methods -->
            <div class="payment-grid left-grid">
                <p id="grandTotalText">New Grand Total: <span id="grandTotalValue"></span></p>
                
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
            </div>
            
            <!-- Right Grid - Previous Payment Methods -->
            <div class="payment-grid right-grid">
                <h3>Previous Payment Methods</h3>
                <table class="previous-payments">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Method</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previous_payments as $index => $payment): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $payment['payment_method'] ?></td>
                            <td><?= number_format($payment['partial_amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"><strong>Initial Total:</strong></td>
                            <td><strong><?= number_format($previous_total, 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <button id="completeTransaction" class="buttony">Update Order</button>
    </div>
</div>

<script>
// Make sure these functions are available globally
window.openModal = function(total, orderId) {
    console.group("Payment Modal Opened");
    console.log("Order ID:", orderId);
    console.log("Grand Total:", total);
    
    grandTotal = parseFloat(total);
    document.getElementById("grandTotalValue").innerText = grandTotal.toFixed(2);
    document.getElementById("paymentTotalValue").innerText = totalPayment.toFixed(2);
    document.getElementById("paymentModal").style.display = "flex";
    
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
</script>