<?php
session_start();
require 'db.php';

header('Content-Type: application/json');



// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Vui lòng đăng nhập để thêm vào giỏ hàng."]);
    exit;
}

// Lấy dữ liệu từ form
$masp = isset($_POST['masp']) ? trim($_POST['masp']) : '';
$spec = isset($_POST['spec']) ? trim($_POST['spec']) : '';
$color = isset($_POST['color']) ? trim($_POST['color']) : '';
$size = isset($_POST['size']) ? trim($_POST['size']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;


// Bắt đầu transaction
$conn->begin_transaction();

try {
    // Kiểm tra sản phẩm trong CT_SANPHAM
    $stmt = $conn->prepare("SELECT ct.MACTSP, ct.GIABAN, ct.SOLUONG, s.TENSP 
                            FROM CT_SANPHAM ct 
                            JOIN SANPHAM s ON ct.MASP = s.MASP 
                            WHERE ct.MASP = ? AND ct.THONGSO = ? AND ct.MAU = ? AND ct.SIZE = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn: " . $conn->error);
    }
    $stmt->bind_param("ssss", $masp, $spec, $color, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Sản phẩm không tồn tại.");
    }

    $product = $result->fetch_assoc();
    $mactsp = $product['MACTSP'];
    $stmt->close();

    if ($product['SOLUONG'] < $quantity) {
        throw new Exception("Số lượng tồn kho không đủ. Còn lại: " . $product['SOLUONG']);
    }

    // Trừ số lượng trong CT_SANPHAM
    $stmt = $conn->prepare("UPDATE CT_SANPHAM SET SOLUONG = SOLUONG - ? WHERE MACTSP = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị cập nhật CT_SANPHAM: " . $conn->error);
    }
    $stmt->bind_param("is", $quantity, $mactsp);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật CT_SANPHAM: " . $stmt->error);
    }
    $stmt->close();

    // Trừ số lượng trong SANPHAM
    $stmt = $conn->prepare("UPDATE SANPHAM SET SOLUONG = SOLUONG - ? WHERE MASP = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị cập nhật SANPHAM: " . $conn->error);
    }
    $stmt->bind_param("is", $quantity, $masp);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật SANPHAM: " . $stmt->error);
    }
    $stmt->close();

    // Kiểm tra xem người dùng đã có đơn hàng trong giỏ hàng (TRANGTHAI = -1) chưa
    $matk = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT MADH FROM DONHANG WHERE MATK = ? AND TRANGTHAI = -1");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị truy vấn DONHANG: " . $conn->error);
    }
    $stmt->bind_param("s", $matk);
    $stmt->execute();
    $result = $stmt->get_result();
    $madh = null;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $madh = $row['MADH'];
    } else {
        // Tạo đơn hàng mới
         $stmt = $conn->prepare("SELECT COUNT(*) AS order_count FROM DONHANG");
        $stmt->execute();
        $result = $stmt->get_result();
        $orderCount = $result->fetch_assoc()['order_count'] + 1; // Tăng để lấy STT tiếp theo
        $stmt->close();

        $madh = sprintf("DH%04d", $orderCount); // Ví dụ: DH0001, DH0002

      
        $ngaydh = date('Y-m-d H:i:s');
        $tongtien = 0;
        $diachi = '';
        $trangthai = -1;

        $stmt = $conn->prepare("INSERT INTO DONHANG (MADH, MATK, TONGTIEN, NGAYDH, DIACHI, TRANGTHAI) VALUES (?, ?, ?, NOW(), ?, ?)");
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị thêm DONHANG: " . $conn->error);
        }
        $stmt->bind_param("ssdsi", $madh, $matk, $tongtien, $diachi, $trangthai);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi thêm DONHANG: " . $stmt->error);
        }
    }
    $stmt->close();

    // Thêm hoặc cập nhật chi tiết đơn hàng trong CT_DONHANG
    $dongia = $product['GIABAN'];
    $thanhtien = $dongia * $quantity;

    error_log("CT_DONHANG: madh=$madh, mactsp=$mactsp, soluong=$quantity, dongia=$dongia, thanhtien=$thanhtien");

    $stmt = $conn->prepare("SELECT SOLUONG FROM CT_DONHANG WHERE MADH = ? AND MACTSP = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị kiểm tra CT_DONHANG: " . $conn->error);
    }
    $stmt->bind_param("ss", $madh, $mactsp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật số lượng và thành tiền
        $stmt = $conn->prepare("UPDATE CT_DONHANG SET SOLUONG = SOLUONG + ?, DONGIA = ?, THANHTIEN = THANHTIEN + ? WHERE MADH = ? AND MACTSP = ?");
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị cập nhật CT_DONHANG: " . $conn->error);
        }
        $stmt->bind_param("idsss", $quantity, $dongia, $thanhtien, $madh, $mactsp);
    } else {
        // Thêm mới vào CT_DONHANG
        $stmt = $conn->prepare("INSERT INTO CT_DONHANG (MADH, MACTSP, SOLUONG, DONGIA, THANHTIEN) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị thêm CT_DONHANG: " . $conn->error);
        }
        $stmt->bind_param("ssidd", $madh, $mactsp, $quantity, $dongia, $thanhtien);
    }
    if (!$stmt->execute()) {
        throw new Exception("Lỗi xử lý CT_DONHANG: " . $stmt->error);
    }
    $stmt->close();

    // Cập nhật TONGTIEN trong DONHANG
    $stmt = $conn->prepare("UPDATE DONHANG SET TONGTIEN = (SELECT SUM(THANHTIEN) FROM CT_DONHANG WHERE MADH = ?) WHERE MADH = ?");
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị cập nhật TONGTIEN: " . $conn->error);
    }
    $stmt->bind_param("ss", $madh, $madh);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật TONGTIEN: " . $stmt->error);
    }
    $stmt->close();

    $conn->commit();

    // Trả về phản hồi
    echo json_encode([
        "success" => true,
        "message" => "Thêm vào giỏ hàng thành công!",
        "redirect" => "layout/cart.php"
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error in add_cart.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conn->close();
?>