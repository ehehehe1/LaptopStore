<?php
session_start();
require '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch admin details
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Fetch user count
$userCountStmt = $conn->query("SELECT COUNT(*) FROM users");
$userCount = $userCountStmt->fetchColumn();

// Fetch receipt count
$receiptCountStmt = $conn->query("SELECT COUNT(*) FROM receipts");
$receiptCount = $receiptCountStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <title>Dashboard</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-5">
        <h1>Welcome, <?php echo htmlspecialchars($admin['username']); ?></h1>
        <div class="row">
            <div class="col-md-6">
                <h2>User Management</h2>
                <p>Total Users: <?php echo $userCount; ?></p>
                <a href="users/list.php" class="btn btn-primary">Manage Users</a>
                <a href="users/add.php" class="btn btn-secondary">Add User</a>
            </div>
            <div class="col-md-6">
                <h2>Import Receipts</h2>
                <p>Total Receipts: <?php echo $receiptCount; ?></p>
                <a href="receipts/list.php" class="btn btn-primary">View Receipts</a>
            </div>
        </div>
    </div>
    <script src="../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>