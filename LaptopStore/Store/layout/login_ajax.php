<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
session_start();
require 'db.php';

// Ghi log để gỡ lỗi
error_log("Yêu cầu đăng nhập: " . print_r($_SERVER, true));
error_log("Dữ liệu POST: " . print_r($_POST, true));

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Yêu cầu không hợp lệ. Phương thức: " . $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Lấy dữ liệu từ form
$TENDANGNHAP = isset($_POST['loginUsername']) ? trim($_POST['loginUsername']) : '';
$MATKHAU     = isset($_POST['loginPassword']) ? $_POST['loginPassword'] : '';

// Kiểm tra dữ liệu đầu vào
$errors = [];
if (empty($TENDANGNHAP)) $errors[] = "Vui lòng nhập tên đăng nhập.";
if (empty($MATKHAU)) $errors[] = "Vui lòng nhập mật khẩu.";

if (!empty($errors)) {
    echo json_encode(["success" => false, "error" => implode("<br>", $errors)]);
    exit;
}

// Kiểm tra thông tin đăng nhập
$stmt = $conn->prepare("SELECT MATK, TENDANGNHAP, MATKHAU, TRANGTHAI FROM TAIKHOAN WHERE TENDANGNHAP = ?");
if (!$stmt) {
    error_log("Lỗi prepare: " . $conn->error);
    echo json_encode(["success" => false, "error" => "Lỗi cơ sở dữ liệu"]);
    exit;
}
$stmt->bind_param("s", $TENDANGNHAP);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Tên đăng nhập không tồn tại."]);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Kiểm tra trạng thái tài khoản
if ($user['TRANGTHAI'] != 1) {
    echo json_encode(["success" => false, "error" => "Tài khoản đã bị khóa."]);
    exit;
}


// Kiểm tra mật khẩu
if (!password_verify($MATKHAU, $user['MATKHAU'])) {
    echo json_encode(["success" => false, "error" => "Mật khẩu không đúng."]);
    exit;
}

// Lưu thông tin vào session
$_SESSION['user_id'] = $user['MATK'];
$_SESSION['username'] = $user['TENDANGNHAP'];
echo json_encode(["success" => true, "message" => "Đăng nhập thành công!"]);

$conn->close();
?>