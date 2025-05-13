<?php
require_once '../../includes/connect.php';

$from = $_GET['from'] ?? '2000-01-01';
$to = $_GET['to'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

$sql = "SELECT 
            sp.TENSP, 
            SUM(ct.SOLUONG) AS quantity,
            SUM(ct.SOLUONG * ct.DONGIA) AS revenue
        FROM sanpham sp
        JOIN chitietdonhang ct ON sp.MASP = ct.MASP
        JOIN donhang dh ON ct.MADH = dh.MADH
        WHERE dh.NGAYDAT BETWEEN :from AND :to 
        AND sp.TENSP LIKE :search
        GROUP BY sp.TENSP
        ORDER BY quantity DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':from' => $from,
    ':to' => $to,
    ':search' => "%$search%"
]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
