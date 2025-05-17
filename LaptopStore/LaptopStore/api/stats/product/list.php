<?php
require '../../../includes/connect.php';

$search = $_GET['search'] ?? '';
$brand = $_GET['brand'] ?? '';
$category = $_GET['category'] ?? '';

// Base SQL
$sql = "
    SELECT sp.MASP, sp.TENSP, sp.THUONGHIEU, lsp.TENLOAI
    FROM sanpham sp
    JOIN loaisp lsp ON sp.MALOAI = lsp.MALOAI
    WHERE 1
";

$params = [];

if ($search !== '') {
    $sql .= " AND sp.TENSP LIKE ?";
    $params[] = "%$search%";
}
if ($brand !== '') {
    $sql .= " AND sp.THUONGHIEU = ?";
    $params[] = $brand;
}
if ($category !== '') {
    $sql .= " AND sp.MALOAI = ?";
    $params[] = $category;
}

$sql .= " ORDER BY sp.MASP DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
