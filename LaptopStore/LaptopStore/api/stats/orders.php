
<?php
require_once '../../includes/connect.php';
//Phúc
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

$status = $_GET['status'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$address = $_GET['address'] ?? '';

function getOrders($status, $fromDate, $toDate, $address) {
    global $conn;

    $sql = "SELECT d.*, t.HOTEN, t.DIACHI as DIACHI_KH
            FROM donhang d
            JOIN taikhoan t ON d.MATK = t.MATK
            WHERE 1=1";
    $params = [];

    if ($status !== '') {
        $sql .= " AND d.TRANGTHAI = ?";
        $params[] = $status;
    }

    if ($fromDate && $toDate) {
        $sql .= " AND d.NGAYDH BETWEEN ? AND ?";
        $params[] = $fromDate . ' 00:00:00';
        $params[] = $toDate . ' 23:59:59';
    }

    if ($address) {
        $sql .= " AND t.DIACHI LIKE ?";
        $params[] = "%$address%";
    }

    $sql .= " ORDER BY d.NGAYDH DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$orders = getOrders($status, $fromDate, $toDate, $address);

// Thêm TRANGTHAI_TEXT vào dữ liệu
$orderStatuses = [
    0 => 'Chờ xác nhận',
    1 => 'Đã xác nhận',
    2 => 'Đang giao hàng',
    3 => 'Giao thành công',
    4 => 'Đã hủy'
];

$orders = array_map(function($order) use ($orderStatuses) {
    $order['TRANGTHAI_TEXT'] = $orderStatuses[$order['TRANGTHAI']] ?? 'Không xác định';
    return $order;
}, $orders);

echo json_encode($orders);
?>