<?php
require_once '../../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['madh'])) {
        $madh = $_POST['madh'];

        $sql = "SELECT dh.MADH, dh.NGAYDH, dh.TRANGTHAI, t.HOTEN, t.DIACHI, t.SDT,
                       sp.TENSP, ct.SOLUONG, ct.THANHTIEN
                FROM donhang dh
                JOIN ct_donhang ct ON dh.MADH = ct.MADH
                JOIN ct_sanpham ctsp ON ct.MACTSP = ctsp.MACTSP
                JOIN sanpham sp ON ctsp.MASP = sp.MASP
                JOIN taikhoan t ON dh.MATK = t.MATK
                WHERE dh.MADH = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$madh]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($items) === 0) {
            echo "<p style='padding:20px;'>Không có sản phẩm nào trong đơn hàng.</p>";
            exit;
        }

        // Danh sách trạng thái đơn hàng
        $orderStatuses = [
            0 => 'Chờ xác nhận',
            1 => 'Đã xác nhận',
            2 => 'Đang giao hàng',
            3 => 'Giao thành công',
            4 => 'Đã hủy'
        ];

        $orderDate = $items[0]['NGAYDH'];
        $hoten = $items[0]['HOTEN'];
        $diachi = $items[0]['DIACHI'];
        $sdt = $items[0]['SDT'];
        $trangthai = $orderStatuses[$items[0]['TRANGTHAI']] ?? 'Không xác định';
        $tongTien = array_sum(array_column($items, 'THANHTIEN'));
        ?>
        <h2>Chi tiết đơn hàng: <strong><?= htmlspecialchars($madh) ?></strong></h2>
        <p><strong>Ngày đặt:</strong> <?= $orderDate ?></p>
        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($hoten) ?></p>
        <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($sdt) ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($diachi) ?></p>
        <p><strong>Trạng thái:</strong> <?= $trangthai ?></p>

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
                <tr style="font-weight: bold; border-top: 1px solid #ccc;">
                    <td colspan="2" style="text-align: right;">Tổng cộng:</td>
                    <td><?= number_format($tongTien, 0, ',', '.') ?> đ</td>
                </tr>
            </tbody>
        </table>
        <?php
        exit;
    }

    // Trường hợp xem tất cả đơn hàng của 1 sản phẩm
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
