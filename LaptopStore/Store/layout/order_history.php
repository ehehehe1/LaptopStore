<?php

require 'db.php';



$matk = $_SESSION['user_id'];

// Lấy danh sách đơn hàng đã mua 
$stmt = $conn->prepare("SELECT MADH, TONGTIEN, NGAYDH, DIACHI, PHUONGTHUC 
                        FROM DONHANG 
                        WHERE MATK = ? AND TRANGTHAI != -1
                        ORDER BY NGAYDH DESC");
$stmt->bind_param("s", $matk);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng</title>
    <style>
        .order-history {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
        a {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }
        a:hover {
            text-decoration: underline;
        }
        .no-orders {
            text-align: center;
            color: #555;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            max-height: 800vh;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            overflow-y: auto;
            animation: slideIn 0.3s ease-out;
        }
        .modal-content {
            padding: 20px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 500;
            animation: fadeIn 0.3s ease;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 24px;
            color: #333;
            font-weight: bold;
            background: #f0f0f0;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .close-btn:hover {
            background: #ddd;
            color: #e63946;
        }
        .modal h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .modal table {
            margin-bottom: 0;
        }
        @keyframes slideIn {
            from { transform: translate(-50%, -60%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @media (max-width: 768px) {
            .modal {
                width: 95%;
                max-height: 70vh;
            }
        }
        @media (max-width: 480px) {
            .modal {
                width: 98%;
                max-height: 60vh;
            }
            .close-btn {
                width: 32px;
                height: 32px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
<div class="order-history">
    <h1>Lịch sử đơn hàng</h1>
    <?php if (empty($orders)): ?>
        <p class="no-orders">Bạn chưa có đơn hàng nào.</p>
        <button onclick="window.location.href='../index.php'">Tiếp tục mua sắm</button>
    <?php else: ?>
        <table>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Địa chỉ giao</th>
                <th>Phương thức</th>
                <th>Chi tiết</th>
            </tr>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['MADH']); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['NGAYDH']))); ?></td>
                    <td><?php echo number_format($order['TONGTIEN'], 0, ',', '.'); ?> đ</td>
                    <td><?php echo htmlspecialchars($order['DIACHI']); ?></td>
                    <td><?php echo htmlspecialchars($order['PHUONGTHUC'] === 'cod' ? 'Tiền mặt (COD)' : 'Trực tuyến'); ?></td>
                    <td><a onclick="showOrderDetails('<?php echo htmlspecialchars($order['MADH']); ?>')">Xem chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<!-- Modal cho chi tiết đơn hàng -->
<div class="overlay" onclick="closeOrderModal()"></div>
<div class="modal" id="order-modal">
    <span class="close-btn" onclick="closeOrderModal()">×</span>
    <div class="modal-content" id="order-details-content">
        <!-- Chi tiết đơn hàng sẽ được thêm bằng AJAX -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function showOrderDetails(madh) {
    $.ajax({
        type: 'POST',
        url: 'get_order_details.php',
        data: { madh: madh },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.details.length > 0) {
                let html = `
                    <h2>Chi tiết đơn hàng ${madh}</h2>
                    <table>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Thông số</th>
                            <th>Màu</th>
                            <th>Kích thước</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                `;
                response.details.forEach(detail => {
                    html += `
                        <tr>
                            <td>${detail.tensp}</td>
                            <td>${detail.thongso}</td>
                            <td>${detail.mau}</td>
                            <td>${detail.size}</td>
                            <td>${detail.soluong}</td>
                            <td>${Number(detail.dongia).toLocaleString('vi-VN')} đ</td>
                            <td>${Number(detail.thanhtien).toLocaleString('vi-VN')} đ</td>
                        </tr>
                    `;
                });
                html += '</table>';
                $('#order-details-content').html(html);
                $('#order-modal').show();
                $('.overlay').show();
            } else {
                $('#order-details-content').html('<p>Không tìm thấy chi tiết đơn hàng.</p>');
                $('#order-modal').show();
                $('.overlay').show();
            }
        },
        error: function(xhr) {
            $('#order-details-content').html('<p>Lỗi: ' + xhr.responseText + '</p>');
            $('#order-modal').show();
            $('.overlay').show();
        }
    });
}

function closeOrderModal() {
    $('#order-modal').hide();
    $('.overlay').hide();
    $('#order-details-content').html('');
}
</script>
</body>
</html>