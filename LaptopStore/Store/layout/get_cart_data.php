<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

ob_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Vui lòng đăng nhập.", "cart_empty" => true]);
    exit;
}

$matk = $_SESSION['user_id'];
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';

if (empty($madh)) {
    echo json_encode(["success" => false, "error" => "Mã đơn hàng không hợp lệ.", "cart_empty" => true]);
    exit;
}

// Kiểm tra đơn hàng
$stmt = $conn->prepare("SELECT TONGTIEN FROM DONHANG WHERE MADH = ? AND MATK = ? AND TRANGTHAI = -1");
$stmt->bind_param("ss", $madh, $matk);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Giỏ hàng không tồn tại hoặc đã được thanh toán.", "cart_empty" => true]);
    $stmt->close();
    exit;
}
$order = $result->fetch_assoc();
$stmt->close();

// Lấy dữ liệu giỏ hàng
$stmt = $conn->prepare("SELECT d.MADH, d.TONGTIEN, ct.MACTSP, ct.SOLUONG, ct.DONGIA, ct.THANHTIEN, s.TENSP, ctsp.THONGSO, ctsp.MAU, ctsp.SIZE 
                        FROM DONHANG d 
                        JOIN CT_DONHANG ct ON d.MADH = ct.MADH 
                        JOIN CT_SANPHAM ctsp ON ct.MACTSP = ctsp.MACTSP 
                        JOIN SANPHAM s ON ctsp.MASP = s.MASP 
                        WHERE d.MATK = ? AND d.TRANGTHAI = -1 AND d.MADH = ?");
$stmt->bind_param("ss", $matk, $madh);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = [
        'MACTSP' => $row['MACTSP'],
        'SOLUONG' => (int)$row['SOLUONG'],
        'DONGIA' => (int)$row['DONGIA'],
        'THANHTIEN' => (int)$row['THANHTIEN'],
        'TENSP' => htmlspecialchars($row['TENSP']),
        'THONGSO' => htmlspecialchars($row['THONGSO']),
        'MAU' => htmlspecialchars($row['MAU']),
        'SIZE' => htmlspecialchars($row['SIZE'])
    ];
    $total = (int)$row['TONGTIEN'];
}
$stmt->close();

ob_end_clean();
echo json_encode([
    "success" => true,
    "cart_items" => $cart_items,
    "total" => $total,
    "cart_empty" => empty($cart_items)
]);
$conn->close();
?>