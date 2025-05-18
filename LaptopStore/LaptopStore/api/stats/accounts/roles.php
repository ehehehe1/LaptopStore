<?php
header('Content-Type: application/json');
require_once "../../../includes/connect.php";

$sql = "SELECT MACV, TENCV FROM CHUCVU WHERE TRANGTHAI = 1";
$stmt = $conn->query($sql);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($roles);
?>