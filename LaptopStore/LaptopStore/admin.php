<?php
session_start();
include "./includes/connect.php";

// Logic từ danhsachdonhang.php
// Xử lý filter cho đơn hàng
$status = $_GET['status'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$address = $_GET['address'] ?? '';

// Hàm lấy danh sách đơn hàng
function getOrders($status, $fromDate, $toDate, $address)
{
    global $conn;

    $sql = "SELECT d.*, t.HOTEN, t.DIACHI as DIACHI_KH
            FROM donhang d
            JOIN taikhoan t ON d.MATK = t.MATK
            WHERE 1=1";
    $params = [];

    if ($status !== '') {
        $sql .= " AND d.TRANGTHAI = ?";
        $params[] = $status;
    }

    if ($fromDate && $toDate) {
        $sql .= " AND d.NGAYDH BETWEEN ? AND ?";
        $params[] = $fromDate . ' 00:00:00';
        $params[] = $toDate . ' 23:59:59';
    }

    if ($address) {
        $sql .= " AND t.DIACHI LIKE ?";
        $params[] = "%$address%";
    }

    $sql .= " ORDER BY d.NGAYDH DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$orders = getOrders($status, $fromDate, $toDate, $address);

// Trạng thái đơn hàng
$orderStatuses = [
    0 => 'Chờ xác nhận',
    1 => 'Đã xác nhận',
    2 => 'Đang giao hàng',
    3 => 'Giao thành công',
    4 => 'Đã hủy'
];

// Logic từ capnhatdonhang.php
// Xử lý cập nhật trạng thái đơn hàng qua AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $MADH = $_POST['madh'] ?? null;
    $trangThaiMoi = (int) $_POST['trangthai'] ?? null;

    if (!$MADH) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy mã đơn hàng.']);
        exit;
    }

    // Lấy trạng thái hiện tại
    $stmt = $conn->prepare("SELECT TRANGTHAI FROM donhang WHERE MADH = ?");
    $stmt->execute([$MADH]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại.']);
        exit;
    }

    $trangThaiHienTai = (int) $order['TRANGTHAI'];

    // Xác định trạng thái tiếp theo hợp lệ
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

    if (!in_array($trangThaiMoi, $allowedNext)) {
        echo json_encode(['success' => false, 'message' => 'Không được chuyển sang trạng thái đã chọn.']);
        exit;
    }

    // Cập nhật trạng thái
    $stmt = $conn->prepare("UPDATE donhang SET TRANGTHAI = ? WHERE MADH = ?");
    $stmt->execute([$trangThaiMoi, $MADH]);
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='./assets/img/favicon.png' rel='icon' type='image/x-icon' />
    <link rel="stylesheet" href="./assets/css/admin.css">
    <link rel="stylesheet" href="./assets/css/toast-message.css">
    <link href="./assets/font/font-awesome-pro-v6-6.2.0/css/all.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="./assets/css/admin-responsive.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>Quản lý cửa hàng</title>
</head>

<body>
    <header class="header">
        <button class="menu-icon-btn">
            <div class="menu-icon">
                <i class="fa-regular fa-bars"></i>
            </div>
        </button>
    </header>
    <div class="container">
        <aside class="sidebar open">
            <div class="top-sidebar">
                <a href="#" class="channel-logo"><img src="./assets/img/favicon.png" alt="Channel Logo"></a>
                <div class="hidden-sidebar your-channel"><img src="" style="height: 30px;" alt=""></div>
            </div>
            <div class="middle-sidebar">
                <ul class="sidebar-list">
                    <li class="sidebar-list-item tab-content active">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-light fa-house"></i></div>
                            <div class="hidden-sidebar">Trang tổng quan</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-light fa-pot-food"></i></div>
                            <div class="hidden-sidebar">Sản phẩm</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-light fa-users"></i></div>
                            <div class="hidden-sidebar">Khách hàng</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-light fa-basket-shopping"></i></div>
                            <div class="hidden-sidebar">Đơn hàng</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-light fa-chart-simple"></i></div>
                            <div class="hidden-sidebar">Thống kê</div>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-sidebar">
                <ul class="sidebar-list">
                    <li class="sidebar-list-item user-logout">
                        <a href="/" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-thin fa-circle-chevron-left"></i></div>
                            <div class="hidden-sidebar">Trang chủ</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item user-logout">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa-light fa-circle-user"></i></div>
                            <div class="hidden-sidebar" id="name-acc"></div>
                        </a>
                    </li>
                    <li class="sidebar-list-item user-logout">
                        <a href="#" class="sidebar-link" id="logout-acc">
                            <div class="sidebar-icon"><i class="fa-light fa-arrow-right-from-bracket"></i></div>
                            <div class="hidden-sidebar">Đăng xuất</div>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
        <main class="content">
            <!-- Section Trang tổng quan -->
            <div class="section active">
                <h1 class="page-title">Trang tổng quát của cửa hàng</h1>
                <div class="cards">
                    <div class="card-single">
                        <div class="box">
                            <h2 id="amount-user">0</h2>
                            <div class="on-box">
                                <img src="assets/img/admin/s1.png" alt="" style="width: 200px;">
                                <h3>Khách hàng</h3>
                                <p>Sản phẩm là bất cứ cái gì có thể đưa vào thị trường để tạo sự chú ý, mua sắm, sử dụng
                                    hay tiêu dùng nhằm thỏa mãn một nhu cầu hay ước muốn. Nó có thể là những vật thể,
                                    dịch vụ, con người, địa điểm, tổ chức hoặc một ý tưởng.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-single">
                        <div class="box">
                            <div class="on-box">
                                <img src="assets/img/admin/s2.png" alt="" style="width: 200px;">
                                <h2 id="amount-product">0</h2>
                                <h3>Sản phẩm</h3>
                                <p>Khách hàng mục tiêu là một nhóm đối tượng khách hàng trong phân khúc thị trường mục
                                    tiêu mà doanh nghiệp bạn đang hướng tới.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-single">
                        <div class="box">
                            <h2 id="doanh-thu">0</h2>
                            <div class="on-box">
                                <img src="assets/img/admin/s3.png" alt="" style="width: 200px;">
                                <h3>Doanh thu</h3>
                                <p>Doanh thu của doanh nghiệp là toàn bộ số tiền sẽ thu được do tiêu thụ sản phẩm, cung
                                    cấp dịch vụ với sản lượng.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Section Sản phẩm -->
            <div class="section product-all">
                <div class="admin-control">
                    <div class="admin-control-left">
                        <select id="filter-brand" onchange="showProduct()">
                            <option value="">Thương hiệu</option>
                            <option value="DELL">DELL</option>
                            <option value="HP">HP</option>
                            <option value="ASUS">ASUS</option>
                            <option value="ACER">ACER</option>
                            <option value="LENOVO">LENOVO</option>
                        </select>

                        <select id="filter-category" onchange="showProduct()">
                            <option value="">Phân loại</option>
                            <?php
                            require './includes/connect.php';
                            $res = $conn->query("SELECT MALOAI, TENLOAI FROM loaisp WHERE TRANGTHAI = 1");
                            while ($r = $res->fetch()) {
                                echo "<option value='{$r['MALOAI']}'>{$r['TENLOAI']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="admin-control-center">
                        <form action="" class="form-search">
                            <span class="search-btn"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input id="form-search-product" type="text" class="form-search-input"
                                placeholder="Tìm kiếm tên máy..." oninput="showProduct()">
                        </form>
                    </div>
                    <div class="admin-control-right">
                        <button class="btn-control-large" id="btn-cancel-product" onclick="cancelSearchProduct()"><i
                                class="fa-light fa-rotate-right"></i> Làm mới</button>
                        <button class="btn-control-large" id="btn-add-product"
                            onclick="document.getElementById('modal-add-product').classList.add('open')"><i
                                class="fa-light fa-plus"></i> Thêm món mới</button>
                    </div>
                </div>
                <div class="table">
                    <table width="100%">
                        <thead>
                            <tr>
                                <td>STT</td>
                                <td>Tên sản phẩm</td>
                                <td>Phân loại</td>
                                <td>Thương hiệu</td>
                                <td>Thao tác</td>
                            </tr>
                        </thead>
                        <tbody id="show-product"></tbody>
                    </table>
                </div>
            </div>
            <!-- Section Khách hàng -->
            <div class="section">
                <div class="admin-control">
                    <div class="admin-control-left">
                        <select name="tinh-trang-user" id="tinh-trang-user" onchange="showUser()">
                            <option value="2">Tất cả</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Bị khóa</option>
                        </select>
                    </div>
                    <div class="admin-control-center">
                        <form action="" class="form-search">
                            <span class="search-btn"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input id="form-search-user" type="text" class="form-search-input"
                                placeholder="Tìm kiếm khách hàng..." oninput="showUser()">
                        </form>
                    </div>
                    <div class="admin-control-right">
                        <form action="" class="fillter-date">
                            <div>
                                <label for="time-start">Từ</label>
                                <input type="date" class="form-control-date" id="time-start-user" onchange="showUser()">
                            </div>
                            <div>
                                <label for="time-end">Đến</label>
                                <input type="date" class="form-control-date" id="time-end-user" onchange="showUser()">
                            </div>
                        </form>
                        <button class="btn-reset-order" onclick="cancelSearchUser()"><i
                                class="fa-light fa-arrow-rotate-right"></i></button>
                        <button id="btn-add-user" class="btn-control-large" onclick="openCreateAccount()"><i
                                class="fa-light fa-plus"></i> <span>Thêm khách hàng</span></button>
                    </div>
                </div>
                <div class="table">
                    <table width="100%">
                        <thead>
                            <tr>
                                <td>STT</td>
                                <td>Họ và tên</td>
                                <td>Liên hệ</td>
                                <td>Ngày tham gia</td>
                                <td>Tình trạng</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody id="show-user"></tbody>
                    </table>
                </div>
            </div>
            <!-- phúc -->
            <div class="section">
                <h2>Quản lý đơn hàng</h2>
                <form id="order-filter-form" class="admin-control">
                    <div class="admin-control-left">
                        <select name="status" id="tinh-trang">
                            <option value="">Tất cả</option>
                            <?php foreach ($orderStatuses as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $status === (string) $key ? 'selected' : '' ?>><?= $value ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-control-center">
                        <div class="form-search">
                            <span class="search-btn"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input id="form-search-order" type="text" name="address" class="form-search-input"
                                placeholder="Tìm kiếm theo địa chỉ..." value="<?= $address ?>">
                        </div>
                    </div>
                    <div class="admin-control-right">
                        <div class="fillter-date">
                            <div>
                                <label for="time-start">Từ</label>
                                <input type="date" name="from_date" class="form-control-date" id="time-start"
                                    value="<?= $fromDate ?>">
                            </div>
                            <div>
                                <label for="time-end">Đến</label>
                                <input type="date" name="to_date" class="form-control-date" id="time-end"
                                    value="<?= $toDate ?>">
                            </div>
                        </div>
                        <button type="button" class="btn-reset-order" onclick="resetOrderFilter()"><i
                                class="fa-light fa-arrow-rotate-right"></i></button>
                    </div>
                </form>
                <div class="table">
                    <table width="100%">
                        <thead>
                            <tr>
                                <td>Mã ĐH</td>
                                <td>Khách hàng</td>
                                <td>Ngày đặt</td>
                                <td>Tổng tiền</td>
                                <td>Địa chỉ</td>
                                <td>Trạng thái</td>
                                <td>Thao tác</td>
                            </tr>
                        </thead>
                        <tbody id="showOrder">
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= $order['MADH'] ?></td>
                                        <td><?= $order['HOTEN'] ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['NGAYDH'])) ?></td>
                                        <td><?= number_format($order['TONGTIEN'], 0, ',', '.') ?>₫</td>
                                        <td><?= $order['DIACHI_KH'] ?></td>
                                        <td><?= $orderStatuses[$order['TRANGTHAI']] ?? 'Không xác định' ?></td>
                                        <td>
                                            <button class="btn-detail" onclick="fetchOrderDetailById('<?= $order['MADH'] ?>')">
                                                <i class="fa-regular fa-eye"></i> Chi tiết
                                            </button>
                                            <?php if ($order['TRANGTHAI'] < 3): ?>
                                                <button class="btn btn-warning"
                                                    onclick="openUpdateOrderModal('<?= $order['MADH'] ?>')">
                                                    <i class="fa-light fa-edit"></i> Cập nhật TT
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Không có đơn hàng nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section Thống kê -->
            <div class="section">
                <div class="admin-control">
                    <div class="admin-control-left">
                        <select id="select-statistics" onchange="switchStatistics()">
                            <option value="product">Thống kê sản phẩm</option>
                            <option value="customer">Thống kê khách hàng</option>
                        </select>
                    </div>
                    <button class="btn-control-large" onclick="switchStatistics(true)">
                        <i class="fa-solid fa-ranking-star"></i> Top 5
                    </button>
                    <div class="admin-control-center">
                        <form action="" class="form-search">
                            <span class="search-btn"><i class="fa-light fa-magnifying-glass"></i></span>
                            <input id="form-search-tk" type="text" class="form-search-input"
                                placeholder="Tìm kiếm tên máy..." oninput="switchStatistics()">
                        </form>
                    </div>
                    <div class="admin-control-right">
                        <form action="" class="fillter-date">
                            <div>
                                <label for="time-start">Từ</label>
                                <input type="date" class="form-control-date" id="time-start-tk"
                                    onchange="switchStatistics()">
                            </div>
                            <div>
                                <label for="time-end">Đến </label>
                                <input type="date" class="form-control-date" id="time-end-tk"
                                    onchange="switchStatistics()">
                            </div>
                        </form>
                        <button class="btn-reset-order" onclick="switchStatistics(false, 1)"><i
                                class="fa-regular fa-arrow-up-short-wide"></i></button>
                        <button class="btn-reset-order" onclick="switchStatistics(false, 2)"><i
                                class="fa-regular fa-arrow-down-wide-short"></i></button>
                        <button class="btn-reset-order" onclick="switchStatistics(false, 0)"><i
                                class="fa-light fa-arrow-rotate-right"></i></button>
                    </div>
                </div>
                <div id="product-statistics">
                    <div class="order-statistical" id="order-statistical">
                        <div class="order-statistical-item">
                            <div class="order-statistical-item-content">
                                <p class="order-statistical-item-content-desc">Sản phẩm được bán ra</p>
                                <h4 class="order-statistical-item-content-h" id="product-total"></h4>
                            </div>
                            <div class="order-statistical-item-icon">
                                <i class="fa-light fa-laptop"></i>
                            </div>
                        </div>
                        <div class="order-statistical-item">
                            <div class="order-statistical-item-content">
                                <p class="order-statistical-item-content-desc">Số lượng bán ra</p>
                                <h4 class="order-statistical-item-content-h" id="product-quantity"></h4>
                            </div>
                            <div class="order-statistical-item-icon">
                                <i class="fa-light fa-file-lines"></i>
                            </div>
                        </div>
                        <div class="order-statistical-item">
                            <div class="order-statistical-item-content">
                                <p class="order-statistical-item-content-desc">Doanh thu</p>
                                <h4 class="order-statistical-item-content-h" id="product-revenue"></h4>
                            </div>
                            <div class="order-statistical-item-icon">
                                <i class="fa-light fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    <div class="table">
                        <table width="100%">
                            <thead>
                                <tr>
                                    <td>STT</td>
                                    <td>Tên món</td>
                                    <td>Số lượng bán</td>
                                    <td>Doanh thu</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody id="showProductStats"></tbody>
                        </table>
                    </div>
                </div>
                <div id="customer-statistics" style="display: none;">
                    <div class="order-statistical">
                        <div class="order-statistical-item">
                            <div class="order-statistical-item-content">
                                <p class="order-statistical-item-content-desc">Số khách hàng</p>
                                <h4 class="order-statistical-item-content-h" id="customer-total"></h4>
                            </div>
                            <div class="order-statistical-item-icon">
                                <i class="fa-light fa-users"></i>
                            </div>
                        </div>
                        <div class="order-statistical-item">
                            <div class="order-statistical-item-content">
                                <p class="order-statistical-item-content-desc">Số đơn hàng</p>
                                <h4 class="order-statistical-item-content-h" id="customer-orders"></h4>
                            </div>
                            <div class="order-statistical-item-icon">
                                <i class="fa-light fa-file-lines"></i>
                            </div>
                        </div>
                        <div class="order-statistical-item">
                            <div class="order-statistical-item-content">
                                <p class="order-statistical-item-content-desc">Tổng doanh thu</p>
                                <h4 class="order-statistical-item-content-h" id="customer-revenue"></h4>
                            </div>
                            <div class="order-statistical-item-icon">
                                <i class="fa-light fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    <div class="table">
                        <table width="100%">
                            <thead>
                                <tr>
                                    <td>STT</td>
                                    <td>Tên khách hàng</td>
                                    <td>Số lượng đơn</td>
                                    <td>Doanh thu</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody id="showCustomerStats"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Modal -->
    <div class="modal detail-order-product" id="product-order-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">×</span>
            <div id="modal-product-order-content"></div>
        </div>
    </div>
    <div id="order-detail-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeOrderDetailModal()">×</span>
            <div id="modal-order-detail-content"></div>
        </div>
    </div>
    <div id="customer-detail-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCustomerDetailModal()">×</span>
            <div id="modal-customer-detail-content"></div>
        </div>
    </div>
    <div id="update-order-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeUpdateOrderModal()">×</span>
            <div id="modal-update-order-content"></div>
        </div>
    </div>

    <!-- modal-add-product    -->
    <div class="modal modal-product" id="modal-add-product">
        <div class="modal-content">
            <span class="close-modal" onclick="this.closest('.modal').classList.remove('open')">&times;</span>
            <form id="add-product-form" enctype="multipart/form-data">
                <h3>Thêm sản phẩm mới</h3>

                <input type="text" name="tensp" placeholder="Tên sản phẩm" required>
                <input type="text" name="thuonghieu" placeholder="Thương hiệu" required>

                <select name="maloai" required>
                    <option value="">--Chọn loại sản phẩm--</option>
                    <?php
                    require './includes/connect.php';
                    $stmt = $conn->query("SELECT MALOAI, TENLOAI FROM loaisp WHERE TRANGTHAI = 1");
                    while ($row = $stmt->fetch()) {
                        echo "<option value='{$row['MALOAI']}'>{$row['TENLOAI']}</option>";
                    }
                    ?>

                </select>

                <input type="text" name="mau" placeholder="Màu sắc" required>
                <input type="text" name="size" placeholder="Kích thước" required>
                <input type="number" name="gianhap" placeholder="Giá nhập" required>
                <input type="number" name="giaban" placeholder="Giá bán" required>
                <input type="number" name="soluong" placeholder="Số lượng" required>

                <div class="form-pair">
                    <textarea name="thongso" placeholder="Thông số kỹ thuật" required></textarea>

                    <div class="upload-group">
                        <label for="file-upload">Chọn ảnh</label>
                        <input id="file-upload" type="file" name="img" accept="image/*" onchange="previewImage(event)"
                            required>
                    </div>
                </div>

                <img id="preview-img" style="display: none; grid-column: span 2; margin: 10px auto; width: 120px;">




                <button type="submit">Thêm sản phẩm</button>
            </form>
        </div>
    </div>

    <!-- modal-update-product -->
    <div class="modal modal-product" id="modal-update-product">
        <div class="modal-content">
            <span class="close-modal" onclick="this.closest('.modal').classList.remove('open')">&times;</span>
            <form id="update-product-form" enctype="multipart/form-data">
                <h3>Cập nhật sản phẩm</h3>

                <input type="hidden" name="masp">

                <input type="text" name="tensp" placeholder="Tên sản phẩm" required>
                <input type="text" name="thuonghieu" placeholder="Thương hiệu" required>

                <select name="maloai" required>
                    <option value="">--Chọn loại sản phẩm--</option>
                    <?php
                    require './includes/connect.php';
                    $stmt = $conn->query("SELECT MALOAI, TENLOAI FROM loaisp WHERE TRANGTHAI = 1");
                    while ($row = $stmt->fetch()) {
                        echo "<option value='{$row['MALOAI']}'>{$row['TENLOAI']}</option>";
                    }
                    ?>
                </select>

                <input type="text" name="mau" placeholder="Màu sắc" required>
                <input type="text" name="size" placeholder="Kích thước" required>
                <input type="number" name="gianhap" placeholder="Giá nhập" required>
                <input type="number" name="giaban" placeholder="Giá bán" required>
                <input type="number" name="soluong" placeholder="Số lượng" required>
                <textarea name="thongso" placeholder="Thông số kỹ thuật"></textarea>

                <div class="upload-group" style="grid-column: span 2;">
                    <label for="update-file">Chọn ảnh mới (nếu muốn)</label>
                    <input id="update-file" type="file" name="img" accept="image/*"
                        onchange="previewUpdateImage(event)">
                    <img id="preview-update-img" style="display: none; width: 120px; margin-top: 10px;">

                </div>

                <button type="submit">Cập nhật sản phẩm</button>
            </form>
        </div>
    </div>


    <!-- Modal chi tiết sản phẩm -->
    <div id="modal-product-detail" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close-modal"
                onclick="document.getElementById('modal-product-detail').classList.remove('open')">&times;</span>
            <div id="modal-product-detail-content"></div>
        </div>
    </div>
    <div id="toast">
    </div>


    <script src="assets/js/stats.js" defer></script>
    <script src="assets/js/admin.js" defer></script>
    <script src="assets/js/product-add.js"></script>


</body>

</html>