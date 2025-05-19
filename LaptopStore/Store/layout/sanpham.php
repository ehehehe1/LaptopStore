<?php
require 'db.php';

// Truy vấn danh sách sản phẩm tĩnh (mặc định, không phân trang)
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link type="text/css" rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: flex;
            gap: 20px;
        }

        .filter-sidebar {
            width: 250px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-sidebar h3 {
            margin: 0 0 15px;
            font-size: 1.2rem;
            color: #333;
        }

        .filter-group {
            margin-bottom: 15px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .filter-group select,
        .filter input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        .filter-sidebar button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .filter-sidebar button:hover {
            background: #0056b3;
        }

        .main-content {
            flex: 1;
        }

        .filter {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

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
            margin: 20px auto;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s;
        }

        .pagination a.active,
        .pagination a:hover {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .pagination a.disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        @keyframes slideIn {
            from {
                transform: translate(-50%, -60%);
                opacity: 0;
            }

            to {
                transform: translate(-50%, -50%);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .filter-sidebar {
                width: 100%;
            }

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

            .product p {
                font-size: 0.9rem;
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


    <!-- Nội dung chính -->
    <div class="main-content">




        <div class="loading" id="loading">Đang tải...</div>
        <div class="product-list" id="product-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <a href="layout/chitietsp.php?masp=<?php echo $row['MASP']; ?>" class="product-link"
                        style="text-decoration: none; color: inherit;">
                        <div class="product">
                            <img src="assets/img/product/<?php echo htmlspecialchars($row['IMG']); ?>"
                                alt="<?php echo htmlspecialchars($row['TENSP']); ?>" loading="lazy">

                            <p><?php echo htmlspecialchars($row['TENSP']); ?></p>
                            <p class="price"><?php echo number_format($row['GIABAN'], 0, ',', '.'); ?> đ</p>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php endif; ?>
        </div>
        <div id="pagination"></div>
    </div>

    <!-- Modal Popup sử dụng iframe -->
    <div class="overlay" id="iframe-overlay" onclick="closeModal()"></div>
    <div class="modal" id="iframe-modal">
        <span class="close-btn" onclick="closeModal()">×</span>
        <iframe id="modal-iframe" src=""></iframe>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // $(document).ready(function() {
        //     // Tìm kiếm thời gian thực khi nhập
        //     let timeout;
        //     $('#search').on('keyup', function() {
        //         clearTimeout(timeout);
        //         timeout = setTimeout(() => {
        //             applyFilters(1);
        //         }, 300);
        //     });

        //     // Hàm áp dụng bộ lọc và tìm kiếm
        //     window.applyFilters = function(page) {
        //         var query = $('#search').val();
        //         var price = $('#filter-price').val();
        //         var type = $('#filter-type').val() || $('#nav_menu li a.active').data('type') || '';
        //         var $results = $('#product-container').empty();
        //         var $loading = $('#loading').show();

        //         $.ajax({
        //             type: 'POST',
        //             url: '/LaptopStore-master/LaptopStore/Store/layout/search.php',
        //             data: {
        //                 query: query,
        //                 price: price,
        //                 type: type,
        //                 page: page,
        //                 paginate: true
        //             },
        //             dataType: 'json',
        //             success: function(response) {
        //                 $loading.hide();
        //                 if (response.success && response.products.length > 0) {
        //                     $('#product-container').html(response.html);
        //                     $('#pagination').html(response.pagination);
        //                 } else {
        //                     $results.append('<p>Không tìm thấy sản phẩm nào.</p>');
        //                     $('#pagination').hide();
        //                 }
        //             },
        //             error: function(xhr) {
        //                 $loading.hide();
        //                 $results.html('<p>Lỗi: ' + xhr.responseText + '</p>');
        //                 $('#pagination').hide();
        //                 Swal.fire({
        //                     icon: 'error',
        //                     title: 'Lỗi server',
        //                     text: 'Lỗi: ' + xhr.responseText
        //                 });
        //             }
        //         });
        //     };

        $(document).ready(function () {
            // Tìm kiếm thời gian thực khi nhập
            let timeout;
            $('#search').on('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    applyFilters(1); // Luôn gọi page 1
                }, 300);
            });

            // Hàm áp dụng bộ lọc và tìm kiếm (không phân trang)
            window.applyFilters = function (page) {
                var query = $('#search').val();
                var price = $('#filter-price').val();
                var type = $('#filter-type').val() || $('#nav_menu li a.active').data('type') || '';
                var $results = $('#product-container').empty();
                var $loading = $('#loading').show();
                $('#pagination').empty(); // Ẩn phân trang

                $.ajax({
                    type: 'POST',
                    url: 'search.php',
                    data: {
                        query: query,
                        price: price,
                        type: type,
                        page: 1, // Luôn page 1
                        paginate: false // Không phân trang
                    },
                    dataType: 'json',
                    success: function (response) {
                        $loading.hide();
                        if (response.success && response.products.length > 0) {
                            $('#product-container').html(response.html);
                        } else {
                            $results.append('<p>Không tìm thấy sản phẩm nào.</p>');
                        }
                    },
                    error: function (xhr) {
                        $loading.hide();
                        $results.html('<p>Lỗi: ' + xhr.responseText + '</p>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi server',
                            text: 'Lỗi: ' + xhr.responseText
                        });
                    }
                });
            };
            // Hàm xử lý click menu loại sản phẩm
            window.showproductMenu = function (type) {
                console.log('Filtering products for type:', type);
                $.ajax({
                    type: 'POST',
                    url: 'layout/fetch_products.php',

                    data: {
                        type: type,
                        page: 1,
                        paginate: true
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('Fetch products response:', response);
                        if (response.success) {
                            $('#product-container').html(response.html);
                            $('#pagination').html(response.pagination);
                            // Cập nhật trạng thái active cho menu
                            $('#nav_menu li a').removeClass('active');
                            if (type) {
                                $(`#nav_menu li a[data-type="${type}"]`).addClass('active');
                            } else {
                                $(`#nav_menu li a[data-type=""]`).addClass('active');
                            }
                            // Reset bộ lọc
                            $('#filter-price').val('');
                            $('#filter-type').val('');
                            $('#search').val('');
                        } else {
                            $('#product-container').html('<p>Không tìm thấy sản phẩm nào.</p>');
                            $('#pagination').hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: response.error
                            });
                        }
                    },
                    error: function (xhr) {
                        $('#product-container').html('<p>Lỗi: ' + xhr.responseText + '</p>');
                        $('#pagination').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi server',
                            text: 'Lỗi: ' + xhr.responseText
                        });
                    }
                });
            };

            // Xử lý phân trang
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                var page = $(this).data('page');
                var type = $(this).data('type') || '';
                var source = $(this).data('source') || '';

                console.log('Loading page:', page, 'Type:', type, 'Source:', source);
                if (source === 'search') {
                    applyFilters(page);
                } else {
                    $.ajax({
                        type: 'POST',
                        url: 'layout/fetch_products.php',

                        data: {
                            type: type,
                            page: page,
                            paginate: true
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                $('#product-container').html(response.html);
                                $('#pagination').html(response.pagination);
                            } else {
                                $('#product-container').html('<p>Không tìm thấy sản phẩm nào.</p>');
                                $('#pagination').hide();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Lỗi',
                                    text: response.error
                                });
                            }
                        },
                        error: function (xhr) {
                            $('#product-container').html('<p>Lỗi: ' + xhr.responseText + '</p>');
                            $('#pagination').hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi server',
                                text: 'Lỗi: ' + xhr.responseText
                            });
                        }
                    });
                }
            });

            // Hàm hiển thị modal chi tiết sản phẩm
            window.showModal = function (url) {
                document.getElementById('modal-iframe').src = url;
                document.getElementById('iframe-modal').style.display = 'block';
                document.getElementById('iframe-overlay').style.display = 'block';
                return false;
            };

            // Hàm đóng modal
            window.closeModal = function () {
                document.getElementById('modal-iframe').src = "";
                document.getElementById('iframe-modal').style.display = 'none';
                document.getElementById('iframe-overlay').style.display = 'none';
            };

            // Event delegation cho các sản phẩm động
            $(document).on('click', '#product-container a.product-link', function (e) {
                e.preventDefault();
                showModal(this.href);
                return false;
            });
        });
    </script>
</body>

</html>