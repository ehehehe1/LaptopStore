<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
$path = "../../../includes/connect.php";

if (!file_exists($path)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy tệp connect.php']);
    exit;
}

include $path;

if (!isset($conn)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

$status = $_GET['status'] ?? '2';
$search = $_GET['search'] ?? '';

$sql = "SELECT t.MATK, t.TENDANGNHAP, t.HOTEN, t.SDT, t.EMAIL, t.TRANGTHAI, c.TENCV
        FROM taikhoan t
        JOIN CHUCVU c ON t.MACV = c.MACV
        WHERE 1=1";
$params = [];

if ($status !== '2') {
    $sql .= " AND t.TRANGTHAI = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (t.TENDANGNHAP LIKE ? OR t.HOTEN LIKE ? OR t.EMAIL LIKE ? OR t.SDT LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY t.MATK";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ob_end_clean();
    echo json_encode($accounts);
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $e->getMessage()]);
}
?>