<?php
require '../../../includes/connect.php';

function generateId($prefix, $table, $column, $conn)
{
    $stmt = $conn->query("SELECT MAX($column) AS max_id FROM $table");
    $max = $stmt->fetch()['max_id'];
    $number = intval(substr($max, strlen($prefix))) + 1;
    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

function safe_filename($filename)
{
    $filename = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
    $filename = strtolower($filename);
    $filename = preg_replace('/[^a-z0-9_\.\-]/', '_', $filename);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    return $name . '_' . time() . '.' . $ext;
}

try {
    // Tạo MASP
    $masp = generateId('SP', 'sanpham', 'MASP', $conn);

    // Lấy dữ liệu từ form
    $tensp = $_POST['tensp'];
    $thuonghieu = $_POST['thuonghieu'];
    $maloai = $_POST['maloai'];

    // Xử lý ảnh
    $img_name = safe_filename($_FILES['img']['name']);
    $img_tmp = $_FILES['img']['tmp_name'];
    $img_path = "../../../../Store/assets/img/product/" . $img_name;
    move_uploaded_file($img_tmp, $img_path);

    // Thêm vào bảng sanpham
    $stmt1 = $conn->prepare("INSERT INTO sanpham (MASP, TENSP, IMG, THUONGHIEU, MALOAI, SOLUONG, TRANGTHAI)
                                VALUES (?, ?, ?, ?, ?, 0, 1)");
    $stmt1->execute([$masp, $tensp, $img_name, $thuonghieu, $maloai]);

    // Tạo biến thể sản phẩm
    $mactsp = generateId('CTSP', 'ct_sanpham', 'MACTSP', $conn);
    $mau = $_POST['mau'];
    $size = $_POST['size'];
    $gianhap = $_POST['gianhap'];
    $giaban = $_POST['giaban'];
    $thongso = $_POST['thongso'];
    $soluong = $_POST['soluong'];

    $stmt2 = $conn->prepare("INSERT INTO ct_sanpham (MACTSP, MASP, MAU, SIZE, GIANHAP, GIABAN, THONGSO, SOLUONG, TRANGTHAI)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt2->execute([$mactsp, $masp, $mau, $size, $gianhap, $giaban, $thongso, $soluong]);

    echo json_encode(["success" => true, "message" => "Thêm sản phẩm thành công!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
}
