<?php
require 'db.php';

header('Content-Type: application/json');

$price = isset($_POST['price']) ? trim($_POST['price']) : '';
$size = isset($_POST['size']) ? trim($_POST['size']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : '';
$query = isset($_POST['query']) ? trim($_POST['query']) : '';

try {
    $sql = "SELECT s.MASP as masp, s.TENSP as tensp, s.IMG as hinhanh, MIN(ct.GIABAN) as gia
            FROM SANPHAM s
            JOIN CT_SANPHAM ct ON s.MASP = ct.MASP
            WHERE s.TRANGTHAI = 1 AND ct.SOLUONG > 0 AND ct.TRANGTHAI = 1";
    
    $params = [];
    $types = '';

    if (!empty($query)) {
        $sql .= " AND s.TENSP LIKE ?";
        $params[] = "%$query%";
        $types .= 's';
    }

    if (!empty($price)) {
        list($min_price, $max_price) = explode('-', $price);
        $sql .= " AND ct.GIABAN BETWEEN ? AND ?";
        $params[] = (int)$min_price;
        $params[] = (int)$max_price;
        $types .= 'ii';
    }

    if (!empty($size)) {
        $sql .= " AND ct.SIZE = ?";
        $params[] = $size;
        $types .= 's';
    }

    if (!empty($type)) {
        $sql .= " AND s.MALOAI = ?";
        $params[] = $type;
        $types .= 's';
    }

    $sql .= " GROUP BY s.MASP";
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
} catch (Exception $e) {
    error_log("Error in filter_price.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>