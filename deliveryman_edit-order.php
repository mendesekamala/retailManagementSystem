<?php
session_start();
include('db_connection.php');

// Get the order ID from the URL
$order_id = $_GET['order_id'];

// Update order items' status if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivered'])) {
    // Loop through each checked item and update the status
    if (isset($_POST['order_items'])) {
        foreach ($_POST['order_items'] as $item_id) {
            $updateQuery = "UPDATE order_items SET status = 'delivered' WHERE item_id = $item_id";
            mysqli_query($conn, $updateQuery);
        }
    }
    header("Location: orders.php?order_id=" . urlencode($_GET['order_id']));

}

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
    <title>Edit Order</title>
</head>
<body>

<div class="receipt-container">
    <h1 class="company-name">Chiades Possibility Co</h1>

    <form method="POST" action="">
        <div class="order-items">
            <?php if (mysqli_num_rows($resultOrderItems) > 0): ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Select</th>
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
                            <td><input type="checkbox" name="order_items[]" value="<?php echo $item['item_id']; ?>"></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['selling_price'], 2); ?></td>
                            <td><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="total-price">Grand Total: <strong><?php echo number_format($grandTotal, 2); ?></strong></div>
            <?php else: ?>
                <p>No items found for this order.</p>
            <?php endif; ?>
        </div>

        <button type="submit" name="delivered" class="delivered-btn">Delivered</button>
    </form>
</div>

</body>
</html>
