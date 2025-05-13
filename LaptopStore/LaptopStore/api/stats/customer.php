<?php
header('Content-Type: application/json');
require_once '../../includes/connect.php';

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$search = $_GET['search'] ?? '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
$sort = $_GET['sort'] ?? null;

$sql = "SELECT tk.MATK AS customer_id,
               tk.HOTEN AS customer_name, 
               COUNT(DISTINCT dh.MADH) AS order_count,
               SUM(ct.THANHTIEN) AS total_revenue
        FROM taikhoan tk
        JOIN donhang dh ON tk.MATK = dh.MATK
        JOIN ct_donhang ct ON dh.MADH = ct.MADH
        WHERE 1 = 1";

$params = [];

if (!empty($from) && !empty($to)) {
    $sql .= " AND dh.NGAYDH BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}

if (!empty($search)) {
    $sql .= " AND tk.HOTEN LIKE ?";
    $params[] = "%$search%";
}

// Sửa GROUP BY theo MATK để chính xác, tránh trùng tên
$sql .= " GROUP BY tk.HOTEN";

switch ($sort) {
    case "1":
        $sql .= " ORDER BY order_count ASC";
        break;
    case "2":
        $sql .= " ORDER BY order_count DESC";
        break;
    case "0":
    default:
        $sql .= " ORDER BY total_revenue DESC";
        break;
}


if ($limit) {
    $sql .= " LIMIT " . $limit;
}


try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_customers = count($results);
    $total_orders = 0;
    $total_revenue = 0;

    foreach ($results as $row) {
        $total_orders += $row['order_count'];
        $total_revenue += $row['total_revenue'];
    }

    echo json_encode([
        'summary' => [
            'total_customers' => $total_customers,
            'total_orders' => $total_orders,
            'total_revenue' => $total_revenue
        ],
        'details' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Query failed',
        'message' => $e->getMessage()
    ]);
}
