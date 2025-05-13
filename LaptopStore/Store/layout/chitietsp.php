<?php
require 'db.php';

// Lấy mã sản phẩm từ URL
$masp = isset($_GET['masp']) ? $_GET['masp'] : '';
if (empty($masp)) {
    echo "<p>Không tìm thấy mã sản phẩm.</p>";
    exit;
}

// Truy vấn JOIN giữa SANPHAM và CT_SANPHAM, lấy cột IMG
$sql = "SELECT s.TENSP, s.IMG, ct.MAU, ct.SIZE, ct.GIABAN, ct.THONGSO, ct.SOLUONG 
        FROM SANPHAM s 
        INNER JOIN CT_SANPHAM ct ON s.MASP = ct.MASP 
        WHERE s.MASP = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $masp);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p>Sản phẩm không tồn tại.</p>";
    exit;
}

$variants = [];
$colors = [];
$sizes = [];
$specs = [];
$image = '';

while ($row = $result->fetch_assoc()) {
    $variants[] = $row;
    if (!in_array($row['MAU'], $colors)) {
        $colors[] = $row['MAU'];
    }
    if (!in_array($row['SIZE'], $sizes)) {
        $sizes[] = $row['SIZE'];
    }
    if (!in_array($row['THONGSO'], $specs)) {
        $specs[] = $row['THONGSO'];
    }
    // Lấy hình ảnh từ bản ghi đầu tiên
    if (empty($image)) {
        $image = $row['IMG'] ?: 'images/default-product.jpg'; // Đường dẫn mặc định nếu không có hình
    }
}

// Lấy thông tin cơ bản từ biến thể đầu tiên
$row_first = $variants[0];
$name = $row_first['TENSP'];
$defaultPrice = $row_first['GIABAN'];
$defaultQty = $row_first['SOLUONG'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <style>
        .detail {
            display: flex;
            border: 1px solid #ddd;
            padding: 20px;
            max-width: 900px; /* Tăng chiều rộng để chứa cả hình và thông tin */
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .image-container {
            flex: 1;
            max-width: 40%;
            padding-right: 20px;
        }
        .image-container img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            object-fit: cover;
            max-height: 400px; /* Giới hạn chiều cao hình ảnh */
        }
        .info-container {
            flex: 1;
            max-width: 60%;
        }
        .info-container h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: 500;
            color: #555;
        }
        select, input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            padding: 10px 20px;
            margin-top: 15px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
        }
        button:hover {
            background: #0056b3;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }
        .quantity-controls button {
            padding: 5px 12px;
            font-size: 1.2rem;
            margin: 0 5px;
        }
        .stock {
            margin-top: 10px;
            color: #333;
            font-weight: 500;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .detail {
                flex-direction: column;
            }
            .image-container, .info-container {
                max-width: 100%;
                padding-right: 0;
                margin-bottom: 20px;
            }
            .image-container img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
<div class="detail">
    <div class="image-container">
        <img src="/LaptopStore/Store/assets/img/product/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>">
    </div>
    <div class="info-container">
        <h1><?php echo htmlspecialchars($name); ?></h1>
        <p id="product-price">Giá: <?php echo number_format($defaultPrice, 0, ',', '.'); ?> đ</p>
        <p class="stock">Số lượng còn lại: <span id="remaining-quantity"><?php echo htmlspecialchars($defaultQty); ?></span></p>
        
        <form id="add-to-cart-form" method="POST">
            <input type="hidden" name="masp" value="<?php echo htmlspecialchars($masp); ?>">
            
            <label for="spec-select">Chọn thông số:</label>
            <select name="spec" id="spec-select">
                <?php foreach ($specs as $spec): ?>
                    <option value="<?php echo htmlspecialchars($spec); ?>"><?php echo htmlspecialchars($spec); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label for="color-select">Chọn màu:</label>
            <select name="color" id="color-select">
                <?php
                $initialSpec = $specs[0];
                $initialColors = [];
                foreach ($variants as $variant) {
                    if ($variant['THONGSO'] === $initialSpec && !in_array($variant['MAU'], $initialColors)) {
                        $initialColors[] = $variant['MAU'];
                        echo '<option value="' . htmlspecialchars($variant['MAU']) . '">' . htmlspecialchars($variant['MAU']) . '</option>';
                    }
                }
                ?>
            </select>
            
            <label for="size-select">Chọn kích thước:</label>
            <select name="size" id="size-select">
                <?php
                $initialSizes = [];
                foreach ($variants as $variant) {
                    if ($variant['THONGSO'] === $initialSpec && !in_array($variant['SIZE'], $initialSizes)) {
                        $initialSizes[] = $variant['SIZE'];
                        echo '<option value="' . htmlspecialchars($variant['SIZE']) . '">' . htmlspecialchars($variant['SIZE']) . '</option>';
                    }
                }
                ?>
            </select>
            
            <label for="buy-quantity">Số lượng muốn mua:</label>
            <div class="quantity-controls">
                <button type="button" id="decrement">–</button>
                <input type="number" name="quantity" id="buy-quantity" min="1" value="1" max="<?php echo htmlspecialchars($defaultQty); ?>">
                <button type="button" id="increment">+</button>
            </div>
            
            <button type="submit">Thêm vào giỏ hàng</button>
        </form>
        <div id="cart-message" class="message" style="display: none;"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
var variants = <?php echo json_encode($variants); ?>;

function updateOptions() {
    var selectedSpec = document.getElementById('spec-select').value;
    var filteredVariants = variants.filter(function(v) {
        return v.THONGSO === selectedSpec;
    });
    
    var availableColors = [];
    filteredVariants.forEach(function(v) {
        if (availableColors.indexOf(v.MAU) === -1) {
            availableColors.push(v.MAU);
        }
    });
    var colorSelect = document.getElementById('color-select');
    colorSelect.innerHTML = "";
    availableColors.forEach(function(color) {
        var op = document.createElement("option");
        op.value = color;
        op.text = color;
        colorSelect.appendChild(op);
    });
    
    var availableSizes = [];
    filteredVariants.forEach(function(v) {
        if (availableSizes.indexOf(v.SIZE) === -1) {
            availableSizes.push(v.SIZE);
        }
    });
    var sizeSelect = document.getElementById('size-select');
    sizeSelect.innerHTML = "";
    availableSizes.forEach(function(size) {
        var op = document.createElement("option");
        op.value = size;
        op.text = size;
        sizeSelect.appendChild(op);
    });
    
    updatePrice();
}

function updatePrice() {
    var selectedSpec = document.getElementById('spec-select').value;
    var selectedColor = document.getElementById('color-select').value;
    var selectedSize = document.getElementById('size-select').value;
    
    var variant = variants.find(function(v) {
        return v.THONGSO === selectedSpec && v.MAU === selectedColor && v.SIZE === selectedSize;
    });
    
    if (variant) {
        document.getElementById('product-price').innerText = "Giá: " + 
            parseInt(variant.GIABAN).toLocaleString('vi-VN') + " đ";
        document.getElementById('remaining-quantity').innerText = variant.SOLUONG;
        document.getElementById('buy-quantity').max = variant.SOLUONG;
        
        var currentQty = parseInt(document.getElementById('buy-quantity').value, 10);
        if (currentQty > variant.SOLUONG) {
            document.getElementById('buy-quantity').value = 1;
        }
    } else {
        document.getElementById('product-price').innerText = "Giá: Liên hệ";
        document.getElementById('remaining-quantity').innerText = "0";
        document.getElementById('buy-quantity').max = 0;
    }
}

document.getElementById('spec-select').addEventListener('change', updateOptions);
document.getElementById('color-select').addEventListener('change', updatePrice);
document.getElementById('size-select').addEventListener('change', updatePrice);

document.getElementById('increment').addEventListener('click', function() {
    var quantityInput = document.getElementById('buy-quantity');
    var currentQuantity = parseInt(quantityInput.value, 10);
    var maxQuantity = parseInt(quantityInput.max, 10) || 0;
    if (currentQuantity < maxQuantity) {
        quantityInput.value = currentQuantity + 1;
    }
});

document.getElementById('decrement').addEventListener('click', function() {
    var quantityInput = document.getElementById('buy-quantity');
    var currentQuantity = parseInt(quantityInput.value, 10);
    if (currentQuantity > 1) {
        quantityInput.value = currentQuantity - 1;
    }
});

// Xử lý AJAX cho form
$(document).ready(function() {
    $('#add-to-cart-form').on('submit', function(event) {
        event.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "/LaptopStore/Store/layout/add_cart.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                var messageDiv = $('#cart-message');
                messageDiv.hide().removeClass('success error');
                if (response.success) {
                    messageDiv.addClass('success').html(response.message).show();
                } else {
                    messageDiv.addClass('error').html(response.error).show();
                }
            },
            error: function(xhr, status, error) {
                $('#cart-message').addClass('error').html('Lỗi: ' + xhr.responseText).show();
            }
        });
    });
});

updateOptions();
</script>
</body>
</html>