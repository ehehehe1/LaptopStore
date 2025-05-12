<?php
session_start();
require '../../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $receipt_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM receipts WHERE id = ?");
    $stmt->execute([$receipt_id]);
    $receipt = $stmt->fetch();

    if (!$receipt) {
        die("Receipt not found.");
    }
} else {
    die("No receipt ID provided.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <title>Receipt Details</title>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <h2>Receipt Details</h2>
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($receipt['id']); ?></td>
            </tr>
            <tr>
                <th>Date</th>
                <td><?php echo htmlspecialchars($receipt['date']); ?></td>
            </tr>
            <tr>
                <th>Supplier</th>
                <td><?php echo htmlspecialchars($receipt['supplier']); ?></td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td><?php echo htmlspecialchars($receipt['total_amount']); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo htmlspecialchars($receipt['status']); ?></td>
            </tr>
        </table>
        <a href="list.php" class="btn btn-secondary">Back to Receipts List</a>
    </div>
    <script src="../../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>
</body>
</html>