<?php
// Include the database connection
include('db_connection.php');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$feedback = ''; // To store success or error messages
$feedback_class = ''; // To store the CSS class for styling the message

// Handle form submission for creating users
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phoneNo = $_POST['phoneNo'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Role checkboxes
    $admin = isset($_POST['admin']) ? 'yes' : 'no';
    $cashier = isset($_POST['cashier']) ? 'yes' : 'no';
    $store_keeper = isset($_POST['store_keeper']) ? 'yes' : 'no';
    $delivery_man = isset($_POST['delivery_man']) ? 'yes' : 'no';

    // Insert the user data into the users table
    $insertUserQuery = "
        INSERT INTO users (username, firstName, lastName, email, phoneNo, password)
        VALUES ('$username', '$firstName', '$lastName', '$email', '$phoneNo', '$password')";

    if (mysqli_query($conn, $insertUserQuery)) {
        // Get the last inserted user ID
        $user_id = mysqli_insert_id($conn);

        // Insert role data into the roles table
        $insertRoleQuery = "
            INSERT INTO roles (user_id, company_owner, cashier, store_keeper, delivery_man, admin)
            VALUES ('$user_id', '$company_owner', '$cashier', '$store_keeper', '$delivery_man', '$admin')";

        if (mysqli_query($conn, $insertRoleQuery)) {
            // Update the user's role_id in the users table from the roles table
            $getRoleIdQuery = "SELECT role_id FROM roles WHERE user_id = '$user_id' LIMIT 1";
            $roleResult = mysqli_query($conn, $getRoleIdQuery);
            $roleRow = mysqli_fetch_assoc($roleResult);
            $role_id = $roleRow['role_id'];

            $updateUserRoleQuery = "UPDATE users SET role_id = '$role_id' WHERE user_id = '$user_id'";
            mysqli_query($conn, $updateUserRoleQuery);

            $feedback = "User created successfully!";
            $feedback_class = "success";
        } else {
            $feedback = "Error: " . mysqli_error($conn);
            $feedback_class = "error";
        }
    } else {
        $feedback = "Error: " . mysqli_error($conn);
        $feedback_class = "error";
    }
}


// Get company ID of the logged-in company owner
$user_id = $_SESSION['user_id'];
$companyQuery = "SELECT company_id FROM users WHERE user_id = '$user_id' LIMIT 1";
$companyResult = mysqli_query($conn, $companyQuery);
$companyRow = mysqli_fetch_assoc($companyResult);
$company_id = $companyRow['company_id'];

// Fetch users associated with the logged-in owner's company
$usersQuery = "
    SELECT u.username, CONCAT(u.firstName, ' ', u.lastName) AS fullname, u.email, u.phoneNo,
           CASE
               WHEN r.company_owner = 'yes' THEN 'owner'
               WHEN r.cashier = 'yes' THEN 'cashier'
               WHEN r.store_keeper = 'yes' THEN 'storekeeper'
               WHEN r.delivery_man = 'yes' THEN 'deliveryman'
               ELSE 'unknown'
           END AS role
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    WHERE u.company_id = '$company_id'";
$usersResult = mysqli_query($conn, $usersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Roles</title>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/owner-users.css">
</head>

<?php include ('sidebar.php'); ?>

<body>


<div class="container-users"> 
    <h1><i class='bx bx-user'></i> Company Users</h1>

    <!-- Users Table -->
    <table class="users-table">
        <thead>
            <tr>
                
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone No</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                <tr>
                    
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phoneNo']); ?></td>
                    <td class="role-<?php echo htmlspecialchars($user['role']); ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </td>
                    <td>
                        <i class='bx bx-pencil' title="Edit"></i>
                        <i class='bx bx-show' title="View"></i>
                        <i class='bx bx-trash' title="Delete"></i>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<div class="container">
    <!-- Feedback message -->
    <?php if ($feedback): ?>
        <div id="feedback" class="feedback <?php echo $feedback_class; ?>">
            <?php echo $feedback; ?>
        </div>
    <?php endif; ?>

    <h1><i class='bx bx-user-plus'></i> Create User & Assign Roles</h1>
    
    <form method="POST" action="owner-users.php" class="user-form">
    <!-- User information inputs -->
    <div class="form-row">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phoneNo">Phone Number:</label>
            <input type="text" id="phoneNo" name="phoneNo" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
    </div>

    <!-- Role checkboxes -->
    <h2>Assign Roles</h2>
    <div class="form-row role-checkboxes">
        <label><input type="checkbox" name="admin"> Admin</label>
        <label><input type="checkbox" name="cashier"> Cashier</label>
        <label><input type="checkbox" name="store_keeper"> Store Keeper</label>
        <label><input type="checkbox" name="delivery_man"> Delivery Man</label>
    </div>

    <!-- Submit button -->
    <button type="submit" class="create-user-btn"><i class='bx bx-user-plus'></i> Create User</button>
</form>

</div>




<!-- JavaScript to hide the message after 3 seconds -->
<script>
    // Check if the feedback message exists
    const feedback = document.getElementById('feedback');
    if (feedback) {
        // Set a timeout to hide the message after 3 seconds (3000 ms)
        setTimeout(() => {
            feedback.style.display = 'none';
        }, 3000);
    }
</script>

</body>
</html>

</html>
