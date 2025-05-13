<?php
header('Content-Type: application/json');
require_once '../../includes/connect.php';

/////////////////////////
// XỬ LÝ YÊU CẦU POST //
/////////////////////////
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TENSP'])) {
    $tensp = $_POST['TENSP'];

    $sql = "SELECT dh.MADH, dh.NGAYDAT, ct.SOLUONG, ct.THANHTIEN
            FROM donhang dh
            JOIN ct_donhang ct ON dh.MADH = ct.MADH
            JOIN ct_sanpham ctsp ON ct.MACTSP = ctsp.MACTSP
            JOIN sanpham sp ON ctsp.MASP = sp.MASP
            WHERE sp.TENSP = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$tensp]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Chi tiết đơn hàng sản phẩm: <?= htmlspecialchars($tensp) ?></title>
        <style>
            body { font-family: sans-serif; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
            th { background: #eee; }
        </style>
    </head>
    <body>
        <h2>Chi tiết các đơn hàng của sản phẩm: <strong><?= htmlspecialchars($tensp) ?></strong></h2>
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['MADH'] ?></td>
                        <td><?= $order['NGAYDAT'] ?></td>
                        <td><?= $order['SOLUONG'] ?></td>
                        <td><?= number_format($order['THANHTIEN'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

/////////////////////////
// XỬ LÝ YÊU CẦU GET //
/////////////////////////

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$search = $_GET['search'] ?? '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
$sort = $_GET['sort'] ?? null;

$sql = "SELECT sp.TENSP, SUM(ct.SOLUONG) AS quantity, SUM(ct.THANHTIEN) AS revenue
        FROM donhang dh
        JOIN ct_donhang ct ON dh.MADH = ct.MADH
        JOIN ct_sanpham ctsp ON ct.MACTSP = ctsp.MACTSP
        JOIN sanpham sp ON ctsp.MASP = sp.MASP
        WHERE 1 = 1";

$params = [];

if (!empty($from) && !empty($to)) {
    $sql .= " AND dh.NGAYDH BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}

if (!empty($search)) {
    $sql .= " AND sp.TENSP LIKE ?";
    $params[] = "%$search%";
}

$sql .= " GROUP BY sp.TENSP";

// Xử lý sắp xếp
switch ($sort) {
    case "1":
        $sql .= " ORDER BY revenue ASC";
        break;
    case "2":
        $sql .= " ORDER BY revenue DESC";
        break;
    default:
        $sql .= " ORDER BY revenue DESC";
        break;
}


// Giới hạn kết quả nếu có
if ($limit) {
    $sql .= " LIMIT " . $limit;
}

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_product = count($results);
    $total_quantity = 0;
    $total_revenue = 0;

    foreach ($results as $row) {
        $total_quantity += $row['quantity'];
        $total_revenue += $row['revenue'];
    }

    echo json_encode([
        'summary' => [
            'total_product' => $total_product,
            'total_quantity' => $total_quantity,
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
?>
