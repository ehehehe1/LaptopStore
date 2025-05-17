<?php
require '../../../includes/connect.php';

$masp = $_POST['masp'] ?? null;

if (!$masp) {
    echo "Không tìm thấy sản phẩm";
    exit;
}

// Lấy thông tin từ bảng sanpham + loaisp
$stmt = $conn->prepare("
    SELECT sp.TENSP, sp.IMG, sp.THUONGHIEU, sp.MASP, lsp.TENLOAI
    FROM sanpham sp
    JOIN loaisp lsp ON sp.MALOAI = lsp.MALOAI
    WHERE sp.MASP = ?
");
$stmt->execute([$masp]);
$sp = $stmt->fetch();

if (!$sp) {
    echo "Không tìm thấy sản phẩm";
    exit;
}

// Lấy thông tin chi tiết từ ct_sanpham
$stmt2 = $conn->prepare("
    SELECT MAU, SIZE, GIANHAP, GIABAN, THONGSO, SOLUONG
    FROM ct_sanpham
    WHERE MASP = ?
");
$stmt2->execute([$masp]);
$details = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Render HTML
?>

<h2><?= htmlspecialchars($sp['TENSP']) ?></h2>
<img src="../../../../Store/assets/img/product/<?= htmlspecialchars($sp['IMG']) ?>" alt="Hình sản phẩm" style="width: 200px; margin-bottom: 10px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

<p><strong>Thương hiệu:</strong> <?= htmlspecialchars($sp['THUONGHIEU']) ?></p>
<p><strong>Phân loại:</strong> <?= htmlspecialchars($sp['TENLOAI']) ?></p>

<hr>

<h3>Biến thể sản phẩm</h3>
<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th>Màu</th>
            <th>Size</th>
            <th>Giá nhập</th>
            <th>Giá bán</th>
            <th>Tồn kho</th>
            <th>Thông số</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($details as $ct): ?>
        <tr>
            <td><?= htmlspecialchars($ct['MAU']) ?></td>
            <td><?= htmlspecialchars($ct['SIZE']) ?></td>
            <td><?= number_format($ct['GIANHAP']) ?>₫</td>
            <td><?= number_format($ct['GIABAN']) ?>₫</td>
            <td><?= $ct['SOLUONG'] ?></td>
            <td><?= nl2br(htmlspecialchars($ct['THONGSO'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
