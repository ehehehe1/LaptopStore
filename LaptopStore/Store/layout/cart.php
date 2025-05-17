<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';



$matk = $_SESSION['user_id'];

// Lấy thông tin tài khoản
$stmt = $conn->prepare("SELECT HOTEN, SDT, EMAIL, DIACHI FROM TAIKHOAN WHERE MATK = ?");
$stmt->bind_param("s", $matk);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy đơn hàng giỏ hàng (TRANGTHAI = -1)
$stmt = $conn->prepare("SELECT d.MADH, d.TONGTIEN, ct.MACTSP, ct.SOLUONG, ct.DONGIA, ct.THANHTIEN, s.TENSP, ctsp.THONGSO, ctsp.MAU, ctsp.SIZE, ctsp.MASP 
                        FROM DONHANG d 
                        JOIN CT_DONHANG ct ON d.MADH = ct.MADH 
                        JOIN CT_SANPHAM ctsp ON ct.MACTSP = ctsp.MACTSP 
                        JOIN SANPHAM s ON ctsp.MASP = s.MASP 
                        WHERE d.MATK = ? AND d.TRANGTHAI = -1");
$stmt->bind_param("s", $matk);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;
$madh = '';
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['THANHTIEN'];
    $madh = $row['MADH'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        /* Giữ nguyên CSS hiện tại */
        .cart {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        .total {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: right;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
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
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        .quantity-controls button {
            padding: 5px 10px;
            font-size: 1rem;
        }
        .quantity-controls input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 5px;
        }
        .delete-btn {
            background: #e63946;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        .delete-btn:hover {
            background: #b32d39;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }
        .empty-cart {
            text-align: center;
            color: #555;
        }
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
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
            font-size: 1.5rem;
        }
        .modal h3 {
            color: #555;
            margin: 15px 0 10px;
            font-size: 1.2rem;
        }
        .modal label {
            display: block;
            margin-top: 10px;
            font-weight: 500;
            color: #555;
        }
        .modal textarea, .modal select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .modal button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            font-size: 1rem;
        }
        .modal table {
            margin-bottom: 0;
        }
        .modal .total {
            margin-top: 10px;
            color: #333;
        }
        .modal .user-info p, .modal .order-info p {
            margin: 5px 0;
            font-size: 1rem;
        }
        .modal .success-message {
            color: #28a745;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #555;
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
<div class="cart">
    <h1>Giỏ hàng của bạn</h1>
    <div id="cart-error" class="error" style="display: none;"></div>
    <?php if (empty($cart_items)): ?>
        <p class="empty-cart">Giỏ hàng trống. <a href="/LaptopStore-master/LaptopStore/Store/index.php">Tiếp tục mua sắm</a></p>
    <?php else: ?>
        <table id="cart-table">
            <tr>
                <th>Sản phẩm</th>
                <th>Thông số</th>
                <th>Màu</th>
                <th>Kích thước</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
                <th>Xóa</th>
            </tr>
            <?php foreach ($cart_items as $item): ?>
                <tr data-mactsp="<?php echo htmlspecialchars($item['MACTSP']); ?>">
                    <td><?php echo htmlspecialchars($item['TENSP']); ?></td>
                    <td><?php echo htmlspecialchars($item['THONGSO']); ?></td>
                    <td><?php echo htmlspecialchars($item['MAU']); ?></td>
                    <td><?php echo htmlspecialchars($item['SIZE']); ?></td>
                    <td>
                        <div class="quantity-controls">
                            <button class="decrement">-</button>
                            <input type="number" class="quantity" value="<?php echo htmlspecialchars($item['SOLUONG']); ?>" min="1" readonly>
                            <button class="increment">+</button>
                        </div>
                    </td>
                    <td class="dongia"><?php echo number_format($item['DONGIA'], 0, ',', '.'); ?> đ</td>
                    <td class="thanhtien"><?php echo number_format($item['THANHTIEN'], 0, ',', '.'); ?> đ</td>
                    <td>
                        <button class="delete-btn" onclick="deleteCartItem('<?php echo htmlspecialchars($item['MACTSP']); ?>')">Xóa</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="total">
            Tổng cộng: <span id="cart-total"><?php echo number_format($total, 0, ',', '.'); ?></span> đ
        </div>
        <button id="checkout-btn">Thanh toán</button>
        <p><a href="/LaptopStore-master/LaptopStore/Store/index.php">Tiếp tục mua sắm</a></p>
    <?php endif; ?>
</div>

<!-- Modal thanh toán -->
<div class="overlay" id="checkout-overlay" onclick="closeCheckoutModal()"></div>
<div class="modal" id="checkout-modal">
    <span class="close-btn" onclick="closeCheckoutModal()">×</span>
    <div class="modal-content">
        <h2>Thanh toán</h2>
        <div id="checkout-error" class="error" style="display: none;"></div>
        <div class="user-info">
            <h3>Thông tin tài khoản</h3>
            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($user['HOTEN']); ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user['SDT']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['EMAIL']); ?></p>
        </div>
        <div class="cart-info">
            <h3>Giỏ hàng</h3>
            <table id="checkout-table">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Thông số</th>
                    <th>Màu</th>
                    <th>Kích thước</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
                <!-- Dữ liệu sẽ được thêm bởi JavaScript -->
            </table>
            <div class="total" id="checkout-total"></div>
        </div>
        <form id="checkout-form">
            <label for="diachi">Địa chỉ giao hàng:</label>
            <textarea name="diachi" id="diachi" rows="3"><?php echo htmlspecialchars($user['DIACHI']); ?></textarea>
            <label for="phuongthuc">Phương thức thanh toán:</label>
            <select name="phuongthuc" id="phuongthuc" required>
                <option value="cod">Tiền mặt (COD)</option>
                <option value="online">Thanh toán trực tuyến</option>
            </select>
            <button type="submit">Xác nhận thanh toán</button>
        </form>
    </div>
</div>

<!-- Modal xác nhận đơn hàng -->
<div class="overlay" id="confirmation-overlay" onclick="closeConfirmationModal()"></div>
<div class="modal" id="confirmation-modal">
    <span class="close-btn" onclick="closeConfirmationModal()">×</span>
    <div class="modal-content" id="confirmation-content">
        <p class="loading">Đang xử lý...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    // Xử lý tăng/giảm số lượng
    $('.increment, .decrement').on('click', function() {
        var $row = $(this).closest('tr');
        var mactsp = $row.data('mactsp');
        var $quantityInput = $row.find('.quantity');
        var currentQuantity = parseInt($quantityInput.val());
        var newQuantity = $(this).hasClass('increment') ? currentQuantity + 1 : currentQuantity - 1;

        if (newQuantity < 1) {
            alert('Số lượng không thể nhỏ hơn 1.');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '/LaptopStore-master/LaptopStore/Store/layout/update_cart.php',
            data: {
                madh: '<?php echo htmlspecialchars($madh); ?>',
                mactsp: mactsp,
                new_quantity: newQuantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $quantityInput.val(newQuantity);
                    var dongia = parseInt($row.find('.dongia').text().replace(/[^0-9]/g, ''));
                    var newThanhtien = dongia * newQuantity;
                    $row.find('.thanhtien').text(newThanhtien.toLocaleString('vi-VN') + ' đ');
                    var total = 0;
                    $('.thanhtien').each(function() {
                        total += parseInt($(this).text().replace(/[^0-9]/g, ''));
                    });
                    $('#cart-total').text(total.toLocaleString('vi-VN') + ' đ');
                    $('#cart-error').hide();
                } else {
                    $('#cart-error').text('Lỗi: ' + response.error).show();
                }
            },
            error: function(xhr) {
                console.error('update_cart error:', xhr.responseText);
                $('#cart-error').text('Lỗi: ' + xhr.responseText).show();
            }
        });
    });

    // Xử lý xóa sản phẩm
    window.deleteCartItem = function(mactsp) {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;

        $.ajax({
            type: 'POST',
            url: '/LaptopStore-master/LaptopStore/Store/layout/delete_cart_item.php',
            data: {
                madh: '<?php echo htmlspecialchars($madh); ?>',
                mactsp: mactsp
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(`tr[data-mactsp="${mactsp}"]`).remove();
                    var total = 0;
                    $('.thanhtien').each(function() {
                        total += parseInt($(this).text().replace(/[^0-9]/g, ''));
                    });
                    $('#cart-total').text(total.toLocaleString('vi-VN') + ' đ');
                    if ($('#cart-table tr').length <= 1) {
                        $('.cart').html(`
                            <h1>Giỏ hàng của bạn</h1>
                            <p class="empty-cart">Giỏ hàng trống. <a href="/LaptopStore-master/LaptopStore/Store/index.php">Tiếp tục mua sắm</a></p>
                        `);
                    }
                    $('#cart-error').hide();
                } else {
                    $('#cart-error').text('Lỗi: ' + response.error).show();
                }
            },
            error: function(xhr) {
                console.error('delete_cart_item error:', xhr.responseText);
                $('#cart-error').text('Lỗi: ' + xhr.responseText).show();
            }
        });
    };

    // Xử lý form thanh toán
    $('#checkout-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('#checkout-error').hide();
        $('#confirmation-content').html('<p class="loading">Đang xử lý...</p>');
        $('#checkout-modal').hide();
        $('#confirmation-modal').show();
        $('#confirmation-overlay').show();

        $.ajax({
            type: 'POST',
            url: '/LaptopStore-master/LaptopStore/Store/layout/process_checkout.php',
            data: formData + '&madh=<?php echo htmlspecialchars($madh); ?>',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var html = `
                        <p class="success-message">Đơn hàng của bạn đã được đặt thành công!</p>
                        <h2>Xác nhận đơn hàng</h2>
                        <div class="order-info">
                            <p><strong>Mã đơn hàng:</strong> ${response.order.madh}</p>
                            <p><strong>Tổng tiền:</strong> ${Number(response.order.tongtien).toLocaleString('vi-VN')} đ</p>
                            <p><strong>Địa chỉ giao hàng:</strong> ${response.order.diachi}</p>
                            <p><strong>Phương thức thanh toán:</strong> ${response.order.phuongthuc === 'cod' ? 'Tiền mặt (COD)' : 'Thanh toán trực tuyến'}</p>
                        </div>
                        <h3>Chi tiết đơn hàng</h3>
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
                    response.order.items.forEach(item => {
                        html += `
                            <tr>
                                <td>${item.tensp}</td>
                                <td>${item.thongso}</td>
                                <td>${item.mau}</td>
                                <td>${item.size}</td>
                                <td>${item.soluong}</td>
                                <td>${Number(item.dongia).toLocaleString('vi-VN')} đ</td>
                                <td>${Number(item.thanhtien).toLocaleString('vi-VN')} đ</td>
                            </tr>
                        `;
                    });
                    html += `
                        </table>
                        <div class="total">Tổng cộng: ${Number(response.order.tongtien).toLocaleString('vi-VN')} đ</div>
                        <button onclick="window.location.href='/LaptopStore-master/LaptopStore/Store/index.php'">Tiếp tục mua sắm</button>
                    `;
                    $('#confirmation-content').html(html);
                    $('.cart').html(`
                        <h1>Giỏ hàng của bạn</h1>
                        <p class="empty-cart">Giỏ hàng trống. <a href="/LaptopStore-master/LaptopStore/Store/index.php">Tiếp tục mua sắm</a></p>
                    `);
                } else {
                    $('#confirmation-modal').hide();
                    $('#confirmation-overlay').hide();
                    $('#checkout-modal').show();
                    $('#checkout-overlay').show();
                    $('#checkout-error').text('Lỗi: ' + response.error).show();
                }
            },
            error: function(xhr) {
                console.error('process_checkout error:', xhr.responseText);
                $('#confirmation-modal').hide();
                $('#confirmation-overlay').hide();
                $('#checkout-modal').show();
                $('#checkout-overlay').show();
                $('#checkout-error').text('Lỗi: ' + xhr.responseText).show();
            }
        });
    });

    // Xử lý nút thanh toán
    $('#checkout-btn').on('click', function() {
        showCheckoutModal();
    });
});

// Hiển thị modal thanh toán
function showCheckoutModal() {
    if ($('#cart-table tr').length <= 1) {
        alert('Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
        return;
    }

    // Lấy dữ liệu giỏ hàng từ CSDL
    $.ajax({
        type: 'POST',
        url: '/LaptopStore-master/LaptopStore/Store/layout/get_cart_data.php',
        data: { madh: '<?php echo htmlspecialchars($madh); ?>' },
        dataType: 'json',
        success: function(response) {
            if (response.success && !response.cart_empty) {
                // Cập nhật bảng trong modal
                let html = '';
                response.cart_items.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.TENSP}</td>
                            <td>${item.THONGSO}</td>
                            <td>${item.MAU}</td>
                            <td>${item.SIZE}</td>
                            <td>${item.SOLUONG}</td>
                            <td>${Number(item.THANHTIEN).toLocaleString('vi-VN')} đ</td>
                        </tr>
                    `;
                });
                $('#checkout-table').html(`
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Thông số</th>
                        <th>Màu</th>
                        <th>Kích thước</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                    ${html}
                `);
                $('#checkout-total').text(`Tổng cộng: ${Number(response.total).toLocaleString('vi-VN')} đ`);
                $('#checkout-modal').show();
                $('#checkout-overlay').show();
                $('#checkout-error').hide();
            } else {
                alert('Giỏ hàng trống hoặc lỗi: ' + (response.error || 'Không thể tải dữ liệu.'));
            }
        },
        error: function(xhr) {
            console.error('get_cart_data error:', xhr.responseText);
            $('#checkout-error').text('Lỗi khi tải dữ liệu giỏ hàng: ' + xhr.responseText).show();
        }
    });
}

// Đóng modal thanh toán
function closeCheckoutModal() {
    $('#checkout-modal').hide();
    $('#checkout-overlay').hide();
    $('#checkout-error').hide();
}

// Đóng modal xác nhận
function closeConfirmationModal() {
    $('#confirmation-modal').hide();
    $('#confirmation-overlay').hide();
    window.location.href = '/LaptopStore-master/LaptopStore/Store/index.php';
}

</script>
</body>
</html>