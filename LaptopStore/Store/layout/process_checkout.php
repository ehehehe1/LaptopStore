<?php
session_start();
require_once 'db.php';

// Đảm bảo không có đầu ra trước JSON
if (ob_get_length()) {
    error_log("Unexpected output buffer: " . ob_get_contents());
    ob_end_clean();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Vui lòng đăng nhập để xác nhận đơn hàng']);
    exit;
}

$matk = $_SESSION['user_id'];
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';
$diachi = isset($_POST['diachi']) ? trim($_POST['diachi']) : '';
$phuongthuc = isset($_POST['phuongthuc']) ? trim($_POST['phuongthuc']) : '';

error_log("POST data: " . print_r($_POST, true)); // Debug POST data

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
$stmt = $conn->prepare("SELECT TONGTIEN FROM DONHANG WHERE MADH = ? AND MATK = ? AND TRANGTHAI = -1");
if (!$stmt) {
    $response['error'] = "Lỗi truy vấn cơ sở dữ liệu: " . $conn->error;
    error_log("SQL prepare error: " . $conn->error);
    echo json_encode($response);
    exit;
}
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
if (!$stmt) {
    $response['error'] = "Lỗi truy vấn cơ sở dữ liệu: " . $conn->error;
    error_log("SQL prepare error: " . $conn->error);
    echo json_encode($response);
    exit;
}
$stmt->bind_param("s", $madh);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Bắt đầu transaction
$conn->begin_transaction();
try {
    // Cập nhật đơn hàng
    $stmt = $conn->prepare("UPDATE DONHANG SET DIACHI = ?, PHUONGTHUC = ?, TRANGTHAI = 0 WHERE MADH = ? AND MATK = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị cập nhật đơn hàng: " . $conn->error);
    }
    $stmt->bind_param("ssss", $diachi, $phuongthuc, $madh, $matk);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật đơn hàng: " . $stmt->error);
    }
    $stmt->close();

    // Giả lập thanh toán trực tuyến
    if ($phuongthuc === 'online') {
        $_SESSION['thanh_toan_status'] = 'success';
    }

    // Commit transaction
    $conn->commit();

    // Chuẩn bị dữ liệu xác nhận
    $response['success'] = true;
    $response['order'] = [
        'madh' => $madh,
        'tongtien' => (int)$order['TONGTIEN'],
        'diachi' => htmlspecialchars($diachi),
        'phuongthuc' => htmlspecialchars($phuongthuc),
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
    error_log("Transaction error: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

$json_response = json_encode($response);
error_log("JSON length: " . strlen($json_response)); // Debug JSON length
error_log("Response: " . $json_response); // Debug response
echo $json_response;
$conn->close();
?>