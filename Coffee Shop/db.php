<?php
$host = 'localhost';
$user = 'root';      // default XAMPP user on Mac
$pass = '';          // default password is empty
$dbname = 'leaky_latte';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
