<?php
require_once '../../includes/connect.php';

$from = $_GET['from'] ?? '2000-01-01';
$to = $_GET['to'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

$sql = "SELECT 
            kh.HOTEN AS customer_name,
            COUNT(dh.MADH) AS order_count,
            SUM(ct.SOLUONG * ct.DONGIA) AS total_revenue
        FROM khachhang kh
        JOIN donhang dh ON kh.MAKH = dh.MAKH
        JOIN chitietdonhang ct ON dh.MADH = ct.MADH
        WHERE dh.NGAYDAT BETWEEN :from AND :to 
        AND kh.HOTEN LIKE :search
        GROUP BY kh.HOTEN
        ORDER BY total_revenue DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':from' => $from,
    ':to' => $to,
    ':search' => "%$search%"
]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
