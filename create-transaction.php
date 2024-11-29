<?php
session_start();

// Include database connection
include('db_connection.php');

// Get the company_id from session
$company_id = $_SESSION['company_id'];

// Fetch active payment methods for the logged-in user's company
$payment_methods_query = "SELECT * FROM payment_methods WHERE company_id = ?";
$stmt = $conn->prepare($payment_methods_query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$payment_methods_result = $stmt->get_result();

// Fetch the current cash balance
$cash_query = "SELECT cash FROM money WHERE company_id = ?";
$cash_stmt = $conn->prepare($cash_query);
$cash_stmt->bind_param("i", $company_id);
$cash_stmt->execute();
$cash = $cash_stmt->get_result()->fetch_assoc()['cash'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Transaction</title>
    <link rel="stylesheet" href="css/create-transaction.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet"> <!-- Add Boxicons stylesheet -->
</head>

<?php include('sidebar.php'); ?>

<body>
    <a href="active_payments.php">
        <span class="settings-icon"><i class='bx bx-cog'></i></span> <!-- Boxicon for settings -->
    </a>

    <div class="content">
        <h3>Business Cash</h3>
        <input type="text" id="business-cash" value="<?= number_format($cash, 2); ?>" readonly>

        <!-- Message Display Area -->
        <div id="message" class="message"></div>

        <h3>Make a New Transaction</h3>
        <form id="transaction-form">
            <div class="transaction-inputs">
                <div class="input-group">
                    <label for="transaction-type">Transaction Type</label>
                    <select name="transaction_type" id="transaction-type" required>
                        <option value="expenses">Expenses</option>
                        <option value="drawings">Drawings</option>
                        <option value="add_capital">Add Capital</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="payment-method">Payment Method</label>
                    <select name="payment_method" id="payment-method" required>
                        <?php
                        // Loop through payment methods and display only active ones
                        while ($row = $payment_methods_result->fetch_assoc()) {
                            foreach (['cash', 'NMB', 'CRDB', 'NBC', 'mpesa', 'airtel_money', 'tigo_pesa', 'halo_pesa', 'azam_pesa', 'debt'] as $method) {
                                if ($row[$method] === 'yes') {
                                    echo "<option value=\"$method\">" . ucfirst(str_replace('_', ' ', $method)) . "</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" required>
            </div>

            <div>
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="3" required></textarea>
            </div>

            <input type="submit" value="Submit">
        </form>
    </div>

    <script>
        document.getElementById('transaction-form').addEventListener('submit', function(event) {
            event.preventDefault();

            // Get form data
            const formData = new FormData(this);

            // Send AJAX request to process-transactions.php
            fetch('process-transactions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                const businessCashInput = document.getElementById('business-cash');

                if (data.success) {
                    messageDiv.className = 'message success';
                    messageDiv.innerText = 'Transaction recorded successfully. New business cash: ' + data.new_cash;

                    businessCashInput.value = data.new_cash;

                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 5000);
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.innerText = 'Error: ' + data.message;
                }

                messageDiv.style.display = 'block';
            })
            .catch(error => {
                const messageDiv = document.getElementById('message');
                messageDiv.className = 'message error';
                messageDiv.innerText = 'Error processing request.';
                messageDiv.style.display = 'block';
            });
        });
    </script>
</body>
</html>
