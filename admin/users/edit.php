<?php
session_start();
require '../../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: list.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, status = ? WHERE id = ?");
    $stmt->execute([$username, $email, $status, $user_id]);

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <title>Edit User</title>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <h2>Edit User</h2>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="locked" <?php echo $user['status'] === 'locked' ? 'selected' : ''; ?>>Locked</option>
                </select>
            </div>
            <button class="btn btn-primary">Update User</button>
        </form>
    </div>
    <script src="../../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>
</body>
</html>