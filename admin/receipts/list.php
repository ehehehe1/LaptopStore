<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM receipts ORDER BY created_at DESC");
$stmt->execute();
$receipts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <title>Danh sách biên lai</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-5">
        <h2>Danh sách biên lai nhập</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ngày</th>
                    <th>Tổng tiền</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receipts as $receipt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($receipt['id']); ?></td>
                        <td><?php echo htmlspecialchars($receipt['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($receipt['total_amount']); ?></td>
                        <td><a href="view.php?id=<?php echo htmlspecialchars($receipt['id']); ?>" class="btn btn-info">Xem</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>