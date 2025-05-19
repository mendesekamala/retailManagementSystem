<?php
session_start();
include 'db_connection.php';

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['order_id'];
$company_id = $_SESSION['company_id'];

// Fetch order details
$orderQuery = "SELECT o.*, c.company_name, c.owner_name 
               FROM orders o
               JOIN company c ON o.company_id = c.company_id
               WHERE o.order_id = ? AND o.company_id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("ii", $order_id, $company_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Fetch order items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = ? AND company_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("ii", $order_id, $company_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch cashier phone number
$cashierQuery = "SELECT u.phoneNo 
                FROM users u
                JOIN roles r ON u.user_id = r.user_id
                WHERE u.company_id = ? AND r.cashier = 'yes'
                LIMIT 1";
$stmt = $conn->prepare($cashierQuery);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$cashier = $stmt->get_result()->fetch_assoc();

// Add a query to fetch owner details similar to how you fetch cashier
$ownerQuery = "SELECT u.phoneNo 
              FROM users u
              JOIN roles r ON u.user_id = r.user_id
              WHERE u.company_id = ? AND r.company_owner = 'yes'
              LIMIT 1";
$stmt = $conn->prepare($ownerQuery);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$owner = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - <?= htmlspecialchars($order['orderNo']) ?></title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="css/sidebar.css" rel="stylesheet">
    <link href="css/view-order.css" rel="stylesheet">
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <div class="order-header-container">
            <header class="dashboard-header">
                <h1>Viewing Order #<?= htmlspecialchars($order['orderNo']) ?></h1>
                <div class="order-actions">
                    <button class="action-btn print-btn" onclick="window.print()" title="Print Receipt">
                        <i class='bx bx-printer'></i>
                    </button>
                    <button class="action-btn email-btn" title="Email Receipt">
                        <i class='bx bx-envelope'></i>
                    </button>
                    <button class="action-btn whatsapp-btn" title="Send via WhatsApp">
                        <i class='bx bxl-whatsapp'></i>
                    </button>
                </div>
            </header>
        </div>

        <div class="receipt-wrapper">
            <div class="receipt-container">
                <div class="receipt-header">
                    <h2><?= htmlspecialchars($order['company_name']) ?></h2>
                    <p>Order Receipt</p>
                </div>

                <div class="receipt-meta">
                    <div>
                        <strong>Order #:</strong> <?= htmlspecialchars($order['orderNo']) ?>
                    </div>
                    <div>
                        <strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($order['time'])) ?>
                    </div>
                </div>

                <div class="contact-info">
                    <div>
                        <strong>Owner:</strong> <?= htmlspecialchars($order['owner_name']) ?><br>
                        <strong>Phone:</strong> <?= htmlspecialchars($owner['phoneNo'] ?? 'N/A') ?>
                    </div>
                    <div>
                        <strong>Cashier:</strong> <?= htmlspecialchars($_SESSION['first_name'] ?? 'Cashier') ?><br>
                        <strong>Phone:</strong> <?= htmlspecialchars($cashier['phoneNo'] ?? 'N/A') ?>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Item</th>
                            <th class="text-right">@Price</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td class="text-right"><?= number_format($item['selling_price']) ?></td>
                            <td class="text-right"><?= $item['quantity'] ?></td>
                            <td class="text-right"><?= number_format($item['sum']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="total-section">
                    
                    <div class="total-row grand-total">
                        <span>GRAND TOTAL:</span>
                        <span>Tsh <?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>

                <div class="customer-message">
                    <p>Welcome back <?= htmlspecialchars($order['customer_name']) ?>!</p>
                    <p>Thank you for your business</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>