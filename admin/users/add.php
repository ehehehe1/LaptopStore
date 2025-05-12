<?php
session_start();
require '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, email, status) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $password, $email, $status])) {
        $success = "User added successfully.";
    } else {
        $error = "Error adding user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <title>Add User</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Add New User</h2>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button class="btn btn-primary">Add User</button>
        </form>
    </div>
    <script src="../../assets/bootstrap/bootstrap.min.js"></script>
    <script src="../../assets/js/scripts.js"></script>
</body>
</html>