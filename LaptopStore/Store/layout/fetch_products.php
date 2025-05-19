<?php
require 'db.php';

header('Content-Type: application/json');

$type = isset($_POST['type']) ? $_POST['type'] : '';
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;
$paginate = isset($_POST['paginate']) && $_POST['paginate'] === 'true';
$productsPerPage = 2;
$offset = ($page - 1) * $productsPerPage;

try {
    // Truy vấn tổng số sản phẩm
    $sql_count = "SELECT COUNT(DISTINCT s.MASP) as total 
                  FROM SANPHAM s 
                  LEFT JOIN CT_SANPHAM ct ON s.MASP = ct.MASP 
                  WHERE s.TRANGTHAI = 1";
    if ($type) {
        $sql_count .= " AND s.MALOAI = ?";
    }
    $stmt_count = $conn->prepare($sql_count);
    if ($type) {
        $stmt_count->bind_param("s", $type);
    }
    $stmt_count->execute();
    $totalProducts = $stmt_count->get_result()->fetch_assoc()['total'];
    $totalPages = ceil($totalProducts / $productsPerPage);

    // Truy vấn danh sách sản phẩm
    $sql = "SELECT s.MASP, s.TENSP, s.IMG, MIN(ct.GIABAN) as GIABAN 
            FROM SANPHAM s 
            LEFT JOIN CT_SANPHAM ct ON s.MASP = ct.MASP 
            WHERE s.TRANGTHAI = 1";
    if ($type) {
        $sql .= " AND s.MALOAI = ?";
    }
    $sql .= " GROUP BY s.MASP LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if ($type) {
        $stmt->bind_param("sii", $type, $productsPerPage, $offset);
    } else {
        $stmt->bind_param("ii", $productsPerPage, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Tạo HTML danh sách sản phẩm
    $html = '';
    if ($result->num_rows > 0) {
        while ($product = $result->fetch_assoc()) {
            $imageSrc = $product['IMG'] ? '../assets/img/product/' . htmlspecialchars($product['IMG']) : '../assets/img/product/default-product.jpg';
            $html .= '

                <a href="layout/chitietsp.php?masp=' . htmlspecialchars($product['MASP']) . '" class="product-link" style="text-decoration: none; color: inherit;">

                    <div class="product">
                        <img src="' . $imageSrc . '" alt="' . htmlspecialchars($product['TENSP']) . '" loading="lazy">
                        <p>' . htmlspecialchars($product['TENSP']) . '</p>
                        <p class="price">' . number_format($product['GIABAN'], 0, ',', '.') . ' đ</p>
                    </div>
                </a>
            ';
        }
    } else {
        $html = '<p>Không tìm thấy sản phẩm nào.</p>';
    }

    // Tạo HTML phân trang
    $pagination = '';
    if ($paginate && $totalPages > 1) {
        $pagination .= '<div class="pagination">';
        if ($page > 1) {
            $pagination .= '<a href="#" data-page="' . ($page - 1) . '" data-type="' . htmlspecialchars($type) . '">Trang trước</a>';
        } else {
            $pagination .= '<a class="disabled">Trang trước</a>';
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            $pagination .= '<a href="#" data-page="' . $i . '" data-type="' . htmlspecialchars($type) . '" class="' . ($i === $page ? 'active' : '') . '">' . $i . '</a>';
        }

        if ($page < $totalPages) {
            $pagination .= '<a href="#" data-page="' . ($page + 1) . '" data-type="' . htmlspecialchars($type) . '">Trang sau</a>';
        } else {
            $pagination .= '<a class="disabled">Trang sau</a>';
        }
        $pagination .= '</div>';
    }

    // Trả về JSON
    echo json_encode([
        'success' => true,
        'html' => $html,
        'pagination' => $pagination
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi truy vấn: ' . $e->getMessage()
    ]);
}
?>