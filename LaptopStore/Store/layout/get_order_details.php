<?php
session_start();
require 'db.php';

$matk = $_SESSION['user_id'];
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';

$response = ['success' => false, 'details' => []];

if (!empty($madh)) {
    $stmt = $conn->prepare("SELECT ct.MACTSP, ct.SOLUONG, ct.DONGIA, ct.THANHTIEN, s.TENSP, ctsp.THONGSO, ctsp.MAU, ctsp.SIZE 
                            FROM CT_DONHANG ct 
                            JOIN CT_SANPHAM ctsp ON ct.MACTSP = ctsp.MACTSP 
                            JOIN SANPHAM s ON ctsp.MASP = s.MASP 
                            WHERE ct.MADH = ? AND EXISTS (
                                SELECT 1 FROM DONHANG d WHERE d.MADH = ct.MADH AND d.MATK = ?
                            )");
    $stmt->bind_param("ss", $madh, $matk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $response['details'][] = [
            'tensp' => htmlspecialchars($row['TENSP']),
            'thongso' => htmlspecialchars($row['THONGSO']),
            'mau' => htmlspecialchars($row['MAU']),
            'size' => htmlspecialchars($row['SIZE']),
            'soluong' => (int)$row['SOLUONG'],
            'dongia' => (int)$row['DONGIA'],
            'thanhtien' => (int)$row['THANHTIEN']
        ];
    }
    
    $response['success'] = true;
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>