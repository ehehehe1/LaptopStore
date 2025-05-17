<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

header('Content-Type: application/json');



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Yêu cầu không hợp lệ."]);
    exit;
}

// Lấy dữ liệu
$madh = isset($_POST['madh']) ? trim($_POST['madh']) : '';
$mactsp = isset($_POST['mactsp']) ? trim($_POST['mactsp']) : '';

// Kiểm tra dữ liệu đầu vào
$errors = [];

if (!empty($errors)) {
    echo json_encode(["success" => false, "error" => implode("<br>", $errors)]);
    exit;
}

// Bắt đầu transaction
$conn->begin_transaction();

try {
    // Kiểm tra đơn hàng
    $stmt = $conn->prepare("SELECT MATK FROM DONHANG WHERE MADH = ? AND TRANGTHAI = -1");
    $stmt->bind_param("s", $madh);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0 || $result->fetch_assoc()['MATK'] !== $_SESSION['user_id']) {
        throw new Exception("Đơn hàng không hợp lệ.");
    }
    $stmt->close();

    // Lấy thông tin CT_DONHANG
    $stmt = $conn->prepare("SELECT SOLUONG FROM CT_DONHANG WHERE MADH = ? AND MACTSP = ?");
    $stmt->bind_param("ss", $madh, $mactsp);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Sản phẩm không có trong giỏ hàng.");
    }
    $ct_donhang = $result->fetch_assoc();
    $soluong = $ct_donhang['SOLUONG'];
    $stmt->close();

    // Xóa mục trong CT_DONHANG
    $stmt = $conn->prepare("DELETE FROM CT_DONHANG WHERE MADH = ? AND MACTSP = ?");
    $stmt->bind_param("ss", $madh, $mactsp);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi xóa CT_DONHANG: " . $stmt->error);
    }
    $stmt->close();

    // Cập nhật lại số lượng trong CT_SANPHAM
    $stmt = $conn->prepare("UPDATE CT_SANPHAM SET SOLUONG = SOLUONG + ? WHERE MACTSP = ?");
    $stmt->bind_param("is", $soluong, $mactsp);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật CT_SANPHAM: " . $stmt->error);
    }
    $stmt->close();

    // Cập nhật số lượng trong SANPHAM
    $stmt = $conn->prepare("UPDATE SANPHAM SET SOLUONG = SOLUONG + ? WHERE MASP = (SELECT MASP FROM CT_SANPHAM WHERE MACTSP = ?)");
    $stmt->bind_param("is", $soluong, $mactsp);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật SANPHAM: " . $stmt->error);
    }
    $stmt->close();

    // Cập nhật TONGTIEN trong DONHANG
    $stmt = $conn->prepare("UPDATE DONHANG SET TONGTIEN = (SELECT COALESCE(SUM(THANHTIEN), 0) FROM CT_DONHANG WHERE MADH = ?) WHERE MADH = ?");
    $stmt->bind_param("ss", $madh, $madh);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật TONGTIEN: " . $stmt->error);
    }
    $stmt->close();

    // Lấy tổng tiền mới
    $stmt = $conn->prepare("SELECT TONGTIEN FROM DONHANG WHERE MADH = ?");
    $stmt->bind_param("s", $madh);
    $stmt->execute();
    $result = $stmt->get_result();
    $tongtien = $result->num_rows > 0 ? $result->fetch_assoc()['TONGTIEN'] : 0;
    $stmt->close();

    // Kiểm tra nếu đơn hàng không còn mục nào thì xóa đơn hàng
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM CT_DONHANG WHERE MADH = ?");
    $stmt->bind_param("s", $madh);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();

    if ($count == 0) {
        $stmt = $conn->prepare("DELETE FROM DONHANG WHERE MADH = ?");
        $stmt->bind_param("s", $madh);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi xóa DONHANG: " . $stmt->error);
        }
        $stmt->close();
        $tongtien = 0; // Tổng tiền bằng 0 nếu đơn hàng bị xóa
    }

    // Đồng bộ session với database
    $_SESSION['cart'] = [];
    if ($count > 0) {
        $stmt = $conn->prepare("SELECT ct.MACTSP, ct.SOLUONG, s.TENSP, ctsp.THONGSO, ctsp.MAU, ctsp.SIZE, ct.DONGIA, ct.THANHTIEN 
                                FROM CT_DONHANG ct 
                                JOIN CT_SANPHAM ctsp ON ct.MACTSP = ctsp.MACTSP 
                                JOIN SANPHAM s ON ctsp.MASP = s.MASP 
                                WHERE ct.MADH = ?");
        $stmt->bind_param("s", $madh);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $_SESSION['cart'][] = [
                'mactsp' => $row['MACTSP'],
                'quantity' => $row['SOLUONG'],
                'tensp' => $row['TENSP'],
                'thongso' => $row['THONGSO'],
                'mau' => $row['MAU'],
                'size' => $row['SIZE'],
                'dongia' => $row['DONGIA'],
                'thanhtien' => $row['THANHTIEN']
            ];
        }
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Xóa sản phẩm thành công.",
        "tongtien" => $tongtien
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in delete_cart_item.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conn->close();
?>