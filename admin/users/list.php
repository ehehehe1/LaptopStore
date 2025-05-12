<?php
session_start();
require '../../includes/db.php';
require '../../includes/header.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll();

?>

<div class="container mt-5">
    <h2>Quản lý người dùng</h2>
    <a href="add.php" class="btn btn-success mb-3">Thêm người dùng</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Email</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['is_locked'] ? 'Khóa' : 'Hoạt động'; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">Chỉnh sửa</a>
                        <a href="lock_unlock.php?id=<?php echo $user['id']; ?>" class="btn btn-<?php echo $user['is_locked'] ? 'success' : 'danger'; ?>">
                            <?php echo $user['is_locked'] ? 'Mở khóa' : 'Khóa'; ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require '../../includes/footer.php'; ?>