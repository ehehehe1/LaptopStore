<?php
session_start();
require 'includes/db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Admin Panel</title>
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome to the Admin Panel</h1>
        <p>Please <a href="admin/login.php">log in</a> to access the admin functionalities.</p>
    </div>
    <script src="assets/bootstrap/bootstrap.min.js"></script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>