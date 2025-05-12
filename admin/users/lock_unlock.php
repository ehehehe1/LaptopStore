<?php
session_start();
require '../../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'lock') {
        $stmt = $conn->prepare("UPDATE users SET status = 'locked' WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "User account locked successfully.";
    } elseif ($action === 'unlock') {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "User account unlocked successfully.";
    } else {
        $message = "Invalid action.";
    }
}

$users = $conn->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <title>Lock/Unlock Users</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Lock/Unlock Users</h2>
        <?php if (isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['status']; ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php if ($user['status'] === 'active'): ?>
                                    <button type="submit" name="action" value="lock" class="btn btn-danger">Lock</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="unlock" class="btn btn-success">Unlock</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="../../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>
</body>
</html>