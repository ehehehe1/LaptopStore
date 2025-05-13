<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /LaptopStore/Store/layout/login.php");
    exit;
}

$madh = isset($_GET['madh']) ? $_GET['madh'] : '';
$phuongthuc = $_SESSION['phuongthuc_thanh_toan'] ?? 'cod';
$thanh_toan_status = $_SESSION['thanh_toan_status'] ?? 'success';

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT TONGTIEN, DIACHI, NGAYDH FROM DONHANG WHERE MADH = ? AND MATK = ?");
$stmt->bind_param("ss", $madh, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: /LaptopStore/Store/layout/cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đơn hàng</title>
    <style>
        .confirmation {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #28a745;
        }
        p {
            font-size: 1.1rem;
            color: #333;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="confirmation">
    <h1>Đơn hàng đã được xác nhận!</h1>
    <p>Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn có mã <strong><?php echo htmlspecialchars($madh); ?></strong> đã được ghi nhận.</p>
    <p><strong>Tổng tiền:</strong> <?php echo number_format($order['TONGTIEN'], 0, ',', '.'); ?> đ</p>
    <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order['DIACHI']); ?></p>
    <p><strong>Phương thức thanh toán:</strong> <?php echo $phuongthuc === 'cod' ? 'Tiền mặt (COD)' : 'Thanh toán trực tuyến'; ?></p>
    <p><strong>Trạng thái thanh toán:</strong> <?php echo $thanh_toan_status === 'success' ? 'Thành công' : 'Chưa xử lý'; ?></p>
    <p><a href="/LaptopStore/Store/index.php">Tiếp tục mua sắm</a></p>
</div>
</body>
</html>