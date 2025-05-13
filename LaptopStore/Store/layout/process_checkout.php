<?php
session_start();
require 'db.php';
ob_start();
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

$matk = $_SESSION['user_id'];
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';
$diachi = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
$phuongthuc = isset($_POST['phuongthuc']) ? trim($_POST['phuongthuc']) : '';

$response = ['success' => false, 'error' => '', 'order' => []];

$errors = [];
if (empty($madh)) $errors[] = "Mã đơn hàng không hợp lệ.";
if (empty($diachi)) $errors[] = "Địa chỉ giao hàng không được để trống.";
if (!in_array($phuongthuc, ['cod', 'online'])) $errors[] = "Phương thức thanh toán không hợp lệ.";

if (!empty($errors)) {
    $response['error'] = implode("; ", $errors);
    echo json_encode($response);
    exit;
}

// Kiểm tra đơn hàng thuộc về người dùng và TRANGTHAI = 0
$stmt = $conn->prepare("SELECT TONGTIEN FROM DONHANG WHERE MADH = ? AND MATK = ? AND TRANGTHAI = 0");
$stmt->bind_param("ss", $madh, $matk);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $response['error'] = "Đơn hàng không hợp lệ hoặc không thuộc về bạn.";
    echo json_encode($response);
    exit;
}

// Lấy chi tiết đơn hàng
$stmt = $conn->prepare("SELECT ct.SOLUONG, ct.DONGIA, ct.THANHTIEN, s.TENSP, ctsp.THONGSO, ctsp.MAU, ctsp.SIZE 
                        FROM CT_DONHANG ct 
                        JOIN CT_SANPHAM ctsp ON ct.MACTSP = ctsp.MACTSP 
                        JOIN SANPHAM s ON ctsp.MASP = s.MASP 
                        WHERE ct.MADH = ?");
$stmt->bind_param("s", $madh);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Bắt đầu transaction
$conn->begin_transaction();
try {
    // Cập nhật đơn hàng
    $stmt = $conn->prepare("UPDATE DONHANG SET DIACHI = ?, PHUONGTHUC = ?, TRANGTHAI = 1 WHERE MADH = ? AND MATK = ?");
    $stmt->bind_param("ssss", $diachi, $phuongthuc, $madh, $matk);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật đơn hàng: " . $stmt->error);
    }
    $stmt->close();

    // Lưu phương thức thanh toán (giả lập)
    $_SESSION['phuongthuc_thanh_toan'] = $phuongthuc;

    // Giả lập thanh toán trực tuyến
    if ($phuongthuc === 'online') {
        $_SESSION['thanh_toan_status'] = 'success';
    }

    // Xóa giỏ hàng trong session
    unset($_SESSION['cart']);

    // Commit transaction
    $conn->commit();

    // Chuẩn bị dữ liệu xác nhận
    $response['success'] = true;
    $response['order'] = [
        'madh' => $madh,
        'tongtien' => $order['TONGTIEN'],
        'diachi' => $diachi,
        'phuongthuc' => $phuongthuc,
        'items' => array_map(function($item) {
            return [
                'tensp' => htmlspecialchars($item['TENSP']),
                'thongso' => htmlspecialchars($item['THONGSO']),
                'mau' => htmlspecialchars($item['MAU']),
                'size' => htmlspecialchars($item['SIZE']),
                'soluong' => (int)$item['SOLUONG'],
                'dongia' => (int)$item['DONGIA'],
                'thanhtien' => (int)$item['THANHTIEN']
            ];
        }, $items)
    ];

} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
ob_end_clean();
$conn->close();
?>