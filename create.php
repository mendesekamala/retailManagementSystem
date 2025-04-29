<?php
session_start();
include('db_connection.php');

// Initialize message variables
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $quantified_as = $_POST['quantified_as'];
    $under_stock_reminder = $_POST['under_stock_reminder'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $quantity = 0; // Default quantity
    
    // Retrieve user_id and company_id from session
    $created_by = $_SESSION['user_id'];
    $company_id = $_SESSION['company_id'];

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('product_') . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }

    // Insert product into database
    $sql = "INSERT INTO products (name, quantified, under_stock_reminder, buying_price, selling_price, quantity, created_by, company_id, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssdddiiis", $name, $quantified_as, $under_stock_reminder, $buying_price, $selling_price, $quantity, $created_by, $company_id, $image_path);

        if ($stmt->execute()) {
            $message = "Product added successfully!";
            $message_type = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
        }
    } else {
        $message = "Error in preparing statement: " . $conn->error;
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/create.css">
</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="dashboard-container">
        <header class="page-header">
            <h1><i class='bx bx-plus-circle'></i> Create New Product</h1>
        </header>

        <div class="two-panel-layout">
            <!-- Left Panel - Form -->
            <div class="form-panel">
                <div class="card create-form-card">
                    <?php if ($message): ?>
                        <div class="message <?= $message_type ?>"><?= $message ?></div>
                    <?php endif; ?>

                    <form action="create.php" method="POST" enctype="multipart/form-data" id="product-form">
                        <div class="form-section">
                            
                        <h3>Basic Information</h3>

                            <div class="form-group">
                                <label for="name">Product Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter product name" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="quantified_as">Quantified As</label>
                                    <input type="text" id="quantified_as" name="quantified_as" placeholder="e.g., kg, liter, piece" required>
                                </div>

                                <div class="form-group">
                                    <label for="under_stock_reminder">Low Stock Alert</label>
                                    <input type="number" id="under_stock_reminder" name="under_stock_reminder" placeholder="Minimum quantity before alert" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="buying_price">Buying Price</label>
                                    <div class="input-with-icon">
                                        <i class='bx bx-dollar'></i>
                                        <input type="number" id="buying_price" name="buying_price" step="0.01" placeholder="0.00" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="selling_price">Selling Price</label>
                                    <div class="input-with-icon">
                                        <i class='bx bx-dollar'></i>
                                        <input type="number" id="selling_price" name="selling_price" step="0.01" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Product Image</h3>
                            
                            <div class="image-upload-container">
                                <div class="image-preview" id="image-preview">
                                    <i class='bx bx-image-add'></i>
                                    <span>select image</span>
                                </div>
                                
                                <input type="file" id="product_image" name="product_image" accept="image/*" hidden>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class='bx bx-save'></i> Save Product
                            </button>
                            <button type="reset" class="btn-secondary">
                                <i class='bx bx-reset'></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Panel - Welcome Message -->
            <div class="welcome-panel">
                <div class="welcome-content">
                    <div class="welcome-header">
                        <h2>Inventory Management System</h2>
                        <p class="system-version">v2.0</p>
                    </div>
                    
                    <div class="typing-container">
                        <p id="typing-text" class="typing-animation"></p>
                    </div>
                    
                    <div class="welcome-features">
                        <div class="feature-item">
                            <i class='bx bx-check-circle'></i>
                            <span>Easy product registration</span>
                        </div>
                        <div class="feature-item">
                            <i class='bx bx-check-circle'></i>
                            <span>Inventory tracking</span>
                        </div>
                        <div class="feature-item">
                            <i class='bx bx-check-circle'></i>
                            <span>Sales management</span>
                        </div>
                    </div>
                    
                    <div class="quick-stats">
                        <div class="stat-card">
                            <i class='bx bx-package'></i>
                            <div>
                                <h3>Total Products</h3>
                                <p>Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts/create.js"></script>
    <script>
        // Enhanced typing animation
        const messages = [
            "Welcome to Product Creation!",
            "Let's grow your inventory...",
            "Add products with ease...",
            "Manage your stock efficiently..."
        ];
        
        let messageIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        let typingSpeed = 100;
        const typingText = document.getElementById('typing-text');
        
        function typeWriter() {
            const currentMessage = messages[messageIndex];
            
            if (isDeleting) {
                typingText.textContent = currentMessage.substring(0, charIndex - 1);
                charIndex--;
                typingSpeed = 50;
            } else {
                typingText.textContent = currentMessage.substring(0, charIndex + 1);
                charIndex++;
                typingSpeed = charIndex % 3 === 0 ? 150 : 100;
            }
            
            if (!isDeleting && charIndex === currentMessage.length) {
                typingSpeed = 2000; // Pause at end
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                messageIndex = (messageIndex + 1) % messages.length;
                typingSpeed = 500;
            }
            
            setTimeout(typeWriter, typingSpeed);
        }
        
        // Start the typing effect
        setTimeout(typeWriter, 1000);
        
        // Fetch product count (example - replace with actual API call)
        setTimeout(() => {
            document.querySelector('.quick-stats .stat-card p').textContent = "125"; // Example number
        }, 1500);
    </script>
</body>
</html>