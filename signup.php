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
    $companyCheckQuery = "SELECT * FROM company WHERE company_name = '$company_name'";
    $result = mysqli_query($conn, $companyCheckQuery);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('There exists another company with that name, kindly use another.');</script>";
        exit;
    }

    // Check if username or email already exists
    $userCheckQuery = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $result = mysqli_query($conn, $userCheckQuery);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Username or email already exists!');</script>";
        exit;
    }

    // Insert company into the company table
    $insertCompanyQuery = "INSERT INTO company (company_name, owner_name, date_created) VALUES ('$company_name', '$username', NOW())";
    if (mysqli_query($conn, $insertCompanyQuery)) {
        $company_id = mysqli_insert_id($conn);

        // Insert user into users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insertUserQuery = "INSERT INTO users (username, email, company_id, password) VALUES ('$username', '$email', '$company_id', '$hashed_password')";

        if (mysqli_query($conn, $insertUserQuery)) {
            $user_id = mysqli_insert_id($conn);

            // Insert roles for the company owner (set only the company_owner role to 'yes')
            $insertRoleQuery = "INSERT INTO roles (user_id, company_owner, cashier, store_keeper, delivery_man, admin) VALUES ('$user_id', 'yes', 'no', 'no', 'no', 'no')";
            mysqli_query($conn, $insertRoleQuery);

            // Initialize money record for the company, including the `money` column
            $insertMoneyQuery = "INSERT INTO money (company_id, money, cash, bank, mPesa, tigoPesa, debtors, creditors) VALUES ('$company_id', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00)";
            mysqli_query($conn, $insertMoneyQuery);

            echo "<script>alert('User created successfully!'); window.location.href='user.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Error creating company: " . mysqli_error($conn);
    }
}
?>

