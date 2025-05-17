<?php
require '../../../includes/connect.php';

try {
    $masp = $_POST['masp'];
    $tensp = $_POST['tensp'];
    $thuonghieu = $_POST['thuonghieu'];
    $maloai = $_POST['maloai'];
    $mau = $_POST['mau'];
    $size = $_POST['size'];
    $gianhap = $_POST['gianhap'];
    $giaban = $_POST['giaban'];
    $soluong = $_POST['soluong'];
    $thongso = $_POST['thongso'];

    // Nếu có ảnh mới được chọn
    if (isset($_FILES['img']) && $_FILES['img']['name'] != '') {
        $img_name = $_FILES['img']['name'];
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $new_img = pathinfo($img_name, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['img']['tmp_name'], "../../../../Store/assets/img/product/" . $new_img);

        // Cập nhật bảng sanpham (có ảnh)
        $stmt = $conn->prepare("UPDATE sanpham SET TENSP = ?, THUONGHIEU = ?, MALOAI = ?, IMG = ? WHERE MASP = ?");
        $stmt->execute([$tensp, $thuonghieu, $maloai, $new_img, $masp]);
    } else {
        // Cập nhật bảng sanpham (không đổi ảnh)
        $stmt = $conn->prepare("UPDATE sanpham SET TENSP = ?, THUONGHIEU = ?, MALOAI = ? WHERE MASP = ?");
        $stmt->execute([$tensp, $thuonghieu, $maloai, $masp]);
    }

    // Cập nhật bảng ct_sanpham
    $stmt2 = $conn->prepare("UPDATE ct_sanpham SET MAU = ?, SIZE = ?, GIANHAP = ?, GIABAN = ?, SOLUONG = ?, THONGSO = ? WHERE MASP = ?");
    $stmt2->execute([$mau, $size, $gianhap, $giaban, $soluong, $thongso, $masp]);

    echo json_encode(["success" => true, "message" => "✔️ Cập nhật sản phẩm thành công!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "❌ Lỗi: " . $e->getMessage()]);
}
