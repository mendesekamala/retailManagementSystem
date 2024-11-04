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
                messageDiv.classList.add('hidden');
            }, 5000);
        } else {
            messageDiv.className = 'message error';
            messageDiv.innerText = 'Error: ' + data.message;
        }

        // Show the message
        messageDiv.classList.remove('hidden');
    })
    .catch(error => {
        const messageDiv = document.getElementById('message');
        messageDiv.className = 'message error';
        messageDiv.innerText = 'Error processing request.';
        messageDiv.classList.remove('hidden');
    });
});
