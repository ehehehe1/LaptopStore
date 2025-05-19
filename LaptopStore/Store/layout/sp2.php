<?php
require 'db.php';  
$sql = "SELECT sp.MASP, sp.TENSP, sp.IMG, MIN(ct.GIABAN) AS GIABAN
        FROM SANPHAM sp
        LEFT JOIN CT_SANPHAM ct ON sp.MASP = ct.MASP
        WHERE sp.TRANGTHAI = 1
        GROUP BY sp.MASP, sp.TENSP, sp.IMG";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link type="text/css" rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 0 10px;
        }
        .product {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            cursor: pointer;
            height: 320px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        .product p {
            padding: 15px;
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
            margin: 0;
            line-height: 1.4;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        .product .price {
            color: #e63946;
            font-weight: bold;
            margin: 10px 0;
        }
        #iframe-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            height: 80vh;
            max-height: 600px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            overflow: hidden;
            animation: slideIn 0.3s ease-out;
        }
        #iframe-modal iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        #iframe-overlay {
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
        .loading {
            display: none;
            margin: 10px auto;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
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
            .product-list {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }
            .product {
                height: 280px;
            }
            .product img {
                height: 160px;
            }
            .product p {
                font-size: 1rem;
                padding: 10px;
            }
            #iframe-modal {
                width: 95%;
                height: 70vh;
                max-height: 500px;
            }
        }
        @media (max-width: 480px) {
            .product-list {
                grid-template-columns: 1fr;
            }
            .product {
                height: 260px;
            }
            .product img {
                height: 140px;
            }
            #iframe-modal {
                width: 98%;
                height: 60vh;
                max-height: 400px;
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
    <div class="loading" id="loading">Đang tìm kiếm...</div>
    <div class="product-list" id="product-results">
        <?php while ($row = $result->fetch_assoc()): ?>
            <a href="layout/chitietsp.php?masp=<?php echo htmlspecialchars($row['MASP']); ?>" onclick="return showModal(this.href)" style="text-decoration: none; color: inherit;">
                <div class="product">
                    <img src="assets/img/product/<?php echo htmlspecialchars($row['IMG']); ?>" alt="<?php echo htmlspecialchars($row['TENSP']); ?>">
                    <p><?php echo htmlspecialchars($row['TENSP']); ?></p>
                    <p class="price"><?php echo number_format($row['GIABAN'], 0, ',', '.') . ' đ'; ?></p>
                </div>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Modal Popup sử dụng iframe -->
    <div class="overlay" id="iframe-overlay" onclick="closeModal()"></div>
    <div class="modal" id="iframe-modal">
        <span class="close-btn" onclick="closeModal()">×</span>
        <iframe id="modal-iframe" src=""></iframe>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        function showModal(url) {
            document.getElementById('modal-iframe').src = url;
            document.getElementById('iframe-modal').style.display = 'block';
            document.getElementById('iframe-overlay').style.display = 'block';
            return false;
        }
        function closeModal() {
            document.getElementById('modal-iframe').src = "";
            document.getElementById('iframe-modal').style.display = 'none';
            document.getElementById('iframe-overlay').style.display = 'none';
        }
    </script>
    
</body>
</html>