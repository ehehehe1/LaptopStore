<?php
require_once '../../includes/connect.php';
//Phúc
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['madh'])) {
        // Lấy chi tiết sản phẩm trong đơn hàng theo MADH
        $madh = $_POST['madh'];

        $sql = "SELECT dh.MADH, dh.NGAYDH, sp.TENSP, ct.SOLUONG, ct.THANHTIEN
                FROM donhang dh
                JOIN ct_donhang ct ON dh.MADH = ct.MADH
                JOIN ct_sanpham ctsp ON ct.MACTSP = ctsp.MACTSP
                JOIN sanpham sp ON ctsp.MASP = sp.MASP
                WHERE dh.MADH = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$madh]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($items) === 0) {
            echo "<p style='padding:20px;'>Không có sản phẩm nào trong đơn hàng.</p>";
            exit;
        }

        $orderDate = $items[0]['NGAYDH'];
        ?>
        <h2>Chi tiết đơn hàng: <strong><?= htmlspecialchars($madh) ?></strong></h2>
        <p><strong>Ngày đặt:</strong> <?= $orderDate ?></p>

        <table class="modal-table">
            <thead>
                <tr>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['TENSP']) ?></td>
                        <td><?= $item['SOLUONG'] ?></td>
                        <td><?= number_format($item['THANHTIEN'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        exit;
    }

    // Trường hợp thống kê sản phẩm truyền vào TENSP
    if (isset($_POST['tensp'])) {
        $tensp = $_POST['tensp'];

        $sql = "SELECT dh.MADH, dh.NGAYDH, ct.SOLUONG, ct.THANHTIEN
                FROM donhang dh
                JOIN ct_donhang ct ON dh.MADH = ct.MADH
                JOIN ct_sanpham ctsp ON ct.MACTSP = ctsp.MACTSP
                JOIN sanpham sp ON ctsp.MASP = sp.MASP
                WHERE sp.TENSP = ?
                ORDER BY dh.NGAYDH DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$tensp]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <h2>Chi tiết các đơn hàng của sản phẩm: <strong><?= htmlspecialchars($tensp) ?></strong></h2>

        <table class="modal-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['MADH'] ?></td>
                        <td><?= $order['NGAYDH'] ?></td>
                        <td><?= $order['SOLUONG'] ?></td>
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
        <?php
        exit;
    }
}
?>
