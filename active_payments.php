<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the current user's session data
$created_by = $_SESSION['user_id'];  // The user who is making the change
$company_id = $_SESSION['company_id'];  // The company to update

// If the form is submitted, update the payment methods
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Define the payment methods array
    $payment_methods = [
        'cash', 'NMB', 'CRDB', 'NBC', 'mpesa', 'airtel_money', 'tigo_pesa', 'halopesa', 'azamPesa'
    ];
    
    // Loop through each payment method and update to 'yes' if selected
    foreach ($payment_methods as $method) {
        if (isset($_POST[$method]) && $_POST[$method] == 'on') {
            $sql = "UPDATE payment_methods SET $method = 'yes', created_by = ? WHERE company_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $created_by, $company_id);
            $stmt->execute();
        }
    }
    
    // After updating, redirect to a confirmation page or reload the page
    echo "<script>alert('Payment methods updated successfully!'); window.location.href = 'active_payments.php';</script>";
}

// Fetch current payment method status from the database
$query = "SELECT * FROM payment_methods WHERE company_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $company_id);
$stmt->execute();
$result = $stmt->get_result();
$payment_method_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Payment Methods</title>
    <link rel="stylesheet" href="css/active_payments.css">
</head>

<?php include ('sidebar.php'); ?>

<body>

<div class="body-container">
    <div class="container">
        <h2>Manage Active Payment Methods</h2>
        
        <!-- Payment Methods Form -->
        <form action="active_payments.php" method="POST">
            <div class="payment-methods">
                <label><input type="checkbox" name="cash" <?php echo ($payment_method_data['cash'] == 'yes') ? 'checked' : ''; ?>> Cash</label>
                <label><input type="checkbox" name="NMB" <?php echo ($payment_method_data['NMB'] == 'yes') ? 'checked' : ''; ?>> NMB</label>
                <label><input type="checkbox" name="CRDB" <?php echo ($payment_method_data['CRDB'] == 'yes') ? 'checked' : ''; ?>> CRDB</label>
                <label><input type="checkbox" name="NBC" <?php echo ($payment_method_data['NBC'] == 'yes') ? 'checked' : ''; ?>> NBC</label>
                <label><input type="checkbox" name="mpesa" <?php echo ($payment_method_data['mpesa'] == 'yes') ? 'checked' : ''; ?>> Mpesa</label>
                <label><input type="checkbox" name="airtel_money" <?php echo ($payment_method_data['airtel_money'] == 'yes') ? 'checked' : ''; ?>> Airtel Money</label>
                <label><input type="checkbox" name="tigo_pesa" <?php echo ($payment_method_data['tigo_pesa'] == 'yes') ? 'checked' : ''; ?>> Tigo Pesa</label>
                <label><input type="checkbox" name="halopesa" <?php echo ($payment_method_data['halo_pesa'] == 'yes') ? 'checked' : ''; ?>> Halopesa</label>
                <label><input type="checkbox" name="azamPesa" <?php echo ($payment_method_data['azam_pesa'] == 'yes') ? 'checked' : ''; ?>> AzamPesa</label>
            </div>
            
            <button type="submit" class="finish-btn">Finish</button>
        </form>
    </div>
</div>
    

</body>
</html>
