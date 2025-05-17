
<div id="containter_shop">
     <!-- <div class="container_slideshow">
                <img class="slideshow" src="/LaptopStore-master/LaptopStore/Store/assets/img/slideshow3.webp" alt="slide1" style="width: 100%; height: 100%;"> 
                <img class="slideshow" src="/LaptopStore-master/LaptopStore/Store/assets/img/slideshow2.webp" alt="slide2" style="width: 100%; height: 100%;"> 
                <img class="slideshow" src="/LaptopStore-master/LaptopStore/Store/assets/img/slideshow1.webp" alt="slide3" style="width: 100%; height: 100%;">
            </div>
    <div class="container_main"> -->
        <div class="content-wrapper">
            <?php
                    $page = isset($_GET["page"]) ? $_GET["page"] : "home";
                    switch ($page) {
                        case "home":
                            include 'filter.php';
                            break;
                        case "search":
                            include 'sanpham.php'; // Sử dụng sanpham.php cho kết quả tìm kiếm
                            break;
                        case "page1":
                            break;
                        default:
                            break;
                    }
                    ?>
            </div>

            <!-- Nội dung bên phải -->
            <div class="content-main">
                <div id="content">
                    <?php
                    $page = isset($_GET["page"]) ? $_GET["page"] : "home";
                    switch ($page) {
                        case "home":
                            include 'sanpham.php';
                            break;
                        case "sanpham":
                            include 'sanpham.php';
                            break;
                        case "cart":
                            include 'cart.php';
                            include 'order_history.php';
                            break;
                        case "search":
                            include 'sanpham.php'; // Sử dụng sanpham.php cho kết quả tìm kiếm
                            break;
                        default:
                            echo "Page not found";
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .content-wrapper {
        display: flex;
     
        margin: 0 auto;
        padding: 20px;
        gap: 20px;
    }

    .filter-sidebar {
        width: 250px;
        background: #f8f8f8;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .filter-sidebar h3 {
        margin-bottom: 20px;
        font-size: 1.5rem;
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

    .filter-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        outline: none;
    }

    .filter-group select:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
    }

    .filter-sidebar button {
        width: 100%;
        padding: 12px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .filter-sidebar button:hover {
        background: #0056b3;
    }

    .content-main {
        flex: 1;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .content-wrapper {
            flex-direction: column;
        }

        .filter-sidebar {
            width: 100%;
        }

        .content-main {
            width: 100%;
        }
    }
</style>