<?php
session_start();
require '../includes/connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success'=>false, 'error'=>'Chưa đăng nhập admin']);
    exit;
}

$action = $_POST['action'] ?? '';
switch ($action) {
    case 'add':
        // Validate và thêm user mới
        // ... (giống register_ajax.php, nhưng cho phép admin chọn trạng thái, quyền)
        break;
    case 'edit':
        // Sửa thông tin user
        // Nhận MATK, các trường cần sửa, validate và update
        break;
    case 'toggle_status':
        // Khoá/mở user
        $matk = $_POST['matk'] ?? '';
        $status = $_POST['status'] ?? '';
        if ($matk === '' || !in_array($status, ['0','1'])) {
            echo json_encode(['success'=>false, 'error'=>'Dữ liệu không hợp lệ']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE TAIKHOAN SET TRANGTHAI=? WHERE MATK=?");
        $stmt->bind_param("ss", $status, $matk);
        $stmt->execute();
        echo json_encode(['success'=>true]);
        break;
    case 'list':
        // Lấy danh sách user
        $result = $conn->query("SELECT MATK, TENDANGNHAP, HOTEN, EMAIL, SDT, DIACHI, TRANGTHAI, MACV FROM TAIKHOAN");
        $users = [];
        while ($row = $result->fetch_assoc()) $users[] = $row;
        echo json_encode(['success'=>true, 'users'=>$users]);
        break;
    default:
        echo json_encode(['success'=>false, 'error'=>'Hành động không hợp lệ']);
}
