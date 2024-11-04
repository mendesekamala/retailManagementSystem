<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $company_name = $_POST['company_name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit;
    }

    // Check if the company name already exists
    $companyCheckQuery = "SELECT * FROM users WHERE company_name = '$company_name'";
    $result = mysqli_query($conn, $companyCheckQuery);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('There exists another company with that name, kindly use another.');</script>";
        exit;
    }

    // Insert user into users table
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insertUserQuery = "INSERT INTO users (username, email, company_name, password) VALUES ('$username', '$email', '$company_name', '$hashed_password')";

    if (mysqli_query($conn, $insertUserQuery)) {
        $user_id = mysqli_insert_id($conn);

        // Insert roles for the company owner (all roles set to 'yes')
        $insertRoleQuery = "INSERT INTO roles (user_id, company_owner, cashier, store_keeper, delivery_man) VALUES ('$user_id', 'yes', 'yes', 'yes', 'yes')";
        mysqli_query($conn, $insertRoleQuery);

        echo "<script>alert('User created successfully!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
