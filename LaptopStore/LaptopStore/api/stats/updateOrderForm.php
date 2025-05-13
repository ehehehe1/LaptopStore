<?php
require_once '../../includes/connect.php';

$MADH = $_GET['madh'] ?? null;
if (!$MADH) {
    echo "<p>Không tìm thấy mã đơn hàng.</p>";
    exit;
}

$stmt = $conn->prepare("SELECT TRANGTHAI FROM donhang WHERE MADH = ?");
$stmt->execute([$MADH]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Đơn hàng không tồn tại.</p>";
    exit;
}

$trangThaiHienTai = (int)$order['TRANGTHAI'];

$statuses = [
    0 => 'Chờ xác nhận',
    1 => 'Đã xác nhận',
    2 => 'Đang giao hàng',
    3 => 'Giao thành công',
    4 => 'Đã hủy'
];

$allowedNext = [];
switch ($trangThaiHienTai) {
    case 0:
        $allowedNext = [1, 4];
        break;
    case 1:
        $allowedNext = [2, 4];
        break;
    case 2:
        $allowedNext = [3];
        break;
    default:
        $allowedNext = [];
}
?>

<h2>Cập nhật trạng thái đơn hàng #<?= htmlspecialchars($MADH) ?></h2>
<form id="update-order-form" class="admin-control">
    <input type="hidden" name="madh" value="<?= htmlspecialchars($MADH) ?>">
    <p><strong>Trạng thái hiện tại:</strong> <?= $statuses[$trangThaiHienTai] ?? 'Không xác định' ?></p>

    <?php if (!empty($allowedNext)) : ?>
        <label for="trangthai">Chọn trạng thái mới:</label>
        <select name="trangthai" id="trangthai" class="form-select">
            <?php foreach ($allowedNext as $key): ?>
                <option value="<?= $key ?>"><?= $statuses[$key] ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <button type="submit" class="btn-control-large"><i class="fa-light fa-save"></i> Cập nhật</button>
    <?php else: ?>
        <p style="color: gray;">Đơn hàng đã hoàn tất hoặc đã huỷ, không thể cập nhật.</p>
    <?php endif; ?>
</form>