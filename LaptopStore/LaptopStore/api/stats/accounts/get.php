<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
$path = "../../../includes/connect.php";

if (!file_exists($path)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy tệp connect.php']);
    exit;
}

include $path;

if (!isset($conn)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

if (isset($_GET['matk'])) {
    $matk = $_GET['matk'];
    try {
        $stmt = $conn->prepare("SELECT MATK, TENDANGNHAP, HOTEN, EMAIL, SDT, DIACHI, MACV, TRANGTHAI 
                               FROM taikhoan WHERE MATK = ?");
        $stmt->execute([$matk]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            ob_end_clean();
            echo json_encode(['success' => true, 'account' => $account]);
        } else {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại']);
        }
    } catch (PDOException $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $e->getMessage()]);
    }
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Thiếu mã tài khoản']);
}
?>