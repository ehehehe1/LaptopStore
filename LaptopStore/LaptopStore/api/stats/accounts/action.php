<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
$path = "../../../includes/connect.php";

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

$action = $_POST['action'] ?? '';

if ($action === 'add_account') {
    $tendangnhap = $_POST['tendangnhap'] ?? '';
    $hoten = $_POST['hoten'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $email = $_POST['email'] ?? '';
    $matkhau = password_hash($_POST['matkhau'] ?? '', PASSWORD_DEFAULT);
    $macv = $_POST['macv'] ?? '';
    $diachi = $_POST['diachi'] ?? '';

    try {
        // Kiểm tra email hoặc số điện thoại đã tồn tại
        $stmt = $conn->prepare("SELECT 1 FROM taikhoan WHERE EMAIL = ? OR SDT = ?");
        $stmt->execute([$email, $sdt]);
        if ($stmt->fetch()) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Email hoặc số điện thoại đã tồn tại']);
            exit;
        }

        // Tạo MATK
        $sql = "SELECT COUNT(*) AS total FROM TAIKHOAN";
        $result = $conn->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $totalAccounts = $row['total'] + 1;
        $matk = "TK" . str_pad($totalAccounts, 3, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO taikhoan (MATK, TENDANGNHAP,HOTEN, DIACHI, SDT, EMAIL, MATKHAU, MACV, TRANGTHAI) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$matk, $tendangnhap,$hoten, $diachi, $sdt, $email, $matkhau, $macv]);
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Thêm tài khoản thành công']);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} elseif ($action === 'edit_account') {
    $matk = $_POST['matk'] ?? '';
    $hoten = $_POST['hoten'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $email = $_POST['email'] ?? '';
    $macv = $_POST['macv'] ?? '';
    $diachi = $_POST['diachi'] ?? '';
    $trangthai = isset($_POST['trangthai']) ? (int)$_POST['trangthai'] : 1;

    // Debug: Ghi log dữ liệu nhận được
    error_log("Edit account: matk=$matk, trangthai=$trangthai");

    try {
        // Kiểm tra email hoặc số điện thoại đã tồn tại (trừ tài khoản hiện tại)
        $stmt = $conn->prepare("SELECT 1 FROM taikhoan WHERE (EMAIL = ? OR SDT = ?) AND MATK != ?");
        $stmt->execute([$email, $sdt, $matk]);
        if ($stmt->fetch()) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Email hoặc số điện thoại đã được sử dụng']);
            exit;
        }

        // Kiểm tra trạng thái hợp lệ
        if (!in_array($trangthai, [0, 1])) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            exit;
        }

        $sql = "UPDATE taikhoan SET HOTEN = ?, DIACHI = ?, SDT = ?, EMAIL = ?, MACV = ?, TRANGTHAI = ? WHERE MATK = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$hoten, $diachi, $sdt, $email, $macv, $trangthai, $matk]);
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Cập nhật tài khoản thành công']);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
}
?>