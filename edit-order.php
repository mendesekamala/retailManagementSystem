<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Fetch user roles from the database
    $queryUserRole = "SELECT delivery_man, store_keeper FROM roles WHERE user_id = $user_id";
    $resultUserRole = mysqli_query($conn, $queryUserRole);

    if ($resultUserRole) {
        $role = mysqli_fetch_assoc($resultUserRole);

        // Redirect based on user role
        if ($role['delivery_man'] === 'yes') {
            header("Location: deliveryman_edit-order.php?order_id=" . urlencode($_GET['order_id']));
            exit;
        } elseif ($role['store_keeper'] === 'yes') {
            header("Location: storekeeper_edit-order.php?order_id=" . urlencode($_GET['order_id']));
            exit;
        }
    } else {
        echo "Error: Query failed - " . mysqli_error($conn);
    }
}

// Get the order ID from the URL
$order_id = $_GET['order_id'];

// Fetch order details and items for the specified order_id
$queryOrderDetails = "SELECT status, debt_amount FROM orders WHERE order_id = $order_id";
$resultOrderDetails = mysqli_query($conn, $queryOrderDetails);

if (!$resultOrderDetails) {
    die("Error fetching order details: " . mysqli_error($conn));
}

$orderDetails = mysqli_fetch_assoc($resultOrderDetails);

$status = $orderDetails['status'] ?? '';
$debtAmount = $orderDetails['debt_amount'] ?? 0;

// Alert and disable editing if the order is cancelled
if ($status === 'cancelled') {
    echo "<script>
            alert('You cannot edit a cancelled order');
            window.onload = function() {
                document.getElementById('editOrderForm').style.pointerEvents = 'none';
                document.getElementById('editOrderForm').style.opacity = '0.6';
            }
          </script>";
}
// Alert and disable editing if the order is delivered
if ($status === 'delivered') {
    echo "<script>
            alert('You cannot edit a delivered order');
            window.onload = function() {
                document.getElementById('editOrderForm').style.pointerEvents = 'none';
                document.getElementById('editOrderForm').style.opacity = '0.6';
            }
          </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/edit-order.css">
    <title>Edit Order</title>
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="content">
        <form id="editOrderForm" action="finish-editing_order.php" method="POST">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            
            <div class="main-container">

                <div class="left-section">
                        <div class="status-icon">
                            <?php
                            $icons = [
                                'cancelled' => 'bx bx-x-circle icon-cancelled',
                                'created' => 'bx bx-plus-circle icon-created',
                                'sent' => 'bx bx-right-arrow-circle icon-sent',
                                'delivered' => 'bx bx-check-circle icon-delivered'
                            ];
                            echo "<i class='" . ($icons[$status] ?? '') . "'></i>";
                            ?>
                        </div>

                        <div id="statusText" class="status-text text-<?php echo $status; ?>">
                            <?php echo ucfirst($status); ?>
                        </div>

                        

                        <?php if ($debtAmount > 0): ?>
                            <label for="debtAmount" class="debt-label">Debt Amount</label>
                            <input type="text" name="debt_amount" id="debtAmount" value="<?php echo number_format($debtAmount, 2); ?>">
                        <?php endif; ?>
                </div> 

                <div class="receipt-container">
                    <?php 
                    $queryOrderItems = "SELECT * FROM order_items WHERE order_id = $order_id";
                    $resultOrderItems = mysqli_query($conn, $queryOrderItems);

                    if (mysqli_num_rows($resultOrderItems) > 0): 
                    ?>
                        <?php
                        $sn = 1;
                        $grandTotal = 0;

                        while ($row = mysqli_fetch_assoc($resultOrderItems)):
                            $subtotal = $row['quantity'] * $row['selling_price'];
                            $grandTotal += $subtotal;
                        ?>
                            <div class="order-item" id="item-<?php echo $row['item_id']; ?>">
                                <i class="bx bx-x-circle" onclick="removeItem(<?php echo $row['item_id']; ?>)"></i>
                                <span class="sn"><?php echo $sn++; ?>.</span>
                                <span class="name"><?php echo $row['name']; ?></span>
                                <span class="quantity">
                                    <i class="bx bx-left-arrow-alt" onclick="updateQuantity(<?php echo $row['item_id']; ?>, -1)"></i>
                                    <span id="quantity-<?php echo $row['item_id']; ?>"><?php echo $row['quantity']; ?></span>
                                    <i class="bx bx-right-arrow-alt" onclick="updateQuantity(<?php echo $row['item_id']; ?>, 1)"></i>
                                </span>
                                <span class="price">@<?php echo number_format($row['selling_price'], 2); ?></span>
                                <span class="total" id="subtotal-<?php echo $row['item_id']; ?>"> => <?php echo number_format($subtotal, 2); ?></span>
                                
                                <!-- Hidden fields for each item's data -->
                                <input type="hidden" name="items[<?php echo $row['item_id']; ?>][quantity]" value="<?php echo $row['quantity']; ?>" id="input-quantity-<?php echo $row['item_id']; ?>">
                                <input type="hidden" name="items[<?php echo $row['item_id']; ?>][removed]" value="0" id="input-removed-<?php echo $row['item_id']; ?>">
                                <input type="hidden" id="selling-price-<?php echo $row['item_id']; ?>" value="<?php echo $row['selling_price']; ?>">
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="total-price">Grand Total: <span id="grandTotal"><?php echo number_format($grandTotal, 2); ?></span></div>
                    <?php else: ?>
                        <p>No items found for this order.</p>
                    <?php endif; ?>
                    
                    <button type="submit" class="finish-editing-button">Finish Editing</button>
                </div>
             
            </div>    

        </form>
    </div>

    <script src="scripts/edit-order.js"></script>

    <script>
        let grandTotal = <?php echo $grandTotal; ?>;

        function updateQuantity(itemId, delta) {
            const quantityElement = document.getElementById(`quantity-${itemId}`);
            let quantity = parseInt(quantityElement.textContent) + delta;

            if (quantity < 1) return;

            quantityElement.textContent = quantity;
            document.getElementById(`input-quantity-${itemId}`).value = quantity;

            const sellingPrice = parseFloat(document.getElementById(`selling-price-${itemId}`).value);
            const newSubtotal = quantity * sellingPrice;
            document.getElementById(`subtotal-${itemId}`).textContent = ` => ${newSubtotal.toFixed(2)}`;

            grandTotal += delta * sellingPrice;
            document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
        }

        function removeItem(itemId) {
            document.getElementById(`item-${itemId}`).style.display = 'none';
            document.getElementById(`input-removed-${itemId}`).value = 1;

            const quantity = parseInt(document.getElementById(`input-quantity-${itemId}`).value);
            const sellingPrice = parseFloat(document.getElementById(`selling-price-${itemId}`).value);
            const subtotal = quantity * sellingPrice;

            grandTotal -= subtotal;
            document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
        }

        function updateStatusText() {
            const status = document.getElementById('orderStatus').value;
            const statusText = document.getElementById('statusText');
            const statusIcon = document.querySelector('.status-icon i');

            statusText.className = 'status-text text-' + status;
            statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);

            switch (status) {
                case 'created':
                    statusIcon.className = 'bx bx-plus-circle icon-created';
                    break;
                case 'sent':
                    statusIcon.className = 'bx bx-right-arrow-circle icon-sent';
                    break;
                case 'delivered':
                    statusIcon.className = 'bx bx-check-circle icon-delivered';
                    break;
                case 'cancelled':
                    statusIcon.className = 'bx bx-x-circle icon-cancelled';
                    break;
            }
        }
    </script>
</body>
</html>
