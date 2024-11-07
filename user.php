<?php
// Start session
session_start();

// Include database connection file
include 'db_connection.php';

// Check if user ID is set in session
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user information
$userQuery = "SELECT u.*, c.company_name, r.company_owner, r.cashier, r.store_keeper, r.delivery_man
              FROM users u
              LEFT JOIN company c ON u.company_id = c.company_id
              LEFT JOIN roles r ON u.role_id = r.role_id
              WHERE u.user_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
    $fullName = $user['firstName'] . ' ' . $user['lastName'];
    $username = $user['username'];
    $email = $user['email'];
    $phoneNo = $user['phoneNo'];
    $companyName = $user['company_name'] ?? 'N/A';
    
    // Determine role
    $role = 'N/A';
    if ($user['company_owner'] === 'yes') {
        $role = 'Company Owner';
    } elseif ($user['cashier'] === 'yes') {
        $role = 'Cashier';
    } elseif ($user['store_keeper'] === 'yes') {
        $role = 'Store Keeper';
    } elseif ($user['delivery_man'] === 'yes') {
        $role = 'Delivery Man';
    }
} else {
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/users.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <title>User Profile</title>

    <style>
        
    </style>

</head>

<?php include('sidebar.php'); ?>

<body>
    <div class="container">
        <!-- Left Grid - User Profile -->
        <div class="left-grid">
            <div class="profile-photo">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?= $user['profile_image'] ?>" alt="User Profile Photo">
                <?php else: ?>
                    <i class="bx bx-user-circle" style="font-size: 100px; color: #888;"></i>
                <?php endif; ?>
            </div>
            <h2 class="section-title">User Profile</h2>
            <hr>
            <div class="profile-info">
                <div class="info-item">
                    <i class='bx bx-user'></i> <span class="label">Full Name:</span> <span class="info-data"><?= htmlspecialchars($fullName) ?></span>
                </div>
                <div class="info-item">
                    <i class='bx bx-user-check'></i> <span class="label">Username:</span> <span class="info-data"><?= htmlspecialchars($username) ?></span>
                </div>
                <div class="info-item">
                    <i class='bx bx-envelope'></i> <span class="label">Email:</span> <span class="info-data"><?= htmlspecialchars($email) ?></span>
                </div>
                <div class="info-item">
                    <i class='bx bx-phone'></i> <span class="label">Phone No:</span> <span class="info-data"><?= htmlspecialchars($phoneNo) ?></span>
                </div>
            </div>
        </div>

        <!-- Right Grids - Company and Settings -->
        <div class="right-grids">
            <div class="top-grid">
                <h2 class="section-title">Company Details</h2>
                <hr>
                <div class="company-info">
                    <div class="info-item">
                        <i class='bx bx-store'></i> <span class="label">Company Name:</span> <span class="info-data"><?= htmlspecialchars($companyName) ?></span>
                    </div>
                    <div class="info-item">
                        <i class='bx bx-user-pin'></i> <span class="label">Role:</span> <span class="info-data"><?= htmlspecialchars($role) ?></span>
                    </div>
                </div>
            </div>
            <div class="bottom-grid">
                <h2 class="section-title">Settings</h2>
                <hr>
                <div class="settings-info">
                    <div class="info-item">
                        <i class='bx bx-key'></i> <span class="label">Change Password</span> <i class='bx bx-pencil'></i>
                    </div>
                    <div class="info-item">
                        <i class='bx bx-moon'></i> <span class="label">Dark Mode</span> <input type="checkbox" class="toggle-switch">
                    </div>
                    <div class="info-item">
                        <i class='bx bx-message'></i> <span class="label">Messages</span> <span class="info-data">5 Unread</span>
                    </div>
                    <div class="info-item">
                        <i class='bx bx-book'></i> <span class="label">Records</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
