<?php
session_start();
require '../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['adminUsername'] ?? '');
    $password = $_POST['adminPassword'] ?? '';
    $errors = [];

    if (!$username) $errors[] = "Vui lòng nhập tên đăng nhập.";
    if (!$password) $errors[] = "Vui lòng nhập mật khẩu.";

    if (!$errors) {
        $stmt = $conn->prepare("SELECT MATK, TENDANGNHAP, MATKHAU, MACV, TRANGTHAI FROM TAIKHOAN WHERE TENDANGNHAP = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) $errors[] = "Tên đăng nhập không tồn tại.";
        else {
            $user = $result->fetch_assoc();
            if ($user['TRANGTHAI'] != 1) $errors[] = "Tài khoản đã bị khóa.";
            elseif ($user['MACV'] !== 'CV000') $errors[] = "Bạn không có quyền truy cập trang quản trị.";
            elseif (!password_verify($password, $user['MATKHAU'])) $errors[] = "Mật khẩu không đúng.";
            else {
                $_SESSION['admin_id'] = $user['MATK'];
                $_SESSION['admin_username'] = $user['TENDANGNHAP'];
                header("Location: /LaptopStore/LaptopStore/admin.php");
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!-- HTML form đăng nhập admin -->
<!DOCTYPE html>
<html>
<head><title>Admin Login</title></head>
<body>
    <form method="post">
        <input name="adminUsername" placeholder="Tên đăng nhập" required>
        <input name="adminPassword" type="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng nhập</button>
        <?php if (!empty($errors)) echo '<div style="color:red">'.implode('<br>',$errors).'</div>'; ?>
    </form>
</body>
</html>
