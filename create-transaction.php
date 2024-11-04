<?php
session_start();

// Include database connection
include('db_connection.php');

// Fetch the current cash balance
$cash_query = "SELECT cash FROM money";
$result = $conn->query($cash_query);
$cash = $result->fetch_assoc()['cash'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Transaction</title>
    <link rel="stylesheet" href="css/create-transaction.css">
    <style>
        .message {
            display: none;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .success {
            background-color: green;
            color: white;
        }
        .error {
            background-color: red;
            color: white;
        }
    </style>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="content">
        <h3>Business Cash</h3>
        <input type="text" id="business-cash" value="<?= number_format($cash, 2); ?>" readonly>

        <!-- Message Display Area -->
        <div id="message" class="message"></div>
        
        <h3>Make a New Transaction</h3>
        <form id="transaction-form">
            <div>
                <label for="transaction-type">Transaction Type</label>
                <select name="transaction_type" id="transaction-type" required>
                    <option value="expenses">Expenses</option>
                    <option value="drawings">Drawings</option>
                    <option value="add_capital">Add Capital</option>
                </select>
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

                // Check if transaction was successful
                if (data.success) {
                    messageDiv.className = 'message success';
                    messageDiv.innerText = 'Transaction recorded successfully. New business cash: ' + data.new_cash;
                    
                    // Update the cash value in the input field
                    businessCashInput.value = data.new_cash;

                    // Hide the message after 5 seconds if success
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 5000);
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.innerText = 'Error: ' + data.message;
                }

                // Show the message
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
