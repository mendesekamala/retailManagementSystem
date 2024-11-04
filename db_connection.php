<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'test_units';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
