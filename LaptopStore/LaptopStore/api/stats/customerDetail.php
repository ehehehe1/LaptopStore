<?php
require_once '../../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['customer_id'])) {
    echo "<p class='modal-error'>Không tìm thấy khách hàng.</p>";
    exit;
}

$customerId = $_POST['customer_id'];

// Lấy thông tin khách hàng
$sql_info = "SELECT HOTEN, EMAIL, SDT FROM taikhoan WHERE MATK = ?";
$stmt = $conn->prepare($sql_info);
$stmt->execute([$customerId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo "<p class='modal-error'>Không tìm thấy thông tin khách hàng.</p>";
    exit;
}

// Lấy danh sách đơn hàng
$sql_orders = "SELECT dh.MADH, dh.NGAYDH, SUM(ct.THANHTIEN) AS THANHTIEN
        FROM donhang dh
        JOIN ct_donhang ct ON dh.MADH = ct.MADH
        WHERE dh.MATK = ?
        GROUP BY dh.MADH, dh.NGAYDH
        ORDER BY dh.NGAYDH DESC";
$stmt = $conn->prepare($sql_orders);
$stmt->execute([$customerId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Thông tin khách hàng</h2>
<ul class="customer-info">
    <li><strong>Họ tên:</strong> <?= htmlspecialchars($customer['HOTEN']) ?></li>
    <li><strong>Email:</strong> <?= htmlspecialchars($customer['EMAIL']) ?></li>
    <li><strong>Số điện thoại:</strong> <?= htmlspecialchars($customer['SDT']) ?></li>
</ul>

<h3>Danh sách đơn hàng đã mua</h3>
<?php if (count($orders) > 0): ?>
    <table class="modal-table">
        <thead>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['MADH'] ?></td>
                    <td><?= $order['NGAYDH'] ?></td>
                    <td><?= number_format($order['THANHTIEN'], 0, ',', '.') ?> đ</td>
                    <td>
                        <button class="btn-detail" onclick="fetchOrderDetailById('<?= $order['MADH'] ?>')">
                            <i class="fa-regular fa-eye"></i> Chi tiết
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Khách hàng chưa có đơn hàng nào.</p>
<?php endif; ?>
