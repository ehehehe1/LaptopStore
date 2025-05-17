<?php
require '../../../includes/connect.php';

$masp = $_POST['masp'] ?? '';
$forceDelete = $_POST['force'] ?? false;

if (!$masp) {
    echo json_encode(["success" => false, "message" => "❌ Thiếu mã sản phẩm"]);
    exit;
}

try {
    // 1. Kiểm tra đã từng bán chưa
    $stmt = $conn->prepare("
    SELECT COUNT(*) FROM ct_donhang 
    WHERE MACTSP IN (
        SELECT MACTSP FROM ct_sanpham WHERE MASP = ?
    )
");
    $stmt->execute([$masp]);
    $soldCount = $stmt->fetchColumn();

    // 2. Tính tổng số lượng tồn kho của tất cả biến thể
    $stmt = $conn->prepare("SELECT SUM(SOLUONG) AS total FROM ct_sanpham WHERE MASP = ?");
    $stmt->execute([$masp]);
    $totalQuantity = $stmt->fetchColumn();
    $totalQuantity = (int) $totalQuantity;

    // 3. Nếu đã bán → chỉ ẩn
    if ($soldCount > 0) {
        $conn->prepare("UPDATE sanpham SET TRANGTHAI = 0 WHERE MASP = ?")->execute([$masp]);
        echo json_encode(["success" => true, "message" => "⚠️ Sản phẩm đã từng bán. Đã ẩn khỏi danh sách."]);
        exit;
    }

    // 4. Nếu còn tồn kho nhưng chưa xác nhận forceDelete
    if ($totalQuantity > 0 && !$forceDelete) {
        echo json_encode([
            "success" => false,
            "needConfirm" => true,
            "message" => "🛑 Sản phẩm còn $totalQuantity cái trong kho. Bạn có chắc muốn xoá không?"
        ]);
        exit;
    }

    // 5. Xóa chi tiết sản phẩm và sản phẩm chính
    $conn->prepare("DELETE FROM ct_sanpham WHERE MASP = ?")->execute([$masp]);
    $conn->prepare("DELETE FROM sanpham WHERE MASP = ?")->execute([$masp]);

    echo json_encode(["success" => true, "message" => "🗑️ Đã xoá sản phẩm vĩnh viễn."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "❌ Lỗi khi xoá: " . $e->getMessage()]);
}
