<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

ob_start(); // Ngăn đầu ra không mong muốn
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

$matk = $_SESSION['user_id'];
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';
$mactsp = isset($_POST['mactsp']) ? trim($_POST['mactsp']) : '';
$new_quantity = isset($_POST['new_quantity']) ? (int)$_POST['new_quantity'] : 0;

$response = ['success' => false, 'error' => ''];

if (empty($madh) || empty($mactsp) || $new_quantity < 1) {
    $response['error'] = 'Dữ liệu không hợp lệ.';
    echo json_encode($response);
    exit;
}

// Kiểm tra đơn hàng
$stmt = $conn->prepare("SELECT TONGTIEN FROM DONHANG WHERE MADH = ? AND MATK = ? AND TRANGTHAI = 0");
$stmt->bind_param("ss", $madh, $matk);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $response['error'] = 'Đơn hàng không hợp lệ.';
    echo json_encode($response);
    $stmt->close();
    exit;
}
$stmt->close();

// Kiểm tra tồn kho
$stmt = $conn->prepare("SELECT SOLUONG, DONGIA FROM CT_DONHANG WHERE MADH = ? AND MACTSP = ?");
$stmt->bind_param("ss", $madh, $mactsp);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    $response['error'] = 'Sản phẩm không tồn tại trong giỏ hàng.';
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("SELECT SOLUONG FROM CT_SANPHAM WHERE MACTSP = ?");
$stmt->bind_param("s", $mactsp);
$stmt->execute();
$inventory = $stmt->get_result()->fetch_assoc()['SOLUONG'];
$stmt->close();

if ($new_quantity > $inventory) {
    $response['error'] = "Số lượng yêu cầu ($new_quantity) vượt quá tồn kho ($inventory).";
    echo json_encode($response);
    exit;
}

$dongia = $item['DONGIA'];
$thanhtien = $dongia * $new_quantity;

// Bắt đầu transaction
$conn->begin_transaction();
try {
    // Cập nhật số lượng và thành tiền
    $stmt = $conn->prepare("UPDATE CT_DONHANG SET SOLUONG = ?, THANHTIEN = ? WHERE MADH = ? AND MACTSP = ?");
    $stmt->bind_param("idss", $new_quantity, $thanhtien, $madh, $mactsp);
    $stmt->execute();
    $stmt->close();

    // Cập nhật tổng tiền đơn hàng
    $stmt = $conn->prepare("UPDATE DONHANG SET TONGTIEN = (SELECT SUM(THANHTIEN) FROM CT_DONHANG WHERE MADH = ?) WHERE MADH = ?");
    $stmt->bind_param("ss", $madh, $madh);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $response['success'] = true;
} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = 'Lỗi cập nhật giỏ hàng: ' . $e->getMessage();
    error_log("update_cart.php - Error: " . $e->getMessage());
}

ob_end_clean(); // Xóa bộ đệm đầu ra
echo json_encode($response);
$conn->close();
?>