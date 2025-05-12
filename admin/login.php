<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch();

  if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    header("Location: dashboard.php");
    exit;
  } else {
    $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
  }
}
?>
<!-- Form đăng nhập sử dụng Bootstrap -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <title>Đăng nhập quản trị</title>
</head>
<body>
    <form method="post" class="container mt-5">
        <h2>Đăng nhập quản trị</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Đăng nhập</button>
    </form>
    <script src="../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>