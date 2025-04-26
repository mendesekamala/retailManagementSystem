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
$grandTotal = $orderDetails['total'] ?? 0;

$queryOrderItems = "SELECT * FROM order_items WHERE order_id = $order_id";
$resultOrderItems = mysqli_query($conn, $queryOrderItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/view-order.css">
    <title>View Order</title>
</head>
<body>

<div class="receipt-container">
    <!-- Company Name -->
    <h1 class="company-name">Chiades Possibility Co</h1>

    <!-- Contact Information -->
    <table class="contact-table">
        <tr>
            <td>Mobile Number 1:</td>
            <td>0715 200 400</td>
        </tr>
        <tr>
            <td>Mobile Number 2:</td>
            <td>0755 209 463</td>
        </tr>
    </table>

    <!-- Order Items -->
    <div class="order-items">
        <?php if (mysqli_num_rows($resultOrderItems) > 0): ?>
            <table class="items-table">
                <thead>
                    <tr>
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
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['selling_price'], 2); ?></td>
                        <td><?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Grand Total -->
            <div class="total-price">Grand Total: <strong><?php echo number_format($grandTotal, 2); ?></strong></div>
        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>
    </div>

    <!-- Thank You Message -->
    <div class="thank-you">Karibu Tena</div>

    <!-- Print Icon -->
    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
</div>

</body>
</html>
