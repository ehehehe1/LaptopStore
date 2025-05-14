<?php
require 'db.php';

header('Content-Type: application/json');

$query = isset($_POST['query']) ? trim($_POST['query']) : '';
$price = isset($_POST['price']) ? trim($_POST['price']) : '';
$size = isset($_POST['size']) ? trim($_POST['size']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : '';

$response = ['success' => false, 'products' => [], 'html' => ''];

try {
    // Truy vấn danh sách sản phẩm
    $sql = "SELECT sp.MASP, sp.TENSP, sp.IMG, MIN(ct.GIABAN) AS GIABAN
            FROM SANPHAM sp
            LEFT JOIN CT_SANPHAM ct ON sp.MASP = ct.MASP
            WHERE sp.TRANGTHAI = 1";
    $conditions = [];

    if (!empty($query)) {
        $query = $conn->real_escape_string($query);
        $conditions[] = "sp.TENSP LIKE '%$query%'";
    }
    if (!empty($size)) {
        $size = $conn->real_escape_string($size);
        $conditions[] = "ct.SIZE = '$size'";
    }
    if (!empty($type)) {
        $type = $conn->real_escape_string($type);
        $conditions[] = "sp.MALOAI = '$type'";
    }

    // Xử lý bộ lọc giá
    $order_by = '';
    if (!empty($price)) {
        switch ($price) {
            case 'under_20':
                $conditions[] = "ct.GIABAN <= 20000000";
                break;
            case '20_30':
                $conditions[] = "ct.GIABAN BETWEEN 20000000 AND 30000000";
                break;
            case 'over_30':
                $conditions[] = "ct.GIABAN >= 30000000";
                break;
            case 'low_to_high':
                $order_by = "ORDER BY GIABAN ASC";
                break;
            case 'high_to_low':
                $order_by = "ORDER BY GIABAN DESC";
                break;
        }
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    $sql .= " GROUP BY sp.MASP, sp.TENSP, sp.IMG";
    if (!empty($order_by)) {
        $sql .= " $order_by";
    }

    $result = $conn->query($sql);

    if ($result) {
        $response['success'] = true;
        $html = '';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imageSrc = $row['IMG'] ? '/LaptopStore-master/LaptopStore/Store/assets/img/product/' . htmlspecialchars($row['IMG']) : '/LaptopStore-master/LaptopStore/Store/assets/img/product/default-product.jpg';
                $html .= '
                    <a href="/LaptopStore-master/LaptopStore/Store/layout/chitietsp.php?masp=' . htmlspecialchars($row['MASP']) . '" class="product-link" style="text-decoration: none; color: inherit;">
                        <div class="product">
                            <img src="' . $imageSrc . '" alt="' . htmlspecialchars($row['TENSP']) . '" loading="lazy">
                            <p>' . htmlspecialchars($row['TENSP']) . '</p>
                            <p class="price">' . number_format($row['GIABAN'], 0, ',', '.') . ' đ</p>
                        </div>
                    </a>
                ';
                $response['products'][] = [
                    'masp' => $row['MASP'],
                    'tensp' => $row['TENSP'],
                    'hinhanh' => $row['IMG'],
                    'gia' => (int)$row['GIABAN']
                ];
            }
        } else {
            $html = '<p>Không tìm thấy sản phẩm nào.</p>';
        }
        $response['html'] = $html;
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['html'] = '<p>Lỗi: ' . $e->getMessage() . '</p>';
}

echo json_encode($response);
$conn->close();


// require 'db.php';

// header('Content-Type: application/json');

// $query = isset($_POST['query']) ? trim($_POST['query']) : '';
// $price = isset($_POST['price']) ? trim($_POST['price']) : '';
// $size = isset($_POST['size']) ? trim($_POST['size']) : '';
// $type = isset($_POST['type']) ? trim($_POST['type']) : '';
// $page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;
// $paginate = isset($_POST['paginate']) && $_POST['paginate'] === 'true';
// $productsPerPage = 8;
// $offset = ($page - 1) * $productsPerPage;

// $response = ['success' => false, 'products' => [], 'html' => '', 'pagination' => ''];

// try {
//     // Truy vấn tổng số sản phẩm
//     $sql_count = "SELECT COUNT(DISTINCT sp.MASP) as total
//                   FROM SANPHAM sp
//                   LEFT JOIN CT_SANPHAM ct ON sp.MASP = ct.MASP
//                   WHERE sp.TRANGTHAI = 1";
//     $conditions = [];

//     if (!empty($query)) {
//         $query = $conn->real_escape_string($query);
//         $conditions[] = "sp.TENSP LIKE '%$query%'";
//     }
//     if (!empty($size)) {
//         $size = $conn->real_escape_string($size);
//         $conditions[] = "ct.SIZE = '$size'";
//     }
//     if (!empty($type)) {
//         $type = $conn->real_escape_string($type);
//         $conditions[] = "sp.MALOAI = '$type'";
//     }

//     // Xử lý bộ lọc giá
//     $order_by = '';
//     if (!empty($price)) {
//         switch ($price) {
//             case 'under_20':
//                 $conditions[] = "ct.GIABAN <= 20000000";
//                 break;
//             case '20_30':
//                 $conditions[] = "ct.GIABAN BETWEEN 20000000 AND 30000000";
//                 break;
//             case 'over_30':
//                 $conditions[] = "ct.GIABAN >= 30000000";
//                 break;
//             case 'low_to_high':
//                 $order_by = "ORDER BY GIABAN ASC";
//                 break;
//             case 'high_to_low':
//                 $order_by = "ORDER BY GIABAN DESC";
//                 break;
//         }
//     }

//     if (!empty($conditions)) {
//         $sql_count .= " AND " . implode(" AND ", $conditions);
//     }

//     $result_count = $conn->query($sql_count);
//     $totalProducts = $result_count->fetch_assoc()['total'];
//     $totalPages = ceil($totalProducts / $productsPerPage);

//     // Truy vấn danh sách sản phẩm
//     $sql = "SELECT sp.MASP, sp.TENSP, sp.IMG, MIN(ct.GIABAN) AS GIABAN
//             FROM SANPHAM sp
//             LEFT JOIN CT_SANPHAM ct ON sp.MASP = ct.MASP
//             WHERE sp.TRANGTHAI = 1";
//     if (!empty($conditions)) {
//         $sql .= " AND " . implode(" AND ", $conditions);
//     }
//     $sql .= " GROUP BY sp.MASP, sp.TENSP, sp.IMG";
//     if (!empty($order_by)) {
//         $sql .= " $order_by";
//     }
//     $sql .= " LIMIT $productsPerPage OFFSET $offset";

//     $result = $conn->query($sql);

//     if ($result) {
//         $response['success'] = true;
//         $html = '';
//         if ($result->num_rows > 0) {
//             while ($row = $result->fetch_assoc()) {
//                 $imageSrc = $row['IMG'] ? '/LaptopStore-master/LaptopStore/Store/assets/img/product/' . htmlspecialchars($row['IMG']) : '/LaptopStore-master/LaptopStore/Store/assets/img/product/default-product.jpg';
//                 $html .= '
//                     <a href="/LaptopStore-master/LaptopStore/Store/layout/chitietsp.php?masp=' . htmlspecialchars($row['MASP']) . '" class="product-link" style="text-decoration: none; color: inherit;">
//                         <div class="product">
//                             <img src="' . $imageSrc . '" alt="' . htmlspecialchars($row['TENSP']) . '" loading="lazy">
//                             <p>' . htmlspecialchars($row['TENSP']) . '</p>
//                             <p class="price">' . number_format($row['GIABAN'], 0, ',', '.') . ' đ</p>
//                         </div>
//                     </a>
//                 ';
//                 $response['products'][] = [
//                     'masp' => $row['MASP'],
//                     'tensp' => $row['TENSP'],
//                     'hinhanh' => $row['IMG'],
//                     'gia' => (int)$row['GIABAN']
//                 ];
//             }
//         } else {
//             $html = '<p>Không tìm thấy sản phẩm nào.</p>';
//         }
//         $response['html'] = $html;

//         // Tạo HTML phân trang
//         if ($paginate && $totalPages > 1) {
//             $pagination = '<div class="pagination">';
//             if ($page > 1) {
//                 $pagination .= '<a href="#" data-page="' . ($page - 1) . '" data-type="' . htmlspecialchars($type) . '" data-source="search">Trang trước</a>';
//             } else {
//                 $pagination .= '<a class="disabled">Trang trước</a>';
//             }

//             for ($i = 1; $i <= $totalPages; $i++) {
//                 $pagination .= '<a href="#" data-page="' . $i . '" data-type="' . htmlspecialchars($type) . '" data-source="search" class="' . ($i === $page ? 'active' : '') . '">' . $i . '</a>';
//             }

//             if ($page < $totalPages) {
//                 $pagination .= '<a href="#" data-page="' . ($page + 1) . '" data-type="' . htmlspecialchars($type) . '" data-source="search">Trang sau</a>';
//             } else {
//                 $pagination .= '<a class="disabled">Trang sau</a>';
//             }
//             $pagination .= '</div>';
//             $response['pagination'] = $pagination;
//         }
//     }

// } catch (Exception $e) {
//     $response['success'] = false;
//     $response['html'] = '<p>Lỗi: ' . $e->getMessage() . '</p>';
// }

// echo json_encode($response);
// $conn->close();

?>