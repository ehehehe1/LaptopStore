<?php
require '../../../includes/connect.php';

$masp = $_POST['masp'] ?? '';
if (!$masp) {
    echo json_encode(["success" => false, "message" => "Thiếu mã sản phẩm"]);
    exit;
}

// lấy sản phẩm
$stmt = $conn->prepare("SELECT * FROM sanpham WHERE MASP = ?");
$stmt->execute([$masp]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $conn->prepare("SELECT * FROM ct_sanpham WHERE MASP = ? LIMIT 1");
$stmt2->execute([$masp]);
$detail = $stmt2->fetch(PDO::FETCH_ASSOC);

if ($product && $detail) {
    echo json_encode(["success" => true, "product" => $product, "detail" => $detail]);
} else {
    echo json_encode(["success" => false, "message" => "Không tìm thấy sản phẩm"]);
}
