<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
session_start();

$path = "../../includes/connect.php";

if (!file_exists($path)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy tệp connect.php']);
    exit;
}

require_once $path;

if (!isset($conn)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$tendangnhap = $_POST['tendangnhap'] ?? '';
$matkhau = $_POST['matkhau'] ?? '';

if (empty($tendangnhap) || empty($matkhau)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên đăng nhập và mật khẩu']);
    exit;
}

try {
    // Kiểm tra tài khoản
    $stmt = $conn->prepare("SELECT MATK, TENDANGNHAP, HOTEN, MATKHAU, MACV, TRANGTHAI 
                           FROM taikhoan 
                           WHERE TENDANGNHAP = ?");
    $stmt->execute([$tendangnhap]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập không tồn tại']);
        exit;
    }

    // Kiểm tra mật khẩu
    if (!password_verify($matkhau, $account['MATKHAU'])) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Mật khẩu không đúng']);
        exit;
    }

    // Kiểm tra trạng thái tài khoản
    if ($account['TRANGTHAI'] == 0) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Tài khoản đã bị khóa']);
        exit;
    }

    // Kiểm tra vai trò hợp lệ
    $allowed_roles = ['CV001', 'CV002', 'CV003']; // Admin, Nhân viên bán hàng, Nhân viên kho
    if (!in_array($account['MACV'], $allowed_roles)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập khu vực admin']);
        exit;
    }

    // Lưu thông tin vào session
    $_SESSION['admin_id'] = $account['MATK'];
    $_SESSION['admin_name'] = $account['HOTEN'];
    $_SESSION['admin_role'] = $account['MACV'];

    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công']);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
exit;
?>