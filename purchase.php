<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Modal</title>
    <link rel="stylesheet" href="css/payment-modal.css"> <!-- ✅ Link CSS -->
</head>
<body>

    <?php include 'payment-modal.php'; 
        $grandTotal= 1000;
    ?>
    
    <button onclick="openModal(<?= $grandTotal ?>)">Complete Transaction</button>
    <script src="scripts/payment.js"></script>
</body>
