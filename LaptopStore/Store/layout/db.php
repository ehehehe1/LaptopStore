<?php
$host = 'localhost';
$db   = 'laptop';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>

