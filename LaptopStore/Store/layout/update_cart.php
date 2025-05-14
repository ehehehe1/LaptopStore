<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// Ngăn đầu ra không mong muốn
if (ob_get_length()) {
    error_log("update_cart.php - Buffer content: " . bin2hex(ob_get_contents()));
    ob_end_clean();
}

header('Content-Type: application/json');

$matk = $_SESSION['user_id'];
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';
$mactsp = isset($_POST['mactsp']) ? trim($_POST['mactsp']) : '';
$new_quantity = isset($_POST['new_quantity']) ? (int)$_POST['new_quantity'] : 0;

error_log("update_cart.php - POST data: " . print_r($_POST, true)); // Debug POST
error_log("update_cart.php - MATK: " . $matk);

$response = ['success' => false, 'error' => ''];

if (empty($madh) || empty($mactsp) || $new_quantity < 1) {
    $response['error'] = 'Dữ liệu không hợp lệ.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra đơn hàng
$stmt = $conn->prepare("SELECT TONGTIEN FROM DONHANG WHERE MADH = ? AND MATK = ? AND TRANGTHAI = -1");
if (!$stmt) {
    $response['error'] = 'Lỗi truy vấn cơ sở dữ liệu: ' . $conn->error;
    error_log("update_cart.php - SQL prepare error: " . $conn->error);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param("ss", $madh, $matk);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $response['error'] = 'Đơn hàng không hợp lệ hoặc không thuộc về bạn.';
    error_log("update_cart.php - Order not found: MADH=$madh, MATK=$matk");
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}
$stmt->close();

// Kiểm tra sản phẩm trong giỏ
$stmt = $conn->prepare("SELECT SOLUONG, DONGIA FROM CT_DONHANG WHERE MADH = ? AND MACTSP = ?");
if (!$stmt) {
    $response['error'] = 'Lỗi truy vấn cơ sở dữ liệu: ' . $conn->error;
    error_log("update_cart.php - SQL prepare error: " . $conn->error);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param("ss", $madh, $mactsp);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    $response['error'] = 'Sản phẩm không tồn tại trong giỏ hàng.';
    error_log("update_cart.php - Item not found: MADH=$madh, MACTSP=$mactsp");
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$current_quantity = $item['SOLUONG'];
$quantity_diff = $new_quantity - $current_quantity; // Số lượng thay đổi

// Kiểm tra tồn kho với khóa
$conn->begin_transaction();
try {
    // Lấy MASP và kiểm tra tồn kho từ CT_SANPHAM
    $stmt = $conn->prepare("SELECT MASP, SOLUONG FROM CT_SANPHAM WHERE MACTSP = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception('Lỗi truy vấn tồn kho: ' . $conn->error);
    }
    $stmt->bind_param("s", $mactsp);
    $stmt->execute();
    $ct_sanpham = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$ct_sanpham) {
        throw new Exception('Chi tiết sản phẩm không tồn tại.');
    }
    $masp = $ct_sanpham['MASP'];
    $inventory = $ct_sanpham['SOLUONG'];

    if ($quantity_diff > 0 && $quantity_diff > $inventory) {
        throw new Exception("Số lượng yêu cầu ($new_quantity) vượt quá tồn kho ($inventory).");
    }

    // Khóa bản ghi SANPHAM
    $stmt = $conn->prepare("SELECT SOLUONG FROM SANPHAM WHERE MASP = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception('Lỗi truy vấn sản phẩm: ' . $conn->error);
    }
    $stmt->bind_param("s", $masp);
    $stmt->execute();
    $sanpham = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$sanpham) {
        throw new Exception("Sản phẩm với MASP $masp không tồn tại trong kho.");
    }
    $sanpham_inventory = $sanpham['SOLUONG'];

    if ($quantity_diff > 0 && $quantity_diff > $sanpham_inventory) {
        throw new Exception("Số lượng yêu cầu ($new_quantity) vượt quá tồn kho sản phẩm ($sanpham_inventory).");
    }

    // Cập nhật số lượng và thành tiền trong CT_DONHANG
    $dongia = $item['DONGIA'];
    $thanhtien = $dongia * $new_quantity;
    $stmt = $conn->prepare("UPDATE CT_DONHANG SET SOLUONG = ?, THANHTIEN = ? WHERE MADH = ? AND MACTSP = ?");
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị cập nhật giỏ hàng: ' . $conn->error);
    }
    $stmt->bind_param("idss", $new_quantity, $thanhtien, $madh, $mactsp);
    if (!$stmt->execute()) {
        throw new Exception('Lỗi cập nhật giỏ hàng: ' . $stmt->error);
    }
    $stmt->close();

    // Cập nhật tồn kho CT_SANPHAM
    $new_inventory = $inventory - $quantity_diff;
    $stmt = $conn->prepare("UPDATE CT_SANPHAM SET SOLUONG = ? WHERE MACTSP = ?");
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị cập nhật tồn kho CT_SANPHAM: ' . $conn->error);
    }
    $stmt->bind_param("is", $new_inventory, $mactsp);
    if (!$stmt->execute()) {
        throw new Exception('Lỗi cập nhật tồn kho CT_SANPHAM: ' . $stmt->error);
    }
    $stmt->close();

    // Cập nhật tồn kho SANPHAM
    $new_sanpham_inventory = $sanpham_inventory - $quantity_diff;
    $stmt = $conn->prepare("UPDATE SANPHAM SET SOLUONG = ? WHERE MASP = ?");
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị cập nhật tồn kho SANPHAM: ' . $conn->error);
    }
    $stmt->bind_param("is", $new_sanpham_inventory, $masp);
    if (!$stmt->execute()) {
        throw new Exception('Lỗi cập nhật tồn kho SANPHAM: ' . $stmt->error);
    }
    $stmt->close();

    // Cập nhật tổng tiền đơn hàng
    $stmt = $conn->prepare("UPDATE DONHANG SET TONGTIEN = (SELECT SUM(THANHTIEN) FROM CT_DONHANG WHERE MADH = ?) WHERE MADH = ?");
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị cập nhật tổng tiền: ' . $conn->error);
    }
    $stmt->bind_param("ss", $madh, $madh);
    if (!$stmt->execute()) {
        throw new Exception('Lỗi cập nhật tổng tiền: ' . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    $response['success'] = true;
} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
    error_log("update_cart.php - Error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conn->close();
?>