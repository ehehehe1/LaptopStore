<?php
session_start();
require '../includes/connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success'=>false, 'error'=>'Chưa đăng nhập admin']);
    exit;
}

$action = $_POST['action'] ?? '';
switch ($action) {
    case 'list':
        $result = $conn->query("SELECT PN.MAPN, PN.NGAYNHAP, NV.HOTEN as TENNV, PN.TONGTIEN 
                                FROM PHIEUNHAP PN JOIN NHANVIEN NV ON PN.MANV = NV.MANV 
                                ORDER BY PN.NGAYNHAP DESC");
        $phieu = [];
        while ($row = $result->fetch_assoc()) $phieu[] = $row;
        echo json_encode(['success'=>true, 'phieu'=>$phieu]);
        break;
    case 'detail':
        $mapn = $_POST['mapn'] ?? '';
        $stmt = $conn->prepare("SELECT CT.MASP, SP.TENSP, CT.SOLUONG, CT.DONGIA 
                                FROM CHITIET_PHIEUNHAP CT JOIN SANPHAM SP ON CT.MASP = SP.MASP 
                                WHERE CT.MAPN = ?");
        $stmt->bind_param("s", $mapn);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = [];
        while ($row = $result->fetch_assoc()) $details[] = $row;
        echo json_encode(['success'=>true, 'details'=>$details]);
        break;
    default:
        echo json_encode(['success'=>false, 'error'=>'Hành động không hợp lệ']);
}
