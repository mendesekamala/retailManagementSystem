<?php
session_start();
include('db_connection.php');

// Get the order ID from the URL
$order_id = $_GET['order_id'];

// Fetch order details and items for the specified order_id
$queryOrderDetails = "SELECT * FROM orders WHERE order_id = $order_id";
$resultOrderDetails = mysqli_query($conn, $queryOrderDetails);

if (!$resultOrderDetails) {
    die("Error fetching order details: " . mysqli_error($conn));
}

$orderDetails = mysqli_fetch_assoc($resultOrderDetails);
$orderStatus = $orderDetails['status'] ?? 'created';
$grandTotal = $orderDetails['total'] ?? 0;

$queryOrderItems = "SELECT * FROM order_items WHERE order_id = $order_id";
$resultOrderItems = mysqli_query($conn, $queryOrderItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/edit-order.css">
    <title>Edit Order</title>
</head>
<body>

<div class="receipt-container">
    <!-- Order Status -->
    <div class="order-status">
        <?php
        $statusIcon = '';
        $statusColor = '';
        switch ($orderStatus) {
            case 'created':
                $statusIcon = '&#x2795;'; // Plus icon
                $statusColor = 'blue';
                break;
            case 'sent':
                $statusIcon = '&#x27A1;'; // Right arrow
                $statusColor = 'orange';
                break;
            case 'delivered':
                $statusIcon = '&#x1F69A;'; // Cargo car icon
                $statusColor = 'green';
                break;
            case 'cancelled':
                $statusIcon = '&#x274C;'; // X icon
                $statusColor = 'red';
                break;
        }
        ?>
        <span class="status-icon" style="color: <?php echo $statusColor; ?>;">
            <?php echo $statusIcon; ?>
        </span>
        <span class="status-text" style="color: <?php echo $statusColor; ?>;">
            <?php echo ucfirst($orderStatus); ?>
        </span>
    </div>

    <!-- Order Items -->
    <div class="order-items">
        <?php if (mysqli_num_rows($resultOrderItems) > 0): ?>
            <table class="items-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sn = 1;
                    while ($item = mysqli_fetch_assoc($resultOrderItems)):
                        $subtotal = $item['quantity'] * $item['selling_price'];
                    ?>
                    <tr data-item-id="<?php echo $item['item_id']; ?>">
                        <td>
                            <button class="remove-btn">&#x274C;</button>
                        </td>
                        <td><?php echo $sn++; ?></td>
                        <td > <?php echo $item['name']; ?></td>
                        <td style="width:100px;">
                            <button class="quantity-btn decrease">&larr;</button>
                            <span class="quantity"><?php echo $item['quantity']; ?></span>
                            <button class="quantity-btn increase">&rarr;</button>
                        </td>
                        <td><?php echo number_format($item['selling_price'], 2, '.', ''); ?></td>
                        <td class="subtotal"><?php echo number_format($subtotal, 2, '.', ''); ?></td>
                        
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Grand Total -->
            <div class="total-price">Grand Total: <strong id="grand-total"><?php echo number_format($grandTotal, 2, '.', ''); ?></strong></div>
        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>
    </div>

    <!-- Finish Editing Button -->
    <button class="finish-btn">Finish Editing</button>
    
</div>



<script>
    const updateTotals = () => {
    let grandTotal = 0;

    // Iterate through each row to calculate the new grand total
    document.querySelectorAll('.items-table tbody tr').forEach(row => {
        const subtotal = parseFloat(row.querySelector('.subtotal').textContent) || 0;
        grandTotal += subtotal;
    });

    // Update the grand total display
    document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    };

    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');

            // Remove the row
            row.remove();

            // Update the grand total after removal
            updateTotals();
        });
    });

    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const quantitySpan = row.querySelector('.quantity');
            let quantity = parseInt(quantitySpan.textContent);
            const price = parseFloat(row.querySelector('td:nth-child(4)').textContent);

            // Increment or decrement the quantity
            if (this.classList.contains('increase')) {
                quantity++;
            } else if (this.classList.contains('decrease') && quantity > 1) {
                quantity--;
            }

            // Update the quantity display
            quantitySpan.textContent = quantity;

            // Calculate the new subtotal and update it
            const newSubtotal = quantity * price;
            row.querySelector('.subtotal').textContent = newSubtotal.toFixed(2);

            // Update the grand total after the change
            updateTotals();
        });
    });


</script>

</body>
</html>
