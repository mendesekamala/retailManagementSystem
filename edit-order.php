<?php
session_start();
include('db_connection.php');

error_log("=== EDIT-ORDER.PHP STARTED ===");
error_log("Session data: " . print_r($_SESSION, true));
error_log("GET parameters: " . print_r($_GET, true));

if (!isset($_GET['order_id'])) {
    error_log("No order ID provided - exiting");
    echo "No order ID provided.";
    exit();
}

$order_id = intval($_GET['order_id']);
$company_id = $_SESSION['company_id'];
error_log("Processing order ID: $order_id for company ID: $company_id");

// Fetch order info
$orderQuery = "SELECT * FROM orders WHERE order_id = $order_id AND company_id = $company_id";
error_log("Executing order query: $orderQuery");
$orderResult = mysqli_query($conn, $orderQuery);
$order = mysqli_fetch_assoc($orderResult);

if (!$order) {
    error_log("Order not found for ID: $order_id");
    echo "Order not found.";
    exit();
}

error_log("Found order: " . print_r($order, true));

// Fetch order items with unit information
$itemsQuery = "SELECT oi.*, p.quantity as available_quantity, p.quantified, 
              u.unit_id, u.name as unit_name, u.per_single_quantity
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.product_id
              LEFT JOIN units u ON oi.sold_in = u.name AND oi.product_id = u.product_id AND u.company_id = $company_id
              WHERE oi.order_id = $order_id AND oi.company_id = $company_id";
error_log("Executing items query: $itemsQuery");
$itemsResult = mysqli_query($conn, $itemsQuery);

// Helper for icon based on status
function getStatusIcon($status) {
    switch ($status) {
        case 'created': return '📝';
        case 'sent': return '📤';
        case 'delivered': return '✅';
        case 'cancelled': return '❌';
        default: return '❓';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order</title>
    <link rel="stylesheet" href="css/edit-order.css">
    <link rel="stylesheet" href="css/payment-modal_edit-order.css">
</head>
<body>
<div class="edit-order-modal">
    <div class="edit-order-content">
        <h2 class="order-status">
            <?php echo getStatusIcon($order['status']) . " " . ucfirst($order['status']); ?>
        </h2>
        <p class="order-heading">Editing Order No: <strong><?php echo $order['order_id']; ?></strong></p>

        <div class="order-items">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="orderItems">
                    <?php
                    $i = 1;
                    $total = 0;
                    while ($item = mysqli_fetch_assoc($itemsResult)) {
                        $subtotal = $item['quantity'] * $item['selling_price'];
                        $total += $subtotal;
                        
                        // Calculate available quantity in the current unit
                        $available_in_unit = $item['available_quantity'];
                        if ($item['per_single_quantity'] && $item['per_single_quantity'] > 0) {
                            $available_in_unit = $item['available_quantity'] / $item['per_single_quantity'];
                        }
                        
                        // Clean up the name to remove any existing unit information
                        $clean_name = $item['name'];
                        
                        // Only show unit if sold_in exists and it's not "whole"
                        $unit_display = '';
                        if (!empty($item['sold_in']) && $item['sold_in'] !== 'whole') {
                            $clean_name = preg_replace('/\s*\(.*?\)\s*$/', '', $item['name']);
                            $unit_display = " ({$item['sold_in']})";
                        }
                        
                        echo "<tr data-item-id='{$item['item_id']}' 
                                data-product-id='{$item['product_id']}' 
                                data-available-qty='{$item['available_quantity']}'
                                data-unit-multiplier='".($item['per_single_quantity'] ?: 1)."'
                                data-sold-in='{$item['sold_in']}'
                                data-unit-id='{$item['unit_id']}'
                                data-buying-price='{$item['buying_price']}'>
                            <td><button onclick='removeItem(this)' class='remove-btn'>❌</button></td>
                            <td>$i</td>
                            <td>{$clean_name}$unit_display</td>
                            <td>
                                <button onclick='changeQty(this, -1)' class='qty-btn'>◀</button>
                                <span class='qty'>{$item['quantity']}</span>
                                <button onclick='changeQty(this, 1)' class='qty-btn'>▶</button>
                            </td>
                            <td><span class='price'>{$item['selling_price']}</span></td>
                            <td><span class='subtotal'>" . number_format($subtotal, 2) . "</span></td>
                        </tr>";
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="order-total">
            <p>Total: <span id="orderTotal" data-raw="<?php echo $total; ?>"><?php echo number_format($total, 2); ?></span></p>
        </div>

        <button onclick="finishEditing()" class="complete-btn">Finish Editing</button>
    </div>
</div>

<script>
// Track stock changes during editing
const pendingStockChanges = {};

// Initialize with original quantities
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM loaded - initializing order editing");
    document.querySelectorAll('#orderItems tr').forEach(row => {
        const itemId = row.getAttribute('data-item-id');
        const originalQty = parseInt(row.querySelector('.qty').textContent);
        const availableQty = parseFloat(row.getAttribute('data-available-qty'));
        const unitMultiplier = parseFloat(row.getAttribute('data-unit-multiplier')) || 1;
        
        // Calculate available quantity in the current unit
        const availableInUnit = availableQty / unitMultiplier;
        
        pendingStockChanges[itemId] = {
            originalQty: originalQty,
            remainingStock: availableInUnit,
            unitMultiplier: unitMultiplier
        };
        
        console.log(`Initialized item ${itemId}:`, pendingStockChanges[itemId]);
    });
});

function removeItem(btn) {
    const row = btn.closest('tr');
    const itemId = row.getAttribute('data-item-id');
    console.log(`Removing item ${itemId}`);
    
    // Return reserved stock if item is removed
    if (pendingStockChanges[itemId]) {
        const currentQty = parseInt(row.querySelector('.qty').textContent);
        const originalQty = pendingStockChanges[itemId].originalQty;
        pendingStockChanges[itemId].remainingStock += (currentQty - originalQty);
        console.log(`Stock returned for item ${itemId}:`, pendingStockChanges[itemId]);
    }
    
    row.remove();
    updateTotal();
}

function changeQty(btn, delta) {
    const qtySpan = btn.parentNode.querySelector('.qty');
    let qty = parseInt(qtySpan.textContent);
    const newQty = qty + delta;
    console.log(`Changing quantity from ${qty} to ${newQty} (delta: ${delta})`);

    if (newQty < 1) {
        console.log("Quantity cannot be less than 1");
        return;
    }

    const row = btn.closest('tr');
    const itemId = row.getAttribute('data-item-id');
    const productId = row.getAttribute('data-product-id');
    const unitMultiplier = parseFloat(row.getAttribute('data-unit-multiplier')) || 1;

    // Initialize if not already tracked
    if (!pendingStockChanges[itemId]) {
        const availableQty = parseFloat(row.getAttribute('data-available-qty'));
        const availableInUnit = availableQty / unitMultiplier;
        pendingStockChanges[itemId] = {
            originalQty: qty,
            remainingStock: availableInUnit,
            unitMultiplier: unitMultiplier
        };
        console.log(`New item tracking initialized for ${itemId}:`, pendingStockChanges[itemId]);
    }

    const stockInfo = pendingStockChanges[itemId];

    // Block increment if stock is insufficient
    if (delta === 1 && stockInfo.remainingStock <= 0) {
        console.log(`Insufficient stock for item ${itemId}`);
        alert("You can't add more of these products, they are over in the store");
        return;
    }

    // Update pending changes
    if (delta === 1) {
        stockInfo.remainingStock--;
    } else if (delta === -1) {
        stockInfo.remainingStock++;
    }

    console.log(`Updated stock info for item ${itemId}:`, stockInfo);
    qtySpan.textContent = newQty;
    
    // Update subtotal
    const price = parseFloat(row.querySelector('.price').textContent);
    const newSubtotal = newQty * price;
    row.querySelector('.subtotal').textContent = newSubtotal.toFixed(2);
    
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('#orderItems tr').forEach(row => {
        const subtotal = parseFloat(row.querySelector('.subtotal').textContent.replace(/,/g, ''));
        total += subtotal;
    });
    const orderTotal = document.getElementById('orderTotal');
    orderTotal.textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    orderTotal.setAttribute('data-raw', total);
    console.log("Updated order total:", total);
}

function finishEditing() {
    console.group("finishEditing() started");
    const orderId = <?php echo $order_id; ?>;
    const orderItems = [];
    console.log("Processing order ID:", orderId);
    
    document.querySelectorAll('#orderItems tr').forEach(row => {
        const productId = row.getAttribute('data-product-id');
        const quantity = parseInt(row.querySelector('.qty').textContent);
        const price = parseFloat(row.querySelector('.price').textContent);
        const soldIn = row.getAttribute('data-sold-in');
        const unitMultiplier = parseFloat(row.getAttribute('data-unit-multiplier')) || 1;
        const unitId = row.getAttribute('data-unit-id');
        
        const unit_type = soldIn === 'whole' ? 'whole' : 'unit';
        const unit_id = soldIn === 'whole' ? null : unitId;
        
        const name = row.querySelector('td:nth-child(3)').textContent.replace(/\(.*?\)/g, '').trim();
        const buying_price = parseFloat(row.getAttribute('data-buying-price')) || 0;
        
        const itemData = {
            product_id: productId,
            name: name,
            quantity: quantity,
            price: price,
            sum: quantity * price,
            unit_type: unit_type,
            unit_id: unit_id,
            buying_price: buying_price,
            sold_in: soldIn
        };
        
        console.log("Adding item to order:", itemData);
        orderItems.push(itemData);
    });

    const total = orderItems.reduce((sum, item) => sum + item.sum, 0);
    console.log("Calculated order total:", total);
    document.getElementById('orderTotal').textContent = total.toFixed(2);
    document.getElementById('orderTotal').setAttribute('data-raw', total);
    
    console.log("All order items:", orderItems);
    console.groupEnd();
    
    openPaymentModal(orderItems);
}

function openPaymentModal(orderItems) {
    console.group("openPaymentModal() started");
    const total = parseFloat(document.getElementById('orderTotal').getAttribute('data-raw'));
    const orderId = <?php echo $order_id; ?>;
    
    console.log("Opening payment modal with:", {
        orderItems: orderItems,
        total: total,
        orderId: orderId
    });

    fetch(`payment-modal_edit-order.php?order_id=${orderId}`)
        .then(response => {
            console.log("Payment modal response status:", response.status);
            return response.text();
        })
        .then(html => {
            console.log("Payment modal HTML received");
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = html;
            document.body.appendChild(modalContainer);

            if (!document.querySelector('link[href="css/payment-model.css"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'css/payment-model.css';
                document.head.appendChild(link);
            }

            const script = document.createElement('script');
            script.src = 'scripts/payment_edit-order.js';
            script.onload = function() {
                console.log("Payment modal script loaded, passing data:", {
                    orderItems: orderItems,
                    total: total,
                    orderId: orderId
                });
                
                window.orderItems = orderItems;
                openModal(total, orderId);
                console.groupEnd();
            };
            document.body.appendChild(script);
        })
        .catch(error => {
            console.error("Error loading payment modal:", error);
            console.groupEnd();
        });
}
</script>
</body>
</html>