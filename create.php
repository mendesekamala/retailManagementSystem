<?php
session_start();

// Include database connection
include('db_connection.php');

// Initialize an empty message variable
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $quantified_as = $_POST['quantified_as'];
    $under_stock_reminder = $_POST['under_stock_reminder'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];

   // Define a variable for quantity
    $quantity = 0;

    // Insert product into database
    $sql = "INSERT INTO products (name, quantified, under_stock_reminder, buying_price, selling_price, quantity) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        // Display SQL error if prepare() fails
        die("Error in prepare: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssdddi", $name, $quantified_as, $under_stock_reminder, $buying_price, $selling_price, $quantity);

    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link rel="stylesheet" href="css/create.css">
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <h1>Create Product</h1>
    
    <!-- Display success/error message -->
    <?php if ($message): ?>
        <div class="message"><?= $message; ?></div>
    <?php endif; ?>

    <form action="create.php" method="POST" class="create-form">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantified_as">Quantified As</label>
                <input type="text" id="quantified_as" name="quantified_as" required>
            </div>

            <div class="form-group">
                <label for="under_stock_reminder">Under-Stock Reminder</label>
                <input type="number" id="under_stock_reminder" name="under_stock_reminder" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buying_price">Buying Price</label>
                <input type="number" id="buying_price" name="buying_price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="selling_price">Selling Price</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" required>
            </div>
        </div>
    
        <button type="submit">Add Product</button>
        <button type="reset">Reset</button>
    </form>
</div>

</body>
</html>
