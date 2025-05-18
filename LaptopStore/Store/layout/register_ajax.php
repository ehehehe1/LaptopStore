<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
session_start();
require 'db.php';

// Ghi log để gỡ lỗi
error_log("Yêu cầu nhận được: " . print_r($_SERVER, true));
error_log("Dữ liệu POST: " . print_r($_POST, true));

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Yêu cầu không hợp lệ. Phương thức: " . $_SERVER['REQUEST_METHOD']]);
    exit;
}
$sql = "SELECT COUNT(*) AS total FROM TAIKHOAN";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalAccounts = $row['total'] + 1; // Tăng số lượng lên 1

// Lấy dữ liệu từ form
$TENDANGNHAP = isset($_POST['registerUsername']) ? trim($_POST['registerUsername']) : '';
$HOTEN       = isset($_POST['registerName']) ? trim($_POST['registerName']) : '';
$EMAIL       = isset($_POST['registerEmail']) ? trim($_POST['registerEmail']) : '';
$SDT         = isset($_POST['registerPhone']) ? trim($_POST['registerPhone']) : '';
$DIACHI      = isset($_POST['registerAddress']) ? trim($_POST['registerAddress']) : '';
$MATKHAU     = isset($_POST['registerPassword']) ? $_POST['registerPassword'] : '';
$MACV        = "CV004"; // Mặc định quyền user

$TRANGTHAI   = 1; // Hoạt động


$MATK = "TK" . str_pad($totalAccounts, 3, '0', STR_PAD_LEFT);

// Kiểm tra dữ liệu đầu vào
$errors = [];
if (empty($TENDANGNHAP)) $errors[] = "Vui lòng nhập tên đăng nhập.";
if (empty($HOTEN)) $errors[] = "Vui lòng nhập họ tên.";
if (empty($EMAIL)) $errors[] = "Vui lòng nhập email.";
elseif (!filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ.";
if (empty($SDT)) $errors[] = "Vui lòng nhập số điện thoại.";
elseif (!preg_match('/^0[0-9]{9}$/', $SDT)) $errors[] = "Số điện thoại không hợp lệ.";
if (empty($DIACHI)) $errors[] = "Vui lòng nhập địa chỉ.";
if (empty($MATKHAU)) $errors[] = "Vui lòng nhập mật khẩu.";
elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $MATKHAU)) {
    $errors[] = "Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.";
}

if (!empty($errors)) {
    echo json_encode(["success" => false, "error" => implode("<br>", $errors)]);
    exit;
}

// Kiểm tra tên đăng nhập hoặc email đã tồn tại
$stmt = $conn->prepare("SELECT MATK FROM TAIKHOAN WHERE TENDANGNHAP = ? OR EMAIL = ?");
if (!$stmt) {
    error_log("Lỗi prepare: " . $conn->error);
    echo json_encode(["success" => false, "error" => "Lỗi cơ sở dữ liệu"]);
    exit;
}
$stmt->bind_param("ss", $TENDANGNHAP, $EMAIL);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Tên đăng nhập hoặc email đã tồn tại."]);
    $stmt->close();
    exit;
}
$stmt->close();

// Mã hóa mật khẩu
$hashed_password = password_hash($MATKHAU, PASSWORD_DEFAULT);

// Thêm tài khoản mới vào bảng TAIKHOAN
$stmt = $conn->prepare("INSERT INTO TAIKHOAN (MATK, TENDANGNHAP, MATKHAU, HOTEN, SDT, EMAIL, DIACHI, MACV, TRANGTHAI) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log("Lỗi prepare insert: " . $conn->error);
    echo json_encode(["success" => false, "error" => "Lỗi cơ sở dữ liệu"]);
    exit;
}
$stmt->bind_param("ssssssssi", $MATK, $TENDANGNHAP, $hashed_password, $HOTEN, $SDT, $EMAIL, $DIACHI, $MACV, $TRANGTHAI);

if ($stmt->execute()) {
    // Lưu thông tin vào session
    $_SESSION['user_id'] = $MATK;
    $_SESSION['username'] = $TENDANGNHAP;
    
    echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);


} else {
    error_log("Lỗi execute: " . $stmt->error);
    echo json_encode(["success" => false, "error" => "Đăng ký thất bại: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>