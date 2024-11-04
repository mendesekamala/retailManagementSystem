<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username exists
    $userQuery = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $userQuery);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            

            // Fetch user roles
            $roleQuery = "SELECT * FROM roles WHERE user_id = '" . $user['user_id'] . "'";
            $roleResult = mysqli_query($conn, $roleQuery);
            $roles = mysqli_fetch_assoc($roleResult);

            // Store roles in session
            $_SESSION['roles'] = $roles;

            // Redirect based on role
            if ($roles['cashier'] === 'yes') {
                header("Location: user-cashier.php");
            } elseif ($roles['company_owner'] === 'yes') {
                header("Location: user-company_owner.php");
            } elseif ($roles['delivery_man'] === 'yes') {
                header("Location: user-delivery_man.php");
            } elseif ($roles['store_keeper'] === 'yes') {
                header("Location: user-store_keeper.php");
            } else {
                // If no specific role, redirect to a default page
                header("Location: users.php");
            }

            exit(); // Ensure no further code runs after the redirect
        } else {
            echo "<script>alert('Incorrect password!');</script>";
            header("Location: register.php");
        }
    } else {
        echo "<script>alert('Username does not exist!');</script>";
        header("Location: register.php");
    }
}
?>
