<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

$matk = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT SUM(ct.SOLUONG) AS count
                        FROM DONHANG dh
                        JOIN CT_DONHANG ct ON dh.MADH = ct.MADH
                        WHERE dh.MATK = ? AND dh.TRANGTHAI = -1");
$stmt->bind_param("s", $matk);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'] ?? 0;
$stmt->close();

echo json_encode(['success' => true, 'count' => $count]);

$conn->close();
?>